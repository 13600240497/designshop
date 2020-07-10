<?php

namespace app\base;

/**
 * 站点组(PC/M/APP三端合一) 公用方法
 *
 * @since v1.4.0
 */
class SitePlatform
{
    /** @var string pc平台 */
    const PLATFORM_CODE_PC = 'pc';
    /** @var string wap平台 */
    const PLATFORM_CODE_WAP = 'wap';
    /** @var string app平台 */
    const PLATFORM_CODE_APP = 'app';
    /** @var string ios平台 */
    const PLATFORM_CODE_IOS = 'ios';
    /** @var string ipad平台 */
    const PLATFORM_CODE_IPAD = 'ipad';
    /** @var string ANDROID 平台 */
    const PLATFORM_CODE_ANDROID = 'android';
    /** @var string Web 平台(自适应平台，适配PC、平板、手机) */
    const PLATFORM_CODE_WEB = 'web';

    /** @var string 站点简码分隔符 */
    const SITE_CODE_SEPARATOR = '-';

    /** @var int 平台类型 - 未知 */
    const PLATFORM_TYPE_UNKNOWN = 0;
    /** @var int 平台类型 - PC */
    const PLATFORM_TYPE_PC = 1;
    /** @var int 平台类型 - Wap */
    const PLATFORM_TYPE_WAP = 2;
    /** @var int 平台类型 - App */
    const PLATFORM_TYPE_APP = 3;
    /** @var int 平台类型 - ios */
    const PLATFORM_TYPE_IOS = 4;
    /** @var int 平台类型 - App */
    const PLATFORM_TYPE_IPAD = 5;
    /** @var int 平台类型 - ANDROID */
    const PLATFORM_TYPE_ANDROID = 6;
    /** @var int 平台类型 - Web 平台(自适应平台，适配PC、平板、手机) */
    const PLATFORM_TYPE_WEB = 7;


    /** @var array 平台显示名称 */
    const PLATFORM_NAMES = [
        self::PLATFORM_TYPE_PC      => 'PC',
        self::PLATFORM_TYPE_WAP     => 'M',
        self::PLATFORM_TYPE_APP     => 'APP',
        self::PLATFORM_TYPE_IOS     => 'IOS',
        self::PLATFORM_TYPE_IPAD    => 'IPAD',
        self::PLATFORM_TYPE_ANDROID => 'Android',
        self::PLATFORM_TYPE_WEB     => 'Web',
    ];

    /** @var array 平台类型to平台简码map */
    const PLATFORM_CODE_TO_TYPE_MAP = [
        self::PLATFORM_CODE_PC      => self::PLATFORM_TYPE_PC,
        self::PLATFORM_CODE_WAP     => self::PLATFORM_TYPE_WAP,
        self::PLATFORM_CODE_APP     => self::PLATFORM_TYPE_APP,
        self::PLATFORM_CODE_IOS     => self::PLATFORM_TYPE_IOS,
        self::PLATFORM_CODE_IPAD    => self::PLATFORM_TYPE_IPAD,
        self::PLATFORM_CODE_ANDROID => self::PLATFORM_TYPE_ANDROID,
        self::PLATFORM_CODE_WEB     => self::PLATFORM_TYPE_WEB,
    ];


    /**
     * 根据站点简码获取站点平台类型
     *
     * @param string $siteCode 站点简码
     *
     * @return int 大于0，站点平台类型; 0未知类型
     */
    public static function getPlatformTypeBySiteCode($siteCode)
    {
        $platform = self::splitSiteCode($siteCode)[1];

        return self::getPlatformTypeByPlatformCode($platform);

    }

    /**
     * 根据平台简码获取平台类型
     *
     * @param string $platformCode 平台简码
     *
     * @return int 大于0，站点平台类型; 0未知类型
     */
    public static function getPlatformTypeByPlatformCode($platformCode)
    {
        return self::PLATFORM_CODE_TO_TYPE_MAP[ $platformCode ] ?? self::PLATFORM_TYPE_UNKNOWN;
    }

    /**
     * 获取站点下的平台站点简码
     *
     * @param int    $platformType  平台类型(SitePlatform::PLATFORM_TYPE_* 开头常量定义)
     * @param string $siteGroupCode 站点组简码，默认NULL（从cookie中获取）
     *
     * @return string 成功站点简码，NULL获取失败
     */
    public static function getSiteCodeByPlatformType($platformType, $siteGroupCode = null)
    {
        if (null == $siteGroupCode)
            $siteGroupCode = self::getCurrentSiteGroupCode();

        if (empty($siteGroupCode))
            return null;

        $platformType = (int) $platformType;
        $siteGroupCode = strtolower($siteGroupCode);
        switch ($platformType) {
            case self::PLATFORM_TYPE_PC:
                return self::getPcPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_WAP:
                return self::getWapPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_APP:
                return self::getAppPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_IOS:
                return self::getIosPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_IPAD:
                return self::getIpadPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_ANDROID:
                return self::getAndroidPlatformSiteCode($siteGroupCode);
                break;
            case self::PLATFORM_TYPE_WEB:
                return self::getWebPlatformSiteCode($siteGroupCode);
                break;
            default:
                return null;
        }
    }

    /**
     * 根据平台类型获取平台简码
     *
     * @param int $platformType
     *
     * @return string
     */
    public static function getPlatformCodeByPlatformType($platformType)
    {
        $platformType = (int) $platformType;
        $map = array_flip(self::PLATFORM_CODE_TO_TYPE_MAP);

        return $map[ $platformType ] ?? null;
    }

    /**
     * 获取站点下的平台站点简码
     *
     * @param string $platformCode  平台简码
     * @param string $siteGroupCode 站点组简码，默认NULL（从cookie中获取）
     *
     * @return string 成功站点简码，NULL获取失败
     */
    public static function getSiteCodeByPlatformCode($platformCode, $siteGroupCode = null)
    {
        return self::getPlatformSiteCode($platformCode, $siteGroupCode);
    }


    /**
     * 通过平台简码获取平台名称
     *
     * @param string $platformCode 平台简码
     *
     * @return string 平台名称; NULL平台简码无效
     */
    public static function getPlatformNameByCode($platformCode)
    {
        $type = self::getPlatformTypeByPlatformCode($platformCode);

        return self::PLATFORM_NAMES[ $type ] ?? null;
    }

    /**
     * 通过平台简码获取平台名称
     *
     * @param int $platformType 平台类型(SitePlatform::PLATFORM_TYPE_* 开头常量定义)
     *
     * @return string 平台名称，NULL平台类型无效
     */
    public static function getPlatformNameByType($platformType)
    {
        return self::PLATFORM_NAMES[ $platformType ] ?? null;
    }

    /**
     * 获取所有平台类型
     *
     * @return array 平台类型列表
     */
    public static function getAllPlatformTypes()
    {
        return [self::PLATFORM_TYPE_PC, self::PLATFORM_TYPE_WAP, self::PLATFORM_TYPE_APP];
    }

    /**
     * 判断指定平台类型是否有效
     *
     * @param int $platformType 平台类型(SitePlatform::PLATFORM_TYPE_* 开头常量定义)
     *
     * @return boolean true有效；false无效
     */
    public static function isValidPlatformType($platformType)
    {
        $type = (int) $platformType;
        if (in_array($type, self::getAllPlatformTypes(), true))
            return true;

        return false;
    }

    /**
     * 获取PC平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getPcPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_PC, $siteGroupCode);
    }

    /**
     * 获取Wap平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getWapPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_WAP, $siteGroupCode);
    }

    /**
     * 获取App平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getAppPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_APP, $siteGroupCode);
    }

    /**
     * 获取Ios平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getIosPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_IOS, $siteGroupCode);
    }

    /**
     * 获取ipad平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getIpadPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_IPAD, $siteGroupCode);
    }

    /**
     * 获取Android平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getAndroidPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_ANDROID, $siteGroupCode);
    }

    /**
     * 获取响应式Web平台的站点简码(site_code)
     *
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getWebPlatformSiteCode($siteGroupCode = null)
    {
        return self::getPlatformSiteCode(self::PLATFORM_CODE_WEB, $siteGroupCode);
    }

    /**
     * 获取平台的站点简码(site_code)
     *
     * @param string $platformCode  平台简码
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return string 站点简码(site_code); NULL 没有配置站点或无效的站点组简码
     */
    public static function getPlatformSiteCode($platformCode, $siteGroupCode = null)
    {
        if (null == $siteGroupCode)
            $siteGroupCode = self::getCurrentSiteGroupCode();

        if (!empty($siteGroupCode)) {
            $siteCode = $siteGroupCode . self::SITE_CODE_SEPARATOR . $platformCode;
            if (self::siteExists($siteCode)) {
                return $siteCode;
            }
        }

        return null;
    }

    /**
     * 是否为pc平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isPcPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_PC)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为wap平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isWapPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_WAP)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为app平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isAppPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_APP)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为Android平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isAndroidPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_ANDROID)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为IOS平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isIosPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_IOS)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为IPAD平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isIpadPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_IPAD)) {
            return true;
        }

        return false;
    }

    /**
     * 是否为Web平台
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean true是，false 否
     */
    public static function isWebPlatform($siteCode)
    {
        if (self::endsWith($siteCode, self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_WEB)) {
            return true;
        }

        return false;
    }

    /**
     * 获取所有支持的设备
     *
     * @return array 所有平台简码
     */
    public static function getAllSupportPlatforms()
    {
        return [self::PLATFORM_CODE_PC, self::PLATFORM_CODE_WAP, self::PLATFORM_CODE_APP];
    }


    /**
     * 获取所有支持的设备(GB)
     *
     * @return array 所有平台简码
     */
    public static function getGbSupportPlatforms()
    {
        return [self::PLATFORM_CODE_PC, self::PLATFORM_CODE_WAP, self::PLATFORM_CODE_IOS, self::PLATFORM_CODE_ANDROID];
    }

    /**
     * 获取所有支持的设备(DL)
     * @return array 所有平台简码
     */
    public static function getDLSupportPlatforms()
    {
        return [self::PLATFORM_CODE_WEB, self::PLATFORM_CODE_APP];
    }

    /**
     * 判断指定站点是否是当前选择站点的平台子站点
     *
     * @param string $siteCode      站点简码
     * @param string $siteGroupCode 站点组简码,默认null(从cookie中获取)
     *
     * @return boolean
     */
    public static function isCurrentSiteGroupPlatformSite($siteCode, $siteGroupCode = null)
    {
        if (empty($siteGroupCode))
            $siteGroupCode = self::getCurrentSiteGroupCode();

        if (empty($siteCode) || empty($siteGroupCode))
            return false;


        if (self::siteExists($siteCode) && self::startsWith($siteCode, strtolower($siteGroupCode))) {
            return true;
        }

        return false;
    }

    /**
     * 获取当前站点组简码
     *
     * @see bootstrap.php
     * @return string 成功返回站点组简码,否则返回 NULL
     */
    public static function getCurrentSiteGroupCode()
    {
        return defined('SITE_GROUP_CODE') ? SITE_GROUP_CODE : null;
    }

    /**
     * 获取当前站点组下默认站点简码
     *
     * @return string 站点简码,如果站点有配置，NULL 默认站点没有配置
     */
    public static function getCurrentSiteGroupDefaultSiteCode()
    {
        $siteGroupCode = self::getCurrentSiteGroupCode();

        return self::getSiteGroupDefaultSiteCode($siteGroupCode);
    }

    /**
     * 检查站点是否存在
     *
     * @param string $siteCode 站点简码
     *
     * @return boolean 存在 true， 不存在 false
     */
    public static function siteExists($siteCode)
    {
        if (isset(app()->params['sites'][ $siteCode ]))
            return true;

        return false;
    }

    /**
     * 获取站点专题活动有效的语言key列表
     *
     * @param string $siteCode          站点简码
     * @param array  $checkLanguageKeys 要检查的语言key列表
     *
     * @return array 有效的语言key列表
     */
    public static function getSiteSpecialPageValidLanguageKeys($siteCode, $checkLanguageKeys)
    {
        if (!$siteCode || !is_array($checkLanguageKeys) || empty($checkLanguageKeys)) {
            return [];
        }

        $supportLanguageKeys = self::getSiteSpecialPageSupportLanguageKeys($siteCode);

        return self::getValidLanguageKeys($supportLanguageKeys, $checkLanguageKeys);
    }

    /**
     * 过滤掉不支持的语言Key
     *
     * @param array $supportLanguageKeys 支持的语言key
     * @param array $checkLanguageKeys   要检查的语言key
     *
     * @return array 有效的语言key
     */
    public static function getValidLanguageKeys($supportLanguageKeys, $checkLanguageKeys)
    {
        $validLangKeys = [];
        foreach ($checkLanguageKeys as $languageKey) {
            if (!in_array($languageKey, $supportLanguageKeys, true))
                continue;

            $validLangKeys[] = $languageKey;
        }

        return $validLangKeys;
    }

    /**
     * 根据配置获取站点专题活动支持的语言key列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 语言key列表，例：['en', 'es']
     */
    public static function getSiteSpecialPageSupportLanguageKeys($siteCode)
    {
        $languages = self::getSiteSpecialPageSupportLanguages($siteCode);

        return array_column($languages, 'key');
    }

    /**
     * 根据配置获取站点广告落地页活动支持的语言key列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 语言key列表，例：['en', 'es']
     */
    public static function getSiteHomePageSupportLanguageKeys($siteCode)
    {
        $languages = self::getSiteHomePageSupportLanguages($siteCode);

        return array_column($languages, 'key');
    }

    /**
     * 根据配置获取站点广告落地页活动支持的语言key列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 语言key列表，例：['en', 'es']
     */
    public static function getSiteAdvertisingPageSupportLanguageKeys($siteCode)
    {
        $languages = self::getSiteAdvertisingPageSupportLanguages($siteCode);

        return array_column($languages, 'key');
    }

    /**
     * 根据配置获取站点专题活动支持的语言列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 支持语言列表,或者空数组
     *  - code      语言iso简码
     *  - name      名称
     *  - key       语言key
     *  - url       专题活动URL前缀
     */
    public static function getSiteSpecialPageSupportLanguages($siteCode)
    {
        return self::getSitePageSupportLanguages($siteCode, 'secondary_domain');
    }

    /**
     * 根据配置获取站点首页活动支持的语言列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 支持语言列表,或者空数组
     *  - code      语言iso简码
     *  - name      名称
     *  - key       语言key
     *  - url       首页活动URL前缀
     */
    public static function getSiteHomePageSupportLanguages($siteCode)
    {
        return self::getSitePageSupportLanguages($siteCode, 'home_secondary_domain');
    }

    /**
     * 根据配置获取站点广告落地页支持的语言列表
     *
     * @param string $siteCode 站点简码
     *
     * @return array 支持语言列表,或者空数组
     *  - code      语言iso简码
     *  - name      名称
     *  - key       语言key
     *  - url       首页活动URL前缀
     */
    public static function getSiteAdvertisingPageSupportLanguages($siteCode)
    {
        return self::getSitePageSupportLanguages($siteCode, 'ad_secondary_domain');
    }

    /**
     * 根据配置获取站点活动页面支持的语言列表
     *
     * @param string $siteCode    站点简码
     * @param string $configIndex 站点配置项名称
     *
     * @return array 支持语言列表
     *  - code      语言iso简码
     *  - name      名称
     *  - key       语言key
     *  - url       活动页面URL前缀
     */
    private static function getSitePageSupportLanguages($siteCode, $configIndex)
    {
        if (!self::siteExists($siteCode))
            return [];

        if (isGearbestSite($siteCode) && ('ad_secondary_domain' == $configIndex)) {
            SitePlatform::setGbLanguageNames();
        }
        $configAllLanguages = app()->params['lang'] ?? [];
        $configSiteLanguages = app()->params['sites'][ $siteCode ][ $configIndex ] ?? [];
        if (!is_array($configAllLanguages) || !is_array($configSiteLanguages)
            || empty($configAllLanguages) || empty($configSiteLanguages)
        ) {
            return [];
        }
        $siteLanguageKeys = array_keys($configSiteLanguages);

        $siteSupportLanguages = [];
        foreach ($configAllLanguages as $langKey => $langInfo) {
            if (!\in_array($langKey, $siteLanguageKeys, true))
                continue;

            $langInfo['key'] = $langKey;
            $langInfo['url'] = $configSiteLanguages[ $langKey ];
            $siteSupportLanguages[] = $langInfo;
        }

        return $siteSupportLanguages;
    }

    /**
     * 分隔站点简码
     *
     * @param string $siteCode 站点简码
     *
     * @return array [站点组简码， 平台简码]
     */
    public static function splitSiteCode($siteCode)
    {
        return explode(self::SITE_CODE_SEPARATOR, $siteCode, 2);
    }

    /**
     * 根据站点简码获取站点
     *
     * @param string $siteCode
     *
     * @return mixed
     */
    public static function getSiteBySiteCode(string $siteCode)
    {
        return self::splitSiteCode($siteCode)[0];
    }

    /**
     * 获取站点分组下默认站点简码
     *
     * @param string $siteGroupCode 站点组简码
     *
     * @return string 站点简码,如果站点有配置，NULL 默认站点没有配置
     */
    public static function getSiteGroupDefaultSiteCode($siteGroupCode)
    {
        if ($siteGroupCode) {
            // dresslily站点默认web端
            if (SiteConstants::SITE_GROUP_CODE_DL == $siteGroupCode) {
                $siteCode = strtolower($siteGroupCode) . self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_WEB;
            } else {
                $siteCode = strtolower($siteGroupCode) . self::SITE_CODE_SEPARATOR . self::PLATFORM_CODE_PC;
            }

            if (self::siteExists($siteCode))
                return $siteCode;
        }

        return null;
    }

    /**
     * 根据站点简码获取站点组简码
     *
     * @param string $siteCode 站点简码
     *
     * @return string 站点组简码
     */
    public static function getSiteGroupCodeBySiteCode($siteCode)
    {
        return strstr($siteCode, self::SITE_CODE_SEPARATOR, true);
    }

    /**
     * 根据站点简码获得平台简码
     *
     * @param string $siteCode 站点简码
     *
     * @return string 平台简码, NULL 站点简码无效
     */
    public static function getPlatformCodeBySiteCode($siteCode)
    {
        return self::splitSiteCode($siteCode)[1];
    }

    /**
     * 判断字符串是否已指定字符串结束
     *
     * @param string $text   要检查的字符串
     * @param string $needle 要搜索的字符串
     *
     * @return bool 匹配true；否则false
     */
    private static function endsWith($text, $needle)
    {
        return 0 === substr_compare($text, $needle, -strlen($needle));
    }

    /**
     * 判断字符串是否已指定字符串开始
     *
     * @param string $text   要检查的字符串
     * @param string $needle 要搜索的字符串
     *
     * @return bool 匹配true；否则false
     */
    private static function startsWith($text, $needle)
    {
        return 0 === substr_compare($text, $needle, 0, strlen($needle));
    }

    /**
     * 根据siteCode获取siteGroup
     *
     * @param string $siteCodes
     *
     * @return string
     */
    public static function getSiteGroupsBySiteCodes(string $siteCodes)
    {
        $siteCodeArr = explode(',', $siteCodes);
        if (empty($siteCodes) || empty($siteCodeArr)) {
            return $siteCodes;
        }

        $list = [];
        foreach ($siteCodeArr as $siteCode) {
            $list[] = explode(static::SITE_CODE_SEPARATOR, $siteCode)[0];
        }

        return implode(',', array_unique($list));
    }


    /**
     * 获取可用的渠道列表
     *
     * @return array
     */
    public static function getSiteSpecialPageSupportPipeline()
    {
        $configIndex = 'secondary_domain';
        $supportPlatforms = SitePlatform::getGbSupportPlatforms();
        $list['platform'] = $supportPlatforms;
        foreach ($supportPlatforms as $platformCode) {
            $platformType = self::getPlatformTypeByPlatformCode($platformCode);
            if (self::PLATFORM_TYPE_UNKNOWN == $platformType) {
                continue;
            }

            $siteCode = self::getSiteCodeByPlatformType($platformType);
            if (empty($siteCode)) {
                continue;
            }
            $site = self::getSiteBySiteCode($siteCode);
            $configAllLanguages = app()->params['lang'] ?? [];
            $configAllPipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
            $configSitePipeline = app()->params['sites'][ $siteCode ][ $configIndex ] ?? [];
            if (!is_array($configAllPipeline) || !is_array($configSitePipeline)
                || empty($configAllLanguages) || empty($configSitePipeline)
            ) {
                continue;
            }
            foreach ($configSitePipeline as $key => $row) {
                if (empty($list[ $platformCode ]['pipeline'][ $key ])) {
                    $list[ $platformCode ]['pipeline'][ $key ] = [
                        'key'  => $key,
                        'name' => $configAllPipeline[ $key ] ?? '',
                        'url'  => current($row)
                    ];
                }
                foreach ($row as $k => $item) {
                    if (
                        empty($list[ $platformCode ]['pipeline'][ $key ]['language'])
                        || !in_array($k, $list[ $platformCode ]['pipeline'][ $key ]['language'])
                    ) {
                        $list[ $platformCode ]['pipeline'][ $key ]['language'][ $k ] = [
                            'key'  => $k,
                            'name' => $configAllLanguages[ $k ]['name'] ?? '',
                            'url'  => $item
                        ];
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 获取站点可用的渠道和语言列表
     *
     * @param   string $siteCode 站点简码
     * @param   array  $pipeline 选中的渠道语言数组
     *
     * @return  array
     */
    public static function getSiteSpecialPageValidPipelines($siteCode, $pipeline)
    {
        $pipelineList = [];
        $configIndex = 'secondary_domain';
        if (!$siteCode || !is_array($pipeline) || empty($pipeline) || !self::siteExists($siteCode)) {
            return $pipelineList;
        }
        $configSitePipeline = app()->params['sites'][ $siteCode ][ $configIndex ] ?? [];
        foreach ($pipeline as $key => $item) {
            if (isset($configSitePipeline[ $key ])) {
                foreach ($item as $row) {
                    if (isset($configSitePipeline[ $key ][ $row ])) {
                        $pipelineList[ $key ][] = $row;
                    }
                }
            }
        }

        return $pipelineList;
    }


    /**
     * 获取页面分享信息配置
     *
     * @param  string $siteCode
     *
     * @return array
     */
    public static function getSiteShareDetail($siteCode)
    {
        return app()->params['sites'][ $siteCode ]['share'] ?? [];
    }

    /**
     * 获取页面访问链接参数
     *
     * @param   string $lang
     * @param   string $siteCode
     * @param   string $pipeline
     *
     * @return  string
     */
    public static function getPlatformUrl($lang, $siteCode, $pipeline)
    {
        $params = '';
        $platform = self::getPlatformCodeBySiteCode($siteCode);
        switch ($platform) {
            case 'android':
            case 'ios':
                $params = '?platform=' . $platform;
                if ('en' != $lang && $pipeline == 'GB') {
                    $params .= '&language=' . $lang;
                }
                break;
            default:
                if ('en' != $lang && $pipeline == 'GB') {
                    $params = '?lang=' . $lang;
                }
                break;
        }

        return $params;
    }

    /**
     * 获取GB站点语言名称,临时函数
     */
    public static function setGbLanguageNames()
    {
        //GB站点语言配置
        if (is_file($file = APP_PATH . '/config/gb_web_module_ad.php')) {
            app()->params = \yii\helpers\ArrayHelper::merge(app()->params, require($file));
        }
    }

    /**
     * 根据渠道简码获取渠道名称
     *
     * @param  array  $pipeline
     * @param  string $siteCode
     *
     * @return string
     */
    public static function getPipelineNameByPipelineCode(array $pipeline, $siteCode)
    {
        if (empty($pipeline)) {
            return '';
        }
        $site = self::getSiteBySiteCode($siteCode);
        $configAllPipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
        $string = '';
        foreach ($pipeline as $item) {
            $string .= $configAllPipeline[ $item ] . ',';
        }

        return trim($string, ',');
    }
}
