<?php
namespace ego\enums;

use ReflectionClass;

/**
 * 可枚举的常量类
 */
class AbstractEnum
{
     /*
     * @var array 键为常量名，值为常量值的数组
     */
    private static $constants = [];
    /*
    * @var array 键为常量值，值为常量名的数组
    */
    private static $flipConstants = [];

    /**
     * 获取所有定义的常量
     *
     * @param bool $flip
     * @return array
     */
    public static function getAll($flip = false)
    {
        $class = static::class;
        if (!isset(self::$constants[$class])) {
            self::$constants[$class] = (new ReflectionClass($class))->getConstants();
        }

        if (!$flip) {
            return self::$constants[$class];
        } elseif (!isset(self::$flipConstants[$class])) {
            $constants = array_filter(self::$constants[$class], function($value) {
                return is_scalar($value);
            });
            self::$flipConstants[$class] = array_flip($constants);
        }
        return self::$flipConstants[$class];
    }
}
