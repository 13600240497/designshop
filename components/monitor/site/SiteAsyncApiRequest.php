<?php
namespace app\components\monitor\site;

use Yii;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use app\base\SiteConstants;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;


/**
 * 站点异步接口监控请求
 *
 * @author TianHaisen
 */
class SiteAsyncApiRequest
{
  /** @var string ua */
  const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36';

  /** @var int 最大重试次数 */
  private $maxRetries = 0;

  /** @var array 服装站点异步API信息列表 */
  private $asyncApiInfoList;

  /** @var array 请求失败API列表 */
  private $failResult = [];

  /** @var int 批次号 */
  private $batchId;

  /**
   * 构造函数
   *
   * @param int $maxRetries 最大重试次数,默认0不重试
   */
  public function __construct($maxRetries = 0)
  {
    $this->maxRetries = $maxRetries;
    $this->batchId = time();
  }

  /**
   * 获取批次号
   *
   * @return int
   */
  public function getBatchId()
  {
    return $this->batchId;
  }

  /**
   * 检查服装站点API接口是否健康
   *
   * @param int $concurrency 并发数量
   */
  public function checkSiteAsyncApi($concurrency = 5)
  {
    $this->loadAsyncApiConfig();
    $handlerStack = HandlerStack::create();
    if ($this->maxRetries > 0) {
      // 创建重试中间件，指定决策者为 $this->retryDecider(),指定重试延迟为 $this->retryDelay()
      $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
    }

    $client = new Client(['handler' => $handlerStack]);
    $requests = $this->getAsyncRequests($client);
    $pool = new Pool($client, $requests, [
      'concurrency' => $concurrency,
      'fulfilled' => function (Response $response, $index) {
//        $this->taskSuccess($response, $index);
      },
      'rejected' => function (ClientException $reason, $index) {
        $this->taskFail($reason, $index);
      },
    ]);
    $pool->promise()->wait();
  }

  /**
   * @return array
   */
  public function getFailResult()
  {
    return $this->failResult;
  }

  /**
   * 获取请求列表
   *
   * @param Client $client
   * @return \Generator
   */
  protected function getAsyncRequests(Client $client)
  {
    foreach ($this->asyncApiInfoList as $apiInfo) {
      yield function () use ($client, $apiInfo) {
        return $client->getAsync($apiInfo['url'], [
          RequestOptions::VERIFY => false,
          RequestOptions::TIMEOUT => 5,
          RequestOptions::HEADERS => [
            'User-Agent' => self::USER_AGENT,
          ]
        ]);
      };
    }
  }

  /**
   * 请求成功
   *
   * @param Response $response
   * @param int $index
   */
  protected function taskSuccess(Response $response, $index)
  {
    $resultInfo = $this->asyncApiInfoList[$index];
    $siteCode = $resultInfo['siteCode'];
    if (StringHelper::startsWith($siteCode, SiteConstants::SITE_GROUP_CODE_ZF)) {
      $content = $response->getBody()->getContents();
      if (StringHelper::startsWith($content, '{')) {
        $result = Json::decode($content, true);
        if (isset($result['code']) && (int)$result['code'] === 10001) {
          // 10001 请求参数不合法 - 方法不存在
          $resultInfo['httpCode'] = 404;
          $resultInfo['failMessage'] = '404 Not Found';
          $this->failResult[$siteCode][] = $resultInfo;
        }
      }
    }
  }

  /**
   * 请求失败
   *
   * @param ClientException $reason
   * @param int $index
   */
  protected function taskFail(ClientException $reason, $index)
  {
    $resultInfo = $this->asyncApiInfoList[$index];
    $siteCode = $resultInfo['siteCode'];

    $errorMessage = $reason->getMessage();
    if (!empty($errorMessage)) {
      $_parts = explode('response:', $reason->getMessage(), 2);
      if (count($_parts) == 2) {
        $errorMessage = $_parts[0];
      }
    }

    $httpCode = $reason->getCode();
    if (404 !== $httpCode) {
      $resultInfo['httpCode'] = $httpCode;
      $resultInfo['failMessage'] = $errorMessage;
      $this->failResult[$siteCode][] = $resultInfo;
    }
  }

  /**
   * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
   * @return \Closure
   */
  protected function retryDecider()
  {
    return function ($retries, Request $request, Response $response = null, RequestException $exception = null) {
      // 超过最大重试次数，不再重试
      if ($retries >= $this->maxRetries) {
        return false;
      }

      // 请求失败，继续重试
      if ($exception instanceof ConnectException) {
        return true;
      }

      if ($response) {
        // 如果请求有响应，但是状态码大于等于500，继续重试(这里根据自己的业务而定)
        if ($response->getStatusCode() >= 500) {
          return true;
        }
      }

      return false;
    };
  }

  /**
   * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
   * @return \Closure
   */
  protected function retryDelay()
  {
    return function ($numberOfRetries) {
      return 1000 * $numberOfRetries;
    };
  }

  /**
   * 加载站点的接口配置
   */
  private function loadAsyncApiConfig()
  {
    $_asyncApiInfoList = [];
    $allowSiteCode = ['rg-pc', 'rg-wap', 'zf-pc', 'zf-wap', 'dl-web'];
    $configFile = Yii::getAlias('@app/config/sites/interface/interface.' . YII_ENV . '.php');
    $allAsyncApiInfo = require($configFile);
    foreach ($allAsyncApiInfo as $siteCode => $apiInfoList) {
      if (in_array($siteCode, $allowSiteCode)) {
        foreach ($apiInfoList as $apiKey => $apiInfo) {
          if (isset($apiInfo['isJsonp']) && $apiInfo['isJsonp'] == 1) {
            $apiInfo['siteCode'] = $siteCode;
            $apiInfo['apiKey'] = $apiKey;
            $_asyncApiInfoList[] = $apiInfo;
          }
        }
      }
    }

    $this->asyncApiInfoList = $_asyncApiInfoList;
  }
}