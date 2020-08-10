<?php
namespace Globalegrow\PhpProfile;

/**
 * 抽象xhprof
 */
abstract class AbstractXhprof
{
    /**
     * 开启
     */
    abstract public function enable();

    /**
     * 结束
     *
     * @return array
     */
    abstract public function disable();

    /**
     * 是否可用?
     *
     * @return bool
     */
    public function isEnable()
    {
        return extension_loaded(substr(
            static::class,
            strrpos(static::class, '\\') + 1
        ));
    }
}
