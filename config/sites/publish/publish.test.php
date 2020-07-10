<?php
/**
 * 站点测试环境发布配置
 */
return [
    /****************************************测试站点配置START*****************************************/
    'test-pc'    => [
        's3PublishPath'         => [//活动页面静态HTML文件存放路径
                                    'en' => 'publish/rw-pc/en',
                                    'es' => 'publish/rw-pc/es',
        ],
        's3HomePublishPath'     => [//首页静态HTML文件存放路径
                                    'en' => 'publish/rw-pc/en/test/home',
                                    'es' => 'publish/rw-pc/es/test/home',
        ],
        's3StaticPath'          => [//页面使用的JS与css文件存放路径
                                    'en' => 'statics/rw-pc/en',
                                    'es' => 'statics/rw-pc/es',
        ],
        'secondary_domain'      => [//活动页面代理站点的访问链接
                                    'en' => 'http://www.pc-rosewholesale' . TEST_DOMAIN . '/promotion',
                                    'es' => 'http://es.pc-rosewholesale' . TEST_DOMAIN . '/promotion',
        ],
        'home_secondary_domain' => [//首页代理站点的访问链接
                                    'en' => 'http://www.pc-rosewholesale' . TEST_DOMAIN . '/promotion/test/home',
                                    'es' => 'http://es.pc-rosewholesale' . TEST_DOMAIN . '/promotion/test/home',
        ]
    ],
    'test-wap'   => [
        's3PublishPath'         => [
            'en' => 'publish/rw-wap/en',
        ],
        's3HomePublishPath'     => [
            'en' => 'publish/rw-wap/en/test/home',
        ],
        's3StaticPath'          => [
            'en' => 'statics/rw-wap/en',
        ],
        'secondary_domain'      => [
            'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/promotion'
        ],
        'home_secondary_domain' => [
            'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/promotion/test/home'
        ]
    ],
    'test-app'   => [
        's3PublishPath'         => [
            'en' => 'publish/rw-wap/en/app',
        ],
        's3HomePublishPath'     => [
            'en' => 'publish/rw-wap/en/test/home',
        ],
        's3StaticPath'          => [
            'en' => 'statics/rw-wap/en',
        ],
        'secondary_domain'      => [
            'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/promotion/app'
        ],
        'home_secondary_domain' => [
            'en' => 'http://m.wap-rosewholesale' . TEST_DOMAIN . '/promotion/test/home'
        ]
    ],
    /****************************************测试站点配置END*******************************************/
    'rw-pc'      => [
        's3PublishPath'         => [
            'en' => 'publish/rw-pc/en',
            'es' => 'publish/rw-pc/es',
        ],
        's3HomePublishPath'     => [
            'en' => 'publish/rw-pc/en/home',
            'es' => 'publish/rw-pc/es/home',
        ],
        's3StaticPath'          => [
            'en' => 'statics/rw-pc/en',
            'es' => 'statics/rw-pc/es',
        ],
        'secondary_domain'      => [
            'en' => 'http://www.pc-rosewholesale' . RW_DOMAIN . '/promotion',
            'es' => 'http://es.pc-rosewholesale' . RW_DOMAIN . '/promotion',
        ],
        'home_secondary_domain' => [
            'en' => 'http://www.pc-rosewholesale' . RW_DOMAIN . '/promotion/home',
            'es' => 'http://es.pc-rosewholesale' . RW_DOMAIN . '/promotion/home',
        ]
    ],
    'rw-wap'     => [
        's3PublishPath'         => [
            'en' => 'publish/rw-wap/en',
        ],
        's3HomePublishPath'     => [
            'en' => 'publish/rw-wap/en/home',
        ],
        's3StaticPath'          => [
            'en' => 'statics/rw-wap/en',
        ],
        'secondary_domain'      => [
            'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/promotion'
        ],
        'home_secondary_domain' => [
            'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/promotion/home'
        ]
    ],
    'rw-app'     => [
        's3PublishPath'         => [
            'en' => 'publish/rw-wap/en/app',
        ],
        's3HomePublishPath'     => [
            'en' => 'publish/rw-wap/en/home',
        ],
        's3StaticPath'          => [
            'en' => 'statics/rw-wap/en',
        ],
        'secondary_domain'      => [
            'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/promotion/app'
        ],
        'home_secondary_domain' => [
            'en' => 'http://m.wap-rosewholesale' . RW_DOMAIN . '/promotion/home'
        ]
    ],
    'rg-pc'      => [
        's3PublishPath'         => [
            'RG' => [
                 'en' => 'publish/rg-pc/en',
                ],
            'RGFR' => [
                 'fr' => 'publish/rg-pc/fr',
                ],
            'RGRU' => [
                 'ru' => 'publish/rg-pc/ru',
                ]

],
        's3HomePublishPath'     => [
            'RG' => [
                'en' => 'publish/rg-pc/en/home',
                ],
            'RGFR' => [
                'fr' => 'publish/rg-pc/fr/home',
                ],
            'RGRU' => [
                'ru' => 'publish/rg-pc/ru/home',
                ]

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
                ]
        ],
        'secondary_domain'      => [
            'RG' => [
                'en' => 'http://www.pc-rosegal' . RG_DOMAIN . '/promotion',
                ],
            'RGFR' => [
                'fr' => 'http://fr.pc-rosegal' . RG_DOMAIN . '/promotion',
                ],
            'RGRU' => [
                'ru' => 'http://ru.pc-rosegal' . RG_DOMAIN . '/promotion',

                ]
        ],
        'home_secondary_domain' => [
            'RG' => [
                'en' => 'http://www.pc-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGFR' => [
                'fr' => 'http://fr.pc-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGRU' => [
                'ru' => 'http://ru.pc-rosegal' . RG_DOMAIN . '/promotion/home',
                ]
        ]
    ],
    'rg-wap'     => [
        's3PublishPath'         => [
            'RG' => [
                'en' => 'publish/rg-wap/en',
                ],
            'RGFR' => [
                'fr' => 'publish/rg-wap/fr',
                ],
            'RGES' => [
                'es' => 'publish/rg-wap/es',
                ]
        ],
        's3HomePublishPath'     => [
            'RG' => [
                'en' => 'publish/rg-wap/en/home',
                ],
            'RGFR' => [
                'fr' => 'publish/rg-wap/fr/home',
                ],
            'RGES' => [
                'es' => 'publish/rg-wap/es/home',
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
                'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/promotion',
                ],
            'RGFR' => [
                'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/promotion',
                ],
            'RGES' => [
                'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/promotion',
                ]
        ],
        'home_secondary_domain' => [
            'RG' => [
                'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGFR' => [
                'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGES' => [
                'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ]
        ]
    ],
    'rg-app'     => [
        's3PublishPath'         => [
            'RG' => [
                'en' => 'publish/rg-wap/en/app',
                ],
            'RGFR' => [
                'fr' => 'publish/rg-wap/fr/app',
                ],
            'RGES' => [
                'es' => 'publish/rg-wap/es/app',
                ]
        ],
        's3HomePublishPath'     => [
            'RG' => [
                'en' => 'publish/rg-wap/en/home',
                ],
            'RGFR' => [
                'fr' => 'publish/rg-wap/fr/home',
                ],
            'RGES' => [
                'es' => 'publish/rg-wap/es/home',
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
                'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/promotion/app',
                ],
            'RGFR' => [
                'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/promotion/app',
                ],
            'RGES' => [
                'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/promotion/app',
                ]
        ],
        'home_secondary_domain' => [
            'RG' => [
                'en' => 'http://m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGFR' => [
                'fr' => 'http://fr-m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ],
            'RGES' => [
                'es' => 'http://es-m.wap-rosegal' . RG_DOMAIN . '/promotion/home',
                ]
        ]
    ],

    /**************************************** SUK *******************************************/
    'suk-pc'      => [
        's3PublishPath'         => [
            'SUK' => [
                'en' => 'publish/suk-pc/en',
                ],
            'SUKJP' => [
                'ja' => 'publish/suk-pc/ja',
                ]
        ],
        's3HomePublishPath'     => [
            'SUK' => [
                'en' => 'publish/suk-pc/en/home',
                ],
            'SUKJP' => [
                'ja' => 'publish/suk-pc/ja/home',
                ]
        ],
        's3StaticPath'          => [
            'SUK' => [
                'en' => 'statics/suk-pc/en',
                ],
            'SUKJP' => [
                'ja' => 'statics/suk-pc/ja',
                ]
        ],
        'secondary_domain'      => [
            'SUK' => [
                'en' => 'http://suaoki' . SUK_DOMAIN . '/promotion',
                ],
            'SUKJP' => [
                'ja' => 'http://jpsuaoki' . SUK_DOMAIN . '/promotion',
                ]
        ],
        'home_secondary_domain' => [
            'SUK' => [
                'en' => 'http://suaoki' . SUK_DOMAIN . '/promotion/home',
                ],
            'SUKJP' => [
                'ja' => 'http://jpsuaoki' . SUK_DOMAIN . '/promotion/home',
                ]
        ]
    ],
    'suk-wap'     => [
        's3PublishPath'         => [
            'SUK' => [
                'en' => 'publish/suk-wap/en',
                ],
            'SUKJP' => [
                'ja' => 'publish/suk-wap/ja',
                ]
        ],
        's3HomePublishPath'     => [
            'SUK' => [
                'en' => 'publish/suk-wap/en/home',
                ],
            'SUKJP' => [
                'ja' => 'publish/suk-wap/ja/home',
                ]
        ],
        's3StaticPath'          => [
            'SUK' => [
                'en' => 'statics/suk-wap/en',
                ],
            'SUKJP' => [
                'ja' => 'statics/suk-wap/ja',
                ]
        ],
        'secondary_domain'      => [
            'SUK' => [
                'en' => 'http://msuaoki' . SUK_DOMAIN . '/promotion',
                ],
            'SUKJP' => [
                'ja' => 'http://jpmsuaoki' . SUK_DOMAIN . '/promotion',
                ]
        ],
        'home_secondary_domain' => [
            'SUK' => [
                'en' => 'http://msuaoki' . SUK_DOMAIN . '/promotion/home',
                ],
            'SUKJP' => [
                'ja' => 'http://jpmsuaoki' . SUK_DOMAIN . '/promotion/home',
                ]
        ]
    ],

    /**************************************** ZF *******************************************/
    'zf-pc'      => [
        's3PublishPath'         => [
            'ZF'   => [
                'en' => 'publish/www.zaful.com/en',
            ],
            'ZFES' => [
                'es' => 'publish/es.zaful.com/es',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr.zaful.com/fr',
            ],
            'ZFDE' => [
                'de' => 'publish/de.zaful.com/de',
            ],
            'ZFPT' => [
                'pt' => 'publish/pt.zaful.com/pt',
            ],
            'ZFIT' => [
                'it' => 'publish/it.zaful.com/it',
            ],
            'ZFIE' => [
                'en' => 'publish/eur.zaful.com/en',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz.zaful.com/en',
            ],
            'ZFGB' => [
                'en' => 'publish/uk.zaful.com/en',
            ],
            'ZFCA' => [
                'en' => 'publish/ca.zaful.com/en',
                'fr' => 'publish/ca.zaful.com/fr',
            ],
            'ZFBE' => [
                'fr' => 'publish/be.zaful.com/fr',
            ],
            'ZFCH' => [
                'de' => 'publish/ch.zaful.com/de',
                'en' => 'publish/ch.zaful.com/en',
                'fr' => 'publish/ch.zaful.com/fr',
            ],
            'ZFPH' => [
                'en' => 'publish/ph.zaful.com/en',
            ],
            'ZFIN' => [
                'en' => 'publish/in.zaful.com/en',
            ],
            'ZFSG' => [
                'en' => 'publish/sg.zaful.com/en',
            ],
            'ZFMY' => [
                'en' => 'publish/my.zaful.com/en',
            ],
            'ZFAU' => [
                'en' => 'publish/au.zaful.com/en',
            ],
            'ZFAT' => [
                'de' => 'publish/at.zaful.com/de'
            ],
            'ZFMX' => [
                'es' => 'publish/latam.zaful.com/es',
            ],
            'ZFZA' => [
                'en' => 'publish/za.zaful.com/en',
            ],
            'ZFBR' => [
                'pt' => 'publish/br.zaful.com/pt'
            ],
            'ZFTH' => [
                'th' => 'publish/th.zaful.com/th',
            ],
            'ZFID' => [
                'id' => 'publish/id.zaful.com/id',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw.zaful.com/zh-tw'
            ],
            'ZFAR' => [
                'ar' => 'publish/ar.zaful.com/ar'
            ],
            'ZFIL' => [
                'en' => 'publish/il.zaful.com/en'
            ],
            'ZFRU' => [
                'ru' => 'publish/ru.zaful.com/ru'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk.zaful.com/zh-tw'
            ],
            'ZFTR' => [
                'tr' => 'publish/tr.zaful.com/tr'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx.zaful.com/es'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro.zaful.com/ro'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp.zaful.com/ja'
            ],
        ],
        's3HomePublishPath'     => [
            'ZF'   => [
                'en' => 'publish/www.zaful.com/en/home',
            ],
            'ZFES' => [
                'es' => 'publish/es.zaful.com/es/home',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr.zaful.com/fr/home',
            ],
            'ZFDE' => [
                'de' => 'publish/de.zaful.com/de/home',
            ],
            'ZFPT' => [
                'pt' => 'publish/pt.zaful.com/pt/home',
            ],
            'ZFIT' => [
                'it' => 'publish/it.zaful.com/it/home',
            ],
            'ZFIE' => [
                'en' => 'publish/eur.zaful.com/en/home',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz.zaful.com/en/home',
            ],
            'ZFGB' => [
                'en' => 'publish/uk.zaful.com/en/home',
            ],
            'ZFCA' => [
                'en' => 'publish/ca.zaful.com/en/home',
                'fr' => 'publish/ca.zaful.com/fr/home',
            ],
            'ZFBE' => [
                'fr' => 'publish/be.zaful.com/fr/home',
            ],
            'ZFCH' => [
                'de' => 'publish/ch.zaful.com/de/home',
                'en' => 'publish/ch.zaful.com/en/home',
                'fr' => 'publish/ch.zaful.com/fr/home',
            ],
            'ZFPH' => [
                'en' => 'publish/ph.zaful.com/en/home',
            ],
            'ZFIN' => [
                'en' => 'publish/in.zaful.com/en/home',
            ],
            'ZFSG' => [
                'en' => 'publish/sg.zaful.com/en/home',
            ],
            'ZFMY' => [
                'en' => 'publish/my.zaful.com/en/home',
            ],
            'ZFAU' => [
                'en' => 'publish/au.zaful.com/en/home',
            ],
            'ZFAT' => [
                'de' => 'publish/at.zaful.com/de/home'
            ],
            'ZFMX' => [
                'es' => 'publish/latam.zaful.com/es/home',
            ],
            'ZFZA' => [
                'en' => 'publish/za.zaful.com/en/home',
            ],
            'ZFBR' => [
                'pt' => 'publish/br.zaful.com/pt/home'
            ],
            'ZFTH' => [
                'th' => 'publish/th.zaful.com/th/home',
            ],
            'ZFID' => [
                'id' => 'publish/id.zaful.com/id/home',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw.zaful.com/zh-tw/home'
            ],
            'ZFAR' => [
                'ar' => 'publish/ar.zaful.com/ar/home'
            ],
            'ZFIL' => [
                'en' => 'publish/il.zaful.com/en/home'
            ],
            'ZFRU' => [
                'ru' => 'publish/ru.zaful.com/ru/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk.zaful.com/zh-tw/home'
            ],
            'ZFTR' => [
                'tr' => 'publish/tr.zaful.com/tr/home'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx.zaful.com/es/home'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro.zaful.com/ro/home'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp.zaful.com/ja/home'
            ],
        ],
        's3StaticPath'          => [
            'ZF'   => [
                'en' => 'statics/zf-pc/ZF-en',
            ],
            'ZFES' => [
                'es' => 'statics/zf-pc/ZFES-es',
            ],
            'ZFFR' => [
                'fr' => 'statics/zf-pc/ZFFR-fr',
            ],
            'ZFDE' => [
                'de' => 'statics/zf-pc/ZFDE-de',
            ],
            'ZFPT' => [
                'pt' => 'statics/zf-pc/ZFPT-pt',
            ],
            'ZFIT' => [
                'it' => 'statics/zf-pc/ZFIT-it',
            ],
            'ZFIE' => [
                'en' => 'statics/zf-pc/ZFIE-en',
            ],
            'ZFNZ' => [
                'en' => 'statics/zf-pc/ZFNZ-en',
            ],
            'ZFGB' => [
                'en' => 'statics/zf-pc/ZFGB-en',
            ],
            'ZFCA' => [
                'en' => 'statics/zf-pc/ZFCA-en',
                'fr' => 'statics/zf-pc/ZFCA-fr',
            ],
            'ZFBE' => [
                'fr' => 'statics/zf-pc/ZFBE-fr',
            ],
            'ZFCH' => [
                'de' => 'statics/zf-pc/ZFCH-de',
                'en' => 'statics/zf-pc/ZFCH-en',
                'fr' => 'statics/zf-pc/ZFCH-fr',
            ],
            'ZFPH' => [
                'en' => 'statics/zf-pc/ZFPH-en',
            ],
            'ZFIN' => [
                'en' => 'statics/zf-pc/ZFIN-en',
            ],
            'ZFSG' => [
                'en' => 'statics/zf-pc/ZFSG-en',
            ],
            'ZFMY' => [
                'en' => 'statics/zf-pc/ZFMY-en',
            ],
            'ZFAU' => [
                'en' => 'statics/zf-pc/ZFAU-en',
            ],
            'ZFAT' => [
                'de' => 'statics/zf-pc/ZFAT-de'
            ],
            'ZFMX' => [
                'es' => 'statics/zf-pc/ZFMX-es',
            ],
            'ZFZA' => [
                'en' => 'statics/zf-pc/ZFZA-en',
            ],
            'ZFBR' => [
                'pt' => 'statics/zf-pc/ZFBR-pt'
            ],
            'ZFTH' => [
                'th' => 'statics/zf-pc/ZFTH-th',
            ],
            'ZFID' => [
                'id' => 'statics/zf-pc/ZFID-id',
            ],
            'ZFTW' => [
                'zh-tw' => 'statics/zf-pc/ZFTW-zh-tw'
            ],
            'ZFAR' => [
                'ar' => 'statics/zf-pc/ZFAR-ar'
            ],
            'ZFIL' => [
                'en' => 'statics/zf-pc/ZFIL-en'
            ],
            'ZFRU' => [
                'ru' => 'statics/zf-pc/ZFRU-ru'
            ],
            'ZFHK' => [
                'zh-tw' => 'statics/zf-pc/ZFHK-zh-tw'
            ],
            'ZFTR' => [
                'tr' => 'statics/zf-pc/ZFTR-tr'
            ],
            'ZFMX01' => [
                'es' => 'statics/zf-pc/ZFMX01-es'
            ],
            'ZFRO' => [
                'ro' => 'statics/zf-pc/ZFRO-ro'
            ],
            'ZFJP' => [
                'ja' => 'statics/zf-pc/ZFJP-ja'
            ],
        ],
        'secondary_domain'      => [
            'ZF'   => [
                'en' => 'http://www.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFES' => [
                'es' => 'http://es.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFFR' => [
                'fr' => 'http://fr.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFDE' => [
                'de' => 'http://de.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFPT' => [
                'pt' => 'http://pt.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFIT' => [
                'it' => 'http://it.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFIE' => [
                'en' => 'http://eur.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFNZ' => [
                'en' => 'http://nz.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFGB' => [
                'en' => 'http://uk.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFCA' => [
                'en' => 'http://ca.pc-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://ca.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFBE' => [
                'fr' => 'http://be.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFCH' => [
                'de' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFPH' => [
                'en' => 'http://ph.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFIN' => [
                'en' => 'http://in.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFSG' => [
                'en' => 'http://sg.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFMY' => [
                'en' => 'http://my.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFAU' => [
                'en' => 'http://au.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFAT' => [
                'de' => 'http://at.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFMX' => [
                'es' => 'http://latam.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFZA' => [
                'en' => 'http://za.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFBR' => [
                'pt' => 'http://br.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFTH' => [
                'th' => 'http://th.pc-zaful' . ZF_DOMAIN . '/promotion',
            ],
            /*	'ZFID' => [
                    'id' => 'http://id.pc-zaful' . ZF_DOMAIN . '/promotion',
                ],*/
            'ZFTW' => [
                'zh-tw' => 'http://tw.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFAR' => [
                'ar' => 'http://ar.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFIL' => [
                'en' => 'http://il.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFRU' => [
                'ru' => 'http://ru.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFTR' => [
                'tr' => 'http://tr.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFMX01' => [
                'es' => 'http://mx.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFRO' => [
                'ro' => 'http://ro.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFJP' => [
                'ja' => 'http://jp.pc-zaful' . ZF_DOMAIN . '/promotion'
            ],
        ],
        'home_secondary_domain' => [
            'ZF'   => [
                'en' => 'http://www.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFES' => [
                'es' => 'http://es.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFFR' => [
                'fr' => 'http://fr.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFDE' => [
                'de' => 'http://de.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFPT' => [
                'pt' => 'http://pt.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFIT' => [
                'it' => 'http://it.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFIE' => [
                'en' => 'http://eur.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFNZ' => [
                'en' => 'http://nz.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFGB' => [
                'en' => 'http://uk.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFCA' => [
                'en' => 'http://ca.pc-zaful' . ZF_DOMAIN . '/promotion/home',
                'fr' => 'http://ca.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFBE' => [
                'fr' => 'http://be.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFCH' => [
                'de' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion/home',
                'en' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion/home',
                'fr' => 'http://ch.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFPH' => [
                'en' => 'http://ph.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFIN' => [
                'en' => 'http://in.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFSG' => [
                'en' => 'http://sg.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFMY' => [
                'en' => 'http://my.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFAU' => [
                'en' => 'http://au.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFAT' => [
                'de' => 'http://at.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFMX' => [
                'es' => 'http://latam.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFZA' => [
                'en' => 'http://za.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFBR' => [
                'pt' => 'http://br.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFTH' => [
                'th' => 'http://th.pc-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            /*		'ZFID' => [
                        'id' => 'http://id.pc-zaful' . ZF_DOMAIN . '/promotion/home',
                    ],*/
            'ZFTW' => [
                'zh-tw' => 'http://tw.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFAR' => [
                'ar' => 'http://ar.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFIL' => [
                'en' => 'http://il.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFRU' => [
                'ru' => 'http://ru.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFTR' => [
                'tr' => 'http://tr.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFMX01' => [
                'es' => 'http://mx.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFRO' => [
                'ro' => 'http://ro.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFJP' => [
                'ja' => 'http://jp.pc-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
        ]
    ],
    'zf-wap'     => [
        'closed_pipeline_lang' => [ // 停止运营的渠道语言
            'ZFSG' => ['id']
        ],
        's3PublishPath'         => [
            'ZF'   => [
                'en' => 'publish/m.zaful.com/en',
                'es' => 'publish/m.zaful.com/es',
            ],
            'ZFES' => [
                'es' => 'publish/es-m.zaful.com/es',
                'en' => 'publish/es-m.zaful.com/en',
                'fr' => 'publish/es-m.zaful.com/fr',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr-m.zaful.com/fr',
                'en' => 'publish/fr-m.zaful.com/en',
            ],
            'ZFDE' => [
                'de' => 'publish/de-m.zaful.com/de',
                'en' => 'publish/de-m.zaful.com/en',
                'es' => 'publish/de-m.zaful.com/es',
            ],
            'ZFIE' => [
                'en' => 'publish/eur-m.zaful.com/en',
                'fr' => 'publish/eur-m.zaful.com/fr',
                'de' => 'publish/eur-m.zaful.com/de',
                'es' => 'publish/eur-m.zaful.com/es',
                'pt' => 'publish/eur-m.zaful.com/pt',
                'it' => 'publish/eur-m.zaful.com/it',
                'ru' => 'publish/eur-m.zaful.com/ru',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz-m.zaful.com/en',
            ],
            'ZFGB' => [
                'en' => 'publish/uk-m.zaful.com/en',
                'es' => 'publish/uk-m.zaful.com/es',
            ],
            'ZFCA' => [
                'en' => 'publish/ca-m.zaful.com/en',
                'fr' => 'publish/ca-m.zaful.com/fr',
            ],
            'ZFBE' => [
                'fr' => 'publish/be-m.zaful.com/fr',
                'en' => 'publish/be-m.zaful.com/en',
                'de' => 'publish/be-m.zaful.com/de',
            ],
            'ZFCH' => [
                'de' => 'publish/ch-m.zaful.com/de',
                'en' => 'publish/ch-m.zaful.com/en',
                'fr' => 'publish/ch-m.zaful.com/fr',
                'it' => 'publish/ch-m.zaful.com/it',
                'es' => 'publish/ch-m.zaful.com/es',
            ],
            'ZFPH' => [
                'en' => 'publish/ph-m.zaful.com/en',
            ],
            'ZFIN' => [
                'en' => 'publish/in-m.zaful.com/en',
            ],
            'ZFSG' => [
                'en' => 'publish/sg-m.zaful.com/en',
                'th' => 'publish/sg-m.zaful.com/th',
                'id' => 'publish/sg-m.zaful.com/id',
            ],
            'ZFMY' => [
                'en' => 'publish/my-m.zaful.com/en',
            ],
            'ZFAU' => [
                'en' => 'publish/au-m.zaful.com/en',
            ],
            'ZFAT' => [
                'de' => 'publish/at-m.zaful.com/de',
                'en' => 'publish/at-m.zaful.com/en'
            ],
            'ZFMX' => [
                'es' => 'publish/latam-m.zaful.com/es',
                'en' => 'publish/latam-m.zaful.com/en',
            ],
            'ZFZA' => [
                'en' => 'publish/za-m.zaful.com/en',
            ],
            'ZFBR' => [
                'pt' => 'publish/br-m.zaful.com/pt',
                'en' => 'publish/br-m.zaful.com/en'
            ],
            'ZFTH' => [
                'th' => 'publish/th-m.zaful.com/th',
                'en' => 'publish/th-m.zaful.com/en',
            ],
            'ZFID' => [
                'id' => 'publish/id-m.zaful.com/id',
                'en' => 'publish/id-m.zaful.com/en',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw-m.zaful.com/zh-tw',
                'en' => 'publish/tw-m.zaful.com/en'
            ],
            'ZFAR' => [
                'ar' => 'publish/ar-m.zaful.com/ar',
                'en' => 'publish/ar-m.zaful.com/en'
            ],
            'ZFIT' => [
                'it' => 'publish/it-m.zaful.com/it',
                'en' => 'publish/it-m.zaful.com/en',
                'de' => 'publish/it-m.zaful.com/de'
            ],
            'ZFIL' => [
                'en' => 'publish/il-m.zaful.com/en',
                'he' => 'publish/il-m.zaful.com/he'
            ],
            'ZFRU' => [
                'ru' => 'publish/ru-m.zaful.com/ru',
                'en' => 'publish/ru-m.zaful.com/en'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk-m.zaful.com/zh-tw'
            ],
            'ZFTR' => [
                'tr' => 'publish/tr-m.zaful.com/tr',
                'en' => 'publish/tr-m.zaful.com/en'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx-m.zaful.com/es'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro-m.zaful.com/ro'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp-m.zaful.com/ja',
                'en' => 'publish/jp-m.zaful.com/en'
            ],
            'ZFVN' => [
	            'vi' => 'publish/vn-m.zaful.com/vi',
	            'en' => 'publish/vn-m.zaful.com/en'
            ],
        ],
        's3HomePublishPath'     => [
            'ZF'   => [
                'en' => 'publish/m.zaful.com/en/home',
            ],
            'ZFES' => [
                'es' => 'publish/es-m.zaful.com/es/home',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr-m.zaful.com/fr/home',
            ],
            'ZFDE' => [
                'de' => 'publish/de-m.zaful.com/de/home',
            ],
            'ZFIE' => [
                'en' => 'publish/eur-m.zaful.com/en/home',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz-m.zaful.com/en/home',
            ],
            'ZFGB' => [
                'en' => 'publish/uk-m.zaful.com/en/home',
            ],
            'ZFCA' => [
                'en' => 'publish/ca-m.zaful.com/en/home',
                'fr' => 'publish/ca-m.zaful.com/fr/home',
            ],
            'ZFBE' => [
                'fr' => 'publish/be-m.zaful.com/fr/home',
            ],
            'ZFCH' => [
                'de' => 'publish/ch-m.zaful.com/de/home',
                'en' => 'publish/ch-m.zaful.com/en/home',
                'fr' => 'publish/ch-m.zaful.com/fr/home',
            ],
            'ZFPH' => [
                'en' => 'publish/ph-m.zaful.com/de/home',
            ],
            'ZFIN' => [
                'en' => 'publish/in-m.zaful.com/en/home',
            ],
            'ZFSG' => [
                'en' => 'publish/sg-m.zaful.com/en/home',
            ],
            'ZFMY' => [
                'en' => 'publish/my-m.zaful.com/en/home',
            ],
            'ZFAU' => [
                'en' => 'publish/au-m.zaful.com/en/home',
            ],
            'ZFAT' => [
                'de' => 'publish/at-m.zaful.com/de/home'
            ],
            'ZFMX' => [
                'es' => 'publish/latam-m.zaful.com/es/home',
            ],
            'ZFZA' => [
                'en' => 'publish/za-m.zaful.com/en/home',
            ],
            'ZFBR' => [
                'pt' => 'publish/br-m.zaful.com/pt/home'
            ],
            'ZFTH' => [
                'th' => 'publish/th-m.zaful.com/th/home',
            ],
            'ZFID' => [
                'id' => 'publish/id-m.zaful.com/id/home',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/home'
            ],
            'ZFAR' => [
                'ar' => 'publish/ar-m.zaful.com/ar/home'
            ],
            'ZFIL' => [
                'en' => 'publish/il-m.zaful.com/en/home'
            ],
            'ZFRU' => [
                'ru' => 'publish/ru-m.zaful.com/ru/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/home'
            ],
            'ZFTR' => [
                'tr' => 'publish/tr-m.zaful.com/tr/home'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx-m.zaful.com/es/home'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro-m.zaful.com/ro/home'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp-m.zaful.com/ja/home'
            ],
        ],
        's3StaticPath'          => [
            'ZF'   => [
                'en' => 'statics/zf-wap/ZF-en',
                'es' => 'statics/zf-wap/ZF-es',
            ],
            'ZFES' => [
                'es' => 'statics/zf-wap/ZFES-es',
                'en' => 'statics/zf-wap/ZFES-en',
                'fr' => 'statics/zf-wap/ZFES-fr',
            ],
            'ZFFR' => [
                'fr' => 'statics/zf-wap/ZFFR-fr',
                'en' => 'statics/zf-wap/ZFFR-en'
            ],
            'ZFDE' => [
                'de' => 'statics/zf-wap/ZFDE-de',
                'en' => 'statics/zf-wap/ZFDE-en',
                'es' => 'statics/zf-wap/ZFDE-es',
            ],
            'ZFIE' => [
                'en' => 'statics/zf-wap/ZFIE-en',
                'fr' => 'statics/zf-wap/ZFIE-fr',
                'de' => 'statics/zf-wap/ZFIE-de',
                'es' => 'statics/zf-wap/ZFIE-es',
                'pt' => 'statics/zf-wap/ZFIE-pt',
                'it' => 'statics/zf-wap/ZFIE-it',
                'ru' => 'statics/zf-wap/ZFIE-ru',
            ],
            'ZFNZ' => [
                'en' => 'statics/zf-wap/ZFNZ-en',
            ],
            'ZFGB' => [
                'en' => 'statics/zf-wap/ZFGB-en',
                'es' => 'statics/zf-wap/ZFGB-es',
            ],
            'ZFCA' => [
                'en' => 'statics/zf-wap/ZFCA-en',
                'fr' => 'statics/zf-wap/ZFCA-fr',
            ],
            'ZFBE' => [
                'fr' => 'statics/zf-wap/ZFBE-fr',
                'en' => 'statics/zf-wap/ZFBE-en',
                'de' => 'statics/zf-wap/ZFBE-de',
            ],
            'ZFCH' => [
                'de' => 'statics/zf-wap/ZFCH-de',
                'en' => 'statics/zf-wap/ZFCH-en',
                'fr' => 'statics/zf-wap/ZFCH-fr',
                'it' => 'statics/zf-wap/ZFCH-it',
                'es' => 'statics/zf-wap/ZFCH-es',
            ],
            'ZFPH' => [
                'en' => 'statics/zf-wap/ZFPH-en',
            ],
            'ZFIN' => [
                'en' => 'statics/zf-wap/ZFIN-en',
            ],
            'ZFSG' => [
                'en' => 'statics/zf-wap/ZFSG-en',
                'th' => 'statics/zf-wap/ZFSG-th',
                'id' => 'statics/zf-wap/ZFSG-id',
            ],
            'ZFMY' => [
                'en' => 'statics/zf-wap/ZFMY-en',
            ],
            'ZFAU' => [
                'en' => 'statics/zf-wap/ZFIE-en',
            ],
            'ZFAT' => [
                'de' => 'statics/zf-wap/ZFAT-de',
                'en' => 'statics/zf-wap/ZFAT-en'
            ],
            'ZFMX' => [
                'es' => 'statics/zf-wap/ZFMX-es',
            ],
            'ZFZA' => [
                'en' => 'statics/zf-wap/ZFZA-en',
            ],
            'ZFBR' => [
                'pt' => 'statics/zf-wap/ZFBR-pt',
                'en' => 'statics/zf-wap/ZFBR-en'
            ],
            'ZFTH' => [
                'th' => 'statics/zf-wap/ZFTH-th',
                'en' => 'statics/zf-wap/ZFTH-en',
            ],
            'ZFID' => [
                'id' => 'statics/zf-wap/ZFID-id',
                'en' => 'statics/zf-wap/ZFID-en',
            ],
            'ZFTW' => [
                'zh-tw' => 'statics/zf-wap/ZFTW-zh-tw',
                'en' => 'statics/zf-wap/ZFTW-en'
            ],
            'ZFAR' => [
                'ar' => 'statics/zf-wap/ZFAR-ar',
                'en' => 'statics/zf-wap/ZFAR-en'
            ],
            'ZFIT' => [
                'it' => 'statics/zf-wap/ZFIT-it',
                'en' => 'statics/zf-wap/ZFIT-en',
                'de' => 'statics/zf-wap/ZFIT-de'
            ],
            'ZFIL' => [
                'en' => 'statics/zf-wap/ZFIL-en',
                'he' => 'statics/zf-wap/ZFIL-he'
            ],
            'ZFRU' => [
                'ru' => 'statics/zf-wap/ZFRU-ru',
                'en' => 'statics/zf-wap/ZFRU-en'
            ],
            'ZFHK' => [
                'zh-tw' => 'statics/zf-wap/ZFHK-zh-tw'
            ],
            'ZFTR' => [
                'tr' => 'statics/zf-wap/ZFTR-tr',
                'en' => 'statics/zf-wap/ZFTR-en'
            ],
            'ZFMX01' => [
                'es' => 'statics/zf-wap/ZFMX01-es'
            ],
            'ZFRO' => [
                'ro' => 'statics/zf-wap/ZFRO-ro'
            ],
            'ZFJP' => [
                'ja' => 'statics/zf-wap/ZFJP-ja',
                'en' => 'statics/zf-wap/ZFJP-en'
            ],
            'ZFVN' => [
	            'vi' => 'statics/zf-wap/ZFVN-vi',
	            'en' => 'statics/zf-wap/ZFVN-en'
            ],
        ],
        'secondary_domain'      => [
            'ZF'   => [
                'en' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'es' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFES' => [
                'es' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFFR' => [
                'fr' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFDE' => [
                'de' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'es' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFIE' => [
                'en' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'de' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'es' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'pt' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'it' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'ru' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFNZ' => [
                'en' => 'http://nz-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFGB' => [
                'en' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'es' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFCA' => [
                'en' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFBE' => [
                'fr' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'de' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFCH' => [
                'de' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'fr' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'it' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'es' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFPH' => [
                'en' => 'http://ph-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFIN' => [
                'en' => 'http://in-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFSG' => [
                'en' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'th' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'id' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFMY' => [
                'en' => 'http://my-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFAU' => [
                'en' => 'http://au-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFAT' => [
                'de' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFMX' => [
                'es' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFZA' => [
                'en' => 'http://za-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            'ZFBR' => [
                'pt' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFTH' => [
                'th' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
            /*		'ZFID' => [
                        'id' => 'http://id-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                    ],*/
            'ZFTW' => [
                'zh-tw' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFAR' => [
                'ar' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFIT' => [
                'it' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'de' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFIL' => [
                'en' => 'http://il-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'he' => 'http://il-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFRU' => [
                'ru' => 'http://ru-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://ru-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFTR' => [
                'tr' => 'http://tr-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://tr-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFMX01' => [
                'es' => 'http://mx-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFRO' => [
                'ro' => 'http://ro-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFJP' => [
                'ja' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion',
                'en' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion'
            ],
            'ZFVN' => [
	            'vi' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion',
	            'en' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion',
            ],
        ],
        'home_secondary_domain' => [
            'ZF'   => [
                'en' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFES' => [
                'es' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFFR' => [
                'fr' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFDE' => [
                'de' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFIE' => [
                'en' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFNZ' => [
                'en' => 'http://nz-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFGB' => [
                'en' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFCA' => [
                'en' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
                'fr' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFBE' => [
                'fr' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFCH' => [
                'de' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
                'en' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
                'fr' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFPH' => [
                'en' => 'http://ph-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFIN' => [
                'en' => 'http://in-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFSG' => [
                'en' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFMY' => [
                'en' => 'http://my-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFAU' => [
                'en' => 'http://au-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFAT' => [
                'de' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFMX' => [
                'es' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFZA' => [
                'en' => 'http://za-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            'ZFBR' => [
                'pt' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFTH' => [
                'th' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
            ],
            /*	'ZFID' => [
                    'id' => 'http://id-m.wap-zaful' . ZF_DOMAIN . '/promotion/home',
                ],*/
            'ZFTW' => [
                'zh-tw' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFAR' => [
                'ar' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFIL' => [
                'en' => 'http://il-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFRU' => [
                'ru' => 'http://ru-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFTR' => [
                'tr' => 'http://tr-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFMX01' => [
                'es' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFRO' => [
                'ro' => 'http://ro-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
            'ZFJP' => [
                'ja' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion/home'
            ],
        ]
    ],
    'zf-app'     => [
        'closed_pipeline_lang' => [ // 停止运营的渠道语言
            'ZFSG' => ['id']
        ],
        's3PublishPath'         => [
            'ZF'   => [
                'en' => 'publish/m.zaful.com/en/app',
                'es' => 'publish/m.zaful.com/es/app',
            ],
            'ZFES' => [
                'es' => 'publish/es-m.zaful.com/es/app',
                'en' => 'publish/es-m.zaful.com/en/app',
                'fr' => 'publish/es-m.zaful.com/fr/app',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr-m.zaful.com/fr/app',
                'en' => 'publish/fr-m.zaful.com/en/app',
            ],
            'ZFDE' => [
                'de' => 'publish/de-m.zaful.com/de/app',
                'en' => 'publish/de-m.zaful.com/en/app',
                'es' => 'publish/de-m.zaful.com/es/app',
            ],
            'ZFIE' => [
                'en' => 'publish/eur-m.zaful.com/en/app',
                'fr' => 'publish/eur-m.zaful.com/fr/app',
                'de' => 'publish/eur-m.zaful.com/de/app',
                'es' => 'publish/eur-m.zaful.com/es/app',
                'pt' => 'publish/eur-m.zaful.com/pt/app',
                'it' => 'publish/eur-m.zaful.com/it/app',
                'ru' => 'publish/eur-m.zaful.com/ru/app',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz-m.zaful.com/en/app',
            ],
            'ZFGB' => [
                'en' => 'publish/uk-m.zaful.com/en/app',
                'es' => 'publish/uk-m.zaful.com/es/app',
            ],
            'ZFCA' => [
                'en' => 'publish/ca-m.zaful.com/en/app',
                'fr' => 'publish/ca-m.zaful.com/fr/app',
            ],
            'ZFBE' => [
                'fr' => 'publish/be-m.zaful.com/fr/app',
                'en' => 'publish/be-m.zaful.com/en/app',
                'de' => 'publish/be-m.zaful.com/de/app',
            ],
            'ZFCH' => [
                'de' => 'publish/ch-m.zaful.com/de/app',
                'en' => 'publish/ch-m.zaful.com/en/app',
                'fr' => 'publish/ch-m.zaful.com/fr/app',
                'it' => 'publish/ch-m.zaful.com/it/app',
                'es' => 'publish/ch-m.zaful.com/es/app',
            ],
            'ZFPH' => [
                'en' => 'publish/ph-m.zaful.com/en/app',
            ],
            'ZFIN' => [
                'en' => 'publish/in-m.zaful.com/en/app',
            ],
            'ZFSG' => [
                'en' => 'publish/sg-m.zaful.com/en/app',
                'th' => 'publish/sg-m.zaful.com/th/app',
                'id' => 'publish/sg-m.zaful.com/id/app',
            ],
            'ZFMY' => [
                'en' => 'publish/my-m.zaful.com/en/app',
            ],
            'ZFAU' => [
                'en' => 'publish/au-m.zaful.com/en/app',
            ],
            'ZFAT' => [
                'de' => 'publish/at-m.zaful.com/de/app',
                'en' => 'publish/at-m.zaful.com/en/app'
            ],
            'ZFMX' => [
                'es' => 'publish/latam-m.zaful.com/es/app',
                'en' => 'publish/latam-m.zaful.com/en/app',
            ],
            'ZFZA' => [
                'en' => 'publish/za-m.zaful.com/en/app',
            ],
            'ZFBR' => [
                'pt' => 'publish/br-m.zaful.com/pt/app',
                'en' => 'publish/br-m.zaful.com/en/app'
            ],
            'ZFTH' => [
                'th' => 'publish/th-m.zaful.com/th/app',
                'en' => 'publish/th-m.zaful.com/en/app',
            ],
            'ZFID' => [
                'id' => 'publish/id-m.zaful.com/id/app',
                'en' => 'publish/id-m.zaful.com/en/app',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/app',
                'en' => 'publish/tw-m.zaful.com/en/app',
            ],
            'ZFAR' => [
                'ar' => 'publish/ar-m.zaful.com/ar/app',
                'en' => 'publish/ar-m.zaful.com/en/app'
            ],
            'ZFIT' => [
                'it' => 'publish/it-m.zaful.com/it/app',
                'en' => 'publish/it-m.zaful.com/en/app',
                'de' => 'publish/it-m.zaful.com/de/app',
            ],
            'ZFIL' => [
                'en' => 'publish/il-m.zaful.com/en/app',
                'he' => 'publish/il-m.zaful.com/he/app'
            ],
            'ZFRU' => [
                'ru' => 'publish/ru-m.zaful.com/ru/app',
                'en' => 'publish/ru-m.zaful.com/en/app'
            ],
            'ZFTR' => [
                'tr' => 'publish/tr-m.zaful.com/tr/app',
                'en' => 'publish/tr-m.zaful.com/en/app'
            ],
            'ZFVN' => [
                'vi' => 'publish/vn-m.zaful.com/vi/app',
                'en' => 'publish/vn-m.zaful.com/en/app'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp-m.zaful.com/ja/app',
                'en' => 'publish/jp-m.zaful.com/en/app'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/app'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx-m.zaful.com/es/app'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro-m.zaful.com/ro/app'
            ],
        ],
        's3HomePublishPath'     => [
            'ZF'   => [
                'en' => 'publish/m.zaful.com/en/app/home',
                'es' => 'publish/m.zaful.com/es/app/home',
            ],
            'ZFES' => [
                'es' => 'publish/es-m.zaful.com/es/app/home',
                'en' => 'publish/es-m.zaful.com/en/app/home',
                'fr' => 'publish/es-m.zaful.com/fr/app/home',
            ],
            'ZFFR' => [
                'fr' => 'publish/fr-m.zaful.com/fr/app/home',
                'en' => 'publish/fr-m.zaful.com/en/app/home',
            ],
            'ZFDE' => [
                'de' => 'publish/de-m.zaful.com/de/app/home',
                'en' => 'publish/de-m.zaful.com/en/app/home',
                'es' => 'publish/de-m.zaful.com/es/app/home',
            ],
            'ZFIE' => [
                'en' => 'publish/ie-m.zaful.com/en/app/home',
                'fr' => 'publish/ie-m.zaful.com/fr/app/home',
                'de' => 'publish/ie-m.zaful.com/de/app/home',
                'es' => 'publish/ie-m.zaful.com/es/app/home',
                'pt' => 'publish/ie-m.zaful.com/pt/app/home',
                'it' => 'publish/ie-m.zaful.com/it/app/home',
                'ru' => 'publish/ie-m.zaful.com/ru/app/home',
            ],
            'ZFNZ' => [
                'en' => 'publish/nz-m.zaful.com/en/app/home',
            ],
            'ZFGB' => [
                'en' => 'publish/uk-m.zaful.com/en/app/home',
                'es' => 'publish/uk-m.zaful.com/es/app/home',
            ],
            'ZFCA' => [
                'en' => 'publish/ca-m.zaful.com/en/app/home',
                'fr' => 'publish/ca-m.zaful.com/fr/app/home',
            ],
            'ZFBE' => [
                'fr' => 'publish/be-m.zaful.com/fr/app/home',
                'en' => 'publish/be-m.zaful.com/en/app/home',
                'de' => 'publish/be-m.zaful.com/de/app/home',
            ],
            'ZFCH' => [
                'de' => 'publish/ch-m.zaful.com/de/app/home',
                'en' => 'publish/ch-m.zaful.com/en/app/home',
                'fr' => 'publish/ch-m.zaful.com/fr/app/home',
                'it' => 'publish/ch-m.zaful.com/it/app/home',
                'es' => 'publish/ch-m.zaful.com/es/app/home',
            ],
            'ZFPH' => [
                'en' => 'publish/ph-m.zaful.com/en/app/home',
            ],
            'ZFIN' => [
                'en' => 'publish/in-m.zaful.com/en/app/home',
            ],
            'ZFSG' => [
                'en' => 'publish/sg-m.zaful.com/en/app/home',
                'th' => 'publish/sg-m.zaful.com/th/app/home',
                'id' => 'publish/sg-m.zaful.com/id/app/home',
            ],
            'ZFMY' => [
                'en' => 'publish/my-m.zaful.com/en/app/home',
            ],
            'ZFAU' => [
                'en' => 'publish/sz-m.zaful.com/de/app/home',
            ],
            'ZFAT' => [
                'de' => 'publish/at-m.zaful.com/de/app/home',
                'en' => 'publish/at-m.zaful.com/en/app/home'
            ],
            'ZFMX' => [
                'es' => 'publish/mx-m.zaful.com/es/app/home',
                'en' => 'publish/mx-m.zaful.com/en/app/home',
            ],
            'ZFZA' => [
                'en' => 'publish/za-m.zaful.com/en/app/home',
            ],
            'ZFBR' => [
                'pt' => 'publish/br-m.zaful.com/pt/app/home',
                'en' => 'publish/br-m.zaful.com/en/app/home'
            ],
            'ZFTH' => [
                'th' => 'publish/th-m.zaful.com/th/app/home',
                'en' => 'publish/th-m.zaful.com/en/app/home',
            ],
            'ZFID' => [
                'id' => 'publish/id-m.zaful.com/id/app/home',
                'en' => 'publish/id-m.zaful.com/en/app/home',
            ],
            'ZFTW' => [
                'zh-tw' => 'publish/tw-m.zaful.com/zh-tw/app/home',
                'en' => 'publish/tw-m.zaful.com/en/app/home'
            ],
            'ZFAR' => [
                'ar' => 'publish/ar-m.zaful.com/ar/app/home',
                'en' => 'publish/ar-m.zaful.com/en/app/home'
            ],
            'ZFVN' => [
                'vi' => 'publish/vn-m.zaful.com/vi/app/home',
                'en' => 'publish/vn-m.zaful.com/en/app/home'
            ],
            'ZFJP' => [
                'ja' => 'publish/jp-m.zaful.com/ja/app/home',
                'en' => 'publish/jp-m.zaful.com/en/app/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'publish/hk-m.zaful.com/zh-tw/app/home'
            ],
            'ZFMX01' => [
                'es' => 'publish/mx-m.zaful.com/es/app/home'
            ],
            'ZFRO' => [
                'ro' => 'publish/ro-m.zaful.com/ro/app/home'
            ],
        ],
        's3StaticPath'          => [
            'ZF'   => [
                'en' => 'statics/zf-wap/ZF-en',
                'es' => 'statics/zf-wap/ZF-es',
            ],
            'ZFES' => [
                'es' => 'statics/zf-wap/ZFES-es',
                'en' => 'statics/zf-wap/ZFES-en',
                'fr' => 'statics/zf-wap/ZFES-fr',
            ],
            'ZFFR' => [
                'fr' => 'statics/zf-wap/ZFFR-fr',
                'en' => 'statics/zf-wap/ZFFR-en',
            ],
            'ZFDE' => [
                'de' => 'statics/zf-wap/ZFDE-de',
                'en' => 'statics/zf-wap/ZFDE-en',
                'es' => 'statics/zf-wap/ZFDE-es',
            ],
            'ZFIE' => [
                'en' => 'statics/zf-wap/ZFIE-en',
                'fr' => 'statics/zf-wap/ZFIE-fr',
                'de' => 'statics/zf-wap/ZFIE-de',
                'es' => 'statics/zf-wap/ZFIE-es',
                'pt' => 'statics/zf-wap/ZFIE-pt',
                'it' => 'statics/zf-wap/ZFIE-it',
                'ru' => 'statics/zf-wap/ZFIE-ru',
            ],
            'ZFNZ' => [
                'en' => 'statics/zf-wap/ZFNZ-en',
            ],
            'ZFGB' => [
                'en' => 'statics/zf-wap/ZFGB-en',
                'es' => 'statics/zf-wap/ZFGB-es',
            ],
            'ZFCA' => [
                'en' => 'statics/zf-wap/ZFCA-en',
                'fr' => 'statics/zf-wap/ZFCA-fr',
            ],
            'ZFBE' => [
                'fr' => 'statics/zf-wap/ZFBE-fr',
                'en' => 'statics/zf-wap/ZFBE-en',
                'de' => 'statics/zf-wap/ZFBE-de',
            ],
            'ZFCH' => [
                'de' => 'statics/zf-wap/ZFCH-de',
                'en' => 'statics/zf-wap/ZFCH-en',
                'fr' => 'statics/zf-wap/ZFCH-fr',
                'it' => 'statics/zf-wap/ZFCH-it',
                'es' => 'statics/zf-wap/ZFCH-es',
            ],
            'ZFPH' => [
                'en' => 'statics/zf-wap/ZFPH-en',
            ],
            'ZFIN' => [
                'en' => 'statics/zf-wap/ZFIN-en',
            ],
            'ZFSG' => [
                'en' => 'statics/zf-wap/ZFSG-en',
                'th' => 'statics/zf-wap/ZFSG-th',
                'id' => 'statics/zf-wap/ZFSG-id',
            ],
            'ZFMY' => [
                'en' => 'statics/zf-wap/ZFMY-en',
            ],
            'ZFAU' => [
                'en' => 'statics/zf-wap/ZFAU-en',
            ],
            'ZFAT' => [
                'de' => 'statics/zf-wap/ZFAT-de',
                'en' => 'statics/zf-wap/ZFAT-en'
            ],
            'ZFMX' => [
                'es' => 'statics/zf-wap/ZFMX-es',
                'en' => 'statics/zf-wap/ZFMX-en',
            ],
            'ZFZA' => [
                'en' => 'statics/zf-wap/ZFZA-en',
            ],
            'ZFBR' => [
                'pt' => 'statics/zf-wap/ZFBR-pt',
                'en' => 'statics/zf-wap/ZFBR-en'
            ],
            'ZFTH' => [
                'th' => 'statics/zf-wap/ZFTH-th',
                'en' => 'statics/zf-wap/ZFTH-en',
            ],
            'ZFID' => [
                'id' => 'statics/zf-wap/ZFID-id',
                'en' => 'statics/zf-wap/ZFID-en',
            ],
            'ZFTW' => [
                'zh-tw' => 'statics/zf-wap/ZFTW-zh-tw',
                'en' => 'statics/zf-wap/ZFTW-en'
            ],
            'ZFAR' => [
                'ar' => 'statics/zf-wap/ZFAR-ar',
                'en' => 'statics/zf-wap/ZFAR-en'
            ],
            'ZFIT' => [
                'it' => 'statics/zf-wap/ZFIT-it',
                'en' => 'statics/zf-wap/ZFIT-en',
                'de' => 'statics/zf-wap/ZFIT-de',
            ],
            'ZFIL' => [
                'en' => 'statics/zf-wap/ZFIL-en',
                'he' => 'statics/zf-wap/ZFIL-he'
            ],
            'ZFRU' => [
                'ru' => 'statics/zf-wap/ZFRU-ru',
                'en' => 'statics/zf-wap/ZFRU-en'
            ],
            'ZFTR' => [
                'tr' => 'statics/zf-wap/ZFTR-tr',
                'en' => 'statics/zf-wap/ZFTR-en'
            ],
            'ZFVN' => [
                'vi' => 'statics/zf-wap/ZFVN-vi',
                'en' => 'statics/zf-wap/ZFVN-en'
            ],
            'ZFJP' => [
                'ja' => 'statics/zf-wap/ZFJP-ja',
                'en' => 'statics/zf-wap/ZFJP-en'
            ],
            'ZFHK' => [
                'zh-tw' => 'statics/zf-wap/ZFHK-zh-tw'
            ],
            'ZFMX01' => [
                'es' => 'statics/zf-wap/ZFMX01-es'
            ],
            'ZFRO' => [
                'ro' => 'statics/zf-wap/ZFRO-ro'
            ],
        ],
        'secondary_domain'      => [
            'ZF'   => [
                'en' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'es' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFES' => [
                'es' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'fr' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFFR' => [
                'fr' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFDE' => [
                'de' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'es' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFIE' => [
                'en' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'fr' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'de' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'es' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'pt' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'it' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'ru' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFNZ' => [
                'en' => 'http://nz-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFGB' => [
                'en' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'es' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFCA' => [
                'en' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'fr' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFBE' => [
                'fr' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'de' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFCH' => [
                'de' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'fr' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'it' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'es' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFPH' => [
                'en' => 'http://ph-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFIN' => [
                'en' => 'http://in-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFSG' => [
                'en' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'th' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'id' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFMY' => [
                'en' => 'http://my-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFAU' => [
                'en' => 'http://au-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFAT' => [
                'de' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFMX' => [
                'es' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFZA' => [
                'en' => 'http://za-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            'ZFBR' => [
                'pt' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFTH' => [
                'th' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
            ],
            /*	'ZFID' => [
                    'id' => 'http://id-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                ],*/
            'ZFTW' => [
                'zh-tw' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFAR' => [
                'ar' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFIT' => [
                'it' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'de' => 'http://it-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFIL' => [
                'en' => 'http://il-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'he' => 'http://il-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFRU' => [
                'ru' => 'http://ru-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://ru-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFTR' => [
                'tr' => 'http://tr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://tr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFVN' => [
                'vi' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFJP' => [
                'ja' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion/app',
                'en' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFMX01' => [
                'es' => 'http://mx-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
            'ZFRO' => [
                'ro' => 'http://ro-m.wap-zaful' . ZF_DOMAIN . '/promotion/app'
            ],
        ],
        'home_secondary_domain' => [
            'ZF'   => [
                'en' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'es' => 'http://m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFES' => [
                'es' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'fr' => 'http://es-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFFR' => [
                'fr' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://fr-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFDE' => [
                'de' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'es' => 'http://de-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFIE' => [
                'en' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'fr' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'de' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'es' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'pt' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'it' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'ru' => 'http://eur-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFNZ' => [
                'en' => 'http://nz-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFGB' => [
                'en' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'es' => 'http://uk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFCA' => [
                'en' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'fr' => 'http://ca-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFBE' => [
                'fr' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'de' => 'http://be-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFCH' => [
                'de' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'fr' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'it' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'es' => 'http://ch-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFPH' => [
                'en' => 'http://ph-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFIN' => [
                'en' => 'http://in-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFSG' => [
                'en' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'th' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'id' => 'http://sg-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFMY' => [
                'en' => 'http://my-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFAU' => [
                'en' => 'http://au-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFAT' => [
                'de' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://at-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFMX' => [
                'es' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://latam-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFZA' => [
                'en' => 'http://za-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFBR' => [
                'pt' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://br-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            /*
            'ZFTH' => [
                'th' => 'http://th-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFID' => [
                'id' => 'http://id-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
            ],
            'ZFTW' => [
                'zh-tw' => 'http://tw-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFAR' => [
                'ar' => 'http://ar-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],*/

            'ZFVN' => [
                'vi' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://vn-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFJP' => [
                'ja' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home',
                'en' => 'http://jp-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFHK' => [
                'zh-tw' => 'http://hk-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFMX01' => [
                'es' => 'http://mx-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
            'ZFRO' => [
                'ro' => 'http://ro-m.wap-zaful' . ZF_DOMAIN . '/promotion/app/home'
            ],
        ]
    ],
    /**************************************** GB *******************************************/
    'gb-pc'      => [
        's3PublishPath'         => [
            'GB'    => [
                'en' => 'publish/gb-pc/www.gearbest.net/en/activity'
            ],
            'GBES'  => ['ep' => 'publish/gb-pc/es.gearbest.net/ep/activity'],
            'GBFR'  => ['fr' => 'publish/gb-pc/fr.gearbest.net/fr/activity'],
            'GBRU'  => ['ru' => 'publish/gb-pc/ru.gearbest.net/ru/activity'],
            'GBPT'  => ['po' => 'publish/gb-pc/pt.gearbest.net/po/activity'],
            'GBIT'  => ['it' => 'publish/gb-pc/it.gearbest.net/it/activity'],
            'GBDE'  => ['de' => 'publish/gb-pc/de.gearbest.net/de/activity'],
            'GBUK'  => ['en' => 'publish/gb-pc/uk.gearbest.net/en/activity'],
            'GBUS'  => ['en' => 'publish/gb-pc/us.gearbest.net/en/activity'],
            'GBBR'  => ['pt-br' => 'publish/gb-pc/br.gearbest.net/pt-br/activity'],
            'GBTR'  => ['tr' => 'publish/gb-pc/tr.gearbest.net/tr/activity'],
            'GBMX'  => ['ep-mx' => 'publish/gb-pc/mx.gearbest.net/ep-mx/activity'],
            'GBMA'  => ['fr' => 'publish/gb-pc/ma.gearbest.net/fr/activity'],
            'GBGR'  => ['el' => 'publish/gb-pc/gr.gearbest.net/el/activity'],
            'GBHU'  => ['hu' => 'publish/gb-pc/hu.gearbest.net/hu/activity'],
            'GBNL'  => ['nl' => 'publish/gb-pc/nl.gearbest.net/nl/activity'],
            'GBSK'  => ['sk' => 'publish/gb-pc/sk.gearbest.net/sk/activity'],
            'GBRO'  => ['ro' => 'publish/gb-pc/ro.gearbest.net/ro/activity'],
            'GBCZ'  => ['cs' => 'publish/gb-pc/cz.gearbest.net/cs/activity'],
            'GBAU'  => ['en' => 'publish/gb-pc/au.gearbest.net/en/activity'],
            'GBIN'  => ['en' => 'publish/gb-pc/in.gearbest.net/en/activity'],
            'GBJP'  => ['ja' => 'publish/gb-pc/jp.gearbest.net/ja/activity'],
            /*'GBUA' => ['uk' => 'publish/gb-pc/ua.gearbest.net/uk/activity'],
            'GBIL' => ['he' => 'publish/gb-pc/il.gearbest.net/he/activity'],
            'GBKZ' => ['kk' => 'publish/gb-pc/kz.gearbest.net/kk/activity'],
            'GBTH' => ['th' => 'publish/gb-pc/th.gearbest.net/th/activity'],
            'GBVN' => ['vi' => 'publish/gb-pc/vn.gearbest.net/vi/activity'],
            'GBID' => ['id' => 'publish/gb-pc/id.gearbest.net/id/activity'],*/
            'GBPL'  => ['pl' => 'publish/gb-pc/pl.gearbest.net/pl/activity'],
            'GBSTY' => ['en' => 'publish/gb-pc/stylebest.gearbest.net/en/activity'],
            'GBGAG' => ['en' => 'publish/gb-pc/gagabop.gearbest.net/en/activity'],
            'GBCOZ' => ['en' => 'publish/gb-pc/cozyvoices.gearbest.net/en/activity'],
        ],
        'ad_s3PublishPath'      => [
            'en'    => 'publish/gb-pc/www/ad',
            'ep'    => 'publish/gb-pc/ep/ad',
            'fr'    => 'publish/gb-pc/fr/ad',
            'ru'    => 'publish/gb-pc/ru/ad',
            'po'    => 'publish/gb-pc/po/ad',
            'it'    => 'publish/gb-pc/it/ad',
            'de'    => 'publish/gb-pc/de/ad',
            'en_gb' => 'publish/gb-pc/ak/ad',
            'en_us' => 'publish/gb-pc/us/ad',
            'en_au' => 'publish/gb-pc/au/ad',
            'pt_br' => 'publish/gb-pc/br/ad',
            'tr'    => 'publish/gb-pc/tr/ad',
            'pl'    => 'publish/gb-pc/pl/ad',
            'en_in' => 'publish/gb-pc/in/ad',
            'el'    => 'publish/gb-pc/el/ad',
            'ep-mx' => 'publish/gb-pc/mx/ad',
            'hu'    => 'publish/gb-pc/hu/ad',
            'sk'    => 'publish/gb-pc/sk/ad',
            'cs'    => 'publish/gb-pc/cs/ad',
            'nl'    => 'publish/gb-pc/nl/ad',
            'ro'    => 'publish/gb-pc/ro/ad',
            'fr_ma' => 'publish/gb-pc/ma/ad',
            //'ja'    => 'publish/gb-pc/jp/ad',
            //'uk'    => 'publish/gb-pc/ua/ad',
            //'vi'    => 'publish/gb-pc/vn/ad',
            //'he'    => 'publish/gb-pc/il/ad',
            //'kk'    => 'publish/gb-pc/kz/ad',
            //'th'    => 'publish/gb-pc/th/ad',
            //'id'    => 'publish/gb-pc/id/ad',
        ],
        'ad_s3StaticPath'       => [
            'en'    => 'statics/gb-pc/www',
            'ep'    => 'statics/gb-pc/ep',
            'fr'    => 'statics/gb-pc/fr',
            'ru'    => 'statics/gb-pc/ru',
            'po'    => 'statics/gb-pc/po',
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
                'en' => 'https://www' . GB_DOMAIN . '/promotion'
            ],
            'GBES'  => ['ep' => 'https://es' . GB_DOMAIN . '/promotion'],
            'GBFR'  => ['fr' => 'https://fr' . GB_DOMAIN . '/promotion'],
            'GBRU'  => ['ru' => 'https://ru' . GB_DOMAIN . '/promotion'],
            'GBPT'  => ['po' => 'https://pt' . GB_DOMAIN . '/promotion'],
            'GBIT'  => ['it' => 'https://it' . GB_DOMAIN . '/promotion'],
            'GBDE'  => ['de' => 'https://de' . GB_DOMAIN . '/promotion'],
            'GBUK'  => ['en' => 'https://uk' . GB_DOMAIN . '/promotion'],
            'GBUS'  => ['en' => 'https://us' . GB_DOMAIN . '/promotion'],
            'GBBR'  => ['pt-br' => 'https://br' . GB_DOMAIN . '/promotion'],
            'GBTR'  => ['tr' => 'https://tr' . GB_DOMAIN . '/promotion'],
            'GBMX'  => ['ep-mx' => 'https://mx' . GB_DOMAIN . '/promotion'],
            'GBMA'  => ['fr' => 'https://ma' . GB_DOMAIN . '/promotion'],
            'GBGR'  => ['el' => 'https://gr' . GB_DOMAIN . '/promotion'],
            'GBHU'  => ['hu' => 'https://hu' . GB_DOMAIN . '/promotion'],
            'GBNL'  => ['nl' => 'https://nl' . GB_DOMAIN . '/promotion'],
            'GBSK'  => ['sk' => 'https://sk' . GB_DOMAIN . '/promotion'],
            'GBRO'  => ['ro' => 'https://ro' . GB_DOMAIN . '/promotion'],
            'GBCZ'  => ['cs' => 'https://cz' . GB_DOMAIN . '/promotion'],
            'GBAU'  => ['en' => 'https://au' . GB_DOMAIN . '/promotion'],
            'GBIN'  => ['en' => 'https://in' . GB_DOMAIN . '/promotion'],
            'GBJP'  => ['ja' => 'https://jp' . GB_DOMAIN . '/promotion'],
            /*'GBUA' => ['uk' => 'https://ua' . GB_DOMAIN . '/promotion'],
            'GBIL' => ['he' => 'https://il' . GB_DOMAIN . '/promotion'],
            'GBKZ' => ['kk' => 'https://kz' . GB_DOMAIN . '/promotion'],
            'GBTH' => ['th' => 'https://th' . GB_DOMAIN . '/promotion'],
            'GBVN' => ['vi' => 'https://vn' . GB_DOMAIN . '/promotion'],
            'GBID' => ['id' => 'https://id' . GB_DOMAIN . '/promotion'],*/
            'GBPL'  => ['pl' => 'https://pl' . GB_DOMAIN . '/promotion'],
            'GBSTY' => ['en' => 'https://stylebest' . GB_DOMAIN . '/promotion'],
            'GBGAG' => ['en' => 'https://gagabop' . GB_DOMAIN . '/promotion'],
            'GBCOZ' => ['en' => 'https://cozyvoices' . GB_DOMAIN . '/promotion'],
        ],
        'ad_secondary_domain'   => [
            'en'    => 'https://www' . GB_DOMAIN . '/advertising',
            'ep-mx' => 'https://mx' . GB_DOMAIN . '/advertising',
            'pt_br' => 'https://br' . GB_DOMAIN . '/advertising',
            'tr'    => 'https://tr' . GB_DOMAIN . '/advertising',
            'it'    => 'https://it' . GB_DOMAIN . '/advertising',
            'de'    => 'https://de' . GB_DOMAIN . '/advertising',
            'ru'    => 'https://ru' . GB_DOMAIN . '/advertising',
            'po'    => 'https://pt' . GB_DOMAIN . '/advertising',
            'ep'    => 'https://es' . GB_DOMAIN . '/advertising',
            'fr'    => 'https://fr' . GB_DOMAIN . '/advertising',
            //'fr_ma' => 'https://ma' . GB_DOMAIN . '/advertising',
        ],
        'home_secondary_domain' => [
            'en' => 'https://m' . GB_DOMAIN . '/promotion/home',
        ]
    ],
    'gb-wap'     => [
        's3PublishPath'         => [
            'GB'    => [
                'en'    => 'publish/gb-wap/m.gearbest.net/en/activity',
                'ep'    => 'publish/gb-wap/m.gearbest.net/ep/activity',
                'fr'    => 'publish/gb-wap/m.gearbest.net/fr/activity',
                'ru'    => 'publish/gb-wap/m.gearbest.net/ru/activity',
                'de'    => 'publish/gb-wap/m.gearbest.net/de/activity',
                'tr'    => 'publish/gb-wap/m.gearbest.net/tr/activity',
                'pt-br' => 'publish/gb-wap/m.gearbest.net/pt-br/activity',
                'po'    => 'publish/gb-wap/m.gearbest.net/po/activity'
            ],
            'GBES'  => ['ep' => 'publish/gb-wap/m-es.gearbest.net/ep/activity'],
            'GBFR'  => ['fr' => 'publish/gb-wap/m-fr.gearbest.net/fr/activity'],
            'GBRU'  => ['ru' => 'publish/gb-wap/m-ru.gearbest.net/ru/activity'],
            'GBPT'  => ['po' => 'publish/gb-wap/m-pt.gearbest.net/en/activity'],
            'GBIT'  => ['it' => 'publish/gb-wap/m-it.gearbest.net/it/activity'],
            'GBDE'  => ['de' => 'publish/gb-wap/m-de.gearbest.net/de/activity'],
            'GBUK'  => ['en' => 'publish/gb-wap/m-uk.gearbest.net/en/activity'],
            'GBUS'  => ['en' => 'publish/gb-wap/m-us.gearbest.net/en/activity'],
            'GBBR'  => ['pt-br' => 'publish/gb-wap/m-br.gearbest.net/pt-br/activity'],
            'GBTR'  => ['tr' => 'publish/gb-wap/m-tr.gearbest.net/tr/activity'],
            'GBMX'  => ['ep-mx' => 'publish/gb-wap/m-mx.gearbest.net/ep-mx/activity'],
            'GBMA'  => ['fr' => 'publish/gb-wap/m-ma.gearbest.net/fr/activity'],
            'GBGR'  => ['el' => 'publish/gb-wap/m-gr.gearbest.net/el/activity'],
            'GBHU'  => ['hu' => 'publish/gb-wap/m-hu.gearbest.net/hu/activity'],
            'GBNL'  => ['nl' => 'publish/gb-wap/m-nl.gearbest.net/nl/activity'],
            'GBSK'  => ['sk' => 'publish/gb-wap/m-sk.gearbest.net/sk/activity'],
            'GBRO'  => ['ro' => 'publish/gb-wap/m-ro.gearbest.net/ro/activity'],
            'GBCZ'  => ['cs' => 'publish/gb-wap/m-cz.gearbest.net/cs/activity'],
            'GBAU'  => ['en' => 'publish/gb-wap/m-au.gearbest.net/en/activity'],
            'GBIN'  => ['en' => 'publish/gb-wap/m-in.gearbest.net/en/activity'],
            'GBJP'  => ['ja' => 'publish/gb-wap/m-jp.gearbest.net/ja/activity'],
            /*'GBUA' => ['uk' => 'publish/gb-wap/m-ua.gearbest.net/uk/activity'],
            'GBIL' => ['he' => 'publish/gb-wap/m-il.gearbest.net/he/activity'],
            'GBKZ' => ['kk' => 'publish/gb-wap/m-kz.gearbest.net/kk/activity'],
            'GBTH' => ['th' => 'publish/gb-wap/m-th.gearbest.net/th/activity'],
            'GBVN' => ['vi' => 'publish/gb-wap/m-vn.gearbest.net/vi/activity'],
            'GBID' => ['id' => 'publish/gb-wap/m-id.gearbest.net/id/activity'],*/
            'GBPL'  => ['pl' => 'publish/gb-wap/m-pl.gearbest.net/pl/activity'],
            'GBSTY' => ['en' => 'publish/gb-wap/m-stylebest.gearbest.net/en/activity'],
            'GBGAG' => ['en' => 'publish/gb-wap/m-gagabop.gearbest.net/en/activity'],
            'GBCOZ' => ['en' => 'publish/gb-wap/m-cozyvoices.gearbest.net/en/activity'],
        ],
        'ad_s3PublishPath'      => [
            'en'    => 'publish/gb-wap/m/ad',
            'ep'    => 'publish/gb-wap/m-ep/ad',
            'fr'    => 'publish/gb-wap/m-fr/ad',
            'ru'    => 'publish/gb-wap/m-ru/ad',
            'po'    => 'publish/gb-wap/m-po/ad',
            'it'    => 'publish/gb-wap/m-it/ad',
            'de'    => 'publish/gb-wap/m-de/ad',
            'en_gb' => 'publish/gb-wap/m-ak/ad',
            'en_us' => 'publish/gb-wap/m-us/ad',
            'en_au' => 'publish/gb-wap/m-au/ad',
            'pt_br' => 'publish/gb-wap/m-br/ad',
            'tr'    => 'publish/gb-wap/m-tr/ad',
            'pl'    => 'publish/gb-wap/m-pl/ad',
            'en_in' => 'publish/gb-wap/m-in/ad',
            'el'    => 'publish/gb-wap/m-el/ad',
            'ep-mx' => 'publish/gb-wap/m-mx/ad',
            'hu'    => 'publish/gb-wap/m-hu/ad',
            'sk'    => 'publish/gb-wap/m-sk/ad',
            'cs'    => 'publish/gb-wap/m-cs/ad',
            'nl'    => 'publish/gb-wap/m-nl/ad',
            'ro'    => 'publish/gb-wap/m-ro/ad',
            'fr_ma' => 'publish/gb-wap/m-ma/ad',
            //'ja'    => 'publish/gb-wap/m-jp/ad',
            //'uk'    => 'publish/gb-wap/m-ua/ad',
            //'he'    => 'publish/gb-wap/m-il/ad',
            //'kk'    => 'publish/gb-wap/m-kz/ad',
            //'th'    => 'publish/gb-wap/m-th/ad',
            //'id'    => 'publish/gb-wap/m-id/ad',
        ],
        'ad_s3StaticPath'       => [
            'en'    => 'statics/gb-wap/m',
            'ep'    => 'statics/gb-wap/ep',
            'fr'    => 'statics/gb-wap/fr',
            'ru'    => 'statics/gb-wap/ru',
            'po'    => 'statics/gb-wap/po',
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
                'pt-br' => 'statics/gb-wap/GB-pt-br',
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
                'en'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ep'    => 'https://m' . GB_DOMAIN . '/promotion',
                'fr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ru'    => 'https://m' . GB_DOMAIN . '/promotion',
                'de'    => 'https://m' . GB_DOMAIN . '/promotion',
                'tr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'po'    => 'https://m' . GB_DOMAIN . '/promotion',
                'pt-br' => 'https://m' . GB_DOMAIN . '/promotion',
            ],
            'GBES'  => ['ep' => 'https://m-es' . GB_DOMAIN . '/promotion'],
            'GBFR'  => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion'],
            'GBRU'  => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion'],
            'GBPT'  => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion'],
            'GBIT'  => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion'],
            'GBDE'  => ['de' => 'https://m-de' . GB_DOMAIN . '/promotion'],
            'GBUK'  => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion'],
            'GBUS'  => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion'],
            'GBBR'  => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion'],
            'GBTR'  => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion'],
            'GBMX'  => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion'],
            'GBMA'  => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion'],
            'GBGR'  => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion'],
            'GBHU'  => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion'],
            'GBNL'  => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion'],
            'GBSK'  => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion'],
            'GBRO'  => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion'],
            'GBCZ'  => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion'],
            'GBAU'  => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion'],
            'GBIN'  => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion'],
            'GBJP'  => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion'],
            /*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion'],
            'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion'],
            'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion'],
            'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion'],
            'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion'],
            'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion'],*/
            'GBPL'  => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion'],
            'GBSTY' => ['en' => 'https://m-stylebest' . GB_DOMAIN . '/promotion'],
            'GBGAG' => ['en' => 'https://m-gagabop' . GB_DOMAIN . '/promotion'],
            'GBCOZ' => ['en' => 'https://m-cozyvoices' . GB_DOMAIN . '/promotion'],
        ],
        'ad_secondary_domain'   => [
            'en'    => 'https://m' . GB_DOMAIN . '/advertising',
            'ep-mx' => 'https://m-mx' . GB_DOMAIN . '/advertising',
            'pt_br' => 'https://m-br' . GB_DOMAIN . '/advertising',
            'tr'    => 'https://m-tr' . GB_DOMAIN . '/advertising',
            'it'    => 'https://m-it' . GB_DOMAIN . '/advertising',
            'de'    => 'https://m-de' . GB_DOMAIN . '/advertising',
            'ru'    => 'https://m-ru' . GB_DOMAIN . '/advertising',
            'po'    => 'https://m-pt' . GB_DOMAIN . '/advertising',
            'fr'    => 'https://m-fr' . GB_DOMAIN . '/advertising',
            'ep'    => 'https://m-es' . GB_DOMAIN . '/advertising',
            //'fr_ma' => 'https://m-ma' . GB_DOMAIN . '/advertising',
        ],
        'home_secondary_domain' => [
            'en' => 'https://m' . GB_DOMAIN . '/promotion/home',
        ]
    ],
    'gb-ios'     => [
        's3PublishPath'         => [
            'GB'    => [
                'en'    => 'publish/gb-ios/m.gearbest.net/en/activity',
                'ep'    => 'publish/gb-ios/m.gearbest.net/ep/activity',
                'fr'    => 'publish/gb-ios/m.gearbest.net/fr/activity',
                'ru'    => 'publish/gb-ios/m.gearbest.net/ru/activity',
                'po'    => 'publish/gb-ios/m.gearbest.net/po/activity',
                'it'    => 'publish/gb-ios/m.gearbest.net/it/activity',
                'de'    => 'publish/gb-ios/m.gearbest.net/de/activity',
                'tr'    => 'publish/gb-ios/m.gearbest.net/tr/activity',
                'pt-br' => 'publish/gb-ios/m.gearbest.net/pt-br/activity'
            ],
            'GBRU'  => ['ru' => 'publish/gb-ios/m-ru.gearbest.net/ru/activity'],
            'GBFR'  => ['fr' => 'publish/gb-ios/m-fr.gearbest.net/fr/activity'],
            'GBPT'  => ['po' => 'publish/gb-ios/m-pt.gearbest.net/po/activity'],
            'GBIT'  => ['it' => 'publish/gb-ios/m-it.gearbest.net/it/activity'],
            'GBUK'  => ['en' => 'publish/gb-ios/m-uk.gearbest.net/en/activity'],
            'GBUS'  => ['en' => 'publish/gb-ios/m-us.gearbest.net/en/activity'],
            'GBBR'  => ['pt-br' => 'publish/gb-ios/m-br.gearbest.net/pt-br/activity'],
            'GBTR'  => ['tr' => 'publish/gb-ios/m-tr.gearbest.net/tr/activity'],
            'GBMX'  => ['ep-mx' => 'publish/gb-ios/m-mx.gearbest.net/ep-mx/activity'],
            'GBMA'  => ['fr' => 'publish/gb-ios/m-ma.gearbest.net/fr/activity'],
            'GBGR'  => ['el' => 'publish/gb-ios/m-gr.gearbest.net/el/activity'],
            'GBHU'  => ['hu' => 'publish/gb-ios/m-hu.gearbest.net/hu/activity'],
            'GBNL'  => ['nl' => 'publish/gb-ios/m-nl.gearbest.net/nl/activity'],
            'GBSK'  => ['sk' => 'publish/gb-ios/m-sk.gearbest.net/sk/activity'],
            'GBRO'  => ['ro' => 'publish/gb-ios/m-ro.gearbest.net/ro/activity'],
            'GBCZ'  => ['cs' => 'publish/gb-ios/m-cz.gearbest.net/cs/activity'],
            'GBAU'  => ['en' => 'publish/gb-ios/m-au.gearbest.net/en/activity'],
            'GBIN'  => ['en' => 'publish/gb-ios/m-in.gearbest.net/en/activity'],
            'GBJP'  => ['ja' => 'publish/gb-ios/m-jp.gearbest.net/ja/activity'],
            /*'GBUA' => ['uk' => 'publish/gb-ios/m-ua.gearbest.net/uk/activity'],
            'GBIL' => ['he' => 'publish/gb-ios/m-il.gearbest.net/he/activity'],
            'GBKZ' => ['kk' => 'publish/gb-ios/m-kz.gearbest.net/kk/activity'],
            'GBTH' => ['th' => 'publish/gb-ios/m-th.gearbest.net/th/activity'],
            'GBVN' => ['vi' => 'publish/gb-ios/m-vn.gearbest.net/vi/activity'],
            'GBID' => ['id' => 'publish/gb-ios/m-id.gearbest.net/id/activity'],*/
            'GBPL'  => ['pl' => 'publish/gb-ios/m-pl.gearbest.net/pl/activity'],
        ],
        's3StaticPath'          => [
            'GB'    => [
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
            'GBRU'  => ['ru' => 'statics/gb-ios/GBRU-ru'],
            'GBFR'  => ['fr' => 'statics/gb-ios/GBFR-fr'],
            'GBPT'  => ['po' => 'statics/gb-ios/GBPT-po'],
            'GBIT'  => ['it' => 'statics/gb-ios/GBIT-it'],
            'GBUK'  => ['en' => 'statics/gb-ios/GBUK-en'],
            'GBUS'  => ['en' => 'statics/gb-ios/GBUS-en'],
            'GBBR'  => ['pt-br' => 'statics/gb-ios/GBBR-pt-br'],
            'GBTR'  => ['tr' => 'statics/gb-ios/GBTR-tr'],
            'GBMX'  => ['ep-mx' => 'statics/gb-ios/GBMX-ep-mx'],
            'GBMA'  => ['fr' => 'statics/gb-ios/GBMA-fr'],
            'GBGR'  => ['el' => 'statics/gb-ios/GBGR-el'],
            'GBHU'  => ['hu' => 'statics/gb-ios/GBHU-hu'],
            'GBNL'  => ['nl' => 'statics/gb-ios/GBNL-nl'],
            'GBSK'  => ['sk' => 'statics/gb-ios/GBSK-sk'],
            'GBRO'  => ['ro' => 'statics/gb-ios/GBRO-ro'],
            'GBCZ'  => ['cs' => 'statics/gb-ios/GBCZ-cs'],
            'GBAU'  => ['en' => 'statics/gb-ios/GBAU-en'],
            'GBIN'  => ['en' => 'statics/gb-ios/GBIN-en'],
            'GBJP'  => ['ja' => 'statics/gb-ios/GBJP-ja'],
            /*'GBUA' => ['uk' => 'statics/gb-ios/GBUA-uk'],
            'GBIL' => ['he' => 'statics/gb-ios/GBIL-he'],
            'GBKZ' => ['kk' => 'statics/gb-ios/GBKZ-kk'],
            'GBTH' => ['th' => 'statics/gb-ios/GBTH-th'],
            'GBVN' => ['vi' => 'statics/gb-ios/GBVN-vi'],
            'GBID' => ['id' => 'statics/gb-ios/GBID-id'],*/
            'GBPL'  => ['pl' => 'statics/gb-ios/GBPL-pl'],
        ],
        'secondary_domain'      => [
            'GB'    => [
                'en'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ep'    => 'https://m' . GB_DOMAIN . '/promotion',
                'fr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ru'    => 'https://m' . GB_DOMAIN . '/promotio',
                'po'    => 'https://m' . GB_DOMAIN . '/promotion',
                'it'    => 'https://m' . GB_DOMAIN . '/promotion',
                'de'    => 'https://m' . GB_DOMAIN . '/promotion',
                'tr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'pt-br' => 'https://m' . GB_DOMAIN . '/promotion'
            ],
            'GBRU'  => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion'],
            'GBFR'  => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion'],
            'GBPT'  => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion'],
            'GBIT'  => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion'],
            'GBUK'  => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion'],
            'GBUS'  => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion'],
            'GBBR'  => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion'],
            'GBTR'  => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion'],
            'GBMX'  => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion'],
            'GBMA'  => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion'],
            'GBGR'  => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion'],
            'GBHU'  => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion'],
            'GBNL'  => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion'],
            'GBSK'  => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion'],
            'GBRO'  => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion'],
            'GBCZ'  => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion'],
            'GBAU'  => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion'],
            'GBIN'  => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion'],
            'GBJP'  => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion'],
            /*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion'],
            'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion'],
            'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion'],
            'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion'],
            'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion'],
            'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion'],*/
            'GBPL'  => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion'],
        ],
        'home_secondary_domain' => [
            'en' => 'https://m' . GB_DOMAIN . '/promotion/home',
        ]
    ],
    'gb-android' => [
        's3PublishPath'         => [
            'GB'    => [
                'en'    => 'publish/gb-android/m.gearbest.net/en/activity',
                'ep'    => 'publish/gb-android/m.gearbest.net/ep/activity',
                'fr'    => 'publish/gb-android/m.gearbest.net/fr/activity',
                'ru'    => 'publish/gb-android/m.gearbest.net/ru/activity',
                'po'    => 'publish/gb-android/m.gearbest.net/po/activity',
                'it'    => 'publish/gb-android/m.gearbest.net/it/activity',
                'de'    => 'publish/gb-android/m.gearbest.net/de/activity',
                'tr'    => 'publish/gb-android/m.gearbest.net/tr/activity',
                'pt-br' => 'publish/gb-android/m.gearbest.net/pt-br/activity'
            ],
            'GBRU'  => ['ru' => 'publish/gb-android/m-ru.gearbest.net/ru/activity'],
            'GBFR'  => ['fr' => 'publish/gb-android/m-fr.gearbest.net/fr/activity'],
            'GBPT'  => ['po' => 'publish/gb-android/m-pt.gearbest.net/po/activity'],
            'GBIT'  => ['it' => 'publish/gb-android/m-it.gearbest.net/it/activity'],
            'GBUK'  => ['en' => 'publish/gb-android/m-uk.gearbest.net/en/activity'],
            'GBUS'  => ['en' => 'publish/gb-android/m-us.gearbest.net/en/activity'],
            'GBBR'  => ['pt-br' => 'publish/gb-android/m-br.gearbest.net/pt-br/activity'],
            'GBTR'  => ['tr' => 'publish/gb-android/m-tr.gearbest.net/tr/activity'],
            'GBMX'  => ['ep-mx' => 'publish/gb-android/m-mx.gearbest.net/ep-mx/activity'],
            'GBMA'  => ['fr' => 'publish/gb-android/m-ma.gearbest.net/fr/activity'],
            'GBGR'  => ['el' => 'publish/gb-android/m-gr.gearbest.net/el/activity'],
            'GBHU'  => ['hu' => 'publish/gb-android/m-hu.gearbest.net/hu/activity'],
            'GBNL'  => ['nl' => 'publish/gb-android/m-nl.gearbest.net/nl/activity'],
            'GBSK'  => ['sk' => 'publish/gb-android/m-sk.gearbest.net/sk/activity'],
            'GBRO'  => ['ro' => 'publish/gb-android/m-ro.gearbest.net/ro/activity'],
            'GBCZ'  => ['cs' => 'publish/gb-android/m-cz.gearbest.net/cs/activity'],
            'GBAU'  => ['en' => 'publish/gb-android/m-au.gearbest.net/en/activity'],
            'GBIN'  => ['en' => 'publish/gb-android/m-in.gearbest.net/en/activity'],
            'GBJP'  => ['ja' => 'publish/gb-android/m-jp.gearbest.net/ja/activity'],
            /*'GBUA' => ['uk' => 'publish/gb-android/m-ua.gearbest.net/uk/activity'],
            'GBIL' => ['he' => 'publish/gb-android/m-il.gearbest.net/he/activity'],
            'GBKZ' => ['kk' => 'publish/gb-android/m-kz.gearbest.net/kk/activity'],
            'GBTH' => ['th' => 'publish/gb-android/m-th.gearbest.net/th/activity'],
            'GBVN' => ['vi' => 'publish/gb-android/m-vn.gearbest.net/vi/activity'],
            'GBID' => ['id' => 'publish/gb-android/m-id.gearbest.net/id/activity'],*/
            'GBPL'  => ['pl' => 'publish/gb-android/m-pl.gearbest.net/pl/activity'],
        ],
        's3StaticPath'          => [
            'GB'    => [
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
            'GBRU'  => ['ru' => 'statics/gb-android/GBRU-ru'],
            'GBFR'  => ['fr' => 'statics/gb-android/GBFR-fr'],
            'GBPT'  => ['po' => 'statics/gb-android/GBPT-po'],
            'GBIT'  => ['it' => 'statics/gb-android/GBIT-it'],
            'GBUK'  => ['en' => 'statics/gb-android/GBUK-en'],
            'GBUS'  => ['en' => 'statics/gb-android/GBUS-en'],
            'GBBR'  => ['pt-br' => 'statics/gb-android/GBBR-pt-br'],
            'GBTR'  => ['tr' => 'statics/gb-android/GBTR-tr'],
            'GBMX'  => ['ep-mx' => 'statics/gb-android/GBMX-ep-mx'],
            'GBMA'  => ['fr' => 'statics/gb-android/GBMA-fr'],
            'GBGR'  => ['el' => 'statics/gb-android/GBGR-el'],
            'GBHU'  => ['hu' => 'statics/gb-android/GBHU-hu'],
            'GBNL'  => ['nl' => 'statics/gb-android/GBNL-nl'],
            'GBSK'  => ['sk' => 'statics/gb-android/GBSK-sk'],
            'GBRO'  => ['ro' => 'statics/gb-android/GBRO-ro'],
            'GBCZ'  => ['cs' => 'statics/gb-android/GBCZ-cs'],
            'GBAU'  => ['en' => 'statics/gb-android/GBAU-en'],
            'GBIN'  => ['en' => 'statics/gb-android/GBIN-en'],
            'GBJP'  => ['ja' => 'statics/gb-android/GBJP-ja'],
            /*'GBUA' => ['uk' => 'statics/gb-android/GBUA-uk'],
            'GBIL' => ['he' => 'statics/gb-android/GBIL-he'],
            'GBKZ' => ['kk' => 'statics/gb-android/GBKZ-kk'],
            'GBTH' => ['th' => 'statics/gb-android/GBTH-th'],
            'GBVN' => ['vi' => 'statics/gb-android/GBVN-vi'],
            'GBID' => ['id' => 'statics/gb-android/GBID-id'],*/
            'GBPL'  => ['pl' => 'statics/gb-android/GBPL-pl'],
        ],
        'secondary_domain'      => [
            'GB'    => [
                'en'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ep'    => 'https://m' . GB_DOMAIN . '/promotion',
                'fr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'ru'    => 'https://m' . GB_DOMAIN . '/promotion',
                'po'    => 'https://m' . GB_DOMAIN . '/promotion',
                'it'    => 'https://m' . GB_DOMAIN . '/promotion',
                'de'    => 'https://m' . GB_DOMAIN . '/promotion',
                'tr'    => 'https://m' . GB_DOMAIN . '/promotion',
                'pt-br' => 'https://m' . GB_DOMAIN . '/promotion'
            ],
            'GBRU'  => ['ru' => 'https://m-ru' . GB_DOMAIN . '/promotion'],
            'GBFR'  => ['fr' => 'https://m-fr' . GB_DOMAIN . '/promotion'],
            'GBPT'  => ['po' => 'https://m-pt' . GB_DOMAIN . '/promotion'],
            'GBIT'  => ['it' => 'https://m-it' . GB_DOMAIN . '/promotion'],
            'GBUK'  => ['en' => 'https://m-uk' . GB_DOMAIN . '/promotion'],
            'GBUS'  => ['en' => 'https://m-us' . GB_DOMAIN . '/promotion'],
            'GBBR'  => ['pt-br' => 'https://m-br' . GB_DOMAIN . '/promotion'],
            'GBTR'  => ['tr' => 'https://m-tr' . GB_DOMAIN . '/promotion'],
            'GBMX'  => ['ep-mx' => 'https://m-mx' . GB_DOMAIN . '/promotion'],
            'GBMA'  => ['fr' => 'https://m-ma' . GB_DOMAIN . '/promotion'],
            'GBGR'  => ['el' => 'https://m-gr' . GB_DOMAIN . '/promotion'],
            'GBHU'  => ['hu' => 'https://m-hu' . GB_DOMAIN . '/promotion'],
            'GBNL'  => ['nl' => 'https://m-nl' . GB_DOMAIN . '/promotion'],
            'GBSK'  => ['sk' => 'https://m-sk' . GB_DOMAIN . '/promotion'],
            'GBRO'  => ['ro' => 'https://m-ro' . GB_DOMAIN . '/promotion'],
            'GBCZ'  => ['cs' => 'https://m-cz' . GB_DOMAIN . '/promotion'],
            'GBAU'  => ['en' => 'https://m-au' . GB_DOMAIN . '/promotion'],
            'GBIN'  => ['en' => 'https://m-in' . GB_DOMAIN . '/promotion'],
            'GBJP'  => ['ja' => 'https://m-jp' . GB_DOMAIN . '/promotion'],
            /*'GBUA' => ['uk' => 'https://m-ua' . GB_DOMAIN . '/promotion'],
            'GBIL' => ['he' => 'https://m-il' . GB_DOMAIN . '/promotion'],
            'GBKZ' => ['kk' => 'https://m-kz' . GB_DOMAIN . '/promotion'],
            'GBTH' => ['th' => 'https://m-th' . GB_DOMAIN . '/promotion'],
            'GBVN' => ['vi' => 'https://m-vn' . GB_DOMAIN . '/promotion'],
            'GBID' => ['id' => 'https://m-id' . GB_DOMAIN . '/promotion'],*/
            'GBPL'  => ['pl' => 'https://m-pl' . GB_DOMAIN . '/promotion'],
        ],
        'home_secondary_domain' => [
            'en' => 'https://m' . GB_DOMAIN . '/promotion/home',
        ]
    ],

    /**************************************** DL *******************************************/
    'dl-web'     => [
        's3PublishPath'         => [
            'DL' => [
                'en' => 'publish/www.dresslily.com/en',
                ],
            'DLFR' => [
                'fr' => 'publish/fr.dresslily.com/fr',
                ]
        ],
        's3HomePublishPath'     => [
            'DL' => [
                'en' => 'publish/www.dresslily.com/en/home',
                ],
            'DLFR' => [
                'fr' => 'publish/fr.dresslily.com/fr/home',
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
                'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/promotion',
                ],
            'DLFR' => [
                'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/promotion',
                ]
        ],
        'home_secondary_domain' => [
            'DL' => [
                'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/promotion/home',
                ],
            'DLFR' => [
                'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/promotion/home',
                ]
        ]
    ],
    'dl-app'     => [
        's3PublishPath'    => [
            'DL' => [
                'en' => 'publish/www.dresslily.com/en/app',
                ],
            'DLFR' => [
                'fr' => 'publish/fr.dresslily.com/fr/app',
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
                'en' => 'http://www.pc-dresslily' . DL_DOMAIN . '/promotion/app',
                ],
            'DLFR' => [
                'fr' => 'http://fr.pc-dresslily' . DL_DOMAIN . '/promotion/app',
                ]
        ]
    ],

];
