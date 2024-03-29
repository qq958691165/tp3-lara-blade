# ThinkPHP3.2 blade模板引擎

## 用法
### 安装
```shell
composer require qq958691165/tp3-lara-blade
```

### 使用
共有两种用法: 控制器用法(局部使用)、配置用法(全局使用)
#### 控制器用法
若无"APP_DIR"常量请先定义该常量，值为Runtime的父级目录

##### 创建基类AbstractController
```php
use Tp3LaraBlade\Library\BladeView;

class AbstractController extends \Think\Controller {
    public function __construct()
    {
        parent::__construct();
        $this->view=BladeView::instance();
    }
}
```

##### 控制器继承AbstractController类即可使用
Home/Controller/IndexController.class.php
```php
class IndexController extends AbstractController{
    public function index(){
        $this->msg='123456';
//        $str=$this->fetch();
//        dump($str);
        $this->display();
    }
}
```

Home/View/default/Index/index.blade.php
```html
@extends('Home::default.Index.layout')
@section('body')
<p>hello {{$msg}}</p>
@endsection
```

#### 配置用法
配置config.php(或C函数配置)
```php
return [
    //...
    'TMPL_ENGINE_TYPE'=>'blade',
    'TMPL_TEMPLATE_SUFFIX'=>'.blade.php',
    //...
];
```
或
```php
C('TMPL_ENGINE_TYPE','blade');
C('TMPL_TEMPLATE_SUFFIX','.blade.php');
```

### 说明
#### blade模板语法请参考[laravel文档](https://learnku.com/docs/laravel/5.8/blade/3902)

#### 若要启用@extends用法，则需要注册命名空间，以命名空间的方式去调用继承的视图(默认已注册Home模块)
```php
\Tp3LaraBlade\RegisterContainer::registerNamespace('Admin',APP_DIR.'/Admin/View');
```