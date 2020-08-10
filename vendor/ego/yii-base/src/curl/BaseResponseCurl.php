<?php
namespace ego\curl;

use yii;
use yii\log\Logger;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use ego\enums\CommonError;

/**
 * 返回结果为`GuzzleHttp\Psr7\Response`的curl组件
 */
class BaseResponseCurl extends Component
{
    /**
     * @var string `GuzzleHttp\Client`默认base_uri配置
     */
    public $url = null;
    /**
     * @var bool|null|int 记录日志？
     */
    public $enableLog = true;
    /**
     * @var float 请求消耗时间大于指定值时，开启日志
     */
    public $enableLogTime = 1;
    /**
     * @var string 日志分类
     */
    public $logCategory = 'curl';
    /**
     * @var array guzzlehttp curl选项
     */
    public $guzzleOptions = [];
    /**
     * @var array 敏感字段，写日志时将使用*替代，比如登录时的密码
     */
    public $sensitiveFields = [];
    /**
     * @var string
     */
    public $logItemClass = __NAMESPACE__ . '\LogItem';
    /**
     * @var Client $client
     */
    protected $client = null;
    /**
     * @var Response phpunit单元测试模拟响应
     */
    protected $response = null;
    /**
     * @var LogItem
     */
    protected $logItem = null;
    /**
     * @var bool 不抛出异常？
     */
    protected $slient = false;

    /**
     * 请求
     *
     * @param string $method 服务名
     * @param string $uri 方法名
     * @param array $params 请求参数
     * @return Response|null
     * @throws GuzzleException|\Exception
     */
    public function request($method, $uri = '', array $params = [])
    {
        // phpunit测试，模拟响应
        if ($this->response && app()->env->isPhpunit()) {
            $response = $this->response;
            $this->response = null;
            return $this->parseResponse($response, $this->slient);
        }

        $slient = $this->slient;
        $this->slient = false;
        $this->logItem->startTime = app()->helper->microtime();
        $this->logItem->method = $method;
        $this->logItem->baseUrl = $this->url;
        $this->logItem->uri = $uri;
        $this->logItem->params = $params;
        try {
            $result = $this->parseResponse(
                $response = $this->requestInternal($method, $uri, $params),
                $slient
            );
            if ($this->getIsEnableLog($result, $slient)) {
                $this->logItem->response = $response->getBody() . '';
                $this->logItem->log(Logger::LEVEL_INFO);
            }
            return $result;
        } catch (GuzzleException $e) {
            $this->logItem->error = get_class($e) . ': ' . $e->getMessage();
            $this->logItem->log(Logger::LEVEL_ERROR);
            if ($this->shouldThrowException(CommonError::ERR_CURL_REQUEST_FAIL, $slient)) {
                throw $e;
            }
            return null;
        } finally {
            if (!isset($result) && !isset($e)) {
                $this->logItem->response = isset($response) ? $response->getBody() . '' : '';
                $this->logItem->log(Logger::LEVEL_INFO);
            }
        }
    }

    /**
     * 获取`GuzzleHttp\Client`对象
     *
     * @param bool $forceNew
     * @return Client
     */
    public function getClient($forceNew = false)
    {
        if ($forceNew) {
            return new Client(app()->helper->arr->merge(
                $this->guzzleOptions,
                ['base_uri' => $this->url]
            ));
        } elseif (!$this->client) {
            $this->client = new Client(app()->helper->arr->merge(
                $this->guzzleOptions,
                ['base_uri' => $this->url]
            ));
        }
        return $this->client;
    }

    /**
     * 设置调用是否抛出异常
     *
     * 只对当前请求有效
     *
     * @param array|int|bool $slient
     * 支持以下值：
     * - `true`: 抛出异常
     * - `false`: 不抛出异常
     * - `int`: 不抛出异常的错误号
     * - `array`: 不抛出异常的错误号组成的数组
     * @return $this
     */
    public function slient($slient = true)
    {
        $this->slient = $slient;
        return $this;
    }

    /**
     * 启用日志？
     *
     * @param bool|int|null $enable
     * @return $this
     */
    public function enableLog($enable)
    {
        if (null === $enable) {
            $this->enableLog = app()->env->isLocal() ? true : 1 == rand(1, 100);
        } elseif (is_numeric($enable)) {
            $this->enableLog = rand(1, $enable) == $enable;
        } else {
            $this->enableLog = $enable;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->enableLog($this->enableLog);
        $this->logItem = Yii::createObject([
            'class' => $this->logItemClass,
            'logCategory' => $this->logCategory,
            'sensitiveFields' => $this->sensitiveFields,
        ]);
        $this->logItem->class = get_class($this);
        $this->guzzleOptions = app()->helper->arr->merge(
            [
                'connect_timeout' => 10,
                'timeout' => 60,
            ],
            $this->guzzleOptions
        );
    }

    /**
     * 执行请求
     *
     * @param string $method 请求方法
     * @param string $uri 方法名
     * @param array $params 请求参数
     * @return Response
     */
    protected function requestInternal($method, $uri, array $params)
    {
        return $this->getClient()->request($method, $uri, $params);
    }

    /**
     * 是否应该抛出异常？
     *
     * @param int $code
     * @param bool|array $slient
     * @return bool
     */
    protected function shouldThrowException($code, $slient)
    {
        if (0 === $code) {
            return false;
        } elseif (is_bool($slient)) {
            return !$slient;
        } else {
            return !in_array($code, (array) $slient);
        }
    }

    /**
     * 解析请求响应
     *
     * @param Response|array $response
     * @param bool $slient
     * @return Response
     */
    protected function parseResponse($response, $slient)
    {
        unset($slient); // phpstorm unused ...
        return $response;
    }

    /**
     * 获取是否启用日志
     *
     * @param Response|Result $result
     * @param bool $slient
     * @return bool
     */
    protected function getIsEnableLog($result, $slient)
    {
        unset($result, $slient); // phpstorm unused ...
        return $this->enableLog
            || app()->helper->microtime() - $this->logItem->startTime > $this->enableLogTime;
    }
}
