<?php
namespace app\components\fallback\api;

use Yii;
use app\base\SiteConstants;

class PageAsyncApiCrontab
{
  /** @var string 网站简码，如： rg/zf/dl */
  private $websiteCode;

  /**
   * 构造函数
   * @param string $websiteCode 网站简码，如: zf/rg
   */
  public function __construct($websiteCode)
  {
    $this->websiteCode = $websiteCode;
  }

  /**
   * 消费组件异步接口兜底队列
   * @throws AsyncApiFallbackException
   */
  public function consumeUiAsyncApiFallbackQueue()
  {

    $startTime = microtime(true);
    $message = sprintf('开始消费 %s 站点组件异步接口兜底队列', strtoupper($this->websiteCode));
    Yii::info($message, __CLASS__);

    while (true) {
      $taskInfo = PageAsyncApiManager::getUiAsyncApiFallbackTask($this->websiteCode);
      if (empty($taskInfo)) {
        break;
      }

      $this->fallbackTask($taskInfo);
    }

    $runTime = (microtime(true) - $startTime) * 1000;
    $message = sprintf(
      '完成消费 %s 站点组件异步接口兜底队列本次任务执行了 %d 毫秒',
      strtoupper($this->websiteCode), $runTime
    );
    Yii::info($message, __CLASS__);
  }

  /**
   * @param array $taskInfo
   * @throws AsyncApiFallbackException
   */
  protected function fallbackTask($taskInfo)
  {
    $pageModel = $this->getPageUiModel($this->websiteCode, $taskInfo['pageId']);
    if ($pageModel) {
      $pageAsyncApiFallback = new PageAsyncApiFallback($pageModel, $taskInfo['lang']);
      $pageAsyncApiFallback->fallback();
    }
  }

  /**
   * 获取页面对象
   *
   * @param string $websiteCode
   * @param int $pageId
   * @return \app\modules\common\zf\models\PageModel
   */
  private function getPageUiModel($websiteCode, $pageId)
  {
    if (SiteConstants::SITE_GROUP_CODE_ZF === $websiteCode) {
      $pageModel = \app\modules\common\zf\models\PageModel::findOne($pageId);
    } elseif (SiteConstants::SITE_GROUP_CODE_DL === $websiteCode) {
      $pageModel = \app\modules\common\dl\models\PageModel::findOne($pageId);
    } else {
      $pageModel = \app\modules\common\models\PageModel::findOne($pageId);
    }

    return $pageModel;
  }
}