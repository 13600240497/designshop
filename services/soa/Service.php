<?php

namespace app\services\soa;

use ego\curl\StandardResponseCurl;
use GuzzleHttp\RequestOptions;
use app\base\ServiceTrack;

/**
 * 服务组件
 */
class Service extends StandardResponseCurl
{
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
    
    /**
     * POST请求API接口
     *
     * @param string $apiUrl  API接口URL
     * @param array  $params  Body参数
     * @param array  $headers Header参数
     *
     * @return Result|array
     * @throws
     */
    protected function post($apiUrl, $params)
    {
        $options = ['form_params' => $params];
        if ($rpcTracer = ServiceTrack::getRpcTracer(ServiceTrack::TRACE_RPC_SOA, $apiUrl)) {
            $rpcTracer->start();
            $options[RequestOptions::HEADERS] = ServiceTrack::getRpcTraceHeader($rpcTracer);
            $rpcTracer->finish();
        }

        return $this->request('POST', $apiUrl, $options);
    }
    
    /**
     * GB站点POST请求API接口
     *
     * @param $apiUrl
     * @param $params
     * @param $headers
     *
     * @return array|\ego\curl\Result
     * @throws
     */
    protected function gbPost($apiUrl, $params, $headers)
    {
        $body = ['body' => $params, 'header' => $headers];
        $options = [
            RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
            RequestOptions::BODY    => \GuzzleHttp\json_encode($body)
        ];
        if ($rpcTracer = ServiceTrack::getRpcTracer(ServiceTrack::TRACE_RPC_SOA, $apiUrl)) {
            $rpcTracer->start();
            $options[RequestOptions::HEADERS] = array_merge($options[RequestOptions::HEADERS], ServiceTrack::getRpcTraceHeader($rpcTracer));
            $rpcTracer->finish();
        }
        return $this->request('POST', $apiUrl, $options);
    }
    
    /**
     * 获取API接口URL完整地址
     *
     * @param string $apiUrlPrefix API接口URL前缀
     * @param string $apiUri       API接口URI
     *
     * @return string API接口URL完整地址
     */
    protected function getFullApiUrl($apiUrlPrefix, $apiUri)
    {
        return rtrim($apiUrlPrefix, '/') . '/' . ltrim($apiUri, '/');
    }
}
