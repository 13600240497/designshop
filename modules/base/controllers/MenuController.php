<?php
namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 菜单控制器
 * Class MenuController
 * @package app\modules\base\controllers
 */
class MenuController extends Controller
{
    /**
     * 默认菜单列表主页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 菜单列表
     * @return array
     */
    public function actionList()
    {
        return $this->MenuComponent->lists();
    }

    /**
     * 添加菜单
     * @return array
     * @throws \Throwable
     */
    public function actionAdd()
    {
        return $this->MenuComponent->add(app()->request->post());
    }

    /**
     * 编辑菜单
     * @return array
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionEdit()
    {
        return $this->MenuComponent->edit(app()->request->post('id'), app()->request->post());
    }

    /**
     * 删除菜单
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete()
    {
        return $this->MenuComponent->delete(app()->request->post('id'));
    }

    /**
     * 获取下拉框数据
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \ReflectionException
     */
    public function actionPublicSelectOptions()
    {
        return app()->helper->arrayResult(
            0,
            'success',
            [
                'menus'     => $this->MenuComponent->getTopMenu(),
                'phpRoutes' => $this->MenuComponent->getPhpRoutes(),
            ]
        );
    }

    /**
     * 获取用户站点左侧菜单数据
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws
     */
    public function actionPublicMenus()
    {
        return $this->MenuComponent->getSiteMenus();
    }

}
