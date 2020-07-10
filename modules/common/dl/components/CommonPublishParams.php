<?php
namespace app\modules\common\dl\components;

/**
 * 由于发布函数参数多切层层传递，这里用类的静态方法保存参数，以便在后期使用
 * ！！！ 注意，只能在同步环境下使用
 */
class CommonPublishParams
{
    /** @var int 首页类型 (参考 SiteConstants::HOME_PAGE_TYPE_* 常量定义）,默认0 未知类型 */
    public static $homePageType = 0;

}