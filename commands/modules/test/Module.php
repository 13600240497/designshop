<?php

namespace app\commands\modules\test;

use Yii;

/**
 * 测试模块
 * @package app\commands\modules\test
 */
class Module extends \app\base\Module
{
    public $controllerNamespace = 'app\commands\modules\test\controllers';

    public function init()
    {
        parent::init();
    }
}
