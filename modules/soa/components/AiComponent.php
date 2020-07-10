<?php
namespace app\modules\soa\components;

use app\base\SiteConstants;
use app\services\soa\AiService;

/**
 * 人工智能系统组件
 *
 * @property \app\services\soa\AiService $AiService
 */
class AiComponent extends Component
{

    /**
     * 过滤同款产品下7天销量最好的SKU
     *
     * @param string $siteCode geshop站点简码
     * @param array $goodsCodeList 选品获取的
     * 格式：
     * - spu 商品SPU
     * - sku 商品SKU
     *
     * @return array 同款销售最高的SKU
     * 格式：
     * - spu 商品SPU
     * - sku 商品SKU
     */
    public function filterSameSpuBestSaleSku($siteCode, $goodsCodeList)
    {
        //按SPU分组
        $goodsSpuMapping = [];
        foreach ($goodsCodeList as $goodsCode) {
            $spu = $goodsCode['spu'];
            if (!isset($goodsSpuMapping[$spu])) {
                $goodsSpuMapping[$spu] = [];
            }

            if (!in_array($goodsCode['sku'], $goodsSpuMapping[$spu]))
                $goodsSpuMapping[$spu][] = $goodsCode['sku'];
        }

        //合并同款下有多个SKU
        $filterGoodsSku = [];
        foreach ($goodsSpuMapping as $spuGoodsSkuList) {
            if (count($spuGoodsSkuList) == 1)
                continue;

            $filterGoodsSku = array_merge($filterGoodsSku, $spuGoodsSkuList);
        }

        //没有同款多个SKU情况
        if (empty($filterGoodsSku)) {
            return $goodsCodeList;
        }

        //获取人工智能商品销售数据
        $website = $this->transGeshopSiteCodeToWebSiteCode($siteCode);
        $apiParams = [
            'website' => $website,
            'show_type' => 'SKU',
            'code_type' => 'SKU',
            'code_number' => join(SiteConstants::CHAR_COMMA, $filterGoodsSku),
            'sort_field' => 'sku_sales_7',
            'sort_type' => AiService::SORT_TYPE_DESC,
            'page' => 1,
            'size' => 500,
        ];

        $sameSpuBestSaleMapping = [];
        $result = $this->AiService->slient()->asArray()->getSelectIpsMainReportData($apiParams);
        $this->checkAiApiStandardResponse($result);
        foreach ($result['data']['list'] as $goodsInfo) {
            $spu = $goodsInfo['goods_spu']; //goods_spu,goods_sn,sku_sales_7

            //按降序排序的，第一次出现的就是销售最高的
            if (isset($sameSpuBestSaleMapping[$spu]))
                continue;

            $sameSpuBestSaleMapping[$spu] = $goodsInfo['goods_sn'];
        }

        return $sameSpuBestSaleMapping;
    }

    /**
     * 选品系统API接口返回检查
     *
     * @param array $result
     *
     * @throws \ego\base\JsonResponseException
     */
    protected function checkAiApiStandardResponse($result)
    {
        // 请求失败
        if (false === $result || (isset($result['code']) && intval($result['code']) !== 200)) {
            $err = isset($data['code']) ? ' code:' . $data['code'] : '';
            $err .= isset($data['message']) ? ' message:' . $data['message'] : '';
            throw $this->newJsonException('人工智能系统接口异常，获取数据失败 '. $err);
        }

    }
}