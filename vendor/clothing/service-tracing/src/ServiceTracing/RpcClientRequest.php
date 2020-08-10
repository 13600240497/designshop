<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * RPC 客户端请求追踪
 *
 */
class RpcClientRequest
{
    use ActionTrait;
    use ObjectHashTrait;

    /**
     * 调用链追踪者
     *
     * @var \Clothing\Tools\ServiceTracing\Tracer
     */
    protected $tracer;

    /**
     * 当前调用链 spanId
     *
     * @var string
     */
    protected $spanId;

    /**
     * 当前的服务接口名称，如果作为服务调用方则为调用方自身的服务名称
     * 如果作为服务提供方，则为提供方的服务名称
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
     * 构造方法
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     * @param string                                $serviceId
     * @param string                                $methodName
     */
    public function __construct(Tracer $tracer, $serviceId, $methodName)
    {
        $this->tracer     = $tracer;
        $this->serviceId  = (string) $serviceId;
        $this->methodName = (string) $methodName;
        $this->spanId     = Generator::generateSpanId();

        $this->registerObjectHash();
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
        return $this->tracer->getSpanId();
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

    /**
     * 获取客户端应向服务端发送的 HTTP 头信息
     *
     * @return array
     */
    public function getHeaders()
    {
        return [
            'traceId: ' . $this->tracer->getTraceId(),
            'siteCode: ' . $this->tracer->getLocalService()->getSiteCode(),
            'spanId: ' . $this->getSpanId(),
            'parentId: ' . $this->getParentSpanId(),
        ];
    }
}
