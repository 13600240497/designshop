<?php
namespace app\services\soa;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use yii\helpers\Json;

/**
 * 商品运营平台接口服务
 *
 * @see http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=64259363
 * @author TianHaisen
 * @since 2.1.7
 */
class SopService extends Service
{
    /** @var array 选品系统配置 */
    private $config;

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        if (!isset(app()->params['soa']['sop'])) {
            throw new Exception('没有商品运营平台系统配置');
        }

        $config = app()->params['soa']['sop']['api'];
        if (!isset($config['host'], $config['secretKey'])) {
            throw new Exception('商品运营平台系统配置不完整');
        }

        $this->config = $config;
    }

    /**
     * 获取活动信息
     *
     * @param array $params API参数
     *  - 字段名称       类型    必填     说明
     *  - website	    string	Y	    网站编码,如：ZF
     *
     * @return array
     * @see http://www.yapi.com.php5.egomsl.com/project/281/interface/api/7285
     */
    public function getRuleList($params)
    {
        $apiParams = array_merge([
            'source' => 'geshop',
            'date' => time(),
        ], $params);

        $client = new Client();
        try {

            $options = [
                RequestOptions::FORM_PARAMS => $this->buildApiRequestParams($apiParams),
                RequestOptions::VERIFY => false
            ];

            $url = $this->getApiUrl('geshop/rules');
            if (app()->env->isPreRelease()) {
                $_domain = parse_url($url, PHP_URL_HOST);
                $cookieJar = CookieJar::fromArray(
                    ['staging' => 'true'],
                    mb_substr($_domain, stripos($_domain, '.'))
                );
                $options[ RequestOptions::COOKIES ] = $cookieJar;
            }

            $response = $client->post($url, $options);
            $jsonString = $response->getBody()->getContents();
            return Json::decode($jsonString, true);
        } catch (ClientException $ex) {
            \Yii::error(
                sprintf('调用商品运营平台接口[%s], 错误: %s', json_encode($apiParams), $ex->getMessage()),
                __FUNCTION__
            );
            return ['status' => 500, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 构建API请求参数
     * @param array $params API参数
     * @return array
     */
    private function buildApiRequestParams($params)
    {
        ksort($params);

        $signString = '';
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $sign = md5($signString . $this->config['secretKey']);
        return [
            'data' => $params,
            'sign' => $sign
        ];
    }

    /**
     * 获取选品系统API请求token
     * @param string $jsonParams json参数
     * @return string
     */
    private function getToken($jsonParams)
    {
        return md5($this->config['key'] . $jsonParams . $this->config['sn']);
    }

    /**
     * 获取完整API请求地址
     * @param string $apiUri
     * @return string
     */
    private function getApiUrl($apiUri)
    {
        $apiUri = 'api/'. $apiUri;
        return $this->getFullApiUrl($this->config['host'], $apiUri);
    }

    private function getMockData()
    {
        $json = '[{"ruleId":"14","ruleName":"PC全球站women流量扶持"},{"ruleId":"16","ruleName":"测试001"},{"ruleId":"17","ruleName":"all-women 饰品"},{"ruleId":"18","ruleName":"PC-打压"},{"ruleId":"19","ruleName":"alltest002"},{"ruleId":"20","ruleName":"alltest001001001001"},{"ruleId":"21","ruleName":"M全球"},{"ruleId":"22","ruleName":"M-印度印尼非"},{"ruleId":"23","ruleName":"PC-法国德国"},{"ruleId":"24","ruleName":"ceshi001"}]';
        return json_decode($json, true);
    }
}