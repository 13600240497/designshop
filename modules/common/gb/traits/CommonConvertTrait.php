<?php

namespace app\modules\common\gb\traits;

use app\base\SitePlatform;
use app\modules\common\gb\models\{
    ActivityModel, PageConvertRelationModel, PageGroupModel, PageLayoutDataModel, PageLayoutModel, PageModel, PageUiComponentDataModel, PageUiModel
};

use app\modules\component\models\UiTplRelationModel;
use app\modules\common\components\CommonPageUiComponentDataComponent;
use yii\db\Exception;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/07/05
 * Time: 15:03
 */
trait CommonConvertTrait
{
    /**
     * 获取页面UI相关联数据
     *
     * @param int    $pageId          页面ID
     * @param string $lang            语言代码简称
     * @param string $uiDataCondition uiData的额外查询条件(is_m/is_app)
     * @param bool   $isApp           是否是转APP
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getPageLayoutUiData(int $pageId, string $sourceLang, string $uiDataCondition, $isApp = false)
    {
        $LayoutComponentColumns = PageLayoutModel::getTableSchema()->getColumnNames();
        $layoutDataColumns = PageLayoutDataModel::getTableSchema()->getColumnNames();
        $uiComponentColumns = PageUiModel::getTableSchema()->getColumnNames();
        $uiDataColumns = PageUiComponentDataModel::getTableSchema()->getColumnNames();
        
        //取layout数据
        $layoutComponent = PageLayoutModel::find()->select($LayoutComponentColumns)->where([
            'page_id' => $pageId,
            'lang'    => $sourceLang
        ])->asArray()->all();
        $layoutIds = $layoutComponent ? array_column($layoutComponent, 'id') : [];
        $layoutData = PageLayoutDataModel::find()->select($layoutDataColumns)->where([
            'component_id' => $layoutIds
        ])->asArray()->all();
        
        //查ui_component数据
        $uiComponent = PageUiModel::find()->select($uiComponentColumns)->where([
            'layout_id' => $layoutIds
        ])->asArray()->all();
        $uiData = PageUiComponentDataModel::find()->select($uiDataColumns)->where([
            'component_id' => array_column($uiComponent, 'id'),
            'lang'         => $sourceLang
        ])->andWhere($uiDataCondition)->asArray()->all();
        
        $tplIds = array_unique(array_column($uiComponent, 'tpl_id'));
        if ($isApp) {
            // M转APP的保持一一对应关系即可
            $relationTplIds = $tplIds ? UiTplRelationModel::getWapTplRelationId($tplIds) : [];
        } else {
            $relationTplIds = $tplIds ? UiTplRelationModel::getTplRelationId($tplIds) : [];
        }
        
        $relationKeys = empty($uiComponent) ? []
            : UiTplRelationModel::getComponentRelationKey(array_unique(array_column($uiComponent, 'component_key')));
        
        return [
            ['columns' => $LayoutComponentColumns, 'data' => $this->buildLayoutInLang($layoutComponent)],
            //['columns' => $LayoutComponentColumns, 'data' => $layoutComponent],
            ['columns' => $layoutDataColumns, 'data' => $this->buildLayoutDataInLayoutId($layoutData)],
            ['columns' => $uiComponentColumns, 'data' => $this->buildUiInLayoutId($uiComponent)],
            ['columns' => $uiDataColumns, 'data' => $this->buildUiDataInUiId($uiData, $relationTplIds)],
            $relationKeys,
            $relationTplIds
        ];
    }
    
    /**
     * 按语言来对layout分组
     *
     * @param array $layoutArr
     *
     * @return array
     */
    private function buildLayoutInLang(array $layoutArr)
    {
        $data = [];
        if (!empty($layoutArr)) {
            foreach ($layoutArr as $layout) {
                $data[ $layout['lang'] ][] = $layout;
            }
        }
        
        return $data;
    }
    
    /**
     * 按layoutId来对layoutData分组
     *
     * @param array $layoutDataArr
     *
     * @return array
     */
    private function buildLayoutDataInLayoutId(array $layoutDataArr)
    {
        $data = [];
        if (!empty($layoutDataArr)) {
            foreach ($layoutDataArr as $layoutData) {
                $data[ $layoutData['component_id'] ][] = $layoutData;
            }
        }
        
        return $data;
    }
    
    /**
     * 按layoutId来对layout分组
     *
     * @param array $uiArr ui数组
     *
     * @return array
     */
    private function buildUiInLayoutId(array $uiArr)
    {
        $data = [];
        if (!empty($uiArr)) {
            foreach ($uiArr as $ui) {
                $data[ $ui['layout_id'] ][] = $ui;
            }
        }
        
        return $data;
    }
    
    /**
     * 按uiId组装uiData数据
     *
     * @param array $uiDataArr
     * @param array $relationTplIds
     *
     * @return array
     */
    private function buildUiDataInUiId(array $uiDataArr, array $relationTplIds)
    {
        $data = [];
        if (!empty($uiDataArr)) {
            foreach ($uiDataArr as $uiData) {
                //模板对应转换规则
                $uiData['tpl_id'] = $relationTplIds[ $uiData['tpl_id'] ] ?? 0;
                //查询模板内字段属性配置
                $fieldsConfig = CommonPageUiComponentDataComponent::getConfig($uiData['tpl_id'], 'fields');
                //修改字段属性
                if (!$uiData['is_public']) {
                    //!!!对于公开的而言，其tpl_id字段不准确!!!
                    $uiData['is_public'] = $fieldsConfig[ $uiData['key'] ]['is_public'] ?? 0;//is_public
                }
                $uiData['is_m'] = $fieldsConfig[ $uiData['key'] ]['is_m'] ?? 0;//is_m
                $uiData['is_app'] = $fieldsConfig[ $uiData['key'] ]['is_app'] ?? 0;//is_app
                //分组存储
                $data[ $uiData['component_id'] ][] = $uiData;
            }
        }
        
        return $data;
    }
    
    /**
     * 拷贝layoutData
     *
     * @param array $layoutDataArr
     * @param array $layoutDataColumn
     * @param int   $layoutId
     *
     * @return bool|string
     */
    private function copyPageLayoutData(array $layoutDataArr, array $layoutDataColumn, int $layoutId)
    {
        if (!empty($layoutDataArr)) {
            $layoutDataColumn = array_values(array_diff($layoutDataColumn, ['id']));
            foreach ($layoutDataArr as &$layoutData) {
                unset($layoutData['id']);
                $layoutData['component_id'] = $layoutId;
            }
            unset($layoutData);
            try {
                PageLayoutDataModel::insertAll($layoutDataColumn, $layoutDataArr);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            
        }
        
        return true;
    }
    
    /**
     * 拷贝uiData
     *
     * @param array $uiDataArr
     * @param array $uiDataColumn
     * @param int   $uiId
     *
     * @return bool|string
     */
    private function copyPageUiData(array $uiDataArr, array $uiDataColumn, int $uiId)
    {
        if (!empty($uiDataArr)) {
            $uiDataColumn = array_values(array_diff($uiDataColumn, ['id']));
            foreach ($uiDataArr as &$uiData) {
                unset($uiData['id']);
                $uiData['component_id'] = $uiId;
            }
            unset($uiData);
            try {
                PageUiComponentDataModel::insertAll($uiDataColumn, $uiDataArr);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            
        }
        
        return true;
    }
    
    /**
     * 页面转换基础信息记录
     *
     * @param \app\modules\common\models\PageModel $sourcePageModel
     * @param \app\modules\common\models\PageModel $targetPageModel
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    private function initConvert($sourcePageModel, $targetPageModel)
    {
        $targetPageModel->auto_refresh = PageModel::NOT_REFRESH;//页面内容有变更，不能自动刷新
        $targetPageModel->verify_user = '';
        $targetPageModel->verify_time = 0;
        
        //更新页面信息
        if (false === $targetPageModel->save(true)) {
            return [false, $targetPageModel->flattenErrors(', ')];
        }
        
        //记录转换关系
        if (true !== ($relation = $this->saveConvertRelation($sourcePageModel->id, $targetPageModel->id))) {
            return [false, $relation];
        }
        
        return [true, ''];
    }
    
    /**
     * 记录转换关系
     *
     * @param int $sourceId
     * @param int $targetId
     *
     * @return bool|string
     * @throws \Throwable
     */
    private function saveConvertRelation(int $sourceId, int $targetId)
    {
        $convertRelation = [
            'source_id' => $sourceId,
            'target_id' => $targetId
        ];
        
        if (!($relationModel = PageConvertRelationModel::findOne($convertRelation))) {
            //没有则新增记录
            $relationModel = new PageConvertRelationModel();
            $relationModel->load($convertRelation, '');
            $relationModel->update_user = app()->user->username;//新增时也需要记录update_time信息，会根据这个字段排序的
            $relationModel->update_time = time();
        }
        
        if (false === $relationModel->save(true)) {
            return $relationModel->flattenErrors(', ');
        }
        
        return true;
    }
    
    /**
     * 覆盖式-创建默认layout
     *
     * @param int    $pageId              页面ID
     * @param string $lang
     * @param string $defaultComponentKey 默认组件key
     *
     * @return array
     * @throws \Throwable
     */
    private function createDefaultLayout(int $pageId, string $lang, string $defaultComponentKey)
    {
        $layoutModel = new PageLayoutModel();
        $layoutModel->page_id = $pageId;
        $layoutModel->lang = $lang;
        $layoutModel->component_key = $defaultComponentKey;
        $layoutModel->next_id = 0;
        if (false === $layoutModel->insert(true)) {
            return [false, $layoutModel->flattenErrors(', ')];
        }
        
        // 生成默认的layout data数据
        $layoutDataModel = new PageLayoutDataModel();
        $layoutDataModel->component_id = $layoutModel->id;
        $layoutDataModel->lang = $lang;
        $layoutDataModel->data = json_encode([]);
        if (false === $layoutDataModel->insert(true)) {
            return [false, $layoutDataModel->flattenErrors(', ')];
        }
        
        return [true, $layoutModel->id];
    }
    
    /**
     * 追加式-获取最后一个layout
     *
     * @param int    $pageId              页面ID
     * @param string $lang
     * @param string $defaultComponentKey 默认组件key
     *
     * @return array
     * @throws \Throwable
     */
    private function getLastLayout(int $pageId, string $lang, string $defaultComponentKey)
    {
        if ($layoutModel = PageLayoutModel::findOne([
            'page_id' => $pageId,
            'lang'    => $lang,
            'next_id' => 0
        ])
        ) {
            return [true, $layoutModel->id];
        }
        
        return $this->createDefaultLayout($pageId, $lang, $defaultComponentKey);
    }
    
    /**
     * 获取页面的转换关系
     *
     * @param int    $page_id
     * @param string $lang
     * @param string $siteCode
     * @param string $pipeline
     *
     * @return array
     */
    public function getConvertRelationList(int $page_id, string $lang, string $siteCode, string $pipeline)
    {
        $site = explode('-', $siteCode);
        $list = [
            'pc'      => ['name' => 'PC端', 'siteCode' => $site[0] . '-pc', 'url' => ''],
            'wap'     => ['name' => 'M端', 'siteCode' => $site[0] . '-wap', 'url' => ''],
            'ios'     => ['name' => 'IOS端', 'siteCode' => $site[0] . '-ios', 'url' => ''],
            'android' => ['name' => '安卓端', 'siteCode' => $site[0] . '-android', 'url' => '']
        ];
        //子页面分组信息
        $pageToGroupMap = PageGroupModel::find()->where(['page_id' => $page_id])->indexBy('page_id')->asArray()->all();
        if (!empty($pageToGroupMap)) {
            $pageGroupIds = array_column($pageToGroupMap, 'page_group_id');
            $pageGroupModelList = PageGroupModel::find()->where(['page_group_id' => $pageGroupIds])->indexBy('platform_type')->asArray()->all();

            foreach ($list as $key => $item) {
                $platform = SitePlatform::getPlatformTypeByPlatformCode($key);
                if (!empty($pageGroupModelList[$platform])) {
                    $page = PageModel::find()->select('group_id')->where(['id'=>$pageGroupModelList[$platform]['page_id']])->one();
                    $list[$key]['url'] = $this->getPageDesignUrl($page['group_id'], $lang, $pipeline);
                } else {
                    unset($list[$key]);
                }
            }
        }
        return $list;
    }
    
    /**
     * 根据PC获取Wap和App信息
     *
     * @param int    $pageId
     * @param string $lang
     * @param string $pipeline
     * @param array  $list
     */
    private function getWapAndAppByPc(int $pageId, string $lang, string $pipeline, array &$list)
    {
        //查询wap
        if ($wapPage = PageConvertRelationModel::getLastTargetPid($pageId)) {
            $list['wap']['url'] = $this->getPageDesignUrl($wapPage->group_id, $lang, $pipeline);
            
            //查询app
            if ($appPage = PageConvertRelationModel::getLastTargetPid($wapPage->page_id)) {
                $site = explode('-', $appPage->site_code);
                $list[ $site[1] ]['url'] = $this->getPageDesignUrl($appPage->group_id, $lang, $pipeline);
            } else {
                unset($list['android'], $list['ios']);//没有的则去掉这个key
            }
        } else {
            $list = [];//只有一项也需置空，不展示
        }
    }
    
    /**
     * 根据Wap获取Pc和App信息
     *
     * @param int    $pageId
     * @param string $lang
     * @param string $pipeline
     * @param array  $list
     */
    private function getPcAndAppByWap(int $pageId, string $lang, string $pipeline, array &$list)
    {
        //查询pc
        if ($pcPage = PageConvertRelationModel::getLastSourcePid($pageId)) {
            $list['pc']['url'] = $this->getPageDesignUrl($pcPage->group_id, $lang, $pipeline);
        } else {
            unset($list['pc']);//没有的则去掉这个key
        }
        
        //查询app
        if ($appPage = PageConvertRelationModel::getLastTargetPid($pageId)) {
            $site = explode('-', $appPage->site_code);
            $list[ $site[1] ]['url'] = $this->getPageDesignUrl($appPage->group_id, $lang, $pipeline);
        } else {
            unset($list['android'], $list['ios']);//没有的则去掉这个key
        }
    }
    
    /**
     * 根据App获取Pc和Wap信息
     *
     * @param int    $pageId
     * @param string $lang
     * @param string $pipeline
     * @param array  $list
     */
    private function getPcAndWapByApp(int $pageId, string $lang, string $pipeline, string $type, array &$list)
    {
        //查询wap
        if ($wapPage = PageConvertRelationModel::getLastSourcePid($pageId)) {
            $list['wap']['url'] = $this->getPageDesignUrl($wapPage->group_id, $lang, $pipeline);
            
            //查询pc
            if ($pcPage = PageConvertRelationModel::getLastSourcePid($wapPage->page_id)) {
                $list['pc']['url'] = $this->getPageDesignUrl($pcPage->group_id, $lang, $pipeline);
            } else {
                unset($list['pc']);//没有的则去掉这个key
            }
    
            //查询app
            if ($appPage = PageConvertRelationModel::getLastSourcePid($pageId)) {
                $platform = ('ios' == $type) ? 'android' : 'ios';
                $list[ $platform ]['url'] = $this->getPageDesignUrl($appPage->group_id, $lang, $pipeline);
            }
        } else {
            $list = [];//只有一项也需置空，不展示
        }
    }
    
    /**
     * 获取页面预览地址
     *
     * @param string $pid
     * @param string $lang
     * @param string $pipeline
     *
     * @return string
     */
    public function getPagePreviewUrl(string $pid, string $lang, string $pipeline)
    {
        return Url::to([
            '/activity/' . app()->controller->module->id . '/design/preview',
            'pid'      => $pid,
            'pipeline' => $pipeline,
            'lang'     => $lang
        ], true);
    }
    
    /**
     * 获取页面装修地址
     *
     * @param string $groupId
     * @param string $lang
     * @param string $pipeline
     *
     * @return string
     */
    private function getPageDesignUrl(string $groupId, string $lang, string $pipeline)
    {
        return Url::to([
            '/activity/' . app()->controller->module->id . '/design/index',
            'group_id' => $groupId,
            'pipeline' => $pipeline,
            'lang'     => $lang
        ], true);
    }
}
