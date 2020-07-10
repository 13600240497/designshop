<?php
namespace app\common\util;

use app\common\constants\SiteConstants;
use app\common\base\YiiHelper;

/**
 * 站点相关工具函数
 *
 * @author Haishen Tian
 * @since 1.0
 */
abstract class SiteUtils
{
    /**
     * 是否RG(RoseGal)站点,参考本类 isSite 函数
     *
     * @param string $code 参考 isSite 函数
     * @return bool
     * @see SiteUtils::isSite()
     */
    public static function isRGSite($code)
    {
        return static::isSite(SiteConstants::WEBSITE_CODE_RG, $code);
    }

    /**
     * 是否RW(rosewholesale)站点, 参考本类 isSite 函数
     *
     * @param string $code
     * @return bool
     * @see SiteUtils::isSite()
     */
    public static function isRWSite($code)
    {
        return static::isSite(SiteConstants::WEBSITE_CODE_RW, $code);
    }

    /**
     * 是否ZF(zaful)站点, 参考本类 isSite 函数
     *
     * @param string $code
     * @return bool
     * @see SiteUtils::isSite()
     */
    public static function isZFSite($code)
    {
        return static::isSite(SiteConstants::WEBSITE_CODE_ZF, $code);
    }


    /**
     * 是否DL(dresslily)站点,参考本类 isSite 函数
     *
     * @param string $code
     * @return bool
     * @see SiteUtils::isSite()
     */
    public static function isDLSite($code)
    {
        return static::isSite(SiteConstants::WEBSITE_CODE_DL, $code);
    }

    /**
     * 是否GB(GearBest)站点,参考本类 isSite 函数
     *
     * @param string $code
     * @return bool
     * @see SiteUtils::isSite()
     */
    public static function isGBSite($code)
    {
        return static::isSite(SiteConstants::WEBSITE_CODE_GB, $code);
    }

    /**
     * 根据code判断是否为自动的站点。
     * 注意： code 为空时，默认会取常量SITE_GROUP_CODE的值,SITE_GROUP_CODE 在 bootstrap.php 定义。
     *
     * @param string $websiteCode
     * @param string $code 网站简码或者站点简码, 如： zf/zf-pc/zf-app
     * @return bool
     */
    private static function isSite($websiteCode, $code)
    {
        $cookieWebSiteCode = YiiHelper::getCurrentWebsiteCode() ?? '';
        $_websiteCode = empty($code) ? $cookieWebSiteCode :
            (strpos($code, SiteConstants::SITE_CODE_SEPARATOR) !== false) ?
                self::getWebsiteCodeBySiteCode($code) : $code;
        return $websiteCode == $_websiteCode;
    }

    /**
     * 分隔站点简码
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     *
     * @return array [网站简码， 平台简码]
     */
    public static function splitSiteCode($siteCode)
    {
        return explode(SiteConstants::SITE_CODE_SEPARATOR, $siteCode, 2);
    }

    /**
     * 获取站点简码
     *
     * @param string $websiteCode 网站简码, 如： zf/rw/rg/dl
     * @param string $platformCode 端口简码, 如：pc/wap/app
     * @return string 站点简码
     */
    public static function getSiteCode($websiteCode, $platformCode)
    {
        return $websiteCode . SiteConstants::SITE_CODE_SEPARATOR . $platformCode;
    }

    /**
     * 根据站点简码获取网站简码
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return string 网站简码
     */
    public static function getWebsiteCodeBySiteCode($siteCode)
    {
        return strstr($siteCode, SiteConstants::SITE_CODE_SEPARATOR, true);
    }

    /**
     * 根据站点简码获取端口简码
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return string 端口简码
     */
    public static function getPlatformCodeBySiteCode($siteCode)
    {
        list(, $platformCode) = static::splitSiteCode($siteCode);
        return $platformCode;
    }
}