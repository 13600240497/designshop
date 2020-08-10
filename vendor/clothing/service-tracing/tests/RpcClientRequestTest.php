<?php

namespace Clothing\Tools\Tests;

/**
 * @internal
 */
class RpcClientRequestTest extends TestCase
{
    public function testAll()
    {
        $tracerServiceId  = 'tracer.service.id';
        $tracerMethodName = 'tracer.service.method';
        $rpcServiceId     = 'rpc.service.id';
        $rpcMethodName    = 'rpc.method';

        $tracer = $this->getTracer(
            $this->getLocalService('ZF', 'zaful-app'),
            $this->getRecorder('phpunit_rpc_request_test', $this->getLogHandler()),
            $tracerServiceId,
            $tracerMethodName
        );

        $request = $tracer->newRpcClientRequest($rpcServiceId, $rpcMethodName);

        $this->assertRegExp('/^\w{24}$/', $request->getSpanId());
        $this->assertSame($tracer->getSpanId(), $request->getParentSpanId());
        $this->assertSame($rpcServiceId, $request->getServiceId());
        $this->assertSame($rpcMethodName, $request->getMethodName());
        $this->assertSame([
            'traceId: ' . $tracer->getTraceId(),
            'siteCode: ' . $tracer->getLocalService()->getSiteCode(),
            'spanId: ' . $request->getSpanId(),
            'parentId: ' . $tracer->getSpanId(),
        ], $request->getHeaders());
    }
}
