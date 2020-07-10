<?php
namespace app\modules\component\gb\servers;

use app\services\soa\GearbestService as SoaServer;

/**
 * Gearbest站点Soa接口服务调用
 *
 * Class GearbestSoaServer
 *
 * @package app\modules\soa\servers
 */
class GearbestSoaServer extends SoaServer
{
    
    /**
     * 批量获取商品基础数据
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=82510934
     *
     * @return mixed
     */
    public function getGoodsBaseInfo(array $data)
    {
        return $this->setServer(config('soa.gb.servers.goods.IGoodsServer.soaServer'))
            ->setMethod(config('soa.gb.servers.goods.IGoodsServer.soaMethod.goodsBaseInfo'))
            ->soaCall($data);
    }
    
    /**
     * 获取店铺系统coupon
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=14322447
     *
     * @return mixed
     */
    public function getShopSkuCoupon(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.CouponApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.CouponApiServer.soaMethod.shopSkuCoupon'))
            ->soaCall($data);
    }

    /**
     * coupon - 根据coupon码获取coupon信息
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=27690912
     * @param array $data
     * @return mixed
     */
    public function findCouponInfoByCode(array $data)
    {
        return $this->setServer('com.globalegrow.spi.promotion.common.inter.CouponApi')
            ->setMethod('findCouponInfoByCode')
            ->soaCall($data);
    }

    /**
     * 批量查询商品价格
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=10814796
     *
     * @return mixed
     */
    public function getPriceList(array $data)
    {
        if (!empty($data['requestList']) && is_array($data['requestList'])) {
            foreach ($data['requestList'] as &$list) {
                $list['siteCode'] = (string) config('soa.gb.siteCode');
            }
        }
        
        return $this->setServer(config('soa.gb.servers.promotion.PriceApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.PriceApiServer.soaMethod.priceList'))
            ->soaCall($data);
    }
    
    /**
     * 批量查询预售商品实时价格
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=26577253
     *
     * @return mixed
     */
    public function getSpecifiedPriceList(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.PriceApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.PriceApiServer.soaMethod.specifiedPriceList'))
            ->soaCall($data);
    }
    
    /**
     * 查询虚拟仓商品库存
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=13769508
     *
     * @return mixed
     */
    public function getGoodsStockOfVirWh(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.StockQueryServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.StockQueryServer.soaMethod.stockOfVirWh'))
            ->soaCall($data);
    }
    
    /**
     * 获取商品活动(单个商品)
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=12617805
     *
     * @return mixed
     */
    public function getGoodsActivity(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.ActivityApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.ActivityApiServer.soaMethod.goodsActivity'))
            ->soaCall($data);
    }
    
    /**
     * 获取活动凑单商品和赠品信息
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=19837101
     *
     * @return mixed
     */
    public function getComposeGiftGood(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.ActivityApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.ActivityApiServer.soaMethod.composeGiftGood'))
            ->soaCall($data);
    }
    
    /**
     * 根据系统code获取系统coupon信息
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=27690932
     *
     * @return mixed
     */
    public function getCouponTemplate(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.CouponApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.CouponApiServer.soaMethod.getCouponTemplate'))
            ->soaCall($data);
    }
    
    /**
     * 获取指定配件价格信息
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=27690897
     *
     * @return mixed
     */
    public function getPartsPrice(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.PartsApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.PartsApiServer.soaMethod.getPartsPrice'))
            ->soaCall($data);
    }
    
    /**
     * 获取配件列表信息
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=12616240
     *
     * @return mixed
     */
    public function getPartsInfo(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.PartsApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.PartsApiServer.soaMethod.getPartsInfo'))
            ->soaCall($data);
    }
    
    /**
     * 查询站点虚拟仓列表
     *
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=27102833
     * @return mixed
     */
    public function getSiteVirWarehouseList(array $data)
    {
        return $this->setServer(config('soa.gb.servers.stock.WarehouseServer.soaServer'))
            ->setMethod(config('soa.gb.servers.stock.WarehouseServer.soaMethod.siteVirWarehouseList'))
            ->soaCall($data);
        
    }
    
    /**
     * 获取品牌信息
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=19837258
     *
     * @return mixed
     */
    public function getBrandList(array $data)
    {
        return $this->setServer(config('soa.gb.servers.goods.IGoodBrandServer.soaServer'))
            ->setMethod(config('soa.gb.servers.goods.IGoodBrandServer.soaMethod.getBrandInfo'))
            ->soaCall($data);
    }
    
    /**
     * 查询商品的分类id
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=35784904
     *
     * @return mixed
     */
    public function getCategoryIdBySku(array $data)
    {
        return $this->setServer(config('soa.gb.servers.goods.ICategoryServer.soaServer'))
            ->setMethod(config('soa.gb.servers.goods.ICategoryServer.soaMethod.categoryIdByGoodSn'))
            ->soaCall($data);
    }
    
    /**
     * 获取coupon可用的商品
     *
     * @param array $data
     * wiki：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=69730799
     *
     * @return mixed
     */
    public function getCouponGoodSnByCode(array $data)
    {
        return $this->setServer(config('soa.gb.servers.promotion.CouponApiServer.soaServer'))
            ->setMethod(config('soa.gb.servers.promotion.CouponApiServer.soaMethod.getCouponGoodSn'))
            ->soaCall($data);
    }
}
