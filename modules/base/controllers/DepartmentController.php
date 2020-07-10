<?php
namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 部门控制器
 * Class DepartmentController
 * @package app\modules\base\controllers
 */
class DepartmentController extends Controller
{
    /**
     * @file  : actionIndex
     * @brief : 默认部门列表主页
     * @return:  string
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * @file  : actionList
     * @brief : 部门列表
     * @return:  mixed
     */
    public function actionList()
    {
        return $this->DepartmentComponent->departmentList();
    }

    /**
     * @file  : actionPublicOutline
     * @brief : 获取部门下拉框数据
     * @return:  mixed
     */
    public function actionPublicOutline()
    {
        return $this->DepartmentComponent->departmentOutlineWidget();
    }

    /**
     * @file  : actionInfo
     * @brief : 部门信息 -- 编辑站点
     * @return:  mixed
     */
    public function actionInfo()
    {
        return $this->DepartmentComponent->departmentInfo();
    }

    /**
     * @file  : actionEdit
     * @brief : 部门编辑
     * @return:  mixed
     * @throws
     */
    public function actionEdit()
    {
        $departmentId = app()->request->post('id');
        $params = app()->request->post();

        return $this->DepartmentComponent->departmentEdit($departmentId, $params);
    }

}
