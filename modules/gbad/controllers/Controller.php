<?php

namespace app\modules\gbad\controllers;

use app\modules\gbad\traits\MagicPropertyTrait;
use yii\web\Response;
use ego\base\JsonResponseException;

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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!isGearbestSite()) {
            throw new JsonResponseException(0, '只有GB站点才能访问该页面!');
        }

        return true;
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
