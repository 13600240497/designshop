<?php

namespace app\components;

use Clothing\Tools\ServiceTracing\Tracer;
use yii\web\ErrorHandler;

/**
 * 自定义错误异常组件
 */
class CustomErrorHandler extends ErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function renderException($exception)
    {
        if (404 === intval($exception->statusCode)) {
            echo  $this->renderFile(APP_PATH . '/views/error/404.php', []);
        } else {
            parent::renderException($exception);
        }
    }
}
