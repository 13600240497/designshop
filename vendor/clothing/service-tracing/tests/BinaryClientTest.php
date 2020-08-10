<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\BinaryClientRequest;

/**
 * @internal
 */
class BinaryClientTest extends TestCase
{
    public function testAll()
    {
        $serviceId  = 'service.id';
        $methodName = 'service.method';

        $tracer = $this->getTracer(
            $this->getLocalService('ZF', 'zaful-app'),
            $this->getRecorder('phpunit_binary_client_test', $this->getLogHandler()),
            $serviceId,
            $methodName
        );

        $type   = 'mysql';
        $client = $tracer->newBinaryClient($type);

        $this->assertSame($type, $client->getClientType());
        $this->assertSame(0, count($client->getRequests()));
        $this->assertInstanceOf(BinaryClientRequest::class, $client->newRequest('foo', [1, 2]));
        $this->assertInstanceOf(BinaryClientRequest::class, $client->newRequest('bar', [3]));
        $this->assertSame(2, count($client->getRequests()));
    }
}
