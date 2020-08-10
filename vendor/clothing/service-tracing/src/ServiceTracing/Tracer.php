<?php

namespace Clothing\Tools\ServiceTracing;

use Clothing\Tools\Utils\Traits\SingletonTrait;

/**
 * 调用链追踪主类
 *
 * 实例化前，需要创建一个 Logger 实例，以用作数据存储用途
 *
 */
class Tracer
{
    use ActionTrait;
    use SingletonTrait;

    /**
     * 客户端发起请求
     */
    const CLIENT_START = 'cs';

    /**
     * 服务端收到请求
     */
    const SERVER_RECV = 'sr';

    /**
     * 服务端完成处理，并将结果发送给客户端
     */
    const SERVER_SEND = 'ss';

    /**
     * 客户端收到服务端返回的信息
     */
    const CLIENT_RECV = 'cr';

    /**
     * 本地服务信息
     *
     * @var \Clothing\Tools\ServiceTracing\LocalService
     */
    protected $localService;

    /**
     * 日志记录器
     *
     * @var \Clothing\Tools\ServiceTracing\Recorder
     */
    protected $recorder;

    /**
     * 全局跟踪 traceId
     *
     * @var string
     */
    protected $traceId;

    /**
     * 当前调用链 spanId
     *
     * @var string
     */
    protected $spanId;

    /**
     * 上级链路 span id
     *
     * @var string
     */
    protected $parentSpanId;

    /**
     * 当前的服务接口名称，如果作为服务调用方则为调用方自身的服务名称
     * 如果作为服务提供方，则为提供方的服务名称
     *
     * @var string
     */
    protected $serviceId = '';

    /**
     * 当前的服务方法名称，如果作为服务调用方则为调用方自身的名称
     * 如果作为服务提供方，则为提供方的名称
     *
     * @var string
     */
    protected $methodName = '';

    /**
     * 客户端请求，针对各种客户请求，例如 mysql/redis/elasticsearch/...
     *
     * @var \Clothing\Tools\ServiceTracing\BinaryClient[]
     */
    protected $binaryClients = [];

    /**
     * RPC 请求，针对各种 rpc 或者 http api 调用
     *
     * @var \Clothing\Tools\ServiceTracing\RpcClientRequest[]
     */
    protected $rpcRequests = [];

    /**
     * 标识当前的信息是否已写入到日志服务
     *
     * @var bool
     */
    protected $flushed = false;

    /**
     * 构造方法，需要传入默认的配置参数，以及日志记录器
     *
     * @param \Clothing\Tools\ServiceTracing\LocalService $localService
     * @param \Clothing\Tools\ServiceTracing\Recorder     $recorder
     * @param string                                      $serviceId
     * @param string                                      $methodName
     */
    public function __construct(
        LocalService $localService,
        Recorder $recorder,
        $serviceId,
        $methodName
    ) {
        $this->localService = $localService;
        $this->recorder     = $recorder;
        $this->serviceId    = (string) $serviceId;
        $this->methodName   = (string) $methodName;

        $this->traceId      = $localService->getGlobalTraceId() ?: Generator::generateTraceId();
        $this->spanId       = $localService->getSpanId() ?: Generator::generateSpanId();
        $this->parentSpanId = $localService->getParentSpanId() ?: '0';

        // @codeCoverageIgnoreStart
        register_shutdown_function(function () {
            $this->flush();
        });
        // @codeCoverageIgnoreEnd

        $this->registerSingleton();
    }

    /**
     * 获取本地服务信息类
     *
     * @return \Clothing\Tools\ServiceTracing\LocalService
     */
    public function getLocalService()
    {
        return $this->localService;
    }

    /**
     * 获取追踪 id
     *
     * @return string
     */
    public function getTraceId()
    {
        return $this->traceId;
    }

    /**
     * 获取 span id
     *
     * @return string
     */
    public function getSpanId()
    {
        return $this->spanId;
    }

    /**
     * 获取上级链路 span id
     *
     * @return string
     */
    public function getParentSpanId()
    {
        return $this->parentSpanId;
    }

    /**
     * 获取 serviceId
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * 获取 methodName
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

	public function setServiceId($serviceId)
    {
        $this->serviceId = (string)$serviceId;
    }

    public function setMethodName($methodName)
    {
        $this->methodName = (string)$methodName;
    }
	
    /**
     * 创建一个追踪客户端，针对各种客户请求，例如 mysql/redis/elasticsearch/...
     *
     * @param string $clientType
     * @param null | int $requestCountLimit
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClient
     */
    public function newBinaryClient($clientType,$requestCountLimit = null)
    {
        $client = new BinaryClient($this, $clientType,$requestCountLimit);

        $this->binaryClients[$client->getHashId()] = $client;

        return $client;
    }

    /**
     * 将一个已创建完成的客户端代理，加入到追踪链中
     *
     * @param \Clothing\Tools\ServiceTracing\BinaryClientProxy $proxy
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClientProxy
     */
    public function newBinaryClientFromProxy(BinaryClientProxy $proxy)
    {
        $this->binaryClients[$proxy->getHashId()] = $proxy;

        return $proxy;
    }

    /**
     * 创建一个客户端代理，方便在不对原代码做过多改动的情况下实现追踪
     *
     * @param string     $clientType
     * @param object     $clientInstance
     * @param null|array $listenMethods
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClientProxy
     */
    public function newBinaryClientWithProxy(
        $clientType,
        $clientInstance,
        array $listenMethods=null
    ) {
        return $this->newBinaryClientFromProxy(
            new BinaryClientProxy($this, $clientType, $clientInstance, $listenMethods)
        );
    }

    /**
     * 根据 hash id 来获取一个已创建的 rpc 请求追踪客户端
     *
     * @param string $hashId
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClient|false
     */
    public function getBinaryClient($hashId)
    {
        return isset($this->binaryClients[$hashId])
            ? $this->binaryClients[$hashId] : false;
    }

    /**
     * 获取所有的追踪客户
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClient[]
     */
    public function getBinaryClients()
    {
        return $this->binaryClients;
    }

    /**
     * 创建一个 RPC 请求追踪客户端，针对各种 rpc 或 http api 调用
     *
     * @param string $serviceId
     * @param string $methodName
     *
     * @return \Clothing\Tools\ServiceTracing\RpcClientRequest
     */
    public function newRpcClientRequest($serviceId, $methodName)
    {
        $request = new RpcClientRequest($this, $serviceId, $methodName);

        $this->rpcRequests[$request->getHashId()] = $request;

        return $request;
    }

    /**
     * 根据 hash id 来获取一个已创建的 rpc 请求追踪客户端
     *
     * @param string $hashId
     *
     * @return \Clothing\Tools\ServiceTracing\RpcClientRequest|false
     */
    public function getRpcClientRequest($hashId)
    {
        return isset($this->rpcRequests[$hashId])
            ? $this->rpcRequests[$hashId] : false;
    }

    /**
     * 获取所有的 RPC 请求追踪客户端
     *
     * @return \Clothing\Tools\ServiceTracing\RpcClientRequest[]
     */
    public function getRpcClientRequests()
    {
        return $this->rpcRequests;
    }

    /**
     * 当前追踪数据是否已经刷新到日志中
     *
     * @return bool
     */
    public function isFlushed()
    {
        return $this->flushed;
    }

    /**
     * 将当前调用链信息写入到日志中（只可刷新一次，无法重复写入）
     * 当 Tracer 类被析构时，将自动调用 flush 方法
     *
     * @return static
     */
    public function flush()
    {
        if ($this->isStarted()) {
            if (! $this->isFinished()) {
                $this->finish();
            }

            if (! $this->flushed) {
                $this->recorder->flush($this);
                $this->flushed = true;
            }
        }

        return $this;
    }

    /**
     * 析构方法，当 Tracer 类结束时，自动调用 flush 方法
     *
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->flush();
    }
}
