<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 客户端请求追踪，针对 mysql/redis/elasticsearch/...
 */
class BinaryClient
{
    use ObjectHashTrait;

    /**
     * 主追踪器
     * @var \Clothing\Tools\ServiceTracing\Tracer
     */
    protected $tracer;

    /**
     * 客户端类型，例如 mysql, redis, ...
     *
     * @var string
     */
    protected $type;

    /**
     * 客户端请求
     * @var \Clothing\Tools\ServiceTracing\BinaryClientRequest[]
     */
    protected $requests = [];

    /**
     * @var null | int 限制request数量
     */
    protected $requestCountLimit = null;

    /**
     * 构造方法
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     * @param string                                $clientType
     */
    public function __construct(Tracer $tracer, $clientType,$requestCountLimit = null)
    {
        $this->tracer = $tracer;
        $this->type   = (string) $clientType;
        $this->requestCountLimit = $requestCountLimit;

        $this->registerObjectHash();
    }

    /**
     * 获取客户端类型，例如 mysql, redis, ...
     * @return string
     */
    public function getClientType()
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getRequestCountLimit()
    {
        return $this->requestCountLimit;
    }

    /**
     * 创建一个新的客户端请求
     * @param string $method
     * @param array  $params
     *
     * @return \Clothing\Tools\ServiceTracing\BinaryClientRequest
     */
    public function newRequest($method, array $params=[])
    {
        $request = new BinaryClientRequest($method, $params);

        $this->requests[$request->getHashId()] = $request;

        return $request;
    }

    /**
     * 获取所有的客户端请求
     * @return \Clothing\Tools\ServiceTracing\BinaryClientRequest[]
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
