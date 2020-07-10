<?php

namespace app\modules\common\dl\components;

use app\modules\component\models\LayoutModel;

use app\modules\common\dl\models\{
    ActivityModel, PageHomeModel, PageLayoutDataModel, PageModel, PageLayoutModel, PageUiModel
};

use ego\base\JsonResponseException;

/**
 * 页面装修设计-Layout组件部分
 *
 */
class CommonPageLayoutDesignComponent extends CommonPageDesignComponent
{
    const FIELD_PAGE_ID = 'page_id';

    const LANG = 'lang';

    /**
     * 复制布局组件
     *
     * @param array $data [
     *                    'layout_id' => '当前布局组件ID',
     *                    'prev_id' => '前一个UI组件id',
     *                    'position' => '布局位置',
     *                    'copy_id' => '被复制的UI组件ID',
     *                    'lang' => '语言代码简称（当前展示所属的语言）'
     *                    ]
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function copyLayoutComponent($data)
    {
        $data = array_map('trim', $data);
        if (!isset($data[static::FIELD_PAGE_ID], $data['prev_id'], $data['copy_id'], $data['lang'], $data['num'])
        ) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }
        $num = (int)$data['num'];
        if ($num > 10 || $num <1) {
            throw new JsonResponseException($this->codeFail, '请输入1到10之间的数字');
        }

        $this->checkLang($data['lang']);
        $layoutInfo = PageLayoutModel::findOne((int)$data['copy_id']);
        if (!$layoutInfo || $layoutInfo['lang'] !== $data['lang']) {
            throw new JsonResponseException($this->codeFail, '布局组件不存在或已被删除');
        }
        if (!$preInfo = PageLayoutModel::findOne((int)$data['prev_id'])) {
            throw new JsonResponseException($this->codeFail, '布局组件不存在或已被删除');
        }
        $data['component_key'] = $layoutInfo['component_key'];
        $data['next_id'] = $preInfo['next_id'];
        $html = '';
        $htmlArr = [];
        $errors = [];
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            for ($i = 0; $i < $num; $i++) {
                //复制布局组件
                $layout = new CommonLayout();
                if (!$layout->copyLayout($data)) {
                    throw new JsonResponseException($this->codeFail, $layout->errors);
                }
                //复制UI组件
                $this->copyUiFromLayout($layout->instanceId, $data);
                $data['next_id'] = $layout->instanceId;

                //渲染组件
                $layout->needStatic = false;
                $layout->siteCode = $this->pageInfo->site_code;
                $layout->lang = $data['lang'];
                $layout->data = $this->getUiDataByLayoutId($layout->instanceId, $layoutInfo['lang']);
                $layout->activityId = $this->pageInfo->activity_id;
                $layout->pageId = $this->pageInfo->id;
                if (false === ($componentHtml = $layout->renderComponent($layout))) {//组件解析
                    $errors[] = $layout->errors;
                    $componentHtml = sprintf(
                        $this->layoutParseErrorTpl,
                        $layout->key,
                        $layout->instanceId,
                        'layout组件解析失败：' . $layout->errors
                    );
                }
                $htmlArr[$i] = $componentHtml;
            }
            $tr->commit();

        } catch (\Exception $e) {
            $tr->rollBack();
            throw new JsonResponseException($this->codeFail, $e->getMessage());
        }
        krsort($htmlArr);
        foreach ($htmlArr as $item) {
            $html .= $item;
        }
        return app()->helper->arrayResult(0, '复制成功', ['component_html' => $html], ['errors' => $errors]);
    }

    /**
     * 获取布局组件下有序的UI组件数组
     * @param int $id 布局组件ID
     * @param array $data 数据
     * @return bool
     * @throws JsonResponseException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function copyUiFromLayout($id, $data)
    {
        if (!$list = PageUiModel::find()->where(['layout_id' => $data['copy_id']])->asArray()->all()) {
            return true;
        }
        $uiList = [];
        array_map(function ($value) use (&$uiList) {
            $uiList[$value['position']][] = $value;
        }, $list);
        foreach ($uiList as &$uiInfo) {
            $uiInfo = $this->getOrderedComponents($uiInfo);
            krsort($uiInfo);
            $nextId = 0;
            foreach ($uiInfo as $ui) {
                $uiData = [
                    'page_id' => $data['page_id'],
                    'prev_id' => 0,
                    'copy_id' => $ui['id'],
                    'layout_id' => $id,
                    'position' => $ui['position'],
                    'lang' => $data['lang'],
                    'next_id' => $nextId
                ];
                $ui = new CommonUi();
                if (!$ui->copyUi($uiData)) {
                    throw new JsonResponseException($this->codeFail, $ui->errors);
                }
                $nextId = $ui->instanceId;
            }
        }
        return true;
    }


    /**
     * 添加页面组件
     * @param array $data [
     *      'page_id' => '当前页面ID',
     *      'prev_id' => '前一个组件id',
     *      'component_key' => '当前组件key'
     * ]
     * @param bool $isCustom 是否自定义组件
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function addLayoutComponent($data, $isCustom = false)
    {
        if (!$isCustom) {
            unset($data['width'], $data['columns']);
        }
        if (!isset($data[static::FIELD_PAGE_ID], $data[static::LANG], $data['prev_id'])) {
            return app()->helper->arrayResult(1, '参数不全');
        }
        if (!PageModel::findOne((int)$data[static::FIELD_PAGE_ID])) {
            return app()->helper->arrayResult(2, '页面不存在或已被删除');
        }
        if (self::SUCCESS_MSG !== ($checkMsg = $this->checkLayoutParams($data))) {
            return app()->helper->arrayResult(3, $checkMsg);
        }

        if (!(int)$data['prev_id'] && $prevModel = PageLayoutModel::findOne($data['prev_id'])) {
            // 当prev_id有值时，则page_id等信息从数据库查询
            $data[ static::FIELD_PAGE_ID ] = $prevModel->page_id;
        }

        $data['next_id'] = $this->getNextIdByPrevId($data['prev_id'], $data[static::FIELD_PAGE_ID], $data['lang']);
        $layout = new CommonLayout();//布局对象
        if ($isCustom && !empty($data['width']) && !empty($data['columns'])) {
            $columns = explode(',', $data['columns']);
            $layout->layoutData = [
                'width' => $data['width'],
                'columns' => array_map(function ($item) {
                    return [
                        'width' => $item
                    ];
                }, $columns),
                'count' => \count($columns)
            ];
        }

        if ($isCustom && !empty($data['wap_width']) && !empty($data['wap_columns'])) {
            $columns = explode(',', $data['wap_columns']);
            $layout->layoutData['wapWidth'] = $data['wap_width'];
            $layout->layoutData['wapColumns'] = array_map(function ($item) {
                return [
                    'width' => $item
                ];
            }, $columns);
            $layout->layoutData['wapCount'] = \count($columns);
        }

        if (!$layout->addComponent($data, false)) {//添加布局位置记录
            return app()->helper->arrayResult(4, $layout->errors);
        }

        $layout->needStatic = false;
        $layout->siteCode = $this->pageInfo->site_code;
        $layout->lang = $data['lang'];
        $layout->activityId = $this->pageInfo->activity_id;
        $layout->pageId = $this->pageInfo->id;
        if (false === ($componentHtml = $layout->renderComponent($layout))) {//组件解析
            $componentHtml = sprintf(
                $this->layoutParseErrorTpl,
                $layout->key,
                $layout->instanceId,
                'layout组件解析失败：' . $layout->errors
            );
        }
        return app()->helper->arrayResult(0, '添加成功', ['component_html' => $componentHtml], ['errors' => $layout->errors]);
    }

    /**
     * 移动页面layout组件
     * @param array $data [
     *      'id' => '当前布局组件ID',
     *      'page_id' => '页面ID',
     *      'prev_id' => '移动位置之后前一个布局组件位置ID,如果没有就为0',
     * ]
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function moveLayoutComponent($data)
    {
        if (!isset($data['id'], $data['prev_id'], $data['lang'])) {
            return app()->helper->arrayResult(1, '参数不全');
        }

        $data['oldprev_id'] = $this->getPrevId($data['id'], $data[static::FIELD_PAGE_ID]);
        $data['oldnext_id'] = $this->getNextIdByPrevId($data['id'], $data[static::FIELD_PAGE_ID], $data['lang']);
        $data['next_id'] = $this->getNextIdByPrevId($data['prev_id'], $data[static::FIELD_PAGE_ID], $data['lang']);
        $layout = new CommonLayout();
        if (!$layout->pageMoveComponent($data)) {
            //如果没有返回组件id
            return app()->helper->arrayResult(1, $layout->errors);
        }
        return app()->helper->arrayResult(0, '保存成功');
    }

    /**
     * 删除页面layout组件
     * @param array $data [
     *      'id' => '当前布局组件ID',
     *      'page_id' => '页面ID',
     * ]
     * @return array
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function deleteLayoutComponent($data)
    {
        if (!isset($data['id'], $data['lang'])) {
            return app()->helper->arrayResult(1, '参数不全');
        }

        $data['prev_id'] = $this->getPrevId($data['id'], $data[static::FIELD_PAGE_ID]);
        $data['next_id'] = $this->getNextIdByPrevId($data['id'], $data[static::FIELD_PAGE_ID], $data['lang']);
        $layout = new CommonLayout();
        $ui_ids = PageUiModel::find()->select('id, lang')
            ->where(['layout_id' => $data['id']])
            ->asArray()
            ->all();
        $ui_ids = !empty($ui_ids) ? array_column($ui_ids, 'lang', 'id') : [];
        foreach ($ui_ids as $ui_id => $ui_lang){
            $sync_data['del_info'][] = [
                'geshop_component_ui_id' => $ui_id
            ];
        }
        if (!$layout->pageRemoveComponent($data, PageLayoutDataModel::COMPONENT_TYPE)) {
            //如果没有返回组件id
            return app()->helper->arrayResult(1, $layout->errors);
        }

        //同步删除IPS子活动
        $sync_data['geshop_activity_id'] =$this->pageInfo->activity_id;
        \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);
        return app()->helper->arrayResult(0, '删除成功');
    }

    /**
     * 获取layout的form表单
     * @param int $layoutId Layout组件在page中的记录ID
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function getLayoutForm($layoutId)
    {
        /** @var \app\modules\common\models\PageLayoutModel $layoutModel */
        if (!$layoutModel = PageLayoutModel::findOne($layoutId)) {
            return app()->helper->arrayResult(1, '未找到对应的UI组件信息');
        }

        $layoutDataModel = PageLayoutDataModel::findOne(['component_id' => $layoutId]);

        $layout = new CommonLayout();//布局对象
        if ($layoutDataModel) {
            $layout->layoutData = json_decode($layoutDataModel->data, true);
            //设置自定义CSS和背景图等属性
            $layout->customData = [
                'background_color' => $layoutDataModel->background_color,
                'background_img' => $layoutDataModel->background_img
            ];
        }

        $layout->instanceId = $layoutModel->id;
        $layout->key = $layoutModel->component_key;
        $layout->tpl = self::FORM_NAME;

        $layout->needStatic = false;
        $layout->siteCode = $this->pageInfo->site_code;
        $layout->lang = $layoutModel->lang;
        $layout->activityId = $this->pageInfo->activity_id;
        $layout->pageId = $this->pageInfo->id;
        if (!$componentHtml = $layout->renderComponent($layout)) {//组件解析
            return app()->helper->arrayResult(1, $layout->errors);
        }
        return app()->helper->arrayResult(0, '获取成功', ['component_html' => $componentHtml], ['errors' => $layout->errors]);
    }

    /**
     * 保存Layout组件表单数据
     * @param array $data [
     *      'id' => '当前Layout组件ID',
     *      'data' => '用户配置的json格式数据',
     * ]
     * @param bool $runValidation
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function saveLayoutForm($data, $runValidation = true)
    {
        $data = array_map('trim', $data);
        $layoutModel = PageLayoutModel::findOne($data['id']);

        if (!$layoutModel) {
            return app()->helper->arrayResult(1, '更新数据参数不正确');
        }

        $layoutDataModel = PageLayoutDataModel::findOne(['component_id' => $data['id']]);
        if (!$layoutDataModel) {
            $layoutDataModel = new PageLayoutDataModel();
            $layoutDataModel->component_id = $data['id'];
            $layoutDataModel->lang = $layoutModel->lang;
            $layoutDataModel->data = json_encode([]);
        }

        $layoutDataModel->custom_css = !empty($data['custom_css']) ? $data['custom_css'] : '';
        $layoutDataModel->background_color = !empty($data['background_color']) ? $data['background_color'] : '';
        $layoutDataModel->background_img = !empty($data['background_img']) ? $data['background_img'] : '';


        //活动有变化则要验证活动是否有效
        $activityId = PageLayoutModel::getActivityIdById($data['id']);
        if ($activityId && true !== ($res = ActivityModel::isEnabled($activityId))) {
            return app()->helper->arrayResult(1, $res['message']);
        }

        if (false === $layoutDataModel->save($runValidation)) {
            return app()->helper->arrayResult($this->codeFail, '保存失败', $layoutDataModel->getErrors());
        }

        $layout = new CommonLayout();//布局对象
        if ($layoutDataModel) {
            $layout->layoutData = json_decode($layoutDataModel->data, true);
            //设置自定义CSS和背景图等属性
            $layout->customData = [
                'background_color' => $layoutDataModel->background_color,
                'background_img' => $layoutDataModel->background_img
            ];
        }

        $layout->instanceId = $data['id'];
        $layout->key = $layoutModel->component_key;
        $layout->data = $this->getUiDataByLayoutId($data['id'], $layoutModel->lang);//获取UiData

        $layout->needStatic = false;
        $layout->siteCode = $this->pageInfo->site_code;
        $layout->lang = $layoutModel->lang;
        $layout->activityId = $this->pageInfo->activity_id;
        $layout->pageId = $this->pageInfo->id;
        if (!$componentHtml = $layout->renderComponent($layout)) {//组件解析
            return app()->helper->arrayResult(1, $layout->errors);
        }

        return app()->helper->arrayResult($this->codeSuccess, '保存成功', ['component_html' => $componentHtml], ['errors' => $layout->errors]);
    }

    /**
     * Layout组件配置时，校验前端元素传递的值是否合理
     * @param array $data
     * @return string
     */
    private function checkLayoutParams($data)
    {
        if (isset($data['component_key']) && !LayoutModel::findOne(['component_key' => $data['component_key']])) {
            return '非法的参数component_key，查不到对应数据';
        }
        return self::SUCCESS_MSG;
    }

    /**
     * 根据当前节点ID查询上一节点ID
     * @param int $layoutId 当前节点ID
     * @param int $pageId 页面ID
     * @return int
     */
    private function getPrevId($layoutId, $pageId)
    {
        $prev = PageLayoutModel::findOne([
            'page_id' => $pageId,
            'next_id' => $layoutId
        ]);

        return $prev ? (int)$prev->id : 0;
    }

    /**
     * 根据上一节点ID查询下一节点ID
     * @param int $prevId 上一节点ID
     * @param int $pageId 页面ID
     * @param string $lang 语言
     * @return int
     */
    private function getNextIdByPrevId($prevId, $pageId, $lang)
    {
        $prev = PageLayoutModel::findOne($prevId);

        return $prev ? (int)$prev->next_id : $this->getFirstId($pageId, $lang);
    }

    /**
     * 获取页面首位layoutId
     * @param int $pageId 页面ID
     * @param string $lang 语言
     * @return int
     */
    private function getFirstId($pageId, $lang)
    {
        $list = PageLayoutModel::find()->where(['page_id' => $pageId, 'lang' => $lang])->asArray()->all();

        $list = $this->getOrderedComponents($list);

        if (!$list) {
            //list为空放在getOrderedComponents后面判断，这样可以过滤掉数据库中的脏数据的影响
            return 0;
        }

        return (int)$list[0]['id'];
    }
}
