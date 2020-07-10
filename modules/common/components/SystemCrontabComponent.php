<?php
namespace app\modules\common\components;

use app\base\RequestUtils;

/**
 * 系统定时任务
 */
class SystemCrontabComponent extends Component
{
  /**
   * UI组件异步接口监控
   *
   * @return array
   */
  public static function uiAsyncApiMonitor()
  {
    ini_set('memory_limit', '1G');

    $lockKey = 'geshop:task:ui-aync-api-monitor:lock';
    if (null === app()->redis->set($lockKey, 1, 'EX', 3600, 'NX')) {
      return app()->helper->arrayResult(1, '消费UI组件异步接口监控任务后台运行中，无需再次操作');
    }

    try {
      app()->session->open();
      RequestUtils::closeConnectionAndFlush('消费UI组件异步接口监控任务切入后台运行，请去日志中查看结果');
      app()->response->isSent = true;

      set_time_limit(0);
      ignore_user_abort(true);

      $monitor = new \app\components\monitor\site\SiteAsyncApiMonitor();
      $monitor->runTask();
    } catch (\Throwable $throwable) {
      \Yii::error('消费UI组件异步接口监控任务错误： ' . $throwable->getMessage(), __METHOD__);
    } finally {
      app()->redis->del($lockKey); // 删除锁
    }
  }
}