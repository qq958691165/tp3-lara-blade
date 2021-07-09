# ThinkPHP3.2 blade模板引擎

## 用法
### 1.安装
```shell
composer require qq958691165/tp3-lara-blade
```

### 2.使用

若无"APP_DIR"常量请先定义该常量，值为Runtime的父级目录

#### 2.1 创建基数AbstractController
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

#### 2.2 控制器继承AbstractController类即可
