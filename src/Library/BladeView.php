<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Tp3LaraBlade\Library;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View;
use Think\Hook;
use Tp3LaraBlade\RegisterContainer;

/**
 * ThinkPHP 视图类
 */
class BladeView {
    private static $_self;
    private $_factory;

    public static function instance(){
        if (!self::$_self){
            self::$_self=new self();
        }
        return self::$_self;
    }

    private function __construct()
    {
        $this->file=new Filesystem();
        $this->compiler=new BladeCompiler($this->file,APP_DIR.'/Runtime/Cache');
        $resolver=new EngineResolver();
        $resolver->register('blade',function (){
            return new CompilerEngine($this->compiler);
        });

        $this->resolver=$resolver;
    }

    public function getFactory($prefix=''){

        if ($this->_factory){
            return $this->_factory;
        }
        if ($prefix) {
            $this->compiler->setCachePath(APP_DIR.'/Runtime/Cache/'.$prefix);
        }
        $event=new Dispatcher();
        $factory = new BladeFactory($this->resolver,new FileViewFinder($this->file,[]),$event);
        foreach (RegisterContainer::getNamespaces() as $namespace=>$path) {
            $factory->addNamespace($namespace,$path);
        }
        $this->_factory=$factory;
        return $this->_factory;
    }

    /**
     * 模板输出变量
     * @var tVar
     * @access protected
     */ 
    protected $tVar     =   array();

    /**
     * 模板主题
     * @var theme
     * @access protected
     */ 
    protected $theme    =   '';

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 取得模板变量的值
     * @access public
     * @param string $name
     * @return mixed
     */
    public function get($name=''){
        if('' === $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name])?$this->tVar[$name]:false;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        G('viewStartTime');
        // 视图开始标签
        Hook::listen('view_begin',$templateFile);
        // 解析并获取模板内容
        $content = $this->fetch($templateFile,$content,$prefix);
        // 输出模板内容
        $this->render($content,$charset,$contentType);
        // 视图结束标签
        Hook::listen('view_end');
    }

    /**
     * 输出内容文本可以包括Html
     * @access private
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @return mixed
     */
    private function render($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // 网页字符编码
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: '.C('HTTP_CACHE_CONTROL'));  // 页面缓存控制
        //header('X-Powered-By:ThinkPHP');
        // 输出模板文件
        echo $content;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix='') {
        if(empty($content)) {
            $templateFile   =   $this->parseTemplate($templateFile);
            // 模板文件不存在直接返回
            if(!is_file($templateFile)) E(L('_TEMPLATE_NOT_EXIST_').':'.$templateFile);
        }else{
            defined('THEME_PATH') or    define('THEME_PATH', $this->getThemePath());
        }
        $factory=$this->getFactory($prefix);
        $content=$factory->file($templateFile)->with($this->tVar)->render();
        // 输出模板文件
        return $content;
    }

    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template='') {
        $suffix='.blade.php';
        if(is_file($template)) {
            return $template;
        }
        $depr       =   C('TMPL_FILE_DEPR');
        $template   =   str_replace(':', $depr, $template);

        // 获取当前模块
        $module   =  MODULE_NAME;
        if(strpos($template,'@')){ // 跨模块调用模版文件
            list($module,$template)  =   explode('@',$template);
        }
        //因TP的挂件在跨模块调用时不太好使，这里将主题地址改成变量获取，不使用常量
        $theme_path = $this->getThemePath($module);
        // 获取当前主题的模版路径
        defined('THEME_PATH') or    define('THEME_PATH', $this->getThemePath($module));

        // 分析模板文件规则
        if('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = CONTROLLER_NAME . $depr . ACTION_NAME;
        }elseif(false === strpos($template, $depr)){
            $template = CONTROLLER_NAME . $depr . $template;
        }
        $file   =   THEME_PATH.$template.$suffix;
        if(C('TMPL_LOAD_DEFAULTTHEME') && THEME_NAME != C('DEFAULT_THEME') && !is_file($file)){
            // 找不到当前主题模板的时候定位默认主题中的模板
            $file   =   dirname(theme_path).'/'.C('DEFAULT_THEME').'/'.$template.$suffix;
        }
        return $file;
    }

    /**
     * 获取当前的模板路径
     * @access protected
     * @param  string $module 模块名
     * @return string
     */
    protected function getThemePath($module=MODULE_NAME){
        // 获取当前主题名称
        $theme = $this->getTemplateTheme();
        // 获取当前主题的模版路径
        $tmplPath   =   C('VIEW_PATH'); // 模块设置独立的视图目录
        if(!$tmplPath){ 
            // 定义TMPL_PATH 则改变全局的视图目录到模块之外
            $tmplPath   =   defined('TMPL_PATH')? TMPL_PATH.$module.'/' : APP_PATH.$module.'/'.C('DEFAULT_V_LAYER').'/';
        }
        return $tmplPath.$theme;
    }

    /**
     * 设置当前输出的模板主题
     * @access public
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme){
        $this->theme = $theme;
        return $this;
    }

    /**
     * 获取当前的模板主题
     * @access private
     * @return string
     */
    private function getTemplateTheme() {
        if($this->theme) { // 指定模板主题
            $theme = $this->theme;
        }else{
            /* 获取模板主题名称 */
            $theme =  C('DEFAULT_THEME');
            if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])){
                    $theme = $_GET[$t];
                }elseif(cookie('think_template')){
                    $theme = cookie('think_template');
                }
                if(!in_array($theme,explode(',',C('THEME_LIST')))){
                    $theme =  C('DEFAULT_THEME');
                }
                cookie('think_template',$theme,864000);
            }
        }
        defined('THEME_NAME') || define('THEME_NAME',   $theme);                  // 当前模板主题名称
        return $theme?$theme . '/':'';
    }

}