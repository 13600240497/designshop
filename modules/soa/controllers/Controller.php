<?php
namespace app\modules\soa\controllers;

use app\modules\soa\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * SOA服务模块基础控制器
 *
 * @author TianHaisen
 * @since 1.5.0
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
}
