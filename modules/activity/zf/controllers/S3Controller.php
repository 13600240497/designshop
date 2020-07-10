<?php
/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/24
 * Time: 16:29
 */

namespace app\modules\activity\zf\controllers;

/**
 * S3文件处理类
 * @property \app\modules\activity\zf\components\S3Component S3Component
 * @package app\modules\activity\controllers
 */
class S3Controller extends Controller
{
    public function actionSyncResource()
    {
        return $this->S3Component->syncResource();
    }
}
