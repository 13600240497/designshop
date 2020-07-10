<?php
namespace app\commands\modules\test\controllers;

use Yii;
use app\common\util\RedisKeyUtils;

class TestController extends Controller
{
    public function actionIndex()
    {
        echo RedisKeyUtils::getRgKeyPrefix();
    }
}