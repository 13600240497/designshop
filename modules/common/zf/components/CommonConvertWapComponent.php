<?php

namespace app\modules\common\zf\components;

use app\modules\common\zf\models\{
    ActivityModel,
    ActivityGroupModel,
    PageLanguageModel,
    PageLayoutModel,
    PageModel,
    PageUiComponentDataModel,
    PageUiDataModel,
    PageUiModel
};
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use yii\base\Exception;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use app\base\SiteConstants;
use yii\helpers\Url;
use app\modules\common\zf\traits\CommonPublishTrait;
use app\modules\common\zf\traits\CommonConvertTrait;
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\base\components\AccessLogComponent;

class CommonConvertWapComponent extends Component
{
    use CommonPublishTrait, CommonConvertTrait;

    /**
     * 转换模式|1-追加
     */
    const CONVERT_MODEL_APPEND = 1;

    /**
     * 转换模式|2-覆盖
     */
    const CONVERT_MODEL_COVER = 2;

    /**
     * Wap端默认layout组件key
     */
    const DEFAULT_LAYOUT_KEY = 'L000019';

    /**
     * Wap端默认layout组件position
     */
    const DEFAULT_LAYOUT_POSITION = 1;

    /**
     * UI组件转换后id对应关系
     */
    private $convertUiRelation = [];

    /**
     * 获取当前站点下当前用户创建的所有Wap活动
     *
     * @param int $activityType 活动类型，默认专题活动
     *
     * @return array
     */
    public function getCreatorWapLists($activityType=SiteConstants::ACTIVITY_TYPE_SPECIAL)
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        $wapSiteCode = SitePlatform::getWapPlatformSiteCode($siteGroupCode);
        if (empty($wapSiteCode)) {
            return app()->helper->arrayResult($this->codeFail, 'Wap站点不存在');
        }

        $activityList = ActivityModel::find()->alias('a')
            ->leftJoin(ActivityGroupModel::tableName() . ' as g', 'g.id = a.group_id')
            ->select('a.id, a.name')
            ->where([
                'type'        => ActivityModel::TYPE_MOBILE,
                'site_code'   => $wapSiteCode,
                'mold'        => $activityType,
                'is_delete'   => ActivityModel::NOT_DELETE
            ])
            ->andWhere('a.create_user = "' . app()->user->admin->username . '" OR a.is_lock = ' . ActivityModel::UN_LOCK)
            ->andWhere(['g.platform_list' => SitePlatform::PLATFORM_CODE_WAP]) //过滤只有WAP端口的活动
            ->orderBy(['a.id' => SORT_DESC])
            ->all();

        if (!empty($activityList)) {
            $activityList = ArrayHelper::toArray($activityList);
            $pageList = PageModel::find()->alias('p')
                ->select('p.id, p.group_id, p.activity_id, p.pipeline, pl.title, pl.lang')
                ->leftJoin(
                    PageLanguageModel::tableName() . ' as pl',
                    'p.id = pl.page_id'
                )->where([
                    'p.activity_id' => array_column($activityList, 'id'),
                    'p.is_delete' => PageModel::NOT_DELETE
                ])->all();
            if (!empty($pageList)) {
                $pageList = ArrayHelper::toArray($pageList);
                $this->mergeActivityPageList($activityList, $pageList, $siteGroupCode);
            }
        } else {
            $activityList = [];
        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $activityList);
    }

    /**
     * 聚合活动和活动页面列表数据
     *
     * @param array $activityList
     * @param array $pageList
     * @param string $siteGroupCode
     */
    private function mergeActivityPageList(&$activityList, $pageList, $siteGroupCode)
    {
        $pageArr = [];
        $pipelineList = [];
        foreach ($pageList as $pageLangInfo) {
            $activityId = $pageLangInfo['activity_id'];
            $pageId = $pageLangInfo['id'];

            $pageLangList = isset($pageArr[$activityId][$pageId]) ? $pageArr[$activityId][$pageId]['lang_list'] : [];
            $pageLangList[] = $pageLangInfo['lang'];
            $pageLangInfo['lang_list'] = $pageLangList;
            $pageArr[$activityId][$pageId] = $pageLangInfo;
        }

        $configAllPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($siteGroupCode);
        $configAllLanguages = app()->params['lang'] ?? [];
        foreach ($pageArr as $activityId => $pages) {
            foreach ($pages as $pageId => $page) {
                $groupId = $page['group_id'];
                !isset($pipelineList[$activityId][$groupId]) && $pipelineList[$activityId][$groupId] = [
                    'group_id' => $groupId,
                    'page_title' => $page['title'],
                ];

                $_langList = [];
                foreach ($page['lang_list'] as $_langCode) {
                    $_langList[] = [
                        'code' => $_langCode,
                        'name' => $configAllLanguages[$_langCode]['name'] ?? ''
                    ];
                }
                $pipelineList[$activityId][$groupId]['pipeline_list'][] = [
                    'page_id' => $page['id'],
                    'code' => $page['pipeline'],
                    'name' => $configAllPipelineList[$page['pipeline']] ?? '',
                    'lang_list' => $_langList,
                ];
            }
        }


        $privilegeComponent = new AdminSitePrivilegeComponent();
        $hasPermissions = $privilegeComponent->getCurrentUserSiteSpecialPermissions($siteGroupCode);

        foreach ($activityList as &$activity) {
            $pageList = isset($pipelineList[$activity['id']]) ? array_values($pipelineList[$activity['id']]) : [];
            if (!empty($pageList)) {
                foreach ($pageList as $key => $pageInfo) {
                    $_newPipelineList = [];
                    foreach ($pageInfo['pipeline_list'] as $_pipelineInfo) {
                        if (in_array($_pipelineInfo['code'], $hasPermissions)) {
                            $_newPipelineList[] = $_pipelineInfo;
                        }
                    }
                    $pageList[$key]['pipeline_list'] = $_newPipelineList;
                }
            }

            $activity['page_ist'] = $pageList;
        }
        unset($activity);
    }

    /**
     * 校验PC转Wap的操作参数和权限
     * @param array $params
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws JsonResponseException
     */
    private function checkConvertParamsAndAuth(array $params)
    {
        //校验传参
        $rules = [
            [['source', 'target', 'model'], 'required'],
            [['model'], 'integer'],
            [['source', 'target'], 'string'],
            ['model', 'in', 'range' => [self::CONVERT_MODEL_APPEND, self::CONVERT_MODEL_COVER]]
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        $params['source'] = json_decode($params['source'], true);
        $params['target'] = json_decode($params['target'], true);
        if (!is_array($params['source']) || !is_array($params['target'])) {
            throw new JsonResponseException($this->codeFail, '参数source或target无效');
        }

        $sourcePipeline = key($params['source']);
        $sourceInfo = current($params['source']);
        //检查源页面，活动是否加锁，并判断权限
        $sourcePageModel = PageModel::getPageActivityInfo($sourceInfo['id']);
        if (!$sourcePageModel || $sourcePageModel->is_delete === PageModel::IS_DELETE) {
            throw new JsonResponseException($this->codeFail, '源活动页面不存在');
        }
        if ((int)$sourcePageModel->type !== ActivityModel::TYPE_PC) {
            throw new JsonResponseException($this->codeFail, '源页面非PC端页面');
        }
        $sourceActivityInfo = ActivityModel::getActivityInfo($sourcePageModel->activity_id);
        if (false === ActivityModel::checkAuth($sourceActivityInfo)) {
            throw new JsonResponseException($this->codeFail, '您对源页面没有操作权限');
        }

        if (!is_array($sourceActivityInfo['lang'])) {
            $sourceActivityInfo['lang'] = json_decode($sourceActivityInfo['lang'], true);
        }
        //检查传过来的渠道是都在源页面的范围内
        if (!isset($sourceActivityInfo['lang'][$sourcePipeline])) {
            throw new JsonResponseException($this->codeFail, '源页面不包含所选渠道');
        }

        //检查传过来的lang是否在源页面的范围内
        if (!\in_array($sourceInfo['lang'], $sourceActivityInfo['lang'][$sourcePipeline], true)) {
            throw new JsonResponseException($this->codeFail, '源页面不包含所选语言');
        }


        $targetPageModelList = [];
        foreach ($params['target'] as $targetPipeline => $targetInfo) {
            //检查目标页面，活动是否加锁，并判断权限
            $targetPageModel = PageModel::getPageActivityInfo($targetInfo['id']);
            if (!$targetPageModel || $targetPageModel->is_delete === PageModel::IS_DELETE) {
                throw new JsonResponseException($this->codeFail, '目标活动页面不存在');
            }
            if ((int)$targetPageModel->type !== ActivityModel::TYPE_MOBILE) {
                throw new JsonResponseException($this->codeFail, '，目标页面非Wap端页面');
            }
            $targetActivityInfo = ActivityModel::getActivityInfo($targetPageModel->activity_id);
            if (false === ActivityModel::checkAuth($targetActivityInfo)) {
                throw new JsonResponseException($this->codeFail, '您对目标页面没有操作权限');
            }

            if (!is_array($targetActivityInfo['lang'])) {
                $targetActivityInfo['lang'] = json_decode($targetActivityInfo['lang'], true);
            }
            //源页面和目标页面是否属于同级站点（比如都是rw/rg/zaful）
            if (explode($sourceActivityInfo['site_code'], '-')[0] !== explode($targetActivityInfo['site_code'], '-')[0]) {
                throw new JsonResponseException($this->codeFail, '源页面和目标页面不属于同一站点下的不同端');
            }

            //检查传过来的渠道是都在目标页面的范围内
            if (!isset($targetActivityInfo['lang'][$targetPipeline])) {
                throw new JsonResponseException($this->codeFail, '目标页面不包含所选渠道');
            }

            //检查传过来的lang是否在目标页面的范围内
            foreach ($targetInfo['lang_list'] as $_langCode) {
                if (!empty($_langCode) && !\in_array($_langCode, $targetActivityInfo['lang'][$targetPipeline], true)) {
                    throw new JsonResponseException($this->codeFail, '所选语言超出目标页面范围');
                }
            }

            $targetPageModelList[$targetPipeline] = $targetPageModel;
        }

        return [$sourcePageModel, $targetPageModelList];
    }

    /**
     * 获取源页面数据
     *
     * @param int $pageId
     * @param string $lang 语言代码简称
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getSourcePageData(int $pageId, string $lang)
    {
        //取源页面数据
        $uiDataCondition = 'is_m = ' . PageUiComponentDataModel::IS_M
            . ' OR is_public = ' . PageUiComponentDataModel::IS_PUBLIC;

        return $this->getPageLayoutUiData($pageId, [$lang], $uiDataCondition);
    }

    /**
     * PC转Wap
     *
     * @param array $params = [
     *      'source_id' => 1,//源页面ID
     *      'target_id' => 2,//目标页面ID
     *      'model'     => 1,//模式，1-追加，2-覆盖
     * ]
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws JsonResponseException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function pcConvertWap(array $params)
    {
        /** @var PageModel $sourcePageModel */
        //校验参数和权限
        list($sourcePageModel, $targetPageModelList) = $this->checkConvertParamsAndAuth($params);

        $params['source'] = json_decode($params['source'], true);
        $params['target'] = json_decode($params['target'], true);
        $sourceInfo = current($params['source']);

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId(array_column($params['target'], 'id'));
        $ipsComponent = new \app\modules\soa\components\IpsComponent();

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            //取源页面数据,单语言数据
            $componentData = $this->getSourcePageData($sourcePageModel->id, $sourceInfo['lang']);

            $toUrl = NULL;
            $datas = [];

            /** @var PageModel $targetPageModel */
            foreach ($targetPageModelList as $targetPipeline => $targetPageModel) {
                //初始化修改
                list($initRes, $initMsg) = $this->initConvert($sourcePageModel, $targetPageModel);
                if (!$initRes) {
                    throw new Exception($initMsg);
                }

                $this->convertUiRelation = []; // 重置变量
                if (self::CONVERT_MODEL_APPEND === (int)$params['model']) {
                    //追加到目标页面内容后面
                    if (true !== ($res = $this->appendConvertWap($targetPageModel->id, $componentData, $params['target'][$targetPipeline]))) {
                        throw new Exception($res);
                    }
                } elseif (true !== ($res = $this->coverConvertWap($targetPageModel->id, $componentData, $params['target'][$targetPipeline]))) {
                    //会直接覆盖目标页面内容
                    throw new Exception($res);
                }

                // 如果原页面组件关联选品系统,复制关联选品活动信息
                if (!empty($this->convertUiRelation)) {
                    $ipsPageInfo = [
                        'siteCode' => $sourcePageModel->site_code,
                        'pageId'   => $sourcePageModel->id,
                        'lang'     => $sourceInfo['lang'],
                        'toPageId' => $targetPageModel->id,
                    ];

                    foreach ($this->convertUiRelation as $_targetLangCode => $uiRelations) {
                        if (self::CONVERT_MODEL_COVER === (int)$params['model']) {
                            // 清除目标页面UI组件IPS关联
                            $ipsTargetInfo = [
                                'siteCode' => $targetPageModel->site_code,
                                'pageId'   => $targetPageModel->id,
                                'lang'     => $_targetLangCode,
                            ];
                            $ipsComponent->delPageUiRelatedIpsActivityIfSkuFromIps($ipsTargetInfo);
                        }
                        $ipsPageInfo['toLang'] = $_targetLangCode;
                        foreach ($uiRelations as $_fromUid => $_toUid) {
                            $ipsComponent->copyRelatedIpsActivityIfSkuFromIps($ipsPageInfo, $_fromUid, $_toUid);
                        }
                    }
                }


                $toUrl = [
                    'group_id'  => $targetPageModel->group_id,
                    'pipeline'  => $targetPipeline,
                    'lang'      => $params['target'][$targetPipeline]['lang_list'][0] ?? 'en',
                    'site_code' => $targetPageModel->site_code
                ];

            }

            //执行成功，提交事务
            $transaction->commit();

            //同步创建ips活动
            //查找目标页面需要绑定到ips的组件
            foreach ($targetPageModelList as $targetPipeline => $targetPageModel) {
                $page_layout = PageLayoutModel::find()->where(['page_id' => $targetPageModel->id])->asArray()->one();
                $layout_id = $page_layout['id'];
                $lang = $page_layout['lang'];
                $page_ui_lists = PageUiModel::find()->where(['layout_id' => $layout_id])->asArray()->all();
                foreach ($page_ui_lists as $page_ui_list) {
                    $page_ui = PageUiModel::findOne($page_ui_list['id']);
                    if(empty($page_ui)){
                       continue;
                    }
                    $ui_datas = PageUiComponentDataModel::find()->where(['component_id'=>$page_ui_list['id'],"tpl_id"=>$page_ui->tpl_id])->asArray()->all();
                    $is_need_sync = false;
                    $is_ips = false;
                    $is_rule = false;
                    $ipsFilterInfo = '';
                    foreach ($ui_datas as $ui_data){
                        if($ui_data['key']=="goodsDataFrom" &&
                            $ui_data['value'] == 2){
                            $is_ips= true;
                        }
                        if($ui_data['key']=="ipsMethods" &&
                            $ui_data['value'] == 3){
                            $is_rule = true;
                        }
                        if($ui_data['key'] == "ipsFilterInfo"){
                            if(!empty(json_decode($ui_data['value'],1))){
                                $ipsFilterInfo = json_decode($ui_data['value'],1);
                            }
                        }

                    }
                    $is_need_sync = $is_ips && $is_rule;
                    if($is_need_sync && $ipsFilterInfo){
                        if (isset($ipsFilterInfo["ips_activity_child_id"])) {
                            //规则选品需同步
                            $tmp['page_id'] = $targetPageModel->id;
                            $tmp['lang'] = $lang;
                            $tmp['id'] = $page_ui_list['id'];
                            $tmp['tpl_id'] = $page_ui_list['tpl_id'];
                            $tmp['is_auto_activity'] = 2;
                            $tmp['ips_activity_child_id'] = $ipsFilterInfo["ips_activity_child_id"];
                            $datas[] = $tmp;
                        }
                    }

                }
            }
            \app\modules\common\components\CommonActivityComponent::batchCreateActivityToIps($datas);

            switch (app()->controller->module->module->id) {
                case 'advertisement':
                    $url = '/advertisement/zf/design/index';
                    break;
                case 'home':
                    $url = '/home/zf/design/index';
                    break;
                default:
                    $url = '/activity/zf/design/index';
                    break;
            }

            return app()->helper->arrayResult(
                $this->codeSuccess,
                $this->msgSuccess,
                [
                    'redirectUrl' => Url::to([
                        $url,
                        'group_id' => $toUrl['group_id'],
                        'pipeline' => $toUrl['pipeline'],
                        'lang'     => $toUrl['lang']
                    ], true),
                    'siteCode' => $toUrl['site_code']
                ]
            );
        } catch (Exception $e) {
            //执行失败，回滚事务
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 追加式更新页面组件
     *
     * @param int $targetId
     * @param array $componentData
     * @param array $targetInfo
     *
     * @return bool|string
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function appendConvertWap(int $targetId, array $componentData, array $targetInfo)
    {
        //第2个$layoutData用不到，空着即可
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        if (!empty($layoutComponent['data'])) {
            $layout = current($layoutComponent['data']);
            foreach ($targetInfo['lang_list'] as $targetLang) {
                //获取最后一个layout组件（PC转Wap是将所有ui组件转到wap的最后一个layout内）
                list($resDefault, $layoutId) = $this->getLastLayout($targetId, $targetLang, self::DEFAULT_LAYOUT_KEY);
                if (!$resDefault) {
                    return $layoutId;
                }

                //追加数据
                $params = [$layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds, $targetLang];
                if (true !== ($res = $this->appendConvertAppByLang($params))) {
                    return $res;
                }

            }
        }

        return true;
    }

    /**
     * 按照语言项来追加
     *
     * @param array $params
     *
     * @return bool|string
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    private function appendConvertAppByLang(array $params)
    {
        list($layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds, $targetLang) = $params;

        //先将最后一个layout内的最后一个ui的next置为-2，为了避开主键冲突(-1已被占用)
        $lastUi = PageUiModel::findOne(['layout_id' => $layoutId, 'next_id' => 0, 'position' => 1]);
        if ($lastUi) {
            $lastUi->next_id = -2;
            if (false === $lastUi->update(true)) {
                return $lastUi->flattenErrors(', ');
            }
        }

        $layout = array_reverse($this->getOrderedComponents($layout));
        $layoutIdsInSort = array_column($layout, 'id');
        //重新写入复制组装过后的数据
        $uiComponentInSort = [];
        $this->buildUiComponentData($uiComponentInSort, $layout, $uiComponent, $uiData, $targetLang);

        //追加的一定要将ui放到一个数组中并排好序，这样才会添加到同一个layout中
        $uiFirstId = 0;
        if (true !== ($res = $this->copyPageUiComponent(
            [
                $this->sortUiComponent($uiComponentInSort, $layoutIdsInSort),
                $relationKeys,
                $relationTplIds,
                $uiData
            ],
            $layoutId,
            $uiFirstId
            ))
        ) {
            return $res;
        }

        //前面有更新，则在此处更新next_id
        if ($lastUi) {
            $lastUi->next_id = $uiFirstId;
            if (false === $lastUi->update(true)) {
                return $lastUi->flattenErrors(', ');
            }
        }

        return true;
    }

    /**
     * 覆盖式更新页面组件
     *
     * @param int $targetId
     * @param array $componentData
     * @param array $targetInfo
     *
     * @return bool|string
     * @throws \Exception
     * @throws \Throwable
     */
    private function coverConvertWap(int $targetId, array $componentData, array $targetInfo)
    {
        //第2个$layoutData用不到，空着即可
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        //删掉原来的绑定关系
        $ui = new CommonUi();
        $ui->pageId = $targetId;
        $page_layouts = PageLayoutModel::find()->select("id")->where(['page_id'=>$targetId])->asArray()->all();

        foreach ($page_layouts as $page_layout){
            $ui_ids =  PageUiModel::find()->where(['layout_id' => $page_layout])->asArray()->all();
            $ui_ids = empty($ui_ids) ? [] : array_column($ui_ids,'lang','id');
            foreach ($ui_ids as $id =>$lang){
                $ui->delUserData($id);
                $ui->cancelUiComponentBindRelation($id, $lang);
                $sync_data['del_info'][] = [
                    'geshop_component_ui_id' => $id
                ];
            }

        }

        //同步删除IPS子活动
        $activity = ActivityModel::getActivityByPageId($targetId);
        $sync_data['geshop_activity_id'] = $activity->id;

        \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

        //清空原有layout
        if (true !== ($resDel = PageLayoutModel::deletePageLayouts($targetId, $targetInfo['lang_list']))) {
            return $resDel;
        }

        if (!empty($layoutComponent['data'])) {
            $layout = current($layoutComponent['data']);
            $layout = array_reverse($this->getOrderedComponents($layout));
            $layoutIdsInSort = array_column($layout, 'id');

            foreach ($targetInfo['lang_list'] as $targetLang) {

                //创建默认的layout组件（PC转Wap是将所有ui组件转到wap的一个layout内）
                list($resDefault, $layoutId) = $this->createDefaultLayout($targetId, $targetLang, self::DEFAULT_LAYOUT_KEY);
                if (!$resDefault) {
                    return $layoutId;
                }

                //重新写入复制组装过后的数据
                $uiComponentInSort = [];
                $this->buildUiComponentData($uiComponentInSort, $layout, $uiComponent, $uiData, $targetLang);

                //追加的一定要将ui放到一个数组中并排好序，这样才会添加到同一个layout中
                $uiFirstId = 0;
                if (true !== ($res = $this->copyPageUiComponent(
                    [
                        $this->sortUiComponent($uiComponentInSort, $layoutIdsInSort),
                        $relationKeys,
                        $relationTplIds,
                        $uiData
                    ],
                    $layoutId,
                    $uiFirstId
                    ))
                ) {
                    return $res;
                }
            }
        }

        return true;
    }

    /**
     * 重新写入复制组装过后的数据
     *
     * @param $uiComponentInSort
     * @param $layout
     * @param $uiComponent
     * @param $uiData
     * @param $targetLang
     */
    private function buildUiComponentData(&$uiComponentInSort, $layout, &$uiComponent, &$uiData, $targetLang)
    {
        foreach ($layout as $value) {
            if (!empty($uiComponent['data'][ $value['id'] ]) && is_array($uiComponent['data'][ $value['id'] ])) {
                foreach ($uiComponent['data'][ $value['id'] ] as &$uiItem) {
                    $uiItem['lang'] = $targetLang;
                }

                //这里empty要前置判断，...语法会取出数组中的一项，而array_push() expects at least 2 parameters
                array_push($uiComponentInSort, ...$uiComponent['data'][ $value['id'] ]);
            }
        }

        if (!empty($uiData['data']) && is_array($uiData['data'])) {
            foreach ($uiData['data'] as &$uiDataItemList) {
                foreach ($uiDataItemList as &$uiDataItem) {
                    $uiDataItem['lang'] = $targetLang;
                }
            }
        }
    }

    /**
     * 拷贝UI组件及uiData
     *
     * @param array $uiParams
     * @param int $layoutId
     * @param int $uiFirstId
     *
     * @return bool|string
     * @throws \Throwable
     */
    private function copyPageUiComponent(array $uiParams, int $layoutId, int &$uiFirstId)
    {
        list($uiArr, $relationKeys, $relationTplIds, $uiData) = $uiParams;
        if (!empty($uiArr)) {

            $nextId = 0;
            /** @var array $uiArr */
            foreach ($uiArr as $ui) {
                //和wap转app的区别，是pc和wap的ui组件有关联关系，没有关联关系的要过滤掉
                //!!!一定要在插入这里过滤，不能在取数据那里，因为后面要排序，完整数据才不会导致链表断掉!!!
                if (!isset($relationTplIds[$ui['tpl_id']])) {
                    continue;
                }

                /** @var \app\modules\common\zf\models\PageUiModel $uiModel */
                $uiModel = new PageUiModel();
                $uiModel->component_key = $relationKeys[$ui['component_key']];
                $uiModel->next_id = $nextId;
                $uiModel->lang = $ui['lang'];
                $uiModel->layout_id = $layoutId;
                $uiModel->position = self::DEFAULT_LAYOUT_POSITION;//wap端的默认放在position=1位置
                $uiModel->tpl_id = $relationTplIds[$ui['tpl_id']] ?? 0;
                $uiModel->async_data_format = $ui['async_data_format'] ?? '';

                if (false === $uiModel->insert(true)) {
                    return $uiModel->flattenErrors(', ');
                }

                // 记录一个页面语言下的组件新老对象关系
                if (!isset($this->convertUiRelation[$uiModel->lang])) {
                    $this->convertUiRelation[$uiModel->lang] = [];
                }
                $this->convertUiRelation[$uiModel->lang][$ui['id']] = $uiModel->id;

                $uiDataInUiId = $uiData['data'][$ui['id']] ?? [];
                if (true !== ($res = $this->copyPageUiData($uiDataInUiId, $uiData['columns'], $uiModel->id))) {
                    return $res;
                }
                $nextId = $uiModel->id;
            }
            $uiFirstId = $nextId;//将最后添加的一个uiId返回，这样方便前面的续接上
        }

        return true;
    }

    /**
     * 对所有ui组件按照layout的顺序排序，最终返回的是倒序排列的
     *
     * @param array $uiComponent
     * @param array $layoutIdsInSort
     *
     * @return array
     */
    private function sortUiComponent(array $uiComponent, array $layoutIdsInSort)
    {
        $data = $return = [];

        if (!empty($uiComponent)) {
            foreach ($uiComponent as $ui) {
                if (false !== ($index = array_search($ui['layout_id'], $layoutIdsInSort, false))) {
                    $data[$index][] = $ui;
                }
            }
        }

        if (!empty($data)) {
            foreach ($data as $layoutUi) {
                array_push($return, ...array_reverse($this->getUiOrderedComponents($layoutUi)));
            }
        }

        return $return;
    }

    /**
     * 获取排好序的ui组件数组，倒序排列的
     * @param array $layoutUi
     * @return array
     */
    private function getUiOrderedComponents(array $layoutUi)
    {
        $positionArr = array_unique(array_column($layoutUi, 'position'));
        if (\count($positionArr) === 1) {
            return $this->getOrderedComponents($layoutUi);
        }

        $data = $return = [];
        foreach ($layoutUi as $ui) {
            $data[$ui['position']][] = $ui;
        }
        foreach ($data as $list) {
            array_push($return, ...$this->getOrderedComponents($list));
        }

        return $return;
    }
}
