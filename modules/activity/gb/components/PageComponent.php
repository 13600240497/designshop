<?php

namespace app\modules\activity\gb\components;

use app\modules\common\gb\models\{
    ActivityModel, ActivityGroupModel, PageModel, PageGroupModel, PageLanguageModel, PageLayoutModel, PageSpecialModel, PageUiModel, PageUiComponentDataModel
};
use app\base\SiteConstants;
use app\modules\common\gb\components\CommonPageComponent;
use app\modules\activity\gb\traits\PublishTrait;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use app\base\SitePlatform;
use app\modules\soa\components\ObsComponent;

/**
 * 页面组件
 */
class PageComponent extends CommonPageComponent
{

    /** @var array Banner UI组件key */
    const BANNER_UI_COMPONENT_KEY = [
        SitePlatform::PLATFORM_CODE_PC  => 'U000025',
        SitePlatform::PLATFORM_CODE_WAP => 'U000026',
        SitePlatform::PLATFORM_CODE_APP => 'U000026'
    ];

    /** @var string 子页面默认预览图片地址 */
    const PAGE_DEFAULT_PREVIEW_PIC_URL = '/resources/images/default/banner_default.png';


    /**
     * 页面列表
     *
     * @param $params
     *
     * @return array
     */
    public function lists($params)
    {
        if (!$params['activity_id']) {
            return app()->helper->arrayResult(1, 'activity_id不能为空');
        }
        $lang = '';
        $langList = [];
        $langConf = app()->params['lang'];
        $activity = ActivityModel::findOne($params['activity_id']);
        if ($activity && !empty($activity->pipeline)) {
            $site = SitePlatform::getSiteBySiteCode($activity->site_code);
            $configAllPipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
            $pipelineGroup = ActivityGroupModel::findOne($activity->group_id);
            $i = 0;
            foreach (json_decode($pipelineGroup->lang_list, true) as $key => $item) {
                $langList[ $i ] = [
                    'key'  => $key,
                    'name' => $configAllPipeline[ $key ] ?? ''
                ];
                foreach ($item as $row) {
                    $langList[ $i ]['langList'][] = [
                        'key'  => $row,
                        'name' => isset($langConf[ $row ]['name']) ? $langConf[ $row ]['name'] : ''
                    ];
                    $lang = $lang ?? $row;
                }
                $i++;
            }
        }

        $pageList = $this->pageLists(new PageModel(), $params);
        $data = [];
        if ($pageList) {
            $pageList = ArrayHelper::toArray($pageList);
            $pageIds = array_column($pageList, 'id');

            $pageLangList = PageLanguageModel::find()->alias('pl')
                ->where(['pl.page_id' => $pageIds])
                ->orderBy('pl.id asc')
                ->all();
            if ($pageLangList) {
                $data = $this->buildListData($pageList, ArrayHelper::toArray($pageLangList), $activity);
                //获取子页面活动组语言列表
                list($pageListGroupInfoList, $pageListGroupLanguages) = $this->getPageListGroupInfoAndLanguages(
                    $activity, $pageIds, $pageLangList
                );
                //获取子页面预览图片地址，获取第一个Banner UI组件的图片地址，没有则使用默认图片
                $pagePreviewPicUrls = $this->getPageFirstBannerPicUrls($pageIds, $activity->site_code, $lang);
                foreach ($data as &$_pageInfo) {
                    if (isset($pageListGroupInfoList[ $_pageInfo['id'] ]))
                        $_pageInfo['group_info'] = $pageListGroupInfoList[ $_pageInfo['id'] ];
                    $_pageInfo['group_languages'] = $pageListGroupLanguages[ $_pageInfo['id'] ];
                    $_pageInfo['preview_pic_url'] = $pagePreviewPicUrls[ $_pageInfo['id'] ];
                }
            }
        }
        $themeList = (new ObsComponent())->getThemeByActivity($params['activity_id']); //obs选择活动

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list'      => $data,
                'langList'  => $langList,
                'themeList' => $themeList
            ]
        );
    }

    /**
     * 获取子页面列表分组信息
     *
     * @param \app\modules\common\gb\models\ActivityModel $activity
     * @param array                                       $pageIds
     * @param array                                       $pageLangList
     *
     * @return array
     */
    private function getPageListGroupInfoAndLanguages($activity, $pageIds, $pageLangList)
    {
        $pageGroupInfo = [];
        $pageGroupLanguages = [];
        $groupPageLangMap = [];
        $groupPageMap = [];

        //子页面分组信息
        $pageToGroupMap = PageGroupModel::find()->where(['page_id' => $pageIds])->indexBy('page_id')->asArray()->all();
        if (!empty($pageToGroupMap)) {
            $pageGroupIds = array_column($pageToGroupMap, 'page_group_id');
            $pageGroupModelList = PageGroupModel::find()->where(['page_group_id' => $pageGroupIds])->asArray()->all();
            $allPageIds = array_column($pageGroupModelList, 'page_id');
            $otherPageIds = array_diff($allPageIds, $pageIds);

            foreach ($pageGroupModelList as $pageGroupModel) {
                $groupPageMap[ $pageGroupModel['page_group_id'] ][ $pageGroupModel['page_id'] ] = $pageGroupModel;
            }

            $otherPageLangList = PageLanguageModel::find()->alias('pl')
                ->where(['pl.page_id' => $otherPageIds])
                ->orderBy('pl.id asc')
                ->all();

            $allPageLangList = ArrayHelper::merge(
                ArrayHelper::toArray($pageLangList),
                ArrayHelper::toArray($otherPageLangList)
            );
        } else {
            $allPageLangList = ArrayHelper::toArray($pageLangList);
        }

        foreach ($allPageLangList as $langInfo) {
            $langInfo['langName'] = isset(app()->params['lang'][ $langInfo['lang'] ]['name'])
                ? app()->params['lang'][ $langInfo['lang'] ]['name']
                : '';

            $langInfo['share'] = $langInfo['share'] ? \GuzzleHttp\json_decode($langInfo['share'], true) : [];
            $groupPageLangMap[ $langInfo['page_id'] ][ $langInfo['lang'] ] = $langInfo;
        }
        $site = SitePlatform::getSiteBySiteCode(SitePlatform::getCurrentSiteGroupDefaultSiteCode());
        $configAllPipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
        foreach ($pageIds as $pageId) {
            if (isset($pageToGroupMap[ $pageId ])) {
                $pageGroupId = $pageToGroupMap[ $pageId ]['page_group_id'];
                $pageGroupInfo[ $pageId ] = [
                    'activity_group_id' => $pageToGroupMap[ $pageId ]['activity_group_id'],
                    'page_group_id'     => $pageGroupId,
                    'platform_list'     => []
                ];

                foreach ($groupPageMap[ $pageGroupId ] as $_groupInfo) {
                    $platformCode = SitePlatform::getPlatformCodeByPlatformType($_groupInfo['platform_type']);
                    $pageGroupLanguages[ $pageId ][ $platformCode ][ $_groupInfo['pipeline'] ] = [
                        'key'      => $_groupInfo['pipeline'],
                        'name'     => $configAllPipeline[ $_groupInfo['pipeline'] ] ?? '',
                        'language' => $groupPageLangMap[ $_groupInfo['page_id'] ],
                    ];
                    $pageGroupInfo[ $pageId ]['platform_list'][ $platformCode ][ $_groupInfo['pipeline'] ] = ['page_id' => $_groupInfo['page_id']];
                }
            }
        }

        return [$pageGroupInfo, $pageGroupLanguages];
    }

    /**
     * 三端合一, 新增子页面
     *
     * @param array $params post参数
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupAdd(array $params)
    {
        $nowTime = time();

        //公共数据检查
        if (empty($params['activity_id']) || !is_numeric($params['activity_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的活动ID');
        }

        if (($params['end_time'] > 0) && ($params['end_time'] <= $nowTime)) {
            throw new JsonResponseException($this->codeFail, '下线时间无效');
        }

        if (empty($params['lang_list'])) {
            throw new JsonResponseException($this->codeFail, '没有渠道数据');
        }
        if (empty($params['platForm'])) {
            throw new JsonResponseException($this->codeFail, '没有应用端数据');
        }

        $postLangDataList = json_decode($params['lang_list'], true);
        if (!is_array($postLangDataList) || empty($postLangDataList)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        //检查活动是否存在
        /** @var \app\modules\common\models\ActivityModel $activityModel */
        $activityModel = ActivityModel::find()
            ->where([
                'id'        => $params['activity_id'],
                'is_delete' => ActivityModel::NOT_DELETE
            ])->one();
        if (!$activityModel) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        $validPlatformParams = $this->checkAndBuildData($activityModel, 0, $params, $postLangDataList);

        unset($postLangDataList);

        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        $addedPageList = [];
        $pageGroupId = PageGroupModel::generatePageGroupId();

        try {
            $pageSpecialModel = new PageSpecialModel();
            $pageSpecialModel->page_group_id = $pageGroupId;
            if (!$pageSpecialModel->insert(true)) {
                throw new Exception('添加子页面专题数据失败');
            }

            //保存数据
            foreach ($validPlatformParams as $platformCode => $platformParams) {

                if (empty($platformParams)) {
                    continue;
                }

                foreach ($platformParams as $pipeline => $pipelineParams) {
                    if (empty($pipelineParams['list']) || empty($pipelineParams['page_model'])) {
                        continue;
                    }
                    $this->beforeSaveProcessor($validPlatformParams, $pipelineParams, $pipeline, $pageSpecialModel->id);
                    //保存子页面数据
                    /** @var \app\modules\common\models\PageModel $pageModel */
                    $pageModel = $pipelineParams['page_model'];
                    unset($pipelineParams['page_model']);

                    $pageModel->site_code = $pipelineParams['site_code'] ?? '';
                    $pageModel->refresh_time = $pipelineParams['refresh_time'];
                    $pageModel->end_time = $pipelineParams['end_time'];
                    $pageModel->pipeline = $pipeline;
                    $pageModel->group_id = $pipelineParams['group_id'];

                    $result = $this->doActivityBatchEdit($pageModel, $pipelineParams);
                    if ($result['code'] == $this->codeFail) {
                        $errorMessage = sprintf(
                            "应用端口 %s 添加失败: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']
                        );
                        throw new Exception($errorMessage);
                    }
                    //保存页面分组
                    /** @var \app\modules\common\models\PageGroupModel $pageGroupModel */
                    $pageGroupModel = new PageGroupModel();
                    $pageGroupModel->activity_group_id = $activityModel->group_id;
                    $pageGroupModel->page_group_id = $pageGroupId;
                    $pageGroupModel->platform_type = SitePlatform::getPlatformTypeByPlatformCode($platformCode);
                    $pageGroupModel->page_id = $pageModel->id;
                    $pageGroupModel->pipeline = $pipeline;
                    $pageGroupModel->special_id = $pageSpecialModel->id;
                    if (!$pageGroupModel->insert(true)) {
                        throw new Exception('添加子页面分组失败');
                    }
                    $this->beforeSaveObs($pipelineParams, [
                        $pageModel->id,
                        SitePlatform::getPlatformTypeByPlatformCode($platformCode),
                        $params['activity_id'],
                        SitePlatform::getSiteCodeByPlatformCode($platformCode),
                        $pipeline
                    ]);
                    $addedPageList[ $platformCode ] = ['page_id' => $pageModel->id];
                }
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '添加成功', $addedPageList);
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 三端合一, 编辑子页面
     *
     * @param array $params post参数
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.6.0
     */
    public function groupEdit(array $params)
    {
        $nowTime = time();

        //公共数据检查
        if (empty($params['page_id']) || !is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的子页面ID');
        }

        if (($params['end_time'] > 0) && ($params['end_time'] <= $nowTime)) {
            throw new JsonResponseException($this->codeFail, '下线时间无效');
        }

        if (empty($params['platForm'])) {
            throw new JsonResponseException($this->codeFail, '没有应用端数据');
        }

        $postLangDataList = json_decode($params['lang_list'], true);
        if (!is_array($postLangDataList) || empty($postLangDataList)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        //检查活动是否存在
        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::findOne([
            'id'        => $params['page_id'],
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '子页面不存在');
        }

        /** @var \app\modules\common\models\ActivityModel $activityModel */
        $activityModel = ActivityModel::find()
            ->where([
                'id'        => $pageModel->activity_id,
                'is_delete' => ActivityModel::NOT_DELETE
            ])->one();

        $validPlatformParams = $this->checkAndBuildData($activityModel, $params['page_id'], $params, $postLangDataList);
        unset($postLangDataList);

        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            //保存数据
            foreach ($validPlatformParams as $platformCode => $platformParams) {

                if (empty($platformParams)) {
                    continue;
                }

                foreach ($platformParams as $pipeline => $pipelineParams) {
                    if (empty($pipelineParams['list'])) {
                        continue;
                    }

                    $pageSpecialId = PageGroupModel::findOne(['page_id' => $pipelineParams['page_id']])->getAttribute('special_id');
                    $this->beforeSaveProcessor($validPlatformParams, $pipelineParams, $pipeline, $pageSpecialId);
                    //保存子页面数据
                    /** @var \app\modules\common\models\PageModel $pageModel */
                    $pageModel = $pipelineParams['page_model'];
                    unset($pipelineParams['page_model']);

                    $pageModel->end_time = $pipelineParams['end_time'];
                    $pageModel->pipeline = $pipeline;

                    $result = $this->doActivityBatchEdit($pageModel, $pipelineParams);
                    if ($result['code'] == $this->codeFail) {
                        $errorMessage = sprintf(
                            "应用端口 %s 修改失败: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']
                        );
                        throw new Exception($errorMessage);
                    }
                    $this->beforeSaveObs($pipelineParams,
                        [
                            $pipelineParams['page_id'],
                            SitePlatform::getPlatformTypeByPlatformCode($platformCode),
                            $pageModel->activity_id,
                            SitePlatform::getSiteCodeByPlatformCode($platformCode),
                            $pipeline,
                        ]
                    );
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
     * 子页面数据保存前处理
     *
     * @param        $validPlatformParams
     * @param        $platformParams
     * @param string $pipeline
     * @param int    $specialId
     */
    private function beforeSaveProcessor($validPlatformParams, &$platformParams, $pipeline = '', $specialId = 0)
    {
        //关联PC跳M链接(redirect_url)
        if (SitePlatform::isPcPlatform($platformParams['site_code'])
            && isset($validPlatformParams[ SitePlatform::PLATFORM_CODE_WAP ][ $pipeline ])
        ) {
            $wapSiteCode = $validPlatformParams[ SitePlatform::PLATFORM_CODE_WAP ][ $pipeline ]['site_code'];
            $allSupportLanguages = app()->params['sites'][ $wapSiteCode ]['secondary_domain'][ $pipeline ] ?? [];
            foreach ($platformParams['list'] as $lang => $langData) {
                $mobileActivityUrl = '';
                $validParams = $validPlatformParams[ SitePlatform::PLATFORM_CODE_WAP ][ $pipeline ];
                if (isset($allSupportLanguages[ $lang ])) {
                    $mobileActivityUrl = trim($allSupportLanguages[ $lang ], '/') . '/';
                    $mobileActivityUrl .= $validParams['list'][ $lang ]['url_name'];
                    if ($specialId > 0) {
                        $mobileActivityUrl .= "-special-{$specialId}.html";
                    } else {
                        $mobileActivityUrl .= '.html';
                    }
                }
                $platformParams['list'][ $lang ]['redirect_url'] = $mobileActivityUrl;
            }

            //APP端没有活动结束跳转链接(end_url)
            if (SitePlatform::isAppPlatform($platformParams['site_code'])) {
                foreach ($platformParams['list'] as $lang => $langData) {
                    $platformParams['list'][ $lang ]['end_url'] = '';
                }
            }
        }
    }

    /**
     * 保存obs关系
     *
     * @param array  $platformParams
     * @param  array $params
     *  -   page_id      页面ID
     *  -   platform     平台ID
     *  -   activity_id  活动ID
     */
    private function beforeSaveObs($platformParams, $params)
    {
        $ObsComponent = new ObsComponent();
        list($page_id, $platform, $activity_id, $site_code, $pipeline) = $params;

        foreach ($platformParams['list'] as $lang => $value) {
            //保存obs对应关系
            if (isset($value['obsId']) && isset($value['obsName'])) {//obs关联活动

                $ObsComponent->savePageData([
                    $page_id,
                    $lang,
                    $value['obsId'],
                    $value['obsName'],
                    $platform,
                    $activity_id,
                    $site_code,
                    $pipeline
                ]);
            }
        }
    }


    /**
     * 添加和编辑公共函数
     *
     * @param \app\modules\common\models\ActivityModel $activityModel
     * @param int                                      $pageId 0走添加逻辑,大于0走编辑逻辑
     * @param array                                    $params
     * @param array                                    $postLangDataList
     *
     * @return array
     * @throws JsonResponseException
     */
    private function checkAndBuildData($activityModel, $pageId, $params, $postLangDataList)
    {
        $isModify = $pageId > 0; //是否编辑
        $activityGroupId = $activityModel->group_id;
        $supportPlatformList = null;
        $supportLangList = null;
        $groupActivityModelMap = [];
        $pageGroupInfoMap = [];

        //大于0表示有多个端口，0单端口
        if ($activityGroupId > 0) {
            /** @var \app\modules\common\models\ActivityGroupModel $activityGroupModel */
            $activityGroupModel = ActivityGroupModel::getById($activityGroupId);
            $supportPlatformList = explode(SiteConstants::CHAR_COMMA, $activityGroupModel->platform_list);
            $supportLangList = json_decode($activityGroupModel->lang_list, true);
            $activityModelList = ActivityModel::getActivitiesByGroupId($activityGroupId);
            if (count($supportPlatformList) != count($activityModelList)) {
                throw new JsonResponseException($this->codeFail, '活动组数据不一致');
            }

            /** @var \app\modules\common\models\ActivityModel $activityModel */
            foreach ($activityModelList as $_activityModel) {
                $platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
                $groupActivityModelMap[ $platformCode ] = $_activityModel;
            }

            //编辑模式
            if ($isModify) {
                $pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageId);
                if ($pageGroupId) {
                    $pageGroupModelList = PageGroupModel::getPageListByPageGroupId($pageGroupId);
                    $pageGroupInfoList = ArrayHelper::toArray($pageGroupModelList);
                    foreach ($pageGroupInfoList as $pageGroupInfo) {
                        $_code = SitePlatform::getPlatformCodeByPlatformType($pageGroupInfo['platform_type']);
                        $pageGroupInfoMap[ $_code ][ $pageGroupInfo['pipeline'] ] = $pageGroupInfo;
                    }
                }
            }

        }

        //组装数据
        $allPlatformParams = [];
        foreach ($supportPlatformList as $platformCode) {
            if (strpos($params['platForm'], $platformCode) === false) {
                continue;
            }
            $_activityModel = $groupActivityModelMap[ $platformCode ];
            $group_id = md5(microtime() . random_int(0, 100));
            foreach ($supportLangList as $pipeline => $lang) {
                if (empty($pageGroupInfoMap[ $platformCode ][ $pipeline ]) && $isModify) {
                    continue;
                }
                $allPlatformParams[ $platformCode ][ $pipeline ] = [
                    'site_code'    => SitePlatform::getSiteCodeByPlatformCode($platformCode),
                    'activity_id'  => $_activityModel->id,
                    'page_id'      => $isModify ? $pageGroupInfoMap[ $platformCode ][ $pipeline ]['page_id'] : 0,
                    'refresh_time' => 0,
                    'end_time'     => !empty($params['end_time']) ? $params['end_time'] : 0,
                    'list'         => []
                ];
                if (!$isModify) {
                    $allPlatformParams[ $platformCode ][ $pipeline ]['group_id'] = $group_id;
                }
            }
        }
        $langDataFieldName = ['title', 'url_name', 'seo_title', 'keywords', 'description', 'statistics_code', 'obsId', 'obsName', 'redirect_url', 'share'];
        foreach ($postLangDataList as $pipeline => $pipelineData) {
            //剔除不支持的渠道
            if (!isset($supportLangList[ $pipeline ])) {
                continue;
            }
            $defaultLang = $pipelineData['defaultLang'];
            foreach ($pipelineData['languages'] as $lang => $langData) {
                //剔除不支持的语言
                if (!in_array($lang, $supportLangList[ $pipeline ], true)) {
                    continue;
                }
                foreach ($supportPlatformList as $platformCode) {
                    if (empty($allPlatformParams[ $platformCode ][ $pipeline ]) && $isModify) {
                        continue;
                    }
                    $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ]['default'] = $lang == $defaultLang ? 1 : 0;
                    $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ]['pipeline'] = $pipeline;

                    foreach ($langData as $key => $value) {
                        if (in_array($key, $langDataFieldName, true)) {
                            $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ][ $key ] = $value ?? $postLangDataList[ $pipeline ]['languages'][ $defaultLang ][ $key ];
                            $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ][ $key ] = is_array($allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ][ $key ]) ?
                                json_encode($allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ][ $key ]) : $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ][ $key ];
                        }
                    }

                    // 去除url_name中的空格字符
                    $urlName = $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ]['url_name'];
                    $allPlatformParams[ $platformCode ][ $pipeline ]['list'][ $lang ]['url_name'] = preg_replace('/\s+/', '', $urlName);
                }

                foreach ($langData['platform'] as $langPlatformCode => $langPlatformData) {
                    if (empty($allPlatformParams[ $langPlatformCode ][ $pipeline ]) && $isModify) {
                        continue;
                    }
                    $allPlatformParams[ $langPlatformCode ][ $pipeline ]['list'][ $lang ]['end_url'] = $langPlatformData['end_url'] ?? '';
                    if (0 === (int) $pageId) {
                        $allPlatformParams[ $langPlatformCode ][ $pipeline ]['list'][ $lang ]['tpl_id'] = $langPlatformData['tpl_id'] ?? 0;
                    }

                }
            }
        }
        unset($postLangDataList);

        //验证数据
        $validPlatformParams = [];
        foreach ($allPlatformParams as $platformCode => $platformParams) {
            $siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
            $configSitePipeline = app()->params['sites'][ $siteCode ]['secondary_domain'] ?? [];
            /** @var \app\modules\common\models\ActivityModel $_activityModel */
            $_activityModel = $groupActivityModelMap[ $platformCode ];
            $activitySupportPipelineKeys = explode(',', $_activityModel->pipeline);
            foreach ($platformParams as $pipeline => $pipelineInfo) {
                //----检查参数合法性-----
                if (!\in_array($pipelineInfo['refresh_time'], array_column($this->refreshList, 'key'), true)) {
                    $errorMsg = sprintf("应用端口 %s refresh_time不符合规范", SitePlatform::getPlatformNameByCode($platformCode));
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }
                //剔除活动不支持的渠道
                if (!in_array($pipeline, $activitySupportPipelineKeys, true)) {
                    unset($platformParams[ $pipeline ]);
                    continue;
                }
                foreach ($pipelineInfo['list'] as $langKey => $pageLangInfo) {
                    //剔除不支持的语言
                    if (!isset($configSitePipeline[ $pipeline ][ $langKey ])) {
                        unset($platformParams[ $pipeline ]['list'][ $langKey ]);
                        unset($pipelineInfo['list'][ $langKey ]);
                        continue;
                    }
                    if (empty($pageLangInfo['title'])) {
                        $errorMsg = sprintf("应用端口 %s 名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
                        throw new JsonResponseException($this->codeFail, $errorMsg);
                    }
                    if (!$isModify) {
                        $platformParams[ $pipeline ]['list'][ $langKey ]['group_id'] = $pipelineInfo['group_id'];
                    }
                }
                try {
                    $checkResult = $this->checkBatchPageData($pipelineInfo['activity_id'], $pipelineInfo['page_id'], $pipelineInfo['list'], $pipeline);
                    $platformParams[ $pipeline ]['page_model'] = $checkResult['page'];
                } catch (JsonResponseException $e) {
                    $errorMsg = sprintf("应用端口 %s %s", SitePlatform::getPlatformNameByCode($platformCode), $e->getMessage());
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }
                //通过验证，要保存的数据
                $validPlatformParams[ $platformCode ] = $platformParams;
            }
        }

        return $validPlatformParams;
    }

    /**
     * 批量编辑页面属性
     *
     * @param $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function batchEdit($params)
    {
        $params['list'] = !empty($params['data']) ? json_decode($params['data'], true) : [];
        $params['activity_id'] = !empty($params['activity_id']) ? (int) $params['activity_id'] : 0;
        $params['page_id'] = !empty($params['page_id']) ? (int) $params['page_id'] : 0;
        $params['refresh_time'] = !empty($params['refresh_time']) ? (int) $params['refresh_time'] : 0;
        $params['end_time'] = !empty($params['end_time']) ? (int) $params['end_time'] : 0;

        if (!$params['activity_id'] || empty($params['list']) || empty($params['url_name'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        $nowTime = time();
        if (($params['end_time'] > 0) && ($params['end_time'] <= $nowTime)) {
            throw new JsonResponseException($this->codeFail, '下线时间无效');
        }

        if (!\in_array($params['refresh_time'], array_column($this->refreshList, 'key'), true)) {
            throw new JsonResponseException($this->codeFail, 'refresh_time不符合规范');
        }

        $checkRes = $this->checkBatchPageData($params['activity_id'], $params['page_id'], $params['list'], $params['pipeline']);
        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = $checkRes['page'];
        $pageModel->site_code = $checkRes['activity']->site_code; // site_code要从activity表中去取，不能依靠前端传参
        $pageModel->refresh_time = $params['refresh_time'];
        $pageModel->end_time = $params['end_time'];

        return $this->doActivityBatchEdit($pageModel, $params);
    }

    /**
     * 删除自定义页面
     *
     * @param int  $id
     * @param bool $runValidation
     *
     * @return array
     */
    public function delete($id, $runValidation = true)
    {
        $model = PageModel::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, '自定义页面不存在');
        }

        //检查活动是否加锁，并判断权限
        $activityInfo = ActivityModel::getActivityInfo($model->activity_id);
        if (false === ActivityModel::checkAuth($activityInfo)) {
            return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
        }

        // 先判断是否在线
        if ($model->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult(1, '页面仍在线，请先做下线处理');
        }

        return $this->doDelete($model, $runValidation);
    }

    /**
     * 批量删除自定义页面
     *
     * @param string $ids
     *
     * @return array
     */
    public function batchDelete($ids)
    {
        $pageIds = explode(',', $ids);
        //开启事务
        foreach ($pageIds as $pageId) {
            $model = PageModel::getById($pageId);
            if (!$model) {
                return app()->helper->arrayResult(1, "自定义页面{$pageId}不存在");
            }

            //检查活动是否加锁，并判断权限
            $activityInfo = ActivityModel::getActivityInfo($model->activity_id);
            if (false === ActivityModel::checkAuth($activityInfo)) {
                return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
            }

            // 先判断是否在线
            if ($model->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
                return app()->helper->arrayResult(1, '页面仍在线，请先做下线处理');
            }
        }

        return $this->doBatchDelete($ids);
    }

    /**
     * 获取多个页面下第一个Banner组件的图片地址，没有使用默认图片
     *
     * @param array  $pageIds  子页面id数组
     * @param string $siteCode 站点简码
     * @param string $lang     语言简码
     *
     * @return array
     */
    private function getPageFirstBannerPicUrls($pageIds, $siteCode, $lang)
    {
        //查询多个页面所有布局
        $pagesLayoutList = PageLayoutModel::find()
            ->where(['page_id' => $pageIds, 'lang' => $lang])
            ->indexBy('id')
            ->asArray()
            ->all();

        $pageBannerUrls = [];
        $pageLayoutPositionUiList = [];
        if (!empty($pagesLayoutList)) {
            $layoutIds = array_keys($pagesLayoutList);
            $bannerComponentIds = [];
            $uiComponentKey = $this->getSiteBannerUiComponetKeyBySiteCode($siteCode);

            //查询布局下的UI组件，按照position排序
            $uiList = PageUiModel::find()
                ->where(['layout_id' => $layoutIds, 'lang' => $lang])
                ->orderBy('position asc')
                ->asArray()
                ->all();

            if (!empty($uiList)) {
                //按页面分组并排序layout组件
                $pageOrderedLayoutList = [];
                foreach ($pagesLayoutList as $_layout) {
                    $pageOrderedLayoutList[ $_layout['page_id'] ][] = $_layout;
                }

                foreach ($pageOrderedLayoutList as $_pageId => $_layoutList) {
                    $_orderedLayoutList = $this->getOrderedComponents($_layoutList);
                    foreach ($_orderedLayoutList as $_layout) {
                        $pageLayoutPositionUiList[ $_pageId ][ $_layout['id'] ] = [];
                    }
                }

                //按页面组件及栏位置分组UI组件
                foreach ($uiList as $ui) {
                    $pageId = $pagesLayoutList[ $ui['layout_id'] ]['page_id'];
                    $bannerComponentIds[] = $ui['id'];
                    $pageLayoutPositionUiList[ $pageId ][ $ui['layout_id'] ][ $ui['position'] ][] = $ui;
                }

                //查询对应的组件imgSrc数据
                $uiDataList = PageUiComponentDataModel::find()
                    ->select('component_id, value')
                    ->where(['component_id' => $bannerComponentIds, 'key' => 'imgSrc'])
                    ->asArray()
                    ->all();

                $uiDataMap = array_column($uiDataList, 'value', 'component_id');

                //排序并查找banner组件
                foreach ($pageLayoutPositionUiList as $_pageId => $_orderedLayoutList) {
                    foreach ($_orderedLayoutList as $_layoutId => $_positionList) {
                        foreach ($_positionList as $_uiList) {
                            $_orderedUiList = $this->getOrderedComponents($_uiList);
                            foreach ($_orderedUiList as $_ui) {
                                if (($uiComponentKey == $_ui['component_key']) && !empty($uiDataMap[ $_ui['id'] ])) {
                                    $_picUrl = json_decode($uiDataMap[ $_ui['id'] ]);
                                    if (!empty($_picUrl)) {
                                        $pageBannerUrls[ $_pageId ] = $_picUrl;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }


        }

        foreach ($pageIds as $_pageId) {
            if (empty($pageBannerUrls[ $_pageId ])) {
                $pageBannerUrls[ $_pageId ] = static::PAGE_DEFAULT_PREVIEW_PIC_URL;
            }
        }

        return $pageBannerUrls;
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
        if (!$pageModel->activity_id) {
            throw new JsonResponseException($this->codeFail, '首页请勿调用活动页接口', $data);
        }

        if ($pageModel->status !== PageModel::PAGE_STATUS_HAS_ONLINE
            && $pageModel->status !== PageModel::PAGE_STATUS_HAS_OFFLINE
        ) {
            throw new JsonResponseException($this->codeFail, '页面还未上线或下线过，无访问链接', $data);
        }
    }

    /**
     * 根据站点简码获取Banner组件key
     *
     * @param string $siteCode
     *
     * @return string UI组件key，NULL如果没有对应站点的组件
     */
    private function getSiteBannerUiComponetKeyBySiteCode($siteCode)
    {
        $platformCode = SitePlatform::getPlatformCodeBySiteCode($siteCode);
        if (empty($platformCode))
            return null;

        return static::BANNER_UI_COMPONENT_KEY[ $platformCode ] ?? null;
    }

    /**
     * 获取domainKey
     */
    public function getDomainKey()
    {
        return 'secondary_domain';
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
        return PageModel::getPageActivityInfo($pageId);
    }

    /**
     * 获取渠道列表
     *
     * @param int $pageId
     *
     * @return PageModel|array|null
     */
    public function getPipelineList(string $group_id)
    {
        return PageLanguageModel::getPipelineList($group_id);
    }
}
