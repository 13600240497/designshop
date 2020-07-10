<?php
/**
 * 站点测试环境接口地址配置
 */
$_site_interface_config = [
	/****************************************测试站点配置START*****************************************/
	'test-pc'    => [
		'goods'                   => [//商品详情接口地址
		                              'url'         => '/m-interface-a-getGoodsDetail.html',//接口路由
		                              'method'      => 'post',
		                              'description' => '商品详情（旧）',
		                              'dataKey'     => 'goodsInfo'//结果数据索引
		],
		'headFooterMonitorDomain' => [
			'home'     => [//站点首页模板地址
			               'en' => 'http://www.pc-rosewholesale' . TEST_DOMAIN . '/index.php?m=index&my=1',
			               'es' => 'http://es.pc-rosewholesale' . TEST_DOMAIN . '/index.php?m=index&my=1'
			],
			'activity' => [//站点活动页模板地址
			               'en' => 'http://www.pc-rosewholesale' . TEST_DOMAIN . '/geshop-activity.html',
			               'es' => 'http://es.pc-rosewholesale.' . TEST_DOMAIN . '/geshop-activity.html'
			]
		],
	],
	'test-wap'   => [
		'goods'                   => [
			'url'         => '/geshop/interface/get-goods-detail/',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo'
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/index.php?m=index&my=1'
			],
			'activity' => [
				'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/geshop-activity.html'
			]
		],
	],
	'test-app'   => [
		'goods'                   => [
			'url'         => '/geshop/interface/get-goods-detail/',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo',
			'link'        => 'rosewholesale://product?goods_id=%s&source='
		],
		'headFooterMonitorDomain' => [
			'activity' => [
				'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/geshop-activity-app.html'
			]
		],
	],
	/****************************************测试站点配置END*******************************************/
	'rw-pc'      => [
		'goods'                   => [
			'url'         => '/m-interface-a-getGoodsDetail.html',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo'
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'en' => 'http://www.pc-rosewholesale' . RW_DOMAIN . '/index.php?m=index&my=1',
				'es' => 'http://es.pc-rosewholesale' . RW_DOMAIN . '/index.php?m=index&my=1'
			],
			'activity' => [
				'en' => 'http://www.pc-rosewholesale' . RW_DOMAIN . '/geshop-activity.html',
				'es' => 'http://es.pc-rosewholesale' . RW_DOMAIN . '/geshop-activity.html'
			]
		],
	],
	'rw-wap'     => [
		'goods'                   => [
			'url'         => '/geshop/interface/get-goods-detail/',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo'
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/index.php?m=index&my=1'
			],
			'activity' => [
				'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/geshop-activity.html'
			]
		],
	],
	'rw-app'     => [
		'goods'                   => [
			'url'         => '/geshop/interface/get-goods-detail/',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo',
			'link'        => 'rosewholesale://product?goods_id=%s&source='
		],
		'headFooterMonitorDomain' => [
			'activity' => [
				'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/geshop-activity-app.html'
			]
		],
	],
	'rg-pc'      => [
		'goods'                   => [
			'url'         => '/m-interface-a-getGoodsDetail.html',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo'
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'RG' => [
						'en' => 'http://www.pc-rosegal' . RG_DOMAIN . '/index.php?m=index&my=1',
					],
				'RGFR' => [
						'fr' => 'http://fr.pc-rosegal' . RG_DOMAIN . '/index.php?m=index&my=1',
					],
				'RGRU' => [
						'ru' => 'http://ru.pc-rosegal' . RG_DOMAIN . '/index.php?m=index&my=1',
					]
			],
			'activity' => [
				'RG' => [
						'en' => 'http://www.pc-rosegal' . RG_DOMAIN . '/geshop-activity.html',
					],
				'RGFR' => [
						'fr' => 'http://fr.pc-rosegal' . RG_DOMAIN . '/geshop-activity.html',
					],
				'RGRU' => [
						'ru' => 'http://ru.pc-rosegal' . RG_DOMAIN . '/geshop-activity.html',
					]
			]
		],
		'advertisement-push'      => [
			'url'         => 'http://www.pc-rosegal.com.geshop_1022.php5.egomsl.com/geshop/goods/traitSinglePageSku',
			'method'      => 'post',
			'description' => '推送推广落地页到网站',
		],
		'advertisement-get-url'   => [
			'url'         => 'http://www.pc-rosegal.com.geshop_1022.php5.egomsl.com/geshop/goods/generateSinglePageUrl',
			'method'      => 'post',
			'description' => '获取推广落地页链接',
		],
		'activitysigninfo'        => [
			'url'         => 'http://www.pc-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/get_sign_info',
			'method'      => 'post',
			'description' => '获取活动签到积分规则',
			'isJsonp'     => 1
		],
		'activitydosign'          => [
			'url'         => 'http://www.pc-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/do_sign_in',
			'method'      => 'post',
			'description' => '活动签到接口',
			'isJsonp'     => 1
		]
	],
	'rg-wap'     => [
		'goods'                   => [
			'url'         => '/m-interface-a-getGoodsDetail.html',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo'
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'RG' => [
						'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/index.php',
					],
				'RGFR' => [
						'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/index.php',
					],
				'RGES' => [
						'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/index.php'
					]
			],
			'activity' => [
				'RG' => [
						'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/geshop-activity.html',
					],
				'RGFR' => [
						'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/geshop-activity.html',
					],
				'RGES' => [
						'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/geshop-activity.html'
					]
			]
		],
		'advertisement-push'      => [
			'url'         => 'http://www.pc-rosegal.com.geshop_1022.php5.egomsl.com/geshop/goods/traitSinglePageSku',
			'method'      => 'post',
			'description' => '推送推广落地页到网站',
		],
		'advertisement-get-url'   => [
			'url'         => 'http://www.pc-rosegal.com.geshop_1022.php5.egomsl.com/geshop/goods/generateSinglePageUrl',
			'method'      => 'post',
			'description' => '获取推广落地页链接',
		],
		'activitysigninfo'        => [
			'url'         => 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/get_sign_info',
			'method'      => 'post',
			'description' => '获取活动签到积分规则',
			'isJsonp'     => 1
		],
		'activitydosign'          => [
			'url'         => 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/do_sign_in',
			'method'      => 'post',
			'description' => '活动签到接口',
			'isJsonp'     => 1
		]
	],
	'rg-app'     => [
		'goods'                   => [
			'url'         => '/m-interface-a-getGoodsDetail.html',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goodsInfo',
			'link'        => 'rosegal://product?goods_id=%s&source='
		],
		'headFooterMonitorDomain' => [
			'activity' => [
				'RG' => [
					'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/geshop-activity-app.html',
					],
				'RGFR' => [
					'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/geshop-activity-app.html',
					],
				'RGES' => [
					'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/geshop-activity-app.html'
					]
			]
		],
		'activitysigninfo'        => [
			'url'         => 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/get_sign_info',
			'method'      => 'post',
			'description' => '获取活动签到积分规则',
			'isJsonp'     => 1
		],
		'activitydosign'          => [
			'url'         => 'http://m.wap-rosegal.com.geshopsign.php5.egomsl.com/geshop/activity/do_sign_in',
			'method'      => 'post',
			'description' => '活动签到接口',
			'isJsonp'     => 1
		]
	],

    /**************************************** SUK *******************************************/
    'suk-pc'      => [
        'headFooterMonitorDomain' => [
            'home'     => [
	            'SUK' => [
	                'en' => 'http://suaoki' . SUK_DOMAIN . '/geshop/home/index',
		            ],
	            'SUKJP' => [
	                'ja' => 'http://jpsuaoki' . SUK_DOMAIN . '/geshop/home/index',
		            ]
            ],
            'activity' => [
	            'SUK' => [
	                'en' => 'http://suaoki' . SUK_DOMAIN . '/geshop/activity/index',
		            ],
	            'SUKJP' => [
	                'ja' => 'http://jpsuaoki' . SUK_DOMAIN . '/geshop/activity/index',
		            ]
            ]
        ],
    ],
    'suk-wap'      => [
        'headFooterMonitorDomain' => [
            'home'     => [
	            'SUK' => [
	                'en' => 'http://msuaoki' . SUK_DOMAIN . '/geshop/home/index',
		            ],
	            'SUKJP' => [
	                'ja' => 'http://jpmsuaoki' . SUK_DOMAIN . '/geshop/home/index',
		            ]
            ],
            'activity' => [
	            'SUK' => [
	                'en' => 'http://msuaoki' . SUK_DOMAIN . '/geshop/activity/index',
		            ],
	            'SUKJP' => [
	                'ja' => 'http://jpmsuaoki' . SUK_DOMAIN . '/geshop/activity/index',
		            ]
            ]
        ],
    ],

	/**************************************** ZF *******************************************/
	'zf-pc'      => [
		'goods'                   => [
			'url'         => '/api/get_geshop_api.php?act=goodsInfo',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goods_info',
			'link'        => 'rosegal://product?goods_id=%s&source='
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'ZF'     => [
					'en' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFES'   => [
					'es' => 'http://es.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFFR'   => [
					'fr' => 'http://fr.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFDE'   => [
					'de' => 'http://de.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFPT'   => [
					'pt' => 'http://pt.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFIT'   => [
					'it' => 'http://it.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFIE'   => [
					'en' => 'http://eur.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFNZ'   => [
					'en' => 'http://nz.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFGB'   => [
					'en' => 'http://uk.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFCA'   => [
					'en' => 'http://ca.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
					'fr' => 'http://ca.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=fr',
				],
				'ZFBE'   => [
					'fr' => 'http://be.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFCH'   => [
					'de' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
					'en' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=en',
					'fr' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=fr',
				],
				'ZFPH'   => [
					'en' => 'http://ph.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFIN'   => [
					'en' => 'http://in.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFSG'   => [
					'en' => 'http://sg.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFMY'   => [
					'en' => 'http://my.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFAU'   => [
					'en' => 'http://au.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFAT'   => [
					'de' => 'http://at.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFMX'   => [
					'es' => 'http://latam.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFZA'   => [
					'en' => 'http://za.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFBR'   => [
					'pt' => 'http://br.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFTH'   => [
					'th' => 'http://th.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFID'   => [
					'id' => 'http://id.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFTW'   => [
					'zh-tw' => 'http://tw.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFAR'   => [
					'ar' => 'http://ar.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFIL'   => [
					'en' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFRU'   => [
					'ru' => 'http://ru.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFHK'   => [
					'zh-tw' => 'http://hk.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFTR'   => [
					'tr' => 'http://tr.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFMX01' => [
					'es' => 'http://mx.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFRO'   => [
					'ro' => 'http://ro.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFJP'   => [
					'ja' => 'http://jp.pc-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
			],
			'activity' => [
				'ZF'     => [
					'en' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFES'   => [
					'es' => 'http://es.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFFR'   => [
					'fr' => 'http://fr.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFDE'   => [
					'de' => 'http://de.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFPT'   => [
					'pt' => 'http://pt.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFIT'   => [
					'it' => 'http://it.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFIE'   => [
					'en' => 'http://eur.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFNZ'   => [
					'en' => 'http://nz.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFGB'   => [
					'en' => 'http://uk.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFCA'   => [
					'en' => 'http://ca.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'fr' => 'http://ca.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
				],
				'ZFBE'   => [
					'fr' => 'http://be.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFCH'   => [
					'de' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'fr' => 'http://ch.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
				],
				'ZFPH'   => [
					'en' => 'http://ph.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFIN'   => [
					'en' => 'http://in.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFSG'   => [
					'en' => 'http://sg.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFMY'   => [
					'en' => 'http://my.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFAU'   => [
					'en' => 'http://au.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFAT'   => [
					'de' => 'http://at.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFMX'   => [
					'es' => 'http://latam.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFZA'   => [
					'en' => 'http://za.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFBR'   => [
					'pt' => 'http://br.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFTH'   => [
					'th' => 'http://th.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFID'   => [
					'id' => 'http://id.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFTW'   => [
					'zh-tw' => 'http://tw.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFAR'   => [
					'ar' => 'http://ar.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFIL'   => [
					'en' => 'http://il.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFRU'   => [
					'ru' => 'http://ru.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFHK'   => [
					'zh-tw' => 'http://hk.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFTR'   => [
					'tr' => 'http://tr.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFMX01' => [
					'es' => 'http://mx.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFRO'   => [
					'ro' => 'http://ro.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFJP'   => [
					'ja' => 'http://jp.pc-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
			]
		],
	],
	'zf-wap'     => [
		'goods'                   => [
			'url'         => '/api/get_geshop_api.php?act=goodsInfo',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goods_info'
		],
		'cmsgoods'                => [
			'url'         => 'http://m.wap-zaful.com.v44_app.php5.egomsl.com/cms/goods/goods_list',
			'method'      => 'post',
			'description' => '发现好货',
			'isJsonp'     => 1
		],
		'elf_notify'             => [
			'url'         => 'http://www.elf.com.geshop.php5.egomsl.com/api/geshop-api/sync-geshop',
			'method'      => 'post',
			'description' => '通知ELF专题页面链接变更',
			'isJsonp'     => 1
		],
		'cms_notify'             => [
			'url'         => 'http://www.cms.com.geshop.php7.egomsl.com/api/geshop-api/sync-geshop',
			'method'      => 'post',
			'description' => '通知CMS专题页面链接变更',
			'isJsonp'     => 1
		],
		'headFooterMonitorDomain' => [
			'home'     => [
				'ZF'     => [
					'en' => 'http://m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFES'   => [
					'es' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFFR'   => [
					'fr' => 'http://fr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFDE'   => [
					'de' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFIE'   => [
					'en' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFNZ'   => [
					'en' => 'http://nz.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFGB'   => [
					'en' => 'http://uk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFCA'   => [
					'en' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
					'fr' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=fr',
				],
				'ZFBE'   => [
					'fr' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFCH'   => [
					'de' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
					'en' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=en',
					'fr' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php?m=index&lang=fr',
				],
				'ZFPH'   => [
					'en' => 'http://ph.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFIN'   => [
					'en' => 'http://in.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFSG'   => [
					'en' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFMY'   => [
					'en' => 'http://my.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFAU'   => [
					'en' => 'http://au.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFAT'   => [
					'de' => 'http://at.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFMX'   => [
					'es' => 'http://latam.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFZA'   => [
					'en' => 'http://za.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFBR'   => [
					'pt' => 'http://br.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFTH'   => [
					'th' => 'http://th.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFID'   => [
					'id' => 'http://id.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php',
				],
				'ZFTW'   => [
					'zh-tw' => 'http://tw.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFAR'   => [
					'ar' => 'http://ar.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFIL'   => [
					'en' => 'http://il.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFRU'   => [
					'ru' => 'http://ru.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFHK'   => [
					'zh-tw' => 'http://hk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFTR'   => [
					'tr' => 'http://tr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFMX01' => [
					'es' => 'http://mx.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFRO'   => [
					'ro' => 'http://ro.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
				'ZFJP'   => [
					'ja' => 'http://jp.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/index.php'
				],
			],
			'activity' => [
				'ZF'     => [
					'en' => 'http://m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'es' => 'http://m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=es'
				],
				'ZFES'   => [
					'es' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'fr' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
				],
				'ZFFR'   => [
					'fr' => 'http://fr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://fr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFDE'   => [
					'de' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'es' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=es',
				],
				'ZFIE'   => [
					'en' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'fr' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
					'de' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=de',
					'es' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=es',
					'pt' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=pt',
					'it' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=it',
					'ru' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=ru',
				],
				'ZFNZ'   => [
					'en' => 'http://nz.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFGB'   => [
					'en' => 'http://uk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'es' => 'http://uk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=es',
				],
				'ZFCA'   => [
					'en' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'fr' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
				],
				'ZFBE'   => [
					'fr' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'de' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=de',
				],
				'ZFCH'   => [
					'de' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'fr' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=fr',
					'it' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=it',
					'es' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=es',
				],
				'ZFPH'   => [
					'en' => 'http://ph.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFIN'   => [
					'en' => 'http://in.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFSG'   => [
					'en' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'th' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=th',
					'id' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=id',
				],
				'ZFMY'   => [
					'en' => 'http://my.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFAU'   => [
					'en' => 'http://au.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFAT'   => [
					'de' => 'http://at.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://at.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFMX'   => [
					'es' => 'http://latam.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFZA'   => [
					'en' => 'http://za.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
				],
				'ZFBR'   => [
					'pt' => 'http://br.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://br.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFTH'   => [
					'th' => 'http://th.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://th.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
				],
				'ZFID'   => [
					'id' => 'http://id.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://id.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
				],
				'ZFTW'   => [
					'zh-tw' => 'http://tw.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en'    => 'http://tw.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFAR'   => [
					'ar' => 'http://ar.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://ar.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFIT'   => [
					'it' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
					'de' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=de'
				],
				'ZFRU'   => [
					'ru' => 'http://ru.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://ru.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFIL'   => [
					'en' => 'http://il.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en',
                    'he' => 'http://il.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=he'
				],
				'ZFHK'   => [
					'zh-tw' => 'http://hk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFTR'   => [
					'tr' => 'http://tr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://tr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFMX01' => [
					'es' => 'http://mx.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFRO'   => [
					'ro' => 'http://ro.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html'
				],
				'ZFJP'   => [
					'ja' => 'http://jp.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://jp.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				],
				'ZFVN'   => [
					'vi' => 'http://vn.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html',
					'en' => 'http://vn.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity.html?lang=en'
				]
			]
		],
	],
	'zf-app'     => [
		'goods'              => [
			'url'         => '/api/get_geshop_api.php?act=goodsInfo',
			'method'      => 'post',
			'description' => '商品详情（旧）',
			'dataKey'     => 'goods_info',
			'link'        => 'zaful://action?actiontype=3&url=%s&name=scoop-neck-loose-knit-sweater&source=deeplink'
		],
		'cmsgoods'           => [
			'url'         => 'http://m.wap-zaful.com.v44_app.php5.egomsl.com/cms/goods/goods_list',
			'method'      => 'post',
			'description' => '发现好货',
			'isJsonp'     => 1
		],
		'elf_notify'             => [
			'url'         => 'http://www.elf.com.geshop.php5.egomsl.com/api/geshop-api/sync-geshop',
			'method'      => 'post',
			'description' => '通知ELF专题页面链接变更',
			'isJsonp'     => 1
		],
		'cms_notify'             => [
			'url'         => 'http://www.cms.com.geshop.php7.egomsl.com/api/geshop-api/sync-geshop',
			'method'      => 'post',
			'description' => '通知CMS专题页面链接变更',
			'isJsonp'     => 1
		],
		'community_category' => [
			'url'         => 'http://dallas_community.gloapi.com/api/zaful-community/category',
			'method'      => 'get',
			'description' => '社区瀑布流分类',
			'isJsonp'     => 1
		],
		'community_post'     => [
			'url'         => 'http://dallas_community.gloapi.com/api/zaful-community/post',
			'method'      => 'get',
			'description' => '社区瀑布流帖子',
			'isJsonp'     => 1
		],

		'headFooterMonitorDomain' => [
			'activity' => [
				'ZF'     => [
					'en' => 'http://m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'es' => 'http://m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=es',
				],
				'ZFES'   => [
					'es' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
					'fr' => 'http://es.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=fr',
				],
				'ZFFR'   => [
					'fr' => 'http://fr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://fr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
				],
				'ZFDE'   => [
					'de' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
					'es' => 'http://de.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=es',
				],
				'ZFIE'   => [
					'en' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'fr' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=fr',
					'de' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=de',
					'es' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=es',
					'pt' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=pt',
					'it' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=it',
					'ru' => 'http://eur.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=ru',
				],
				'ZFNZ'   => [
					'en' => 'http://nz.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFGB'   => [
					'en' => 'http://uk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'es' => 'http://uk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=es',
				],
				'ZFCA'   => [
					'en' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'fr' => 'http://ca.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=fr',
				],
				'ZFBE'   => [
					'fr' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
					'de' => 'http://be.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=de',
				],
				'ZFCH'   => [
					'de' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
					'fr' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=fr',
					'it' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=it',
					'es' => 'http://ch.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=es',
				],
				'ZFPH'   => [
					'en' => 'http://ph.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFIN'   => [
					'en' => 'http://in.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFSG'   => [
					'en' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'th' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=th',
					'id' => 'http://sg.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=id',
				],
				'ZFMY'   => [
					'en' => 'http://my.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFAU'   => [
					'en' => 'http://au.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFAT'   => [
					'de' => 'http://at.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://at.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFMX'   => [
					'es' => 'http://latam.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://latam.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
				],
				'ZFZA'   => [
					'en' => 'http://za.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
				],
				'ZFBR'   => [
					'pt' => 'http://br.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://br.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFTH'   => [
					'th' => 'http://th.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://th.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
				],
				'ZFID'   => [
					'id' => 'http://id.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://id.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
				],
				'ZFTW'   => [
					'zh-tw' => 'http://tw.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en'    => 'http://tw.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFAR'   => [
					'ar' => 'http://ar.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://ar.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFIT'   => [
					'it' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
					'de' => 'http://it.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=de',
				],
				'ZFIL'   => [
					'en' => 'http://il.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en',
                    'he' => 'http://il.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=he'
				],
				'ZFRU'   => [
					'ru' => 'http://ru.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://ru.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFTR'   => [
					'tr' => 'http://tr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://tr.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFVN'   => [
					'vi' => 'http://vn.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://vn.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFJP'   => [
					'ja' => 'http://jp.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html',
					'en' => 'http://jp.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html?lang=en'
				],
				'ZFHK'   => [
					'zh-tw' => 'http://hk.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html'
				],
				'ZFMX01' => [
					'es' => 'http://mx.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html'
				],
				'ZFRO'   => [
					'ro' => 'http://ro.m.wap-zaful' . ZF_DOMAIN_DEVELOP . '/geshop-activity-app.html'
				],
			]
		]
	],

	/**************************************** GB *******************************************/
	'gb-pc'      => [
		'goods'                   => [],
		'headFooterMonitorDomain' => [
			'activity'  => [
				'GB'    => [
					'en' => 'https://www' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBES'  => [
					'ep' => 'https://es' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBFR'  => [
					'fr' => 'https://fr' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBRU'  => [
					'ru' => 'https://ru' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBPT'  => [
					'po' => 'https://pt' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBIT'  => [
					'it' => 'https://it' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBDE'  => [
					'de' => 'https://de' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBUK'  => [
					'en' => 'https://uk' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBUS'  => [
					'en' => 'https://us' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBBR'  => [
					'pt-br' => 'https://br' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBTR'  => [
					'tr' => 'https://tr' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBMX'  => [
					'ep-mx' => 'https://mx' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBMA'  => [
					'fr' => 'https://ma' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBGR'  => [
					'el' => 'https://gr' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBHU'  => [
					'hu' => 'https://hu' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBNL'  => [
					'nl' => 'https://nl' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBSK'  => [
					'sk' => 'https://sk' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBRO'  => [
					'ro' => 'https://ro' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBCZ'  => [
					'cs' => 'https://cz' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBAU'  => [
					'en' => 'https://au' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBIN'  => [
					'en' => 'https://in' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBJP'  => [
					'ja' => 'https://jp' . GB_DOMAIN . '/geshop-activity.html'
				],
				/*'GBUA' => [
					'uk' => 'https://ua' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBIL' => [
					'he' => 'https://il' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBKZ' => [
					'kk' => 'https://kz' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBTH' => [
					'th' => 'https://th' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBVN' => [
					'vi' => 'https://vn' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBID' => [
					'id' => 'https://id' . GB_DOMAIN . '/geshop-activity.html'
				],*/
				'GBPL'  => [
					'pl' => 'https://pl' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBSTY' => [
					'en' => 'https://stylebest' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBGAG' => [
					'en' => 'https://gagabop' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBCOZ' => [
					'en' => 'https://cozyvoices' . GB_DOMAIN . '/geshop-activity.html'
				],
			],
			'home'      => [
				'en' => 'https://www' . GB_DOMAIN . '/geshop-home.html',
			],
			'promotion' => [
				'en'    => 'https://www' . GB_DOMAIN . '/geshop-advertising.html',
				'ep'    => 'https://es' . GB_DOMAIN . '/geshop-advertising.html',
				'fr'    => 'https://fr' . GB_DOMAIN . '/geshop-advertising.html',
				'ru'    => 'https://ru' . GB_DOMAIN . '/geshop-advertising.html',
				'po'    => 'https://pt' . GB_DOMAIN . '/geshop-advertising.html',
				'it'    => 'https://it' . GB_DOMAIN . '/geshop-advertising.html',
				'de'    => 'https://de' . GB_DOMAIN . '/geshop-advertising.html',
				'en_gb' => 'https://uk' . GB_DOMAIN . '/geshop-advertising.html',
				'en_us' => 'https://us' . GB_DOMAIN . '/geshop-advertising.html',
				'pt_br' => 'https://br' . GB_DOMAIN . '/geshop-advertising.html',
				'tr'    => 'https://tr' . GB_DOMAIN . '/geshop-advertising.html',
				'ep-mx' => 'https://mx' . GB_DOMAIN . '/geshop-advertising.html',
				'fr_ma' => 'https://ma' . GB_DOMAIN . '/geshop-advertising.html',
			]
		],
		'gb_search'               => 'http://10.4.4.83:8082/{domain}/search',
		'i18n_search'             => 'http://10.60.35.119:8082/{domain}/i18n/search'
	],
	'gb-wap'     => [
		'goods'                   => [],
		'headFooterMonitorDomain' => [
			'activity'  => [
				'GB'    => [
					'en'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html',
					'ep'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=es',
					'fr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=fr',
					'ru'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=ru',
					'de'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=de',
					'tr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=tr',
					'pt-br' => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=pt-br',
					'po'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?lang=po'
				],
				'GBES'  => [
					'ep' => 'https://m-es' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBFR'  => [
					'fr' => 'https://m-fr' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBRU'  => [
					'ru' => 'https://m-ru' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBPT'  => [
					'po' => 'https://m-pt' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBIT'  => [
					'it' => 'https://m-it' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBDE'  => [
					'de' => 'https://m-de' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBUK'  => [
					'en' => 'https://m-uk' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBUS'  => [
					'en' => 'https://m-us' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBBR'  => [
					'pt-br' => 'https://m-br' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBTR'  => [
					'tr' => 'https://m-tr' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBMX'  => [
					'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBMA'  => [
					'fr' => 'https://m-ma' . GB_DOMAIN . '/geshop-activity.html',
				],
				'GBGR'  => [
					'el' => 'https://m-gr' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBHU'  => [
					'hu' => 'https://m-hu' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBNL'  => [
					'nl' => 'https://m-nl' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBSK'  => [
					'sk' => 'https://m-sk' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBRO'  => [
					'ro' => 'https://m-ro' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBCZ'  => [
					'cs' => 'https://m-cz' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBAU'  => [
					'en' => 'https://m-au' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBIN'  => [
					'en' => 'https://m-in' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBJP'  => [
					'ja' => 'https://m-jp' . GB_DOMAIN . '/geshop-activity.html'
				],
				/*'GBUA' => [
					'uk' => 'https://m-ua' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBIL' => [
					'he' => 'https://m-il' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBKZ' => [
					'kk' => 'https://m-kz' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBTH' => [
					'th' => 'https://m-th' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBVN' => [
					'vi' => 'https://m-vn' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBID' => [
					'id' => 'https://m-id' . GB_DOMAIN . '/geshop-activity.html'
				],*/
				'GBPL'  => [
					'pl' => 'https://m-pl' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBSTY' => [
					'en' => 'https://m-stylebest' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBGAG' => [
					'en' => 'https://m-gagabop' . GB_DOMAIN . '/geshop-activity.html'
				],
				'GBCOZ' => [
					'en' => 'https://m-cozyvoices' . GB_DOMAIN . '/geshop-activity.html'
				],
			],
			'home'      => [
				'en' => 'https://m' . GB_DOMAIN . '/geshop-home.html',
			],
			'promotion' => [
				'en'    => 'https://m' . GB_DOMAIN . '/geshop-advertising.html',
				'ep'    => 'https://m-es' . GB_DOMAIN . '/geshop-advertising.html',
				'fr'    => 'https://m-fr' . GB_DOMAIN . '/geshop-advertising.html',
				'ru'    => 'https://m-ru' . GB_DOMAIN . '/geshop-advertising.html',
				'po'    => 'https://m-pt' . GB_DOMAIN . '/geshop-advertising.html',
				'it'    => 'https://m-it' . GB_DOMAIN . '/geshop-advertising.html',
				'de'    => 'https://m-de' . GB_DOMAIN . '/geshop-advertising.html',
				'en_gb' => 'https://m-uk' . GB_DOMAIN . '/geshop-advertising.html',
				'en_us' => 'https://m-us' . GB_DOMAIN . '/geshop-advertising.html',
				'pt_br' => 'https://m-br' . GB_DOMAIN . '/geshop-advertising.html',
				'tr'    => 'https://m-tr' . GB_DOMAIN . '/geshop-advertising.html',
				'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/geshop-advertising.html',
				'fr_ma' => 'https://m-ma' . GB_DOMAIN . '/geshop-advertising.html',
			]
		],
		'gb_search'               => 'http://10.4.4.83:8082/{domain}/search',
		'i18n_search'             => 'http://10.60.35.119:8082/{domain}/i18n/search'
	],
	'gb-ios'     => [
		'goods'                   => [],
		'headFooterMonitorDomain' => [
			'activity' => [
				'GB'   => [
					'en'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app',
					'ep'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=es',
					'fr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=fr',
					'ru'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=ru',
					'po'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=pt',
					'it'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=it',
					'de'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=de',
					'tr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=tr',
					'pt-br' => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=pt-br'
				],
				'GBRU' => [
					'ru' => 'https://m-ru' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBFR' => [
					'fr' => 'https://m-fr' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBPT' => [
					'po' => 'https://m-pt' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBIT' => [
					'it' => 'https://m-it' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBUK' => [
					'en' => 'https://m-uk' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBUS' => [
					'en' => 'https://m-us' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBBR' => [
					'pt-br' => 'https://m-br' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBTR' => [
					'tr' => 'https://m-tr' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBMX' => [
					'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBMA' => [
					'fr' => 'https://m-ma' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBGR' => [
					'el' => 'https://m-gr' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBHU' => [
					'hu' => 'https://m-hu' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBNL' => [
					'nl' => 'https://m-nl' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBSK' => [
					'sk' => 'https://m-sk' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBRO' => [
					'ro' => 'https://m-ro' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBCZ' => [
					'cs' => 'https://m-cz' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBAU' => [
					'en' => 'https://m-au' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBIN' => [
					'en' => 'https://m-in' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBJP' => [
					'ja' => 'https://m-jp' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				/*'GBUA' => [
					'uk' => 'https://m-ua' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBIL' => [
					'he' => 'https://m-il' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBKZ' => [
					'kk' => 'https://m-kz' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBTH' => [
					'th' => 'https://m-th' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBVN' => [
					'vi' => 'https://m-vn' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBID' => [
					'id' => 'https://m-id' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],*/
				'GBPL' => [
					'pl' => 'https://m-pl' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
			],
			'home'     => [
				'en' => 'https://m' . GB_DOMAIN . '/geshop-home.html',
			]
		],
		'gb_search'               => 'http://10.4.4.83:8082/{domain}/m/search',
		'i18n_search'             => 'http://10.60.35.119:8082/{domain}/i18n/search'
	],
	'gb-android' => [
		'goods'                   => [],
		'headFooterMonitorDomain' => [
			'activity' => [
				'GB'   => [
					'en'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app',
					'ep'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=es',
					'fr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=fr',
					'ru'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=ru',
					'po'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=pt',
					'it'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=it',
					'de'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=de',
					'tr'    => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=tr',
					'pt-br' => 'https://m' . GB_DOMAIN . '/geshop-activity.html?type=app&lang=pt-br'
				],
				'GBRU' => [
					'ru' => 'https://m-ru' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBFR' => [
					'fr' => 'https://m-fr' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBPT' => [
					'po' => 'https://m-pt' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBIT' => [
					'it' => 'https://m-it' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBUK' => [
					'en' => 'https://m-uk' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBUS' => [
					'en' => 'https://m-us' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBBR' => [
					'pt-br' => 'https://m-br' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBTR' => [
					'tr' => 'https://m-tr' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBMX' => [
					'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBMA' => [
					'fr' => 'https://m-ma' . GB_DOMAIN . '/geshop-activity.html?type=app',
				],
				'GBGR' => [
					'el' => 'https://m-gr' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBHU' => [
					'hu' => 'https://m-hu' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBNL' => [
					'nl' => 'https://m-nl' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBSK' => [
					'sk' => 'https://m-sk' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBRO' => [
					'ro' => 'https://m-ro' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBCZ' => [
					'cs' => 'https://m-cz' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBAU' => [
					'en' => 'https://m-au' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBIN' => [
					'en' => 'https://m-in' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBJP' => [
					'ja' => 'https://m-jp' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				/*'GBUA' => [
					'uk' => 'https://m-ua' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBIL' => [
					'he' => 'https://m-il' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBKZ' => [
					'kk' => 'https://m-kz' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBTH' => [
					'th' => 'https://m-th' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBVN' => [
					'vi' => 'https://m-vn' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
				'GBID' => [
					'id' => 'https://m-id' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],*/
				'GBPL' => [
					'pl' => 'https://m-pl' . GB_DOMAIN . '/geshop-activity.html?type=app'
				],
			],
			'home'     => [
				'en' => 'https://m' . GB_DOMAIN . '/geshop-home.html',
			]
		],
		'gb_search'               => 'http://10.4.4.83:8082/{domain}/m/search',
		'i18n_search'             => 'http://10.60.35.119:8082/{domain}/i18n/search'
	],

	/**************************************** DL *******************************************/
	'dl-web'     => [
		'headFooterMonitorDomain' => [
			'home'     => [
				'DL' => [
					'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/geshop-index.html',
					],
				'DLFR' => [
					'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/geshop-index.html',
					]
			],
			'activity' => [
				'DL' => [
					'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/geshop-activity.html',
					],
				'DLFR' => [
					'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/geshop-activity.html',
					]
			]
		],
		'nodeApi'                 => [
			'render' => [
				'method' => 'post',
				'url'    => '/node/component/render'
			]
		]
	],
	'dl-app'     => [
		'goods'                   => [
			'link' => 'dresslily://action?actiontype=3&url=%s&name=detail&source=banner'
		],
		'headFooterMonitorDomain' => [
			'activity' => [
				'DL' => [
					'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/geshop_activity_app.html',
					],
				'DLFR' => [
					'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/geshop_activity_app.html',
					]
			]
		],
		'nodeApi'                 => [
			'render' => [
				'method' => 'post',
				'url'    => '/node/component/render'
			]
		]
	],
];

return \yii\helpers\ArrayHelper::merge($_site_interface_config, require __DIR__ . '/interface.public.php');
