<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\InvalidArgumentException;

/**
 * 字符串处处理
 */
class Str extends Component
{
    /**
     * @var int 大小写字母
     */
    const LETTER = 1;
    /**
     * @var int 小写字母
     */
    const LOWER = 2;
    /**
     * @var int 大写字母
     */
    const UPPER = 4;
    /**
     * @var int 数字
     */
    const NUMERIC = 8;
    /**
     * @var int 字母与数字
     */
    const ALPHANUMERIC = 16;
    /**
     * @var int 字母与数字，排除容易混淆的字符**oOLl*和数字*01*
     */
    const EXTENDED = 32;
    /**
     * @var int 排除字母和数字外的ascii码为33-126的特殊字符
     */
    const SPECIALCHARS = 64;
    /**
     * @var int 随机字符串模式xor的最大值
     */
    const MAX_XOR = 127;
    /**
     * @var array 随机字符串模式对应字符
     */
    const RANDOM_CHARS = [
        self::LETTER => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LOWER => 'abcdefghijklmnopqrstuvwxyz',
        self::UPPER => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::NUMERIC => '0123456789',
        self::ALPHANUMERIC => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        self::EXTENDED => 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789',
        self::SPECIALCHARS => '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~',
    ];
    /**
     * @var array 字符串处理缓存
     */
    protected static $caches = [];

    /**
     * 字符串中包含指定字符？
     *
     * 区分大小写
     *
     * @param string $string 原始字符串
     * @param array $contains 期望包含的字符串
     * @param string $logic 是包含任意一个还是多个，**|*时任意一个，否则多个
     * @return bool
     */
    public static function has($string, array $contains, $logic = '&')
    {
        $result = false;
        foreach ($contains as $item) {
            $result = false !== strpos($string, $item);

            if ($result && '|' === $logic) {
                return true;
            }
            if (!$result && '|' !== $logic) {
                return false;
            }
        }
        return $result;
    }

    /**
     * 字符串以指定字符开始？
     *
     * 区分大小写
     *
     * @param string $string 原始字符串
     * @param string|array $starts 开始的字符串，如果是一个**array**，表示只要以其中任意一个开始即成立
     * @return bool
     */
    public static function startWith($string, $starts)
    {
        foreach ((array) $starts as $item) {
            if (0 === strpos($string, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 字符串以指定字符结尾？
     *
     * 区分大小写
     *
     * @param string $string 原始字符串
     * @param string|array $ends 结尾的字符串，如果是一个**array**，表示只要以其中任意一个结尾即成立
     * @return bool
     */
    public static function endWith($string, $ends)
    {
        foreach ((array) $ends as $item) {
            if ($item === substr($string, -strlen($item))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 随机产生指定长度的字符串
     *
     * @param int $len 生成字符串的长度
     * @param int $mode 字符串模式
     *
     * - `static::LETTER`
     * - `static::UPPER`
     * - `static::LOWER`
     * - `static::NUMERIC`
     * - `static::ALPHANUMERIC`
     * - `static::EXTENDED`
     * - `static::SPECIALCHARS`
     *
     * @param string $addChars 额外添加的字符
     * @return string
     */
    public static function random($len = 4, $mode = null, $addChars = '')
    {
        $randomChars = static::getRandomChars($mode) . $addChars;
        $randomChars = str_shuffle($randomChars);
        return substr($randomChars, 0, $len);
    }

    /**
     * 获取指定模式的随机字符
     *
     * @param int $mode 字符串模式
     *
     * - `static::LETTER`
     * - `static::UPPER`
     * - `static::LOWER`
     * - `static::NUMERIC`
     * - `static::ALPHANUMERIC`
     * - `static::EXTENDED`
     * - `static::SPECIALCHARS`
     *
     * @return string
     * @throws InvalidArgumentException `$mode`不支持时
     */
    public static function getRandomChars($mode = null)
    {
        if (null === $mode) {
            $mode = static::EXTENDED;
        }

        if (null !== Arr::get(static::RANDOM_CHARS, $mode)) {
            return static::RANDOM_CHARS[$mode];
        }
        if ($mode > 0 && $mode <= static::MAX_XOR) {
            $randomChars = '';
            $loop = [
                static::LETTER,
                static::LOWER,
                static::UPPER,
                static::NUMERIC,
                static::ALPHANUMERIC,
                static::EXTENDED,
                static::SPECIALCHARS,
            ];
            foreach ($loop as $item) {
                if ($item === ($item & $mode)) {
                    $randomChars .= static::RANDOM_CHARS[$item];
                }
            }
            return $randomChars;
        }
        throw new InvalidArgumentException('不支持的字符串模式: ' . $mode);
    }

    /**
     * 将字符串转化为首字母小写的驼峰式风格
     *
     * **abc-def-ghi** -> **abcDefGhi**
     *
     * @param string $string 需要转化的字符串
     * @param string $separator `$string`的分隔符
     * @return string
     */
    public static function toLowerCamelCase($string, $separator = '-')
    {
        if (!isset(static::$caches['toLowerCamelCase'][$string])) {
            static::$caches['toLowerCamelCase'][$string] = lcfirst(
                static::toUpperCamelCase($string, $separator)
            );
        }

        return static::$caches['toLowerCamelCase'][$string];
    }

    /**
     * 将字符串转化为首字母大写的驼峰式风格
     *
     * **abc-def-ghi** -> **AbcDefGhi**
     *
     * @param string $string 需要转化的字符串
     * @param string $separator `$string`的分隔符
     * @return string
     */
    public static function toUpperCamelCase($string, $separator = '-')
    {
        $key = $string;
        if (!isset(static::$caches['toUpperCamelCase'][$key])) {
            $string = str_replace($separator, ' ', $string);   // abc def ghi
            $string = ucwords($string);                 // Abc Def Ghi
            $string = str_replace(' ', '', $string);    // AbcDefGhi
            static::$caches['toUpperCamelCase'][$key] = $string;
        }

        return static::$caches['toUpperCamelCase'][$key];
    }

    /**
     * 将驼峰式风格的字符串还原为用指定字符连接的小写字符串
     *
     * **AbcDefGhi** -> **abc-def-ghi**
     *
     * @param string $string
     * @param string $separator 转化后字符串的分隔符
     * @return string
     */
    public static function revertCamelCase($string, $separator = '-')
    {
        $key = $string . $separator;
        if (isset(static::$caches['revertCamelCase'][$key])) {
            return static::$caches['revertCamelCase'][$key];
        }

        $string = preg_replace_callback(
            '/[A-Z]/',
            function ($matches) use ($separator) {
                return $separator . strtolower($matches[0]);
            },
            $string
        );
        $string = ltrim($string, $separator);
        return static::$caches['revertCamelCase'][$key] = $string;
    }

    /**
     * 截取字符串
     *
     * 该方法与`mb_strcut`一样，但该方法实现的是`mb_strcut($string, $length, 'utf-8')`
     *
     * - 截取的开始位置从**0**开始
     * - 先判断`$string`长度是否超出了`$length`，不超出时直接返回原始字符串
     * - 支持截取后追加字符串
     *
     * 如果你需要指定截取的开始位置，请使用`mb_strcut`函数
     *
     * @param string $string 待截取的字符串
     * @param int $length 截取字节数，不包括`$append`的长度
     * @param string $append 截取后追加在截取词后面的字符串
     * @return string
     */
    public static function substr($string, $length, $append = '')
    {
        if (!isset($string{$length})) {
            return $string;
        }
        return mb_strcut($string, 0, $length, 'utf-8') . $append;
    }

    /**
     * 格式化字符串
     *
     * @param string $string 需要格式化的字符串，需要替换的内容使用**{key}**格式
     * @param array $replacePairs 将`$string`中**{key}**替换的键值对
     * @param string $ldelim **{key}**左界定符
     * @param string $rdelim **{key}**右界定符
     * @return string
     */
    public static function format($string, array $replacePairs, $ldelim = '{', $rdelim = '}')
    {
        if ($replacePairs) {
            $replace = [];
            foreach ($replacePairs as $key => $value) {
                $replace[$ldelim . $key . $rdelim] = $value;
            }
            return strtr($string, $replace);
        }
        return $string;
    }

    /**
     * 将名称转化为url
     *
     * ```php
     *  ### 默认只保留字母和数字
     *  Arr::name2url('About Us'); // about-us
     *  Arr::name2url('android 3.5'); // android-3-5
     *
     *  ### 首字母大写
     *  Arr::name2url('about us'); // About-Us
     *
     *  ### 同时保留"."
     *  Arr::name2url('About Us'); // about-us
     *  Arr::name2url('android 3.5', '.'); // android-3.5
     * ```
     *
     * @param string $name 待转化的字符
     * @param string $extra 额外保留的字符
     * @param bool $ucwords 首字母大写？
     * @return string
     */
    public static function name2url($name, $extra = '', $ucwords = false)
    {
        $key = $name . $extra;
        if (isset(static::$caches['name2url'][$key])) {
            return $ucwords ? ucwords(static::$caches['name2url'][$key]) : static::$caches['name2url'][$key];
        }

        $name = strtolower($name);
        preg_match_all("/[0-9a-zA-Z{$extra}]{1,}/", $name, $match);
        static::$caches['name2url'][$key] = implode('-', $match[0]);

        return $ucwords ? ucwords(static::$caches['name2url'][$key]) : static::$caches['name2url'][$key];
    }
}
