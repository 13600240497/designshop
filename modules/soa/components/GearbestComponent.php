<?php

namespace app\modules\soa\components;

use app\base\SiteConstants;
use app\modules\common\gb\components\CommonGearbestComponnent;
use app\modules\component\gb\servers\GearbestSoaServer as GbSoaServer;
use app\services\search\ESearchService;
use ego\base\JsonResponseException;
use yii\base\Exception;

class GearbestComponent extends CommonGearbestComponnent
{
    const GOODS_ON_SALE         = 2; // 商品上架状态
    const GOODS_OFF_SALE        = 0; // 商品下架状态
    const LIMIT_GOODS_LABELID   = 7; // 限时限量商品labelId
    const ACTIVITY_MARK_UP      = 4; // 加价购活动
    const ACTIVITY_COMPOSE_GIFT = 6; // M元Y件活动
    
    /**
     * 普通商品组件数据
     *
     * @param string $skus
     *
     * @return array
     */
    public function getGeneralGoodsList(array $params)
    {
        $goodsList = $this->getBaseGoodsList($params);
        if (empty($goodsList)) {
            return $goodsList;
        }
        $data = [];
        $stockList = $this->getGoodsStockOfVirWh($params);
        foreach ($goodsList as $goods) {
            $indexKey = "{$goods['goodsSn']}_{$goods['whCode']}";
            $stock = $stockList[ $indexKey ] ?? [];
            if (0 == $stock['isVirtual'] && $stock['stockNum'] <= 0) {
                continue;
            }
            if (!empty($stock['isVirtual']) && empty($stock['stockNum'])) {
                $stock['stockNum'] = 10000;
            }
            $price = ['price' => $goods[ $this->getPriceTypeField($params['platforms']) ]];
            $data[] = $this->transToSiteBaseGoodsInfo($params, $goods, $price, $stock);
        }
        unset($goodsList, $stockList);
        if (!empty($params['componentData']['maxCount'])) {
            $data = array_slice($data, 0, $params['componentData']['maxCount'], true);
        }
        
        return $data;
    }
    
    /**
     * 多商品组件数据
     *
     * @param array $skus
     *
     * @return array
     */
    public function getManyGoodsList(array $params)
    {
        $data = [];
        if (!empty($params['sku'])) {
            $requestBody = [
                'platform'      => $params['platform'],
                'platforms'     => $params['platforms'],
                'pipeline'      => $params['pipeline'],
                'lang'          => $params['lang'],
                'siteCode'      => $params['siteCode'],
                'componentData' => $params['componentData'] ?? []
            ];
            if (is_array($params['sku']) && (count($params['sku']) != count($params['sku'], 1))) {
                foreach ($params['sku'] as $sku) {
                    $requestBody['sku'] = $sku['lists'];
                    $data[] = $this->getGeneralGoodsList($requestBody);
                }
            } else {
                $requestBody['sku'] = !empty($params['lists']) ? $params['sku'] : $params['sku'];
                $data = $this->getGeneralGoodsList($requestBody);
            }
        }
        
        return $data;
    }
    
    /**
     * 加价购商品组件数据
     *
     * @param $params
     *
     * @return array
     */
    public function getMarkUpGoodsList(array $params)
    {
        $activityResponse = (new GbSoaServer())->getComposeGiftGood($params);
        $this->checkActivityType($activityResponse, self::ACTIVITY_MARK_UP);
        $activityGoods = $activityResponse['data']['goodList'] ?? [];
        if (!empty($activityGoods) && is_array($activityGoods)) {
            $goodsSkus = implode(',', array_map(function ($item) {
                return "{$item['value']}_{$item['warehouseCode']}";
            }, $activityGoods));
        }
        
        return [
            'skus'      => $goodsSkus ?? '',
            'startTime' => $activityResponse['data']['startTime'] ?? 0,
            'endTime'   => $activityResponse['data']['endTime'] ?? 0
        ];
    }
    
    /**
     * M元Y件活动组件数据
     *
     * @param array $params
     *
     * @return string
     * @throws JsonResponseException
     */
    public function getComposeGiftGoodsList(array $params)
    {
        $avtivityResponse = (new GbSoaServer())->getComposeGiftGood($params);
        $this->checkActivityType($avtivityResponse, self::ACTIVITY_COMPOSE_GIFT);
        $activityGoods = $avtivityResponse['data']['goodList'] ?? [];
        if (!empty($activityGoods) && is_array($activityGoods)) {
            $activityGoods = array_slice($activityGoods, 0, 200);
            $goodsSkus = implode(',', array_map(function ($item) {
                return "{$item['value']}_{$item['warehouseCode']}";
            }, $activityGoods));
        }
        
        return [
            'skus'      => $goodsSkus ?? '',
            'startTime' => $avtivityResponse['data']['startTime'] ?? 0,
            'endTime'   => $avtivityResponse['data']['endTime'] ?? 0
        ];
    }
    
    /**
     * 抢购商品组件数据
     *
     * @param string $skus
     *
     * @return array
     */
    public function getRushBuyGoodsList(array $params)
    {
        $goodsList = $this->getBaseGoodsList($params);
        if (empty($goodsList)) {
            return $goodsList;
        }
        $data = [];
        try {
            // 取商品价格
            $skuArray = $this->explodeSkuVirWh($params['sku'], 'goodSn', 'warehouseCode');
            $requestData = [
                'goodSns'     => $skuArray,
                'sysLabelId'  => (string) self::LIMIT_GOODS_LABELID,
                'currentTime' => null,
                'lang'        => $params['lang'],
                'pipeline'    => $params['pipeline'],
                'platform'    => $params['platform']
            ];
            $priceResponse = (new GbSoaServer())->getSpecifiedPriceList($requestData);
            $noLimit = $this->checkLimitGoods($params['sku'], $priceResponse['data']['priceList']);
            $priceList = $this->buildPriceGoods($priceResponse['data']['priceList']);
            if (!empty($noLimit)) {
                $defaultPrice = $this->getPriceList(
                    ['sku' => $noLimit, 'lang' => $params['lang'], 'pipeline' => $params['pipeline']]
                );
                $priceList = array_merge($priceList, $defaultPrice);
            }
        } catch (Exception $exception) {
            if ($this->skuCodeFail == $exception->getCode()) {
                throw new Exception($exception->getMessage(), $exception->getCode());
            }
            // 接口异常时降级处理
            $priceList = [];
        }
        
        foreach ($goodsList as $goods) {
            $indexKey = "{$goods['goodsSn']}_{$goods['whCode']}";
            $price = $priceList[ $indexKey ] ?? [];
            $stock = [
                'stockNum'      => !empty($price) ? (intval($price['saleQty']) + intval($price['virtualSaleQty'])) : 0,
                'activityStock' => !empty($price) ? (intval($price['count']) + intval($price['virtualSaleQty'])) : 0
            ];
            if (($stock['stockNum'] - $stock['activityStock']) <= 0) {
                $price['startTime'] = 0;
                $price['endTime'] = 0;
            }
            
            $data[] = $this->transToSiteBaseGoodsInfo($params, $goods, $price, $stock);
        }
        
        unset($goodsList, $skuArray, $priceResponse, $priceList, $stockResponse);
        if (!empty($params['componentData']['maxCount'])) {
            $data = array_slice($data, 0, $params['componentData']['maxCount'], true);
        }
        
        return $data;
    }
    
    /**
     * 多时段抢购商品组件数据
     *
     * @param array $params
     *
     * @return array
     */
    public function getManyRushBuyGoodsList(array $params)
    {
        $data = [];
        if (!empty($params['sku'])) {
            $requestBody = [
                'platform'  => $params['platform'],
                'platforms' => $params['platforms'],
                'pipeline'  => $params['pipeline'],
                'lang'      => $params['lang'],
                'siteCode'  => $params['siteCode']
            ];
            if (is_array($params['sku']) && (count($params['sku']) != count($params['sku'], 1))) {
                foreach ($params['sku'] as $sku) {
                    $requestBody['sku'] = $sku['lists'];
                    $data[] = $this->getRushBuyGoodsList($requestBody);
                }
            } else {
                $data = $this->getRushBuyGoodsList($params);
            }
        }
        
        return $data;
    }
    
    /**
     * 获取搭售商品数据
     *
     * @param array $params
     *
     * @return array
     */
    public function getPartsGoodsList(array $params)
    {
        $goodsList = $this->getBaseGoodsList($params);
        if (empty($goodsList)) {
            return $goodsList;
        }
        $data = [];
        $categoryIds = array_column($goodsList, 'catId', 'goodsSn');
        $sourceSku = $skus = explode(',', $params['sku']);
        $mainSku = array_shift($skus);
        $partsList = $this->getPartsList($mainSku, $categoryIds, $params);
        $this->checkPartsGoodsConnection($mainSku, $skus, $partsList);
        
        $goodsList = array_combine(array_column($goodsList, 'goodsSn'), $goodsList);
        $stockList = $this->getGoodsStockOfVirWh($params);
        $main = mb_substr($mainSku, 0, strpos($mainSku, '_'));
        $priceField = $this->getPriceTypeField($params['platforms']);
        $data['totalAmount'] = 0;
        $data['finalAmount'] = 0;
        foreach ($sourceSku as $key => $source) {
            $isMain = ($key > 0) ? 0 : 1;
            $index = mb_substr($source, 0, strpos($source, '_'));
            if (
                empty($goodsList[ $index ]) ||
                (empty($isMain) && 0 == $stockList[ $source ]['isVirtual'] && $stockList[ $source ]['stockNum'] <= 0)
            ) {
                continue;
            }
            $price = $isMain
                ? $goodsList[ $index ][ $priceField ]
                : (
                !empty($partsList[ $main ][ $index ]['partsPrice'])
                    ? round($partsList[ $main ][ $index ]['partsPrice'], 2)
                    : 0.00
                );
            $stock = ['stockNum' => $stockList[ $source ]['stockNum'] ?? 0];
            if ($isMain > 0)
                $goodsList[ $index ]['shopPrice'] = 0;
            $data['goods_list'][] = $this->transToSiteBaseGoodsInfo(
                $params, $goodsList[ $index ], ['price' => $price], $stock
            );
            $data['totalAmount'] += $isMain
                ? $goodsList[ $index ][ $priceField ]
                : (
                (
                !empty($stockList["{$source}"]['stockNum'] || $stockList[ $source ]['isVirtual'] == 1)
                    ? $goodsList[ $index ]['shopPrice'] : 0.00)
                );
            $data['finalAmount'] += $isMain
                ? $goodsList[ $main ][ $priceField ]
                : (
                (
                    (isset($stockList["{$source}"]['stockNum']) && $stockList["{$source}"]['stockNum'] > 0)
                    || $stockList[ $source ]['isVirtual'] == 1
                )
                    ? (
                        !empty($partsList[ $main ][ $index ]['partsPrice'])
                        ? round($partsList[ $main ][ $index ]['partsPrice'], 2)
                        : 0.00
                    )
                    : 0.00
                );
        }
        
        $data['saveAmount'] = round($data['totalAmount'] - $data['finalAmount'], 2);
        $data['totalAmount'] = round($data['totalAmount'], 2);
        $data['finalAmount'] = round($data['finalAmount'], 2);
        unset($goodsList, $priceResponse, $priceList, $stockResponse, $stockList);
        
        return $data;
    }
    
    /**
     * 获取主商品配件商品数据
     *
     * @param string $mainSku
     *
     * @return array
     */
    private function getPartsList(string $mainSku, array $categoryIds, $params)
    {
        $partsRequest = [];
        $goodSnMain = mb_substr($mainSku, 0, strpos($mainSku, '_'));
        $warehouseCode = mb_substr($mainSku, strpos($mainSku, '_') + 1, strlen($mainSku));
        
        $temp = [
            'goodSnMain'    => (string) $goodSnMain,
            'warehouseCode' => (string) $warehouseCode,
            'categoryId'    => (string) $categoryIds[ $goodSnMain ],
        ];
        array_push($partsRequest, $temp);
        $partsResponse = (new GbSoaServer())->getPartsInfo(
            ['lang' => $params['lang'], 'pipeline' => $params['pipeline'], 'partsListReq' => $partsRequest]
        );
        $parts = array_column($partsResponse['data']['list'], 'partsList', 'goodSnMain');
        foreach ($parts as &$pVal) {
            $pVal = array_combine(array_column($pVal, 'partsGoodSn'), $pVal);
        }
        
        return $parts ?? [];
    }
    
    /**
     * 获取系统coupon数据
     *
     * @param array $params
     *
     * @return array
     */
    public function getSystemCouponList(array $params)
    {
        $data = [];
        $couponResponse = (new GbSoaServer())->getCouponTemplate($params);
        if (!empty($couponResponse['data']['list']) && is_array($couponResponse['data']['list'])) {
            $warehouses = array_column($couponResponse['data']['list'], 'warehouses');
            $whList = $this->getWareHouseName($warehouses);
            foreach ($couponResponse['data']['list'] as $coupon) {
                $couponWhArr = !empty($coupon['warehouses']) ? array_map(function ($item) use ($whList) {
                    return $whList[ $item ] ?? '';
                }, $coupon['warehouses']) : '';
                $data[] = [
                    'coupon_code'               => $coupon['templateCode'],
                    'coupon_name'               => $coupon['couponName'],
                    'warehouses'                => !empty($couponWhArr) ? implode(',', $couponWhArr) : '',
                    'platforms'                 => $this->convertPlatformName($coupon['platforms']),
                    'start_time'                => date('Y/m/d', $coupon['startTime']),
                    'end_time'                  => date('Y/m/d', $coupon['endTime']),
                    'current_time'              => $_SERVER['REQUEST_TIME'],
                    'receive_start_time'        => $coupon['receiveStartTime'],
                    'receive_end_time'          => $coupon['receiveEndTime'],
                    'grant_total_count'         => intval($coupon['grantTotalCount']),
                    'already_grant_total_count' => intval($coupon['alreadyGrantTotalCount']),
                    'url_link'                  => $this->getCouponUrlLink($params, $coupon),
                    'pre_desc'                  => $this->getCouponDesc($coupon),
                    'norm_desc'                 => in_array($coupon['type'], [9, 13])
                        ? sprintf('min order \$%s', (string) $coupon['strategys'])
                        : ''
                ];
            }
        }
        unset($couponResponse);
        
        return $data;
    }
    
    /**
     * 单品推荐(GB)UI组件数据
     *
     * @param array $apiParams API参数
     *                         <pre>
     *                         API参数格式:
     *                         sku      string SKU列表，多个SKU用英文逗号分隔,SKU格式: 商品SKU_库存编码
     *                         lang     string geshop语言简码，如：en
     *                         platform string geshop平台简码，如：pc,wap
     *                         </pre>
     *
     * @return array
     */
    public function getSingleRecommendGoodsList(array $apiParams)
    {
        $this->checkSingleGoodsSku($apiParams['sku']);
        $couponCode = $apiParams['componentData']['right_code'] ?? '';
        
        return $this->getRecommendGoodsList($apiParams, [$apiParams['sku'] => $couponCode]);
    }
    
    /**
     * 双品推荐(GB)UI组件数据
     *
     * @param array $apiParams API参数
     *                         <pre>
     *                         API参数格式:
     *                         sku      string SKU列表，多个SKU用英文逗号分隔,SKU格式: 商品SKU_库存编码
     *                         lang     string geshop语言简码，如：en
     *                         platform string geshop平台简码，如：pc,wap
     *                         </pre>
     *
     * @return array
     */
    public function getDoubleRecommendGoodsList(array $apiParams)
    {
        $goodsSkuCouponMapping = [];
        if (!empty($apiParams['componentData']['left_sku'])) {
            $this->checkSingleGoodsSku($apiParams['componentData']['left_sku']);
            $goodsSkuCouponMapping[ $apiParams['componentData']['left_sku'] ] = $apiParams['componentData']['left_code'] ?? '';
        }
        
        if (!empty($apiParams['componentData']['right_sku'])) {
            $this->checkSingleGoodsSku($apiParams['componentData']['right_sku']);
            $goodsSkuCouponMapping[ $apiParams['componentData']['right_sku'] ] = $apiParams['componentData']['right_code'] ?? '';
        }
        
        return $this->getRecommendGoodsList($apiParams, $goodsSkuCouponMapping);
    }
    
    /**
     * 推荐商品
     *
     * @param array $apiParams             API参数
     * @param array $goodsSkuCouponMapping 组件和coupon对应关系
     *
     * @return array
     */
    private function getRecommendGoodsList(array $apiParams, $goodsSkuCouponMapping)
    {
        $goodsSkuList = $couponCodeList = [];
        foreach ($goodsSkuCouponMapping as $goodsSku => $couponCode) {
            $goodsSkuList[] = $goodsSku;
            if (!empty($couponCode)) $couponCodeList[] = $couponCode;
        }
        
        //获取商品基本信息
        $baseGoodsInfoList = $this->getBaseGoodsList($apiParams);
        if (empty($baseGoodsInfoList)) {
            return [];
        }
        
        $goodsInfoMapping = [];
        foreach ($baseGoodsInfoList as &$baseGoodsInfo) {
            $key = $baseGoodsInfo['goodsSn'] . '_' . $baseGoodsInfo['whCode'];
            
            $baseGoodsInfo['activityInfo'] = [
                'activityType' => 0,
                'price'        => 0,
                'startTime'    => 0,
                'endTime'      => 0,
                'couponCode'   => '',
                'leftQty'      => 0,
            ];
            $goodsInfoMapping[ $key ] = $baseGoodsInfo;
        }
        
        //获取库存信息
        $skuArray = $this->explodeSkuVirWh($apiParams['sku'], 'goodSn', 'virWhCode');
        $apiBodyParams = [
            'pipeline'         => $apiParams['pipeline'],
            'platform'         => $apiParams['platform'],
            'lang'             => $apiParams['lang'],
            'goodsPriceIdList' => $skuArray
        ];
        $stockResponse = (new GbSoaServer())->getGoodsStockOfVirWh($apiBodyParams);
        $stockInfoMapping = $this->buildGoodsStock($stockResponse['data']);
        
        //获取coupon信息
        $couponInfoMapping = null;
        if (!empty($couponCodeList)) {
            $apiBodyParams = [
                'lang'       => $apiParams['lang'],
                'couponList' => $couponCodeList,
                'pipeline'   => $apiParams['pipeline']
            ];
            $couponResponse = (new GbSoaServer())->findCouponInfoByCode($apiBodyParams);
            if (!empty($couponResponse['data']['list'])) {
                $couponInfoMapping = array_column($couponResponse['data']['list'], null, 'couponCode');
            }
        }
        
        foreach ($goodsInfoMapping as $goodsSku => &$baseGoodsInfo) {
            $couponCode = $goodsSkuCouponMapping[ $goodsSku ] ?? null;
            if (!empty($couponInfoMapping) && !empty($couponCode) && isset($couponInfoMapping[ $couponCode ])) {
                $couponInfo = $couponInfoMapping[ $couponCode ];
                //coupon是一口价类型, 10 14
                $couponType = (int) $couponInfo['type'];
                if (in_array($couponType, [10, 14])) {
                    $baseGoodsInfo['activityInfo']['activityType'] = 1;
                    $baseGoodsInfo['activityInfo']['price'] = $couponInfo['strategys'];
                    $baseGoodsInfo['activityInfo']['startTime'] = $couponInfo['startTime'];
                    $baseGoodsInfo['activityInfo']['endTime'] = $couponInfo['endTime'];
                    $baseGoodsInfo['activityInfo']['couponCode'] = $couponCode;
                    //$baseGoodsInfo['activityInfo']['leftQty'] = $couponInfo['limitCount'] - $couponInfo['useCount'];
                }
            }
            
            //如果没有coupon信息，获取商品的限时限量信息
            if (empty($baseGoodsInfo['activityInfo']['price'])) {
                $priceResponse = $this->getAllGoodsLimitPriceAndQtyInfo($apiParams, [$baseGoodsInfo], false);
                if (!empty($priceResponse['data']['priceList'][0])) {
                    $goodsLimitPriceAndQtyInfo = $priceResponse['data']['priceList'][0];
                    $currentVirtualOrder = !empty($goodsLimitPriceAndQtyInfo['currentVirtualOrder'])
                        ? intval($goodsLimitPriceAndQtyInfo['currentVirtualOrder']) : 0;
                    $virtualSaleQty = !empty($goodsLimitPriceAndQtyInfo['virtualSaleQty'])
                        ? intval($goodsLimitPriceAndQtyInfo['virtualSaleQty']) : 0;
                    
                    $baseGoodsInfo['activityInfo']['activityType'] = 2;
                    $baseGoodsInfo['activityInfo']['price'] = $goodsLimitPriceAndQtyInfo['price'];
                    $baseGoodsInfo['activityInfo']['startTime'] = $goodsLimitPriceAndQtyInfo['startTime'];
                    $baseGoodsInfo['activityInfo']['endTime'] = $goodsLimitPriceAndQtyInfo['endTime'];
                    $baseGoodsInfo['activityInfo']['leftQty'] =
                        $goodsLimitPriceAndQtyInfo['saleQty'] - ($currentVirtualOrder + $virtualSaleQty);
                }
            }
            
            //如果没有优惠信息，取商品正常售价
            if (empty($baseGoodsInfo['activityInfo']['price'])) {
                $priceField = $this->getPriceTypeField($apiParams['platforms']);
                $baseGoodsInfo['activityInfo']['price'] = $baseGoodsInfo[ $priceField ];
            }
        }
        
        //聚合数据
        $goodsInfoList = [];
        foreach ($goodsInfoMapping as $goodsSku => $goodsInfo) {
            if (0 == $stockInfoMapping[ $goodsSku ]['isVirtual'] && $stockInfoMapping[ $goodsSku ]['stockNum'] <= 0) {
                continue;
            }
            $price = !empty($goodsInfo['activityInfo']['price'])
                ? $goodsInfo['activityInfo']['price']
                : $goodsInfo['shopPrice'];
            $priceInfo = [
                'startTime' => $goodsInfo['activityInfo']['startTime'],
                'endTime'   => $goodsInfo['activityInfo']['endTime'],
                'price'     => $price,
            ];
            if (!empty($stockInfoMapping[ $goodsSku ]['isVirtual']) && 0 == $stockInfoMapping[ $goodsSku ]['stockNum']) {
                $stockNum = 10000;
            } else {
                $stockNum = $stockInfoMapping[ $goodsSku ]['stockNum'] ?? 0;
            }
            $siteGoodsInfo = $this->transToSiteBaseGoodsInfo(
                $apiParams, $goodsInfo, $priceInfo, ['stockNum' => $stockNum]
            );
            
            $activityInfo = $goodsInfo['activityInfo'];
            unset($activityInfo['price']);
            $goodsInfoList[ $goodsSku ] = array_merge($siteGoodsInfo, $activityInfo);
        }
        
        return $goodsInfoList;
    }
    
    /**
     * 促销码(GB)UI组件数据
     *
     * @param array $apiParams API参数
     *                         <pre>
     *                         API参数格式:
     *                         sku      string SKU列表，多个SKU用英文逗号分隔,SKU格式: 商品SKU_库存编码
     *                         lang     string geshop语言简码，如：en
     *                         platform string geshop平台简码，如：pc,wap
     *                         </pre>
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getCouponGoodsList(array $apiParams)
    {
        $goodsCompositeList = $this->checkCouponGoodsSku($apiParams);
        $baseGoodsInfoList = $this->getBaseGoodsList($apiParams);
        if (empty($baseGoodsInfoList)) {
            return $baseGoodsInfoList;
        }
        $allSkuList = explode(SiteConstants::CHAR_COMMA, $apiParams['sku']);
        
        //获取库存信息,每次最多查询36个
        $stockInfoMapping = [];
        $chunkSkuList = array_chunk($allSkuList, 36);
        foreach ($chunkSkuList as $_skuList) {
            $apiParams['sku'] = $_skuList;
            $stockInfoMapping = $this->getGoodsStockOfVirWh($apiParams);
        }
        //获取coupon信息,每次最多查询16个
        $couponInfoMapping = $allCouponList = [];
        foreach ($goodsCompositeList as $list) {
            array_walk_recursive($list['coupons'], function ($item) use (&$allCouponList) {
                array_push($allCouponList, $item);
            });
        }
        $allCouponList = array_unique($allCouponList);
        $chunkCouponList = array_chunk($allCouponList, 16);
        foreach ($chunkCouponList as $_couponList) {
            $apiBodyParams = [
                'lang'       => $apiParams['lang'],
                'couponList' => $_couponList,
                'pipeline'   => $apiParams['pipeline']
            ];
            $couponResponse = (new GbSoaServer())->findCouponInfoByCode($apiBodyParams);
            if (!empty($couponResponse['data']['list'])) {
                $chunkCouponInfoMapping = array_column($couponResponse['data']['list'], null, 'couponCode');
                $couponInfoMapping = array_merge($couponInfoMapping, $chunkCouponInfoMapping);
            }
        }
        $goodsKeys = array_column($baseGoodsInfoList, 'goodsId');
        $baseGoodsInfoList = array_combine($goodsKeys, array_values($baseGoodsInfoList));
        $goodsInfoList = $missCouponList = [];
        foreach ($goodsCompositeList as $list) {
            $data = $this->buildCouponGoodsList(
                $apiParams, $list, $stockInfoMapping, $baseGoodsInfoList, $couponInfoMapping
            );
            $missCouponList = array_merge($missCouponList, $data['missList']);
            $goodsInfoList = array_merge($goodsInfoList, $data['goodsList']);
        }
        
        if (!empty($missCouponList) && empty($apiParams['type'])) {
            $errorMsg = sprintf('Coupon码 %s 不存在/非一口价类型', join(SiteConstants::CHAR_COMMA, $missCouponList));
            throw new Exception($errorMsg, $this->couponExsitsCodeFail);
        }
        unset($apiParams, $goodsCompositeList, $stockInfoMapping, $baseGoodsInfoList, $couponInfoMapping);
        if (!empty($apiParams['componentData']['maxCount'])) {
            $goodsInfoList = array_slice($goodsInfoList, 0, $apiParams['componentData']['maxCount'], true);
        }
        
        return $goodsInfoList;
    }
    
    /**
     * 组装促销码(GB)UI组件数据
     *
     * @param $apiParams
     * @param $list
     * @param $stockInfoMapping
     * @param $baseGoodsInfoList
     *
     * @throws Exception
     */
    private function buildCouponGoodsList($apiParams, $list, $stockInfoMapping, $baseGoodsInfoList, $couponInfoMapping)
    {
        //聚合数据
        $missCouponList = $goodsInfoList = [];
        foreach ($list['sku'] as $key => $sku) {
            $goods = str_replace('_', '#', $sku);
            if (empty($baseGoodsInfoList[ $goods ])) {
                continue;
            }
            $couponCode = $list['coupons'][ $key ];
            //没有对应coupon信息,或者coupon不是一口价类型, 10 14
            if (!isset($couponInfoMapping[ $couponCode ])
                || !in_array((int) $couponInfoMapping[ $couponCode ]['type'], [10, 14])
            ) {
                $missCouponList[] = $couponCode;
                continue;
            }
            $price = $couponInfoMapping[ $couponCode ]['strategys'];
            $stockNum = isset($stockInfoMapping[ $sku ]) ? $stockInfoMapping[ $sku ]['stockNum'] : 0;
            $siteGoodsInfo = $this->transToSiteBaseGoodsInfo(
                $apiParams, $baseGoodsInfoList[ $goods ], ['price' => $price], ['stockNum' => $stockNum]
            );
            $siteGoodsInfo['couponCode'] = $couponInfoMapping[ $couponCode ]['couponCode'];
            $siteGoodsInfo['limitCount'] = $couponInfoMapping[ $couponCode ]['limitCount'];
            $siteGoodsInfo['useCount'] = $couponInfoMapping[ $couponCode ]['useCount'];
            $siteGoodsInfo['leftCount'] = $siteGoodsInfo['limitCount'] - $siteGoodsInfo['useCount'];
            $siteGoodsInfo['startTime'] = ($siteGoodsInfo['leftCount'] > 0) ? $couponInfoMapping[ $couponCode ]['startTime'] : 0;
            $siteGoodsInfo['endTime'] = ($siteGoodsInfo['leftCount'] > 0) ? $couponInfoMapping[ $couponCode ]['endTime'] : 0;
            if ($siteGoodsInfo['leftCount'] < 0) {
                $siteGoodsInfo['leftCount'] = 0;
            }
            
            $goodsInfoList[ $sku ] = $siteGoodsInfo;
        }
        
        return ['missList' => $missCouponList, 'goodsList' => $goodsInfoList];
    }
    
    /**
     * APP专享价(GB)UI组件数据
     *
     * @param array $apiParams API参数
     *                         <pre>
     *                         API参数格式:
     *                         sku      string SKU列表，多个SKU用英文逗号分隔,SKU格式: 商品SKU_库存编码
     *                         lang     string geshop语言简码，如：en
     *                         platform string geshop平台简码，如：pc,wap
     *                         </pre>
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getAppSpecialPriceGoodsList(array $apiParams)
    {
        $goodsInfoList = [];
        $allSkuList = explode(SiteConstants::CHAR_COMMA, $apiParams['sku']);
        
        //获取商品基本信息
        $baseGoodsInfoList = $this->getBaseGoodsList($apiParams);
        if (empty($baseGoodsInfoList)) {
            return $goodsInfoList;
        }
        $this->checkAppPriceType($baseGoodsInfoList);
        //获取库存信息,每次最多查询36个
        $stockInfoMapping = [];
        $chunkSkuList = array_chunk($allSkuList, 36);
        foreach ($chunkSkuList as $_skuList) {
            $skuArray = $this->explodeSkuVirWh(join(SiteConstants::CHAR_COMMA, $_skuList), 'goodSn', 'virWhCode');
            $apiBodyParams = [
                'pipeline'         => $apiParams['pipeline'],
                'platform'         => $apiParams['platform'],
                'lang'             => $apiParams['lang'],
                'goodsPriceIdList' => $skuArray
            ];
            $stockResponse = (new GbSoaServer())->getGoodsStockOfVirWh($apiBodyParams);
            $chunkStockInfoMapping = $this->buildGoodsStock($stockResponse['data']);
            $stockInfoMapping = array_merge($stockInfoMapping, $chunkStockInfoMapping);
        }
        
        //聚合数据
        foreach ($baseGoodsInfoList as $baseGoodsInfo) {
            $key = $baseGoodsInfo['goodsSn'] . '_' . $baseGoodsInfo['whCode'];
            if (0 == $stockInfoMapping[ $key ]['isVirtual'] && $stockInfoMapping[ $key ]['stockNum'] <= 0) {
                continue;
            }
            $price = $baseGoodsInfo['appDisplayPrice'];
            if (1 == $stockInfoMapping[ $key ]['isVirtual'] && empty($stockInfoMapping[ $key ]['stockNum'])) {
                $stockNum = 10000;
            } else {
                $stockNum = isset($stockInfoMapping[ $key ]) ? $stockInfoMapping[ $key ]['stockNum'] : 0;
            }
            $baseGoodsInfo['shopPrice'] = $baseGoodsInfo['displayPrice'];
            $siteGoodsInfo = $this->transToSiteBaseGoodsInfo(
                $apiParams, $baseGoodsInfo, ['price' => $price], ['stockNum' => $stockNum]
            );
            $goodsInfoList[ $key ] = $siteGoodsInfo;
        }
        if (!empty($apiParams['componentData']['maxCount'])) {
            $goodsInfoList = array_slice($goodsInfoList, 0, $apiParams['componentData']['maxCount'], true);
        }
        
        return $goodsInfoList;
    }
    
    
    /**
     * 获取商品限时限量信息
     *
     * @param array   $apiParams         转换后的参数
     * @param array   $baseGoodsInfoList SOA基础商品接口返回的商品信息列表
     * @param boolean $useSpecified      是否使用指定时间接口，true 将来某个时间点的限时限量信息, false 服务端当前生效的限时限量信息
     *
     * @return array
     */
    private function getAllGoodsLimitPriceAndQtyInfo($apiParams, $baseGoodsInfoList, $useSpecified)
    {
        $priceResponse = null;
        if ($useSpecified) {
            $apiBodyGoodSns = [];
            foreach ($baseGoodsInfoList as $baseGoodsInfo) {
                $apiBodyGoodSns[] = [
                    'warehouseCode' => $baseGoodsInfo['whCode'],
                    'goodSn'        => $baseGoodsInfo['goodsSn'],
                ];
            }
            $apiBodyParams = [
                'pipeline'    => $apiParams['pipeline'],
                'platform'    => $apiParams['platform'],
                'lang'        => $apiParams['lang'],
                'currentTime' => time(),
                'sysLabelId'  => (string) static::LIMIT_GOODS_LABELID,
                'goodSns'     => $apiBodyGoodSns
            ];
            $priceResponse = (new GbSoaServer())->getSpecifiedPriceList($apiBodyParams);
        } else {
            $apiBodyRequestList = [];
            foreach ($baseGoodsInfoList as $baseGoodsInfo) {
                $apiBodyRequestList[] = [
                    'warehouseCode' => $baseGoodsInfo['whCode'],
                    'goodSn'        => $baseGoodsInfo['goodsSn'],
                    'labelId'       => static::LIMIT_GOODS_LABELID,
                    'platform'      => $apiParams['platform']
                ];
            }
            $apiBodyParams = [
                'pipeline'    => $apiParams['pipeline'],
                'platform'    => $apiParams['platform'],
                'lang'        => $apiParams['lang'],
                'requestList' => $apiBodyRequestList
            ];
            $priceResponse = (new GbSoaServer())->getPriceList($apiBodyParams);
        }
        
        return $priceResponse;
    }
    
    /**
     * 取商品基础数据
     *
     * @param array $apiParams
     * - sku      string SKU列表，多个SKU用英文逗号分隔,SKU格式: 商品SKU_库存编码
     * - lang     string 语言简码，如：en
     * - platform string 端口类型，如：1-PC 2-M端 3-IOS 4-ANDROID 5-PAD
     *
     * @return mixed
     */
    public function getBaseGoodsList($apiParams)
    {
        $sourceSku = $this->explodeSkuVirWh($apiParams['sku'], 'goodSn', 'virWhCode');
        $skus = str_replace('_', '#', $apiParams['sku']);
        $apiBodyParams = [
            'domain'  => $apiParams['pipeline'],
            'agent'   => $this->convertPlatformName($apiParams['platform']),
            'filters' => [
                [
                    'field'  => 'goodsId',
                    'values' => explode(',', $skus)
                ]
            ]
        ];
        $searchKey = config('soa.eSearch.gatewayKey');
        if ((string) config('soa.gb.siteCode') === $apiParams['pipeline'] && 'en' !== (string) $apiParams['lang']) {
            $domainPipe = config('lang')[ $apiParams['lang'] ]['pipeline'];
        }
        try {
            $server = app()->params['sites'][ $apiParams['siteCode'] ][ $searchKey ];
            $goodsResponse = (new ESearchService())
                ->setServer(str_replace('{domain}', $domainPipe ?? $apiParams['pipeline'], $server))
                ->call($apiBodyParams);
            if (!empty($apiParams['type'])) {
                $this->getGoodsOnsale($goodsResponse['data'], self::GOODS_OFF_SALE, $apiParams['platforms']);
            } else {
                if (
                    false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
                    || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
                ) {
                    // 检查商品sku是否存在
                    $this->checkGoodsExist($apiParams['sku'], $goodsResponse['data']);
                    // 检查是否有下架的商品
                    $this->checkGoodsOnsale($goodsResponse['data'], self::GOODS_OFF_SALE, $apiParams['platforms']);
                }
            }
            $goodsData = $this->sortSoaGoodsList($sourceSku, $goodsResponse['data'], $apiParams['platform']);
        } catch (Exception $exception) {
            if (
                false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
                || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
            ) {
                throw new Exception($exception->getMessage(), $exception->getCode());
            }
            $goodsData = [];
        }
        
        unset($skuArray, $sourceSku, $goodsResponse);
        
        return $goodsData;
    }
    
    
    /**
     * 过滤下架的商品
     *
     * @param array  $goods
     * @param int    $saleState
     * @param string $platform
     *
     */
    public function getGoodsOnsale(array &$goods, int $saleState, string $platform)
    {
        $stateField = ('pc' == $platform) ? 'webStatus' : (('m' == $platform) ? 'mStatus' : 'appStatus');
        $filter = array_filter($goods, function ($item) use ($saleState, $stateField) {
            return $saleState !== (int) $item[ $stateField ];
        });
        $goods = array_values($filter);
    }
    
    
    /**
     * 按照输入的SKU顺序排序
     *
     * @param $sourceGoods
     * @param $responseGoods
     *
     * @return array
     */
    private function sortSoaGoodsList($sourceGoods, $responseGoods, $platform)
    {
        $stateField = ('pc' == $platform) ? 'webStatus' : (('m' == $platform) ? 'mStatus' : 'appStatus');
        $data = [];
        foreach ($sourceGoods as $source) {
            foreach ($responseGoods as $key => $goods) {
                if ("{$source['goodSn']}_{$source['virWhCode']}" == "{$goods['goodsSn']}_{$goods['whCode']}") {
                    $goods['goodsStatus'] = $goods[ $stateField ];
                    $data[] = $goods;
                    unset($responseGoods[ $key ]);
                }
            }
        }
        unset($sourceGoods, $responseGoods);
        
        return $data;
    }
}
