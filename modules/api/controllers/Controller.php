<?php

namespace app\modules\api\controllers;

use app\modules\home\traits\MagicPropertyTrait;
use ego\base\JsonResponseException;
use yii\web\Response;
use app\components\MyBehavior;


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
        app()->response->format = Response::FORMAT_JSON;
        // 网关校验
        if (false === $this->getBehavior('MyBehavior')->gatewayVerify()) {
            throw new JsonResponseException(403, 'Requests API Gateway Check Error.');
        }
        $this->enableCsrfValidation = false;
    }

    public function behaviors()
    {
        return [
            'MyBehavior' => [
                'class' => MyBehavior::className(),
            ],
        ];
    }
}
