<?php
/**
 * 推广落地页定时任务	
 */

namespace app\modules\advertisement\controllers;

/**
 * 定时任务处理类
 * @property \app\modules\advertisement\components\CrontabComponent CrontabComponent
 * @package app\modules\advertisement\controllers
 */
class CrontabController extends Controller
{
    
     /**
     * 推送活动落地页到网站
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionPushData()
    {
        return $this->CrontabComponent->pushData();
    }
}
