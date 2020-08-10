<?php

namespace Clothing\Tools\Tests;

use Redis;

/**
 * @internal
 */
class TracerTest extends TestCase
{
    protected $siteCode   = 'ZF';
    protected $appName    = 'zaful-pc';
    protected $serviceId  = 'App.Controllers.MyController';
    protected $methodName = 'get';
    protected $servers    = [
        'HTTP_TRACEID'       => '9b559735d3db127adc629d3e',
        'HTTP_SPANID'        => '659c51f60072b86b65596d25',
        'HTTP_PARENTID'      => '16995a3c528c0eda19e72f6b',
    ];

    protected $localService;
    protected $recorder;
    protected $logHandler;
    protected $tracer;

    protected function setUp()
    {
        $this->logHandler   = $this->getLogHandler();
        $this->recorder     = $this->getRecorder('phpunit_tracer_test', $this->logHandler);
        $this->localService = $this->getLocalService($this->siteCode, $this->appName, $this->servers);
        $this->tracer       = $this->getTracer($this->localService, $this->recorder, $this->serviceId, $this->methodName);
    }

    public function testExtendHeaders()
    {
        $this->assertSame($this->servers['HTTP_TRACEID'], $this->tracer->getTraceId());
        $this->assertSame($this->servers['HTTP_SPANID'], $this->tracer->getSpanId());
        $this->assertSame($this->servers['HTTP_PARENTID'], $this->tracer->getParentSpanId());
    }

    public function testBaseInfo()
    {
        $this->assertSame($this->localService, $this->tracer->getLocalService());
        $this->assertSame($this->serviceId, $this->tracer->getServiceId());
        $this->assertSame($this->methodName, $this->tracer->getMethodName());
        $this->assertEmpty($this->tracer->getBinaryClients());
        $this->assertEmpty($this->tracer->getRpcClientRequests());
    }

    /**
     * @depends testBaseInfo
     * @depends testExtendHeaders
     */
    public function testRequests()
    {
        $tracer = $this->tracer->start();

        // ------------------------------------
        // mysql client request
        // ------------------------------------

        $mysql   = $tracer->newBinaryClient('mysql');

        $request = $mysql->newRequest('connect', ['127.0.0.1', 3306])->start();
        usleep(mt_rand(10000, 99999));
        $request->finish();

        $request = $mysql->newRequest('execute', ['set names utf8'])->start();
        usleep(mt_rand(10000, 99999));
        $request->setException(new \RuntimeException('test mysql exception'));

        $request = $mysql->newRequest('execute', ['select * from users'])->start();
        usleep(mt_rand(10000, 99999));
        $request->finish();

        $this->assertSame(1, count($tracer->getBinaryClients()));
        $this->assertSame($mysql, $tracer->getBinaryClient($mysql->getHashId()));

        // ------------------------------------
        // redis client request with proxy
        // ------------------------------------

        $redis   = new Redis;
        $type    = 'redis';
        $listens = ['connect', 'get', 'set'];
        $proxy   = $tracer->newBinaryClientWithProxy($type, $redis, $listens);

        $this->assertTrue($proxy->connect($this->redisHost, $this->redisPort));
        $this->assertTrue($proxy->set(__CLASS__, __FILE__));
        $this->assertSame($proxy->get(__CLASS__), $proxy->call('get', [__CLASS__]));
        $this->assertSame($proxy->get(__CLASS__), $redis->get(__CLASS__));

        $this->assertSame($proxy, $tracer->newBinaryClientFromProxy($proxy));
        $this->assertSame($proxy, $tracer->getBinaryClient($proxy->getHashId()));
        $this->assertSame(2, count($tracer->getBinaryClients()));

        // ------------------------------------
        // api client request
        // ------------------------------------

        $request = $tracer->newRpcClientRequest('pdm.goods.info', 'get')->start();
        usleep(mt_rand(10000, 99999));
        $request->finish();
        $this->assertSame($request, $tracer->getRpcClientRequest($request->getHashId()));
        $this->assertSame(1, count($tracer->getRpcClientRequests()));

        $request = $tracer->newRpcClientRequest('pdm.goods.stock', 'get')->start();
        usleep(mt_rand(10000, 99999));
        $request->setException(new \RuntimeException('test api exception'));
        $this->assertSame($request, $tracer->getRpcClientRequest($request->getHashId()));
        $this->assertSame(2, count($tracer->getRpcClientRequests()));

        $request = $tracer->newRpcClientRequest('pdm.goods.price', 'get')->start();
        usleep(mt_rand(10000, 99999));
        $request->finish();
        $this->assertSame($request, $tracer->getRpcClientRequest($request->getHashId()));
        $this->assertSame(3, count($tracer->getRpcClientRequests()));

        $this->assertFalse($tracer->isFinished());
        $this->assertSame($tracer, $tracer->flush());
        $this->assertTrue($tracer->isFlushed());
        $this->assertTrue($tracer->isFinished());

        // var_export($this->logHandler->getRecords());
    }
}
