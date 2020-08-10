<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\Generator;

/**
 * @internal
 */
class GeneratorTest extends TestCase
{
    const DEFAULT_LENGTH = 24;

    public function testGenTraceId()
    {
        $this->assertSame(mb_strlen(Generator::generateTraceId()), self::DEFAULT_LENGTH);
    }

    public function testGenSpanId()
    {
        $this->assertSame(mb_strlen(Generator::generateSpanId()), self::DEFAULT_LENGTH);
    }
}
