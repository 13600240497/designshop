<?php
namespace app\modules\activity\zf\controllers;

use app\base\SiteConstants;
use app\components\fallback\api\PageAsyncApiManager;

/**
 * 组件异步接口兜底
 */
class UiFallbackController extends Controller
{

  /**
   * @return array
   */
  public function actionTaskQueueInfo()
  {
    $queueInfo = PageAsyncApiManager::getTaskQueueInfo(SiteConstants::SITE_GROUP_CODE_ZF);
    return app()->helper->arrayResult(0, '查询成功', $queueInfo);
  }

  /**
   * @return array
   */
  public function actionCleanTaskQueue()
  {
    $queueInfo = PageAsyncApiManager::cleanTaskQueue(SiteConstants::SITE_GROUP_CODE_ZF);
    return app()->helper->arrayResult(0, '查询成功', $queueInfo);
  }

}