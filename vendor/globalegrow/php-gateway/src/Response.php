<?php
namespace Globalegrow\Gateway;

/**
 * 响应
 */
class Response
{
    /**
     * @var int
     */
    public $code = -1;
    /**
     * @var string
     */
    public $message = 'The system is busy, please try again later';
    /**
     * @var array
     */
    public $data = [];
    /**
     * @var int
     */
    protected $httpCode;
    /**
     * @var string
     */
    protected $httpResponse;

    /**
     * 获取http响应状态码
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * 获取http响应内容
     *
     * @return string
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * 构造函数
     *
     * @param int $httpCode
     * @param string $httpResponse
     */
    public function __construct($httpCode, $httpResponse)
    {
        $this->httpCode = $httpCode;
        $this->httpResponse = $httpResponse;
        $this->parse($httpResponse);
    }

    /**
     * 解析响应
     *
     * @param $response
     */
    protected function parse($response)
    {
        if (!$response = json_decode($response)) {
            return;
        }
        $this->code = isset($response->code) ? $response->code : $this->code;
        $this->message = isset($response->message) ? $response->message : $this->message;
        $this->data = isset($response->data) ? (array) $response->data : $this->data;
    }
}
