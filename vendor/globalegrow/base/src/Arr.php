<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\InvalidArgumentException;

/**
 * 数组组件
 */
class Arr extends Component
{
    /**
     * 递归数组合并
     *
     * @param array|bool $argA 参数a，当是一个**bool**值时，表示是否保留数字键名
     * @param array[] $argN 参数N
     * @return array
     */
    public static function merge($argA, array ...$argN)
    {
        if (is_bool($argA)) {
            $preserveNumericKeys = $argA;
        } else {
            $preserveNumericKeys = false;
            array_unshift($argN, $argA);
        }

        $result = array_shift($argN);
        while (!empty($argN)) {
            $array = array_shift($argN);
            foreach ($array as $key => $value) {
                if (!array_key_exists($key, $result)) {
                    $result[$key] = $value;
                } elseif (!$preserveNumericKeys && is_int($key)) {
                    $result[] = $value;
                } elseif (is_array($value) && is_array($result[$key])) {
                    $result[$key] = static::merge(
                        $preserveNumericKeys,
                        $result[$key],
                        $value
                    );
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 获取数组指定键值，键名支持使用"."语法
     *
     * @param array $array 数组
     * @param mixed $key 键名，获取子数组键值时以"."分隔，**null**时返回`$array`
     * @param mixed $default 键名未定义时返回的默认值
     * @return mixed 键值
     */
    public static function get(array $array, $key = null, $default = null)
    {
        if (null === $key) {
            return $array;
        }
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $item) {
            if (is_array($array) && array_key_exists($item, $array)) {
                $array = $array[$item];
            } else {
                return $default;
            }
        }

        return $array;
    }


    /**
     * 设置数组指定键名的值，键名支持使用"."语法
     *
     * @param array $array 需要操作的数组
     * @param string $key 键名，设置子数组键值时以"."分隔
     * @param mixed $value 新值
     * @return array 设置后的数组
     */
    public static function set(array &$array, $key, $value)
    {
        if (null === $key) {
            return $array = $value;
        }
        if (array_key_exists($key, $array)) {
            $array[$key] = $value;
            return $array;
        }

        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array =& $array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 获取数组第一个元素
     *
     * @param array $array
     * @return mixed
     */
    public static function first(array $array)
    {
        return reset($array);
    }

    /**
     * 获取数组最后一个元素
     *
     * @param array $array
     * @return mixed
     */
    public static function last(array $array)
    {
        return end($array);
    }

    /**
     * 实现`array_map('trim', $array)`
     *
     * @param array|string $data 需要操作的数组或者使用`$separator`分隔的字符串
     * @param string $separator 当`$data`是一个字符串时的分隔符
     * @return array
     */
    public static function trim($data, $separator = ',')
    {
        if (is_array($data)) {
            return static::trimRecursive($data);
        }
        return array_map('trim', explode($separator, $data));
    }

    /**
     * 递归的`trim`数组
     *
     * @param array $data 需要操作的数组
     * @param string|null $charlist
     * @return array
     */
    public static function trimRecursive(array $data, $charlist = null)
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                $item = static::trimRecursive($item, $charlist);
            } elseif (null === $charlist) {
                $item = trim($item);
            } else {
                $item = trim($item, $charlist);
            }
        }
        return $data;
    }

    /**
     * 将多唯数组转化成一唯数组
     *
     * @param array $array 待转化数组
     * @return array 转化后的数组，数组的索引为数字，从0开始
     */
    public static function flatten(array $array)
    {
        $return = [];
        array_walk_recursive($array, function ($item) use (&$return) {
            $return[] = $item;
        });

        return $return;
    }

    /**
     * 返回一个不包含指定的键名的数组，即排除指定键名
     *
     * @param array $array 需要返回的数组
     * @param array|string $keys 排除的键名，字符串时使用","分隔
     * @return array
     */
    public static function exclude(array $array, $keys)
    {
        $keys = static::trim($keys);
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * 返回一个只包含指定键名的数组
     *
     * @param array $array 需要返回的数组
     * @param array|string $keys 返回的键名，字符串时使用","分隔
     * @return array
     */
    public static function pick(array $array, $keys)
    {
        $keys = static::trim($keys);
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * 重命名数组键名
     *
     * @param array $array 原数组
     * @param array $keys 一个格式为`原key => 新key`的数组
     * @return array
     */
    public static function rename(array $array, array $keys)
    {
        foreach ($keys as $old => $new) {
            $array[$new] = $array[$old];
            unset($array[$old]);
        }
        return $array;
    }

    /**
     * 将一个值递归的转化为数组
     *
     * @param array|ArrayAccess $data 需要转化的数组
     * @return array 转化后的数组
     * @throws InvalidArgumentException `$data`参数不是一个数组或者一个`ArrayAccess`时
     */
    public static function toArray($data)
    {
        if (is_array($data)) {
            foreach ($data as &$item) {
                if ($item instanceof ArrayAccess) {
                    $item = $item->toArray();
                }
            }
            return $data;
        }
        if ($data instanceof ArrayAccess) {
            return $data->toArray();
        }

        throw new InvalidArgumentException('不支持的参数: ' . var_export($data, true));
    }

    /**
     * 将类似redis返回的数组数据转化为键值对数组
     *
     * ['hello', 'world', 'someKey', 'someValue'] -> ['hello' => 'world', 'someKey' => 'someValue']
     *
     * @param array $data
     * @return array
     */
    public static function toAssoc(array $data)
    {
        $result = [];
        $len = count($data);
        for ($i = 0; $i < $len; $i += 2) {
            $result[$data[$i]] = $data[$i + 1];
        }
        return $result;
    }

    /**
     * 反转`toAssoc`
     *
     * ['hello', 'world', 'someKey', 'someValue'] -> ['hello' => 'world', 'someKey' => 'someValue']
     *
     * @param array $data
     * @return array
     */
    public static function revertAssoc(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = $key;
            $result[] = $value;
        }
        return $result;
    }
}
