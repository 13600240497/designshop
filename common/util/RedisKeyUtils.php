<?php
namespace app\common\util;

/**
 *
 *
 * @author Haishen Tian
 */
abstract class RedisKeyUtils
{
    const KEY_PREFIX = 'geshop:';

    public static function getRgKeyPrefix()
    {
        return self::getWebsiteKeyPrefix('rg');
    }

    /**
     * 获取站点 Redis key前缀
     * @param string $websiteCode
     * @return string
     */
    private static function getWebsiteKeyPrefix($websiteCode)
    {
        $key = self::getKeyPrefix();
        return $key . $websiteCode .':';
    }

    /**
     * 获取 Redis key前缀
     * @param string $env YII环境
     * @return string
     */
    private static function getKeyPrefix($env = YII_ENV)
    {
        switch ($env) {
            case 'dev':
                $developer = \ego\base\Application::getDeveloper(); // 开发者名称
                return static::KEY_PREFIX . $env .':' . $developer .':';
            case 'test':
                return static::KEY_PREFIX . $env .':';
            default:
                return static::KEY_PREFIX;
        }
    }
}