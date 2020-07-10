<?php

namespace app\modules\home\zf\components;

use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\PageGroupModel;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\components\CommonPageDesignComponent;
use app\base\Translate;
use app\base\RequestUtils;
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\base\components\AccessLogComponent;
use ego\base\JsonResponseException;

/**
 * 页面装修设计-整个页面相关
 *
 */
class PageDesignComponent extends CommonPageDesignComponent
{
    
    /**
     * 获取首页需要的数据
     *
     * @param string $group_id 页面分组ID
     * @param string $lang     语言代码简称
     * @param string $pipeline 国家编码
     *
     * @return array|string
     * @throws \yii\base\InvalidArgumentException
     */
    public function getIndexData($group_id, $lang, $pipeline)
    {
        /** @var PageModel $pageModel */
        $pageModel = PageModel::getByGroupId($group_id, $pipeline);
        
        if (!$pageModel) {
            return '页面不存在或已被删除';
        }
        
        $pageArr = PageModel::getPageInfo($pageModel->id);
        if (empty($pageArr['pageLanguages'])) {
            return '活动当前语言下的属性还未设置';
        }
        
        $activityLang = PageModel::getPageLangAsActivityLang($pageModel->group_id);
        $pageLang = ActivityModel::getPipelineAndLang($activityLang, $pageModel->site_code);
        
        empty($lang) && $lang = $pageModel->default_lang;
        $pageLanguages = array_column($pageArr['pageLanguages'], null, 'lang');
        if (!isset($pageLanguages[ $lang ])) {
            return '活动当前语言不存在';
        }
        
        // 过滤掉用户没有权限的渠道
        $supportedPipelineCodes = array_column($pageLang, 'pipeline');
        $adminSitePrivilegeComponent = new AdminSitePrivilegeComponent();
        $validPipelineCodes = $adminSitePrivilegeComponent->getCurrentUserValidSiteHomePermissions(
            $pageModel->site_code, $supportedPipelineCodes
        );
        if (empty($validPipelineCodes) || !in_array($pipeline, $validPipelineCodes, true)) {
            return '没有渠道装修权限';
        }
        
        $hasAllHomePermissions = (count($supportedPipelineCodes) === count($validPipelineCodes)) ? 1 : 0;
        $hasPermissionsPipelineList = [];
        foreach ($pageLang as $pipelineInfo) {
            if (in_array($pipelineInfo['pipeline'], $validPipelineCodes, true)) {
                $hasPermissionsPipelineList[] = $pipelineInfo;
            }
        }
        
        // 当前语言放在第一个
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
        
        // 是否包含英语，页面上“复制英语页面”和“复制SKU”功能需要用到
        $hasEn = array_key_exists(app()->params['en_lang'], $pageLanguages);
        $siteSuffix = explode('-', $pageModel->site_code)[1];
        $siteConf = app()->params['sites'][ $pageModel->site_code ]['home_secondary_domain'];
        $domain = $siteConf[ $pipeline ][ $lang ];
        $currentLanguage = $pageLanguages[ $lang ];
        $pageUrl = !empty($currentLanguage['page_url']) ? $domain . $currentLanguage['page_url'] : '';
        
        $data = $this->getDesignData($pageModel->id, $pageModel->type, $pageModel->site_code, $lang, 2);
        $siteDomain = app()->params['sites'][ $pageModel->site_code ]['domain'] ?? '';
        
        // 获取用户组件模板列表
        $placeType = RequestUtils::getPageTypeByModuleName();
        $uiTplList = $this->getUserUiTemplateList(app()->user->username, $pageModel->site_code, $placeType);
        
        //设置商品价格货币类型，用于价格显示
        $this->setCurrencyCookie();
        
        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageModel->id);
        
        // 检查子页面所有渠道是否发布过
        $groupHomePageStatus = 2; // 默认全渠道已发布过
        $pipelinePageModels = PageModel::find()->where(['group_id' => $pageModel->group_id])->all();
        foreach ($pipelinePageModels as $_pipelinePageModel) {
            /** @var \app\modules\common\zf\models\PageModel $_pipelinePageModel */
            
            if (in_array($_pipelinePageModel->status, [PageModel::PAGE_STATUS_TO_BE_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE])) {
                $groupHomePageStatus = 3; // 有渠道没有发布过
            }
        }
    
        $isRollbackLock = 0;
        $lockKey = app()->redisKey->getHomePageRollbackLockKey($pageModel->site_code);
        if (app()->redis->sismember($lockKey, $pageModel->id)) {
            $isRollbackLock = 1;
        }
        
        $status = PageModel::getGroupFirstPipelineStatus($pageModel->group_id);
        
        return [
            'lang'                   => $lang,
            'siteDomain'             => $siteDomain,
            'groupId'                => $group_id,
            'groupHomePageStatus'    => $groupHomePageStatus,
            'isHomeB'                => $pageModel->home_type,
            'hasAllHomePermissions'  => $hasAllHomePermissions,
            'hasPermissionPipelines' => $hasPermissionsPipelineList,
            'langName'               => $currentLanguage['lang_name'],
            'defaultLang'            => $pageModel->default_lang,
            'hasEn'                  => $hasEn,
            'type'                   => $pageModel->type,
            'data'                   => $data['data'],
            'uiTplList'              => $uiTplList,
            'customKey'              => $data['customKey'],
            'pageId'                 => $pageModel->id,
            'pageInfo'               => $pageArr,
            'pipeline'               => $pageModel->pipeline,
            'currentLanguage'        => $currentLanguage,
            'pageHtml'               => $data['pageHtml'],
            'activityInfo'           => ['allLangList' => $hasPermissionsPipelineList],
            'preview_url'            => $this->getPagePreviewUrl($pageModel->pid, $lang),
            'relations'              => [
                'current' => $siteSuffix,
                'list'    => $this->getConvertRelationList($pageModel->id, $lang, $pageModel->site_code)
            ],
            'siteCode'               => $pageModel->site_code,
            'platform'               => $siteSuffix,
            'pageUrl'                => $pageUrl,
            'interfaceConfig'        => $this->getInterfaceConfig($pageModel->site_code),
            'jsLanguage'             => Translate::getUiComponentJsTransMessage($lang),
            'isRollbackLock'         => $isRollbackLock,
            'isNewPipeline'          => ($pageModel->status == $status) ? 0 : 1
        ];
    }
}
