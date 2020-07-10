<?php
namespace app\modules\test\controllers;

use app\modules\test\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * test模块基础控制器
 */
class Controller extends \app\base\Controller
{
    use MagicPropertyTrait;

    public function init()
    {
        parent::init();
        app()->response->format = Response::FORMAT_JSON;
    }
}
