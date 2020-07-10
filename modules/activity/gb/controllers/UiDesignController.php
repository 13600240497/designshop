<?php

namespace app\modules\activity\gb\controllers;

/**
 * 活动页面Ui设计管理类
 *
 * @property \app\modules\activity\gb\components\PageUiDesignComponent $PageUiDesignComponent
 * @property \app\modules\activity\gb\components\PageUiComponentDataComponent $PageUiComponentDataComponent
 * @author wangmeng
 *
 */
class UiDesignController extends DesignController
{
    /**
     * 新增UI组件
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionAddUi()
    {
        return $this->PageUiDesignComponent->addUiComponent(app()->request->post());
    }

    /**
     * 复制UI组件
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionCopyUi()
    {
        return $this->PageUiDesignComponent->copyUiComponent(app()->request->post());
    }

    /**
     * 移动UI组件
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionMoveUi()
    {
        return $this->PageUiDesignComponent->moveUiComponent(app()->request->post());
    }

    /**
     * 删除UI组件
     * @throws \ego\base\JsonResponseException
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function actionDeleteUi()
    {
        return $this->PageUiDesignComponent->deleteUIComponent(app()->request->post());
    }

    /**
     * 获取ui组件编辑的form表单--data 和 tpl
     * @param int $id 组件ID
     * @param string $lang 语言代码简称
     * @param int    $tpl_id 模板id
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionGetForm($id, $lang, $tpl_id = 0)
    {
        return $this->PageUiDesignComponent->getForm($id, $lang, (int)$tpl_id);
    }

    /**
     * 保存ui表单数据--data 和 tpl
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionSaveForm()
    {
        return $this->PageUiDesignComponent->saveForm(app()->request->post());
    }

    /**
     * 数据迁移（page_layout_data表中的data数据 和 page_ui_data表中的data、share_data数据 迁移到layout_ui_data数据表）
     * type 参数 layout -表示page_layout_data数据表，  ui -表示page_ui_data数据表
     * 每一个数据表只允许执行一次
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionMoveData()
    {
        return $this->PageUiComponentDataComponent->moveLayoutUiData(app()->request->get('type'));
    }
}
