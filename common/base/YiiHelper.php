<?php
namespace app\common\base;

/**
 * Yii帮助工具类
 *
 * @author Haishen Tian
 * @since 1.0
 */
abstract class YiiHelper
{
    /**
     * 获取所有YII配置
     *
     * @return mixed 配置引用
     */
    public static function &getAllConfigReference()
    {
        return app()->params;
    }

    /**
     * 获取当前站点
     *
     * @return string
     */
    public static function getCurrentWebsiteCode()
    {
        return defined('SITE_GROUP_CODE') ? SITE_GROUP_CODE : NULL;
    }
}