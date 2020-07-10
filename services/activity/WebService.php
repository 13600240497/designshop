<?php
namespace app\services\activity;

use yii\base\Exception;

/**
 * 网站推送接口
 */
class WebService extends Service
{
    
    private $config;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

    }

    /**
     * 推送推广落地页到网站
     *
     * @param array $params API参数
     *  - 字段名称               类型    必填     说明
     *  - goods_sn              string	 Y      产品编码
     *  - lang                  string   Y      页面语言
     *  - platform              string   Y      平台    
     *  - html                  string   Y      页面html
     *  - css                   string   Y      css存储s3地址
     *  - js                    string   Y      js存储s3地址
     * @return \ego\curl\Result|array
     */
    public function pushHtml($siteCode, $params)
    {
        
        if (!isset(app()->params['sites'][$siteCode]['advertisement-push'])) {
            throw new Exception('没有配置网站推送接口');
        }
        $url = app()->params['sites'][$siteCode]['advertisement-push']['url'];
        return $this->post($url, $this->buildApiRequestParams($params));
    }

    /**
     * 根据产品编码和语言获取访问地址
     * @param     array       $params
     *  - 字段名称               类型    必填     说明
     *  - goods_sn              string   Y      产品编码
     *  - lang                  string   Y      页面语言
     *  - platform              string   Y      平台 
     * @return \ego\curl\Result|array   
     */
    public function getUrl($siteCode, $params)
    {
        if (!isset(app()->params['sites'][$siteCode]['advertisement-get-url'])) {
            throw new Exception('没有配置网站推送接口');
        }
        $url = app()->params['sites'][$siteCode]['advertisement-get-url']['url'];
        return $this->post($url, $this->buildApiRequestParams($params));
    }
    /**
     * 构建API请求参数
     * @param array $params API参数
     * @return array
     */
    private function buildApiRequestParams($params)
    {
        $jsonParams = json_encode($params);
        return [
            'token' => $this->getToken($jsonParams),
            'data'  => $jsonParams
        ];
    }

    /**
     * 获取API请求token
     * @param string $jsonParams json参数
     * @return string
     */
    private function getToken($jsonParams)
    {
        return md5(app()->params['gateway']['sign'] . $jsonParams);
    }

}