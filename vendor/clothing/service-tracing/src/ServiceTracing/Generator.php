<?php

namespace Clothing\Tools\ServiceTracing;

use Clothing\Tools\Utils\String\Random;

/**
 * 唯一 ID 生成器
 *
 */
class Generator
{
    /**
     * traceId 默认长度
     */
    const TRACE_ID_LENGTH = 24;

    /**
     * id 默认长度
     */
    const ID_LENGTH = 24;

    /**
     * 生成一个唯一的 traceId
     * @static
     * @return string
     */
    public static function generateTraceId()
    {
        return Random::uniqueID(self::TRACE_ID_LENGTH);
    }

    /**
     * 生成一个唯一的 spanId
     *
     * @static
     *
     * @return string
     */
    public static function generateSpanId()
    {
        return Random::uniqueID(self::ID_LENGTH);
    }
}
