<?php

namespace app\modules\component\controllers;

use yii\web\Response;

/**
 * component 组件模板关联关系控制器
 *
 * @property \app\modules\component\components\RelationComponent RelationComponent
 */
class RelationController extends Controller
{
    /**
     * 模板主页
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        echo $this->render('index');
    }

    /**
     * 组件模板关联关系列表
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionList()
    {
        return $this->RelationComponent->lists(app()->request->get());
    }

    /**
     * 新增组件模板关联关系
     *
     * @return array
     * @throws \Throwable
     */
    public function actionAdd()
    {
        return $this->RelationComponent->add(app()->request->post());
    }

    /**
     * 编辑组件模板关联关系
     *
     * @return array
     * @throws \Throwable
     */
    public function actionEdit()
    {
        return $this->RelationComponent->edit(app()->request->post());
    }

    /**
     * 禁用/启用组件模板关联关系
     *
     * @return array
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionChangeStatus()
    {
        return $this->RelationComponent->changeStatus(app()->request->post());
    }
}
