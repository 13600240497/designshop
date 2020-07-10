<?php

namespace app\modules\home\zf\components;

use app\modules\common\zf\models\{
    PageModel, PageLanguageModel, PagePublishCacheModel, PagePublishLogModel
};
use app\modules\base\models\AdminModel;
use app\base\SiteConstants;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use ego\base\JsonResponseException;
use app\modules\common\zf\components\CommonPageComponent;
use app\modules\base\components\AdminSitePrivilegeComponent;

/**
 * 首页组件
 */
class PageComponent extends CommonPageComponent
{
    const ATTR_SITE_CODE   = 'site_code';
    const SITE_CODE_PC     = '-pc';
    const SITE_CODE_MOBILE = '-wap';
    const SITE_CODE_APP    = '-app';

    /** 首页上线状态 */
    const HOME_PAGE_ONLINE_STATUS = [PageModel::PAGE_STATUS_HAS_ONLINE];

    /** 渠道列表不存在(后面新增渠道，老数据下没有页面)时默认页面ID */
    const NOT_EXIST_PAGE_ID = -1;

    /**
     * 首页装修列表
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lists(array $params)
    {
        if (empty($params['site_code']) || !SitePlatform::isCurrentSiteGroupPlatformSite($params['site_code'])) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        $siteCode = $params['site_code'];

        //查询正在使用的首页
        $onlineGroupIds = $onlinePageList = [];
        if (empty($params['keywords']) && ($params['pageNo'] < 2)) {
            $onlinePageAList = $this->getTopPage($siteCode);
            if (!empty($onlinePageAList)) {
                $onlineGroupIds = array_column($onlinePageAList, 'group_id');
            }
            $onlinePageBList = $this->getTopPage($siteCode, PageModel::HOME_B);
            if (!empty($onlinePageBList)) {
                $onlineGroupIds = array_merge($onlineGroupIds, array_column($onlinePageBList, 'group_id'));
            }
            $onlinePageList = array_merge($onlinePageAList, $onlinePageBList);
        }

        //查询首页列表
        $query = PageModel::find()->alias('h')
            ->select(
                'h.id, h.pid, h.group_id, h.pipeline, h.default_lang, a.realname as create_name,h.create_user,h.create_time,
                a2.realname as update_user, h.update_time, h.status, h.home_type, h.is_lock ,h.site_code, l.page_url,h.version'
            )
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'h.id = l.page_id')
            ->leftJoin(AdminModel::tableName() . ' a', 'h.create_user = a.username')
            ->leftJoin(AdminModel::tableName() . ' a2', 'h.update_user = a2.username')
            ->where([
                'h.is_delete'   => PageModel::NOT_DELETE,
                'h.activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                'h.site_code'   => $siteCode
            ])->andWhere([
                'not in',
                'h.status',
                static::HOME_PAGE_ONLINE_STATUS
            ]);

        if (!empty($onlineGroupIds)) {
            $query->andWhere(['not in', 'h.group_id', $onlineGroupIds]);
        }

        if (!empty($params['keywords'])) {
            $query->andFilterWhere(['like', 'l.title', $params['keywords']]);
        }
        $query = $query->groupBy('h.group_id');

        //分页
        $total = $query->count();
        $pagination = Pagination::new($total);
        $pageList = $query->orderBy('h.id DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        $pageList = $pageList ? ArrayHelper::toArray($pageList) : [];

        //组装数据
        if (!empty($pageList) && \is_array($pageList)) {
            $pageIds = array_column($pageList, 'id');
            $pageLangList = PageLanguageModel::find()
                ->where(['page_id' => $pageIds])
                ->orderBy('id ASC')
                ->all();
            $pageLangList = ArrayHelper::toArray($pageLangList);
            $pageList = $this->buildListData($pageList, $pageLangList);
            if (!empty($pageList)) {
                $pageGroupIds = array_column($pageList, 'group_id');
                list($groupNewPipelineStatus, $groupLangList) = $this->getPageListGroupLanguages($siteCode, $pageGroupIds);
                $allGroupPageStatus = $this->getPageListGroupPageStatus($siteCode, $pageGroupIds);
                foreach ($pageList as &$pageInfo) {
                    // 只有正在使用的页面才提示是否有新渠道
                    $pageInfo['has_new_pipeline'] = false;
                    if (
                        (int) $pageInfo['home_type'] === PageModel::HOME_B
                        && (int) $pageInfo['status'] === PageModel::PAGE_STATUS_HAS_ONLINE
                    ) {
                        $pageInfo['has_new_pipeline'] = $groupNewPipelineStatus[ $pageInfo['group_id'] ] ?? false;
                    }

                    $pageInfo['group_status'] = $allGroupPageStatus[ $pageInfo['group_id'] ];
                    $pageInfo['group_languages'] = $groupLangList[ $pageInfo['group_id'] ] ?? [];
                }
            }
        }

        // 检查当前用户是否有装修权限
        $privilegeComponent = new AdminSitePrivilegeComponent();
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $allPipelineList = $privilegeComponent->getSiteAllPermissions($websiteCode);
        $homePipelineCodes = array_keys(PipelineUtils::getConfigHomeSupportPipelineList($siteCode));
        $validPipelineCodes = $privilegeComponent->getCurrentUserValidSiteHomePermissions($siteCode, $homePipelineCodes);

        $hasHomePermissions = empty($validPipelineCodes) ? 0 : 1;
        $hasAllHomePermissions = (count($homePipelineCodes) === count($validPipelineCodes)) ? 1 : 0;
        $homePermissions = array_filter($allPipelineList, function ($key) use (&$validPipelineCodes) {
            return in_array($key, $validPipelineCodes, true);
        }, ARRAY_FILTER_USE_KEY);

        $data = [
            'top_page'            => $onlinePageList,
            'list'                => $pageList,
            'total'               => $total,
            'has_permissions'     => $hasHomePermissions,
            'has_all_permissions' => $hasAllHomePermissions,
            'home_permissions'    => $homePermissions
        ];

        return app()->helper->arrayResult($this->codeSuccess, 'success', $data);
    }


    /**
     * 获取页面列表，页面分组的发布状态
     *
     * @param string $siteCode
     * @param array  $pageGroupIds
     *
     * @return array
     */
    private function getPageListGroupPageStatus($siteCode, $pageGroupIds)
    {

        $pageList = PageModel::find()
            ->select('id, pid, group_id, pipeline, status')
            ->where([
                'is_delete'   => PageModel::NOT_DELETE,
                'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                'site_code'   => $siteCode,
                'group_id'    => $pageGroupIds
            ])
            ->asArray()
            ->all();

        $groupPageStatus = [];
        foreach ($pageList as $pageInfo) {
            $_groupId = $pageInfo['group_id'];

            if (!isset($groupPageStatus[ $_groupId ])) {
                $groupPageStatus[ $_groupId ] = 2; // 默认全渠道已发布过
            }

            if (in_array($pageInfo['status'], [PageModel::PAGE_STATUS_TO_BE_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE])) {
                $groupPageStatus[ $_groupId ] = 3; // 有渠道没有发布过
            }
        }

        return $groupPageStatus;
    }

    /**
     * 获取页面列表，页面的其他渠道的语言信息
     *
     * @param string $siteCode
     * @param array  $pageGroupIds
     *
     * @return array
     */
    private function getPageListGroupLanguages($siteCode, $pageGroupIds)
    {

        $pageList = PageModel::find()
            ->select('id, pid, group_id, pipeline')
            ->where([
                'is_delete'   => PageModel::NOT_DELETE,
                'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                'site_code'   => $siteCode,
                'group_id'    => $pageGroupIds
            ])
            ->asArray()
            ->all();

        $pageIds = array_column($pageList, 'id');
        $pageLangList = PageLanguageModel::find()->alias('pl')
            ->where(['pl.page_id' => $pageIds])
            ->asArray()
            ->all();

        $configAllLangList = app()->params['lang'];
        $allPageList = [];
        foreach ($pageLangList as $langInfo) {
            $_langCode = $langInfo['lang'];
            $langInfo['lang_name'] = $configAllLangList[ $_langCode ]['name'];
            $allPageList[ $langInfo['page_id'] ][ $_langCode ] = $langInfo;
        }

        $configAllPipelineList = PipelineUtils::getConfigAllPipelineList($siteCode);
        $configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($siteCode);

        $groupLangList = [];
        $groupNewPipelineStatus = [];
        foreach ($pageList as $pageInfo) {
            $_pipelineCode = $pageInfo['pipeline'];
            $_page_id = $pageInfo['id'];
            $groupLangList[ $pageInfo['group_id'] ][ $_pipelineCode ] = [
                'code'      => $_pipelineCode,
                'name'      => $configAllPipelineList[ $_pipelineCode ] ?? '',
                'pid'       => $pageInfo['pid'],
                'page_id'   => $_page_id,
                'lang_list' => $allPageList[ $_page_id ] ?? []
            ];

            // 处理老数据缺失的语言
            if (isset($configPipelineLangList[$_pipelineCode])) {
                $_supportLangList = $configPipelineLangList[$_pipelineCode]['lang_list'];
                $_hasLangList = &$groupLangList[$pageInfo['group_id']][$_pipelineCode]['lang_list'];
                foreach ($_supportLangList as $_langCode => $_langInfo) {

                    if (!isset($_hasLangList[$_langCode])) {
                        $_groupId = $pageInfo['group_id'];
                        $groupNewPipelineStatus[$_groupId] = true;

                        /** @var \app\modules\common\zf\models\PageLanguageModel $pageLanguageModel */
                        $pageLanguageModel = new PageLanguageModel();
                        $pageLanguageModel->id = 0;
                        $pageLanguageModel->page_id = $_page_id;
                        $pageLanguageModel->lang = $_langCode;
                        $pageLanguageModel->group_id = $_groupId;
                        $pageLanguageModel->title = '';
                        $pageLanguageModel->seo_title = '';
                        $pageLanguageModel->keywords = '';
                        $pageLanguageModel->description = '';
                        $pageLanguageModel->tpl_id = 0;
                        $_hasLangList[$_langCode] = ArrayHelper::toArray($pageLanguageModel);
                        $_hasLangList[$_langCode]['lang_name'] = $_langInfo['name'];
                    }
                }
            }
        }

        //新增渠道时，老的活动页面没有渠道的情况下，使用默认信息
        foreach ($groupLangList as $_groupId => &$_pipelineList) {
            $groupNewPipelineStatus[$_groupId] = $groupNewPipelineStatus[$_groupId] ?? false;
            foreach ($configPipelineLangList as $_pipelineCode => $_pipelineInfo) {
                if (isset($_pipelineList[ $_pipelineCode ])) {
                    continue;
                }

                $groupNewPipelineStatus[ $_groupId ] = true;
                //补全新增渠道和语言信息
                $_pipelineList[ $_pipelineCode ] = [
                    'code'      => $_pipelineCode,
                    'name'      => $_pipelineInfo['name'],
                    'pid'       => '',
                    'page_id'   => static::NOT_EXIST_PAGE_ID,
                    'lang_list' => []
                ];

                $missLangList = &$_pipelineList[ $_pipelineCode ]['lang_list'];
                foreach ($_pipelineInfo['lang_list'] as $_missLangCode => $_missLangInfo) {
                    /** @var \app\modules\common\zf\models\PageLanguageModel $pageLanguageModel */
                    $pageLanguageModel = new PageLanguageModel();
                    $pageLanguageModel->id = 0;
                    $pageLanguageModel->page_id = static::NOT_EXIST_PAGE_ID;
                    $pageLanguageModel->lang = $_missLangCode;
                    $pageLanguageModel->group_id = $_groupId;
                    $pageLanguageModel->title = '';
                    $pageLanguageModel->seo_title = '';
                    $pageLanguageModel->keywords = '';
                    $pageLanguageModel->description = '';
                    $pageLanguageModel->tpl_id = 0;
                    $missLangList[ $_missLangCode ] = ArrayHelper::toArray($pageLanguageModel);
                    $missLangList[ $_missLangCode ]['lang_name'] = $_missLangInfo['name'];
                }
            }

        }

        return [$groupNewPipelineStatus, $groupLangList];
    }

    /**
     * 获取正在使用的首页
     *
     * @param     $siteCode
     * @param int $homeType
     *
     * @return array|mixed
     */
    public function getTopPage($siteCode, $homeType = 0)
    {
        $topPage = PageModel::find()->alias('h')
            ->select(
                'h.id,
                h.pid,
                h.home_type,
                h.group_id,
                h.pipeline,
                h.default_lang,
                a.realname as create_name,
                h.create_user,
                h.create_time,
                a2.realname as update_user,
                h.update_time,
                h.status,
                h.is_lock ,
                h.site_code,
                l.page_url,
                h.version'
            )
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'h.id = l.page_id')
            ->leftJoin(AdminModel::tableName() . ' a', 'h.create_user = a.username')
            ->leftJoin(AdminModel::tableName() . ' a2', 'h.update_user = a2.username')
            ->where([
                'h.status'      => static::HOME_PAGE_ONLINE_STATUS,
                'h.is_delete'   => PageModel::NOT_DELETE,
                'h.activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                'h.site_code'   => $siteCode,
                'h.home_type'   => $homeType
            ])
            ->orderBy('h.id DESC, l.id ASC')
            ->groupBy('h.group_id')
            ->asArray()
            ->one();

        //组装数据
        if (!empty($topPage) && \is_array($topPage)) {
            $pageLangList = PageLanguageModel::find()
                ->where(['page_id' => $topPage['id']])
                ->orderBy('id ASC')
                ->all();
            $pageLangList = ArrayHelper::toArray($pageLangList);
            $topPageList = $this->buildListData([$topPage], $pageLangList);
            if (!empty($topPageList)) {
                $pageGroupIds = array_column($topPageList, 'group_id');
                list($groupNewPipelineStatus, $groupLangList) = $this->getPageListGroupLanguages($siteCode, $pageGroupIds);
                $allGroupPageStatus = $this->getPageListGroupPageStatus($siteCode, $pageGroupIds);
                foreach ($topPageList as &$pageInfo) {
                    $pageInfo['has_new_pipeline'] = $groupNewPipelineStatus[ $pageInfo['group_id'] ] ?? false;
                    $pageInfo['group_status'] = $allGroupPageStatus[ $pageInfo['group_id'] ];
                    $pageInfo['group_languages'] = $groupLangList[ $pageInfo['group_id'] ] ?? [];
                }
            }

        }

        return empty($topPageList) ? [] : $topPageList;
    }

    /**
     * 三端合一, 新增首页
     *
     * @param array $params post参数
     *
     * @return array
     * @throws JsonResponseException
     * @throws Exception
     * @since v1.4.0
     */
    public function multiPlatformAdd(array $params)
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        $validPlatformParams = [];

        //首页活动支持PC和WAP端
        $supportPlatforms = app()->params['site'][SITE_GROUP_CODE]['platforms'] ?? [];
        foreach ($supportPlatforms as $platformCode) {
            //剔除不支持的端口
            if (!isset($params[ $platformCode ])) {
                continue;
            }

            $group_id = md5(microtime() . random_int(0, 100));
            $pipelineParams = json_decode($params[ $platformCode ], true);
            $siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
            $configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($siteCode);
            foreach ($pipelineParams as $pipelineCode => $pipelineData) {
                //剔除不支持的渠道
                if (!isset($configPipelineLangList[ $pipelineCode ])) {
                    unset($pipelineParams[ $pipelineCode ]);
                    continue;
                }

                $pipelineName = $configPipelineLangList[ $pipelineCode ]['name'];
                if (empty($pipelineData['default_lang'])) {
                    $errorMsg = sprintf("%s 渠道没有默认语言", $pipelineName);
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }

                if (empty($pipelineData['title'])) {
                    $errorMsg = sprintf("%s 名称为必填项", $pipelineName);
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }

                $pipelineParams[ $pipelineCode ]['group_id'] = $group_id;
                foreach ($pipelineData['list'] as $langCode => $pageLangInfo) {
                    //剔除不支持的语言
                    if (!isset($configPipelineLangList[ $pipelineCode ]['lang_list'][ $langCode ])) {
                        unset($pipelineParams[ $pipelineCode ]['list'][ $langCode ]);
                        continue;
                    }

                    $langName = $configPipelineLangList[$pipelineCode]['lang_list'][$langCode]['name'];
                    $pipelineParams[$pipelineCode]['list'][$langCode]['group_id'] = $group_id;
                    $pipelineParams[$pipelineCode]['list'][$langCode]['title'] = $pipelineData['title'];

                    if (empty($pageLangInfo['seo_title'])) {
                        $errorMsg = sprintf("%s-%s SEO标题为必填项", $pipelineName, $langName);
                        throw new JsonResponseException($this->codeFail, $errorMsg);
                    }
                }
            }

            $validPlatformParams[ $platformCode ] = $pipelineParams;
        }

        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            foreach ($validPlatformParams as $platformCode => $platformParams) {

                $siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
                foreach ($platformParams as $pipelineCode => $pipelineData) {

                    $pipelineData['page_id'] = ''; //新增留空
                    $pipelineData['version'] = 1;//默认初始版本为1
                    $pipelineData['site_code'] = $siteCode;
                    $pipelineData['pipeline'] = $pipelineCode;
                    $pipelineData['platform_code'] = $platformCode;

                    $result = $this->platformAdd($pipelineData);
                    if ($result['code'] == $this->codeFail) {
                        $message = sprintf("应用端口 %s 错误信息: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']);
                        throw new Exception($message);
                    }
                }
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '添加成功');
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 三端合一,新增单个平台首页业务逻辑参考 batchEdit
     *
     * @param array $params 参数
     *
     * @return array
     * @see   \app\modules\home\zf\components\PageComponent::batchEdit()
     * @since v1.4.0
     */
    private function platformAdd(array $params)
    {
        $siteCode = $params['site_code'];

        /** @var \app\modules\common\zf\models\PageModel $pageModel */
        $pageModel = new PageModel();
        $pageModel->create_user = app()->user->admin->username;
        $pageModel->create_time = $_SERVER['REQUEST_TIME'];
        $pageModel->type = $this->getTypeBySiteCode($siteCode);
        $pageModel->site_code = $siteCode;
        $pageModel->activity_id = SiteConstants::HOME_PAGE_ACTIVITY_ID;
        $pageModel->group_id = $params['group_id'];
        $pageModel->pipeline = $params['pipeline'];
        $pageModel->default_lang = $params['default_lang'];
        $pageModel->version = $params['version'];
        if (array_key_exists('home_type', $params)) {
            $pageModel->home_type = $params['home_type'];
        }

        return $this->doHomeBatchEdit($pageModel, $params);
    }

    /**
     * 批量编辑页面属性
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     * @throws Exception
     */
    public function batchEdit(array $params)
    {
        if (empty($params['site_code'])) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        if (empty($params['pipeline_list'])) {
            throw new JsonResponseException($this->codeFail, '无效的pipeline_list');
        }

        $pageIds = [];
        $siteCode = $params['site_code'];
        $configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($siteCode);
        $pipelineParams = json_decode($params['pipeline_list'], true);
        $version = isset($params['version']) ? $params['version'] : 0;

        if (empty($version)) {
            throw new JsonResponseException($this->codeFail, '没有数据版本');
        }

        foreach ($pipelineParams as $pipelineCode => $pipelineData) {
            //剔除不支持的渠道
            if (!isset($configPipelineLangList[ $pipelineCode ])) {
                unset($pipelineParams[ $pipelineCode ]);
                continue;
            }

            $pipelineName = $configPipelineLangList[ $pipelineCode ]['name'];

            if (empty($pipelineData['page_id']) || !is_numeric($pipelineData['page_id'])) {
                throw new JsonResponseException($this->codeFail, '无效的page_id');
            }

            if (empty($pipelineData['default_lang'])) {
                $errorMsg = sprintf("%s 没有默认语言", $pipelineName);
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            if (empty($pipelineData['title'])) {
                $errorMsg = sprintf("%s 名称为必填项", $pipelineName);
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            foreach ($pipelineData['list'] as $langCode => $pageLangInfo) {
                $pipelineParams[$pipelineCode]['list'][$langCode]['title'] = $pipelineData['title'];

                $langName = $configPipelineLangList[$pipelineCode]['lang_list'][$langCode]['name'];
                if (empty($pageLangInfo['seo_title'])) {
                    $errorMsg = sprintf("%s-%s SEO标题为必填项", $pipelineName, $langName);
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }
            }

            $pageIds[] = $pipelineData['page_id'];
        }

        if (empty($pipelineParams)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        $pageModelList = PageModel::find()->where([
            'site_code'   => $siteCode,
            'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
            'id'          => $pageIds
        ])->indexBy('id')->all();

        if (empty($pageModelList)) {
            throw new Exception($this->codeFail, '页面不存在');
        }

        $groupIds = [];
        /** @var \app\modules\common\zf\models\PageModel $pageModel */
        foreach ($pageModelList as $pageModel) {
            $groupIds[] = $pageModel->group_id;
            $oldVersion = $pageModel->version;
        }
        if (count(array_unique($groupIds)) > 1) {
            throw new Exception($this->codeFail, '多个渠道页面不在一个分组内!');
        }
        if ($version > $oldVersion) {
            throw new JsonResponseException($this->codeFail, '非法数据版本');
        }
        if ($version < $oldVersion) {
            throw new JsonResponseException($this->codeFail, '页面数据已过期,页面需刷新');
        }

        //事物开始
        $groupId = $groupIds[0];
        $groupPageModelList = PageModel::find()->where([
            'site_code'   => $siteCode,
            'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
            'group_id'    => $groupId
        ])->indexBy('pipeline')->all();

        /** @var PageModel $globalPageModel */
        $globalPageModel = reset($groupPageModelList) ?? null; // 全球站
        if (empty($globalPageModel)) {
            throw new Exception($this->codeFail, '没有找到全球站页面信息!');
        }

        $platformCode = SitePlatform::getPlatformCodeBySiteCode($siteCode);
        $transaction = app()->db->beginTransaction();
        try {

            foreach ($pipelineParams as $pipelineCode => $pipelineData) {
                $pageId = (int) $pipelineData['page_id'];
                $pipelineData['platform_code'] = strtolower($platformCode);

                //新加渠道,添加子页面信息
                if (static::NOT_EXIST_PAGE_ID === $pageId && !isset($groupPageModelList[$pipelineCode])) {
                    $pipelineData['page_id'] = ''; //新增留空
                    $pipelineData['site_code'] = $siteCode;
                    $pipelineData['pipeline'] = $pipelineCode;
                    $pipelineData['group_id'] = $groupId;
                    $pipelineData['version'] = $version;
                    $pipelineData['home_type'] = $globalPageModel->home_type;

                    foreach ($pipelineData['list'] as &$_langInfo) {
                        $_langInfo['group_id'] = $groupId;
                    }

                    $result = $this->   platformAdd($pipelineData);
                    if ($result['code'] == $this->codeFail) {
                        throw new Exception($result['message']);
                    }
                } else {
                    /** @var \app\modules\common\zf\models\PageModel $pageModel */
                    $pageModel = $groupPageModelList[$pipelineCode] ?? NULL;
                    if (!$pageModel) {
                        throw new Exception($this->codeFail, '页面不存在');
                    }

                    $pageModel->update_user = app()->user->admin->username;
                    $pageModel->update_time = $_SERVER['REQUEST_TIME'];
                    $pageModel->version = $version + 1;

                    foreach ($pipelineData['list'] as &$_langInfo) {
                        $_langInfo['group_id'] = $groupId;
                    }

                    $this->doHomeBatchEdit($pageModel, $pipelineData);
                }
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '修改成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 删除渠道下所有首页面
     *
     * @param array $params
     *
     * @return array
     */
    public function delPipelineAllHomePages($params)
    {
        if (empty($params['group_id'])) {
            return app()->helper->arrayResult($this->codeFail, '无效的渠道组ID');
        }

        $pageRowList = PageModel::find()->where([
            'is_delete'   => PageModel::NOT_DELETE,
            'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
            'group_id'    => $params['group_id'],
        ])->asArray()->all();
        if (empty($pageRowList)) {
            return app()->helper->arrayResult($this->codeFail, '页面不存在');
        }

        //检查页面是否加锁，并判断权限
        $pageRow = current($pageRowList);
        if ((PageModel::IS_LOCK === (int) $pageRow['is_lock']) && (app()->user->admin->is_super < 1)) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        foreach ($pageRowList as $pageRow) {
            if (in_array((int) $pageRow['status'], static::HOME_PAGE_ONLINE_STATUS, true)) {
                return app()->helper->arrayResult($this->codeFail, '当前渠道下有页面仍在线，请先做下线处理!');
            }
        }

        $rows = PageModel::updateAll(
            ['is_delete' => PageModel::IS_DELETE],
            ['activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID, 'group_id' => $params['group_id']]
        );

        if (0 == $rows) {
            return app()->helper->arrayResult($this->codeFail, '删除失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '删除成功');
    }

    /**
     * 加解锁渠道下所有首页面
     *
     * @param array $params
     *
     * @return array
     */
    public function lockPipelineAllHomePages($params)
    {
        if (empty($params['group_id'])) {
            return app()->helper->arrayResult($this->codeFail, '无效的渠道组ID');
        }

        $pageRowList = PageModel::find()->where([
            'is_delete'   => PageModel::NOT_DELETE,
            'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
            'group_id'    => $params['group_id'],
        ])->asArray()->all();
        if (empty($pageRowList)) {
            return app()->helper->arrayResult($this->codeFail, '页面不存在');
        }

        //检查页面是否加锁，并判断权限
        $pageRow = current($pageRowList);
        if ((app()->user->admin->is_super < 1) && (app()->user->admin->username !== $pageRow['create_user'])) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        $lockStatus = (int) $pageRow['is_lock'];
        if (PageModel::UN_LOCK === $lockStatus) {
            $actMsg = '加锁';
            $lockStatus = PageModel::IS_LOCK;
        } else {
            $actMsg = '解锁';
            $lockStatus = PageModel::UN_LOCK;
        }

        $rows = PageModel::updateAll(
            ['is_lock' => $lockStatus],
            ['activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID, 'group_id' => $params['group_id']]
        );

        if (0 == $rows) {
            return app()->helper->arrayResult($this->codeFail, $actMsg . '失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, $actMsg . '成功');
    }

    /**
     * @inheritdoc
     */
    public function getPipelinePageNewestUrls(int $pageId, int $needBtn = 1, int $isPreview = 0)
    {
        /** @var \app\modules\common\zf\models\PageModel $model */
        $model = PageModel::getById($pageId);
        if (!$model) {
            return app()->helper->arrayResult(1, '页面不存在');
        }

        $groupId = $model->group_id;
        $needBtn = empty($params['btn']) ? 1 : (int) $params['btn'];
        $pageRowList = PageModel::find()->where([
            'is_delete'   => PageModel::NOT_DELETE,
            'activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
            'group_id'    => $groupId,
        ])->asArray()->all();
        if (empty($pageRowList)) {
            throw new JsonResponseException($this->codeFail, '没有有效的活动页面');
        }

        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        $configAllPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($siteGroupCode);

        $pipelineUrls = [];
        $missLangList = [];
        foreach ($pageRowList as $pageRow) {
            try {
                $pageStatus = (int) $pageRow['status'];
                $publishedStatus = [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE];
                if (in_array($pageStatus, $publishedStatus, true)) {
                    $result = $this->getPageNewestUrls($pageRow['id'], $needBtn, $isPreview);
                    $pipelineName = $configAllPipelineList[ $pageRow['pipeline'] ] ?? '';
                    if (!empty($result['data']['list'])) {
                        $pipelineUrls['pipeline_list'][] = [
                            'code'      => $pageRow['pipeline'],
                            'name'      => $pipelineName,
                            'lang_list' => $result['data']['list']
                        ];
                    }

                    if (!empty($result['data']['miss'])) {
                        foreach ($result['data']['miss'] as $langName) {
                            $missLangList[] = $pipelineName . '-' . $langName;
                        }

                    }

                }
            } catch (\Exception $e) {
                throw new JsonResponseException($this->codeFail, '内部错误', null, [$e->getTraceAsString()]);
            }
        }

        if (!empty($missLangList)) {
            $pipelineUrls['tips'] = implode('、', $missLangList) . '页面还在推送中' . ($needBtn ? '，若长时间未成功，请' : '');
        }

        if (empty($pipelineUrls)) {
            throw new JsonResponseException($this->codeFail, '页面还未上线或下线过，无访问链接');
        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $pipelineUrls);
    }

    /**
     * 删除首页（目前没有使用，但是保留，以便以后要用删除渠道下单个页面）
     *
     * @param int $id
     *
     * @return array
     */
    public function delete(int $id)
    {
        $model = PageModel::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, '自定义页面不存在');
        }

        //检查页面是否加锁，并判断权限
        if (2 === (int) $model->is_lock && app()->user->admin->is_super < 1) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        // 先判断是否在线
        if ($model->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult(1, '页面仍在线，请先做下线处理');
        }

        return $this->doDelete($model);
    }

    /**
     * 首页加解锁（目前没有使用，但是保留，以便以后要用加解锁渠道下单个页面）
     *
     * @param int $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lock(int $id)
    {
        $model = PageModel::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, '自定义页面不存在');
        }

        //检查页面是否加锁，并判断权限
        if (app()->user->admin->is_super < 1 && app()->user->admin->username !== $model['create_user']) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        return $this->doLock($model);
    }

    /**
     * 根据站点编码获取活动类型
     *
     * @param string $siteCode 站点编码简称
     *
     * @return int
     */
    private function getTypeBySiteCode($siteCode)
    {
        return SitePlatform::getPlatformTypeBySiteCode($siteCode);
    }

    /**
     * 检查页面是否发布过
     *
     * @param PageModel $pageModel
     * @param array     $data
     *
     * @throws JsonResponseException
     */
    public function checkPagePublished($pageModel, &$data)
    {
        if ($pageModel->activity_id) {
            throw new JsonResponseException($this->codeFail, '活动页请勿调用首页接口', $data);
        }

        if (!in_array($pageModel->status, [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE])) {
            throw new JsonResponseException($this->codeFail, '页面还未设置过为首页，无访问链接', $data);
        }
    }

    /**
     * 获取domainKey
     */
    public function getDomainKey()
    {
        return 'home_secondary_domain';
    }

    /**
     * 获取页面和活动信息
     *
     * @param int $pageId
     *
     * @return PageModel|array|null
     */
    public function getPageActivityInfo(int $pageId)
    {
        return PageModel::findOne($pageId);
    }

    /**
     * 查看版本详情
     *
     * @param int    $pageId
     * @param string $version
     * @param string $lang
     *
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    public function viewVersion(int $pageId, string $version, string $lang)
    {
        $log = PagePublishLogModel::findOne(['page_id' => $pageId, 'version' => $version, 'lang' => $lang]);
        if (!$log) {
            return '未找到对应版本的发布记录';
        }

        $cache = PagePublishCacheModel::findOne(['page_id' => $pageId, 'version' => $version, 'lang' => $lang]);
        if (!$cache) {
            return '未找到对应版本的缓存记录';
        }

        $pageHtml = /** @lang html */
            $cache->html . '<style type="text/css">' . $cache->css . '</style>'
            . '<script defer="defer">' . $cache->js . '</script>';

        $cssVersion = app()->params['css_version'];
        $componentStatic['css'] = $this->getHeadExtraCss(
            $pageId,
            $lang,
            $cssVersion,
            $log['site_code'],
            SiteConstants::ACTIVITY_PAGE_TYPE_HOME,
            true
        );
        //解决站点组件JS未加载的问题
        $componentStatic['js'] = $this->getHeadExtraJs(
            $cssVersion,
            $lang,
            $log['site_code'],
            SiteConstants::ACTIVITY_PAGE_TYPE_HOME,
            true
        );

        $pageModel = PageModel::findOne($pageId);
        //获取头尾
        $result = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic, $pageModel->pipeline);

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(static::$dragClass, '', $pageHtml);
        //页面的content用div包起来
        $html = $this->packageContent($html);

        if (!empty($result)) {
            $main = '/<!--\s*geshop\s*main\s*start\s*-->/';
            preg_match($main, $result, $matches);
            if (!empty($matches[0])) {
                $html = str_replace($matches[0], $matches[0] . $html, $result);
            }
        }

        return $html;
    }

    /**
     * 回滚首页版本
     *
     * @param int $logId
     *
     * @return array
     */
    public function rollback(int $logId)
    {
        $publishLog = ArrayHelper::toArray(PagePublishLogModel::getById($logId));
        $errMsg = '回滚版本页面不存在';
        if (!empty($publishLog['local_path']) && !empty($publishLog['s3_url'])) {
            if (app()->s3->getObject($publishLog['local_path'])) {
                $localPath = pathinfo($publishLog['local_path']);
                $pageModel = PageModel::getById($publishLog['page_id']);
                if (PageModel::HOME_B == $pageModel->home_type) {
                    $copyPath = "{$localPath['dirname']}/index_new.{$localPath['extension']}";
                } else {
                    $copyPath = "{$localPath['dirname']}/index.{$localPath['extension']}";
                }

                $s3Res = app()->s3->copyObject($publishLog['s3_url'], $copyPath);
                if (!\is_string($s3Res)) {
                    $lockKey = app()->redisKey->getHomePageRollbackLockKey($publishLog['site_code']);
                    app()->redis->sadd($lockKey, $publishLog['page_id']);
                    $logModel = PagePublishLogModel::getById($logId);
                    $logModel->rollback_user = app()->user->username;
                    $logModel->rollback_time = $_SERVER['REQUEST_TIME'];
                    $logModel->save(true);

                    return app()->helper->arrayResult($this->codeSuccess, '首页回滚成功');
                } else {
                    $errMsg = "{$publishLog['s3_url']}页面回滚失败";
                }
            } else {
                $errMsg = "{$publishLog['s3_url']}页面不存在";
            }
        }

        return app()->helper->arrayResult($this->codeFail, $errMsg);
    }

    public function refreshPushStatus()
    {
        $transaction = app()->db->beginTransaction();
        try {
            $pages = PageModel::find()->select('id')->where(['activity_id' => 0, 'status' => [5, 6]])->asArray()->all();
            if (!empty($pages) && is_array($pages)) {
                $ids = array_column($pages, 'id');
                PageModel::updateAll(['status' => 1], ['id' => $ids]);
            }
            $pages = PageModel::find()->select('id')->where(['activity_id' => 0, 'status' => 4])->asArray()->all();
            if (!empty($pages) && is_array($pages)) {
                $ids = array_column($pages, 'id');
                PageModel::updateAll(['status' => 3], ['id' => $ids]);
            }
            $pages = PageModel::find()->select('id')->where(['activity_id' => 0, 'status' => [7, 8]])->asArray()->all();
            if (!empty($pages) && is_array($pages)) {
                $ids = array_column($pages, 'id');
                PageModel::updateAll(['status' => 2], ['id' => $ids]);
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '操作成功');
        } catch (\yii\db\Exception $exception) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, '操作失败');
        }
    }
}
