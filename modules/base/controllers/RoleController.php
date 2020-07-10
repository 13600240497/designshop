<?php
namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 角色控制器
 * Class RoleController
 * @package app\modules\base\controllers
 */
class RoleController extends Controller
{
    /**
     * 默认角色列表主页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 角色列表
     * @return mixed
     */
    public function actionList()
    {
        return $this->RoleComponent->roleList();
    }

    /**
     * 角色可拥有的所有权限
     * @return mixed
     */
    public function actionAvailablePrivileges()
    {
        return $this->RoleComponent->availablePrivileges();
    }

    /**
     * 添加角色
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionAdd()
    {
        return $this->RoleComponent->roleAdd();
    }

    /**
     * 角色信息 -- 添加/编辑-拉取部门、菜单
     * @return mixed
     */
    public function actionInfo()
    {
        return $this->RoleComponent->roleInfo();
    }

    /**
     * 编辑角色
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEdit()
    {
        return $this->RoleComponent->roleEdit();
    }

    /**
     * 角色下用户列表
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionUserList()
    {
        return $this->RoleComponent->userList();
    }
}
