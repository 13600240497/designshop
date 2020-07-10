<?php

namespace app\modules\activity\zf\controllers;

use app\modules\activity\zf\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * api模块基础控制器
 */
class Controller extends \app\base\Controller
{
    use MagicPropertyTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        app()->response->format = Response::FORMAT_JSON;
    }

    /**
     * 重写render，在render之前输出按照html输出
     * @param $view
     * @param array $params
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function render($view, $params = [])
    {
        app()->response->format = Response::FORMAT_HTML;

        return parent::render($view, $params);
    }
}
