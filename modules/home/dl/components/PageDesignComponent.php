<?php

namespace app\modules\home\dl\components;

use app\base\SitePlatform;
use app\modules\common\dl\models\PageModel;
use app\modules\common\dl\components\CommonPageDesignComponent;
use app\modules\component\models\ComponentModel;
use app\base\Translate;
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
        $siteCode = $pageModel->site_code;

        $pageArr = PageModel::getPageInfo($pageId);
        if (empty($pageArr['pageLanguages'])) {
            return '活动当前语言下的属性还未设置';
        }

        $supportLanguageKeys = SitePlatform::getSiteHomePageSupportLanguageKeys($siteCode);
        $pageLanguageList = array_column($pageArr['pageLanguages'], null, 'lang');
        // 按照配置文件语言顺序排序，英文放在第一个
        $orderedLanguages = $pageLanguageKeys = [];
        foreach ($supportLanguageKeys as $_langCode) {
            if (isset($pageLanguageList[$_langCode])) {
                $pageLanguageKeys[] = $_langCode;
                $orderedLanguages[] = $pageLanguageList[$_langCode];
            }
        }
        $pageArr['pageLanguages'] = $orderedLanguages;
        empty($lang) && $lang = reset($pageLanguageKeys);

        // 是否包含英语，页面上“复制英语页面”和“复制SKU”功能需要用到
        $hasEn = \in_array(app()->params['en_lang'], $pageLanguageKeys, true);

        list(, $platformCode) = SitePlatform::splitSiteCode($siteCode);
        $siteSuffix = $platformCode;
        $siteConf = app()->params['sites'][ $siteCode ]['home_secondary_domain'];
        $domain = $siteConf[ $lang ];
        $urls = array_column($pageArr['pageLanguages'], null, 'lang');
        $pageUrl = !empty($urls[ $lang ]['page_url']) ? $domain . $urls[ $lang ]['page_url'] : '';

        $type = (SitePlatform::PLATFORM_CODE_WEB === $platformCode)
            ? ComponentModel::RANGE_PC : ComponentModel::RANGE_WAP;
        $placeType = RequestUtils::getPageTypeByModuleName();
        $data = $this->getDesignData($pageId, $type, $siteCode, $lang, $placeType);

        // 获取用户组件模板列表
        $uiTplList = $this->getUserUiTemplateList(app()->user->username, $siteCode, $placeType);

        //设置商品价格货币类型，用于价格显示
        $this->setCurrencyCookie();

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageId);

        return [
            'pid'          => $pid,
            'lang'         => $lang,
            'pageStatus' => $pageModel->status,
            'designDevice' => $designDevice,
            'langName'     => $urls[ $lang ]['lang_name'],
            'defaultLang'  => app()->params['en_lang'],
            'hasEn'        => $hasEn,
            'data'         => $data['data'],
            'uiTplList'    => $uiTplList,
            'customKey'    => $data['customKey'],
            'pageId'       => $pageId,
            'pageInfo'     => $pageArr,
            'pageHtml'     => $data['pageHtml'],
            'preview_url'  => $this->getPagePreviewUrl($pid, $lang),
            'relations'    => [
                'current'    => $siteSuffix,
                'list'       => $this->getConvertRelationList($pageId, $lang, $siteCode)
            ],
            'siteCode'     => $siteCode,
            'platform'     => $siteSuffix,
            'pageUrl'      => $pageUrl,
            'interfaceConfig' => $this->getInterfaceConfig($siteCode),
            'jsLanguage'   => Translate::getUiComponentJsTransMessage($lang),
        ];
    }

}
