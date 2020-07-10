<?php

namespace app\modules\base\controllers;

/**
 * 嵌入页面控制器
 */
class FrameController extends Controller
{
    /**
     * 首页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
