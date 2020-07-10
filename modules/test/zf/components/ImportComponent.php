<?php

namespace app\modules\test\zf\components;

use app\modules\common\models\ActivityModel;
use app\modules\common\models\ActivityGroupModel;
use app\modules\common\models\PageModel;
use app\modules\common\models\PageConvertRelationModel;
use app\modules\common\models\PageGroupModel;
use app\modules\common\models\PageLanguageModel;
use app\modules\common\models\PageLayoutModel;
use app\modules\common\models\PageLayoutDataModel;
use app\modules\common\models\PageTplModel;
use app\modules\common\models\PageUiModel;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\common\zf\models\ActivityModel as ZfActivityModel;
use app\modules\common\zf\models\ActivityGroupModel as ZfActivityGroupModel;
use app\modules\common\zf\models\PageModel as ZfPageModel;
use app\modules\common\zf\models\PageConvertRelationModel as ZfPageConvertRelationModel;
use app\modules\common\zf\models\PageGroupModel as ZfPageGroupModel;
use app\modules\common\zf\models\PageLanguageModel as ZfPageLanguageModel;
use app\modules\common\zf\models\PageLayoutModel as ZfPageLayoutModel;
use app\modules\common\zf\models\PageLayoutDataModel as ZfPageLayoutDataModel;
use app\modules\common\zf\models\PageTplModel as ZfPageTplModel;
use app\modules\common\zf\models\PageUiModel as ZfPageUiModel;
use app\modules\common\zf\models\PageUiComponentDataModel as ZfPageUiComponentDataModel;
use yii\db\Exception;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * 数据导入组件
 */
class ImportComponent extends Component
{
    /**
     * @var array 跳过的数据ID
     */
    public $ignoreIds = [];

    /**
     * @var array 错误的数据ID
     */
    public $errorIds = [];

    /**
     * @var array 错误信息集合
     */
    public $errors = [];

    /**
     * @var int 总记录数
     */
    public $allCount = 0;

    /**
     * @var array ZF站点site_code集合
     */
    public $zf_site_code = [];

    /**
     * @var array ZF站点语言和渠道对应关系
     */
    public $zf_lang_to_pipeline = [];

    /**
     * @var array ZF站点渠道和语言对应关系
     */
    public $zf_pipeline_to_lang = [];

    /**
     * @var array ZF站点需要过滤掉的语言(一般需要过滤掉ar语言)
     */
    public $zf_ignore_lang = [];

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->zf_site_code = ['zf-pc', 'zf-wap', 'zf-app'];
        $this->zf_lang_to_pipeline = [
            'en' => 'ZF',
            'es' => 'ZFES',
            'fr' => 'ZFFR',
            'pt' => 'ZFPT',
            'it' => 'ZFIT',
            'de' => 'ZFDE',
        ];
        $this->zf_pipeline_to_lang = array_flip($this->zf_lang_to_pipeline);
        $this->zf_ignore_lang = ['ar'];
    }

    public function noGroupPages()
    {
        $pages = ZfPageModel::find()
            ->where(['between', 'activity_id', 1, 500])
            ->andWhere(['is_delete' => ZfPageModel::NOT_DELETE])
            ->asArray()
            ->all();


        $activityIds = array_column($pages, 'activity_id');
        $activityMapping = ZfActivityModel::find()->where(['id' => $activityIds])->indexBy('id')->asArray()->all();

        $groupIds = array_column($activityMapping, 'group_id');
        $activityGroupMapping = ZfActivityGroupModel::find()->where(['id' => $groupIds])->indexBy('id')->asArray()->all();

        //print_r($pageGroupMapping); print_r($pageActivityMapping);exit();
        $pageIds = array_column($pages, 'id');
        $pageGroupMapping = ZfPageGroupModel::find()->where(['page_id' => $pageIds])->indexBy('page_id')->asArray()->all();

        $pageGroupIdMapping = [];
        foreach ($pages as $pageInfo) {
            $pageId = $pageInfo['id'];
            if (isset($pageGroupMapping[$pageId]))
                continue;

            $pageGroupIdMapping[$pageInfo['group_id']][] = $pageInfo;
        }

        foreach($pageGroupIdMapping as $groupId => $pageList) {

            //事物开始
            $transaction = app()->db->beginTransaction();
            try {

                $pageGroupId = ZfPageGroupModel::generatePageGroupId();
                foreach ($pageList as $_pageInfo) {

                    $activityId = $pageInfo['activity_id'];
                    $activityInfo = $activityMapping[$activityId];

                    $groupId = $activityInfo['group_id'];
                    if (!isset($activityGroupMapping[$groupId])) {
                        throw new Exception('没有分组ID');
                    }

                    //保存页面分组
                    /** @var \app\modules\common\zf\models\PageGroupModel $pageGroupModel */
                    $pageGroupModel = new ZfPageGroupModel();
                    $pageGroupModel->activity_group_id = $groupId;
                    $pageGroupModel->page_group_id = $pageGroupId;
                    $pageGroupModel->platform_type = SitePlatform::getPlatformTypeBySiteCode($_pageInfo['site_code']);
                    $pageGroupModel->page_id = $_pageInfo['id'];;
                    $pageGroupModel->pipeline = $_pageInfo['pipeline'];
                    if (!$pageGroupModel->insert(true)) {
                        throw new Exception('添加子页面分组失败');
                    }
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->errorIds[] = $groupId;
                $this->errors[$groupId] = $e->getMessage() . $e->getTraceAsString();
            }
        }

        $this->allCount = \count($pageGroupIdMapping);
        return $this->returnData();
    }

    private function getActivityLangList($pipelineList) {
        $langList = [];
        foreach ($pipelineList as $code) {
            $langList[$code][] = $this->zf_pipeline_to_lang[$code];
        }
        return json_encode($langList);
    }

    /**
     * 导入zf_activity表数据
     * @return array
     * @throws \Throwable
     */
    public function importActivity()
    {
        /** @var array[] $oldDatas */
        $oldDatas = ActivityModel::find()
            ->where([
                'site_code' => $this->zf_site_code
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();

        $this->allCount = \count($oldDatas);
        if (!empty($oldDatas)) {
            $groupMaxId = ActivityGroupModel::find()->max('id');
            foreach ($oldDatas as $data) {
                // 先检查是否已存在
                if (ZfActivityModel::findOne($data['id'])) {
                    $this->ignoreIds[] = $data['id'];
                    continue;
                }

                // 对于group_id为0的历史数据，修复补全activity_group表记录
                if ((int)$data['group_id'] === 0) {
                    $newGroup = new ZfActivityGroupModel();
                    $newGroup->id = $groupMaxId + $data['id'];
                    $newGroup->platform_list = explode('-', $data['site_code'])[1];
                    $newGroup->lang_list = $this->langToJsonLang($data['lang']);

                    if (!$newGroup->insert(true)) {
                        $this->errorIds[] = $data['id'];
                        $this->errors[$data['id']] = $newGroup->flattenErrors(', ');
                        continue;
                    }

                    $data['group_id'] = $newGroup->id;
                }

                // 不存在的再添加
                $newData = new ZfActivityModel();
                $newData->setAttributes($data, false);
                // 对有变动的字段重新赋值
                $newData->pipeline = $this->langToPipeline($data['lang']);
                $newData->lang = $this->langToJsonLang($data['lang']);

                if (!$newData->insert(true)) {
                    $this->errorIds[] = $data['id'];
                    $this->errors[$data['id']] = $newData->flattenErrors(', ');
                }


            }
        }

        return $this->returnData();
    }

    /**
     * 导入zf_activity_group表数据
     * @return array
     * @throws \Throwable
     */
    public function importActivityGroup()
    {
        $groupList = [];
        $activitys = ZfActivityModel::find()->asArray()->all();

        if (!empty($activitys)) {
            $groupIds = array_unique(array_column($activitys, 'group_id'));
            $groupList = ActivityGroupModel::find()->where([
                    'id' => $groupIds
                ])
                ->orderBy('id ASC')
                ->asArray()
                ->all();
        }

        if (!empty($groupList)) {
            $this->allCount = \count($groupList);
            foreach ($groupList as $group) {
                // 先检查是否已存在
                if (ZfActivityGroupModel::findOne($group['id'])) {
                    $this->ignoreIds[] = $group['id'];
                    continue;
                }
                // 不存在的再添加
                $newGroup = new ZfActivityGroupModel();
                $newGroup->setAttributes($group, false);
                // 对有变动的字段重新赋值
                $newGroup->lang_list = $this->langToJsonLang($group['lang_list']);

                if (!$newGroup->insert(true)) {
                    $this->errorIds[] = $group['id'];
                    $this->errors[$group['id']] = $newGroup->flattenErrors(', ');
                }
            }
        }

        return $this->returnData();
    }

    /**
     * 导入zf_page_convert_relation表数据
     * @return array
     * @throws \Throwable
     */
    public function importPageConvertRelation()
    {
        /** @var array[] $oldDatas */
        $oldDatas = PageConvertRelationModel::find()->alias('pcr')
            ->leftJoin(PageModel::tableName() . ' as p', 'pcr.source_id = p.id')
            ->where([
                'p.site_code' => $this->zf_site_code
            ])
            ->orderBy('pcr.id ASC')
            ->asArray()
            ->all();

        $this->allCount = \count($oldDatas);
        if (!empty($oldDatas)) {
            foreach ($oldDatas as $data) {
                // 先检查是否已存在
                if (ZfPageConvertRelationModel::findOne($data['id'])) {
                    $this->ignoreIds[] = $data['id'];
                    continue;
                }
                // 不存在的再添加
                $newData = new ZfPageConvertRelationModel();
                $newData->setAttributes($data, false);

                if (!$newData->insert(true)) {
                    $this->errorIds[] = $data['id'];
                    $this->errors[$data['id']] = $newData->flattenErrors(', ');
                }
            }
        }

        return $this->returnData();
    }

    /**
     * 导入zf_page_template表数据
     * @return array
     * @throws \Throwable
     */
    public function importPageTemplate()
    {
        /** @var array[] $oldDatas */
        $oldDatas = PageTplModel::find()
            ->where([
                'site_code' => $this->zf_site_code
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();

        $this->allCount = \count($oldDatas);
        if (!empty($oldDatas)) {
            foreach ($oldDatas as $data) {
                // 先检查是否已存在
                if (ZfPageTplModel::findOne($data['id'])) {
                    $this->ignoreIds[] = $data['id'];
                    continue;
                }
                // 不存在的再添加
                $newData = new ZfPageTplModel();
                $newData->setAttributes($data, false);
                // 对有变动的字段重新赋值
                $newData->pipeline = $this->zf_lang_to_pipeline[$data['lang']];

                if (!$newData->insert(true)) {
                    $this->errorIds[] = $data['id'];
                    $this->errors[$data['id']] = $newData->flattenErrors(', ');
                }
            }
        }

        return $this->returnData();
    }

    /**
     * 导入zf_page表数据
     * @param int $start
     * @param int $end
     * @return array
     * @throws \Throwable
     */
    public function importPage($start, $end)
    {
        /** @var array[] $oldPages */
        $oldPages = PageModel::find()
            ->where([
                'site_code' => $this->zf_site_code
            ])
            ->andWhere(['>=', 'id', $start])
            ->andWhere(['<', 'id', $end])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        $this->allCount = \count($oldPages);
        if (empty($oldPages)) {
            return $this->returnData();
        }

        // 先获取所有数据
        $pageIds = array_column($oldPages, 'id');
        $pageData = $this->getPageAllData($pageIds);
        foreach ($oldPages as $page) {
            // 先检查是否已存在
            if (ZfPageModel::findOne($page['id'])) {
                $this->ignoreIds[] = $page['id'];
                continue;
            }
            // 再插入数据
            $this->insertNewPageData($page, $pageData);
        }

        return $this->returnData();
    }

    /**
     * 插入新的页面数据
     * @param array $page
     * @param array $pageData
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    private function insertNewPageData(array $page, array $pageData)
    {
        list($oldPageLanguages, $oldPageLayouts, $oldPageLayoutDatas, $oldPageUis, $oldPageUiDatas) = $pageData;

        // 开启事物
        $tr = app()->db->beginTransaction();
        try {
            // 插入page表
            if (empty($newPageIds = $this->insertPage($page, $oldPageLanguages))) {
                throw new Exception('insertPage插入失败');
            }
            // 插入page_language表
            if (!$this->insertPageLanguage($page, $oldPageLanguages, $newPageIds)) {
                throw new Exception('insertPageLanguage插入失败');
            }

            // 插入page_layout_component、page_layout_data表
            $newLayoutIds = $this->insertPageLayoutAndData($page, $newPageIds, $oldPageLayouts, $oldPageLayoutDatas);
            if (!$newLayoutIds) {
                throw new Exception('insertPageLayoutAndData插入失败');
            }

            // 插入page_ui_component、page_ui_component_data表
            if (true !== $newLayoutIds && true !== $this->insertPageUiAndData($page, $newLayoutIds, $oldPageUis, $oldPageUiDatas)) {
                throw new Exception('insertPageUiAndData插入失败');
            }

            $tr->commit();
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errorIds[] = $page['id'];
            $this->errors[$page['id']] = $e->getMessage() . $e->getTraceAsString();

            return false;
        }

        return true;
    }

    /**
     * 插入page_ui_component、page_ui_component_data表
     * @param array $page
     * @param array $newLayoutIds
     * @param array $oldPageUis
     * @param array $oldPageUiDatas
     * @return array|bool
     * @throws \Throwable
     */
    private function insertPageUiAndData(array $page, array $newLayoutIds, array $oldPageUis, array $oldPageUiDatas)
    {
        if (empty($newLayoutIds)) {
            return true;
        }

        foreach ($newLayoutIds as $layoutId) {
            $pageUis = $oldPageUis[$layoutId] ?? [];
            if (empty($pageUis)) {
                continue;
            }

            foreach ($pageUis as $pageUi) {
                // 基于layout保持原ID，ui直接复制即可
                $newPageUi = new ZfPageUiModel();
                $newPageUi->setAttributes($pageUi, false);

                if (!$newPageUi->insert(true)) {
                    $this->errorIds[] = $page['id'];
                    $this->errors[$page['id']] = $newPageUi->flattenErrors(', ');
                    return false;
                }

                // 复制ui_data
                $oldPageUiDataByUiId = $oldPageUiDatas[$pageUi['id']] ?? [];
                if (empty($oldPageUiDataByUiId)) {
                    continue;
                }
                foreach ($oldPageUiDataByUiId as $pageUiData) {
                    // 基于ui保持原ID，ui_data直接复制即可
                    $newPageUiData = new ZfPageUiComponentDataModel();
                    $newPageUiData->setAttributes($pageUiData, false);

                    if (!$newPageUiData->insert(true)) {
                        $this->errorIds[] = $page['id'];
                        $this->errors[$page['id']] = $newPageUiData->flattenErrors(', ');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 插入page_layout_component、page_layout_data表
     * @param array $page
     * @param array $newPageIds
     * @param array $oldPageLayouts
     * @param array $oldPageLayoutDatas
     * @return array|bool
     * @throws \Throwable
     */
    private function insertPageLayoutAndData(array $page, array $newPageIds, array $oldPageLayouts, array $oldPageLayoutDatas)
    {
        $oldPageLayouts = $oldPageLayouts[$page['id']] ?? [];
        if (empty($oldPageLayouts)) {
            return true;
        }

        $layoutIds = [];
        foreach ($oldPageLayouts as $pageLayout) {
            if (\in_array($pageLayout['lang'], $this->zf_ignore_lang, true)) {
                continue;
            }
            $newPageLayout = new ZfPageLayoutModel();
            $newPageLayout->setAttributes($pageLayout, false);
            // 对有变动的字段重新赋值【保留原ID】
            $newPageLayout->page_id = $newPageIds[$pageLayout['lang']]['page_id'];

            if (!$newPageLayout->insert(true)) {
                $this->errorIds[] = $page['id'];
                $this->errors[$page['id']] = $newPageLayout->flattenErrors(', ');
                return [];
            }
            $layoutIds[] = $pageLayout['id'];

            // 复制layout_data
            $oldPageLayoutDataByLayoutId = $oldPageLayoutDatas[$pageLayout['id']] ?? [];
            if (empty($oldPageLayoutDataByLayoutId)) {
                continue;
            }
            foreach ($oldPageLayoutDataByLayoutId as $pageLayoutData) {
                // 基于layout保持原ID，layout_data直接复制即可
                $newPageLayoutData = new ZfPageLayoutDataModel();
                $newPageLayoutData->setAttributes($pageLayoutData, false);

                if (!$newPageLayoutData->insert(true)) {
                    $this->errorIds[] = $page['id'];
                    $this->errors[$page['id']] = $newPageLayoutData->flattenErrors(', ');
                    return [];
                }
            }
        }

        return $layoutIds;
    }

    /**
     * 插入page_language表
     * @param array $page
     * @param array $oldPageLanguages
     * @param array $newPageIds
     * @return bool
     * @throws \Throwable
     */
    private function insertPageLanguage(array $page, array $oldPageLanguages, array $newPageIds)
    {
        $oldPageLanguages = $oldPageLanguages[$page['id']] ?? [];
        if (empty($oldPageLanguages)) {
            return false;
        }

        foreach ($oldPageLanguages as $pageLanguage) {
            if (\in_array($pageLanguage['lang'], $this->zf_ignore_lang, true)) {
                continue;
            }
            $newPageLanguage = new ZfPageLanguageModel();
            $newPageLanguage->setAttributes($pageLanguage, false);
            // 对有变动的字段重新赋值
            $newPageLanguage->group_id = $newPageIds[$pageLanguage['lang']]['group_id'];
            $newPageLanguage->page_id = $newPageIds[$pageLanguage['lang']]['page_id'];

            if (!$newPageLanguage->insert(true)) {
                $this->errorIds[] = $page['id'];
                $this->errors[$page['id']] = $newPageLanguage->flattenErrors(', ');
                return false;
            }
        }

        return true;
    }

    /**
     * 插入page表
     * @param array $page
     * @param array $oldPageLanguages
     * @return array
     * @throws \Throwable
     */
    private function insertPage(array $page, array $oldPageLanguages)
    {
        // 需要将page按照lang的个数拆分为对应的多个page记录
        $langs = $oldPageLanguages[$page['id']] ?? [];
        if (empty($langs)) {
            return [];
        }

        $langs = array_column($langs, 'lang');
        $newPageIds = [];
        $langs = explode(',', $this->sortByLangAsString(implode(',', $langs)));
        $pageId = $page['id'];
        unset($page['id'], $page['url_name']);
        $maxId = PageModel::find()->max('id') + 100;
        foreach ($langs as $key => $lang) {
            if (\in_array($lang, $this->zf_ignore_lang, true)) {
                continue;
            }
            $newPage = new ZfPageModel();
            $newPage->setAttributes($page, false);
            $newPage->id = $maxId * $key + $pageId; // !!!需要保证id不重复!!!
            $newPage->pid = md5(microtime());
            // 对有变动的字段重新赋值
            $newPage->group_id = md5($pageId . $page['pid']);
            $newPage->pipeline = $this->zf_lang_to_pipeline[$lang];
            $newPage->default_lang = $lang;

            if (!$newPage->insert(true)) {
                $this->errorIds[] = $pageId;
                $this->errors[$pageId] = $newPage->flattenErrors(', ');
                return [];
            }

            $newPageIds[$lang] = [
                'page_id' => $newPage->id,
                'group_id' => $newPage->group_id
            ];
        }

        return $newPageIds;
    }

    /**
     * 获取page页面的所有数据
     * @param array $pageIds
     * @return array
     */
    private function getPageAllData(array $pageIds)
    {
        // 查询page_language
        $oldPageLanguages = PageLanguageModel::find()
            ->where([
                'page_id' => $pageIds
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        // 查询page_layout_component
        $oldPageLayouts = PageLayoutModel::find()
            ->where([
                'page_id' => $pageIds
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        $layoutIds = $oldPageLayouts ? array_column($oldPageLayouts, 'id') : [];
        // 查询page_layout_component
        $oldPageLayoutDatas = PageLayoutDataModel::find()
            ->where([
                'component_id' => $layoutIds
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        // 查询page_ui_component
        $oldPageUis = PageUiModel::find()
            ->where([
                'layout_id' => $layoutIds
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        $uiIds = $oldPageUis ? array_column($oldPageUis, 'id') : [];
        // 查询page_layout_component
        $oldPageUiDatas = PageUiComponentDataModel::find()
            ->where([
                'component_id' => $uiIds
            ])
            ->orderBy('id ASC')
            ->asArray()
            ->all();

        return [
            $this->groupOldPageLanguages($oldPageLanguages),
            $this->groupOldPageLayout($oldPageLayouts),
            $this->groupOldPageLayoutData($oldPageLayoutDatas),
            $this->groupOldPageUi($oldPageUis),
            $this->groupOldPageUiData($oldPageUiDatas),
        ];
    }

    /**
     * 对page_language按page_id分组
     * @param array $oldPageLanguages
     * @return array
     */
    private function groupOldPageLanguages(array $oldPageLanguages)
    {
        $data = [];
        if (!empty($oldPageLanguages)) {
            foreach ($oldPageLanguages as $item) {
                $data[$item['page_id']][] = $item;
            }
        }

        return $data;
    }

    /**
     * 对page_lauout_component按page_id分组
     * @param array $oldPageLayouts
     * @return array
     */
    private function groupOldPageLayout(array $oldPageLayouts)
    {
        $data = [];
        if (!empty($oldPageLayouts)) {
            foreach ($oldPageLayouts as $item) {
                $data[$item['page_id']][] = $item;
            }
        }

        return $data;
    }

    /**
     * 对page_lauout_data按component_id分组
     * @param array $oldPageLayoutDatas
     * @return array
     */
    private function groupOldPageLayoutData(array $oldPageLayoutDatas)
    {
        $data = [];
        if (!empty($oldPageLayoutDatas)) {
            foreach ($oldPageLayoutDatas as $item) {
                $data[$item['component_id']][] = $item;
            }
        }

        return $data;
    }

    /**
     * 对page_ui_component按layout_id分组
     * @param array $oldPageUis
     * @return array
     */
    private function groupOldPageUi(array $oldPageUis)
    {
        $data = [];
        if (!empty($oldPageUis)) {
            foreach ($oldPageUis as $item) {
                $data[$item['layout_id']][] = $item;
            }
        }

        return $data;
    }

    /**
     * 对page_ui_component_data按component_id分组
     * @param array $oldPageUiDatas
     * @return array
     */
    private function groupOldPageUiData(array $oldPageUiDatas)
    {
        $data = [];
        if (!empty($oldPageUiDatas)) {
            foreach ($oldPageUiDatas as $item) {
                $data[$item['component_id']][] = $item;
            }
        }

        return $data;
    }

    /**
     * 导入zf_page_group表数据
     * @return array
     * @throws \Throwable
     */
    public function importPageGroup()
    {
        $oldPageGroups = PageGroupModel::find()->alias('pg')
            ->leftJoin(PageModel::tableName() . ' as p', 'pg.page_id = p.id')
            ->where([
                'p.site_code' => $this->zf_site_code
            ])
            ->asArray()
            ->all();
        $newPages = ZfPageModel::find()->asArray()->all();
        $newPagesByGroupId = $newPagesById = [];
        if (!empty($newPages)) {
            foreach ($newPages as $page) {
                $newPagesById[$page['id']] = $page['group_id'];
                $newPagesByGroupId[$page['group_id']][] = $page;
            }
        }

        if (!empty($oldPageGroups) && !empty($newPages)) {
            $this->allCount = \count($oldPageGroups);
            foreach ($oldPageGroups as $pageGroup) {
                /** @var array $pageGroup */
                // 先判断补充几条记录
                $groupId = $newPagesById[$pageGroup['page_id']] ?? '0';
                $pageList = $newPagesByGroupId[$groupId] ?? [];
                if (empty($pageList)) {
                    continue;
                }
                foreach ($pageList as $pageItem) {
                    // 先检查是否已存在
                    if (ZfPageGroupModel::findOne([
                        'page_id' => $pageItem['id'],
                        'pipeline' => $pageItem['pipeline']
                    ])) {
                        $this->ignoreIds[] = $pageGroup['page_id'];
                        continue;
                    }
                    // 不存在的再添加
                    unset($pageGroup['id']);
                    $newPageGroup = new ZfPageGroupModel();
                    $newPageGroup->setAttributes($pageGroup, false);
                    // 对有变动的字段重新赋值
                    $newPageGroup->page_id = $pageItem['id'];
                    $newPageGroup->pipeline = $pageItem['pipeline'];

                    if (!$newPageGroup->insert(true)) {
                        $this->errorIds[] = $pageGroup['page_id'];
                        $this->errors[$pageGroup['page_id']] = $newPageGroup->flattenErrors(', ');
                    }
                }
            }
        }

        return $this->returnData();
    }

    /**
     * 返回数据
     * @return array
     */
    private function returnData()
    {
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
            'errorIds' => implode(',', array_unique($this->errorIds)),
            'ignoreIds' => implode(',', array_unique($this->ignoreIds)),
            'errors' => $this->errors,
            'message' => '总条数：' . $this->allCount . '，失败：' . \count(array_unique($this->errorIds))
                . '，跳过：' . \count(array_unique($this->ignoreIds))
        ]);
    }

    /**
     * 语言转成对应的渠道
     * @param string $lang
     * @return string
     */
    private function langToPipeline(string $lang)
    {
        $pipelineArr = [];
        $langArr = explode(',', $lang);
        if (!empty($langArr)) {
            foreach ($langArr as $langItem) {
                if (\in_array($langItem, $this->zf_ignore_lang, true)) {
                    continue;
                }
                $pipelineArr[] = $this->zf_lang_to_pipeline[$langItem];
            }
        }

        return $pipelineArr ? $this->sortByPipelineAsString(implode(',', $pipelineArr)) : '';
    }

    /**
     * 语言转成对应的渠道
     * @param string $lang
     * @return string
     */
    private function langToJsonLang(string $lang)
    {
        $jsonArr = [];
        $langArr = explode(',', $lang);
        if (!empty($langArr)) {
            foreach ($langArr as $langItem) {
                if (\in_array($langItem, $this->zf_ignore_lang, true)) {
                    continue;
                }
                $pipeline = $this->zf_lang_to_pipeline[$langItem];
                $jsonArr[$pipeline] = [$langItem];
            }
        }

        return $jsonArr ? json_encode($this->sortByPipeline($jsonArr)) : '';
    }

    /**
     * 根据lang排序
     * @param string $langs
     * @return string
     */
    private function sortByLangAsString(string $langs)
    {
        if (empty($langs)) {
            return '';
        }

        $data = [];
        $sort = array_keys($this->zf_lang_to_pipeline);
        $list = explode(',', $langs);
        foreach ($sort as $item) {
            if (\in_array($item, $list, true)) {
                $data[] = $item;
            }
        }

        return $data ? implode(',', $data) : '';
    }

    /**
     * 根据pipeline排序
     * @param string $pipelines
     * @return string
     */
    private function sortByPipelineAsString(string $pipelines)
    {
        if (empty($pipelines)) {
            return '';
        }

        $data = [];
        $sort = array_values($this->zf_lang_to_pipeline);
        $list = explode(',', $pipelines);
        foreach ($sort as $item) {
            if (\in_array($item, $list, true)) {
                $data[] = $item;
            }
        }

        return $data ? implode(',', $data) : '';
    }

    /**
     * 根据pipeline排序
     * @param array $list
     * @return array
     */
    private function sortByPipeline(array $list)
    {
        $data = [];
        $sort = array_values($this->zf_lang_to_pipeline);
        foreach ($sort as $item) {
            if (isset($list[$item])) {
                $data[$item] = $list[$item];
            }
        }

        return $data;
    }
}
