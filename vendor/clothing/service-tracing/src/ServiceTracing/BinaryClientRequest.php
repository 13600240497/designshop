<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 客户端请求处理
 *
 */
class BinaryClientRequest
{
    use ActionTrait;
    use ObjectHashTrait;

    /**
     * 请求的方法
     * @var string
     */
    protected $method;

    /**
     * 请求的参数
     * @var array
     */
    protected $params;

    /**
     * 构造方法
     * @param string $method
     * @param array  $params
     */
    public function __construct($method, array $params=[])
    {
        $this->method = (string) $method;
        $this->params = $params;

        $this->registerObjectHash();
    }

    /**
     * 获取请求的方法
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 获取请求的参数
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
