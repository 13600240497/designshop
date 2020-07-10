<?php
namespace app\components\base;

class SiteApi
{

    /**
     * 获取站点SKU商品数据
     *
     * @param array $params 接口参数
     * @param array $extParams 扩展参数
     * @return array
     * @throws \Exception
     */
    public static function getSiteGoodsInfo($params, $extParams=[])
    {
        if (empty($params) || !is_array($params)
            || !isset($params['siteCode'], $params['lang'], $params['goodsSku'])
        ) {
            throw new \Exception('参数错误');
        }

        $siteCode = $params['siteCode'];
        $lang     = $params['lang'];
        $goodsSku = $params['goodsSku'];
        if (empty($siteCode) || empty($lang)) {
            throw new \Exception('无效的参数 siteCode/lang');
        }

        if (empty($goodsSku)) {
            return [];
        }

        if (isZufulSite($siteCode)) {
            if (!isset($params['pipeline']) || empty($params['pipeline'])) {
                throw new \Exception('无效的参数 pipeline');
            }

            $explainComponent = new \app\modules\component\zf\components\ExplainComponent();
            $goodsInfo = $explainComponent->getGoodsList(
                $goodsSku, $lang, $siteCode, $params['pipeline'], true
            );

        } elseif (isDresslilySite($siteCode)) {
            // D 网
            $explainComponent = new \app\modules\component\dl\components\ExplainComponent();
            $goodsInfo = $explainComponent->getGoodsList(
                $goodsSku, $lang, $siteCode, $extParams, true
            );

        } else {

            $explainComponent = new \app\modules\component\components\ExplainComponent();
            $goodsInfo = $explainComponent->getGoodsList(
                $goodsSku, $lang, $siteCode, true
            );
        }

        return $goodsInfo;
    }

}