<?php

namespace app\services\search;

use app\base\ServiceTrack;
use ego\curl\StandardResponseCurl;
use GuzzleHttp\RequestOptions;

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
     * POST请求搜索API接口
     *
     * @param $apiUrl
     * @param $params
     * @param $headers
     *
     * @return array|\ego\curl\Result
     */
    protected function post($apiUrl, $params)
    {
        $options = [
            RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
            RequestOptions::BODY    => \GuzzleHttp\json_encode($params)
        ];
        if ($rpcTracer = ServiceTrack::getRpcTracer(ServiceTrack::TRACE_RPC_ESEARCH, $apiUrl)) {
            $rpcTracer->start();
            $options[RequestOptions::HEADERS] = array_merge($options[RequestOptions::HEADERS],ServiceTrack::getRpcTraceHeader($rpcTracer));
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
