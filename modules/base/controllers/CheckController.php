<?php

namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * 数据检测控制器
 * @property \app\modules\base\components\CheckComponent $CheckComponent
 */
class CheckController extends Controller
{
    /**
     * index
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->CheckComponent->index();
    }
}
