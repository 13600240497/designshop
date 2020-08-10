<?php
namespace ego\helpers;

/**
 * 数组组件
 */
class Arr extends \Globalegrow\Base\Arr
{
    /**
     * 批量删除数组多个键名，键名支持使用"."语法
     *
     * @param array $array 待删除的数组
     * @param array|string $keys 需要删除的键名，类型为数组或使用","分隔的字符串
     * @param bool $allowDotNotation  持"."操作？
     * @return array 删除后的数组
     */
    public function delete(array &$array, $keys, $allowDotNotation = true)
    {
        $keys = static::trim($keys);
        $original =& $array;

        foreach ($keys as $key) {
            if (!$allowDotNotation) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                }
            }
            unset($array[array_shift($parts)]);
            // clean up after each pass
            $array =& $original;
        }

        return $array;
    }

    /**
     * 返回一个值全部为整数的数组
     *
     * @param array|string $array 待转换的字符串或数组
     * @param string|null $join **null**时返回数组，否则返回一个用`$join`分隔的字符串
     * @param mixed $exclude 排除值，支持以下类型：
     *
     * - **null**：不排除任何值
     * - `Closure`：一个自定义排除的匿名函数
     * - 其它：排除值（数组或','分隔的字符串）
     *
     * @return array|string 如果`$join`为**true**，返回数组，否则返回用','分隔的字符串
     */
    public function toint($array, $join = ',', $exclude = '< 0')
    {
        $array = array_map('intval', static::trim($array));

        if (null !== $exclude) {
            if ('< 0' === $exclude) { // 排除小于0的值
                $array = array_filter($array, function($v) {
                    return $v > 0;
                });
            } elseif ($exclude instanceof \Closure) {
                // 闭包，自定义排除函数
                $array = array_filter($array, $exclude);
            } else {
                $exclude    = static::trim($exclude);
                $array      = array_diff($array, $exclude);
            }
        }

        if (null === $join) {
            return $array;
        }
        return implode($join, $array);
    }
}
