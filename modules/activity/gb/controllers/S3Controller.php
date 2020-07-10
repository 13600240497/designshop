<?php

namespace app\modules\activity\gb\controllers;

/**
 * S3文件处理类
 * @property \app\modules\activity\components\S3Component S3Component
 * @package app\modules\activity\gb\controllers
 */
class S3Controller extends Controller
{
    public function actionSyncResource()
    {
        return $this->S3Component->syncResource();
    }
}
