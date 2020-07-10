<?php
namespace app\modules\base\dl\controllers;

use yii\web\Response;

/**
 * 任务日志控制器
 */
class TaskLogController extends Controller
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
     * 任务日志详情
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionDetail()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('detail');
    }
}
