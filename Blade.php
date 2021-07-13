<?php


namespace Think\Template\Driver;


use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use Tp3LaraBlade\Library\BladeCompiler;
use Tp3LaraBlade\Library\BladeFactory;
use Tp3LaraBlade\RegisterContainer;

class Blade
{
    private $_factory;

    public function __construct()
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
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile,$var) {
        $content= $this->getFactory()->file($templateFile)->with($var)->render();
        echo $content;
    }
}