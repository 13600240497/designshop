<?php
namespace app\modules\common\controllers;

use app\modules\common\components\SystemCrontabComponent;

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
   * UI组件异步接口监控(common/crontab/ui-async-api-monitor)
   *
   * @return array
   */
  public function actionUiAsyncApiMonitor()
  {
    return SystemCrontabComponent::uiAsyncApiMonitor();
  }
}