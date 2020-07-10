<?php

namespace app\services\soa;

use ego\curl\ResponseException;
use yii\base\Exception;

/**
 * Gearbest站点SOA的基础服务调用相关
 *
 * Class GearbestService
 *
 * @package app\services\soa
 */
class GearbestService extends Service
{
    /**
     * SOA调用的服务名，可以基于config('soa.servers.serverName')获取对应的SOA服务名
     *
     * @var string
     */
    private $soaServer;
    
    /**
     * SOA调用的方法名，可以基于config('soa')配置中获取对应的SOA模块的服务名下的方法名
     *  默认情况下，方法的大版本基于env配置设定，特定方法直接以下格式设定
     *  eg. SoaMethod|1.0.2，
     *
     * @var string
     */
    private $soaMethod;
    
    /**
     * SOA调用的方法版本，基于$soaMethod分析所得
     *
     * @var string
     */
    private $soaMethodVersion;
    
    /**
     * 设置当前RPC服务准备请求的服务名称
     *
     * @param $soaServer
     *
     * @return $this
     */
    final protected function setServer($soaServer)
    {
        $this->soaServer = $soaServer;
        
        return $this;
    }
    
    /**
     * 设置当前SOA服务准备请求的方法
     *
     * @param $soaMethod
     *
     * @return $this
     */
    final protected function setMethod($soaMethod)
    {
        $this->soaMethodVersion = config('soa.gb.env.version');
        sscanf($soaMethod, '%[^|]|%s', $this->soaMethod, $this->soaMethodVersion);
        
        return $this;
    }
    
    /**
     * 针对SOA进行服务调用
     *
     * @param array $data
     *
     * @return mixed
     */
    final protected function soaCall($data = [])
    {
        $siteSetting = [
            'siteCode'     => (string) config('soa.gb.siteCode'),
            'pipelineCode' => (string) $data['pipeline'],
            'platform'     => (int) config("soa.gb.platform"),
            'countryCode'  => (string) config(sprintf('soa.gb.countryCode.%s', $data['pipeline'])),
            'lang'         => (string) $data['lang'],
            //'currencyCode' => (string) config('soa.gb.app.currency')
        ];
        
        $requestHeader = [
            'service' => $this->soaServer,
            'method'  => $this->soaMethod,
            'version' => $this->soaMethodVersion,
            'tokenId' => config('soa.gb.env.tokenId'),
        ];
        
        $requestBody = array_merge($siteSetting, $data);
        try {
            $responseData = $this->gbPost(config('soa.gb.env.gateway'), $requestBody, $requestHeader);
        } catch (ResponseException $exception) {
            $errorMessage = \GuzzleHttp\json_decode($exception->getMessage(), true);
            if (!empty($errorMessage)) {
                throw new Exception($errorMessage['header']['message'], $errorMessage['header']['code']);
            }
        }
        
        unset($requestBody);
        
        return $responseData->toArray();
    }
    
}