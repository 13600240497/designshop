<?php
namespace ego\enums;

/**
 * 平台枚举常量
 */
class Platform extends AbstractEnum
{
    /**
     * @var int pc
     */
    const PC = 1;
    /**
     * @var int wap
     */
    const WAP = 2;
    /**
     * @var int ios
     */
    const IOS = 3;
    /**
     * @var int ANDROID
     */
    const ANDROID = 4;
    /**
     * @var int PAD
     */
    const PAD = 5;
    /**
     * @var array 平台对应关系
     */
    public static $platforms = [
        self::PC => 'PC',
        self::WAP => 'Wap',
        self::IOS => 'IOS',
        self::ANDROID => 'Android',
        self::PAD => 'Pad',
    ];

    /**
     * 获取平台名称
     *
     * @param int $id
     * @return string
     */
    public static function getName($id)
    {
        return static::$platforms[$id] ?? 'unknown';
    }

    /**
     * 获取由","分隔的平台id对应的名称
     *
     * @param string|array $ids
     * @param string|null $join
     * @return array|string
     */
    public static function getNames($ids, $join = '、')
    {
        $platforms = app()->helper->arr->toint($ids, null);
        foreach ($platforms as &$value) {
            $value = static::getName($value);
        }

        if (null !== $join) {
            return join($join, $platforms);
        } else {
            return $platforms;
        }
    }
}
