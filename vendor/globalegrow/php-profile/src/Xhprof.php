<?php
namespace Globalegrow\PhpProfile;

/**
 * xhprof扩展性能分析
 *
 * - 适用php7版本
 * - 需要安装[xhprof](https://github.com/longxinH/xhprof)扩展
 */
class Xhprof extends AbstractXhprof
{
    /**
     * @inheritdoc
     */
    public function enable()
    {
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    }

    public function disable()
    {
        return xhprof_disable();
    }
}
