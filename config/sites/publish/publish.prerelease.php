<?php
/**
 * 站点预发布环境发布配置
 */
return [
	/****************************************测试站点配置START*****************************************/
	'test-pc'    => [
		's3PublishPath'         => [//活动页面静态HTML文件存放路径
		                            'en' => 'publish/rw-pc/en/release',
		                            'es' => 'publish/rw-pc/es/release',
		],
		's3HomePublishPath'     => [//首页静态HTML文件存放路径
		                            'en' => 'publish/rw-pc/en/test/home/release',
		                            'es' => 'publish/rw-pc/es/test/home/release',
		],
		's3StaticPath'          => [//页面使用的JS与css文件存放路径
		                            'en' => 'statics/rw-pc/en',
		                            'es' => 'statics/rw-pc/es',
		],
		'secondary_domain'      => [//活动页面代理站点的访问链接
		                            'en' => 'https://www' . TEST_DOMAIN . '/promotion/release',
		                            'es' => 'https://es' . TEST_DOMAIN . '/promotion/release',
		],
		'home_secondary_domain' => [//首页代理站点的访问链接
		                            'en' => 'https://www' . TEST_DOMAIN . '/promotion/test/home/release',
		                            'es' => 'https://es' . TEST_DOMAIN . '/promotion/test/home/release',
		]
	],
	'test-wap'   => [
		's3PublishPath'         => [
			'en' => 'publish/rw-wap/en/release',
		],
		's3HomePublishPath'     => [
			'en' => 'publish/rw-wap/en/test/home/release',
		],
		's3StaticPath'          => [
			'en' => 'statics/rw-wap/en',
		],
		'secondary_domain'      => [
			'en' => 'https://m' . RW_DOMAIN . '/promotion/release'
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . TEST_DOMAIN . '/promotion/test/home/release'
		]
	],
	'test-app'   => [
		's3PublishPath'         => [
			'en' => 'publish/rw-wap/en/app/release',
		],
		's3HomePublishPath'     => [
			'en' => 'publish/rw-wap/en/app/test/home/release',
		],
		's3StaticPath'          => [
			'en' => 'statics/rw-wap/en',
		],
		'secondary_domain'      => [
			'en' => 'https://m' . TEST_DOMAIN . '/promotion/app/release'
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . TEST_DOMAIN . '/promotion/app/test/home/release'
		]
	],
	/****************************************测试站点配置END*******************************************/
	'rw-pc'      => [
		's3PublishPath'         => [
			'en' => 'publish/rw-pc/en/release',
			'es' => 'publish/rw-pc/es/release',
		],
		's3HomePublishPath'     => [
			'en' => 'publish/rw-pc/en/home/release',
			'es' => 'publish/rw-pc/es/home/release',
		],
		's3StaticPath'          => [
			'en' => 'statics/rw-pc/en',
			'es' => 'statics/rw-pc/es',
		],
		'secondary_domain'      => [
			'en' => 'https://www' . RW_DOMAIN . '/promotion/release',
			'es' => 'https://es' . RW_DOMAIN . '/promotion/release',
		],
		'home_secondary_domain' => [
			'en' => 'https://www' . RW_DOMAIN . '/promotion/home/release',
			'es' => 'https://es' . RW_DOMAIN . '/promotion/home/release',
		]
	],
	'rw-wap'     => [
		's3PublishPath'         => [
			'en' => 'publish/rw-wap/en/release',
		],
		's3HomePublishPath'     => [
			'en' => 'publish/rw-wap/en/home/release',
		],
		's3StaticPath'          => [
			'en' => 'statics/rw-wap/en',
		],
		'secondary_domain'      => [
			'en' => 'https://m' . RW_DOMAIN . '/promotion/release'
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . RW_DOMAIN . '/promotion/home/release'
		]
	],
	'rw-app'     => [
		's3PublishPath'         => [
			'en' => 'publish/rw-wap/en/app/release',
		],
		's3HomePublishPath'     => [
			'en' => 'publish/rw-wap/en/app/home/release',
		],
		's3StaticPath'          => [
			'en' => 'statics/rw-wap/en',
		],
		'secondary_domain'      => [
			'en' => 'https://m' . RW_DOMAIN . '/promotion/app/release'
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . RW_DOMAIN . '/promotion/app/home/release'
		]
	],
	'rg-pc'      => [
		's3PublishPath'         => [
			'RG' => [
				'en' => 'publish/www.rosegal.com/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr.rosegal.com/release',
				],
			'RGRU' => [
				'ru' => 'publish/ru.rosegal.com/release',
				],
			'RGAR' => [
				'ar' => 'publish/ar.rosegal.com/release',
				],
			],
		's3HomePublishPath'     => [
			'RG' => [
				'en' => 'publish/www.rosegal.com/home/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr.rosegal.com/home/release',
				],
			'RGRU' => [
				'ru' => 'publish/ru.rosegal.com/home/release',
				],
			'RGAR' => [
				'ar' => 'publish/ar.rosegal.com/home/release',
				],
			],
		's3StaticPath'          => [
			'RG' => [
				'en' => 'statics/rg-pc/en',
				],
			'RGFR' => [
				'fr' => 'statics/rg-pc/fr',
				],
			'RGRU' => [
				'ru' => 'statics/rg-pc/ru',
				],
			'RGAR' => [
				'ar' => 'statics/rg-pc/ar',
				]
		],
		'secondary_domain'      => [
			'RG' => [
				'en' => 'https://www' . RG_DOMAIN . '/promotion/release',
				],
			'RGFR' => [
				'fr' => 'https://fr' . RG_DOMAIN . '/promotion/release',
				],
			'RGRU' => [
				'ru' => 'https://ru' . RG_DOMAIN . '/promotion/release',
				],
			],
		'home_secondary_domain' => [
			'RG' => [
				'en' => 'https://www' . RG_DOMAIN . '/promotion/home/release',
				],
			'RGFR' => [
				'fr' => 'https://fr' . RG_DOMAIN . '/promotion/home/release',
				],
			'RGRU' => [
				'ru' => 'https://ru' . RG_DOMAIN . '/promotion/home/release',
				],
			],
	],
	'rg-wap'     => [
		's3PublishPath'         => [
			'RG' => [
				'en' => 'publish/m.rosegal.com/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr-m.rosegal.com/release',
				],
			'RGES' => [
				'es' => 'publish/es-m.rosegal.com/release',
				],
			],
		's3HomePublishPath'     => [
			'RG' => [
				'en' => 'publish/m.rosegal.com/home/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr-m.rosegal.com/home/release',
				],
			'RGES' => [
				'es' => 'publish/es-m.rosegal.com/home/release',
				],
			],
		's3StaticPath'          => [
			'RG' => [
				'en' => 'statics/rg-wap/en',
				],
			'RGFR' => [
				'fr' => 'statics/rg-wap/fr',
				],
			'RGES' => [
				'es' => 'statics/rg-wap/es',
				]
		],
		'secondary_domain'      => [
			'RG' => [
				'en' => 'https://m' . RG_DOMAIN . '/promotion/release',
				],
			'RGFR' => [
				'fr' => 'https://fr-m' . RG_DOMAIN . '/promotion/release',
				],
			'RGES' => [
				'es' => 'https://es-m' . RG_DOMAIN . '/promotion/release',
				],
			],
		'home_secondary_domain' => [
			'RG' => [
				'en' => 'https://m' . RG_DOMAIN . '/promotion/home/release',
				],
			'RGFR' => [
				'fr' => 'https://fr-m' . RG_DOMAIN . '/promotion/home/release',
				],
			'RGES' => [
				'es' => 'https://es-m' . RG_DOMAIN . '/promotion/home/release',
				]
			],
	],
	'rg-app'     => [
		's3PublishPath'         => [
			'RG' => [
				'en' => 'publish/m.rosegal.com/app/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr-m.rosegal.com/app/release',
				],
			'RGES' => [
				'es' => 'publish/es-m.rosegal.com/app/release',
				]
		],
		's3HomePublishPath'     => [
			'RG' => [
				'en' => 'publish/m.rosegal.com/app/home/release',
				],
			'RGFR' => [
				'fr' => 'publish/fr-m.rosegal.com/app/home/release',
				],
			'RGES' => [
				'es' => 'publish/es-m.rosegal.com/app/home/release',
				]
		],
		's3StaticPath'          => [
			'RG' => [
				'en' => 'statics/rg-wap/en',
				],
			'RGFR' => [
				'fr' => 'statics/rg-wap/fr',
				],
			'RGES' => [
				'es' => 'statics/rg-wap/es',
				]
		],
		'secondary_domain'      => [
			'RG' => [
				'en' => 'https://m' . RG_DOMAIN . '/promotion/app/release',
				],
			'RGFR' => [
				'fr' => 'https://fr-m' . RG_DOMAIN . '/promotion/app/release',
				],
			'RGES' => [
				'es' => 'https://es-m' . RG_DOMAIN . '/promotion/app/release',
				]
		],
		'home_secondary_domain' => [
			'RG' => [
				'en' => 'https://m' . RG_DOMAIN . '/promotion/app/home/release',
				],
			'RGFR' => [
				'fr' => 'https://fr-m' . RG_DOMAIN . '/promotion/app/home/release',
				],
			'RGES' => [
				'es' => 'https://es-m' . RG_DOMAIN . '/promotion/app/home/release',
				]
		]
	],
	/**************************************** SUK *******************************************/
	'suk-pc'      => [
		's3PublishPath'         => [
			'SUK' => [
				'en' => 'publish/www.suaoki.com/en/release',
				],
			'SUKJP' => [
				'ja' => 'publish/jp.suaoki.com/ja/release',
				]
		],
		's3HomePublishPath'     => [
			'SUK' => [
				'en' => 'publish/www.suaoki.com/en/home/release',
				],
			'SUKJP' => [
				'ja' => 'publish/jp.suaoki.com/ja/home/release',
				]
		],
		's3StaticPath'          => [
			'SUK' => [
				'en' => 'statics/www.suaoki.com/en',
				],
			'SUKJP' => [
				'ja' => 'statics/jp.suaoki.com/ja',
				]
		],
		'secondary_domain'      => [
			'SUK' => [
				'en' => 'http://www.suaoki' . SUK_DOMAIN . '/promotion/release',
				],
			'SUKJP' => [
				'ja' => 'http://jp.suaoki' . SUK_DOMAIN . '/promotion/release',
				]
		],
		'home_secondary_domain' => [
			'SUK' => [
				'en' => 'http://www.suaoki' . SUK_DOMAIN . '/promotion/home/release',
				],
			'SUKJP' => [
				'ja' => 'http://jp.suaoki' . SUK_DOMAIN . '/promotion/home/release',
				]
		]
	],
	'suk-wap'     => [
		's3PublishPath'         => [
			'SUK' => [
				'en' => 'publish/m.suaoki.com/en/release',
				],
			'SUKJP' => [
				'ja' => 'publish/m-jp.suaoki.com/ja/release',
				]
		],
		's3HomePublishPath'     => [
			'SUK' => [
				'en' => 'publish/m.suaoki.com/en/home/release',
				],
			'SUKJP' => [
				'ja' => 'publish/m-jp.suaoki.com/ja/home/release',
				]
		],
		's3StaticPath'          => [
			'SUK' => [
				'en' => 'statics/m.suaoki.com/en',
				],
			'SUKJP' => [
				'ja' => 'statics/m-jp.suaoki.com/ja',
				]
		],
		'secondary_domain'      => [
			'SUK' => [
				'en' => 'http://m.suaoki' . SUK_DOMAIN . '/promotion/release',
				],
			'SUKJP' => [
				'ja' => 'http://m-jp.suaoki' . SUK_DOMAIN . '/promotion/release',
				]
		],
		'home_secondary_domain' => [
			'SUK' => [
				'en' => 'http://m.suaoki' . SUK_DOMAIN . '/promotion/home/release',
				],
			'SUKJP' => [
				'ja' => 'http://m-jp.suaoki' . SUK_DOMAIN . '/promotion/home/release',
				]
		]
	],
	/**************************************** ZF *******************************************/
	'zf-pc'      => [
		's3PublishPath'         => [
			'ZF'     => [
				'en' => 'publish/www.zaful.com/en/release',
			],
			'ZFES'   => [
				'es' => 'publish/es.zaful.com/es/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr.zaful.com/fr/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de.zaful.com/de/release',
			],
			'ZFPT'   => [
				'pt' => 'publish/pt.zaful.com/pt/release',
			],
			'ZFIT'   => [
				'it' => 'publish/it.zaful.com/it/release',
			],
			'ZFIE'   => [
				'en' => 'publish/eur.zaful.com/en/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz.zaful.com/en/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk.zaful.com/en/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca.zaful.com/en/release',
				'fr' => 'publish/ca.zaful.com/fr/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be.zaful.com/fr/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch.zaful.com/de/release',
				'en' => 'publish/ch.zaful.com/en/release',
				'fr' => 'publish/ch.zaful.com/fr/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph.zaful.com/en/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in.zaful.com/en/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg.zaful.com/en/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my.zaful.com/en/release',
			],
			'ZFAU'   => [
				'en' => 'publish/au.zaful.com/en/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at.zaful.com/de/release'
			],
			'ZFMX'   => [
				'es' => 'publish/latam.zaful.com/es/release',
			],
			'ZFZA'   => [
				'en' => 'publish/za.zaful.com/en/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br.zaful.com/pt/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th.zaful.com/th/release',
			],
			'ZFID'   => [
				'id' => 'publish/id.zaful.com/id/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw.zaful.com/zh-tw/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar.zaful.com/ar/release'
			],
			'ZFIL'   => [
				'en' => 'publish/il.zaful.com/en/release'
			],
			'ZFRU'   => [
				'ru' => 'publish/ru.zaful.com/ru/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk.zaful.com/zh-tw/release'
			],
			'ZFTR'   => [
				'tr' => 'publish/tr.zaful.com/tr/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx.zaful.com/es/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro.zaful.com/ro/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp.zaful.com/ja/release'
			],
		],
		's3HomePublishPath'     => [
			'ZF'     => [
				'en' => 'publish/www.zaful.com/en/home/release',
			],
			'ZFES'   => [
				'es' => 'publish/es.zaful.com/es/home/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr.zaful.com/fr/home/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de.zaful.com/de/home/release',
			],
			'ZFPT'   => [
				'pt' => 'publish/pt.zaful.com/pt/home/release',
			],
			'ZFIT'   => [
				'it' => 'publish/it.zaful.com/it/home/release',
			],
			'ZFIE'   => [
				'en' => 'publish/eur.zaful.com/en/home/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz.zaful.com/en/home/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk.zaful.com/en/home/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca.zaful.com/en/home/release',
				'fr' => 'publish/ca.zaful.com/fr/home/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be.zaful.com/fr/home/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch.zaful.com/de/home/release',
				'en' => 'publish/ch.zaful.com/en/home/release',
				'fr' => 'publish/ch.zaful.com/fr/home/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph.zaful.com/en/home/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in.zaful.com/en/home/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg.zaful.com/en/home/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my.zaful.com/en/home/release',
			],
			'ZFAU'   => [
				'en' => 'publish/au.zaful.com/en/home/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at.zaful.com/de/home/release'
			],
			'ZFMX'   => [
				'es' => 'publish/latam.zaful.com/es/home/release',
			],
			'ZFZA'   => [
				'en' => 'publish/za.zaful.com/en/home/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br.zaful.com/pt/home/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th.zaful.com/th/home/release',
			],
			'ZFID'   => [
				'id' => 'publish/id.zaful.com/id/home/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw.zaful.com/zh-tw/home/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar.zaful.com/ar/home/release'
			],
			'ZFIL'   => [
				'en' => 'publish/il.zaful.com/en/home/release'
			],
			'ZFRU'   => [
				'ru' => 'publish/ru.zaful.com/ru/home/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk.zaful.com/zh-tw/home/release'
			],
			'ZFTR'   => [
				'tr' => 'publish/tr.zaful.com/tr/home/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx.zaful.com/es/home/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro.zaful.com/ro/home/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp.zaful.com/ja/home/release'
			],
		],
		's3StaticPath'          => [
			'ZF'     => [
				'en' => 'statics/zf-pc/ZF-en',
			],
			'ZFES'   => [
				'es' => 'statics/zf-pc/ZFES-es',
			],
			'ZFFR'   => [
				'fr' => 'statics/zf-pc/ZFFR-fr',
			],
			'ZFDE'   => [
				'de' => 'statics/zf-pc/ZFDE-de',
			],
			'ZFPT'   => [
				'pt' => 'statics/zf-pc/ZFPT-pt',
			],
			'ZFIT'   => [
				'it' => 'statics/zf-pc/ZFIT-it',
			],
			'ZFIE'   => [
				'en' => 'statics/zf-pc/ZFIE-en',
			],
			'ZFNZ'   => [
				'en' => 'statics/zf-pc/ZFNZ-en',
			],
			'ZFGB'   => [
				'en' => 'statics/zf-pc/ZFGB-en',
			],
			'ZFCA'   => [
				'en' => 'statics/zf-pc/ZFCA-en',
				'fr' => 'statics/zf-pc/ZFCA-fr',
			],
			'ZFBE'   => [
				'fr' => 'statics/zf-pc/ZFBE-fr',
			],
			'ZFCH'   => [
				'de' => 'statics/zf-pc/ZFCH-de',
				'en' => 'statics/zf-pc/ZFCH-en',
				'fr' => 'statics/zf-pc/ZFCH-fr',
			],
			'ZFPH'   => [
				'en' => 'statics/zf-pc/ZFPH-en',
			],
			'ZFIN'   => [
				'en' => 'statics/zf-pc/ZFIN-en',
			],
			'ZFSG'   => [
				'en' => 'statics/zf-pc/ZFSG-en',
			],
			'ZFMY'   => [
				'en' => 'statics/zf-pc/ZFMY-en',
			],
			'ZFAU'   => [
				'en' => 'statics/zf-pc/ZFAU-en',
			],
			'ZFAT'   => [
				'de' => 'statics/zf-pc/ZFAT-de'
			],
			'ZFMX'   => [
				'es' => 'statics/zf-pc/ZFMX-es',
			],
			'ZFZA'   => [
				'en' => 'statics/zf-pc/ZFZA-en',
			],
			'ZFBR'   => [
				'pt' => 'statics/zf-pc/ZFBR-pt'
			],
			'ZFTH'   => [
				'th' => 'statics/zf-pc/ZFTH-th',
			],
			'ZFID'   => [
				'id' => 'statics/zf-pc/ZFID-id',
			],
			'ZFTW'   => [
				'zh-tw' => 'statics/zf-pc/ZFTW-zh-tw'
			],
			'ZFAR'   => [
				'ar' => 'statics/zf-pc/ZFAR-ar'
			],
			'ZFIL'   => [
				'en' => 'statics/zf-pc/ZFIL-en'
			],
			'ZFRU'   => [
				'ru' => 'statics/zf-pc/ZFRU-ru'
			],
			'ZFHK'   => [
				'zh-tw' => 'statics/zf-pc/ZFHK-zh-tw'
			],
			'ZFTR'   => [
				'tr' => 'statics/zf-pc/ZFTR-tr'
			],
			'ZFMX01' => [
				'es' => 'statics/zf-pc/ZFMX01-es'
			],
			'ZFRO'   => [
				'ro' => 'statics/zf-pc/ZFRO-ro'
			],
			'ZFJP'   => [
				'ja' => 'statics/zf-pc/ZFJP-ja'
			],
		],
		'secondary_domain'      => [
			'ZF'     => [
				'en' => 'https://www' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFES'   => [
				'es' => 'https://es' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFFR'   => [
				'fr' => 'https://fr' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFDE'   => [
				'de' => 'https://de' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFPT'   => [
				'pt' => 'https://pt' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIT'   => [
				'it' => 'https://it' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIE'   => [
				'en' => 'https://eur' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFNZ'   => [
				'en' => 'https://nz' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFGB'   => [
				'en' => 'https://uk' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFCA'   => [
				'en' => 'https://ca' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ca' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBE'   => [
				'fr' => 'https://be' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFCH'   => [
				'de' => 'https://ch' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ch' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ch' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFPH'   => [
				'en' => 'https://ph' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIN'   => [
				'en' => 'https://in' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFSG'   => [
				'en' => 'https://sg' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFMY'   => [
				'en' => 'https://my' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAU'   => [
				'en' => 'https://au' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAT'   => [
				'de' => 'https://at' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX'   => [
				'es' => 'https://latam' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFZA'   => [
				'en' => 'https://za' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBR'   => [
				'pt' => 'https://br' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFTH'   => [
				'th' => 'https://th' . ZF_DOMAIN . '/promotion/release',
			],
			/*   'ZFID' => [
				   'id' => 'https://id' . ZF_DOMAIN . '/promotion/release',
			   ],*/
			'ZFTW'   => [
				'zh-tw' => 'https://tw' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFAR'   => [
				'ar' => 'https://ar' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIL'   => [
				'en' => 'https://il' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRU'   => [
				'ru' => 'https://ru' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'https://hk' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFTR'   => [
				'tr' => 'https://tr' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX01' => [
				'es' => 'https://mx' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRO'   => [
				'ro' => 'https://ro' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFJP'   => [
				'ja' => 'https://jp' . ZF_DOMAIN . '/promotion/release'
			],
		],
		'home_secondary_domain' => [
			'ZF'     => [
				'en' => 'https://www' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFES'   => [
				'es' => 'https://es' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFFR'   => [
				'fr' => 'https://fr' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFDE'   => [
				'de' => 'https://de' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFPT'   => [
				'pt' => 'https://pt' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFIT'   => [
				'it' => 'https://it' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFIE'   => [
				'en' => 'https://eur' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFNZ'   => [
				'en' => 'https://nz' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFGB'   => [
				'en' => 'https://uk' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFCA'   => [
				'en' => 'https://ca' . ZF_DOMAIN . '/promotion/home/release',
				'fr' => 'https://ca' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFBE'   => [
				'fr' => 'https://be' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFCH'   => [
				'de' => 'https://ch' . ZF_DOMAIN . '/promotion/home/release',
				'en' => 'https://ch' . ZF_DOMAIN . '/promotion/home/release',
				'fr' => 'https://ch' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFPH'   => [
				'en' => 'https://ph' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFIN'   => [
				'en' => 'https://in' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFSG'   => [
				'en' => 'https://sg' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFMY'   => [
				'en' => 'https://my' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFAU'   => [
				'en' => 'https://au' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFAT'   => [
				'de' => 'https://at' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFMX'   => [
				'es' => 'https://latam' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFZA'   => [
				'en' => 'https://za' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFBR'   => [
				'pt' => 'https://br' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFTH'   => [
				'th' => 'https://th' . ZF_DOMAIN . '/promotion/home/release',
			],
			/*
		   'ZFID' => [
			   'id' => 'https://id' . ZF_DOMAIN . '/promotion/home/release',
		   ],*/
			'ZFTW'   => [
				'zh-tw' => 'https://tw' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFAR'   => [
				'ar' => 'https://ar' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFIL'   => [
				'en' => 'https://il' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFRU'   => [
				'ru' => 'https://ru' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'https://hk' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFTR'   => [
				'tr' => 'https://tr' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFMX01' => [
				'es' => 'https://mx' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFRO'   => [
				'ro' => 'https://ro' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFJP'   => [
				'ja' => 'https://jp' . ZF_DOMAIN . '/promotion/home/release'
			],
		]
	],
	'zf-wap'     => [
        'closed_pipeline_lang' => [ // 停止运营的渠道语言
            'ZFSG' => ['id']
        ],
		's3PublishPath'         => [
			'ZF'     => [
				'en' => 'publish/m.zaful.com/en/release',
				'es' => 'publish/m.zaful.com/es/release',
			],
			'ZFES'   => [
				'es' => 'publish/es-m.zaful.com/es/release',
				'en' => 'publish/es-m.zaful.com/en/release',
				'fr' => 'publish/es-m.zaful.com/fr/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr-m.zaful.com/fr/release',
				'en' => 'publish/fr-m.zaful.com/en/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de-m.zaful.com/de/release',
				'en' => 'publish/de-m.zaful.com/en/release',
				'es' => 'publish/de-m.zaful.com/es/release',
			],
			'ZFIE'   => [
				'en' => 'publish/eur-m.zaful.com/en/release',
				'fr' => 'publish/eur-m.zaful.com/fr/release',
				'de' => 'publish/eur-m.zaful.com/de/release',
				'es' => 'publish/eur-m.zaful.com/es/release',
				'pt' => 'publish/eur-m.zaful.com/pt/release',
				'it' => 'publish/eur-m.zaful.com/it/release',
				'ru' => 'publish/eur-m.zaful.com/ru/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz-m.zaful.com/en/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk-m.zaful.com/en/release',
				'es' => 'publish/uk-m.zaful.com/es/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca-m.zaful.com/en/release',
				'fr' => 'publish/ca-m.zaful.com/fr/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be-m.zaful.com/fr/release',
				'en' => 'publish/be-m.zaful.com/en/release',
				'de' => 'publish/be-m.zaful.com/de/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch-m.zaful.com/de/release',
				'en' => 'publish/ch-m.zaful.com/en/release',
				'fr' => 'publish/ch-m.zaful.com/fr/release',
				'it' => 'publish/ch-m.zaful.com/it/release',
				'es' => 'publish/ch-m.zaful.com/es/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph-m.zaful.com/en/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in-m.zaful.com/en/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg-m.zaful.com/en/release',
				'th' => 'publish/sg-m.zaful.com/th/release',
				'id' => 'publish/sg-m.zaful.com/id/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my-m.zaful.com/en/release',
			],
			'ZFAU'   => [
				'en' => 'publish/au-m.zaful.com/en/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at-m.zaful.com/de/release',
				'en' => 'publish/at-m.zaful.com/en/release'
			],
			'ZFMX'   => [
				'es' => 'publish/latam-m.zaful.com/es/release',
				'en' => 'publish/latam-m.zaful.com/en/release'
			],
			'ZFZA'   => [
				'en' => 'publish/za-m.zaful.com/en/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br-m.zaful.com/pt/release',
				'en' => 'publish/br-m.zaful.com/en/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th-m.zaful.com/th/release',
				'en' => 'publish/th-m.zaful.com/en/release',
			],
			'ZFID'   => [
				'id' => 'publish/id-m.zaful.com/id/release',
				'en' => 'publish/id-m.zaful.com/en/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/release',
				'en' => 'publish/tw-m.zaful.com/en/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar-m.zaful.com/ar/release',
				'en' => 'publish/ar-m.zaful.com/en/release'
			],
			'ZFIT'   => [
				'it' => 'publish/it-m.zaful.com/it/release',
				'en' => 'publish/it-m.zaful.com/en/release',
				'de' => 'publish/it-m.zaful.com/de/release'
			],
			'ZFIL'   => [
				'en' => 'publish/il-m.zaful.com/en/release',
                'he' => 'publish/il-m.zaful.com/he/release'
			],
			'ZFRU'   => [
				'ru' => 'publish/ru-m.zaful.com/ru/release',
				'en' => 'publish/ru-m.zaful.com/en/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/release'
			],
			'ZFTR'   => [
				'tr' => 'publish/tr-m.zaful.com/tr/release',
				'en' => 'publish/tr-m.zaful.com/en/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx-m.zaful.com/es/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro-m.zaful.com/ro/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp-m.zaful.com/ja/release',
				'en' => 'publish/jp-m.zaful.com/en/release'
			],
			'ZFVN'   => [
				'vi' => 'publish/vn-m.zaful.com/vi/release',
				'en' => 'publish/vn-m.zaful.com/en/release'
			],
		],
		's3HomePublishPath'     => [
			'ZF'     => [
				'en' => 'publish/m.zaful.com/en/home/release',
			],
			'ZFES'   => [
				'es' => 'publish/es-m.zaful.com/es/home/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr-m.zaful.com/fr/home/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de-m.zaful.com/de/home/release',
			],
			'ZFIE'   => [
				'en' => 'publish/eur-m.zaful.com/en/home/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz-m.zaful.com/en/home/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk-m.zaful.com/en/home/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca-m.zaful.com/en/home/release',
				'fr' => 'publish/ca-m.zaful.com/fr/home/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be-m.zaful.com/fr/home/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch-m.zaful.com/de/home/release',
				'en' => 'publish/ch-m.zaful.com/en/home/release',
				'fr' => 'publish/ch-m.zaful.com/fr/home/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph-m.zaful.com/en/home/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in-m.zaful.com/en/home/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg-m.zaful.com/en/home/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my-m.zaful.com/en/home/release',
			],
			'ZFAU'   => [
				'en' => 'publish/au-m.zaful.com/en/home/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at-m.zaful.com/de/home/release'
			],
			'ZFMX'   => [
				'es' => 'publish/latam-m.zaful.com/es/home/release',
			],
			'ZFZA'   => [
				'en' => 'publish/za-m.zaful.com/en/home/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br-m.zaful.com/pt/home/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th-m.zaful.com/th/home/release',
			],
			'ZFID'   => [
				'id' => 'publish/id-m.zaful.com/id/home/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/home/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar-m.zaful.com/ar/home/release'
			],
			'ZFIL'   => [
				'en' => 'publish/il-m.zaful.com/en/home/release'
			],
			'ZFRU'   => [
				'ru' => 'publish/ru-m.zaful.com/ru/home/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/home/release'
			],
			'ZFTR'   => [
				'tr' => 'publish/tr-m.zaful.com/tr/home/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx-m.zaful.com/es/home/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro-m.zaful.com/ro/home/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp-m.zaful.com/ja/home/release'
			],
		],
		's3StaticPath'          => [
			'ZF'     => [
				'en' => 'statics/zf-wap/ZF-en',
				'es' => 'statics/zf-wap/ZF-es',
			],
			'ZFES'   => [
				'es' => 'statics/zf-wap/ZFES-es',
				'en' => 'statics/zf-wap/ZFES-en',
				'fr' => 'statics/zf-wap/ZFES-fr',
			],
			'ZFFR'   => [
				'fr' => 'statics/zf-wap/ZFFR-fr',
				'en' => 'statics/zf-wap/ZFFR-en',
			],
			'ZFDE'   => [
				'de' => 'statics/zf-wap/ZFDE-de',
				'en' => 'statics/zf-wap/ZFDE-en',
				'es' => 'statics/zf-wap/ZFDE-es',
			],
			'ZFIE'   => [
				'en' => 'statics/zf-wap/ZFIE-en',
				'fr' => 'statics/zf-wap/ZFIE-en',
				'de' => 'statics/zf-wap/ZFIE-en',
				'es' => 'statics/zf-wap/ZFIE-en',
				'pt' => 'statics/zf-wap/ZFIE-en',
				'it' => 'statics/zf-wap/ZFIE-en',
				'ru' => 'statics/zf-wap/ZFIE-en',
			],
			'ZFNZ'   => [
				'en' => 'statics/zf-wap/ZFNZ-en',
			],
			'ZFGB'   => [
				'en' => 'statics/zf-wap/ZFGB-en',
				'es' => 'statics/zf-wap/ZFGB-es',
			],
			'ZFCA'   => [
				'en' => 'statics/zf-wap/ZFCA-en',
				'fr' => 'statics/zf-wap/ZFCA-fr',
			],
			'ZFBE'   => [
				'fr' => 'statics/zf-wap/ZFBE-fr',
				'en' => 'statics/zf-wap/ZFBE-en',
				'de' => 'statics/zf-wap/ZFBE-de',
			],
			'ZFCH'   => [
				'de' => 'statics/zf-wap/ZFCH-de',
				'en' => 'statics/zf-wap/ZFCH-en',
				'fr' => 'statics/zf-wap/ZFCH-fr',
				'it' => 'statics/zf-wap/ZFCH-it',
				'es' => 'statics/zf-wap/ZFCH-es',
			],
			'ZFPH'   => [
				'en' => 'statics/zf-wap/ZFPH-en',
			],
			'ZFIN'   => [
				'en' => 'statics/zf-wap/ZFIN-en',
			],
			'ZFSG'   => [
				'en' => 'statics/zf-wap/ZFSG-en',
				'th' => 'statics/zf-wap/ZFSG-th',
				'id' => 'statics/zf-wap/ZFSG-id',
			],
			'ZFMY'   => [
				'en' => 'statics/zf-wap/ZFMY-en',
			],
			'ZFAU'   => [
				'en' => 'statics/zf-wap/ZFIE-en',
			],
			'ZFAT'   => [
				'de' => 'statics/zf-wap/ZFAT-de',
				'en' => 'statics/zf-wap/ZFAT-en'
			],
			'ZFMX'   => [
				'es' => 'statics/zf-wap/ZFMX-es',
				'en' => 'statics/zf-wap/ZFMX-en',
			],
			'ZFZA'   => [
				'en' => 'statics/zf-wap/ZFZA-en',
			],
			'ZFBR'   => [
				'pt' => 'statics/zf-wap/ZFBR-pt',
				'en' => 'statics/zf-wap/ZFBR-en'
			],
			'ZFTH'   => [
				'th' => 'statics/zf-wap/ZFTH-th',
				'en' => 'statics/zf-wap/ZFTH-en',
			],
			'ZFID'   => [
				'id' => 'statics/zf-wap/ZFID-id',
				'en' => 'statics/zf-wap/ZFID-en',
			],
			'ZFTW'   => [
				'zh-tw' => 'statics/zf-wap/ZFTW-zh-tw',
				'en' => 'statics/zf-wap/ZFTW-en'
			],
			'ZFAR'   => [
				'ar' => 'statics/zf-wap/ZFAR-ar',
				'en' => 'statics/zf-wap/ZFAR-en'
			],
			'ZFIT'   => [
				'it' => 'statics/zf-wap/ZFIT-it',
				'en' => 'statics/zf-wap/ZFIT-en',
				'de' => 'statics/zf-wap/ZFIT-de'
			],
			'ZFIL'   => [
				'en' => 'statics/zf-wap/ZFIL-en',
                'he' => 'statics/zf-wap/ZFIL-he'
			],
			'ZFRU'   => [
				'ru' => 'statics/zf-wap/ZFRU-ru',
				'en' => 'statics/zf-wap/ZFRU-en'
			],
			'ZFHK'   => [
				'zh-tw' => 'statics/zf-wap/ZFHK-zh-tw'
			],
			'ZFTR'   => [
				'tr' => 'statics/zf-wap/ZFTR-tr',
				'en' => 'statics/zf-wap/ZFTR-en'
			],
			'ZFMX01' => [
				'es' => 'statics/zf-wap/ZFMX01-es'
			],
			'ZFRO'   => [
				'ro' => 'statics/zf-wap/ZFRO-ro'
			],
			'ZFJP'   => [
				'ja' => 'statics/zf-wap/ZFJP-ja',
				'en' => 'statics/zf-wap/ZFJP-en'
			],
			'ZFVN'   => [
				'vi' => 'statics/zf-wap/ZFVN-vi',
				'en' => 'statics/zf-wap/ZFVN-en'
			],
		],
		'secondary_domain'      => [
			'ZF'   => [
				'en' => 'https://m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFES' => [
				'es' => 'https://es-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://es-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://es-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFFR' => [
				'fr' => 'https://fr-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://fr-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFDE' => [
				'de' => 'https://de-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://de-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://de-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIE' => [
				'en' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'pt' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'it' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'ru' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFNZ' => [
				'en' => 'https://nz-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFGB' => [
				'en' => 'https://uk-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://uk-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFCA' => [
				'en' => 'https://ca-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ca-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBE' => [
				'fr' => 'https://be-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://be-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://be-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFCH' => [
				'de' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'it' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFPH' => [
				'en' => 'https://ph-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIN' => [
				'en' => 'https://in-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFSG' => [
				'en' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release',
				'th' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release',
				'id' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMY' => [
				'en' => 'https://my-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAU' => [
				'en' => 'https://au-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAT' => [
				'de' => 'https://at-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://at-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX' => [
				'es' => 'https://latam-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://latam-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFZA' => [
				'en' => 'https://za-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBR' => [
				'pt' => 'https://br-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://br-m' . ZF_DOMAIN . '/promotion/release'
			],

			'ZFTH'   => [
				'th' => 'https://th-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://th-m' . ZF_DOMAIN . '/promotion/release'
			],
			/*    'ZFID' => [
					'id' => 'https://id-m' . ZF_DOMAIN . '/promotion/release',
				],*/
			'ZFTW'   => [
				'zh-tw' => 'https://tw-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://tw-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFAR'   => [
				'ar' => 'https://ar-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ar-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIT'   => [
				'it' => 'https://it-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://it-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://it-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIL'   => [
				'en' => 'https://il-m' . ZF_DOMAIN . '/promotion/release',
                'he' => 'https://il-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRU'   => [
				'ru' => 'https://ru-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ru-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'https://hk-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFTR'   => [
				'tr' => 'https://tr-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://tr-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX01' => [
				'es' => 'https://mx-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRO'   => [
				'ro' => 'https://ro-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFJP'   => [
				'ja' => 'https://jp-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://jp-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFVN'   => [
				'vi' => 'https://vn-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://vn-m' . ZF_DOMAIN . '/promotion/release'
			],
		],
		'home_secondary_domain' => [
			'ZF'     => [
				'en' => 'https://m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFES'   => [
				'es' => 'https://es-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFFR'   => [
				'fr' => 'https://fr-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFDE'   => [
				'de' => 'https://de-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFIE'   => [
				'en' => 'https://eur-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFNZ'   => [
				'en' => 'https://nz-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFGB'   => [
				'en' => 'https://uk-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFCA'   => [
				'en' => 'https://ca-m' . ZF_DOMAIN . '/promotion/home/release',
				'fr' => 'https://ca-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFBE'   => [
				'fr' => 'https://be-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFCH'   => [
				'de' => 'https://ch-m' . ZF_DOMAIN . '/promotion/home/release',
				'en' => 'https://ch-m' . ZF_DOMAIN . '/promotion/home/release',
				'fr' => 'https://ch-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFPH'   => [
				'en' => 'https://ph-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFIN'   => [
				'en' => 'https://in-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFSG'   => [
				'en' => 'https://sg-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFMY'   => [
				'en' => 'https://my-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFAU'   => [
				'en' => 'https://au-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFAT'   => [
				'de' => 'https://at-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFMX'   => [
				'es' => 'https://latam-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFZA'   => [
				'en' => 'https://za-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			'ZFBR'   => [
				'pt' => 'https://br-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFTH'   => [
				'th' => 'https://th-m' . ZF_DOMAIN . '/promotion/home/release',
			],
			/*  'ZFID' => [
				  'id' => 'https://id-m' . ZF_DOMAIN . '/promotion/home/release',
			  ],*/
			'ZFTW'   => [
				'zh-tw' => 'https://tw-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFAR'   => [
				'ar' => 'https://ar-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFIL'   => [
				'en' => 'https://il-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFRU'   => [
				'ru' => 'https://ru-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'https://hk-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFTR'   => [
				'tr' => 'https://tr-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFMX01' => [
				'es' => 'https://mx-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFRO'   => [
				'ro' => 'https://ro-m' . ZF_DOMAIN . '/promotion/home/release'
			],
			'ZFJP'   => [
				'ja' => 'https://jp-m' . ZF_DOMAIN . '/promotion/home/release'
			],
		]
	],
	'zf-app'     => [
        'closed_pipeline_lang' => [ // 停止运营的渠道语言
            'ZFSG' => ['id']
        ],
		's3PublishPath'         => [
			'ZF'     => [
				'en' => 'publish/m.zaful.com/en/app/release',
				'es' => 'publish/m.zaful.com/es/app/release',
			],
			'ZFES'   => [
				'es' => 'publish/es-m.zaful.com/es/app/release',
				'en' => 'publish/es-m.zaful.com/en/app/release',
				'fr' => 'publish/es-m.zaful.com/fr/app/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr-m.zaful.com/fr/app/release',
				'en' => 'publish/fr-m.zaful.com/en/app/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de-m.zaful.com/de/app/release',
				'en' => 'publish/de-m.zaful.com/en/app/release',
				'es' => 'publish/de-m.zaful.com/es/app/release',
			],
			'ZFIE'   => [
				'en' => 'publish/eur-m.zaful.com/en/app/release',
				'fr' => 'publish/eur-m.zaful.com/fr/app/release',
				'de' => 'publish/eur-m.zaful.com/de/app/release',
				'es' => 'publish/eur-m.zaful.com/es/app/release',
				'pt' => 'publish/eur-m.zaful.com/pt/app/release',
				'it' => 'publish/eur-m.zaful.com/it/app/release',
				'ru' => 'publish/eur-m.zaful.com/ru/app/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz-m.zaful.com/en/app/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk-m.zaful.com/en/app/release',
				'es' => 'publish/uk-m.zaful.com/es/app/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca-m.zaful.com/en/app/release',
				'fr' => 'publish/ca-m.zaful.com/fr/app/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be-m.zaful.com/fr/app/release',
				'en' => 'publish/be-m.zaful.com/en/app/release',
				'de' => 'publish/be-m.zaful.com/de/app/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch-m.zaful.com/de/app/release',
				'en' => 'publish/ch-m.zaful.com/en/app/release',
				'fr' => 'publish/ch-m.zaful.com/fr/app/release',
				'it' => 'publish/ch-m.zaful.com/it/app/release',
				'es' => 'publish/ch-m.zaful.com/es/app/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph-m.zaful.com/en/app/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in-m.zaful.com/en/app/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg-m.zaful.com/en/app/release',
				'th' => 'publish/sg-m.zaful.com/th/app/release',
				'id' => 'publish/sg-m.zaful.com/id/app/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my-m.zaful.com/en/app/release',
			],
			'ZFAU'   => [
				'en' => 'publish/au-m.zaful.com/en/app/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at-m.zaful.com/de/app/release',
				'en' => 'publish/at-m.zaful.com/en/app/release'
			],
			'ZFMX'   => [
				'es' => 'publish/latam-m.zaful.com/es/app/release',
				'en' => 'publish/latam-m.zaful.com/en/app/release',
			],
			'ZFZA'   => [
				'en' => 'publish/za-m.zaful.com/en/app/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br-m.zaful.com/pt/app/release',
				'en' => 'publish/br-m.zaful.com/en/app/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th-m.zaful.com/th/app/release',
				'en' => 'publish/th-m.zaful.com/en/app/release',
			],
			'ZFID'   => [
				'id' => 'publish/id-m.zaful.com/id/app/release',
				'en' => 'publish/id-m.zaful.com/en/app/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/app/release',
				'en'    => 'publish/tw-m.zaful.com/en/app/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar-m.zaful.com/ar/app/release',
				'en' => 'publish/ar-m.zaful.com/en/app/release'
			],
			'ZFIT'   => [
				'it' => 'publish/it-m.zaful.com/it/app/release',
				'en' => 'publish/it-m.zaful.com/en/app/release',
				'de' => 'publish/it-m.zaful.com/de/app/release'
			],
			'ZFIL'   => [
				'en' => 'publish/il-m.zaful.com/en/app/release',
                'he' => 'publish/il-m.zaful.com/he/app/release'
			],
			'ZFRU'   => [
				'ru' => 'publish/ru-m.zaful.com/ru/app/release',
				'en' => 'publish/ru-m.zaful.com/en/app/release'
			],
			'ZFTR'   => [
				'tr' => 'publish/tr-m.zaful.com/tr/app/release',
				'en' => 'publish/tr-m.zaful.com/en/app/release'
			],
			'ZFVN'   => [
				'vi' => 'publish/vn-m.zaful.com/vi/app/release',
				'en' => 'publish/vn-m.zaful.com/en/app/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp-m.zaful.com/ja/app/release',
				'en' => 'publish/jp-m.zaful.com/en/app/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/app/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx-m.zaful.com/es/app/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro-m.zaful.com/ro/app/release'
			],
		],
		's3HomePublishPath'     => [
			'ZF'     => [
				'en' => 'publish/m.zaful.com/en/app/home/release',
				'es' => 'publish/m.zaful.com/es/app/home/release',
			],
			'ZFES'   => [
				'es' => 'publish/es-m.zaful.com/es/app/home/release',
				'en' => 'publish/es-m.zaful.com/en/app/home/release',
				'fr' => 'publish/es-m.zaful.com/fr/app/home/release',
			],
			'ZFFR'   => [
				'fr' => 'publish/fr-m.zaful.com/fr/app/home/release',
				'en' => 'publish/fr-m.zaful.com/en/app/home/release',
			],
			'ZFDE'   => [
				'de' => 'publish/de-m.zaful.com/de/app/home/release',
				'en' => 'publish/de-m.zaful.com/en/app/home/release',
				'es' => 'publish/de-m.zaful.com/es/app/home/release',
			],
			'ZFIE'   => [
				'en' => 'publish/ie-m.zaful.com/en/app/home/release',
				'fr' => 'publish/ie-m.zaful.com/fr/app/home/release',
				'de' => 'publish/ie-m.zaful.com/de/app/home/release',
				'es' => 'publish/ie-m.zaful.com/es/app/home/release',
				'pt' => 'publish/ie-m.zaful.com/pt/app/home/release',
				'it' => 'publish/ie-m.zaful.com/it/app/home/release',
				'ru' => 'publish/ie-m.zaful.com/ru/app/home/release',
			],
			'ZFNZ'   => [
				'en' => 'publish/nz-m.zaful.com/en/app/home/release',
			],
			'ZFGB'   => [
				'en' => 'publish/uk-m.zaful.com/en/app/home/release',
				'es' => 'publish/uk-m.zaful.com/es/app/home/release',
			],
			'ZFCA'   => [
				'en' => 'publish/ca-m.zaful.com/en/app/home/release',
				'fr' => 'publish/ca-m.zaful.com/fr/app/home/release',
			],
			'ZFBE'   => [
				'fr' => 'publish/be-m.zaful.com/fr/app/home/release',
				'en' => 'publish/be-m.zaful.com/en/app/home/release',
				'de' => 'publish/be-m.zaful.com/de/app/home/release',
			],
			'ZFCH'   => [
				'de' => 'publish/ch-m.zaful.com/de/app/home/release',
				'en' => 'publish/ch-m.zaful.com/en/app/home/release',
				'fr' => 'publish/ch-m.zaful.com/fr/app/home/release',
				'it' => 'publish/ch-m.zaful.com/it/app/home/release',
				'es' => 'publish/ch-m.zaful.com/es/app/home/release',
			],
			'ZFPH'   => [
				'en' => 'publish/ph-m.zaful.com/en/app/home/release',
			],
			'ZFIN'   => [
				'en' => 'publish/in-m.zaful.com/en/app/home/release',
			],
			'ZFSG'   => [
				'en' => 'publish/sg-m.zaful.com/en/app/home/release',
				'th' => 'publish/sg-m.zaful.com/th/app/home/release',
				'id' => 'publish/sg-m.zaful.com/id/app/home/release',
			],
			'ZFMY'   => [
				'en' => 'publish/my-m.zaful.com/en/app/home/release',
			],
			'ZFAU'   => [
				'en' => 'publish/sz-m.zaful.com/de/app/home/release',
			],
			'ZFAT'   => [
				'de' => 'publish/at-m.zaful.com/de/app/home/release',
				'en' => 'publish/at-m.zaful.com/en/app/home/release'
			],
			'ZFMX'   => [
				'es' => 'publish/mx-m.zaful.com/es/app/home/release',
				'en' => 'publish/mx-m.zaful.com/en/app/home/release',
			],
			'ZFZA'   => [
				'en' => 'publish/za-m.zaful.com/en/app/home/release',
			],
			'ZFBR'   => [
				'pt' => 'publish/br-m.zaful.com/pt/app/home/release',
				'en' => 'publish/br-m.zaful.com/en/app/home/release'
			],
			'ZFTH'   => [
				'th' => 'publish/th-m.zaful.com/th/app/home/release',
			],
			'ZFID'   => [
				'id' => 'publish/id-m.zaful.com/id/app/home/release',
				'en' => 'publish/id-m.zaful.com/en/app/home/release',
			],
			'ZFTW'   => [
				'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/app/home/release',
				'en'    => 'publish/tw-m.zaful.com/en/app/home/release'
			],
			'ZFAR'   => [
				'ar' => 'publish/ar-m.zaful.com/ar/app/home/release',
				'en' => 'publish/ar-m.zaful.com/en/app/home/release'
			],
			'ZFVN'   => [
				'vi' => 'publish/vn-m.zaful.com/vi/app/home/release',
				'en' => 'publish/vn-m.zaful.com/en/app/home/release'
			],
			'ZFJP'   => [
				'ja' => 'publish/jp-m.zaful.com/ja/app/home/release',
				'en' => 'publish/jp-m.zaful.com/en/app/home/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/app/home/release'
			],
			'ZFMX01' => [
				'es' => 'publish/mx-m.zaful.com/es/app/home/release'
			],
			'ZFRO'   => [
				'ro' => 'publish/ro-m.zaful.com/ro/app/home/release'
			],
		],
		's3StaticPath'          => [
			'ZF'     => [
				'en' => 'statics/zf-wap/ZF-en',
				'es' => 'statics/zf-wap/ZF-es',
			],
			'ZFES'   => [
				'es' => 'statics/zf-wap/ZFES-es',
				'en' => 'statics/zf-wap/ZFES-en',
				'fr' => 'statics/zf-wap/ZFES-fr',
			],
			'ZFFR'   => [
				'fr' => 'statics/zf-wap/ZFFR-fr',
				'en' => 'statics/zf-wap/ZFFR-en',
			],
			'ZFDE'   => [
				'de' => 'statics/zf-wap/ZFDE-de',
				'en' => 'statics/zf-wap/ZFDE-en',
				'es' => 'statics/zf-wap/ZFDE-es',
			],
			'ZFIE'   => [
				'en' => 'statics/zf-wap/ZFIE-en',
				'fr' => 'statics/zf-wap/ZFIE-fr',
				'de' => 'statics/zf-wap/ZFIE-de',
				'es' => 'statics/zf-wap/ZFIE-es',
				'pt' => 'statics/zf-wap/ZFIE-pt',
				'it' => 'statics/zf-wap/ZFIE-it',
				'ru' => 'statics/zf-wap/ZFIE-ru',
			],
			'ZFNZ'   => [
				'en' => 'statics/zf-wap/ZFNZ-en',
			],
			'ZFGB'   => [
				'en' => 'statics/zf-wap/ZFGB-en',
				'es' => 'statics/zf-wap/ZFGB-es',
			],
			'ZFCA'   => [
				'en' => 'statics/zf-wap/ZFCA-en',
				'fr' => 'statics/zf-wap/ZFCA-fr',
			],
			'ZFBE'   => [
				'fr' => 'statics/zf-wap/ZFBE-fr',
				'en' => 'statics/zf-wap/ZFBE-en',
				'de' => 'statics/zf-wap/ZFBE-de',
			],
			'ZFCH'   => [
				'de' => 'statics/zf-wap/ZFCH-de',
				'en' => 'statics/zf-wap/ZFCH-en',
				'fr' => 'statics/zf-wap/ZFCH-fr',
				'it' => 'statics/zf-wap/ZFCH-it',
				'es' => 'statics/zf-wap/ZFCH-es',
			],
			'ZFPH'   => [
				'en' => 'statics/zf-wap/ZFPH-en',
			],
			'ZFIN'   => [
				'en' => 'statics/zf-wap/ZFIN-en',
			],
			'ZFSG'   => [
				'en' => 'statics/zf-wap/ZFSG-en',
				'th' => 'statics/zf-wap/ZFSG-th',
				'id' => 'statics/zf-wap/ZFSG-id',
			],
			'ZFMY'   => [
				'en' => 'statics/zf-wap/ZFMY-en',
			],
			'ZFAU'   => [
				'en' => 'statics/zf-wap/ZFAU-en',
			],
			'ZFAT'   => [
				'de' => 'statics/zf-wap/ZFAT-de',
				'en' => 'statics/zf-wap/ZFAT-en'
			],
			'ZFMX'   => [
				'es' => 'statics/zf-wap/ZFMX-es',
				'en' => 'statics/zf-wap/ZFMX-en',
			],
			'ZFZA'   => [
				'en' => 'statics/zf-wap/ZFZA-en',
			],
			'ZFBR'   => [
				'pt' => 'statics/zf-wap/ZFBR-pt',
				'en' => 'statics/zf-wap/ZFBR-en'
			],
			'ZFTH'   => [
				'th' => 'statics/zf-wap/ZFTH-th',
				'en' => 'statics/zf-wap/ZFTH-en',
			],
			'ZFID'   => [
				'id' => 'statics/zf-wap/ZFID-id',
				'en' => 'statics/zf-wap/ZFID-en',
			],
			'ZFTW'   => [
				'zh-tw' => 'statics/zf-wap/ZFTW-zh-tw',
				'en' => 'statics/zf-wap/ZFTW-en'
			],
			'ZFAR'   => [
				'ar' => 'statics/zf-wap/ZFAR-ar',
				'en' => 'statics/zf-wap/ZFAR-en'
			],
			'ZFIT'   => [
				'it' => 'statics/zf-wap/ZFIT-it',
				'en' => 'statics/zf-wap/ZFIT-en',
				'de' => 'statics/zf-wap/ZFIT-de'
			],
			'ZFIL'   => [
				'en' => 'statics/zf-wap/ZFIL-en',
                'he' => 'statics/zf-wap/ZFIL-he'
			],
			'ZFRU'   => [
				'ru' => 'statics/zf-wap/ZFRU-ru',
				'en' => 'statics/zf-wap/ZFRU-en'
			],
			'ZFTR'   => [
				'tr' => 'statics/zf-wap/ZFTR-tr',
				'en' => 'statics/zf-wap/ZFTR-en'
			],
			'ZFVN'   => [
				'vi' => 'statics/zf-wap/ZFVN-vi',
				'en' => 'statics/zf-wap/ZFVN-en'
			],
			'ZFJP'   => [
				'ja' => 'statics/zf-wap/ZFJP-ja',
				'en' => 'statics/zf-wap/ZFJP-en'
			],
			'ZFHK'   => [
				'zh-tw' => 'statics/zf-wap/ZFHK-zh-tw'
			],
			'ZFMX01' => [
				'es' => 'statics/zf-wap/ZFMX01-es'
			],
			'ZFRO'   => [
				'ro' => 'statics/zf-wap/ZFRO-ro'
			],
		],
		'secondary_domain'      => [
			'ZF'     => [
				'en' => 'https://m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFES'   => [
				'es' => 'https://es-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://es-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://es-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFFR'   => [
				'fr' => 'https://fr-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://fr-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFDE'   => [
				'de' => 'https://de-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://de-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://de-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIE'   => [
				'en' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'pt' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'it' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
				'ru' => 'https://eur-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFNZ'   => [
				'en' => 'https://nz-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFGB'   => [
				'en' => 'https://uk-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://uk-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFCA'   => [
				'en' => 'https://ca-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ca-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBE'   => [
				'fr' => 'https://be-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://be-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://be-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFCH'   => [
				'de' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'fr' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'it' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
				'es' => 'https://ch-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFPH'   => [
				'en' => 'https://ph-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFIN'   => [
				'en' => 'https://in-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFSG'   => [
				'en' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release',
				'th' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release',
				'id' => 'https://sg-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFMY'   => [
				'en' => 'https://my-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAU'   => [
				'en' => 'https://au-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFAT'   => [
				'de' => 'https://at-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://at-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX'   => [
				'es' => 'https://latam-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://latam-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFZA'   => [
				'en' => 'https://za-m' . ZF_DOMAIN . '/promotion/release',
			],
			'ZFBR'   => [
				'pt' => 'https://br-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://br-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFTH'   => [
				'th' => 'https://th-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://th-m' . ZF_DOMAIN . '/promotion/release',
			],
			/*  'ZFID' => [
				  'id' => 'https://id-m' . ZF_DOMAIN . '/promotion/release',
			  ],*/
			'ZFTW'   => [
				'zh-tw' => 'https://tw-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://tw-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFAR'   => [
				'ar' => 'https://ar-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ar-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIT'   => [
				'it' => 'https://it-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://it-m' . ZF_DOMAIN . '/promotion/release',
				'de' => 'https://it-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFIL'   => [
				'en' => 'https://il-m' . ZF_DOMAIN . '/promotion/release',
                'he' => 'https://il-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRU'   => [
				'ru' => 'https://ru-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://ru-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFTR'   => [
				'tr' => 'https://tr-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'https://tr-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFVN'   => [
				'vi' => 'http://vn-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'http://vn-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFJP'   => [
				'ja' => 'http://jp-m' . ZF_DOMAIN . '/promotion/release',
				'en' => 'http://jp-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFHK'   => [
				'zh-tw' => 'https://hk-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFMX01' => [
				'es' => 'http://mx-m' . ZF_DOMAIN . '/promotion/release'
			],
			'ZFRO'   => [
				'ro' => 'http://ro-m' . ZF_DOMAIN . '/promotion/release'
			],
		],
		'home_secondary_domain' => [
			'ZF'     => [
				'en' => 'https://m' . ZF_DOMAIN . '/promotion/app/home/release',
				'es' => 'https://m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFES'   => [
				'es' => 'https://es-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'https://es-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'fr' => 'https://es-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFFR'   => [
				'fr' => 'https://fr-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'https://fr-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFDE'   => [
				'de' => 'https://de-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'https://de-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'es' => 'https://de-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFIE'   => [
				'en' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'fr' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'de' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'es' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'pt' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'it' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'ru' => 'https://eur-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFNZ'   => [
				'en' => 'https://nz-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFGB'   => [
				'en' => 'https://uk-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'es' => 'https://uk-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFCA'   => [
				'en' => 'https://ca-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'fr' => 'https://ca-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFBE'   => [
				'fr' => 'https://be-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'https://be-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'de' => 'https://be-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFCH'   => [
				'de' => 'https://ch-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'https://ch-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'fr' => 'https://ch-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'it' => 'https://ch-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'es' => 'https://ch-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFPH'   => [
				'en' => 'https://ph-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFIN'   => [
				'en' => 'https://in-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFSG'   => [
				'en' => 'https://sg-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'th' => 'https://sg-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'id' => 'https://sg-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFMY'   => [
				'en' => 'https://my-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFAU'   => [
				'en' => 'https://au-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFAT'   => [
				'de' => 'http://at-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'http://at-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFMX'   => [
				'es' => 'http://latam-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'http://latam-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFZA'   => [
				'en' => 'http://za-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFBR'   => [
				'pt' => 'http://br-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'http://br-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			/*
			'ZFTH' => [
				'th' => 'http://th-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFID' => [
				'id' => 'http://id-m' . ZF_DOMAIN . '/promotion/app/home/release',
			],
			'ZFTW' => [
				'zh-tw' => 'http://tw-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFAR' => [
				'ar' => 'https://ar-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],*/
			'ZFVN'   => [
				'vi' => 'http://vn-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'http://vn-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFJP'   => [
				'ja' => 'http://jp-m' . ZF_DOMAIN . '/promotion/app/home/release',
				'en' => 'http://jp-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFHk'   => [
				'zh-tw' => 'http://hk-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFMX01' => [
				'es' => 'http://mx-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
			'ZFRO'   => [
				'ro' => 'http://ro-m' . ZF_DOMAIN . '/promotion/app/home/release'
			],
		]
	],

	/**************************************** GB *******************************************/
	'gb-pc'      => [
		's3PublishPath'         => [
			'GB'    => [
				'en' => 'publish/gb-pc/www' . GB_DOMAIN . '/activity/release'
			],
			'GBES'  => ['ep' => 'publish/gb-pc/es' . GB_DOMAIN . '/activity/release'],
			'GBFR'  => ['fr' => 'publish/gb-pc/fr' . GB_DOMAIN . '/activity/release'],
			'GBRU'  => ['ru' => 'publish/gb-pc/ru' . GB_DOMAIN . '/activity/release'],
			'GBPT'  => ['po' => 'publish/gb-pc/pt' . GB_DOMAIN . '/activity/release'],
			'GBIT'  => ['it' => 'publish/gb-pc/it' . GB_DOMAIN . '/activity/release'],
			'GBDE'  => ['de' => 'publish/gb-pc/de' . GB_DOMAIN . '/activity/release'],
			'GBUK'  => ['en' => 'publish/gb-pc/uk' . GB_DOMAIN . '/activity/release'],
			'GBUS'  => ['en' => 'publish/gb-pc/us' . GB_DOMAIN . '/activity/release'],
			'GBBR'  => ['pt-br' => 'publish/gb-pc/br' . GB_DOMAIN . '/activity/release'],
			'GBTR'  => ['tr' => 'publish/gb-pc/tr' . GB_DOMAIN . '/activity/release'],
			'GBMX'  => ['ep-mx' => 'publish/gb-pc/mx' . GB_DOMAIN . '/activity/release'],
			'GBMA'  => ['fr' => 'publish/gb-pc/ma' . GB_DOMAIN . '/activity/release'],
			'GBGR'  => ['el' => 'publish/gb-pc/gr' . GB_DOMAIN . '/activity/release'],
			'GBHU'  => ['hu' => 'publish/gb-pc/hu' . GB_DOMAIN . '/activity/release'],
			'GBNL'  => ['nl' => 'publish/gb-pc/nl' . GB_DOMAIN . '/activity/release'],
			'GBSK'  => ['sk' => 'publish/gb-pc/sk' . GB_DOMAIN . '/activity/release'],
			'GBRO'  => ['ro' => 'publish/gb-pc/ro' . GB_DOMAIN . '/activity/release'],
			'GBCZ'  => ['cs' => 'publish/gb-pc/cz' . GB_DOMAIN . '/activity/release'],
			'GBAU'  => ['en' => 'publish/gb-pc/au' . GB_DOMAIN . '/activity/release'],
			'GBIN'  => ['en' => 'publish/gb-pc/in' . GB_DOMAIN . '/activity/release'],
			'GBJP'  => ['ja' => 'publish/gb-pc/jp' . GB_DOMAIN . '/activity/release'],
			/*'GBUA' => ['uk' => 'publish/gb-pc/ua' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-pc/jp' . GB_DOMAIN . '/activity/release'],
			'GBIL' => ['he' => 'publish/gb-pc/il' . GB_DOMAIN . '/activity/release'],
			'GBKZ' => ['kk' => 'publish/gb-pc/kz' . GB_DOMAIN . '/activity/release'],
			'GBTH' => ['th' => 'publish/gb-pc/th' . GB_DOMAIN . '/activity/release'],
			'GBVN' => ['vi' => 'publish/gb-pc/vn' . GB_DOMAIN . '/activity/release'],
			'GBID' => ['id' => 'publish/gb-pc/id' . GB_DOMAIN . '/activity/release'],*/
			'GBPL'  => ['pl' => 'publish/gb-pc/pl' . GB_DOMAIN . '/activity/release'],
			'GBSTY' => ['en' => 'publish/gb-pc/stylebest' . GB_DOMAIN . '/activity/release'],
			'GBGAG' => ['en' => 'publish/gb-pc/gagabop' . GB_DOMAIN . '/activity/release'],
			'GBCOZ' => ['en' => 'publish/gb-pc/cozyvoices' . GB_DOMAIN . '/activity/release'],
		],
		'ad_s3PublishPath'      => [
			'en'    => 'publish/www' . GB_DOMAIN . '/ad/release',
			'ep'    => 'publish/es' . GB_DOMAIN . '/ad/release',
			'fr'    => 'publish/fr' . GB_DOMAIN . '/ad/release',
			'ru'    => 'publish/ru' . GB_DOMAIN . '/ad/release',
			'po'    => 'publish/pt' . GB_DOMAIN . '/ad/release',
			'it'    => 'publish/it' . GB_DOMAIN . '/ad/release',
			'de'    => 'publish/de' . GB_DOMAIN . '/ad/release',
			'en_gb' => 'publish/ak' . GB_DOMAIN . '/ad/release',
			'en_us' => 'publish/us' . GB_DOMAIN . '/ad/release',
			'en_au' => 'publish/au' . GB_DOMAIN . '/ad/release',
			'pt_br' => 'publish/br' . GB_DOMAIN . '/ad/release',
			'tr'    => 'publish/tr' . GB_DOMAIN . '/ad/release',
			'pl'    => 'publish/pl' . GB_DOMAIN . '/ad/release',
			'en_in' => 'publish/in' . GB_DOMAIN . '/ad/release',
			'el'    => 'publish/el' . GB_DOMAIN . '/ad/release',
			'ep-mx' => 'publish/mx' . GB_DOMAIN . '/ad/release',
			'hu'    => 'publish/hu' . GB_DOMAIN . '/ad/release',
			'sk'    => 'publish/sk' . GB_DOMAIN . '/ad/release',
			'cs'    => 'publish/cs' . GB_DOMAIN . '/ad/release',
			'nl'    => 'publish/nl' . GB_DOMAIN . '/ad/release',
			'ro'    => 'publish/ro' . GB_DOMAIN . '/ad/release',
			'fr_ma' => 'publish/ma' . GB_DOMAIN . '/ad/release',
			//'ja'    => 'publish/jp' . GB_DOMAIN . '/ad/release',
			//'uk'    => 'publish/ua' . GB_DOMAIN . '/ad/release',
			//'vi'    => 'publish/vn' . GB_DOMAIN . '/ad/release',
			//'he'    => 'publish/il' . GB_DOMAIN . '/ad/release',
			//'kk'    => 'publish/kz' . GB_DOMAIN . '/ad/release',
			//'th'    => 'publish/th' . GB_DOMAIN . '/ad/release',
			//'id'    => 'publish/id' . GB_DOMAIN . '/ad/release',
		],
		'ad_s3StaticPath'       => [
			'en'    => 'statics/gb-pc/www',
			'ep'    => 'statics/gb-pc/es',
			'fr'    => 'statics/gb-pc/fr',
			'ru'    => 'statics/gb-pc/ru',
			'po'    => 'statics/gb-pc/pt',
			'it'    => 'statics/gb-pc/it',
			'de'    => 'statics/gb-pc/de',
			'en_gb' => 'statics/gb-pc/ak',
			'en_us' => 'statics/gb-pc/us',
			'en_au' => 'statics/gb-pc/au',
			'pt_br' => 'statics/gb-pc/br',
			'tr'    => 'statics/gb-pc/tr',
			'pl'    => 'statics/gb-pc/pl',
			'en_in' => 'statics/gb-pc/in',
			'el'    => 'statics/gb-pc/el',
			'ep-mx' => 'statics/gb-pc/mx',
			'hu'    => 'statics/gb-pc/hu',
			'sk'    => 'statics/gb-pc/sk',
			'cs'    => 'statics/gb-pc/cs',
			'nl'    => 'statics/gb-pc/nl',
			'ro'    => 'statics/gb-pc/ro',
			'fr_ma' => 'statics/gb-pc/ma',
			//'ja'    => 'statics/gb-pc/jp',
			//'uk'    => 'statics/gb-pc/ua',
			//'vi'    => 'statics/gb-pc/vn',
			//'he'    => 'statics/gb-pc/il',
			//'kk'    => 'statics/gb-pc/kz',
			//'th'    => 'statics/gb-pc/th',
			//'id'    => 'statics/gb-pc/id',
		],
		's3StaticPath'          => [
			'GB'    => [
				'en' => 'statics/gb-pc/GB-en'
			],
			'GBES'  => ['ep' => 'statics/gb-pc/GBES-ep'],
			'GBFR'  => ['fr' => 'statics/gb-pc/GBFR-fr'],
			'GBRU'  => ['ru' => 'statics/gb-pc/GBRU-ru'],
			'GBPT'  => ['po' => 'statics/gb-pc/GBPT-po'],
			'GBIT'  => ['it' => 'statics/gb-pc/GBIT-it'],
			'GBDE'  => ['de' => 'statics/gb-pc/GBDE-de'],
			'GBUK'  => ['en' => 'statics/gb-pc/GBUK-en'],
			'GBUS'  => ['en' => 'statics/gb-pc/GBUS-en'],
			'GBBR'  => ['pt-br' => 'statics/gb-pc/GBBR-pt-br'],
			'GBTR'  => ['tr' => 'statics/gb-pc/GBTR-tr'],
			'GBMX'  => ['ep-mx' => 'statics/gb-pc/GBMX-ep-mx'],
			'GBMA'  => ['fr' => 'statics/gb-pc/GBMA-fr'],
			'GBGR'  => ['el' => 'statics/gb-pc/GBGR-el'],
			'GBHU'  => ['hu' => 'statics/gb-pc/GBHU-hu'],
			'GBNL'  => ['nl' => 'statics/gb-pc/GBNL-nl'],
			'GBSK'  => ['sk' => 'statics/gb-pc/GBSK-sk'],
			'GBRO'  => ['ro' => 'statics/gb-pc/GBRO-ro'],
			'GBCZ'  => ['cs' => 'statics/gb-pc/GBCZ-cs'],
			'GBAU'  => ['en' => 'statics/gb-pc/GBAU-en'],
			'GBIN'  => ['en' => 'statics/gb-pc/GBIN-en'],
			'GBJP'  => ['ja' => 'statics/gb-pc/GBJP-ja'],
			/*'GBUA' => ['uk' => 'statics/gb-pc/GBUA-uk'],
			'GBJP' => ['ja' => 'statics/gb-pc/GBJP-ja'],
			'GBIL' => ['he' => 'statics/gb-pc/GBIL-he'],
			'GBKZ' => ['kk' => 'statics/gb-pc/GBKZ-kk'],
			'GBTH' => ['th' => 'statics/gb-pc/GBTH-th'],
			'GBVN' => ['vi' => 'statics/gb-pc/GBVN-vi'],
			'GBID' => ['id' => 'statics/gb-pc/GBID-id'],*/
			'GBPL'  => ['pl' => 'statics/gb-pc/GBPL-pl'],
			'GBSTY' => ['en' => 'statics/gb-pc/GBSTY-en'],
			'GBGAG' => ['en' => 'statics/gb-pc/GBGAG-en'],
			'GBCOZ' => ['en' => 'statics/gb-pc/GBCOZ-en'],
		],
		'secondary_domain'      => [
			'GB'    => [
				'en' => 'https://www' . GB_DOMAIN . '/promotion/release'
			],
			'GBES'  => ['ep' => 'https://es' . GB_DOMAIN . '/promotion/release'],
			'GBFR'  => ['fr' => 'https://fr' . GB_DOMAIN . '/promotion/release'],
			'GBRU'  => ['ru' => 'https://ru' . GB_DOMAIN . '/promotion/release'],
			'GBPT'  => ['po' => 'https://pt' . GB_DOMAIN . '/promotion/release'],
			'GBIT'  => ['it' => 'https://it' . GB_DOMAIN . '/promotion/release'],
			'GBDE'  => ['de' => 'https://de' . GB_DOMAIN . '/promotion/release'],
			'GBUK'  => ['en' => 'https://uk' . GB_DOMAIN . '/promotion/release'],
			'GBUS'  => ['en' => 'https://us' . GB_DOMAIN . '/promotion/release'],
			'GBBR'  => ['pt-br' => 'https://br' . GB_DOMAIN . '/promotion/release'],
			'GBTR'  => ['tr' => 'https://tr' . GB_DOMAIN . '/promotion/release'],
			'GBMX'  => ['ep-mx' => 'https://mx' . GB_DOMAIN . '/promotion/release'],
			'GBMA'  => ['fr' => 'https://ma' . GB_DOMAIN . '/promotion/release'],
			'GBGR'  => ['el' => 'https://gr' . GB_DOMAIN . '/promotion/release'],
			'GBHU'  => ['hu' => 'https://hu' . GB_DOMAIN . '/promotion/release'],
			'GBNL'  => ['nl' => 'https://nl' . GB_DOMAIN . '/promotion/release'],
			'GBSK'  => ['sk' => 'https://sk' . GB_DOMAIN . '/promotion/release'],
			'GBRO'  => ['ro' => 'https://ro' . GB_DOMAIN . '/promotion/release'],
			'GBCZ'  => ['cs' => 'https://cz' . GB_DOMAIN . '/promotion/release'],
			'GBAU'  => ['en' => 'https://au' . GB_DOMAIN . '/promotion/release'],
			'GBIN'  => ['en' => 'https://in' . GB_DOMAIN . '/promotion/release'],
			'GBJP'  => ['ja' => 'https://jp' . GB_DOMAIN . '/promotion/release'],
			/*'GBUA' => ['uk' => 'https://ua' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://jp' . GB_DOMAIN . '/promotion/release'],
			'GBIL' => ['he' => 'https://il' . GB_DOMAIN . '/promotion/release'],
			'GBKZ' => ['kk' => 'https://kz' . GB_DOMAIN . '/promotion/release'],
			'GBTH' => ['th' => 'https://th' . GB_DOMAIN . '/promotion/release'],
			'GBVN' => ['vi' => 'https://vn' . GB_DOMAIN . '/promotion/release'],
			'GBID' => ['id' => 'https://id' . GB_DOMAIN . '/promotion/release'],*/
			'GBPL'  => ['pl' => 'https://pl' . GB_DOMAIN . '/promotion/release'],
			'GBSTY' => ['en' => 'https://stylebest' . GB_DOMAIN . '/promotion/release'],
			'GBGAG' => ['en' => 'https://gagabop' . GB_DOMAIN . '/promotion/release'],
			'GBCOZ' => ['en' => 'https://cozyvoices' . GB_DOMAIN . '/promotion/release'],
		],
		'ad_secondary_domain'   => [
			'en'    => 'https://www' . GB_DOMAIN . '/selection/release',
			'ep-mx' => 'https://mx' . GB_DOMAIN . '/selection/release',
			'pt_br' => 'https://br' . GB_DOMAIN . '/selection/release',
			'tr'    => 'https://tr' . GB_DOMAIN . '/selection/release',
			'it'    => 'https://it' . GB_DOMAIN . '/selection/release',
			'de'    => 'https://de' . GB_DOMAIN . '/selection/release',
			'ru'    => 'https://ru' . GB_DOMAIN . '/selection/release',
			'po'    => 'https://pt' . GB_DOMAIN . '/selection/release',
			'ep'    => 'https://es' . GB_DOMAIN . '/selection/release',
			'fr'    => 'https://fr' . GB_DOMAIN . '/selection/release',
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . GB_DOMAIN . '/promotion/release/home',
		]
	],
	'gb-wap'     => [
		's3PublishPath'         => [
			'GB'    => [
				'en'    => 'publish/gb-wap/m' . GB_DOMAIN . '/en/activity/release',
				'ep'    => 'publish/gb-wap/m' . GB_DOMAIN . '/ep/activity/release',
				'fr'    => 'publish/gb-wap/m' . GB_DOMAIN . '/fr/activity/release',
				'ru'    => 'publish/gb-wap/m' . GB_DOMAIN . '/ru/activity/release',
				'de'    => 'publish/gb-wap/m' . GB_DOMAIN . '/de/activity/release',
				'tr'    => 'publish/gb-wap/m' . GB_DOMAIN . '/tr/activity/release',
				'pt-br' => 'publish/gb-wap/m' . GB_DOMAIN . '/pt-br/activity/release',
				'po'    => 'publish/gb-wap/m' . GB_DOMAIN . '/po/activity/release'
			],
			'GBES'  => ['ep' => 'publish/gb-wap/m-es' . GB_DOMAIN . '/activity/release'],
			'GBFR'  => ['fr' => 'publish/gb-wap/m-fr' . GB_DOMAIN . '/activity/release'],
			'GBRU'  => ['ru' => 'publish/gb-wap/m-ru' . GB_DOMAIN . '/activity/release'],
			'GBPT'  => ['po' => 'publish/gb-wap/m-pt' . GB_DOMAIN . '/activity/release'],
			'GBIT'  => ['it' => 'publish/gb-wap/m-it' . GB_DOMAIN . '/activity/release'],
			'GBDE'  => ['de' => 'publish/gb-wap/m-de' . GB_DOMAIN . '/activity/release'],
			'GBUK'  => ['en' => 'publish/gb-wap/m-uk' . GB_DOMAIN . '/activity/release'],
			'GBUS'  => ['en' => 'publish/gb-wap/m-us' . GB_DOMAIN . '/activity/release'],
			'GBBR'  => ['pt-br' => 'publish/gb-wap/m-br' . GB_DOMAIN . '/activity/release'],
			'GBTR'  => ['tr' => 'publish/gb-wap/m-tr' . GB_DOMAIN . '/activity/release'],
			'GBMX'  => ['ep-mx' => 'publish/gb-wap/m-mx' . GB_DOMAIN . '/activity/release'],
			'GBMA'  => ['fr' => 'publish/gb-wap/m-ma' . GB_DOMAIN . '/activity/release'],
			'GBGR'  => ['el' => 'publish/gb-wap/m-gr' . GB_DOMAIN . '/activity/release'],
			'GBHU'  => ['hu' => 'publish/gb-wap/m-hu' . GB_DOMAIN . '/activity/release'],
			'GBNL'  => ['nl' => 'publish/gb-wap/m-nl' . GB_DOMAIN . '/activity/release'],
			'GBSK'  => ['sk' => 'publish/gb-wap/m-sk' . GB_DOMAIN . '/activity/release'],
			'GBRO'  => ['ro' => 'publish/gb-wap/m-ro' . GB_DOMAIN . '/activity/release'],
			'GBCZ'  => ['cs' => 'publish/gb-wap/m-cz' . GB_DOMAIN . '/activity/release'],
			'GBAU'  => ['en' => 'publish/gb-wap/m-au' . GB_DOMAIN . '/activity/release'],
			'GBIN'  => ['en' => 'publish/gb-wap/m-in' . GB_DOMAIN . '/activity/release'],
			'GBJP'  => ['ja' => 'publish/gb-wap/m-jp' . GB_DOMAIN . '/activity/release'],
			/*'GBUA' => ['uk' => 'publish/gb-wap/m-ua' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-wap/m-jp' . GB_DOMAIN . '/activity/release'],
			'GBIL' => ['he' => 'publish/gb-wap/m-il' . GB_DOMAIN . '/activity/release'],
			'GBKZ' => ['kk' => 'publish/gb-wap/m-kz' . GB_DOMAIN . '/activity/release'],
			'GBTH' => ['th' => 'publish/gb-wap/m-th' . GB_DOMAIN . '/activity/release'],
			'GBVN' => ['vi' => 'publish/gb-wap/m-vn' . GB_DOMAIN . '/activity/release'],
			'GBID' => ['id' => 'publish/gb-wap/m-id' . GB_DOMAIN . '/activity'/release],*/
			'GBPL'  => ['pl' => 'publish/gb-wap/m-pl' . GB_DOMAIN . '/activity/release'],
			'GBSTY' => ['en' => 'publish/gb-wap/m-stylebest' . GB_DOMAIN . '/activity/release'],
			'GBGAG' => ['en' => 'publish/gb-wap/m-gagabop' . GB_DOMAIN . '/activity/release'],
			'GBCOZ' => ['en' => 'publish/gb-wap/m-cozyvoices' . GB_DOMAIN . '/activity/release'],
		],
		'ad_s3PublishPath'      => [
			'en'    => 'publish/m' . GB_DOMAIN . '/ad/release',
			'ep'    => 'publish/m-es' . GB_DOMAIN . '/ad/release',
			'fr'    => 'publish/m-fr' . GB_DOMAIN . '/ad/release',
			'ru'    => 'publish/m-ru' . GB_DOMAIN . '/ad/release',
			'po'    => 'publish/m-pt' . GB_DOMAIN . '/ad/release',
			'it'    => 'publish/m-it' . GB_DOMAIN . '/ad/release',
			'de'    => 'publish/m-de' . GB_DOMAIN . '/ad/release',
			'en_gb' => 'publish/m-ak' . GB_DOMAIN . '/ad/release',
			'en_us' => 'publish/m-us' . GB_DOMAIN . '/ad/release',
			'en_au' => 'publish/m-au' . GB_DOMAIN . '/ad/release',
			'pt_br' => 'publish/m-br' . GB_DOMAIN . '/ad/release',
			'tr'    => 'publish/m-tr' . GB_DOMAIN . '/ad/release',
			'pl'    => 'publish/m-pl' . GB_DOMAIN . '/ad/release',
			'en_in' => 'publish/m-in' . GB_DOMAIN . '/ad/release',
			'el'    => 'publish/m-el' . GB_DOMAIN . '/ad/release',
			'ep-mx' => 'publish/m-mx' . GB_DOMAIN . '/ad/release',
			'hu'    => 'publish/m-hu' . GB_DOMAIN . '/ad/release',
			'sk'    => 'publish/m-sk' . GB_DOMAIN . '/ad/release',
			'cs'    => 'publish/m-cs' . GB_DOMAIN . '/ad/release',
			'nl'    => 'publish/m-nl' . GB_DOMAIN . '/ad/release',
			'ro'    => 'publish/m-ro' . GB_DOMAIN . '/ad/release',
			'fr_ma' => 'publish/m-ma' . GB_DOMAIN . '/ad/release',
			//'ja'    => 'publish/m-jp' . GB_DOMAIN .'/ad/release',
			//'uk'    => 'publish/m-ua' . GB_DOMAIN .'/ad/release',
			//'he'    => 'publish/m-il' . GB_DOMAIN .'/ad/release',
			//'kk'    => 'publish/m-kz' . GB_DOMAIN .'/ad/release',
			//'th'    => 'publish/m-th' . GB_DOMAIN .'/ad/release',
			//'id'    => 'publish/m-id' . GB_DOMAIN .'/ad/release',
		],
		'ad_s3StaticPath'       => [
			'en'    => 'statics/gb-wap/m',
			'ep'    => 'statics/gb-wap/es',
			'fr'    => 'statics/gb-wap/fr',
			'ru'    => 'statics/gb-wap/ru',
			'po'    => 'statics/gb-wap/pt',
			'it'    => 'statics/gb-wap/it',
			'de'    => 'statics/gb-wap/de',
			'en_gb' => 'statics/gb-wap/ak',
			'en_us' => 'statics/gb-wap/us',
			'en_au' => 'statics/gb-wap/au',
			'pt_br' => 'statics/gb-wap/br',
			'tr'    => 'statics/gb-wap/tr',
			'pl'    => 'statics/gb-wap/pl',
			'en_in' => 'statics/gb-wap/in',
			'el'    => 'statics/gb-wap/el',
			'ep-mx' => 'statics/gb-wap/mx',
			'hu'    => 'statics/gb-wap/hu',
			'sk'    => 'statics/gb-wap/sk',
			'cs'    => 'statics/gb-wap/cs',
			'nl'    => 'statics/gb-wap/nl',
			'ro'    => 'statics/gb-wap/ro',
			'fr_ma' => 'statics/gb-wap/ma',
			//'ja'    => 'statics/gb-wap/jp',
			//'uk'    => 'statics/gb-wap/ua',
			//'he'    => 'statics/gb-wap/il',
			//'kk'    => 'statics/gb-wap/kz',
			//'th'    => 'statics/gb-wap/th',
			//'id'    => 'statics/gb-wap/id',
		],
		's3StaticPath'          => [
			'GB'    => [
				'en'    => 'statics/gb-wap/GB-en',
				'ep'    => 'statics/gb-wap/GB-ep',
				'fr'    => 'statics/gb-wap/GB-fr',
				'ru'    => 'statics/gb-wap/GB-ru',
				'de'    => 'statics/gb-wap/GB-de',
				'tr'    => 'statics/gb-wap/GB-tr',
				'po'    => 'statics/gb-wap/GB-po',
				'pt-br' => 'statics/gb-wap/GB-pt-br'

			],
			'GBES'  => ['ep' => 'statics/gb-wap/GBES-ep'],
			'GBFR'  => ['fr' => 'statics/gb-wap/GBFR-fr'],
			'GBRU'  => ['ru' => 'statics/gb-wap/GBRU-ru'],
			'GBPT'  => ['po' => 'statics/gb-wap/GBPT-po'],
			'GBIT'  => ['it' => 'statics/gb-wap/GBIT-it'],
			'GBDE'  => ['de' => 'statics/gb-wap/GBDE-de'],
			'GBUK'  => ['en' => 'statics/gb-wap/GBUK-en'],
			'GBUS'  => ['en' => 'statics/gb-wap/GBUS-en'],
			'GBBR'  => ['pt-br' => 'statics/gb-wap/GBBR-pt-br'],
			'GBTR'  => ['tr' => 'statics/gb-wap/GBTR-tr'],
			'GBMX'  => ['ep-mx' => 'statics/gb-wap/GBMX-ep-mx'],
			'GBMA'  => ['fr' => 'statics/gb-wap/GBMA-fr'],
			'GBGR'  => ['el' => 'statics/gb-wap/GBGR-el'],
			'GBHU'  => ['hu' => 'statics/gb-wap/GBHU-hu'],
			'GBNL'  => ['nl' => 'statics/gb-wap/GBNL-nl'],
			'GBSK'  => ['sk' => 'statics/gb-wap/GBSK-sk'],
			'GBRO'  => ['ro' => 'statics/gb-wap/GBRO-ro'],
			'GBCZ'  => ['cs' => 'statics/gb-wap/GBCZ-cs'],
			'GBAU'  => ['en' => 'statics/gb-wap/GBAU-en'],
			'GBIN'  => ['en' => 'statics/gb-wap/GBIN-en'],
			'GBJP'  => ['ja' => 'statics/gb-wap/GBJP-ja'],
			/*'GBUA' => ['uk' => 'statics/gb-wap/GBUA-uk'],
			'GBJP' => ['ja' => 'statics/gb-wap/GBJP-ja'],
			'GBIL' => ['he' => 'statics/gb-wap/GBIL-he'],
			'GBKZ' => ['kk' => 'statics/gb-wap/GBKZ-kk'],
			'GBTH' => ['th' => 'statics/gb-wap/GBTH-th'],
			'GBVN' => ['vi' => 'statics/gb-wap/GBVN-vi'],
			'GBID' => ['id' => 'statics/gb-wap/GBID-id'],*/
			'GBPL'  => ['pl' => 'statics/gb-wap/GBPL-pl'],
			'GBSTY' => ['en' => 'statics/gb-wap/GBSTY-en'],
			'GBGAG' => ['en' => 'statics/gb-wap/GBGAG-en'],
			'GBCOZ' => ['en' => 'statics/gb-wap/GBCOZ-en'],
		],
		'secondary_domain'      => [
			'GB'    => [
				'en'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ep'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'fr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ru'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'de'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'tr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'po'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'pt-br' => 'https://m' . GB_DOMAIN . '/promotion/release',

			],
			'GBES'  => ['ep' => 'https://m-es' . GB_DOMAIN . '/promotion/release'],
			'GBFR'  => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion/release'],
			'GBRU'  => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion/release'],
			'GBPT'  => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion/release'],
			'GBIT'  => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion/release'],
			'GBDE'  => ['de' => 'https://m-de' . GB_DOMAIN . '/promotion/release'],
			'GBUK'  => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion/release'],
			'GBUS'  => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion/release'],
			'GBBR'  => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion/release'],
			'GBTR'  => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion/release'],
			'GBMX'  => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion/release'],
			'GBMA'  => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion/release'],
			'GBGR'  => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion/release'],
			'GBHU'  => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion/release'],
			'GBNL'  => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion/release'],
			'GBSK'  => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion/release'],
			'GBRO'  => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion/release'],
			'GBCZ'  => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion/release'],
			'GBAU'  => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion/release'],
			'GBIN'  => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion/release'],
			'GBJP'  => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			/*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion/release'],
			'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion/release'],
			'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion/release'],
			'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion/release'],
			'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion/release'],*/
			'GBPL'  => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion/release'],
			'GBSTY' => ['en' => 'https://m-stylebest' . GB_DOMAIN . '/promotion/release'],
			'GBGAG' => ['en' => 'https://m-gagabop' . GB_DOMAIN . '/promotion/release'],
			'GBCOZ' => ['en' => 'https://m-cozyvoices' . GB_DOMAIN . '/promotion/release'],
		],
		'ad_secondary_domain'   => [
			'en'    => 'https://m' . GB_DOMAIN . '/selection/release',
			'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/selection/release',
			'pt_br' => 'https://m-br' . GB_DOMAIN . '/selection/release',
			'tr'    => 'https://m-tr' . GB_DOMAIN . '/selection/release',
			'ep'    => 'https://m-es' . GB_DOMAIN . '/selection/release',
			'fr'    => 'https://m-fr' . GB_DOMAIN . '/selection/release',
			'ru'    => 'https://m-ru' . GB_DOMAIN . '/selection/release',
			'po'    => 'https://m-pt' . GB_DOMAIN . '/selection/release',
			'it'    => 'https://m-it' . GB_DOMAIN . '/selection/release',
			'de'    => 'https://m-de' . GB_DOMAIN . '/selection/release',
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . GB_DOMAIN . '/promotion/release/home',
		]
	],
	'gb-ios'     => [
		's3PublishPath'         => [
			'GB'   => [
				'en'    => 'publish/gb-ios/m' . GB_DOMAIN . '/en/activity/release',
				'ep'    => 'publish/gb-ios/m' . GB_DOMAIN . '/ep/activity/release',
				'fr'    => 'publish/gb-ios/m' . GB_DOMAIN . '/fr/activity/release',
				'ru'    => 'publish/gb-ios/m' . GB_DOMAIN . '/ru/activity/release',
				'po'    => 'publish/gb-ios/m' . GB_DOMAIN . '/po/activity/release',
				'it'    => 'publish/gb-ios/m' . GB_DOMAIN . '/it/activity/release',
				'de'    => 'publish/gb-ios/m' . GB_DOMAIN . '/de/activity/release',
				'tr'    => 'publish/gb-ios/m' . GB_DOMAIN . '/tr/activity/release',
				'pt-br' => 'publish/gb-ios/m' . GB_DOMAIN . '/pt-br/activity/release'
			],
			'GBRU' => ['ru' => 'publish/gb-ios/m-ru' . GB_DOMAIN . '/activity/release'],
			'GBFR' => ['fr' => 'publish/gb-ios/m-fr' . GB_DOMAIN . '/activity/release'],
			'GBPT' => ['po' => 'publish/gb-ios/m-pt' . GB_DOMAIN . '/activity/release'],
			'GBIT' => ['it' => 'publish/gb-ios/m-it' . GB_DOMAIN . '/activity/release'],
			'GBUK' => ['en' => 'publish/gb-ios/m-uk' . GB_DOMAIN . '/activity/release'],
			'GBUS' => ['en' => 'publish/gb-ios/m-us' . GB_DOMAIN . '/activity/release'],
			'GBBR' => ['pt-br' => 'publish/gb-ios/m-br' . GB_DOMAIN . '/activity/release'],
			'GBTR' => ['tr' => 'publish/gb-ios/m-tr' . GB_DOMAIN . '/activity/release'],
			'GBMX' => ['ep-mx' => 'publish/gb-ios/m-mx' . GB_DOMAIN . '/activity/release'],
			'GBMA' => ['fr' => 'publish/gb-ios/m-ma' . GB_DOMAIN . '/activity/release'],
			'GBGR' => ['el' => 'publish/gb-ios/m-gr' . GB_DOMAIN . '/activity/release'],
			'GBHU' => ['hu' => 'publish/gb-ios/m-hu' . GB_DOMAIN . '/activity/release'],
			'GBNL' => ['nl' => 'publish/gb-ios/m-nl' . GB_DOMAIN . '/activity/release'],
			'GBSK' => ['sk' => 'publish/gb-ios/m-sk' . GB_DOMAIN . '/activity/release'],
			'GBRO' => ['ro' => 'publish/gb-ios/m-ro' . GB_DOMAIN . '/activity/release'],
			'GBCZ' => ['cs' => 'publish/gb-ios/m-cz' . GB_DOMAIN . '/activity/release'],
			'GBAU' => ['en' => 'publish/gb-ios/m-au' . GB_DOMAIN . '/activity/release'],
			'GBIN' => ['en' => 'publish/gb-ios/m-in' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-ios/m-jp' . GB_DOMAIN . '/activity/release'],
			/*'GBUA' => ['uk' => 'publish/gb-ios/m-ua' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-ios/m-jp' . GB_DOMAIN . '/activity/release'],
			'GBIL' => ['he' => 'publish/gb-ios/m-il' . GB_DOMAIN . '/activity/release'],
			'GBKZ' => ['kk' => 'publish/gb-ios/m-kz' . GB_DOMAIN . '/activity/release'],
			'GBTH' => ['th' => 'publish/gb-ios/m-th' . GB_DOMAIN . '/activity/release'],
			'GBVN' => ['vi' => 'publish/gb-ios/m-vn' . GB_DOMAIN . '/activity/release'],
			'GBID' => ['id' => 'publish/gb-ios/m-id' . GB_DOMAIN . '/activity/release'],*/
			'GBPL' => ['pl' => 'publish/gb-ios/m-pl' . GB_DOMAIN . '/activity/release']
		],
		's3StaticPath'          => [
			'GB'   => [
				'en'    => 'statics/gb-ios/GB-en',
				'ep'    => 'statics/gb-ios/GB-ep',
				'fr'    => 'statics/gb-ios/GB-fr',
				'ru'    => 'statics/gb-ios/GB-ru',
				'po'    => 'statics/gb-ios/GB-po',
				'it'    => 'statics/gb-ios/GB-it',
				'de'    => 'statics/gb-ios/GB-de',
				'tr'    => 'statics/gb-ios/GB-tr',
				'pt-br' => 'statics/gb-ios/GB-pt-br'
			],
			'GBRU' => ['ru' => 'statics/gb-ios/GBRU-ru'],
			'GBFR' => ['fr' => 'statics/gb-ios/GBRU-fr'],
			'GBPT' => ['po' => 'statics/gb-ios/GBPT-po'],
			'GBIT' => ['it' => 'statics/gb-ios/GBIT-it'],
			'GBUK' => ['en' => 'statics/gb-ios/GBUK-en'],
			'GBUS' => ['en' => 'statics/gb-ios/GBUS-en'],
			'GBBR' => ['pt-br' => 'statics/gb-ios/GBBR-pt-br'],
			'GBTR' => ['tr' => 'statics/gb-ios/GBTR-tr'],
			'GBMX' => ['ep-mx' => 'statics/gb-ios/GBMX-ep-mx'],
			'GBMA' => ['fr' => 'statics/gb-ios/GBMA-fr'],
			'GBGR' => ['el' => 'statics/gb-ios/GBGR-el'],
			'GBHU' => ['hu' => 'statics/gb-ios/GBHU-hu'],
			'GBNL' => ['nl' => 'statics/gb-ios/GBNL-nl'],
			'GBSK' => ['sk' => 'statics/gb-ios/GBSK-sk'],
			'GBRO' => ['ro' => 'statics/gb-ios/GBRO-ro'],
			'GBCZ' => ['cs' => 'statics/gb-ios/GBCZ-cs'],
			'GBAU' => ['en' => 'statics/gb-ios/GBAU-en'],
			'GBIN' => ['en' => 'statics/gb-ios/GBIN-en'],
			'GBJP' => ['ja' => 'statics/gb-ios/GBJP-ja'],
			/*'GBUA' => ['uk' => 'statics/gb-ios/GBUA-uk'],
			'GBJP' => ['ja' => 'statics/gb-ios/GBJP-ja'],
			'GBIL' => ['he' => 'statics/gb-ios/GBIL-he'],
			'GBKZ' => ['kk' => 'statics/gb-ios/GBKZ-kk'],
			'GBTH' => ['th' => 'statics/gb-ios/GBTH-th'],
			'GBVN' => ['vi' => 'statics/gb-ios/GBVN-vi'],
			'GBID' => ['id' => 'statics/gb-ios/GBID-id'],*/
			'GBPL' => ['pl' => 'statics/gb-ios/GBPL-pl']
		],
		'secondary_domain'      => [
			'GB'   => [
				'en'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ep'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'fr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ru'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'po'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'it'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'de'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'tr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'pt-br' => 'https://m' . GB_DOMAIN . '/promotion/release'
			],
			'GBRU' => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion/release'],
			'GBFR' => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion/release'],
			'GBPT' => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion/release'],
			'GBIT' => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion/release'],
			'GBUK' => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion/release'],
			'GBUS' => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion/release'],
			'GBBR' => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion/release'],
			'GBTR' => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion/release'],
			'GBMX' => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion/release'],
			'GBMA' => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion/release'],
			'GBGR' => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion/release'],
			'GBHU' => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion/release'],
			'GBNL' => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion/release'],
			'GBSK' => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion/release'],
			'GBRO' => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion/release'],
			'GBCZ' => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion/release'],
			'GBAU' => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion/release'],
			'GBIN' => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			/*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion/release'],
			'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion/release'],
			'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion/release'],
			'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion/release'],
			'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion/release'],*/
			'GBPL' => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion/release']
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . GB_DOMAIN . '/promotion/release/home',
		]
	],
	'gb-android' => [
		's3PublishPath'         => [
			'GB'   => [
				'en'    => 'publish/gb-android/m' . GB_DOMAIN . '/en/activity/release',
				'ep'    => 'publish/gb-android/m' . GB_DOMAIN . '/ep/activity/release',
				'fr'    => 'publish/gb-android/m' . GB_DOMAIN . '/fr/activity/release',
				'ru'    => 'publish/gb-android/m' . GB_DOMAIN . '/ru/activity/release',
				'po'    => 'publish/gb-android/m' . GB_DOMAIN . '/po/activity/release',
				'it'    => 'publish/gb-android/m' . GB_DOMAIN . '/it/activity/release',
				'de'    => 'publish/gb-android/m' . GB_DOMAIN . '/de/activity/release',
				'tr'    => 'publish/gb-android/m' . GB_DOMAIN . '/tr/activity/release',
				'pt-br' => 'publish/gb-android/m' . GB_DOMAIN . '/pt-br/activity/release'
			],
			'GBRU' => ['ru' => 'publish/gb-android/m-ru' . GB_DOMAIN . '/activity/release'],
			'GBFR' => ['fr' => 'publish/gb-android/m-fr' . GB_DOMAIN . '/activity/release'],
			'GBPT' => ['po' => 'publish/gb-android/m-pt' . GB_DOMAIN . '/activity/release'],
			'GBIT' => ['it' => 'publish/gb-android/m-it' . GB_DOMAIN . '/activity/release'],
			'GBUK' => ['en' => 'publish/gb-android/m-uk' . GB_DOMAIN . '/activity/release'],
			'GBUS' => ['en' => 'publish/gb-android/m-us' . GB_DOMAIN . '/activity/release'],
			'GBBR' => ['pt-br' => 'publish/gb-android/m-br' . GB_DOMAIN . '/activity/release'],
			'GBTR' => ['tr' => 'publish/gb-android/m-tr' . GB_DOMAIN . '/activity/release'],
			'GBMX' => ['ep-mx' => 'publish/gb-android/m-mx' . GB_DOMAIN . '/activity/release'],
			'GBMA' => ['fr' => 'publish/gb-android/m-ma' . GB_DOMAIN . '/activity/release'],
			'GBGR' => ['el' => 'publish/gb-android/m-gr' . GB_DOMAIN . '/activity/release'],
			'GBHU' => ['hu' => 'publish/gb-android/m-hu' . GB_DOMAIN . '/activity/release'],
			'GBNL' => ['nl' => 'publish/gb-android/m-nl' . GB_DOMAIN . '/activity/release'],
			'GBSK' => ['sk' => 'publish/gb-android/m-sk' . GB_DOMAIN . '/activity/release'],
			'GBRO' => ['ro' => 'publish/gb-android/m-ro' . GB_DOMAIN . '/activity/release'],
			'GBCZ' => ['cs' => 'publish/gb-android/m-cz' . GB_DOMAIN . '/activity/release'],
			'GBAU' => ['en' => 'publish/gb-android/m-au' . GB_DOMAIN . '/activity/release'],
			'GBIN' => ['en' => 'publish/gb-android/m-in' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-android/m-jp' . GB_DOMAIN . '/activity/release'],
			/*'GBUA' => ['uk' => 'publish/gb-android/m-ua' . GB_DOMAIN . '/activity/release'],
			'GBJP' => ['ja' => 'publish/gb-android/m-jp' . GB_DOMAIN . '/activity/release'],
			'GBIL' => ['he' => 'publish/gb-android/m-il' . GB_DOMAIN . '/activity/release'],
			'GBKZ' => ['kk' => 'publish/gb-android/m-kz' . GB_DOMAIN . '/activity/release'],
			'GBTH' => ['th' => 'publish/gb-android/m-th' . GB_DOMAIN . '/activity/release'],
			'GBVN' => ['vi' => 'publish/gb-android/m-vn' . GB_DOMAIN . '/activity/release'],
			'GBID' => ['id' => 'publish/gb-android/m-id' . GB_DOMAIN . '/activity/release'],*/
			'GBPL' => ['pl' => 'publish/gb-android/m-pl' . GB_DOMAIN . '/activity/release']
		],
		's3StaticPath'          => [
			'GB'   => [
				'en'    => 'statics/gb-android/GB-en',
				'ep'    => 'statics/gb-android/GB-ep',
				'fr'    => 'statics/gb-android/GB-fr',
				'ru'    => 'statics/gb-android/GB-ru',
				'po'    => 'statics/gb-android/GB-po',
				'it'    => 'statics/gb-android/GB-it',
				'de'    => 'statics/gb-android/GB-de',
				'tr'    => 'statics/gb-android/GB-tr',
				'pt-br' => 'statics/gb-android/GB-pt-br'
			],
			'GBRU' => ['ru' => 'statics/gb-android/GBRU-ru'],
			'GBFR' => ['fr' => 'statics/gb-android/GBFR-fr'],
			'GBPT' => ['po' => 'statics/gb-android/GBPT-po'],
			'GBIT' => ['it' => 'statics/gb-android/GBIT-it'],
			'GBUK' => ['en' => 'statics/gb-android/GBUK-en'],
			'GBUS' => ['en' => 'statics/gb-android/GBUS-en'],
			'GBBR' => ['pt-br' => 'statics/gb-android/GBBR-pt-br'],
			'GBTR' => ['tr' => 'statics/gb-android/GBTR-tr'],
			'GBMX' => ['ep-mx' => 'statics/gb-android/GBMX-ep-mx'],
			'GBMA' => ['fr' => 'statics/gb-android/GBMA-fr'],
			'GBGR' => ['el' => 'statics/gb-android/GBGR-el'],
			'GBHU' => ['hu' => 'statics/gb-android/GBHU-hu'],
			'GBNL' => ['nl' => 'statics/gb-android/GBNL-nl'],
			'GBSK' => ['sk' => 'statics/gb-android/GBSK-sk'],
			'GBRO' => ['ro' => 'statics/gb-android/GBRO-ro'],
			'GBCZ' => ['cs' => 'statics/gb-android/GBCZ-cs'],
			'GBAU' => ['en' => 'statics/gb-android/GBAU-en'],
			'GBIN' => ['en' => 'statics/gb-android/GBIN-en'],
			'GBJP' => ['ja' => 'statics/gb-android/GBJP-ja'],
			/*'GBUA' => ['uk' => 'statics/gb-android/GBUA-uk'],
			'GBJP' => ['ja' => 'statics/gb-android/GBJP-ja'],
			'GBIL' => ['he' => 'statics/gb-android/GBIL-he'],
			'GBKZ' => ['kk' => 'statics/gb-android/GBKZ-kk'],
			'GBTH' => ['th' => 'statics/gb-android/GBTH-th'],
			'GBVN' => ['vi' => 'statics/gb-android/GBVN-vi'],
			'GBID' => ['id' => 'statics/gb-android/GBID-id'],*/
			'GBPL' => ['pl' => 'statics/gb-android/GBPL-pl']
		],
		'secondary_domain'      => [
			'GB'   => [
				'en'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ep'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'fr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'ru'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'po'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'it'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'de'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'tr'    => 'https://m' . GB_DOMAIN . '/promotion/release',
				'pt-br' => 'https://m' . GB_DOMAIN . '/promotion/release'
			],
			'GBRU' => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion/release'],
			'GBFR' => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion/release'],
			'GBPT' => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion/release'],
			'GBIT' => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion/release'],
			'GBUK' => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion/release'],
			'GBUS' => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion/release'],
			'GBBR' => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion/release'],
			'GBTR' => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion/release'],
			'GBMX' => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion/release'],
			'GBMA' => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion/release'],
			'GBGR' => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion/release'],
			'GBHU' => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion/release'],
			'GBNL' => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion/release'],
			'GBSK' => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion/release'],
			'GBRO' => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion/release'],
			'GBCZ' => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion/release'],
			'GBAU' => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion/release'],
			'GBIN' => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			/*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion/release'],
			'GBJP' => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion/release'],
			'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion/release'],
			'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion/release'],
			'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion/release'],
			'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion/release'],
			'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion/release'],*/
			'GBPL' => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion/release']
		],
		'home_secondary_domain' => [
			'en' => 'https://m' . GB_DOMAIN . '/promotion/release/home',
		]
	],

	/**************************************** DL *******************************************/
	'dl-web'     => [
		's3PublishPath'         => [
			'DL' => [
				'en' => 'publish/www.dresslily.com/en/release',
				],
			'DLFR' => [
				'fr' => 'publish/fr.dresslily.com/fr/release',
				]
		],
		's3HomePublishPath'     => [
			'DL' => [
				'en' => 'publish/www.dresslily.com/en/home/release',
				],
			'DLFR' => [
				'fr' => 'publish/fr.dresslily.com/fr/home/release',
				]
		],
		's3StaticPath'          => [
			'DL' => [
				'en' => 'statics/dl-web/en',
				],
			'DLFR' => [
				'fr' => 'statics/dl-web/fr',
				]
		],
		'secondary_domain'      => [
			'DL' => [
				'en' => 'https://www' . DL_DOMAIN . '/promotion/release',
				],
			'DLFR' => [
				'fr' => 'https://fr' . DL_DOMAIN . '/promotion/release',
				]
		],
		'home_secondary_domain' => [
			'DL' => [
				'en' => 'https://www' . DL_DOMAIN . '/promotion/home/release',
				],
			'DLFR' => [
				'fr' => 'https://fr' . DL_DOMAIN . '/promotion/home/release',
				]
		]
	],
	'dl-app'     => [
		's3PublishPath'    => [
			'DL' => [
				'en' => 'publish/www.dresslily.com/en/app/release',
				],
			'DLFR' => [
				'fr' => 'publish/fr.dresslily.com/fr/app/release',
				]
		],
		's3StaticPath'     => [
			'DL' => [
				'en' => 'statics/dl-app/en',
				],
			'DLFR' => [
				'fr' => 'statics/dl-app/fr',
				]
		],
		'secondary_domain' => [
			'DL' => [
				'en' => 'https://www' . DL_DOMAIN . '/promotion/app/release',
				],
			'DLFR' => [
				'fr' => 'https://fr' . DL_DOMAIN . '/promotion/app/release',
				]
		]
	],
];
