<?php
namespace app\components\monitor\site;

use Yii;
use app\modules\base\models\SysApiMonitorModel;

/**
 * 站点异步接口监控
 *
 * @author TianHaisen
 */
class SiteAsyncApiMonitor
{
  /** @var SiteAsyncApiRequest 异步接口请求对象 */
  private $apiRequest;

  /** @var int 错误次数报警间隔 */
  private $failCountAlarmInterval = 10;

  /**
   * 构造函数
   */
  public function __construct()
  {
    $this->apiRequest = new SiteAsyncApiRequest(0);
  }

  /**
   * 运行检查任务
   *
   * @param int $concurrency 并发数量
   */
  public function runTask($concurrency = 5)
  {
    $startTime = microtime(true);

    $this->apiRequest->checkSiteAsyncApi($concurrency);
    $failResult = $this->apiRequest->getFailResult();

    $runTime = (microtime(true) - $startTime) * 1000;
    if (!empty($failResult)) {
      $this->alarmReport($failResult);
    }
    $this->taskLog($runTime, $failResult);
  }

  /**
   * 错误告警处理
   *
   * @param array $failResult
   */
  private function alarmReport($failResult)
  {
    $failApiList = [];
    $apiModelList = SysApiMonitorModel::getAllFailApi();
    if ($apiModelList) {
      foreach ($apiModelList as $apiModel) {
        $_siteCode = $apiModel->site_code;
        $_apiKey = $apiModel->api_key;
        $failApiList[$_siteCode][$_apiKey] = $apiModel;
      }
    }

    $failMessageList = [];
    foreach ($failResult as $siteCode => $resultInfoList) {
      foreach ($resultInfoList as $resultInfo) {
        $apiKey = $resultInfo['apiKey'];

        if (isset($failApiList[$siteCode][$apiKey])) {
          /** @var SysApiMonitorModel $apiMonitorModel */
          $apiMonitorModel = $failApiList[$siteCode][$apiKey];
          $apiMonitorModel->fail_count++;
        } else {
          $apiMonitorModel = new SysApiMonitorModel();
          $apiMonitorModel->site_code = $siteCode;
          $apiMonitorModel->api_key = $apiKey;
          $apiMonitorModel->fail_count = 1;
          $apiMonitorModel->process_status = SysApiMonitorModel::PROCESS_STATUS_INITIAL;
          $apiMonitorModel->create_time = time();
        }
        $apiMonitorModel->fail_url = $resultInfo['url'];
        $apiMonitorModel->fail_message = $resultInfo['failMessage'];
        $apiMonitorModel->update_time = time();
        $apiMonitorModel->save(false);

        // 触发报警条件,防止频繁收到告警信息
        $_failCount = $apiMonitorModel->fail_count - 1;
        if ($_failCount % $this->failCountAlarmInterval === 0) {
          $failMessageList[$siteCode][] = sprintf(
            '请求 %s 接口[%s]失败,错误次数 %d, 最后请求http状态码[%s], 错误: %s',
            $resultInfo['description'], $resultInfo['url'], $apiMonitorModel->fail_count, $resultInfo['httpCode'], $resultInfo['failMessage']
          );
        }
      }
    }

    if (!empty($failMessageList)) {
      $failMessage = '';
      foreach ($failMessageList as $siteCode => $messageList) {
        $_apiFailMsg = PHP_EOL . join(PHP_EOL, $messageList) . PHP_EOL;
        $failMessage .= sprintf('站点 %s 以下异步接口请求异步: %s', $siteCode, $_apiFailMsg);
      }
      app()->rms->reportPlatformApiError($failMessage);
    }
  }

  /**
   * 获取所有请求失败错误信息
   *
   * @param int $runTime
   * @param array $failResult
   */
  private function taskLog($runTime, $failResult)
  {
    $failMessage = '';
    foreach ($failResult as $siteCode => $resultInfoList) {
      $_apiFailMsgList = [];
      foreach ($resultInfoList as $resultInfo) {
        $_apiFailMsgList[] = sprintf(
          '请求 %s 接口[%s]失败, http状态码[%s], 错误: %s',
          $resultInfo['description'], $resultInfo['url'], $resultInfo['httpCode'], $resultInfo['failMessage']
        );
      }
      $_apiFailMsg = PHP_EOL . join(PHP_EOL, $_apiFailMsgList) . PHP_EOL;
      $failMessage .= sprintf('站点 %s 以下异步接口请求异步: %s', $siteCode, $_apiFailMsg);
    }

    $batchId = $this->apiRequest->getBatchId();
    $failMessage = empty($failMessage) ? '站点所有接口没有发现异常.' : PHP_EOL . $failMessage;
    $logMessage = sprintf(
      '任务运行结束,批次号 %s,执行了 %d 毫秒,结果如下: %s',
      $batchId, $runTime, $failMessage
    );
    Yii::info($logMessage, __CLASS__);
  }
}