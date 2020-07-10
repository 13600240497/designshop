<?php
namespace app\modules\admin\controllers;

use app\modules\admin\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * api模块基础控制器
 */
class Controller extends \app\base\Controller
{
    use MagicPropertyTrait;

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        app()->response->format = Response::FORMAT_JSON;
    }
}
