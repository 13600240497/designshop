<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\BinaryClientProxy;
use Redis;

/**
 * @internal
 */
class BinaryClientProxyTest extends TestCase
{
    public function testAll()
    {
        $serviceId  = 'service.id';
        $methodName = 'service.method';

        $tracer = $this->getTracer(
            $this->getLocalService('ZF', 'zaful-app'),
            $this->getRecorder('phpunit_binary_client_proxy_test', $this->getLogHandler()),
            $serviceId,
            $methodName
        );

        $this->assertSame(0, count($tracer->getBinaryClients()));

        $redis   = new Redis;
        $type    = 'redis';
        $listens = ['connect', 'get', 'set'];
        $proxy   = new BinaryClientProxy($tracer, $type, $redis, $listens);

        $this->assertSame($redis, $proxy->getClientInstance());
        $this->assertSame($proxy, $tracer->newBinaryClientFromProxy($proxy));
        $this->assertSame(1, count($tracer->getBinaryClients()));
        $this->assertTrue($proxy->isListenMethod('connect'));
        $this->assertFalse($proxy->isListenMethod('foobar'));
        $this->assertTrue($proxy->connect($this->redisHost, $this->redisPort));
        $this->assertTrue($proxy->set(__CLASS__, __FILE__));
        $this->assertSame($proxy->get(__CLASS__), $proxy->call('get', [__CLASS__]));
        $this->assertSame($proxy->get(__CLASS__), $redis->get(__CLASS__));

        $proxy = new BinaryClientProxy($tracer, $type, $redis);
        $this->assertTrue($proxy->isListenMethod('connect'));
        $this->assertTrue($proxy->isListenMethod('foobar'));
        $this->assertSame(1, $proxy->del(__CLASS__));
    }
}
