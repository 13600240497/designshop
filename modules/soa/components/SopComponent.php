<?php
namespace app\modules\soa\components;

use app\base\SitePlatform;

/**
 * 商品运营平台组件
 *
 * @property \app\services\soa\SopService $SopService
 *
 * @author TianHaisen
 * @since 2.1.7
 */
class SopComponent extends Component
{
    /**
     * 获取商品运营平台规律列表
     *
     * @param array $params Http GET参数
     * GET参数：
     * - websiteCode 网站编码,如：ZF
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getRuleList($params)
    {
        $rules = [
            ['site_code', 'require', 'site_code 不能为空'],
        ];
        $this->checkRequestParams($params, $rules);

        list($website,) = SitePlatform::splitSiteCode($params['site_code']);
        $apiParams = [
            'website' => $website
        ];
        !empty($params['keyword']) && $apiParams['keyword'] = $params['keyword'];
        $apiParams['page'] = $params['page_num'] ?? 1;
        $apiParams['pageSize'] = $params['page_size'] ?? 30;

        $result = $this->SopService->getRuleList($apiParams);
        $this->checkIpsApiStandardResponse($result);
        return $this->jsonSuccess($result['data']);
    }

    /**
     * 选品系统API接口返回检查
     *
     * @param array $result
     *
     * @throws \ego\base\JsonResponseException
     */
    protected function checkIpsApiStandardResponse($result)
    {
        if (!is_array($result) || !array_key_exists('status', $result) || !array_key_exists('data', $result)
            || !array_key_exists('msg', $result)) {
            throw $this->newJsonException('API接口返回不是标准格式！');
        }

        $status = (int)$result['status'];
        if ($status !== 200) {
            throw $this->newJsonException(sprintf('API接口错误: %s', $result['msg']));
        }
    }
}