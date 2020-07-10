<?php
namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 管理员控制器
 * Class AdminController
 *
 * @property \app\modules\base\components\AdminSitePrivilegeComponent $AdminSitePrivilegeComponent
 * @package app\modules\base\controllers
 */
class AdminController extends Controller
{
    /**
     * 默认管理员列表主页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 管理员列表
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionList()
    {
        return $this->AdminComponent->adminList();
    }

    /**
     * 编辑管理员
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEdit()
    {
        return $this->AdminComponent->adminEdit();
    }

    /**
     * 获取管理员信息
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws
     */
    public function actionInfo()
    {
        return $this->AdminComponent->getAdminInfo();
    }

    /**
     * 获取用户有权限的站点列表
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws
     */
    public function actionSiteList()
    {
        return $this->AdminComponent->siteList();
    }

    /**
     * 初始化平台用户
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionInitUsers()
    {
        return $this->AdminComponent->initUsers();
    }

    /**
     * 更新最近X天的有更新的用户
     * @param int $day
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionUpdateUsers(int $day = 10)
    {
        return $this->AdminComponent->updateUsers($day);
    }

    /**
     * 站点首页活动数据权限
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionSitePrivileges()
    {
        return $this->AdminSitePrivilegeComponent->actionSitePrivileges(app()->request->get());
    }

    /**
     * 保存用户站点首页活动数据权限
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionSaveUserSitePrivilege()
    {
        return $this->AdminSitePrivilegeComponent->actionSaveUserSitePrivilege(app()->request->post());
    }

}
