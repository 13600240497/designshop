<?php
namespace app\modules\base\controllers;

use app\modules\base\models\SettingModel;

use yii\web\Response;

/**
 * 管理员操作日志控制器
 */
class AdminLogController extends Controller
{
    /**
     * 首页
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 列表
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        return $this->AdminLogComponent->lists();
    }

    /**
     * 获取详情
     * @param int $id
     * @return array
     */
    public function actionDetail($id)
    {
        return $this->AdminLogComponent->detail($id);
    }

    /**
     * 请求路由对应的操作名称
     * @return array
     */
    public function actionRequestRoute2name()
    {
        return app()->helper->arrayResult(
            0,
            'success',
            SettingModel::getValue('base', 'request_route2name')
        );
    }

    /**
     * 保存请求路由对应的操作名称
     * @return array
     */
    public function actionSaveRequestRoute2name()
    {
        if ($data = app()->request->post('request_route2name')) {
            SettingModel::saveSetting(
                'base',
                ['request_route2name' => $data]
            );
            return app()->helper->arrayResult(0, '保存成功');
        } else {
            return app()->helper->arrayResult(1, '保存数据不能为空');
        }
    }
}
