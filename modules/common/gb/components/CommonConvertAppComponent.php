<?php

namespace app\modules\common\gb\components;

use app\modules\common\gb\models\{
    ActivityModel, ActivityGroupModel, PageLanguageModel, PageLayoutModel, PageModel, PageUiComponentDataModel, PageUiModel
};

use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use yii\base\Exception;
use yii\helpers\Url;
use app\modules\common\gb\traits\CommonPublishTrait;
use app\modules\common\gb\traits\CommonConvertTrait;
use app\base\SitePlatform;

class CommonConvertAppComponent extends Component
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
     * App端默认layout组件key
     */
    const DEFAULT_LAYOUT_KEY = 'L000046';

    /**
     * Wap端默认layout组件position
     */
    const DEFAULT_LAYOUT_POSITION = 1;

    /**
     * 获取当前站点下当前用户创建的所有APP活动
     *
     * @return array
     */
    public function getCreatorAppLists()
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        $appSiteCode = SitePlatform::getAppPlatformSiteCode($siteGroupCode);
        if (empty($appSiteCode)) {
            return app()->helper->arrayResult($this->codeFail, 'APP站点不存在');
        }

        $activityList = ActivityModel::find()
            ->select('id, name')
            ->where([
                'type'      => ActivityModel::TYPE_APP,
                'group_id'  => ActivityGroupModel::NO_RELATED_GROUP_ID,
                'site_code' => $appSiteCode,
                'is_delete' => ActivityModel::NOT_DELETE
            ])->andWhere('create_user = "' . app()->user->admin->username . '" OR is_lock = ' . ActivityModel::UN_LOCK)
            ->all();
        if (!empty($activityList)) {
            $activityList = ArrayHelper::toArray($activityList);
            $pageList = PageModel::find()->alias('p')
                ->select('p.id, p.pid, p.activity_id, pl.title, pl.lang')
                ->leftJoin(
                    PageLanguageModel::tableName() . ' as pl',
                    'p.id = pl.page_id'
                )->where([
                    'p.activity_id' => array_column($activityList, 'id'),
                    'p.is_delete'   => PageModel::NOT_DELETE
                ])->all();
            if (!empty($pageList)) {
                $pageList = ArrayHelper::toArray($pageList);
                $this->mergeActivityPageList($activityList, $pageList);
            }
        }

        return app()->helper->arrayResult(0, 'success', $activityList ?? []);
    }

    /**
     * 聚合活动和活动页面列表数据
     *
     * @param array $activityList
     * @param array $pageList
     */
    private function mergeActivityPageList(array &$activityList, array $pageList)
    {
        $pageArr = [];
        foreach ($pageList as $page) {
            $pageLang = isset($pageArr[ $page['activity_id'] ][ $page['id'] ])
                ? $pageArr[ $page['activity_id'] ][ $page['id'] ]['lang'] : [];
            $pageLang[] = $page['lang'];
            $page['lang'] = $pageLang;
            $pageArr[ $page['activity_id'] ][ $page['id'] ] = $page;
        }
        foreach ($pageArr as $key => $pages) {
            foreach ($pages as $k => $page) {
                $pageArr[ $key ][ $k ]['lang'] = implode(',', array_unique($pageArr[ $key ][ $k ]['lang']));
                $pageArr[ $key ][ $k ]['langList'] = ActivityModel::getLangListByLangString($pageArr[ $key ][ $k ]['lang']);
            }
        }
        foreach ($activityList as &$activity) {
            $activity['pageList'] = isset($pageArr[ $activity['id'] ]) ? array_values($pageArr[ $activity['id'] ]) : [];
        }
        unset($activity);
    }

    /**
     * 校验PC转Wap的操作参数和权限
     *
     * @param array $params
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws JsonResponseException
     */
    private function checkConvertParamsAndAuth(array $params, string $sourcePipe, string $type)
    {
        $source = current($params['source']);
        //检查源页面，活动是否加锁，并判断权限
        $sourcePageModel = PageModel::getPageActivityInfo($source['id']);
        if (!$sourcePageModel || $sourcePageModel->is_delete === PageModel::IS_DELETE) {
            throw new JsonResponseException($this->codeFail, '源活动页面不存在');
        }
        if ('wap' == $type && (int) $sourcePageModel->type !== ActivityModel::TYPE_MOBILE) {
            throw new JsonResponseException($this->codeFail, '源页面非Wap端页面');
        }
        if ('app' == $type && !in_array($sourcePageModel->type, [ActivityModel::TYPE_ANDROID, ActivityModel::TYPE_IOS])) {
            throw new JsonResponseException($this->codeFail, '源页面非App端页面');
        }
        $sourceActivityInfo = ActivityModel::getActivityInfo($sourcePageModel->activity_id);
        if (false === ActivityModel::checkAuth($sourceActivityInfo)) {
            throw new JsonResponseException($this->codeFail, '您对源页面没有操作权限');
        }
        $sourceLangList = array_combine(
            array_column($sourceActivityInfo['langList'], 'key'), $sourceActivityInfo['langList']
        );

        //检查传过来的渠道是都在源页面的范围内
        if (!\in_array($sourcePipe, array_keys($sourceLangList), true)) {
            throw new JsonResponseException($this->codeFail, '源页面不包含所选渠道');
        }
        //检查传过来的lang是否在源页面的范围内
        if (!empty($params['source']['lang']) && !\in_array($params['source']['lang'], '', true)) {
            throw new JsonResponseException($this->codeFail, '源页面不包含所选语言');
        }

        $sourceSiteCode = explode($sourceActivityInfo['site_code'], '-')[0];
        $targetPageModel = $this->checkConvertTargetParamsAndAuth($sourceSiteCode, $params['target']);

        return [$sourcePageModel, $targetPageModel];
    }

    /**
     * 校验PC转Wap的被转换的操作参数和权限
     *
     * @param string $souceSiteCode
     * @param array  $target
     *
     * @return \stdClass
     * @throws JsonResponseException
     */
    private function checkConvertTargetParamsAndAuth(string $souceSiteCode, array $target)
    {
        $targetPageModel = new \stdClass();
        foreach ($target as $platform => $value) {
            $targetPageModel->$platform = new \stdClass();
            foreach ($value as $pipeline => $item) {
                //检查目标页面，活动是否加锁，并判断权限
                $model = PageModel::getPageActivityInfo($item['id']);

                if (!$model || $model->is_delete === PageModel::IS_DELETE) {
                    throw new JsonResponseException($this->codeFail, '目标活动页面不存在');
                }
                if (!in_array((int) $model->type, [ActivityModel::TYPE_ANDROID, ActivityModel::TYPE_IOS])) {
                    throw new JsonResponseException($this->codeFail, '，目标页面非APP端页面');
                }
                $targetActivityInfo = ActivityModel::getActivityInfo($model->activity_id);
                if (false === ActivityModel::checkAuth($targetActivityInfo)) {
                    throw new JsonResponseException($this->codeFail, '您对目标页面没有操作权限');
                }

                $targetLangList = array_combine(
                    array_column($targetActivityInfo['langList'], 'key'), $targetActivityInfo['langList']
                );

                //检查传过来的渠道是都在源页面的范围内
                if (!\in_array($pipeline, array_keys($targetLangList), true)) {
                    throw new JsonResponseException($this->codeFail, '源页面不包含所选渠道');
                }
                //源页面和目标页面是否属于同级站点（比如都是rw/rg/zaful）
                if ($souceSiteCode !== explode($targetActivityInfo['site_code'], '-')[0]) {
                    throw new JsonResponseException($this->codeFail, '源页面和目标页面不属于同一站点下的不同端');
                }

                //检查传过来的lang是否在目标页面的范围内
                foreach ($item['lang'] as $lang) {
                    if (
                        !empty($lang)
                        && !\in_array($lang, array_column($targetLangList[ $pipeline ]['langList'], 'key'), true)
                    ) {
                        throw new JsonResponseException($this->codeFail, '所选语言超出目标页面范围');
                    }
                }
                $targetPageModel->$platform->$pipeline = $model;

                unset($model, $targetActivityInfo);
            }
        }

        return $targetPageModel;
    }

    /**
     * 获取源页面数据
     *
     * @param int   $pageId
     * @param array $lang 语言代码简称
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getSourcePageData(int $pageId, string $lang)
    {
        //取源页面数据
        $uiDataCondition = 'is_app = ' . PageUiComponentDataModel::IS_APP
            . ' OR is_public = ' . PageUiComponentDataModel::IS_PUBLIC;

        return $this->getPageLayoutUiData($pageId, $lang, $uiDataCondition, true);
    }

    /**
     * 参数校验并解析
     *
     * @param array $params
     *
     * @throws JsonResponseException
     */
    public function paramsValidator(array &$params)
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

        $params['source'] = \GuzzleHttp\json_decode($params['source'], true);
        $params['target'] = \GuzzleHttp\json_decode($params['target'], true);
    }

    /**
     * M版转APP/安卓和IOS互转
     *
     * @param array  $params
     * @param string $type
     *
     * @return array
     */
    public function convertApp(array $params, $type = 'wap')
    {
        $this->paramsValidator($params);
        $sourcePipe = key($params['source']);
        $sourceLang = current($params['source'])['lang'];

        //校验参数和权限
        list($sourcePageModel, $targetPageModel) = $this->checkConvertParamsAndAuth($params, $sourcePipe, $type);

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            //取源页面数据
            $componentData = $this->getSourcePageData($sourcePageModel->id, $sourceLang);
            foreach ($targetPageModel as $platform => $value) {
                foreach ($value as $pipeline => $target) {
                    //初始化修改
                    list($initRes, $initMsg) = $this->initConvert($sourcePageModel, $target);
                    if (!$initRes) {
                        throw new Exception($initMsg);
                    }

                    if (self::CONVERT_MODEL_APPEND === (int) $params['model']) {
                        //追加到目标页面内容后面
                        if (true !== ($res = $this->appendConvertApp(
                                $target->id, $componentData, $params['target'][ $platform ][ $pipeline ]))
                        ) {
                            throw new Exception($res);
                        }
                    } elseif (true !== ($res = $this->coverConvertApp(
                            $target->id, $componentData, $params['target'][ $platform ][ $pipeline ]))
                    ) {
                        //会直接覆盖目标页面内容
                        throw new Exception($res);
                    }
                    $targetGroupId = $target->group_id;
                    $targetPipe = $pipeline;
                    $targetLang = !empty($params['target'][ $platform ][ $pipeline ]['lang'])
                        ? current($params['target'][ $platform ][ $pipeline ]['lang'])
                        : 'en';
                    $targetSiteCode = $target->site_code;
                }
            }
            PageLanguageModel::converAppGoodsComponentStyle($params);
            //执行成功，提交事务
            $transaction->commit();

            return app()->helper->arrayResult(
                $this->codeSuccess,
                $this->msgSuccess,
                [
                    'redirectUrl' => Url::to(
                        [
                            '/activity/gb/design/index',
                            'group_id' => $targetGroupId,
                            'pipeline' => $targetPipe,
                            'lang'     => $targetLang
                        ], true
                    ),
                    'siteCode'    => $targetSiteCode
                ]
            );
        } catch (Exception $e) {
            //执行失败，回滚事务
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 追加式更新
     *
     * @param int   $targetId
     * @param array $componentData
     *
     * @return bool|string
     * @throws \yii\db\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function appendConvertApp(int $targetId, array $componentData, array $params)
    {
        //第2个$layoutData用不到，空着即可
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        if (!empty($layoutComponent['data'])) {
            $layout = current($layoutComponent['data']);
            //foreach ($params as $item) {
                foreach ($params['lang'] as $lang) {
                    //获取最后一个layout组件（PC转Wap是将所有ui组件转到wap的最后一个layout内）
                    list($resDefault, $layoutId) = $this->getLastLayout($params['id'], $lang, self::DEFAULT_LAYOUT_KEY);
                    if (!$resDefault) {
                        return $layoutId;
                    }

                    $list = [$layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds, $lang];
                    if (true !== ($res = $this->appendConvertAppByLang($list))) {
                        return $res;
                    }
                }
            //}
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
        list($layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds, $lang) = $params;

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
        $this->buildUiComponentData($uiComponentInSort, $layout, $uiComponent, $uiData, $lang);

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
     * 覆盖式更新
     *
     * @param int   $targetId
     * @param array $componentData
     * @param array $params
     *
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function coverConvertApp(int $targetId, array $componentData, array $params)
    {
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        //再copy
        if (!empty($layoutComponent['data'])) {
            $layout = current($layoutComponent['data']);
            $layout = array_reverse($this->getOrderedComponents($layout));
            $layoutIdsInSort = array_column($layout, 'id');
            //foreach ($params as $item) {
                //清空原有layout
                if (true !== ($resDel = PageLayoutModel::deletePageLayouts($params['id'], $params['lang']))) {
                    return $resDel;
                }
                foreach ($params['lang'] as $lang) {
                    //创建默认的layout组件（PC转Wap是将所有ui组件转到wap的一个layout内）
                    list($resDefault, $layoutId) = $this->createDefaultLayout(
                        $params['id'], $lang, self::DEFAULT_LAYOUT_KEY
                    );
                    if (!$resDefault) {
                        return $layoutId;
                    }
                    //重新写入复制组装过后的数据
                    $uiComponentInSort = [];
                    $this->buildUiComponentData($uiComponentInSort, $layout, $uiComponent, $uiData, $lang);

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
            //}
        }

        return true;
    }

    /**
     * 拷贝UI组件及uiData
     *
     * @param array $uiParams
     * @param int   $layoutId
     * @param int   $uiFirstId
     *
     * @return bool|string
     * @throws \Throwable
     */
    private function copyPageUiComponent(array $uiParams, int $layoutId, int &$uiFirstId)
    {
        //wap和app的ui是一一对应关系，所以第二个参数$relationKeys不需要
        list($uiArr, , $relationTplIds, $uiData) = $uiParams;

        if (!empty($uiArr)) {
            $nextId = 0;
            foreach ($uiArr as $ui) {
                $uiModel = new PageUiModel();
                $uiModel->component_key = $ui['component_key'];
                $uiModel->next_id = $nextId;
                $uiModel->lang = $ui['lang'];
                $uiModel->layout_id = $layoutId;
                $uiModel->position = 1; // 对于追加的来说，所有UI放到最后一个layout下的position=1的最后
                $uiModel->tpl_id = $relationTplIds[ $ui['tpl_id'] ] ?? 0;

                if (false === $uiModel->insert(true)) {
                    return $uiModel->flattenErrors(', ');
                }

                $uiDataInUiId = $uiData['data'][ $ui['id'] ] ?? [];
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
     * 拷贝UI组件及uiData
     *
     * @param array $uiParams
     * @param int   $layoutId
     *
     * @return bool|string
     * @throws \Throwable
     */
    private function copyPageUiComponentByPosition(array $uiParams, int $layoutId)
    {
        //wap和app的ui是一一对应关系，所以第二个参数$relationKeys不需要
        list($uiArr, , $relationTplIds, $uiData) = $uiParams;

        if (!empty($uiArr)) {
            $uiArr = $this->getOrderedComponentsByPosition($uiArr);
            foreach ($uiArr as $uiList) {
                $nextId = 0;
                $uiList = array_reverse($uiList);
                foreach ($uiList as $ui) {
                    $uiModel = new PageUiModel();
                    $uiModel->component_key = $ui['component_key'];
                    $uiModel->next_id = $nextId;
                    $uiModel->lang = $ui['lang'];
                    $uiModel->layout_id = $layoutId;
                    $uiModel->position = $ui['position'];
                    $uiModel->tpl_id = $relationTplIds[ $ui['tpl_id'] ] ?? 0;

                    if (false === $uiModel->insert(true)) {
                        return $uiModel->flattenErrors(', ');
                    }

                    $uiDataInUiId = $uiData['data'][ $ui['id'] ] ?? [];
                    if (true !== ($res = $this->copyPageUiData($uiDataInUiId, $uiData['columns'], $uiModel->id))) {
                        return $res;
                    }
                    $nextId = $uiModel->id;
                }
            }
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
                    $data[ $index ][] = $ui;
                }
            }
            krsort($data);
        }

        if (!empty($data)) {
            foreach ($data as $layoutUi) {
                array_push($return, ...$this->getUiOrderedComponents($layoutUi));
            }
        }

        return array_reverse($return);
    }

    /**
     * 获取排好序的ui组件数组，倒序排列的
     *
     * @param array $layoutUi
     *
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
            $data[ $ui['position'] ][] = $ui;
        }
        foreach ($data as $list) {
            array_push($return, ...$this->getOrderedComponents($list));
        }

        return $return;
    }

    /**
     * 重新写入复制组装过后的数据
     *
     * @param $uiComponentInSort
     * @param $layout
     * @param $uiComponent
     * @param $uiData
     * @param $lang
     */
    private function buildUiComponentData(&$uiComponentInSort, $layout, &$uiComponent, &$uiData, $lang)
    {
        foreach ($layout as $value) {
            if (!empty($uiComponent['data'][ $value['id'] ])) {
                foreach ($uiComponent['data'][ $value['id'] ] as &$uiItem) {
                    $uiItem['lang'] = $lang;
                }
                //这里empty要前置判断，...语法会取出数组中的一项，而array_push() expects at least 2 parameters
                array_push($uiComponentInSort, ...$uiComponent['data'][ $value['id'] ]);
            }
        }

        if (!empty($uiData['data']) && is_array($uiData['data'])) {
            foreach ($uiData['data'] as &$uData) {
                foreach ($uData as &$uValue) {
                    $uValue['lang'] = $lang;
                }
            }
        }
    }
}
