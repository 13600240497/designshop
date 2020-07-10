<?php
namespace app\common\constants;

/**
 * 站点相关常量
 *
 * @author Haishen Tian
 * @since 1.0
 */
abstract class SiteConstants
{

    //------------ 网站简码 ----------------
    /** @var string RG(RoseGal)站点组简码 */
    const WEBSITE_CODE_RG = 'rg';

    /** @var string RW(rosewholesale)站点组简码 */
    const WEBSITE_CODE_RW = 'rw';

    /** @var string ZF(zaful)站点组简码 */
    const WEBSITE_CODE_ZF = 'zf';

    /** @var string DL(dresslily)站点组简码 */
    const WEBSITE_CODE_DL = 'dl';

    /** @var string GB(GearBest)站点组简码 */
    const WEBSITE_CODE_GB = 'gb';

    //------------ 端口简码 ----------------
    /** @var string 端口简码 - 桌面电脑 */
    const PLATFORM_CODE_PC = 'pc';

    /** @var string 端口简码 - 手机Wap */
    const PLATFORM_CODE_WAP = 'wap';

    /** @var string 端口简码 - 手机APP */
    const PLATFORM_CODE_APP = 'app';

    /** @var string 端口简码 - 苹果系统 */
    const PLATFORM_CODE_IOS = 'ios';

    /** @var string 端口简码 - 平板 */
    const PLATFORM_CODE_IPAD = 'ipad';

    /** @var string 端口简码 -  安卓 */
    const PLATFORM_CODE_ANDROID = 'android';

    /** @var string 端口简码 - Web自适应(适配PC、平板、手机) */
    const PLATFORM_CODE_WEB = 'web';


    //------------ 端口类型 ----------------
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

    /** @var int 平台类型 - Web自适应平台(适配PC、平板、手机) */
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

    /** @var array 平台类型to平台简码关系 */
    const PLATFORM_CODE_TO_TYPE_MAPPING = [
        self::PLATFORM_CODE_PC      => self::PLATFORM_TYPE_PC,
        self::PLATFORM_CODE_WAP     => self::PLATFORM_TYPE_WAP,
        self::PLATFORM_CODE_APP     => self::PLATFORM_TYPE_APP,
        self::PLATFORM_CODE_IOS     => self::PLATFORM_TYPE_IOS,
        self::PLATFORM_CODE_IPAD    => self::PLATFORM_TYPE_IPAD,
        self::PLATFORM_CODE_ANDROID => self::PLATFORM_TYPE_ANDROID,
        self::PLATFORM_CODE_WEB     => self::PLATFORM_TYPE_WEB,
    ];

    /** @var string 站点简码分隔符 */
    const SITE_CODE_SEPARATOR = '-';
}