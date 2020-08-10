<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\LocalService;

/**
 * @internal
 */
class LocalServiceTest extends TestCase
{
    public function testLocalService()
    {
        $siteCode  = 'ZF';
        $appName   = 'zaful-pc';
        $servers   = [
            'SERVER_ADDR'   => '127.0.0.1',
            'HTTP_SITECODE' => 'DL',
            'HTTP_TRACEID'  => '9b559735d3db127adc629d3e',
            'HTTP_SPANID'   => '659c51f60072b86b65596d25',
            'HTTP_PARENTID' => '16995a3c528c0eda19e72f6b',
        ];
        $service   = new LocalService($appName, $siteCode, $servers);
        $localIpv4 = get_local_ipv4_addr();

        $this->assertSame($siteCode, $service->getSiteCode());
        $this->assertSame($appName, $service->getAppName());
        $this->assertSame([$localIpv4, '0'], $service->getLocalHost());
        $this->assertSame($servers['HTTP_TRACEID'], $service->getGlobalTraceId());
        $this->assertSame($servers['HTTP_PARENTID'], $service->getParentSpanId());
        $this->assertSame($servers['HTTP_SPANID'], $service->getSpanId());

        $this->assertSame('ABC', $service->setSiteCode('ABC')->getSiteCode());
        $this->assertSame('abc-web', $service->setAppName('abc-web')->getAppName());
        $this->assertSame([$localIpv4, '80'], $service->setLocalHost(null)->getLocalHost());
        $this->assertSame([$localIpv4, '80'], $service->setLocalHost('')->getLocalHost());
        $this->assertSame([$localIpv4, '80'], $service->setLocalHost(false)->getLocalHost());
        $this->assertSame([$localIpv4, '8801'], $service->setLocalHost('127.0.0.1', '8801')->getLocalHost());
        $this->assertSame([$localIpv4, '8803'], $service->setLocalHost('aabbcc', '8803')->getLocalHost());
        $this->assertSame(['128.0.0.0', '8804'], $service->setLocalHost('128.0.0.0', '8804')->getLocalHost());
        $this->assertSame('8c0e7c5af647f34235100540', $service->setGlobalTraceId('8c0e7c5af647f34235100540')->getGlobalTraceId());
        $this->assertSame('1c63f36acf0b93b10cff44c9', $service->setParentSpanId('1c63f36acf0b93b10cff44c9')->getParentSpanId());
        $this->assertSame('b08938e236aebaaef59a6ca6', $service->setSpanId('b08938e236aebaaef59a6ca6')->getSpanId());

        $servers = [
            'HTTP_SITECODE' => 'DL',
            'SERVER_ADDR'   => '192.168.1.1',
            'SERVER_PORT'   => 8890,
        ];

        $service = new LocalService($appName, null, $servers);
        $this->assertSame($servers['HTTP_SITECODE'], $service->getSiteCode());
        $this->assertSame('', $service->getGlobalTraceId());
        $this->assertSame('', $service->getParentSpanId());
        $this->assertSame('', $service->getSpanId());
        $this->assertSame([$servers['SERVER_ADDR'], (string) $servers['SERVER_PORT']], $service->getLocalHost());

        $service = new LocalService($siteCode, $appName);
        $this->assertSame([$localIpv4, '0'], $service->getLocalHost());
    }
}
