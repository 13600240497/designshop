<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 客户端请求代理
 *
 * 可用于在不对原代码过多修改的前提下，实现追踪效果
 *
 */
class BinaryClientProxy extends BinaryClient
{
    use ObjectHashTrait;

    /**
     * 被代理的客户端实例
     *
     * @var object
     */
    protected $client;

    /**
     * 需要监听的方法，当值为 null 值监听所有的方法
     *
     * @var null|array
     */
    protected $listenMethods;

    /**
     * 构造方法
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     * @param string                                $clientType
     * @param object                                $clientInstance
     * @param null|array                            $listenMethods
     */
    public function __construct(
        Tracer $tracer,
        $clientType,
        $clientInstance,
        array $listenMethods=null
    ) {
        $this->client        = $clientInstance;
        $this->listenMethods = is_array($listenMethods)
                                    ? array_map('strtolower', $listenMethods) : null;

        parent::__construct($tracer, $clientType);
    }

    /**
     * 获取客户端实例
     *
     * @return object
     */
    public function getClientInstance()
    {
        return $this->client;
    }

    /**
     * 判断是否被监听的方法
     *
     * @param string $method
     *
     * @return bool
     */
    public function isListenMethod($method)
    {
        if ($this->listenMethods === null) {
            return true;
        }

        return in_array(strtolower($method), $this->listenMethods, true);
    }

    /**
     * 调用客户端方法，通过魔术方法进行代理调用，并返回调用结果
     *
     * 并开始针对调用，发起客户端调用追踪
     *
     * @codeCoverageIgnore
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (! $this->isListenMethod($method)) {
            return $this->call($method, $arguments);
        }

        $request = $this->newRequest($method, $arguments)->start();

        try {
            $result = $this->call($method, $arguments);
        } catch (\Throwable $e) {
            $request->setException($e);

            throw $e;  // 重新抛出捕获到的异常
        } catch (\Exception $e) {
            $request->setException($e);

            throw $e;
        }

        $request->finish();

        return $result;
    }

    /**
     * 调用客户端方法，并返回调用结果
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function call($method, array $arguments)
    {
        return $this->client->{$method}(...$arguments);
    }
}
