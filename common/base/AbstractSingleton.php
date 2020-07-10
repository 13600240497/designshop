<?php
namespace app\common\base;

/**
 * 单列对象抽象类
 *
 * @author Haishen Tian
 * @since 1.0
 */
abstract class AbstractSingleton
{
    private static $instance;

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }
}
