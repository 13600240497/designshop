<?php

namespace app\modules\activity\gb\components;

use app\base\SitePlatform;
use app\modules\common\gb\models\{
    ActivityModel, PageModel, PageLanguageModel
};
use app\modules\common\gb\components\CommonPageDesignComponent;
use app\modules\component\gb\components\{
    ExplainComponent, ExplainTplComponent
};
use app\base\Translate;
use yii\base\Exception;

/**
 * 页面装修设计-整个页面相关
 *
 */
class PageDesignComponent extends CommonPageDesignComponent
{
    const CONTENTDIVID = 'geshop-page-content,.site-main-design .design-view';
    
    /**
     * 获取首页需要的数据
     *
     * @param $group_id
     * @param $lang
     * @param $pipeline
     *
     * @return array|string
     */
    public function getIndexData($group_id, $lang, $pipeline)
    {
        $pageModel = PageModel::getByGroupId($group_id, $pipeline);
        
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
        $defaultLang = $pageModel->defaultLanguage ?? app()->params['en_lang'];
        empty($lang) && $lang = $pageModel->defaultLanguage;
        $pageArr = PageModel::getPageInfo($pageId, $lang);
        if (empty($pageArr['pageLanguages'])) {
            $lang = $defaultLang;
            $pageArr = PageModel::getPageInfo($pageId, $lang);
        }
        if (empty($pageArr['pageLanguages'])) {
            return '活动当前语言下的属性还未设置';
        }
        //当前语言放在第一个
        $firstLanguage = [];
        foreach ($pageArr['pageLanguages'] as $key => $page) {
            if ($key == 0) {
                $firstLanguage = $page;
            }
            if ($page['lang'] == $lang) {
                $pageArr['pageLanguages'][0] = $page;
                $pageArr['pageLanguages'][ $key ] = $firstLanguage;
            }
        }
        $siteSuffix = explode('-', $activityInfo['site_code'])[1];
        $siteConf = app()->params['sites'][ $activityInfo['site_code'] ]['secondary_domain'][ $pipeline ];
        if (empty($siteConf[ $lang ])) {
            return "{$pipeline}渠道下没有{$lang}语言";
        }
        $domain = $siteConf[ $lang ];
        $urls = array_column($pageArr['pageLanguages'], null, 'lang');
        $pageUrl = !empty($urls[ $lang ]['page_url']) ? $domain . $urls[ $lang ]['page_url'] : '';
        $type = in_array((int) $activityInfo['type'], [ActivityModel::TYPE_APP, ActivityModel::TYPE_IOS, ActivityModel::TYPE_IPAD, ActivityModel::TYPE_ANDROID])
            ? ActivityModel::TYPE_MOBILE : $activityInfo['type'];
        $data = $this->getDesignData($pageId, $type, $activityInfo['site_code'], $lang, 1);
        $langConfig = app()->params['lang'];
        $isEditEnv = \in_array(app()->controller->getRoute(), ExplainComponent::EDIT_ENV_ROUTE, true) ? 1 : 0;
        
        return [
            'isEditEnv'       => $isEditEnv,
            'lang'            => $lang,
            'langName'        => $urls[ $lang ]['lang_name'],
            'defaultLang'     => $defaultLang,
            'defaultLangName' => $langConfig[ $defaultLang ]['name'] ?? '',
            'pipeline'        => $pipeline,
            'data'            => $data['data'],
            'customKey'       => $data['customKey'],
            'pageId'          => $pageId,
            'pageInfo'        => $pageArr,
            'customCss'       => $this->encodeCustomCss($pageArr['pageLanguages'][0], $activityInfo['site_code']),
            'activityInfo'    => $activityInfo,
            'pageHtml'        => $data['pageHtml'],
            'preview_url'     => $this->getPagePreviewUrl($pageModel->pid, $lang, $pipeline),
            'relations'       => [
                'current' => $siteSuffix,
                'list'    => $this->getConvertRelationList($pageModel->id, $lang, $activityInfo['site_code'], $pipeline)
            ],
            'siteCode'             => $activityInfo['site_code'],
            'platform'             => $siteSuffix,
            'pageUrl'              => $pageUrl,
            'interfaceConfig'      => $this->getInterfaceConfig($pageModel->site_code),
            'group_id'             => $group_id,
            'uiComponentJsMessage' => Translate::getGbUiComponentJsTransMessage($lang),
            'shareChannel'         => $pageArr['pageLanguages'][0]['share'] ? join(',', json_decode($pageArr['pageLanguages'][0]['share'], true)['checked']) : '',
        ];
    }
    
    /**
     * 获取自定义样式
     *
     * @param  array $pageLanguage
     *
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
            $background = ' #' . static::CONTENTDIVID . ' {' . $background . '} ';
        }
        
        $customCss = ($pageLanguage['style_type'] === PageLanguageModel::STYLE_TYPE_SYSTEM) ? $background : ($pageLanguage['custom_css'] ?? '');
    
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
        $data = PageModel::getPageAllPreviewList($pageModel->group_id, $pageModel->site_code);
        $urls = [];
        if (!empty($data)) {
            foreach ($data as $pipeline => $valList) {
                if (!empty($valList) && is_array($valList)) {
                    foreach ($valList['lang'] as $lang) {
                        $urls[] = [
                            'name' => config("soa.gb.pipeline.{$pipeline}") . '-' . config('lang')[ $lang ]['name'],
                            'link' => $this->getPagePreviewUrl($valList['pid'], $lang, $pipeline)
                        ];
                    }
                }
                
            }
        }
        
        return app()->helper->arrayResult($this->codeSuccess, 'success', $urls);
    }
    
    /**
     * 批量发布页面
     *
     * @param array $params
     *
     * @return array|string
     */
    public function batchActivityRelease(array $params)
    {
        ignore_user_abort(true);
        if (empty($params['ids']) && !is_string($params['ids'])) {
            return app()->helper->arrayResult($this->codeFail, '参数错误');
        }
        $pageIds = explode(',', $params['ids']);
    
        $siteCode = !empty($params['site_code']) ? $params['site_code'] : 'gb-pc';
        $module = app()->controller->module->module->id;
        $pageLanguageList = PageLanguageModel::getAllPageLangList($pageIds);
        foreach ($pageLanguageList as $langList) {
            $api = app()->params['sites'][ $siteCode ]['headFooterMonitorDomain'][ $module ][ $langList['pipeline'] ][ $langList['lang'] ] ?? '';
            if (!empty($api)) {
                $urls[] = [
                    'site_code' => $siteCode,
                    'pipeline'  => $langList['pipeline'],
                    'api'       => $api
                ];
            }
        }
        
        (new ExplainTplComponent())->promiseSetHeadOrFooter($urls);
        app()->arrayCache->set('release-pipeline-list', array_column($pageLanguageList, 'pipeline'));
        unset($pageLanguageList, $urls);
        
        $tips = [];
        //开启事务
        $transaction = app()->db->beginTransaction();
        foreach ($pageIds as $pageId) {
            $pipeline = '';
            try {
                $pageModel = $this->beforeGbVerifyRelease($pageId);
                /** @var \app\modules\common\models\PageModel $pageModel */
                $pageModel->status = PageModel::PAGE_STATUS_HAS_ONLINE;
                $pageModel->auto_refresh = PageModel::AUTO_REFRESH;
                $pageModel->verify_user = app()->user->username;
                $pageModel->verify_time = time();
                $pipeline = $pageModel->pipeline;
                
                //页面上线，生成上线文件并推送S3
                if (PageModel::PAGE_STATUS_HAS_ONLINE === $pageModel->status) {
                    list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], $pageModel->activity_id,
                        $pageModel->pipeline, false, false, false);
                    if (!$success) {
                        throw new Exception('发布失败', $this->codeFail);
                    }
                }
    
                if (false === $pageModel->update(true)) {
                    throw new Exception('发布失败', $this->codeFail);
                }
            } catch (Exception $exception) {
                array_push($tips,
                    !empty($pipeline) ? config("soa.gb.pipeline.{$pipeline}") : ($exception->getMessage() . $pageId)
                );
            }
        }
        
        //活动上线
        if (false === ActivityModel::changeOnlineActivity($pageModel->activity_id, PageModel::PAGE_STATUS_HAS_ONLINE)) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, '发布失败：活动上线失败');
        }
        
        $transaction->commit();
        $message = empty($tips) ? '发布成功，请在【访问】中查看已发布的页面效果.' : implode(',', $tips) . ' 发布失败.';
        
        return app()->helper->arrayResult($this->codeSuccess, $message);
    }
    
    /**
     * 页面发布前置判断
     *
     * @param $pageId
     *
     * @return null|static
     * @throws Exception
     */
    public function beforeGbVerifyRelease(int $pageId)
    {
        $pageModel = PageModel::findOne([
            'id'        => $pageId,
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            throw new Exception('无效的页面ID', $this->codeFail);
        }
        
        if (!PageModel::checkAllLangSet($pageModel->activity_id, $pageId, $pageModel->pipeline)) {
            throw new Exception('活动设置了多语言，但页面的多语言配置不完整，请先去“页面编辑”下设置', $this->codeFail);
        }
        //复制默认语言的数据到其他语言
        (new PageDesignComponent)->pipelineDefaultLangToOtherLang($pageId);
        
        if (
            !empty($pageModel->activity_id)
            && true !== ($res = ActivityModel::isEnabled($pageModel->activity_id, true))
        ) {
            throw new Exception($res['message'], $this->codeFail);
        }
        
        return $pageModel;
    }
}
