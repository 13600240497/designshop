<?php
namespace app\base;

/**
 * 站点常量，方便维护
 *
 * @since v1.5.0
 */
class SiteConstants
{

    const HOME_PAGE_TYPE_UNKNOWN    = 0;   // 首页类型 - 非首页类型(如专题活动发布时传这个参数)
    const HOME_PAGE_TYPE_INDEX      = 1;   // 首页类型 - 正常首页
    const HOME_PAGE_TYPE_INDEX_B    = 2;   // 首页类型 - AB测试B首页
    const HOME_PAGE_TYPE_KOL        = 3;   // 首页类型 - 网红首页
    const HOME_PAGE_TYPE_TEST       = 4;   // 首页类型 - 测试首页(由于生产环境首页测试人员无法发布，用于生产一个测试页面)


    /** @var int 活动页面类型 - 未知 */
    const ACTIVITY_PAGE_TYPE_UNKNOW = 0;
    /** @var int 活动页面类型 - 专题页 */
    const ACTIVITY_PAGE_TYPE_SPECIAL = 1;
    /** @var int 活动页面类型 - 首页 */
    const ACTIVITY_PAGE_TYPE_HOME  = 2;
    /** @var int 活动页面类型 - 推广页 */
    const ACTIVITY_PAGE_TYPE_ADVERTISEMENT = 3;

    /** @var string 语言简码 - 英文 */
    const LANG_CODE_EN = 'en';

    /** @var int 首页面的固定活动ID */
    const HOME_PAGE_ACTIVITY_ID = 0;

    /** @var string 字段名称 - 站点简码 */
    const KEY_NAME_SITE_CODE = 'site_code';


    /** @var int 商品SKU来源 - 手动输入 */
    const GOODS_SKU_FROM_INPUT = 1;
    /** @var int 商品SKU来源 - 选品系统 */
    const GOODS_SKU_FROM_IPS = 2;
    /** @var int 商品SKU来源 - OBS系统 */
    const GOODS_SKU_FROM_OBS = 3;

    /** @var string 英文逗号 */
    const CHAR_COMMA = ',';

    /** @var string RW站点组简码 */
    const SITE_GROUP_CODE_RG = 'rg';
    /** @var string RW站点组简码 */
    const SITE_GROUP_CODE_RW = 'rw';
    /** @var string GB站点组简码 */
    const SITE_GROUP_CODE_GB = 'gb';
    /** @var string ZF站点组简码 */
    const SITE_GROUP_CODE_ZF = 'zf';
    /** @var string DL(dresslily)站点组简码 */
    const SITE_GROUP_CODE_DL = 'dl';
    /** @var string SUK(suaoki)站点组简码 */
    const SITE_GROUP_CODE_SUK = 'suk';

    const SITE_GROUP_CODE = ['zf' => 'ZAFUL','rg' => 'ROSEGAL','dl' => 'DREEELILY','suk' => 'SUK'];

    /** @var int 活动类型 - 专题活动 */
    const ACTIVITY_TYPE_SPECIAL = 1;
    /** @var int 活动类型 - 服装广告落地页面 */
    const ACTIVITY_TYPE_FZ_ADVERTISEMENT = 2;
    /** @var int 活动类型 - GB广告落地页面 */
    const ACTIVITY_TYPE_GB_ADVERTISEMENT = 3;


    /** @var string 应用模块名称 - GB广告落地页面模块 */
    const MODULE_NAME_GB_ADVERTISEMENT = 'gbad';
    /** @var string 应用模块名称 - 首页活动模块 */
    const MODULE_NAME_HOME = 'home';
    /** @var string 应用模块名称 - 专题活动模块 */
    const MODULE_NAME_ACTIVITY = 'activity';
    /** @var string 应用模块名称 - 推广页模块 */
    const MODULE_NAME_ADVERTISEMENT = 'advertisement';

    /** @var int 渠道复制类型 - 复制页面 */
    const PIPELINE_COPY_TYPE_PAGE = 1;
    /** @var int 渠道复制类型 - 复制组件 */
    const PIPELINE_COPY_TYPE_COMPONENT  = 2;
    /** @var int 渠道复制类型 - 复制SKU */
    const PIPELINE_COPY_TYPE_SKU =3;

    /** @var string UI组件配置项 - 商品SKU列表 */
    const UI_COMPONENT_KEY_GOODS_SKU = 'goodsSKU';
    /** @var string UI组件配置项 - 商品SKU列表限制显示数量 */
    const UI_COMPONENT_KEY_GOODS_SKU_LIMIT = 'goodsSkuLimit';
}
