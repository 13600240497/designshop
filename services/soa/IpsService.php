<?php
namespace app\services\soa;

/**
 * 选品系统SOA服务
 *
 * @see http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=64259363
 * @author TianHaisen
 * @since 1.5.0
 */
class IpsService extends Service
{
    /** @var array 选品系统配置 */
    private $config;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset(app()->params['soa']['ips'])) {
            throw new Exception('没有选品系统配置');
        }

        $config = app()->params['soa']['ips'];
        if (!isset($config['apiUrlPrefix'], $config['key'], $config['sn'], $config['groupCodeToWebsiteCodeMap'])) {
            throw new Exception('选品系统配置不完整');
        }

        $this->config = $config;
    }

    /**
     * 根据GEshop站点组简码获取IPS系统站点简码
     * @param string $siteGroupCode
     * @return string IPS系统站点简码; NULL 没有对应关系
     */
    public function getIpsWebsiteCodeByGeshopSiteGroupCode($siteGroupCode)
    {
        return $this->config['groupCodeToWebsiteCodeMap'][$siteGroupCode] ?? NULL;
    }

    /**
     * 获取活动信息
     *
     * @param array $params API参数
     *  - 字段名称       类型    必填     说明
     *  - activity_id   int	    N       活动id
     *  - website_code	string	Y	    网站编码
     *
     * @return \ego\curl\Result|array
     */
    public function getActivityList($params)
    {
        return $this->post($this->getApiUrl('activity/index'), $this->buildApiRequestParams($params));
    }

    /**
     * 获取活动子活动分组信息
     *
     * @param array $params API参数
     *  - 字段名称       类型    必填     说明
     *  - activity_id   int	    Y       活动id
     *
     * @return \ego\curl\Result|array
     */
    public function getActivityGroupList($params)
    {
        return $this->post($this->getApiUrl('activity-group/index'), $this->buildApiRequestParams($params));
    }

    /**
     * 获取子活动信息
     *
     * @param array $params API参数
     *  - 字段名称                      类型    必填     说明
     *  - activity_child_group_id      int	   Y       子活动分组id
     *
     * @return \ego\curl\Result|array
     */
    public function getActivityChildList($params)
    {
        return $this->post($this->getApiUrl('activity-child/index'), $this->buildApiRequestParams($params));
    }

    /**
     * 获取单个子活动已选SKU，有同款SKU过滤，只保留销量最好的那个SKU
     *
     * @param array $params API参数
     *  - 字段名称                类型    必填     说明
     *  - activity_child_id      int	Y       子活动ID
     *
     * @return \ego\curl\Result|array
     */
    public function getSingleActivityProductList($params)
    {
        return $this->post($this->getApiUrl('activity-product/index'), $this->buildApiRequestParams($params));
    }

    /**
     * 获取多个子活动已选SKU，没有同款SKU过滤
     *
     * @param array $params API参数
     *  - 字段名称                类型    必填     说明
     *  - activity_child_id      array	Y       子活动ID
     *
     * @return \ego\curl\Result|array
     */
    public function getMultiActivityProductList($params)
    {
        return $this->post($this->getApiUrl('activity-product/index-new'), $this->buildApiRequestParams($params));
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
            'sn'    => $this->config['sn'],
            'data'  => $jsonParams
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
        return $this->getFullApiUrl($this->config['apiUrlPrefix'], $apiUri);
    }
}