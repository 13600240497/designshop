<?php
/**
 * SOA服务配置 - 测试环境
 */

return [
    /************************************* 商品运营平台 ***********************************/
    'sop' => [
        'api' => [
            'host' => 'http://sop.glosop.com.master.php7.egomsl.com',
            'secretKey' => 'qc3QPHx9OmNn4kId',
        ],
        'addRuleUrl' => 'http://sop.glosop.com.master.php7.egomsl.com/#/shop/rule/add'
    ],

    /************************************* 选品系统配置 ***********************************/
    'ips' => [
        'host' => "http://ips.hqygou.com",
        'apiUrlPrefix' => 'http://ips.hqygou.com/api',
        'key'          => 'Ti371Gu0jMUwNvyQV9ztgo2OaDd5eCJF',
        'sn'           => 'geshop'
    ],
    
    /************************************* 人工智能系统配置 ***********************************/
    'ai'  => [
        'apiGateway' => 'http://10.60.49.140/dy/gw', //API网关
    ],
    
    /************************************* 电子SOA系统配置 ***********************************/
    'gb'  => [
        // SOA环境相关设置
        'env'                      => [
            'gateway' => 'http://10.40.2.109:2087/gateway',
            'tokenId' => '9c17830f2e3c20e61948653d0697be8f',
            'version' => '1.0.0',
            'type'    => 1
        ],
        
        /*-- GB站点UI组件商品信息SOA方法对应表 --*/
        'uiGoodsInfoMethodMapping' => [
            'U000091' => 'getGeneralGoodsList', //商品列表(GB)	桌面
            'U000092' => 'getGeneralGoodsList', //商品列表(GB)	移动
            'U000093' => 'getRushBuyGoodsList', //商品抢购(GB)	桌面
            'U000094' => 'getRushBuyGoodsList', //商品抢购(GB)	移动
            'U000095' => 'getManyGoodsList', //多商品tab(GB)	桌面
            'U000096' => 'getManyGoodsList', //多商品tab(GB)	移动
            'U000097' => 'getManyRushBuyGoodsList', //多时段抢购(GB)	桌面
            'U000098' => 'getManyRushBuyGoodsList', //多时段抢购(GB)	移动
            'U000099' => 'getMarkUpGoodsList', //加价购(GB)	桌面
            'U000100' => 'getMarkUpGoodsList', //加价购(GB)	移动
            'U000101' => 'getSingleRecommendGoodsList', //单品推荐(GB)	桌面
            'U000102' => 'getSingleRecommendGoodsList', //单品推荐(GB)	移动
            'U000103' => 'getDoubleRecommendGoodsList', //双品推荐(GB)	桌面
            'U000104' => 'getDoubleRecommendGoodsList', //双品推荐(GB)	移动
            'U000105' => 'getCouponGoodsList', //促销码(GB)	桌面
            'U000106' => 'getCouponGoodsList', //促销码(GB)	移动
            'U000107' => 'getAppSpecialPriceGoodsList', //APP专享价(GB)	桌面
            'U000139' => 'getPartsGoodsList', //搭售商品 PC
            'U000140' => 'getPartsGoodsList', //搭售商品 WAP
            'U000137' => 'getSystemCouponList', //优惠券
            'U000148' => 'getSystemCouponList', //优惠券
            'U000123' => 'getGeneralGoodsList', //单个推荐(GB) pc
            'U000124' => 'getGeneralGoodsList', //单个推荐(GB) m
            'U000158' => 'getGeneralGoodsList', //普通商品列表组件-M	移动
            'U000159' => 'getManyGoodsList', //动态图片商品列表-多商品推荐（PC）	桌面
            'U000160' => 'getManyGoodsList', //动态图片商品列表-多商品推荐（M）	移动
            'U000175' => 'getComposeGiftGoodsList', //M元Y件（PC）
            'U000176' => 'getComposeGiftGoodsList', //M元Y件（PC）
            'U000194' => 'getManyGoodsList' //品牌组件(PC)
        ],
        
        /*-- SOA 服务名按模块汇总 --*/
        'servers'                  => [
            
            // 商品模块
            'goods'     => [
                'IGoodsServer'     => [
                    'soaServer' => 'com.globalegrow.spi.goods.common.inter.IGoodsService',
                    'soaMethod' => [
                        'goodsBaseInfo'      => 'querySimpleGoodsInfoForGEShop'
                    ]
                ],
                'IGoodBrandServer' => [
                    'soaServer' => 'com.globalegrow.goods.spi.inter.IGoodBrandService',
                    'soaMethod' => [
                        'getBrandInfo' => 'getBrandInfoList'
                    ]
                ],
                'ICategoryServer'  => [
                    'soaServer' => 'com.globalegrow.goods.spi.inter.ICategoryService',
                    'soaMethod' => [
                        'categoryIdByGoodSn' => 'getCategoryIdByGoodSn'
                    ]
                ]
            ],
            
            // 营销模块
            'promotion' => [
                'CouponApiServer'   => [
                    'soaServer' => 'com.globalegrow.spi.promotion.common.inter.CouponApi',
                    'soaMethod' => [
                        'shopSkuCoupon'     => 'getSystemCouponByShopWcodeSku',
                        'getCouponTemplate' => 'getCouponTemplate',
                        'getCouponGoodSn'   => 'getCouponGoodSnByCode'
                    ]
                ],
                'PriceApiServer'    => [
                    'soaServer' => 'com.globalegrow.spi.promotion.common.inter.PriceApi',
                    'soaMethod' => [
                        'priceList'          => 'getPriceList',
                        'specifiedPriceList' => 'getSpecifiedPriceList'
                    ]
                ],
                'StockQueryServer'  => [
                    'soaServer' => 'com.globalegrow.spi.stock.inter.IStockQueryService',
                    'soaMethod' => [
                        'stockOfVirWh' => 'queryGoodsStockOfVirWh'
                    ]
                ],
                'ActivityApiServer' => [
                    'soaServer' => 'com.globalegrow.spi.promotion.common.inter.ActivityApi',
                    'soaMethod' => [
                        'goodsActivity'   => 'getGoodActivity',
                        'composeGiftGood' => 'getComposeGiftGood'
                    ]
                ],
                'PartsApiServer'    => [
                    'soaServer' => 'com.globalegrow.spi.promotion.common.inter.PartsApi',
                    'soaMethod' => [
                        'getPartsInfo'  => 'getPartsInfo',
                        'getPartsPrice' => 'getPartsPrice'
                    ]
                ]
            ],
            
            // 库存模块
            'stock'     => [
                'WarehouseServer' => [
                    'soaServer' => 'com.globalegrow.spi.stock.inter.IVirtualWarehouseService',
                    'soaMethod' => [
                        'siteVirWarehouseList' => 'getSiteVirWarehouseList'
                    ]
                ]
            ]
        ]
    ],
    
    /************************************* obs系统配置 ********************************/
    'obs' => [
        'apiUrlPrefix' => 'http://www.obs-gb.com/api/geshop',
    ],
];
