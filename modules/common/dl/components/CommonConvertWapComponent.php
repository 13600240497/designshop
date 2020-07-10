<?php

namespace app\modules\common\dl\components;

use app\modules\common\dl\models\{
    ActivityModel, ActivityGroupModel, PageLanguageModel, PageLayoutModel, PageModel, PageUiComponentDataModel, PageUiModel
};
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use yii\base\Exception;
use app\base\SitePlatform;
use app\base\SiteConstants;
use yii\helpers\Url;
use app\modules\common\dl\traits\CommonPublishTrait;
use app\modules\common\dl\traits\CommonConvertTrait;
use app\modules\base\components\AccessLogComponent;
use app\base\RequestUtils;

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

        $activityList = ActivityModel::find()
            ->select('id, name')
            ->where([
                'type'        => ActivityModel::TYPE_MOBILE,
                'group_id'    => ActivityGroupModel::NO_RELATED_GROUP_ID,
                'site_code'   => $wapSiteCode,
                'mold'        => $activityType,
                'is_delete'   => ActivityModel::NOT_DELETE
            ])->andWhere('create_user = "' . app()->user->admin->username . '" OR is_lock = ' . ActivityModel::UN_LOCK)
            ->all();

        if (!empty($activityList)) {
            $activityList = ArrayHelper::toArray($activityList);
            $pageList = PageLanguageModel::find()->alias('pl')
                ->select('pl.id, pl.page_id, p.pid, p.activity_id, pl.title, pl.lang')
                ->leftJoin(
                    PageModel::tableName() . ' as p',
                    'p.id = pl.page_id'
                )->where([
                    'p.activity_id' => array_column($activityList, 'id'),
                    'p.is_delete' => PageModel::NOT_DELETE
                ])->asArray()->all();
            if (!empty($pageList)) {
                $this->mergeActivityPageList($activityList, $pageList);
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
     */
    private function mergeActivityPageList(&$activityList, $pageList)
    {
        $pageArr = [];
        foreach ($pageList as $page) {
            $pageLang = isset($pageArr[$page['activity_id']][$page['page_id']])
                ? $pageArr[$page['activity_id']][$page['page_id']]['lang'] : [];
            $pageLang[] = $page['lang'];
            $page['lang'] = $pageLang;
            $pageArr[$page['activity_id']][$page['page_id']] = $page;
        }
        foreach ($pageArr as $key => $pages) {
            foreach ($pages as $k => $page) {
                $pageArr[$key][$k]['lang'] = implode(',', array_unique($pageArr[$key][$k]['lang']));
                $pageArr[$key][$k]['langList'] = ActivityModel::getLangListByLangString($pageArr[$key][$k]['lang']);
            }
        }
        foreach ($activityList as &$activity) {
            $activity['pageList'] = isset($pageArr[$activity['id']]) ? array_values($pageArr[$activity['id']]) : [];
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
            [['source_id', 'target_id', 'model'], 'required'],
            [['source_id', 'target_id', 'model'], 'integer'],
            ['model', 'in', 'range' => [self::CONVERT_MODEL_APPEND, self::CONVERT_MODEL_COVER]]
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        //检查源页面，活动是否加锁，并判断权限
        $sourcePageModel = PageModel::getPageActivityInfo($params['source_id']);
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
        //检查传过来的lang是否在源页面的范围内
        if (!empty($params['lang']) && !\in_array($params['lang'], array_column($sourceActivityInfo['langList'], 'key'), true)) {
            throw new JsonResponseException($this->codeFail, '源页面不包含所选语言');
        }

        //检查目标页面，活动是否加锁，并判断权限
        $targetPageModel = PageModel::getPageActivityInfo($params['target_id']);
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

        //源页面和目标页面是否属于同级站点（比如都是rw/rg/zaful）
        if (explode($sourceActivityInfo['site_code'], '-')[0] !== explode($targetActivityInfo['site_code'], '-')[0]) {
            throw new JsonResponseException($this->codeFail, '源页面和目标页面不属于同一站点下的不同端');
        }
        //检查传过来的lang是否在目标页面的范围内
        if (!empty($params['lang']) && !\in_array($params['lang'], array_column($targetActivityInfo['langList'], 'key'), true)) {
            throw new JsonResponseException($this->codeFail, '所选语言超出目标页面范围');
        }

        return [$sourcePageModel, $targetPageModel];
    }

    /**
     * 获取源页面数据
     *
     * @param int $pageId
     * @param array $lang 语言代码简称
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getSourcePageData(int $pageId, array $lang)
    {
        //取源页面数据
        $uiDataCondition = 'is_m = ' . PageUiComponentDataModel::IS_M
            . ' OR is_public = ' . PageUiComponentDataModel::IS_PUBLIC;

        return $this->getPageLayoutUiData($pageId, $lang, $uiDataCondition);
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
        /** @var PageModel $targetPageModel */
        //校验参数和权限
        list($sourcePageModel, $targetPageModel) = $this->checkConvertParamsAndAuth($params);

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($params['target_id']);

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            list($initRes, $initMsg) = $this->initConvert($sourcePageModel, $targetPageModel);
            if (!$initRes) {
                throw new Exception($initMsg);
            }

            //取源页面数据
            $componentData = $this->getSourcePageData(
                $sourcePageModel->id,
                //这里传的是目标页面的语言项，因为只取源页面中目标页面有的语言项
                explode(',', !empty($params['lang']) ? $params['lang'] : $targetPageModel->lang)
            );

            if (self::CONVERT_MODEL_APPEND === (int)$params['model']) {
                //追加到目标页面内容后面
                if (true !== ($res = $this->appendConvertWap($targetPageModel->id, $componentData))) {
                    throw new Exception($res);
                }
            } elseif (true !== ($res = $this->coverConvertWap($targetPageModel->id, $componentData, $params))) {
                //会直接覆盖目标页面内容
                throw new Exception($res);
            }
            //执行成功，提交事务
            $transaction->commit();
            switch (RequestUtils::getModuleName()) {
                case 'advertisement':
                    $url = '/advertisement/design/index';
                    break;
                case 'gbad':
					$url = '/gbad/design/index';
					break;
                default:
                    $url = '/activity/design/index';
                    break;
            }
            return app()->helper->arrayResult(
                $this->codeSuccess,
                $this->msgSuccess,
                [
                    'redirectUrl' => Url::to([$url, 'pid' => $targetPageModel->pid], true),
                    'siteCode' => $targetPageModel->site_code
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
     *
     * @return bool|string
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function appendConvertWap(int $targetId, array $componentData)
    {
        //第2个$layoutData用不到，空着即可
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        if (!empty($layoutComponent['data'])) {
            /** @var array[] $layoutComponent */
            foreach ($layoutComponent['data'] as $lang => $layout) {
                //获取最后一个layout组件（PC转Wap是将所有ui组件转到wap的最后一个layout内）
                list($resDefault, $layoutId) = $this->getLastLayout($targetId, $lang, self::DEFAULT_LAYOUT_KEY);
                if (!$resDefault) {
                    return $layoutId;
                }

                $params = [$layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds];
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
        list($layoutId, $layout, $uiComponent, $uiData, $relationKeys, $relationTplIds) = $params;

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
        foreach ($layout as $value) {
            if (!empty($uiComponent['data'][$value['id']])) {
                array_push($uiComponentInSort, ...$uiComponent['data'][$value['id']]);
            }
        }

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
     * @param array $params
     *
     * @return bool|string
     * @throws \Exception
     * @throws \Throwable
     */
    private function coverConvertWap(int $targetId, array $componentData, array $params)
    {
        //第2个$layoutData用不到，空着即可
        list($layoutComponent, , $uiComponent, $uiData, $relationKeys, $relationTplIds) = $componentData;

        //清空原有layout
        if (true !== ($resDel = PageLayoutModel::deletePageLayouts($targetId, !empty($params['lang']) ? $params['lang'] : ''))) {
            return $resDel;
        }

        if (!empty($layoutComponent['data'])) {
            /** @var array[] $layoutComponent */
            foreach ($layoutComponent['data'] as $lang => $layout) {
                //创建默认的layout组件（PC转Wap是将所有ui组件转到wap的一个layout内）
                list($resDefault, $layoutId) = $this->createDefaultLayout($targetId, $lang, self::DEFAULT_LAYOUT_KEY);
                if (!$resDefault) {
                    return $layoutId;
                }

                $layout = array_reverse($this->getOrderedComponents($layout));
                $layoutIdsInSort = array_column($layout, 'id');
                //重新写入复制组装过后的数据
                $uiComponentInSort = [];
                foreach ($layout as $value) {
                    if (!empty($uiComponent['data'][$value['id']])) {
                        //这里empty要前置判断，...语法会取出数组中的一项，而array_push() expects at least 2 parameters
                        array_push($uiComponentInSort, ...$uiComponent['data'][$value['id']]);
                    }
                }

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

                $uiModel = new PageUiModel();
                $uiModel->component_key = $relationKeys[$ui['component_key']];
                $uiModel->next_id = $nextId;
                $uiModel->lang = $ui['lang'];
                $uiModel->layout_id = $layoutId;
                $uiModel->position = self::DEFAULT_LAYOUT_POSITION;//wap端的默认放在position=1位置
                $uiModel->tpl_id = $relationTplIds[$ui['tpl_id']] ?? 0;

                if (false === $uiModel->insert(true)) {
                    return $uiModel->flattenErrors(', ');
                }

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
