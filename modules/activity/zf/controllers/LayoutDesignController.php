<?php

namespace app\modules\activity\zf\controllers;

/**
 * 活动页面Layout设计管理类
 *
 * @property \app\modules\activity\zf\components\PageLayoutDesignComponent $PageLayoutDesignComponent
 * @author wangmeng
 *
 */
class LayoutDesignController extends DesignController
{
    /**
     * 新增布局组件
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionAddLayout()
    {
        return $this->PageLayoutDesignComponent->addLayoutComponent(app()->request->post());
    }

    /**
     * 新增自定义布局组件
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionAddCustomLayout()
    {
        return $this->PageLayoutDesignComponent->addLayoutComponent(app()->request->post(), true);
    }

    /**
     * 复制布局组件
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionCopyLayout()
    {
        return $this->PageLayoutDesignComponent->copyLayoutComponent(app()->request->post());
    }

    /**
     * 移动布局组件
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionMoveLayout()
    {
        return $this->PageLayoutDesignComponent->moveLayoutComponent(app()->request->post());
    }

    /**
     * 删除布局组件
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function actionDeleteLayout()
    {
        return $this->PageLayoutDesignComponent->deleteLayoutComponent(app()->request->post());
    }

    /**
     * 获取layout组件编辑的form表单
     * @param int $id 组件ID
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionGetLayoutForm($id)
    {
        return $this->PageLayoutDesignComponent->getLayoutForm($id);
    }

    /**
     * 保存layout表单数据
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionSaveLayoutForm()
    {
        return $this->PageLayoutDesignComponent->saveLayoutForm(app()->request->post());
    }
}
