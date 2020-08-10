<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\TracingFormatter;

/**
 * @internal
 */
class TracingFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new TracingFormatter;

        $records = [
            'first' => ['message' => uniqid('test', true), 'level' => 'info'],
        ];

        $this->assertSame($records['first']['message'] . "\n", $formatter->format($records['first']));
        $this->assertSame(['first' => $records['first']['message'] . "\n"], $formatter->formatBatch($records));
    }
}
