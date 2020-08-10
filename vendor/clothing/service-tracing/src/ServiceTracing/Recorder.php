<?php

namespace Clothing\Tools\ServiceTracing;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * 追踪日志记录器
 *
 */
class Recorder
{
    use LoggerAwareTrait;

    /**
     * 构造方法
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger=null)
    {
        $this->setLogger(is_null($logger) ? new NullLogger : $logger);
    }

    /**
     * 刷新所有追踪记录到日志
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     * @return void
     */
    public function flush(Tracer $tracer)
    {
        $this->recordServerAnnotations($tracer);
        $this->recordRpcClientAnnotations($tracer);
    }

    /**
     * 记录服务端日志及注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     *
     * @return void
     */
    public function recordServerAnnotations(Tracer $tracer)
    {
        $traceInfo = array_merge(
            $this->getBasicTraceInfo($tracer),
            ['annotations' => $this->getServerAnnotations($tracer)]
        );

        $annotations = [];
        foreach ($tracer->getBinaryClients() as $client) {
            $tempAnnotations = [];
            $tempFilter = [
                'count' => 0,
                'slowDuration' => -1,
                'slowDurationHash' => '',
                'durationList' => [],
            ];
            foreach ($client->getRequests() as $request) {
                $hashId = $request->getHashId();
                $temp = $this->getBinaryAnnotations($tracer, $client, $request);
                if ($limit = $client->getRequestCountLimit()) {
                    if (++$tempFilter['count'] >= $limit) {
                        if ($temp['duration'] <= $tempFilter['slowDuration']) {
                            continue;
                        }
                        unset($tempAnnotations[$tempFilter['slowDurationHash']]);
                        unset($tempFilter['durationList'][$tempFilter['slowDurationHash']]);
                        $tempFilter['slowDuration'] = $temp['duration'];
                        $tempFilter['slowDurationHash'] = $hashId;
                        foreach ($tempFilter['durationList'] as $key => $value) {
                            if ($value < $tempFilter['slowDuration']) {
                                $tempFilter['slowDuration'] = $value;
                                $tempFilter['slowDurationHash'] = $key;
                                continue;
                            }
                        }
                    }
                    $tempFilter['durationList'][$hashId] = $temp['duration'];
                    if ($temp['duration'] < $tempFilter['slowDuration'] || $tempFilter['slowDurationHash'] == $hashId) {
                        $tempFilter['slowDuration'] = $temp['duration'];
                        $tempFilter['slowDurationHash'] = $hashId;
                    }
                }
                $tempAnnotations[$hashId] = $temp;
            }

            $annotations = array_merge($annotations, array_values($tempAnnotations));
        }

        $this->writeLog(array_merge(
            $traceInfo,
            ['binaryAnnotations' => $this->sortByTimestamp($annotations)]
        ));
    }

    /**
     * 记录 RPC 客户端日志及注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     *
     * @return void
     */
    public function recordRpcClientAnnotations(Tracer $tracer)
    {
        $traceInfo = $this->getBasicTraceInfo($tracer);

        foreach ($tracer->getRpcClientRequests() as $request) {
            $data = array_merge(
                $traceInfo,
                [
                    'id'          => $request->getSpanId(),
                    'parentId'    => $request->getParentSpanId(),
                    'serviceId'   => $request->getServiceId(),
                    'spanName'    => $request->getMethodName(),
                    'annotations' => $this->getRpcAnnotations($tracer, $request),
                ]
            );

            if ($request->hasException()) {
                $data['binaryAnnotations'] = [$this->getRpcException($request)];
            }

            $this->writeLog($data);
        }
    }

    /**
     * 获取基础追踪数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     *
     * @return array
     */
    protected function getBasicTraceInfo(Tracer $tracer)
    {
        return [
            'siteName'          => $tracer->getLocalService()->getSiteCode(),
            'appName'           => $tracer->getLocalService()->getAppName(),
            'traceId'           => $tracer->getTraceId(),
            'id'                => $tracer->getSpanId(),
            'parentId'          => $tracer->getParentSpanId(),
            'serviceId'         => $tracer->getServiceId(),
            'spanName'          => $tracer->getMethodName(),
            'timestamp'         => round($tracer->getFinishTimestamp()),
            'duration'          => $tracer->getDuration(),
            'annotations'       => [],
            'binaryAnnotations' => [],
        ];
    }

    /**
     * 获取服务端注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer $tracer
     *
     * @return array
     */
    protected function getServerAnnotations(Tracer $tracer)
    {
        return [
            [
                'host'      => $tracer->getLocalService()->getLocalHost(),
                'timestamp' => round($tracer->getStartTimestamp()),
                'value'     => Tracer::SERVER_RECV,
            ],
            [
                'host'      => $tracer->getLocalService()->getLocalHost(),
                'timestamp' => round($tracer->getFinishTimestamp()),
                'value'     => Tracer::SERVER_SEND,
            ],
        ];
    }

    /**
     * 获取服务端请求客户端的注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer              $tracer
     * @param \Clothing\Tools\ServiceTracing\BinaryClient        $client
     * @param \Clothing\Tools\ServiceTracing\BinaryClientRequest $request
     *
     * @return array
     */
    protected function getBinaryAnnotations(
        Tracer $tracer,
        BinaryClient $client,
        BinaryClientRequest $request
    ) {
        $data = [
            'type'      => $client->getClientType(),
            'key'       => $client->getClientType() . '.' . $request->getMethod(),
            'value'     => $this->toJson($request->getParams()),
            'host'      => $tracer->getLocalService()->getLocalHost(),
            'timestamp' => round($request->getStartTimestamp()),
            'duration'  => $request->getDuration(),
        ];

        if ($request->hasException()) {
            $data['type']  = $data['type'] . '.exception';
            $data['value'] = exception_as_string($request->getException());
        }

        return $data;
    }

    /**
     * 获取 RPC 请求的注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\Tracer           $tracer
     * @param \Clothing\Tools\ServiceTracing\RpcClientRequest $request
     *
     * @return array
     */
    protected function getRpcAnnotations(Tracer $tracer, RpcClientRequest $request)
    {
        return [
            [
                'host'      => $tracer->getLocalService()->getLocalHost(),
                'timestamp' => round($request->getStartTimestamp()),
                'value'     => Tracer::CLIENT_START,
            ],
            [
                'host'      => $tracer->getLocalService()->getLocalHost(),
                'timestamp' => round($request->getFinishTimestamp()),
                'value'     => Tracer::CLIENT_RECV,
            ],
        ];
    }

    /**
     * 获取 RPC 异常注解数据
     *
     * @param \Clothing\Tools\ServiceTracing\RpcClientRequest $request
     *
     * @return array
     */
    protected function getRpcException(RpcClientRequest $request)
    {
        return [
            'type'      => 'rpc.exception',
            'key'       => 'rpc',
            'value'     => exception_as_string($request->getException()),
            'duration'  => round($request->getDuration()),
            'timestamp' => round($request->getFinishTimestamp()),
        ];
    }

    /**
     * JSON 格式化日志数据
     *
     * @codeCoverageIgnore
     *
     * @param array $data
     *
     * @throws \Clothing\Tools\ServiceTracing\Exception
     *
     * @return string
     */
    protected function toJson(array $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new Exception('JSON encode failed: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * 对客户端请求注解按时间进行排序
     *
     * @codeCoverageIgnore
     *
     * @param array $data
     *
     * @return array
     */
    protected function sortByTimestamp($data)
    {
        usort($data, function ($v1, $v2) {
            if ($v1['timestamp'] === $v2['timestamp']) {
                return 0;
            }

            return ($v1['timestamp'] < $v2['timestamp']) ? -1 : 1;
        });

        return array_values($data);
    }

    /**
     * 将相关数据写入到日志
     *
     * @param array $data
     *
     * @return void
     */
    protected function writeLog(array $data)
    {
        try {
            $json = $this->toJson($data);
            $this->logger->info($json);
        } catch (\Exception $e) {
        }
    }
}
