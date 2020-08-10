<?php

namespace ego\curl;

use GuzzleHttp\RequestOptions;
use yii;
use ego\enums\CommonError;
use GuzzleHttp\Psr7\Response;

/**
 * 返回结果包含`code`、`data`、`message`的标准curl组件
 */
class StandardResponseCurl extends BaseResponseCurl
{
    /**
     * @var bool 返回数组而不是`\ego\curl\Result`？
     */
    private $asArray = false;
    
    /**
     * @inheritdoc
     * @return Result|array
     */
    public function request($method, $uri = '', array $params = [])
    {
        $result = parent::request($method, $uri, $params);
        if (null === $result) {
            $result = new Result(app()->helper->arrayResult(
                CommonError::ERR_CURL_REQUEST_FAIL,
                Yii::t('app', 'ERR_SYSTEM_BUSY')
            ));
        }
        if ($this->asArray) {
            $result = $result->toArray();
        }
        $this->asArray = false;
        
        return $result;
    }
    
    /**
     * 返回数组而不是`\ego\curl\Result`？
     *
     * @return $this
     */
    public function asArray()
    {
        $this->asArray = true;
        
        return $this;
    }
    
    /**
     * 解析请求响应
     *
     * @param Response|array $response
     * @param bool|array     $slient
     *
     * @return Result
     * @throws ResponseException
     */
    protected function parseResponse($response, $slient)
    {
        if ($response instanceof Response) {
            $result = json_decode($response->getBody(), true);
            if (is_array($result) && array_key_exists(RequestOptions::BODY, $result)) {
                $result = json_decode($result[ RequestOptions::BODY ], true);
            }
        } else {
            $result = $response;
        }
        $result = new Result($result ?: []);
        $throwException = $this->shouldThrowException(
            $result->code ?? CommonError::ERR_CURL_RESPONSE_FAIL,
            $slient
        );
        
        $result->code = $result->code ?? null;
        $result->message = $result->message ?? null;
        $result->data = $result->data ?? null;
        $result->rawCode = $result->code;
        $result->rawMessage = $result->message;
        
        if ($throwException) {
            if (!$response instanceof Response) {
                $response = new Response(200, [], json_encode($result));
            }
            throw new ResponseException(
                $response->getBody() . '',
                (int) $result->code,
                null,
                $response
            );
            
        } elseif (!isset($result->code)) {
            return new Result(app()->helper->arrayResult(
                CommonError::ERR_CURL_RESPONSE_FAIL,
                Yii::t('app', 'ERR_SYSTEM_BUSY'),
                null,
                ['response' => $response]
            ));
        }
        
        // 将错误码转换为0
        if (!is_bool($slient)) {
            $result->rawCode = $result->code;
            $result->code = 0;
        }
        
        return $result;
    }
    
    /**
     * @inheritdoc
     */
    protected function getIsEnableLog($response, $slient)
    {
        return parent::getIsEnableLog($response, $slient)
            || 0 !== $response->rawCode;
    }
}
