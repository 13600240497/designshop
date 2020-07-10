<?php
namespace app\common\util;

use app\common\constants\SiteConstants;
use app\common\base\YiiHelper;
use app\common\config\ApplicationConfig;

/**
 * 端口相关工具函数
 *
 * @author Haishen Tian
 * @since 1.0
 */
abstract class PlatformUtils
{
    /**
     * 根据站点简码获取站点平台类型
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return int 大于0，站点平台类型; 0未知类型
     */
    public static function getPlatformTypeBySiteCode($siteCode)
    {
        $platformCode = SiteUtils::getPlatformCodeBySiteCode($siteCode);
        return self::getPlatformTypeByPlatformCode($platformCode);
    }

    /**
     * 根据端口简码获取平台类型
     *
     * @param string $platformCode 端口简码, 如：pc/wap/app
     * @return int 大于0，站点平台类型; 0未知类型
     */
    public static function getPlatformTypeByPlatformCode($platformCode)
    {
        return SiteConstants::PLATFORM_CODE_TO_TYPE_MAPPING[ $platformCode ] ?? SiteConstants::PLATFORM_TYPE_UNKNOWN;
    }

    /**
     * 根据端口类型获取端口简码
     *
     * @param int $platformType 端口类型(SiteConstants::PLATFORM_TYPE_* 开头常量定义)
     * @return string
     */
    public static function getPlatformCodeByPlatformType($platformType)
    {
        $platformType = (int) $platformType;
        $mapping = array_flip(SiteConstants::PLATFORM_CODE_TO_TYPE_MAPPING);
        return $mapping[ $platformType ] ?? null;
    }

    /**
     * 获取站点下对应端口站点简码
     *
     * @param int    $platformType 端口类型(SiteConstants::PLATFORM_TYPE_* 开头常量定义)
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string 成功站点简码，NULL获取失败
     */
    public static function getSiteCodeByPlatformType($platformType, $websiteCode = null)
    {
        if (null == $websiteCode)
            $websiteCode = YiiHelper::getCurrentWebsiteCode();

        if (empty($websiteCode))
            return null;

        $platformType = (int) $platformType;
        $websiteCode = strtolower($websiteCode);
        switch ($platformType) {
            case SiteConstants::PLATFORM_TYPE_PC:
                return self::getPcPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_WAP:
                return self::getWapPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_APP:
                return self::getAppPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_IOS:
                return self::getIosPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_IPAD:
                return self::getIpadPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_ANDROID:
                return self::getAndroidPlatformSiteCode($websiteCode);
                break;
            case SiteConstants::PLATFORM_TYPE_WEB:
                return self::getWebPlatformSiteCode($websiteCode);
                break;
            default:
                return null;
        }
    }

    /**
     * 通过端口简码获取平台名称
     *
     * @param string $platformCode 端口简码, 如：pc/wap/app
     * @return string 平台名称; NULL端口简码无效
     */
    public static function getPlatformNameByCode($platformCode)
    {
        $type = self::getPlatformTypeByPlatformCode($platformCode);
        return SiteConstants::PLATFORM_NAMES[ $type ] ?? null;
    }

    /**
     * 通过端口简码获取平台名称
     *
     * @param int $platformType 平台类型(SiteConstants::PLATFORM_TYPE_* 开头常量定义)
     * @return string 平台名称，NULL平台类型无效
     */
    public static function getPlatformNameByType($platformType)
    {
        return SiteConstants::PLATFORM_NAMES[ $platformType ] ?? null;
    }

    /**
     * 是否为pc平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isPcPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_PC)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为wap平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isWapPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_WAP)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为app平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isAppPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_APP)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为Android平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isAndroidPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_ANDROID)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为IOS平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isIosPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_IOS)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为IPAD平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isIpadPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_IPAD)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为Web平台
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @return boolean true是，false 否
     */
    public static function isWebPlatform($siteCode)
    {
        if (self::isPlatform($siteCode, SiteConstants::PLATFORM_CODE_WEB)) {
            return true;
        }
        return false;
    }

    /**
     * 判断站点简码是否为指定端口
     *
     * @param string $siteCode 站点简码, 如： zf-pc/zf-app
     * @param string $platformCode 端口简码, 如：pc/wap/app
     * @return boolean true是，false 否
     */
    private static function isPlatform($siteCode, $platformCode)
    {
        if (StringUtils::endsWith($siteCode, SiteConstants::SITE_CODE_SEPARATOR . $platformCode)) {
            return true;
        }
        return false;
    }

    /**
     * 获取PC平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getPcPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_PC, $websiteCode);
    }

    /**
     * 获取Wap平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getWapPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_WAP, $websiteCode);
    }

    /**
     * 获取App平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getAppPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_APP, $websiteCode);
    }

    /**
     * 获取Ios平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getIosPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_IOS, $websiteCode);
    }

    /**
     * 获取ipad平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getIpadPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_IPAD, $websiteCode);
    }

    /**
     * 获取Android平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getAndroidPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_ANDROID, $websiteCode);
    }

    /**
     * 获取响应式Web平台的站点简码(site_code),参考 getPlatformSiteCode 函数
     *
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string
     * @see PlatformUtils::getPlatformSiteCode()
     */
    public static function getWebPlatformSiteCode($websiteCode = null)
    {
        return self::getPlatformSiteCode(SiteConstants::PLATFORM_CODE_WEB, $websiteCode);
    }

    /**
     * 获取平台的站点简码(site_code)
     *
     * @param string $platformCode 端口简码, 如：pc/wap/app
     * @param string $websiteCode 网站简码,默认null(读取当前操作网站简码)。如： zf/rw/rg/dl
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    private static function getPlatformSiteCode($platformCode, $websiteCode = null)
    {
        if (null == $websiteCode)
            $websiteCode = YiiHelper::getCurrentWebsiteCode();

        if (!empty($websiteCode)) {
            $applicationConfig = ApplicationConfig::getInstance();
            $siteCode = SiteUtils::getSiteCode($websiteCode, $platformCode);
            if ($applicationConfig->siteExists($siteCode)) {
                return $siteCode;
            }
        }

        return null;
    }
}