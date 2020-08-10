<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\InvalidArgumentException;
use Globalegrow\Base\Exceptions\InvalidValueException;

/**
 * 基础助手类
 *
 * 这里的方法，适合所有项目
 */
class Helper extends Component
{
    /**
     * @var array 大小字节对应关系
     */
    const BYTES = [
        'B' => 1,
        'b' => 1,
        'Bytes' => 1,
        'bytes' => 1,
        'K' => 1024,
        'k' => 1024,
        'M' => 1048576,
        'm' => 1048576,
        'G' => 1073741824,
        'g' => 1073741824,
    ];

    /**
     * 获取调用指定方法的回溯跟踪
     *
     * @param int $level 获取跟踪的层级
     * @return ArrayAccess
     * @throws InvalidArgumentException
     * @throws InvalidValueException `$level`层级的回溯未包含**file**和**line**键名时
     */
    public static function getCalledBacktrace($level = 1)
    {
        $calledBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $level + 1);
        $calledBacktrace = new ArrayAccess($calledBacktrace[$level]);
        if (!isset($calledBacktrace->file, $calledBacktrace->line)) {
            ob_start();
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $level + 1);
            throw new InvalidValueException('获取回溯跟踪错误: ' . ob_get_clean());
        }
        return $calledBacktrace;
    }

    /**
     * 指定的值是一个标量或者**null**？
     *
     * @param mixed $value 需要判断的值
     * @return bool
     */
    public static function isScalar($value)
    {
        return is_scalar($value) || null === $value;
    }

    /**
     * 指定的值为空？
     *
     * 以下值视为空：
     *
     * - 空字符串
     * - **null**
     * - 空数组
     *
     * @param mixed $value 需要判断的值
     * @return bool
     */
    public static function isEmpty($value)
    {
        return '' === $value
        || null === $value
        || [] === $value
        || (is_string($value) && '' === trim($value));
    }

    /**
     * 返回一个指定的值，如果值本身是一个匿名函数，则返回调用结果
     *
     * @param mixed $value 指定的值
     * @param array $args 当`$value`是一个`Closure`时传递的调用参数
     * @return mixed
     */
    public static function value($value, array $args = [])
    {
        if ($value instanceof \Closure) {
            return call_user_func_array($value, $args);
        }
        return $value;
    }

    /**
     * 返回带单位的字节大小
     *
     * @param string|int|float $size 字节大小，如果不是一个数字，则说明是一个文件，取该文件大小
     * @param int $precision 小数点精度
     * @return string
     * @throws InvalidArgumentException `$size`为不是一个数字且又不是一个文件时
     */
    public static function formatSize($size, $precision = 2)
    {
        if (!is_numeric($size)) {
            if (is_file($size)) {
                $size = filesize($size);
            } else {
                throw new InvalidArgumentException(sprintf('文件"%s"不存在', $size));
            }
        }
        if ($size < 0) {
            $lt0 = true;
            $size = abs($size);
        }

        if ($size < 1024) {
            $unit = 'Bytes';
        } elseif ($size < 1048576) {
            $size = round($size / 1024 * 100) / 100;
            $unit = 'K';
        } elseif ($size < 1073741824) {
            $size = round($size / 1048576 * 100) / 100 ;
            $unit = 'M';
        } else {
            $size = round($size / 1073741824 * 100) / 100;
            $unit = 'G';
        }

        $result = sprintf('%.' . $precision . 'f', $size) . ' ' . $unit;
        return (isset($lt0) ? '-' : '') . $result;
    }

    /**
     * 将一个带单位的字符串转化为字节数
     *
     * @param string $size 需要转化的字符串
     * @return int
     * @throws InvalidArgumentException `$size`不是一个合法的字符串时
     */
    public static function revertFormatSize($size)
    {
        if (is_numeric($size)) {
            return $size;
        }

        $end = substr($size, -1);
        if (Arr::get(static::BYTES, $end)) {
            $size = trim(substr($size, 0, -1));
        } else {
            // Bytes
            $end = substr($size, 5);
            if ('Bytes' === $end || 'bytes' === $end) {
                $size = trim(substr($size, 0, -5));
            }
        }

        if (is_numeric($size)) {
            return $size * static::BYTES[$end];
        }
        throw new InvalidArgumentException(sprintf('不支持的$size参数"%s"', $size));
    }

    /**
     * 返回一个结果数组
     *
     * @param int $code 错误码
     * @param string $message 提示信息
     * @param mixed $data 数据
     * @return array
     */
    public static function arrayResult($code, $message, $data = null)
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * 返回6位小数点的`microtime()`
     *
     * 直接调用`microtime(true)`，返回的数值小数点只保留4位
     *
     * @return string
     */
    public static function microtime()
    {
        $data = explode(' ', microtime());
        return bcadd($data[1], $data[0], 6);
    }
}
