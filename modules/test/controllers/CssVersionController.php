<?php

namespace app\modules\test\controllers;

use app\common\util\CssJsVersionManage;

/**
 * 配置文件控制器
 */
class CssVersionController extends \ego\web\Controller
{
    public function actionGet()
    {
        echo 'css_version = ' . app()->params['css_version'] . '<br>' ."\n";
        echo 'js_version = ' . app()->params['js_version'] . '<br>' . "\n";
    }
    public function actionReFlush()
    {
        echo 'CssJsVersionManage is reflush:' . CssJsVersionManage::reFlush();
    }
}
