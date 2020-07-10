<?php

namespace app\modules\component\gb\components;

use app\base\SitePlatform;
use app\modules\common\gb\components\CommonGearbestComponnent;
use app\modules\soa\components\GearbestComponent as GbComponent;
use yii\base\Exception;

class ExplainDataComponent extends CommonGearbestComponnent
{
    const GOODS_ON_SALE       = 2; // 商品上架状态
    const GOODS_OFF_SALE      = 3; // 商品下架状态
    const LIMIT_GOODS_LABELID = 7; // 限时限量商品labelId
    const ACTIVITY_MARK_UP    = 4; // 加价购活动
    
    protected $method   = ''; // 组件对应的方法
    protected $lang     = ''; // 多语言
    protected $platform = ''; // 站点平台
    protected $pipeline = ''; //站点渠道
    protected $siteCode = '';
    
    public function __construct($siteCode, $lang, $componentKey = '', $pipeline = '')
    {
        $this->lang = $lang;
        $this->pipeline = !empty($pipeline) ? $pipeline : 'GB';
        $this->siteCode = $siteCode;
        $this->platform = SitePlatform::getPlatformCodeBySiteCode($siteCode);
        $this->method = 'getGeneralGoodsList';
        
        if (!empty(config("soa.gb.uiGoodsInfoMethodMapping.{$componentKey}"))) {
            $this->method = config("soa.gb.uiGoodsInfoMethodMapping.{$componentKey}");
        }
    }
    
    /**
     * 获取组件数据
     *
     * @param $data
     */
    public function getRequestData(&$data)
    {
        //赋值coupon列表数据
        if (!empty($data['coupons']) && !isset($data['goodsSKU'])) {
            $data['coupons'] = $this->processGoodsSku($data['coupons']);
            $data['couponInfo'] = $this->getCouponData($data['coupons']);
        }
        
        //复制加价购商品列表数据
        if (!empty($data['activityId']) || (!empty($data['activityId']) && !empty($data['goodsSKU']))) {
            $activityData = $this->getActivityData($data['activityId']);
            $this->checkActivityGoodsExist($activityData['skus'], $data['goodsSKU'] ?? []);
            if (!empty($activityData['skus'])) {
                $this->method = 'getGeneralGoodsList';
                $data['goodsInfo'] = $this->getGoodsData($activityData['skus'], $data);
                $data['startTime'] = $activityData['startTime'];
                $data['endTime'] = $activityData['endTime'];
            }
        }
        
        //赋值商品列表数据
        if (!empty($data['goodsSKU']) && !isset($data['activityId'])) {
            $data['goodsSKU'] = $this->processGoodsSku($data['goodsSKU']);
            if (is_array($data['goodsSKU']) && (count($data['goodsSKU']) != count($data['goodsSKU'], 1))) {
                foreach ($data['goodsSKU'] as $item) {
                    $item['lists'] = $this->getGoodsData($item['lists'], $data);
                    $data['goodsInfo'][] = $item;
                }
            } else {
                $data['goodsInfo'] = $this->getGoodsData($data['goodsSKU'], $data);
            }
        }
        
        //促销码
        if (!empty($data['coupons']) && isset($data['goodsSKU'])) {
            $data['goodsInfo'] = $this->getGoodsData($data['goodsSKU'], $data);
        }
    }
    
    /**
     * 获取GB组件商品信息
     *
     * @param       $skuString
     * @param array $componentData
     * @param array $goodsSkuTabInfo
     * @param bool  $type
     *
     * @return mixed
     * @throws Exception
     */
    public function getGoodsData($skuString, $componentData = [], $goodsSkuTabInfo = [], $type = false)
    {
        $apiParams = [
            'sku'             => $skuString ?? '',
            'lang'            => $this->lang,
            'platform'        => $this->platform,
            'pipeline'        => $this->pipeline,
            'siteCode'        => $this->siteCode,
            'isCheckGoodsSku' => empty($componentData),
            'componentData'   => $componentData,
            'goodsSkuTabInfo' => $goodsSkuTabInfo,
            'type'            => $type
        ];
        
        //转换到SOA参数
        $this->transToSoaParams($apiParams);
        try {
            $method = $this->method;
            
            $goodsData = (new GbComponent())->$method($apiParams);
            
            return $goodsData;
        } catch (\Exception $exception) {
            $errorMsg = sprintf(
                '调用组件接口方法 %s 参数: %s 错误: %s', $this->method, json_encode($apiParams), $exception->getMessage()
            );
            \Yii::error($errorMsg, __METHOD__);
            
            /* 临时处理提示语 */
            if (10003 == $exception->getCode()) {
                throw new Exception("商品 {$skuString} 不存在", $exception->getCode());
            }
            
            /* 临时处理提示语 */
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
    
    /**
     * 获取GB组件counpon信息
     *
     * @param $coupons
     *
     * @return mixed
     */
    public function getCouponData($coupons)
    {
        $apiParams = [
            'platform'         => $this->platform,
            'lang'             => $this->lang,
            'templateCodeList' => explode(',', $coupons),
            'pipeline'         => $this->pipeline
        ];
        $method = $this->method;
        
        return (new GbComponent())->$method($apiParams);
    }
    
    /**
     * 获取GB组件活动数据
     *
     * @param $activityId
     *
     * @return mixed
     */
    public function getActivityData($activityId)
    {
        $apiParams = [
            'lang'       => (string) $this->lang,
            'activityId' => (string) $activityId,
            'pipeline'   => (string) $this->pipeline
        ];
        $method = $this->method;
        
        return (new GbComponent())->$method($apiParams);
    }
    
    /**
     * @file  : processGoodsSku
     * @brief : 处理sku数据
     *
     * @param $goodsSku
     *
     * @return mixed
     */
    private function processGoodsSku($goodsSku)
    {
        if (!is_array($goodsSku) && false !== strpos($goodsSku, '{')) {//数组形式保存的直接decode
            $goodsSku = json_decode($goodsSku, true);
        }
        
        return $goodsSku;
    }
    
    /**
     * 检查输入的商品是否存在于该活动
     *
     * @param $activityGoods
     * @param $sourceGoods
     *
     * @throws Exception
     */
    private function checkActivityGoodsExist(&$activityGoods, $sourceGoods)
    {
        if (!empty($activityGoods) && !empty($sourceGoods)) {
            $activityGoodsArray = explode(',', $activityGoods);
            $sourceGoodsArray = explode(',', $sourceGoods);
            $intersect = array_intersect($sourceGoodsArray, $activityGoodsArray);
            
            if (empty($intersect)) {
                throw new Exception("商品 {$sourceGoods} 不属于此活动", $this->codeFail);
            }
            $diffGoods = array_diff($sourceGoodsArray, $intersect);
            if (!empty($diffGoods)) {
                $skuString = implode(',', $diffGoods);
                throw new Exception("商品 {$skuString} 不属于此活动", $this->codeFail);
            }
            
            $activityGoods = implode(',', $intersect);
        }
    }
}