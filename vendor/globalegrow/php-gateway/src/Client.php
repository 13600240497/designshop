<?php
namespace Globalegrow\Gateway;

use Globalegrow\Gateway\Exceptions\RequestException;
use Globalegrow\Gateway\Exceptions\ResponseException;

/**
 * 网关客户端
 *
 * 目前只支持curl http请求
 */
class Client
{
    /**
     * @var string
     */
    protected $appKey;
    /**
     * @var string
     */
    protected $secret;
    /**
     * @var callable
     */
    protected $onSent;
    /**
     * @var array
     */
    protected $requestBody;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * 构造函数
     *
     * @param string $gatewayUrl 网关url
     * @param string $appKey app key
     * @param string $secret 密钥
     * @param Request $request 请求对象
     */
    public function __construct($gatewayUrl, $appKey, $secret, Request $request = null)
    {
        $this->appKey = $appKey;
        $this->secret = $secret;
        $this->request = $request ?: new Request($gatewayUrl);
    }

    /**
     * 发送请求
     *
     * @param string $method 请求方法
     * @param array $data 请求数据
     * @param string $module 模块，默认取用"."分割`$method`后的第一段
     * @return Response
     * @throws RequestException http状态码不为200时抛出
     * @throws ResponseException 返回码不为0时返回
     */
    public function send($method, array $data = [], $module = null)
    {
        $httpResponse = $this->request->send(
            $this->buildRequestBody($method, $data, $module)
        );
        $httpCode = $this->request->getinfo(CURLINFO_HTTP_CODE);
        $this->response = new Response($httpCode, $httpResponse);
        
        if ($this->onSent) {
            call_user_func($this->onSent, $this);
        }
        if (200 !== $httpCode) {
            throw new RequestException(
                $this->request->error(),
                $httpCode,
                null,
                $this
            );
        }
        
        if (0 != $this->response->code) {
            throw new ResponseException(
                $this->response->message,
                $this->response->code,
                null,
                $this
            );
        }
        return $this->response;
    }

    /**
     * 请求结束之后
     *
     * 可在回调中写日志
     *
     * @param callable $callback
     * @return $this
     */
    public function onSent(callable $callback)
    {
        $this->onSent = $callback;
        return $this;
    }

    /**
     * 获取`Request`对象
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * 获取响应
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 获取发送的请求体数据
     *
     * @return array
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }
    
    /**
     * 获取请求体数据的签名
     *
     * @param array $data
     *
     * @return string
     */
    public function getRequestSign(array $data)
    {
        return $this->sign($data);
    }
    
    /**
     * 生成请求体
     *
     * @param string $method
     * @param array $data
     * @param string $module
     * @return array
     */
    protected function buildRequestBody($method, array $data, $module)
    {
        $this->requestBody = [
            'app_key' => $this->appKey,
            'module' => $module ?: explode('.', $method, 2)[0],
            'method' => $method,
            'content' => json_encode($data),
            'timestamp' => gmdate('Y-m-d H:i:s')
        ];
        $this->requestBody['sign'] = $this->sign($this->requestBody);
        return $this->requestBody;
    }

    /**
     * 数据签名
     *
     * 算法：
     * - 根据参数名称的ASCII码表的顺序排序。如：foo:1, bar:2, foo_bar:3, foobar:4
     *   排序后的顺序是bar:2, foo:1, foo_bar:3, foobar:4。
     * - 将排序好的参数名和参数值拼装在一起，根据上面的示例得到的结果为：bar2foo1foo_bar3foobar4
     * - 将上一步得到的结果再拼接上提供的**serect**后进行md5
     *
     * @param array $data
     * @return string
     */
    protected function sign(array $data)
    {
        $result = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $result .= $key . $value;
        }
        return md5($result . $this->secret);
    }
}
