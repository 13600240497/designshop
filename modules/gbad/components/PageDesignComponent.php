<?php

namespace app\modules\gbad\components;

use app\base\SitePlatform;
use app\modules\common\models\{
    ActivityModel, PageLanguageModel, PageModel
};
use app\modules\common\components\CommonPageDesignComponent;
use app\base\SiteConstants;
use app\base\Translate;

/**
 * 页面装修设计-整个页面相关
 *
 */
class PageDesignComponent extends CommonPageDesignComponent
{
    const CONTENT_DIV_ID  = 'geshop-page-content,.site-main-design .design-view';
    /**
     * 获取首页需要的数据
     *
     * @param $pid
     * @param $lang
     *
     * @return array|string
     */
    public function getIndexData($pid, $lang)
    {
        $pageModel = PageModel::getByPId($pid);

        if (!$pageModel) {
            return '页面不存在或已被删除';
        }

        $pageId = $pageModel->id;
        $activityInfo = ActivityModel::getActivityInfo($pageModel->activity_id);

        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($activityInfo)) {
            return '只有活动创建者才具有此权限';
        }

        if (empty($activityInfo['langList'])) {
            return '活动还未设置语言选项';
        }

        empty($lang) && $lang = current($activityInfo['langList'])['key'];
        // 是否包含英语，页面上“复制英语页面”和“复制SKU”功能需要用到
        $hasEn = \in_array(app()->params['en_lang'], array_column($activityInfo['langList'], 'key'), true);
        $pageArr = PageModel::getPageInfo($pageId, $lang);
        if (empty($pageArr['pageLanguages'])) {
            return '活动当前语言下的属性还未设置';
        }
        $siteSuffix = explode('-', $activityInfo['site_code'])[1];
        $siteConf = app()->params['sites'][ $activityInfo['site_code'] ]['ad_secondary_domain'];
        $domain = $siteConf[ $lang ];
        $urls = array_column($pageArr['pageLanguages'], null, 'lang');
        $pageUrl = !empty($urls[ $lang ]['page_url']) ? $domain . $urls[ $lang ]['page_url'] : '';
        $type = (ActivityModel::TYPE_APP === (int) $activityInfo['type'])
            ? ActivityModel::TYPE_MOBILE : $activityInfo['type'];
        $data = $this->getDesignData($pageId, $type, $activityInfo['site_code'], $lang, 3);
        $activityType = SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT;

        return [
            'lang'         => $lang,
            'langName'     => $urls[ $lang ]['lang_name'],
            'defaultLang'  => app()->params['en_lang'],
            'activityType' => $activityType,
            'hasEn'        => $hasEn,
            'data'         => $data['data'],
            'customKey'    => $data['customKey'],
            'pageId'       => $pageId,
            'pageInfo'     => $pageArr,
            'customCss'    => $this->encodeCustomCss($pageArr['pageLanguages'][0], $activityInfo['site_code']),
            'activityInfo' => $activityInfo,
            'pageHtml'     => $data['pageHtml'],
            'preview_url'  => $this->getPagePreviewUrl($pid, $lang),
            'relations' => [
                'current' => $siteSuffix,
                'list' => $this->getConvertRelationList($pageId, $lang, $activityInfo['site_code'])
            ],
            'siteCode'     => $activityInfo['site_code'],
            'platform'     => $siteSuffix,
            'pageUrl'      => $pageUrl,
            'uiComponentJsMessage'  => Translate::getGbUiComponentJsTransMessage($lang),
            'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code)
        ];
    }

    /**
     * 获取自定义样式
     * @param  array   $pageLanguage
     * @return   string
     */
    private function encodeCustomCss($pageLanguage, $siteCode)
    {
        $background = '';
        if (!empty($pageLanguage['background_color'])) {
            $background .= 'background-color:' . $pageLanguage['background_color'] . ';';
        }
        if (!empty($pageLanguage['background_image'])) {
            $background .= 'background-image:url("' . $pageLanguage['background_image'] . '");';
        }
        if (!empty($pageLanguage['background_position'])) {
            $background .= 'background-position:' . $pageLanguage['background_position'] . ';';
        }
        if (!empty($pageLanguage['background_repeat'])) {
            $background .= 'background-repeat:' . $pageLanguage['background_repeat'] . ';';
        }
        if (!empty($background)) {
            $background = ' #' . static::CONTENT_DIV_ID . ' {' . $background . '} ';
        }

        $customCss = ($pageLanguage['style_type'] === PageLanguageModel::STYLE_TYPE_SYSTEM) ? $background : $pageLanguage['custom_css'];
    
        $goodsComponentStyle = !empty($pageLanguage['goods_component_style'])
            ? json_decode($pageLanguage['goods_component_style'], true)
            : [];
        if (SitePlatform::isPcPlatform($siteCode)) {
            $sitePath = $siteCode;
        } else {
            $sitePath = str_replace(strstr($siteCode, '-'), '-wap', $siteCode);
        }
        $customCss .= app()->view->renderFile(
            APP_PATH . "/htdocs/resources/sitesPublic/{$sitePath}/twig/tpl_component_stylesheet.twig",
            $goodsComponentStyle
        );
        
        if (empty($customCss)) {
            return '';
        }

        return $customCss;
    }
    
    /**
     * 获取页面下所有渠道和语言的预览链接
     *
     * @param int $pageId
     *
     * @return array|string
     */
    public function previewList(int $pageId)
    {
        $pageModel = PageModel::getById($pageId);
        if (!$pageModel) {
            return '页面不存在或已被删除';
        }
        $pageLanguageModel = PageLanguageModel::find()->where(['page_id' => $pageId])->asArray()->all();
        $langList = !empty($pageLanguageModel) ? array_column($pageLanguageModel, 'lang') : [];
        $urls = [];
        if (!empty($langList) && is_array($langList)) {
            foreach ($langList as $lang) {
                $urls[] = [
                    'name' => config('lang')[ $lang ]['name'],
                    'link' => $this->getPagePreviewUrl($pageModel->pid, $lang)
                ];
            }
        }
        
        return app()->helper->arrayResult($this->codeSuccess, 'success', $urls);
    }
}
