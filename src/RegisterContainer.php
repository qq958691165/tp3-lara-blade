<?php


namespace Tp3LaraBlade;


use Tp3LaraBlade\Library\BladeView;

class RegisterContainer
{
    private function __construct()
    {
    }

    protected static $namespaces=[
        'Home'=>APP_PATH.'/Home/View',
    ];

    /**
     * @param string $namespace
     * @param string $view_path
     */
    public static function registerNamespace($namespace,$view_path){
        self::$namespaces[$namespace]=$view_path;
    }

    /**
     * @return string[]
     */
    public static function getNamespaces(): array
    {
        return self::$namespaces;
    }

    public static function factory(){
        return BladeView::instance()->getFactory();
    }
}