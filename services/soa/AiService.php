<?php
namespace app\services\soa;

use GuzzleHttp\RequestOptions;

/**
 * 人工智能系统SOA服务
 */
class AiService extends Service
{
    /** @var string 排序类型 - 升序 */
    const SORT_TYPE_ASC = 'ASC';
    /** @var string 排序类型 - 降序 */
    const SORT_TYPE_DESC = 'DESC';

    /** @var array 选品系统配置 */
    private $config;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset(app()->params['soa']['ai'])) {
            throw new Exception('没有人工智能系统配置');
        }

        $config = app()->params['soa']['ai'];
        if (!isset($config['apiGateway'])) {
            throw new Exception('人工智能系统配置不完整');
        }

        $this->config = $config;
    }

    /**
     * 获取人工智能商品数据
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=62685305
     * @param array
     * @return array
     */
    public function getSelectIpsMainReportData($params)
    {
        $_params = $params;

        //显示方式: SPU 、SKU
        $_params['show_type'] = $params['show_type'] ?? 'SKU';

        //数据来源:POST、GET
        $_params['data_from'] = 'POST';

        //列表页 LIST 、内页: INNER
        $_params['request_type'] = $params['request_type'] ?? 'LIST';

        $apiParams = [
            'mfunc'     => 'ips',
            'func'      => 'selectIpsMainReportData',
            'params'    => $_params
        ];
        return $this->aiPost($apiParams);
    }

    private function aiPost($apiParams)
    {
        $options = [
            RequestOptions::HEADERS => ['Content-type' => 'application/json'],
            RequestOptions::BODY => json_encode($apiParams)
        ];
        return $this->request('POST', $this->config['apiGateway'], $options);
    }
}