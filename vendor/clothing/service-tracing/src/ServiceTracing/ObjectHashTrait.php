<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 客户端请求追踪，针对 mysql/redis/elasticsearch/...
 *
 */
trait ObjectHashTrait
{
    /**
     * 已注册的对象
     *
     * @var object[]
     */
    private static $__objects = [];

    /**
     * 用于获取当前实例的 hash id
     *
     * @return string
     */
    public function getHashId()
    {
        return spl_object_hash($this);
    }

    /**
     * 将当前实例注册到 hash 列表中，便于静态获取
     * @return static
     */
    public function registerObjectHash()
    {
        self::$__objects[$this->getHashId()] = $this;

        return $this;
    }

    /**
     * 根据 hash id，获取对应的 object 实例
     *
     * @param string $hashId
     *
     * @return false|object
     */
    public static function withHashId($hashId)
    {
        return isset(self::$__objects[$hashId]) ? self::$__objects[$hashId] : false;
    }
}
