<?php
namespace Globalegrow\Base;

/**
 * 调试组件
 *
 * 在开发过程中，对PHP变量或其它信息（如SQL）进行调试是必不可少的。
 *
 * 调试信息通常直接输出到浏览器上，在非生产环境没有任何问题，
 * 但发布到生产环境后，这是不允许的
 *
 * 调试组件在开发环境下输出信息，其它环境下什么也不输出
 */
class Debug extends Component
{
    /**
     * @var bool 调试？
     */
    public $isDebug;

    /**
     * `var_dump`
     *
     * @param array ...$args
     * @return $this
     */
    public function dump(...$args)
    {
        return $this->debug('dump', $args);
    }

    /**
     * `var_export`
     *
     * @param array ...$args
     * @return $this
     */
    public function export(...$args)
    {
        return $this->debug('export', $args);
    }

    /**
     * `exit`
     * @return $this
     */
    public function end()
    {
        return $this->debug('end', [1]);
    }

    /**
     * 调试
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    protected function debug($method, array $args = [])
    {
        if (!$this->isDebug) {
            return $this;
        }
        echo '<pre>';

        // 调用所在文件和所在行数
        $calledBacktrace = Helper::getCalledBacktrace(2);
        $fileLine = $calledBacktrace->file . ' : ' . $calledBacktrace->line;
        echo $fileLine, "\n", str_repeat('-', strlen($fileLine)), "\n";

        foreach ($args as $value) {
            $this->{$method . 'Internal'}($value);
        }

        echo '</pre>';
        return $this;
    }

    /**
     * `var_dump`
     *
     * @param mixed $value
     */
    protected function dumpInternal($value)
    {
        var_dump($value);
    }

    /**
     * `var_export`
     *
     * @param mixed $value
     */
    protected function exportInternal($value)
    {
        var_export($value);
    }

    /**
     * `exit`
     */
    protected function endInternal()
    {
        exit();
    }
}
