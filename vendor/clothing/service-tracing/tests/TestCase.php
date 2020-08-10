<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\LocalService;
use Clothing\Tools\ServiceTracing\Recorder;
use Clothing\Tools\ServiceTracing\Tracer;
use Clothing\Tools\ServiceTracing\TracingFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected $redisHost = '127.0.0.1';
    protected $redisPort = 6379;

    protected function setUp()
    {
        mb_internal_encoding('UTF-8');
    }

    protected function getLogHandler()
    {
        $handler = new StreamHandler(__DIR__ . '/testcase.log');
        $handler->setFormatter(new TracingFormatter);

        return $handler;
    }

    protected function getRecorder($name, AbstractHandler $handler)
    {
        $logger = new Logger($name);
        $logger->pushHandler($handler);

        return new Recorder($logger);
    }

    protected function getLocalService($siteCode, $appName, array $servers=null)
    {
        return new LocalService($siteCode, $appName, $servers);
    }

    protected function getTracer(LocalService $localService, Recorder $recorder, $serviceId, $methodName)
    {
        return new Tracer($localService, $recorder, $serviceId, $methodName);
    }
}
