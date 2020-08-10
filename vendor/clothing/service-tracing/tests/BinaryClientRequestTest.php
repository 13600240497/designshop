<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\BinaryClientRequest;

/**
 * @internal
 */
class BinaryClientRequestTest extends TestCase
{
    public function testInit()
    {
        $method  = 'foo';
        $params  = ['bar', 'baz'];
        $request = new BinaryClientRequest($method, $params);
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($params, $request->getParams());
    }
}
