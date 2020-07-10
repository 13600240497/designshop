<?php

namespace app\modules\base\controllers;

/**
 * 管理员操作日志控制器
 */
class HomeLogController extends Controller
{
    /**
     * 首页
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 列表
     * @param string $site_code
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionList(string $site_code)
    {
        return $this->HomeLogComponent->lists($site_code);
    }
}
