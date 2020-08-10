<?php
namespace ego\soa;

use ego\curl\ResponseException;
use ego\enums\CommonError;
use ego\curl\StandardResponseCurl;
use GuzzleHttp\Psr7\Response;
use ego\curl\Result;

/**
 * 服务组件
 */
class Service extends StandardResponseCurl
{
    /**
     * @var string java端对应的领域
     */
    const DOMAIN = null;
    /**
     * @var string 日志分类
     */
    public $logCategory = 'curl-soa';
    /**
     * @var string
     */
    public $logItemClass = __NAMESPACE__ . '\LogItem';
    /**
     * @var array 错误码（首位）对应的错误类
     */
    protected $code2error = [
        //1 => 'app\enums\GoodsError', // 商品
        //2 => 'app\enums\CartError', // 购物车
        //3 => 'app\enums\OrderError', // 订单
        //4 => 'app\enums\PayError', // 支付
        //5 => 'app\enums\PromotionError', // 营销
        //6 => 'app\enums\LogisticsError', // 物流
        //7 => 'app\enums\UserError', // 会员
    ];

    /**
     * 发送请求
     *
     * @param string $service 服务名
     * @param string $method 方法名
     * @param array $params 请求参数
     * @return \ego\curl\Result|array
     */
    public function send($service, $method, array $params = [])
    {
        if (false !== strpos($service, '{sitename}')) {
            $service = str_replace(
                '{sitename}',
                app()->site->getName($params['siteCode'] ?? null),
                $service
            );
        }
        return $this->request($service, $method, $params);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->url)) {
            $this->url = app()->params['service']['url'];
        }
        $this->enableLog(
            app()->params['service']['enableLog'] ?? $this->enableLog
        );
        $this->guzzleOptions = app()->helper->arr->merge(
            $this->guzzleOptions,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-rpc-pro' => 'php'
                ]
            ],
            app()->params['service']['default_guzzle_options']
        );
    }

    /**
     * 获取错误信息
     *
     * @param int $code
     * @param string $rawMessage
     * @return string
     */
    public function getMessage($code, $rawMessage = '')
    {
        unset($rawMessage); // phpstorm unused ...
        $code = (string) $code;
        if (isset($this->code2error[$code{0}])) {
            return call_user_func(
                $this->code2error[$code{0}] . '::getMessageByJavaCode',
                $code
            );
        } else {
            return CommonError::getMessage(CommonError::ERR_SYSTEM_BUSY);
        }
    }

    /**
     * @inheritdoc
     */
    protected function requestInternal($service, $method, array $params)
    {
        $body = $this->buildRequestBody($service, $method, $params);
        $this->logItem->params = $body;
        return $this->getClient()->post('', ['body' => json_encode($body)]);
    }

    /**
     * 生成请求体
     *
     * @param string $service
     * @param string $method
     * @param array $body
     * @return array
     */
    protected function buildRequestBody($service, $method, array $body = [])
    {
         if (isset($body['siteCode']) && false === $body['siteCode']) {
            unset($body['siteCode']);
        } else {
            $body['siteCode'] = $body['siteCode'] ?? app()->site->code;
        }

        return [
            'header' => [
                'service' => $service,
                'method' => $method,
                //'domain' => static::DOMAIN,
                'version' => '1.0.0',
                'tokenId' => $this->getToken(),
            ],
            'body' => $body
        ];
    }

    /**
     * @inheritdoc
     */
    protected function parseResponse($response, $slient)
    {
        if ($response instanceof Response) {
            $response = json_decode($response->getBody()->getContents(), true) ?: [];
            $response['body'] = json_decode($response['body'] ?? null, true) ?:[];
            $response['code'] = $response['header']['code'] ?? null;
            $response['message'] = $response['header']['message'] ?? null;
            $response['data'] = $response['body']['data'] ?? null;
            $response['rawCode'] = $response['code'];
            $response['rawMessage'] = $response['message'];
            unset($response['body'], $response['header']);
        }

        try {
            $result = parent::parseResponse($response, $slient);
            if (0 !== $result->code) {
                $result->message = $this->getMessage($result->code, $result->rawMessage);
            }
            return $result;
        } catch (ResponseException $e) {
            throw new SoaException(
                $this->getMessage($e->getCode(), $e->getMessage()),
                $e->getCode(),
                $e,
                $e->response
            );
        }
    }

    /**
     * 获取token
     *
     * @return string
     */
    protected function getToken()
    {
        return app()->params['service']['token'] ?? '';
    }
}
