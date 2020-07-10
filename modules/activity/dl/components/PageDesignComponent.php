<?php

namespace app\modules\activity\dl\components;

use app\components\auto\AutoRefreshPage;
use app\base\Translate;
use app\modules\common\dl\models\{
    ActivityModel, PageModel
};
use app\modules\component\models\ComponentModel;
use app\modules\common\dl\components\CommonPageDesignComponent;
use app\base\RequestUtils;
use app\modules\base\components\AccessLogComponent;

/**
 * 页面装修设计-整个页面相关
 *
 */
class PageDesignComponent extends CommonPageDesignComponent
{

    /**
     * 获取首页需要的数据
     *
     * @param \app\modules\common\dl\models\PageModel $pageModel
     * @param string $lang
     * @param string $designDevice 设计时显示设备
     *
     * @return array|string
     */
    public function getIndexData($pageModel, $lang, $designDevice)
    {
        $pid = $pageModel->pid;
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
        //当前语言放在第一个
        $firstLanguage = [];
        foreach ($pageArr['pageLanguages'] as $key=>$page){
            if($key == 0){
                $firstLanguage = $page;
            }
            if($page['lang'] == $lang){
                $pageArr['pageLanguages'][0] = $page;
                $pageArr['pageLanguages'][$key] = $firstLanguage;
            }
        }

        $siteCode = $activityInfo['site_code'];
        $siteSuffix = explode('-', $siteCode)[1];
        $siteConf = app()->params['sites'][ $siteCode ]['secondary_domain'];
        $domain = $siteConf[ $lang ];
        $urls = array_column($pageArr['pageLanguages'], null, 'lang');
        $pageUrl = !empty($urls[ $lang ]['page_url']) ? $domain . $urls[ $lang ]['page_url'] : '';

        $type = (ActivityModel::TYPE_WEB === (int) $activityInfo['type'])
            ? ComponentModel::RANGE_PC : ComponentModel::RANGE_WAP;
        $data = $this->getDesignData($pageId, $type, $activityInfo['site_code'], $lang, 1);

        // 获取用户组件模板列表
        $placeType = RequestUtils::getPageTypeByModuleName();
        $uiTplList = $this->getUserUiTemplateList(app()->user->username, $siteCode, $placeType);

        //设置商品价格货币类型，用于价格显示
        $this->setCurrencyCookie();

        // 自动刷新组件处理
        $autoRefreshPage = new AutoRefreshPage($pageModel, $lang);

        // 站点域名
        if (in_array(YII_ENV, ['dev', 'test'], true)) {
            $siteDomain = app()->params['sites'][ $siteCode ]['developdomain'];
        } else {
            $siteDomain = app()->params['sites'][ $siteCode ]['domain'];
        }

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageId);

        return [
            'pid'          => $pid,
            'lang'         => $lang,
            'designDevice' => $designDevice,
            'langName'     => $urls[ $lang ]['lang_name'],
            'defaultLang'  => app()->params['en_lang'],
            'hasEn'        => $hasEn,
            'data'         => $data['data'],
            'uiTplList'    => $uiTplList,
            'customKey'    => $data['customKey'],
            'pageId'       => $pageId,
            'pageInfo'     => $pageArr,
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
            'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
            'jsLanguage'    => Translate::getUiComponentJsTransMessage($lang),
            'jsAsyncData'   => $autoRefreshPage->getAsyncDataJsVariable(),
            'siteDomain'    => $siteDomain
        ];
    }
}
