<?php
namespace ego\review;

use yii;
use Psr\Http\Message\ResponseInterface;
use ego\curl\StandardResponseCurl;

/**
 * 评论服务组件
 */
class Service extends StandardResponseCurl
{
    /**
     * @var string 日志分类
     */
    public $logCategory = 'curl-review';

    /**
     * @inheritdoc
     * @see \ego\soa\Service::init()
     */
    public function init()
    {
        parent::init();
        $this->url = app()->params['review']['review_url'];
        if (isset(app()->params['review']['enableLog'])) {
            $this->enableLog(app()->params['review']['enableLog']);
        }
    }

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
        return $this->request($service, $method, $params);
    }

    /**
     * @inheritdoc
     */
    public function requestInternal($service, $method, array $params)
    {
        $body = $this->buildRequestBody($service, $method, $params);
        $this->logItem->params = $body;
        return $this->getClient()->post($method, ['form_params' => $body]);
    }

    /**
     * 解析请求响应
     *
     * @param ResponseInterface $response
     * @param bool|array $slient
     * @return \ego\curl\Result
     * @throws Exception
     */
    protected function parseResponse($response, $slient)
    {
        $result = json_decode($response->getBody(), true);
        if (isset($result['response']) && !empty($result['response'])) {
            $result['code'] = (int) $result['response']['code'];
            $result['message'] = $result['response']['msg'];
            if (isset($result['response']['data'])) {
                if (isset($result['response']['count'])) {
                    $result['data']['data'] = $result['response']['data'];
                    $result['data']['count'] = $result['response']['count'];
                } else {
                    $result['data'] = $result['response']['data'];
                }

                $result['success'] = true;
            } else {
                $result['data'] = null;
                $result['success'] = false;
            }
            unset($result['response']);
        }

        return parent::parseResponse($result, $slient);
    }

    /**
     * 生成请求体
     *
     * @param string $service
     * @param int $method
     * @param array $data
     * @return array
     */
    protected function buildRequestBody($service, $method, array $data = [])
    {
        if (!isset($data['site'])) {
            $data['site'] = app()->site->name;
        }
        $data['reconsitution'] = 1;  //重构标识
        return [
            'token' => md5(app()->params['review']['reviewToken'] . json_encode($data)),
            'data' => json_encode($data),
        ];
    }
}
