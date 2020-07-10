<?php

namespace app\commands\component;


use yii\console\ErrorHandler;

class CustomErrorHandler extends ErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function renderException($exception)
    {
        /**
         * 监听异常信息
         */
        app()->rms->observeException($exception);
        parent::renderException($exception);
    }
}