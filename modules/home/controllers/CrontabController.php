<?php
namespace app\modules\home\controllers;

/**
 * 定时任务处理类
 * @property \app\modules\home\components\CrontabComponent CrontabComponent
 * @package app\modules\home\controllers
 */
class CrontabController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * 定时检查各站点头尾部内容变化
     */
    public function actionDiscoverHeadFooter()
    {
        return $this->CrontabComponent->discoverHeadFooterReplace();
    }
}
