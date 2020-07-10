<?php
/**
 * 站点公用接口配置
 * 注：配置的url均为绝对路径的，具体的前缀host取自于domain配置文件中的domain项
 */
$interfaceConfig = [
    'common_verify' => [
        'url' => '/geshop/common/verify',
        'method' => 'post',
        'description' => '统一校验接口',
        'isJsonp' => 0
    ],
    'community' => [
        'url' => '/api/get_geshop_api.php?act=Community',
        'method' => 'get',
        'description' => '社区接口（当前仅支持zaful站点）',
        'isJsonp'     => 0
    ],
    'getdetail'          => [
        'url'         => '/geshop/goods/getdetail',
        'method'      => 'post',
        'description' => '商品详情',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"267585805,266617002,266617005"}'
        ],
        'isJsonp'     => 1
    ],
    'tempgetdetail'      => [
        'url'         => '/geshop/goods/tempdetail',
        'method'      => 'post',
        'description' => '商品详情',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"267585805,266617002,266617005"}'
        ],
        'isJsonp'     => 0
    ],
    'goods_async_detail' => [
        'url'         => '/geshop/goods/async-detail',
        'method'      => 'get',
        'description' => '异步商品详情接口',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"267585805,266617002,266617005"}'
        ],
        'isJsonp'     => 1
    ],
    'fullgiftlistverify' => [
        'url'         => '/geshop/goods/fullgiftverify',
        'method'      => 'get',
        'description' => '搭配购-SKU列表',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"267585805,266617002,266617005"}'
        ],
        'isJsonp'     => 1
    ],
    'isseckill'          => [
        'url'         => '/geshop/goods/isseckill',
        'method'      => 'post',
        'description' => '是否秒杀商品',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"267585805,266617002,266617005"}'
        ],
        'isJsonp'     => 1
    ],
    'getrankdetail'      => [
        'url'         => '/geshop/goods/getrankdetail',
        'method'      => 'get',
        'description' => '排行榜商品列表',
        'example'     => [
            'content' => '{"type":1,"lang":"en","cateid":"2","pageno":1,"pagesize":20}'
        ],
        'isJsonp'     => 1
    ],
    'timeseckilldetail'  => [
        'url'         => '/geshop/goods/timeseckilldetail',
        'method'      => 'get',
        'description' => '秒杀商品列表（根据SKU）',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"168824901,197567301,268015810"}'
        ],
        'isJsonp'     => 1
    ],
    'timesecidlist'      => [
        'url'         => '/geshop/goods/timesecidlist',
        'method'      => 'get',
        'description' => '秒杀商品列表（根据秒杀ID）[还未开发]',
        'example'     => [
            'content' => '{"lang":"en","activityid":"12"}'
        ]
    ],
    'fullgiftlist'       => [
        'url'         => '/geshop/goods/fullgiftlist',
        'method'      => 'get',
        'description' => '满赠商品列表【满赠系列接口1】',
        'example'     => [
            'content' => '{"lang":"en","activityid":"147","pageno":1,"pagesize":20}'
        ],
        'isJsonp'     => 1
    ],
    'getlistinspu'       => [
        'url'         => '/geshop/goods/getlistinspu',
        'method'      => 'get',
        'description' => '获取同SPU下商品列表【满赠系列接口2】',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"201484903"}'
        ],
        'isJsonp'     => 1
    ],
    'addgifttocart'      => [
        'url'         => '/geshop/goods/addgifttocart',
        'method'      => 'get',
        'description' => '满赠商品加入购物车【满赠系列接口3】',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"201484903","manzeng_id":123}'
        ],
        'isJsonp'     => 1
    ],
    'increasebuylist'    => [
        'url'         => '/geshop/goods/increasebuylist',
        'method'      => 'get',
        'description' => '加价购商品列表',
        'example'     => [
            'content' => '{"lang":"en","activityid":"147","pageno":1,"pagesize":20}'
        ],
        'isJsonp'     => 1
    ],
    'getcoupon'          => [
        'url'         => '/geshop/goods/getcoupon',
        'method'      => 'get',
        'description' => '优惠券领取',
        'example'     => [
            'content' => '{"lang":"en","couponid":"123"}'
        ],
        'isJsonp'     => 1
    ],
    'coupondetail'       => [
        'url'         => '/geshop/goods/coupondetail',
        'method'      => 'get',
        'description' => '优惠券详情',
        'example'     => [
            'content' => '{"lang":"en","couponid":"123"}'
        ],
        'isJsonp'     => 1
    ],
    'recommendlist'      => [
        'url'         => '/geshop/goods/recommendlist',
        'method'      => 'get',
        'description' => '智能推荐商品列表（无分类ID的）',
        'example'     => [
            'content' => '{"lang":"en"}'
        ],
        'isJsonp'     => 1
    ],
    'prepromotion'       => [
        'url'         => '/geshop/goods/prepromotion',
        'method'      => 'get',
        'description' => '预促销商品列表',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"195444002,195444001,195530602,195530601,2021393110,2021393104,2021393102"}'
        ],
        'isJsonp'     => 1
    ],
    'redeemlist'         => [
        'url'         => '/geshop/goods/redeemlist',
        'method'      => 'get',
        'description' => '积分兑换商品列表',
        'example'     => [
            'content' => '{"lang":"en","goodsSn":"201484903,201485603,257064502"}'
        ],
        'isJsonp'     => 1
    ],
    'spikegoods'         => [
        'url'         => '/geshop/interface/spike-goods/',
        'method'      => 'get',
        'description' => '秒杀列表',
        'dataKey'     => 'spike-goods',
        'isJsonp'     => 1
    ],
    'couponlist'         => [
        'url'         => '/geshop/goods/couponlist',
        'method'      => 'get',
        'description' => '优惠券列表',
        'dataKey'     => 'couponInfo',
        'isJsonp'     => 1
    ],

    'goods_combogoods'   => [
        'url'          => '/geshop/goods/combogoods',
        'method'       => 'get',
        'description'  => '搭配购-SKU列表',
        'isJsonp'      => 1,
        'support_site' => ['rg', 'zf']
    ],
    'goods_comboprice'   => [
        'url'          => '/geshop/goods/comboprice',
        'method'       => 'get',
        'description'  => '搭配购-获取组合价',
        'isJsonp'      => 1,
        'support_site' => ['rg', 'zf']
    ],
    'goods_comboaddcart' => [
        'url'          => '/geshop/goods/comboaddcart',
        'method'       => 'get',
        'description'  => '搭配购-加入购物车',
        'isJsonp'      => 1,
        'support_site' => ['rg', 'zf']
    ],

    'elf_webgame_do_lottery'   => [
        'url'          => '/api/webgame-api/do-lottery',
        'method'       => 'post',
        'description'  => '抽奖接口',
        'isJsonp'      => 1,
        'support_site' => [
            'rg' => [
                'test'    => 'http://www.elf.com.0514-game.php5.egomsl.com',
                'product' => 'https://activity.rosegal.com'
            ],
            'zf' => [
                'test'    => 'http://www.elf.com.0514-game.php5.egomsl.com',
                'product' => 'https://activity.zaful.com'
            ],
            'dl' => [
                'test'    => 'http://www.elf.com.release.php5.egomsl.com',
                'product' => 'https://activity.dresslily.com'
            ]
        ]
    ],
    'elf_webgame_get_activity' => [
        'url'          => '/api/webgame-api/get-activity',
        'method'       => 'post',
        'description'  => '获取活动信息',
        'isJsonp'      => 1,
        'support_site' => [
            'rg' => [
                'test'    => 'http://www.elf.com.webgame.php5.egomsl.com',
                'product' => 'https://activity.rosegal.com'
            ],
            'zf' => [
                'test'    => 'http://www.elf.com.webgame.php5.egomsl.com',
                'product' => 'https://activity.zaful.com'
            ],
            'dl' => [
                'test'    => 'http://www.elf.com.release.php5.egomsl.com',
                'product' => 'https://activity.dresslily.com'
            ]
        ]
    ],
    'elf_webgame_share_prize'  => [
        'url'          => '/api/webgame-api/share-prize',
        'method'       => 'post',
        'description'  => '分享赠送抽奖次数',
        'isJsonp'      => 1,
        'support_site' => [
            'rg' => [
                'test'    => 'http://www.elf.com.webgame.php5.egomsl.com',
                'product' => 'https://activity.rosegal.com'
            ],
            'zf' => [
                'test'    => 'http://www.elf.com.0514-game.php5.egomsl.com',
                'product' => 'https://activity.zaful.com'
            ],
            'dl' => [
                'test'    => 'http://www.elf.com.release.php5.egomsl.com',
                'product' => 'https://activity.dresslily.com'
            ]
        ]
    ],
    'elf_webgame_info'         => [
        'url'          => '/api/webgame-api/info',
        'method'       => 'post',
        'description'  => '获取抽奖活动数据',
        'isJsonp'      => 1,
        'support_site' => [
            'rg' => [
                'test'    => 'http://www.elf.com.0514-game.php5.egomsl.com',
                'product' => 'https://activity.rosegal.com'
            ],
            'zf' => [
                'test'    => 'http://www.elf.com.0514-game.php5.egomsl.com',
                'product' => 'https://activity.zaful.com'
            ],
            'dl' => [
                'test'    => 'http://www.elf.com.release.php5.egomsl.com',
                'product' => 'https://activity.dresslily.com'
            ]
        ]
    ],

    'user_gettotalconsumptionlist'   => [
        'url'          => '/geshop/user/gettotalconsumptionlist',
        'method'       => 'get',
        'description'  => '高额打榜-榜单列表',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'user_gettotalconsumptiondetail' => [
        'url'          => '/geshop/user/gettotalconsumptiondetail',
        'method'       => 'get',
        'description'  => '高额打榜-详情',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'user_userSubscribe'             => [
        'url'          => '/geshop/user/userSubscribe',
        'method'       => 'get',
        'description'  => '用户订阅',
        'isJsonp'      => 1,
        'support_site' => ['rg', 'suk']
    ],
    'user_activityBook'              => [
        'url'          => '/geshop/activity/book',
        'method'       => 'get',
        'description'  => '预约提醒',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'user_activityBookList'          => [
        'url'          => '/geshop/activity/list',
        'method'       => 'get',
        'description'  => '预约PID信息',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'activity_sendsms'               => [
        'url'          => '/geshop/activity/sendsms',
        'method'       => 'post',
        'description'  => '短信',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'base_verify'                    => [
        'url'          => '/fun/?act=verify',
        'method'       => 'get',
        'description'  => '验证码',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'cmsgoods'                       => [
        'url'          => '/cms/goods/goods_list',
        'method'       => 'post',
        'description'  => '发现好货',
        'isJsonp'      => 1,
        'support_site' => ['zf', 'rg']
    ],
    'cmsgoods_pointsProductDetail'   => [
        'url'          => '/geshop/activity/pointsProductDetail',
        'method'       => 'get',
        'description'  => '积分兑换活动详情',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'activity_exchangePointsProduct' => [
        'url'          => '/geshop/activity/exchangePointsProduct',
        'method'       => 'get',
        'description'  => '积分兑换',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'getrankvalid'                   => [
        'url'          => '/geshop/goods/getrankvalid',
        'method'       => 'get',
        'description'  => '排行榜分类ID校验',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'goods_samelist'                 => [
        'url'          => '/geshop/goods/samelist',
        'method'       => 'get',
        'description'  => 'Table商品列表',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'goods_verifycombo'              => [
        'url'          => '/geshop/goods/verifycombo',
        'method'       => 'get',
        'description'  => '搭配购校验接口',
        'isJsonp'      => 1,
        'support_site' => ['dl']
    ],
    'goods_comblist'                 => [
        'url'          => '/geshop/goods/comblist',
        'method'       => 'get',
        'description'  => '搭配购',
        'isJsonp'      => 1,
        'support_site' => ['dl']
    ],
    'goods_couponlist_new'           => [
        'url'          => '/geshop/goods/couponlist_new',
        'method'       => 'get',
        'description'  => '新优惠码接口',
        'isJsonp'      => 1,
        'support_site' => ['zf']
    ],
    'goods_samelistCatId'            => [
        'url'          => '/geshop/goods/samelistCatId',
        'method'       => 'get',
        'description'  => 'tab商品列表',
        'isJsonp'      => 1,
        'support_site' => ['rg']
    ],
    'goods_receiveCoupon'            => [
        'url'          => '/geshop/goods/receiveCoupon',
        'method'       => 'get',
        'description'  => '领取coupon接口',
        'isJsonp'      => 1,
    ],
    'goods_getCouponInfo'            => [
        'url'          => '/geshop/goods/getCouponInfo',
        'method'       => 'get',
        'description'  => '获取coupon接口',
        'isJsonp'      => 1,
    ],
    'goods_goodsTabList'            => [
        'url'          => '/geshop/goods/goodsTabList',
        'method'       => 'get',
        'description'  => '商品列表Tab',
        'isJsonp'      => 1,
        'support_site' => ['dl']
    ],
    'user_info'            => [
        'url'          => '/geshop/user/info',
        'method'       => 'get',
        'description'  => '用户信息接口',
        'isJsonp'      => 1,
    ],
    'goods_delCache'  => [
        'url'          => '/geshop/goods/delCache',
        'method'       => 'post',
        'description'  => '清除站点组件商品信息缓存',
        'isJsonp'      => 0,
        'support_site' => ['dl']
    ],
    'goods_recommendlistadvanced' => [
      'url'          => '/geshop/goods/recommendlistadvanced',
      'method'       => 'get',
      'description'  => '智能推荐多分类id',
      'isJsonp'      => 1,
      'support_site' => ['rg']
    ],
    'goods_samelistCatIdMultiple' => [
      'url'          => '/geshop/goods/samelistCatIdMultiple',
      'method'       => 'get',
      'description'  => '新多商品分类id同款商品',
      'isJsonp'      => 1,
      'support_site' => ['rg']
    ],
    'goods_samelistCatIdMultiple' => [
      'url'          => '/geshop/goods/samelistCatIdMultiple',
      'method'       => 'get',
      'description'  => '新多商品分类id同款商品',
      'isJsonp'      => 1,
      'support_site' => ['rg']
    ],
    'goods_getrankdetail_new' => [
        'url'          => '/geshop/goods/newRankDetail',
        'method'       => 'get',
        'description'  => 'D网新排行榜',
        'isJsonp'      => 1,
        'support_site' => ['dl']
    ],

    'geshopApi_design_asyncInfo' => [
        'url'          => '/geshop/design/asyncInfo',
        'method'       => 'post',
        'description'  => '装修页面和预览页面获取数据异步数据接口',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'http://api.hqgeshop.com'
            ]
        ]
    ],
    'geshopApi_design_goodsInfo' => [
        'url'          => '/geshop/design/goodsInfo',
        'method'       => 'post',
        'description'  => '装修页面配置SKU',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'http://api.hqgeshop.com'
            ]
        ]
    ],
    'geshopApi_page_asyncInfo' => [
        'url'          => '/activity/page/asyncInfo',
        'method'       => 'post',
        'description'  => '发布页面获取数据异步数据接口',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'geshopApi_page_fallback' => [
        'url'          => '/geshop/design/fallback',
        'method'       => 'post',
        'description'  => '发布页面获取兜底数据',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'gesApi_design_esSearchSortByList' => [
        'url'          => '/geshop/design/esSearchSortByList',
        'method'       => 'get',
        'description'  => '获取站点ES搜索支持排序列表',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'gesApi_pc_goods_getSopGoodsDetail' => [
        'url'          => '/web/pc/goods/getSopGoodsDetail',
        'method'       => 'get',
        'description'  => 'PC端获取商品运营平台商品列表',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'gesApi_m_goods_getSopGoodsDetail' => [
        'url'          => '/web/m/goods/getSopGoodsDetail',
        'method'       => 'get',
        'description'  => 'M端获取商品运营平台商品列表',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'gesApi_pc_goods_getAutoRefreshUiGoodsList' => [
        'url'          => '/web/pc/goods/getAutoRefreshUiGoodsList',
        'method'       => 'get',
        'description'  => 'PC端获取商品实时价格',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ],
            'dl' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ],
    'gesApi_m_goods_getAutoRefreshUiGoodsList' => [
        'url'          => '/web/m/goods/getAutoRefreshUiGoodsList',
        'method'       => 'get',
        'description'  => 'M端获取商品实时价格',
        'isJsonp'      => 1,
        'support_site' => [
            'zf' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ],
            'dl' => [
                'test'    => 'http://test.geshop-api.com.release_sop.php7.egomsl.com',
                'product' => 'https://api.hqgeshop.com'
            ]
        ]
    ]


];

// 补全站点key和url的前缀host
$siteDomainConfig = require __DIR__ . '/../domain/domain.' . YII_ENV . '.php';
$publicInterfaces = [];
if (!empty($siteDomainConfig)) {
    $_envAlias = ['dev' => 'test', 'prerelease' => 'product'];

    /** @var array $siteDomainConfig */
    foreach ($siteDomainConfig as $key => $val) {
        if (false === stristr($val['name'], 'gearbest')) {
            $_domain = in_array(YII_ENV, ['dev', 'test'], true) ? $val['developdomain'] : $val['domain'];
            foreach ($interfaceConfig as $k => $v) {
                $websiteCode = explode('-', $key)[0];
                if (isset($v['support_site'])) {
                    if (isset($v['support_site'][ $websiteCode ])) {
                        $_env = $_envAlias[ YII_ENV ] ?? YII_ENV;
                        if (isset($v['support_site'][ $websiteCode ][ $_env ])) {
                            $v['url'] = $v['support_site'][ $websiteCode ][ $_env ] . $v['url'];
                        } else {
                            $v['url'] = $_domain . $v['url'];
                        }
                    } else {
                        if (in_array($websiteCode, $v['support_site'])) {
                            $v['url'] = $_domain . $v['url'];
                        } else {
                            $v['url'] = null;
                        }
                    }

                    unset($v['support_site']);
                    !empty($v['url']) && $publicInterfaces[ $key ][ $k ] = $v;
                } else {
                    $v['url'] = $_domain . $v['url'];
                    $publicInterfaces[ $key ][ $k ] = $v;
                }

            }
        }
    }
}

return $publicInterfaces;
