<?php
namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 监控列表控制器
 * Class MonitoringController
 * @package app\modules\base\controllers
 */
class MonitoringController extends Controller
{
    /**
     * 首页
     * @return string
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }
}