<?php


namespace Tp3LaraBlade\Library;


use Illuminate\View\Compilers\BladeCompiler as BaseCompiler;

class BladeCompiler extends BaseCompiler
{
    protected $echoFormat = 'v(%s)';


    /**
     * Set the "echo" format to double encode entities.
     *
     * @return void
     */
    public function withDoubleEncoding()
    {
        $this->setEchoFormat('v(%s, true)');
    }

    /**
     * Set the "echo" format to not double encode entities.
     *
     * @return void
     */
    public function withoutDoubleEncoding()
    {
        $this->setEchoFormat('v(%s, false)');
    }

    public function setCachePath($path){
        $this->cachePath=$path;
    }
}