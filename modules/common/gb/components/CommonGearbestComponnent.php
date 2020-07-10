<?php

namespace app\modules\common\gb\components;

use app\base\SiteConstants;
use ego\base\JsonResponseException;
use app\modules\component\gb\servers\GearbestSoaServer as GbSoaServer;
use yii\base\Exception;

class CommonGearbestComponnent extends \app\modules\soa\components\Component
{
    /** @var int SOA端口 - web */
    const SOA_PLATFORM_WEB = 1;
    /** @var int SOA端口 - wap */
    const SOA_PLATFORM_WAP = 2;
    /** @var int SOA端口 - ios */
    const SOA_PLATFORM_IOS = 3;
    /** @var int SOA端口 - android */
    const SOA_PLATFORM_ANDROID = 4;
    /** @var int SOA端口 - ipad */
    const SOA_PLATFORM_PAD = 5;
    
    /** @var int 价格标签 - 手机专享价 */
    const SOA_LABEL_ID_MOBILE = 3;
    
    /* 活动类型列表 */
    const ACTIVITY_TYPE_LIST = [1 => '满减', 2 => '满赠', 3 => '抽奖', 4 => '加价购', 5 => '买即赠', 6 => 'M元Y件'];
    
    /**
     * 将geshop参数类型转换为SOA对应参数类型
     *
     * @param array $params geshop参数
     *
     * @throws JsonResponseException
     */
    public function transToSoaParams(&$params)
    {
        $params['platforms'] = $params['platform'];
        if (isset($params['platform'])) {
            switch ($params['platform']) {
                case \app\base\SitePlatform::PLATFORM_CODE_PC:
                    $params['platform'] = static::SOA_PLATFORM_WEB;
                    break;
                case \app\base\SitePlatform::PLATFORM_CODE_WAP:
                    $params['platform'] = static::SOA_PLATFORM_WAP;
                    break;
                case \app\base\SitePlatform::PLATFORM_CODE_IOS:
                    $params['platform'] = static::SOA_PLATFORM_IOS;
                    break;
                case \app\base\SitePlatform::PLATFORM_CODE_ANDROID:
                    $params['platform'] = static::SOA_PLATFORM_ANDROID;
                    break;
                case \app\base\SitePlatform::PLATFORM_CODE_IPAD:
                    $params['platform'] = static::SOA_PLATFORM_PAD;
                    break;
                default:
                    throw new JsonResponseException($this->codeFail, sprintf('SOA接口不支持的端口%s', $params['platform']));
                    break;
            }
        }
    }
    
    /**
     * 转换为标准站点商品基本信息
     *
     * @param array $params
     * @param array $goodsInfo
     * @param float $price
     * @param int   $stock
     *
     * @return array
     */
    public function transToSiteBaseGoodsInfo($params, $goodsInfo, $price, $stock)
    {
        return [
            'goods_sn'           => $goodsInfo['goodsSn'] . '_' . $goodsInfo['whCode'],
            'goods_sku'          => $goodsInfo['goodsSn'] ?? '',
            'wh_code'            => $goodsInfo['whCode'] ?? '',
            'web_goods_sn'       => $goodsInfo['goodsWebSku'] ?? '',
            'goods_spu'          => $goodsInfo['goodsSpu'] ?? '',
            'web_goods_spu'      => $goodsInfo['goodsWebSpu'] ?? '',
            'category_id'        => $goodsInfo['categoryId'] ?? 0,
            'category_name'      => $goodsInfo['categoryName'] ?? '',
            'goods_title'        => $goodsInfo['goodsTitle'] ?? '',
            'market_price'       => $goodsInfo['shopPrice'] ?? 0.00,
            'shop_price'         => $price['price'] ?? 0.00,
            'url_title'          => $this->getGoodsUrlTitle($params, $goodsInfo['urlTitle'], $goodsInfo['whCode']),
            'goods_img'          => $this->getGoodsImageUrl($params, $goodsInfo['imgUrl']),
            'stock_num'          => (isset($stock['stockNum']) && ($stock['stockNum'] > 0)) ? $stock['stockNum'] : 0,
            'activity_stock'     => (isset($stock['activityStock']) && ($stock['activityStock'] > 0)) ? $stock['activityStock'] : 0,
            'promote_start_date' => $price['startTime'] ?? 0,
            'promote_end_date'   => $price['endTime'] ?? 0,
            'discount'           => ($goodsInfo['shopPrice'] > 0)
                ? $this->getGoodsDiscount($goodsInfo['shopPrice'], $price['price'] ?? 0.00)
                : 0,
            'pass_avg_score'     => $goodsInfo['passAvgScore'] ?? 0, // 通过审核的平均星级
            'price_type'         => $goodsInfo['priceType'] ?? 0 // 价格类型
        ];
    }
    
    /**
     * 取商品价格
     *
     * @param array $params
     *
     * @return array
     */
    public function getPriceList(array $params)
    {
        try {
            // 分割出sku和仓库
            $skuArray = $this->explodeSkuVirWh($params['sku'], 'goodSn', 'warehouseCode');
            $skuArray = array_chunk($skuArray, 36);
            $priceList = [];
            foreach ($skuArray as $chunkSku) {
                $requestData = ['requestList' => $chunkSku, 'lang' => $params['lang'], 'pipeline' => $params['pipeline']];
                $priceResponse = (new GbSoaServer())->getPriceList($requestData);
                $priceList = array_merge($priceList, $priceResponse['data']['priceList']);
            }
            
            return $this->buildPriceGoods($priceList);
        } catch (\Exception $exception) {
            // 接口异常时降级处理
            return [];
        }
    }
    
    /**
     * 组装一下价格商品数据
     *
     * @param array $priceList
     *
     * @return array
     */
    public function buildPriceGoods(array $priceList)
    {
        $data = [];
        if (!empty($priceList) && is_array($priceList)) {
            foreach ($priceList as $price) {
                $data["{$price['goodSn']}_{$price['warehouseCode']}"] =
                    [
                        'labelId'        => $price['labelId'],
                        'price'          => !empty($price['price']) ? $price['price'] : 0.00,
                        'startTime'      => !empty($price['startTime']) ? $price['startTime'] : 0,
                        'endTime'        => !empty($price['endTime']) ? $price['endTime'] : 0,
                        'saleQty'        => !empty($price['saleQty']) ? $price['saleQty'] : 0,
                        'count'          => !empty($price['count']) ? $price['count'] : 0,
                        'virtualSaleQty' => !empty($price['virtualSaleQty']) ? $price['virtualSaleQty'] : 0
                    ];
            }
            unset($priceList);
        }
        
        return $data;
    }
    
    /**
     * 取商品库存
     *
     * @param array $params
     *
     * @return array
     */
    public function getGoodsStockOfVirWh(array $params)
    {
        try {
            // 分割出sku和仓库
            $skuArray = $this->explodeSkuVirWh($params['sku'], 'goodSn', 'virWhCode');
            $requestData = ['goodsPriceIdList' => $skuArray, 'lang' => $params['lang'], 'pipeline' => $params['pipeline']];
            $stockResponse = (new GbSoaServer())->getGoodsStockOfVirWh($requestData);
        } catch (\Exception $exception) {
            // 接口异常时降级处理
            $stockResponse = [];
        }
        
        return !empty($stockResponse) ? $this->buildGoodsStock($stockResponse['data']) : [];
    }
    
    /**
     * 组装一下商品库存数据
     *
     * @param array $stockList
     *
     * @return array
     */
    public function buildGoodsStock(array $stockList)
    {
        $data = $sellOut = [];
        foreach ($stockList as $stock) {
            $indexKey = "{$stock['goodSn']}_{$stock['virWhCode']}";
            $data[ $indexKey ] =
                [
                    'stockNum'      => ($stock['stockSum'] > 0) ? $stock['stockSum'] : 0,
                    'activityStock' => ($stock['activityStock'] > 0) ? $stock['activityStock'] : 0,
                    'isVirtual'     => $stock['isVirtual'] ?? 1
                ];
            if (($stock['stockSum'] <= 0) && empty($stock['isVirtual'])) {
                array_push($sellOut, $indexKey);
            }
        }
        unset($stockList);
        if (
            !empty($sellOut)
            && (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
                || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods'))
        ) {
            $sellOut = implode(',', $sellOut);
            throw new Exception("商品 {$sellOut} 已售罄", $this->sellOutCodeFail);
        }
        
        return $data;
    }
    
    /**
     * 兼容GB站点商品信息，转换GB站点商品信息为统一字段
     *
     * @param array $goodsInfo 商品信息引用
     */
    public function transGeshopGoodsInfo(&$goodsInfo)
    {
        $goodsInfo['goods_id'] = 0;
        $goodsInfo['goods_sn'] = $goodsInfo['goodsSn'] . '_' . $goodsInfo['whCode'];
        $goodsInfo['goods_title'] = $goodsInfo['goodsTitle'];
        $goodsInfo['url_title'] = $goodsInfo['urlTitle'];
        $goodsInfo['goods_img'] = $goodsInfo['goodsImg'];
        $goodsInfo['market_price'] = $goodsInfo['shopPrice'];
        $goodsInfo['shop_price'] = $goodsInfo['price'];
        
        if (!empty($goodsInfo['stockNum'])) {
            $goodsInfo['stock_num'] = $goodsInfo['stockNum'];
        }
    }
    
    /**
     * 分割出sku和仓库
     *
     * @param         $skus
     * @param string  $goodsField
     * @param string  $wareField
     *
     * @return array
     * @throws JsonResponseException
     */
    public function explodeSkuVirWh($skus, string $goodsField, string $wareField)
    {
        $data = $error = [];
        if (!empty($skus)) {
            $skusArray = is_string($skus) ? array_unique(explode(',', $skus)) : $skus;
            foreach ($skusArray as $sku) {
                if (false === strstr($sku, '_')) {
                    array_push($error, $sku);
                } else {
                    $temp = explode('_', $sku);
                    array_push($data, [$goodsField => $temp[0], $wareField => $temp[1]]);
                }
            }
            if (!empty($error)) {
                $errMsg = '商品 ' . implode(',', $error) . ' 格式错误(sku_仓库)';
                throw new Exception($errMsg, $this->skuCodeFail);
            }
        }
        
        return $data;
    }
    
    /**
     * 检查商品sku是否存在
     *
     * @param string $skus
     * @param array  $goodsResult
     *
     * @throws JsonResponseException
     */
    public function checkGoodsExist(string $skus, array $goodsResult)
    {
        if (empty($goodsResult)) {
            $errMsg = "商品 $skus 不存在";
            throw new Exception($errMsg, $this->skuCodeFail);
        }
        
        $needle = [];
        $skusArr = explode(',', $skus);
        foreach ($goodsResult as $item) {
            $needle[] = "{$item['goodsSn']}_{$item['whCode']}";
        }
        $noExist = array_diff($skusArr, $needle);
        if (!empty($noExist)) {
            $noExist = implode(',', $noExist);
            $errMsg = "商品 {$noExist} 不存在";
            throw new Exception($errMsg, $this->skuCodeFail);
        }
    }
    
    /**
     * 检查是否有下架的商品
     *
     * @param array  $goods
     * @param int    $saleState
     * @param string $platform
     *
     * @throws JsonResponseException
     */
    public function checkGoodsOnsale(array $goods, int $saleState, string $platform)
    {
        $stateField = ('pc' == $platform) ? 'webStatus' : (('m' == $platform) ? 'mStatus' : 'appStatus');
        $offSale = [];
        foreach ($goods as $item) {
            if ($saleState === (int) $item[ $stateField ]) {
                array_push($offSale, "{$item['goodsSn']}_{$item['whCode']}");
            }
        }
        
        if (!empty($offSale)) {
            $offSale = implode(',', $offSale);
            $errMsg = "商品 {$offSale} 已下架";
            throw new Exception($errMsg, $this->skuCodeFail);
        }
    }
    
    /**
     * 检查活动ID是不是对应活动
     *
     * @param int $activityType
     *
     * @throws JsonResponseException
     */
    public function checkActivityType($activityResponse, int $activityType)
    {
        if (
            false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            if (empty($activityResponse['data'])) {
                throw new Exception('活动信息不存在', $this->codeFail);
            }
            
            if ($activityType != $activityResponse['data']['activityType']) {
                throw new Exception('该活动不是' . self::ACTIVITY_TYPE_LIST[ $activityType ] . '活动', $this->codeFail);
            }
        }
    }
    
    /**
     * 检查抢购商品是否是限时限量商品
     *
     * @param string $skus
     * @param array  $priceList
     *
     * @throws JsonResponseException
     */
    public function checkLimitGoods(string $skus, array &$priceList)
    {
        $priceList = array_filter($priceList, function ($item) {
            return !empty($item);
        });
        if (empty($priceList)) {
            return $skus;
        }
        $skuArray = explode(',', $skus);
        foreach ($priceList as $value) {
            $key = array_search("{$value['goodSn']}_{$value['warehouseCode']}", $skuArray);
            if (false !== $key) {
                unset($skuArray[ $key ]);
            }
        }
        
        return !empty($skuArray) ? implode(',', $skuArray) : '';
        /*if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')) {
            if (empty($priceList)) {
                throw new Exception("商品 {$skus} 不是限时限量商品", $this->skuCodeFail);
            }
            $skuArray = explode(',', $skus);
            foreach ($priceList as $value) {
                $key = array_search("{$value['goodSn']}_{$value['warehouseCode']}", $skuArray);
                if (false !== $key) {
                    unset($skuArray[ $key ]);
                }
            }
            
            if (!empty($skuArray)) {
                $noLimit = implode(',', $skuArray);
                throw new Exception("商品 {$noLimit} 不是限时限量商品", $this->skuCodeFail);
            }
        }*/
    }
    
    /**
     * 生成数组自定义下标
     *
     * @param array $array
     * @param array $names 下标键列名称
     *
     * @return array
     */
    public function arrayIndexByColumnNames($array, $names)
    {
        $arrayMapping = [];
        if (is_array($array)) {
            foreach ($array as $value) {
                $keyValues = [];
                foreach ($names as $_key) {
                    if (!empty($value[ $_key ]))
                        $keyValues[] = $value[ $_key ];
                }
                $key = join('_', $keyValues);
                $arrayMapping[ $key ] = $value;
            }
        }
        
        return $arrayMapping;
    }
    
    /**
     * 获取仓库数据中的字段信息
     *
     * @param $wareHouse
     *
     * @return array
     */
    public function getWareHouseName($wareHouse, string $field = 'virWhEnName')
    {
        if (!is_array($wareHouse)) {
            $wareHouse = explode(',', $wareHouse);
        }
        try {
            $wareHouseResponse = (new GbSoaServer())->getSiteVirWarehouseList([]);
            if (!empty($wareHouseResponse['data']) && is_array($wareHouseResponse['data'])) {
                $warehouses = [];
                $whArray = array_column($wareHouseResponse['data'], $field, 'virWhCode');
                foreach ($wareHouse as $item) {
                    if (isset($whArray[ $item ])) {
                        array_push($warehouses, $whArray[ $item ]);
                    }
                }
                $warehouses = implode(',', $warehouses);
            }
            
            return $warehouses ?? [];
        } catch (\Exception $exception) {
            // 接口异常时降级处理
            return [];
        }
    }
    
    /**
     * 获取品牌数据中的字段信息
     *
     * @param        $brands
     * @param string $field
     *
     * @return string
     */
    public function getBrandName($brands, string $field = 'brandName')
    {
        if (!is_array($brands)) {
            $brands = explode(',', $brands);
        }
        
        $data = ['brandCodeList' => $brands];
        $brandResponse = (new GbSoaServer())->getBrandList($data);
        if (!empty($brandResponse['data']) && is_array($brandResponse['data'])) {
            $brandsName = [];
            foreach ($brandResponse['data'] as $item) {
                array_push($brandsName, $item[ $field ]);
            }
            $brandsName = implode(',', $brandsName);
        }
        
        return $brandsName ?? '';
    }
    
    /**
     * 获取平台数据中的名称
     *
     * @param array|integer $platform
     *
     * @return string
     */
    public function convertPlatformName($platform)
    {
        $platformArray = [0 => 'web,wap,ios,android,pad', 1 => 'web', 2 => 'wap', 3 => 'ios', 4 => 'android', 5 => 'pad'];
        if (!empty($platform)) {
            if (is_array($platform)) {
                $platformName = [];
                foreach ($platform as $item) {
                    array_push($platformName, $platformArray[ intval($item) ]);
                }
                
                $platformName = implode(',', $platformName);
            } else {
                $platformName = !empty($platformArray[ $platform ]) ? $platformArray[ $platform ] : '';
            }
        }
        
        
        return $platformName ?? '';
    }
    
    /**
     * 检查配件与主商品的关系
     *
     * @param string $mainSku
     * @param array  $source
     * @param array  $parts
     *
     * @throws JsonResponseException
     */
    public function checkPartsGoodsConnection(string $mainSku, array $source, array $parts)
    {
        if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            $main = mb_substr($mainSku, 0, strpos($mainSku, '_'));
            if (empty($parts[ $main ])) {
                throw new Exception("商品 {$main} 没有配件", $this->skuCodeFail);
            }
            $sourceParts = array_map(function ($item) {
                return mb_substr($item, 0, strpos($item, '_'));
            }, $source);
            $currentParts = array_column($parts[ $main ], 'partsGoodSn');
            $diffSku = array_diff($sourceParts, $currentParts);
            if (!empty($diffSku)) {
                $errMsg = '商品 ' . implode(',', $diffSku) . ' 不是主商品 ' . $main . ' 的配件商品';
                throw new Exception($errMsg, $this->skuCodeFail);
            }
        }
    }
    
    /**
     * 检查输入SKU是否为单个SKU
     *
     * @param string $sku
     *
     * @throws JsonResponseException
     */
    protected function checkSingleGoodsSku($sku)
    {
        if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            if (false !== strpos($sku, SiteConstants::CHAR_COMMA)) {
                throw new JsonResponseException($this->codeFail, '商品SKU只能填写一个SKU');
            }
        }
    }
    
    /**
     * 系统coupon使用链接生成
     *
     * a)如果OBS后台有配置取后台配置的，取对应端的值 webLink、 wapLink、 androidLink、 iosLink、 padLink
     * b)后台对应没有配置的情况，如果是满减金额类型 且有适用sku的，使用url形式拼接，如下：
     * PC和M端拼接 /search-coupon?code=系统coupon码
     * APP端拼接 gearbest://couponTogether?code=系统coupon码
     * 如何判断APP客户端嵌套H5页面，type=app；进一步判断是安卓还是ios，platform=ios或android
     *
     * @param $params
     * @param $couponData
     *
     * @return string
     */
    public function getCouponUrlLink($params, $couponData)
    {
        switch ($params['platform']) {
            case 'pc':
                return !empty($couponData['webLink'])
                    ? $couponData['webLink']
                    : config("soa.gb.domain.main.{$params['platform']}.{$params['lang']}") . "/search-coupon?code={$couponData['templateCode']}";
                break;
            case 'wap':
                return !empty($couponData['wapLink'])
                    ? $couponData['wapLink']
                    : config("soa.gb.domain.main.{$params['platform']}.{$params['lang']}") . "/search-coupon?code={$couponData['templateCode']}";
                break;
            case 'app':
                return "gearbest://couponTogether?code={$couponData['templateCode']}";
                break;
        }
    }
    
    /**
     * 根据coupon类型显示优惠信息
     *
     * @param $coupon
     *
     * @return string
     */
    public function getCouponDesc($coupon)
    {
        switch ($coupon['type']) {
            //直减
            case 9:
            case 13:
                if (3 == $coupon['discountForm']) {
                    return sprintf('%s%OFF', (string) $coupon['strategys']);
                    
                } else {
                    return sprintf('\$%s', (string) $coupon['strategys']);
                }
                break;
            //满减
            case 8:
            case 12:
                $strategys = explode('-', $coupon['strategys']);
                //金额封顶 / 金额不封顶
                if (in_array($coupon['discountForm'], [1, 2])) {
                    $fullConditionStr = [1 => 'Over \$ %s save \$ %s', 2 => 'Over \$ %s pcs save \$ %s'];
                    
                    return sprintf($fullConditionStr[ $coupon['fullCondition'] ], (string) $strategys[0], (string) $strategys[1]);
                }
                //百分比减免
                if (3 == $coupon['discountForm']) {
                    $fullConditionStr = [1 => 'Over \$ %s save %s%', 2 => 'Over \$ %s pcs save %s%'];
                    
                    return sprintf($fullConditionStr[ $coupon['fullCondition'] ], (string) $strategys[0], (string) $strategys[1]);
                }
                break;
        }
    }
    
    /**
     * 拼接商详页链接
     *
     * @param array  $params
     * @param string $urlTitle
     *
     * @return string
     */
    public function getGoodsUrlTitle(array $params, string $urlTitle, string $whCode)
    {
        return config("sites.{$params['siteCode']}.domain.{$params['pipeline']}") . $urlTitle . "?wid={$whCode}";
    }
    
    /**
     * 拼接商品图片地址
     *
     * @param array  $params
     * @param string $imgUrl
     *
     * @return string
     */
    public function getGoodsImageUrl(array $params, $imgUrl)
    {
        return config("sites.{$params['siteCode']}.goodsImageUrl") . ($imgUrl ?? '');
    }
    
    /**
     * 取商品折扣
     *
     * @param $shopPrice 原价
     * @param $price     折扣价
     *
     * @return float
     */
    public function getGoodsDiscount($shopPrice, $price)
    {
        return ceil(round(($shopPrice - $price) / $shopPrice * 1, 2) * 100);
    }
    
    /**
     * 根据设备端取价格字段
     *
     * @param string $platform
     *
     * @return string
     */
    public function getPriceTypeField(string $platform)
    {
        return in_array($platform, ['pc', 'm', 'wap']) ? 'displayPrice' : 'appDisplayPrice';
    }
    
    /**
     * 检查商品是否为APP专享价
     *
     * @param array $goodsResult
     *
     * @throws Exception
     */
    public function checkAppPriceType(array &$goodsResult)
    {
        $noAppType = [];
        foreach ($goodsResult as $key => $goods) {
            $indexKey = "{$goods['goodsSn']}_{$goods['whCode']}";
            if (self::SOA_LABEL_ID_MOBILE != $goods['appPriceType']) {
                unset($goodsResult[ $key ]);
                array_push($noAppType, $indexKey);
            }
        }
        
        if (
            !empty($noAppType)
            && (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
                || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods'))
        ) {
            $errorMsg = sprintf('商品 %s 不存在/重复/非APP专享价商品', join(SiteConstants::CHAR_COMMA, $noAppType));
            throw new Exception($errorMsg, $this->appPriceTypeCodeFail);
        }
    }
    
    /**
     * coupon商品关联性校验
     *
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function checkCouponGoodsSku(array $params)
    {
        if (empty($params['componentData']['goodsComposite'])) {
            throw new Exception("组件数据为空，请输入SKU/Coupon信息", $this->codeFail);
        }
        $goodsCompositeList = $params['componentData']['goodsComposite'];
        $data = [];
        foreach ($goodsCompositeList as &$value) {
            $value['sku'] = !empty($value['sku']) ? explode(',', $value['sku']) : '';
            $value['coupons'] = !empty($value['coupons']) ? explode(',', $value['coupons']) : '';
            $this->checkCouponGoodsMatch($value['sku'], $value['coupons']);
            if (!empty($params['componentData']['maxCount'])) {
                $value['coupons'] = array_slice($value['coupons'], 0, $params['componentData']['maxCount'], true);
                $value['sku'] = array_slice($value['sku'], 0, $params['componentData']['maxCount'], true);
            }
            foreach ($value['coupons'] as $key => $coupon) {
                array_push($data, ['coupon' => $coupon, 'sku' => $value['sku'][ $key ]]);
            }
        }
        
        $data = array_chunk($data, 16, true);
        foreach ($data as $datum) {
            $apiBodyParams = [
                'type'       => 1,
                'pipeline'   => $params['pipeline'],
                'lang'       => $params['lang'],
                'couponList' => array_values(array_unique(array_column($datum, 'coupon')))
            ];
            $couponResponse = (new GbSoaServer())->findCouponInfoByCode($apiBodyParams);
            $warehouses = !empty($couponResponse['data']['list'])
                ? array_column($couponResponse['data']['list'], 'warehouses', 'couponCode')
                : [];
            $this->checkCouponWarehouses($datum, $warehouses);
            $goodsResponse = (new GbSoaServer())->getCouponGoodSnByCode($apiBodyParams);
            $goodsList = !empty($goodsResponse['data']['list'])
                ? array_column($goodsResponse['data']['list'], 'data', 'code')
                : [];
            $this->checkCouponGoods($datum, $goodsList);
        }
        
        return $goodsCompositeList;
    }
    
    /**
     * 校验coupon和对应的商品数量是否匹配
     *
     * @param $sku
     * @param $coupons
     *
     * @throws Exception
     */
    private function checkCouponGoodsMatch($sku, $coupons)
    {
        if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            if (empty($sku)) {
                throw new Exception("组件数据为空，请输入SKU/Coupon信息", $this->codeFail);
            }
            if (empty($coupons)) {
                throw new Exception("组件数据为空，请输入SKU/Coupon信息", $this->codeFail);
            }
            if (count($sku) > count($coupons)) {
                $errorTips = implode(',', array_slice($sku, count($coupons), count($sku)));
                throw new Exception("{$errorTips} 无对应的Counpon信息", $this->couponGoodsSkuFail);
            }
            if (count($sku) < count($coupons)) {
                $errorTips = implode(',', array_slice($coupons, count($sku), count($coupons)));
                throw new Exception("{$errorTips} 无对应的SKU信息", $this->couponExsitsCodeFail);
            }
        }
    }
    
    /**
     * 校验商品sku的仓库是都和coupon的一致
     *
     * @param array $formGoods
     * @param array $warehouses
     *
     * @throws Exception
     */
    private function checkCouponWarehouses(array $formGoods, array $warehouses)
    {
        if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            $tips = '';
            foreach ($formGoods as $value) {
                $couponWhCode = !empty($warehouses[ $value['coupon'] ]) ? $warehouses[ $value['coupon'] ] : [];
                $whCode = mb_substr($value['sku'], strpos($value['sku'], '_') + 1, strlen($value['sku']));
                if (!in_array($whCode, $couponWhCode)) {
                    $tips .= "{$value['coupon']}非商品 {$value['sku']} 的Coupon信息";
                }
            }
    
            if (!empty($tips)) {
                throw new Exception($tips, $this->couponGoodsSkuFail);
            }
        }
    }
    
    /**
     * 校验商品sku和coupon是否关联
     *
     * @param array $formGoods
     * @param array $couponGoods
     *
     * @throws Exception
     */
    private function checkCouponGoods(array $formGoods, array $couponGoods)
    {
        if (false !== stristr(app()->controller->getRoute(), 'ui-design/save-form')
            || false !== stristr(app()->controller->getRoute(), 'goods/tpl-goods')
        ) {
            $tips = '';
            foreach ($formGoods as $value) {
                if (empty($couponGoods[ $value['coupon'] ])) {
                    $tips .= "{$value['coupon']}非商品 {$value['sku']} 的coupon信息";
                } else {
                    $sku = mb_substr($value['sku'], 0, strpos($value['sku'], '_'));
                    if (!in_array($sku, $couponGoods[ $value['coupon'] ])) {
                        $tips .= "{$value['coupon']}非商品 {$value['sku']} 的coupon信息";
                    }
                }
            }
    
            if (!empty($tips)) {
                throw new Exception($tips, $this->couponGoodsSkuFail);
            }
        }
        
    }
}
