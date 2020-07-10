<?php
namespace app\modules\component\controllers;

use app\modules\component\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * component模块基础控制器
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
    }

}
