<?php

namespace app\components;

use Clothing\Tools\ServiceTracing\Tracer;
use yii\web\ErrorHandler;

/**
 * 自定义错误异常组件
 */
class CustomErrorHandler extends ErrorHandler
{
    protected static $tracerClient;

    public function getTracerClient()
    {
        return self::$tracerClient ?: Tracer::singleton()->newBinaryClient('server');
    }
    /**
     * @inheritdoc
     */
    protected function renderException($exception)
    {
        /**
         * 监听异常信息
         */
        if ( PHP_SAPI != 'cli'){
            $request = $this->getTracerClient()->newRequest('exception', ['异常'])->start();
            $request->setException($exception);
            $request->finish();
        }

        app()->rms->observeException($exception);
        if (404 === intval($exception->statusCode)) {
            echo  $this->renderFile(APP_PATH . '/views/error/404.php', []);
        } else {
            parent::renderException($exception);
        }
    }
}
