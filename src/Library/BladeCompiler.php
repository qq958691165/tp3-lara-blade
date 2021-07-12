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

    /**
     * Compile the escaped echo statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1] ? $matches[0] : "<?php echo v({$matches[2]}); ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
}