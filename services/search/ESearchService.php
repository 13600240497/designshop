<?php

namespace app\services\search;

use ego\curl\ResponseException;
use yii\base\Exception;

/**
 * Gearbest站点SOA的基础服务调用相关
 *
 * Class GearbestService
 *
 * @package app\services\soa
 */
class ESearchService extends Service
{
    /**
     * 搜索服务的请求URL
     *
     * @var string
     */
    private $searchServer;
    
    /**
     * 设置当前RPC服务准备请求的服务名称
     *
     * @param $soaServer
     *
     * @return $this
     */
    public function setServer($searchServer)
    {
        $this->searchServer = $searchServer;
        
        return $this;
    }
    
    /**
     * 针对SOA进行服务调用
     *
     * @param array $data
     *
     * @return mixed
     */
    public function call($data = [])
    {
        $siteSetting = [
            'version'     => config('soa.eSearch.version'),
            'accessToken' => config('soa.eSearch.tokenId'),
            'pageNo'      => 1,
            'pageSize'    => 200
        ];
        if (!empty($data['language'])) {
            $this->changeSiteLang($data);
        }
        $requestBody = array_merge($siteSetting, $data);
        try {
            $responseData = $this->post($this->searchServer, $requestBody);
        } catch (ResponseException $exception) {
            $errorMessage = \GuzzleHttp\json_decode($exception->getMessage(), true);
            if (!empty($errorMessage)) {
                throw new Exception($errorMessage['msg'], $errorMessage['code']);
            }
        }
        
        unset($requestBody);
        
        return $responseData->toArray();
    }
    
    /**
     * 转换成搜索服务需要的语言简码
     *
     * @param $data
     */
    private function changeSiteLang(&$data)
    {
        $esLangsMap = [
            'en'    => 'en',
            'ep'    => 'es',
            'fr'    => 'fr',
            'ru'    => 'ru',
            'po'    => 'pt',
            'it'    => 'it',
            'de'    => 'de',
            'tr'    => 'tr',
            'pt-br' => 'br'
        ];
        if (!empty($esLangsMap[ $data['language'] ])) {
            $data['language'] = $esLangsMap[ $data['language'] ];
        }
    }
}