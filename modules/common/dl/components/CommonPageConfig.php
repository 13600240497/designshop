<?php

namespace app\modules\common\dl\components;

/**
 * 页面配置项
 */
class CommonPageConfig extends Component
{
    /**
     * @var string 静态资源域名
     */
    public $staticDomain;

    /**
     * @var string 活动ID
     */
    public $activityId;

    /**
     * @var string 语言代码简称
     */
    public $lang = '';

    /**
     * @var string 页面ID
     */
    public $pageId;

    /**
     * @var string 站点简称
     */
    public $siteCode = '';

    /**
     * @var string 页面标题
     */
    public $title = '';

    /**
     * @var string 页面关键词
     */
    public $keywords = '';

    /**
     * @var string 页面描述
     */
    public $description = '';

    /**
     * @var string 背景颜色
     */
    public $background_color = '';

    /**
     * @var string 背景图片
     */
    public $background_image = '';

    /**
     * @var string 背景位置
     */
    public $background_position = '';

    /**
     * @var string 背景定位
     */
    public $background_repeat = '';

    /**
     * @var int 页面样式类型
     */
    public $style_type = 0;

    /**
     * @var array 多时段页面样式
     */
    public $multi_time_style = [];

    /**
     * @var string 统计代码
     */
    public $statisticsCode = '';

    /**
     * @var string 自定义CSS
     */
    public $customCss = '';

    /**
     * @var string 组件合并的css的link标签属性
     */
    public $componentCss = '';

    /**
     * @var string 组件合并的js的script标签属性
     */
    public $componentJs = '';

    /**
     * @var string 页面content包裹的DIV的ID
     */
    public $contentDivId = '';

    /**
     * @var string SEO标题
     */
    public $seo_title = '';

    /**
     * @var string 活动类型
     */
    public $mold = '';

    //分享信息
    public $share_image  = '';
    public $share_link    = '';
    public $share_desc   = '';
    public $share_title  = '';

    public function init()
    {
        parent::init();
        //对于预览地址activity/design/preview，不需要静态域名，直接使用相对路径即可
        $this->staticDomain = \in_array(app()->controller->getRoute(), app()->params['no_domain_route'], true)
            ? '' : app()->params['s3PublishConf']['staticDomain'] . app()->params['s3PublishConf']['staticKeyPre'];
    }
}
