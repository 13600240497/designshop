<?php

namespace app\modules\home\components;

use app\modules\common\models\PageModel;
use app\modules\common\components\CommonPageDesignComponent;
use yii\helpers\Url;
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
     * @param $pid
     * @param $lang
     *
     * @return array|string
     * @throws \yii\base\InvalidArgumentException
     */
    public function getIndexData($pid, $lang)
    {
        $pageModel = PageModel::getByPId($pid);

        if (!$pageModel) {
            return '页面不存在或已被删除';
        }

        $pageArr = PageModel::getPageInfo($pageModel->id);
        if (empty($pageArr['pageLanguages'])) {
            return '活动当前语言下的属性还未设置';
        }

        $pageLang = [];
        foreach ($pageArr['pageLanguages'] as $key => $page) {
            // 为了保证英语排在最前面
            if ($page['lang'] === app()->params['en_lang']) {
                $pageLang[0] = ['key' => $page['lang'], 'name' => ['name'=>'英文']];
                continue ;
            }
            $key++;
            $pageLang[$key]['key'] = $page['lang'];
            $pageLang[$key]['name']['name'] = $page['lang_name'];
        }
        ksort($pageLang);

        empty($lang) && $lang = current($pageLang)['key'];
        $pageLanguages = array_column($pageArr['pageLanguages'], null, 'lang');
        if (!isset($pageLanguages[$lang])) {
            return '活动当前语言不存在';
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
        // 是否包含英语，页面上“复制英语页面”和“复制SKU”功能需要用到
        $hasEn = array_key_exists(app()->params['en_lang'], $pageLanguages);
        $siteSuffix = explode('-', $pageModel->site_code)[1];
        $data = $this->getDesignData($pageModel->id, $pageModel->type, $pageModel->site_code, $lang, 2);
        $siteConf = app()->params['sites'][$pageModel->site_code]['home_secondary_domain'];
        $domain = $siteConf[$lang];
        $currentLanguage = $pageLanguages[$lang];
        $pageUrl = !empty($currentLanguage['page_url']) ? $domain . $currentLanguage['page_url'] : '';

        // 获取用户组件模板列表
        $placeType = RequestUtils::getPageTypeByModuleName();
        $uiTplList = $this->getUserUiTemplateList(app()->user->username, $pageModel->site_code, $placeType);

        //设置商品价格货币类型，用于价格显示
        $this->setCurrencyCookie();

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageModel->id);

        return [
            'lang' => $lang,
            'pageStatus' => $pageModel->status,
            'langName' => $currentLanguage['lang_name'],
            'defaultLang' => app()->params['en_lang'],
            'hasEn'        => $hasEn,
            'type' => $pageModel->type,
            'data' => $data['data'],
            'uiTplList'    => $uiTplList,
            'customKey' => $data['customKey'],
            'pageId' => $pageModel->id,
            'pageInfo' => $pageArr,
            'currentLanguage' => $currentLanguage,
            'pageHtml' => $data['pageHtml'],
            'homelangList' => $pageLang,
            'preview_url' => $this->getPagePreviewUrl($pid, $lang),
            'relations' => [
                'current' => $siteSuffix,
                'list' => $this->getConvertRelationList($pageModel->id, $lang, $pageModel->site_code)
            ],
            'siteCode' => $pageModel->site_code,
            'platform'     => $siteSuffix,
            'pageUrl' => $pageUrl,
            'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
            'jsLanguage'    => Translate::getUiComponentJsTransMessage($lang),
        ];
    }

}
