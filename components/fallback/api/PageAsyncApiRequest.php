<?php
namespace app\components\fallback\api;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;

/**
 * 并发请求站点异步接口
 *
 * @package app\components\fallback\api
 */
class PageAsyncApiRequest
{
  /** @var string ua */
  const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36';

  /**
   * @var array 需求请求的异步API接口信息列表
   * - uiId 组件ID
   * - url 异步API接口网址url
   */
  private $apiInfoList;

  /** @var array 请求结果 */
  private $apiResult = [];

  /**
   * 构造函数
   *
   * @param array $apiInfoList
   */
  public function __construct(array $apiInfoList)
  {
    $this->apiInfoList = $apiInfoList;
  }

  /**
   * 并发请求
   *
   * @param int $concurrency 并发数量
   */
  public function concurrencyRequest($concurrency = 5)
  {
    $client = new Client();
    $requests = $this->getAsyncRequests($client);
    $pool = new Pool($client, $requests, [
      'concurrency' => $concurrency,
      'fulfilled' => function (Response $response, $index) {
        $this->taskSuccess($response, $index);
      },
      'rejected' => function (ClientException $reason, $index) {
        $this->taskFail($reason, $index);
      },
    ]);
    $pool->promise()->wait();
  }

  /**
   * 获取请求结果
   *
   * @return array
   */
  public function getRequestResult()
  {
      return $this->apiResult;
  }

  /**
   * 获取请求列表
   *
   * @param Client $client
   * @return \Generator
   */
  protected function getAsyncRequests(Client $client)
  {
    foreach ($this->apiInfoList as $urlInfo) {
      yield function () use ($client, $urlInfo) {
        return $client->getAsync($urlInfo['url'], [
          RequestOptions::VERIFY => false,
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
    $apiInfo = $this->apiInfoList[$index];
    $this->apiResult[$apiInfo['uiId']] = [
      'status' => true,
      'url' => $apiInfo['url'],
      'content' => $response->getBody()->getContents(),
    ];
  }

  /**
   * 请求失败
   *
   * @param ClientException $reason
   * @param int $index
   */
  protected function taskFail(ClientException $reason, $index)
  {
    $apiInfo = $this->apiInfoList[$index];
    $this->apiResult[$apiInfo['uiId']] = [
      'status' => false,
      'url' => $apiInfo['url'],
      'message' => $reason->getMessage(),
    ];
  }
}