<?php
namespace Globalegrow\PhpProfile;

/**
 * uprofiler扩展性能分析
 *
 * - 适用php5.6版本
 * - 需要安装[uprofiler](https://github.com/FriendsOfPHP/uprofiler)扩展
 */
class Uprofiler extends AbstractXhprof
{
    /**
     * @inheritdoc
     */
    public function enable()
    {
        uprofiler_enable(UPROFILER_FLAGS_CPU + UPROFILER_FLAGS_MEMORY);
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        return uprofiler_disable();
    }
}
