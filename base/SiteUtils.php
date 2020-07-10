<?php
namespace app\base;

/**
 * 站点公共方法
 *
 * @since v1.4.0
 */
class SiteUtils
{
    /**
     * 判断当前请求是否为GB站点的广告落地页面模块
     *
     * @return boolean true 是; false 否
     */
    public static function isGbAdvertisementModule()
    {
        return self::isInModule(SiteConstants::MODULE_NAME_GB_ADVERTISEMENT);
    }

    /**
     * 判断当前请求是否为某个模块
     *
     * @param string $name 模块名称
     * @return boolean true 是; false 否
     */
    private static function isInModule($name)
    {
        return ($name == app()->controller->module->id);
    }
}