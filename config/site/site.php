<?php
/**
 * 站点公共配置
 */


/**-------------- 环境公共配置 --------------------------*/
$siteCommonConfig = [
    'test' => [
        'support_responsive' => false, // 不支持响应式(页面自适应PC、平板、手机)
    ],

    /**************************************** DL *******************************************/
    'dl'   => [
        'support_responsive' => true, // 支持响应式(页面自适应PC、平板、手机)
	    'pipeline'              => [
		    'DL'   => '全球站',
		    'DLFR' => '法国站',
	    ],
	    'pipeline_default_lang' => [
		    'DL'   => 'en',
		    'DLFR' => 'fr',
	    ],
	    'platforms' => ['web','app']
    ],

    /**************************************** RG *******************************************/
    'rg'   => [
        'support_responsive' => false, // 不支持响应式(页面自适应PC、平板、手机)
        'pipeline'              => [
            'RG'   => '全球站',
            'RGFR' => '法国站',
            'RGES' => '西班牙站',
            'RGRU' => '俄罗斯站'
        ],
        'pipeline_default_lang' => [
            'RG'   => 'en',
            'RGFR' => 'fr',
            'RGES' => 'es',
            'RGRU' => 'ru'
        ],
	    'platforms' => ['pc','wap','app']
    ],
	/**************************************** SUK *******************************************/
	'suk'   => [
		'support_responsive' => false, // 不支持响应式(页面自适应PC、平板、手机)
		'pipeline'              => [
			'SUK'   => '全球站',
			'SUKJP' => '日本站',
		],
		'pipeline_default_lang' => [
			'SUK'   => 'en',
			'SUKJP' => 'ja',
		],
		'platforms' => ['pc','wap']
	],

    /**************************************** RW *******************************************/
    'rw'   => [
        'support_responsive' => false, // 不支持响应式(页面自适应PC、平板、手机),
	    'platforms' => ['pc','wap','app']
    ],

    /**************************************** ZF *******************************************/
    'zf'   => [
        'support_responsive'    => false, // 不支持响应式(页面自适应PC、平板、手机)
        'pipeline'              => [
            'ZF'   => '全球站',
            'ZFES' => '西班牙站',
            'ZFFR' => '法国站',
            'ZFPT' => '葡萄牙站',
            'ZFIT' => '意大利站',
            'ZFDE' => '德国站',
            'ZFIE' => '欧洲站',
			'ZFAU' => '澳大利亚站',
			'ZFNZ' => '新西兰站',
			'ZFGB' => '英国站',
			'ZFCA' => '加拿大站',
			'ZFBE' => '比利时站',
			'ZFCH' => '瑞士站',
			'ZFPH' => '菲律宾站',
			'ZFIN' => '印度站',
			'ZFSG' => '新加坡站',
			'ZFMY' => '马来西亚站',
			'ZFAT' => '奥地利站',
			'ZFMX' => '拉美站',
			'ZFZA' => '南非站',
			'ZFBR' => '巴西站',
			'ZFTH' => '泰国站',
			'ZFID' => '印度尼西亚站',
			'ZFTW' => '台湾站',
			'ZFAR' => '沙特站',
			'ZFIL' => '以色列站',
			'ZFRU' => '俄罗斯站',
			'ZFTR' => '土耳其站',
            'ZFVN' => '越南站',
            'ZFJP' => '日本站',
            'ZFHK' => '香港站',
            'ZFMX01' => '墨西哥站',
            'ZFRO' => '罗马尼亚站'
        ],
        /* 渠道域名代理的默认语言，和站点域名代理规则相对应，不可随意更改（对于非默认语言的访问链接会加上lang=xxx的参数） */
        'pipeline_default_lang' => [
            'ZF'   => 'en',
            'ZFES' => 'es',
            'ZFFR' => 'fr',
            'ZFPT' => 'pt',
            'ZFIT' => 'it',
            'ZFDE' => 'de',
            'ZFIE' => 'en', // 爱尔兰的默认语言是en
            'ZFAU' => 'en',
            'ZFNZ' => 'en',
            'ZFGB' => 'en',
            'ZFCA' => 'en',
            'ZFBE' => 'fr',
            'ZFCH' => 'de',
            'ZFPH' => 'en',
            'ZFIN' => 'en',
            'ZFSG' => 'en',
            'ZFMY' => 'en',
            'ZFAT' => 'de',
            'ZFMX' => 'es',
            'ZFZA' => 'en',
            'ZFBR' => 'pt',
            'ZFTH' => 'th',
            'ZFID' => 'id',
            'ZFTW' => 'zh-tw',
            'ZFAR' => 'ar',
            'ZFIL' => 'en',
			'ZFRU' => 'ru',
			'ZFTR' => 'tr',
            'ZFVN' => 'vi',
            'ZFJP' => 'ja',
            'ZFHK' => 'zh-tw',
            'ZFMX01' => 'es',
            'ZFRO' => 'ro'
        ],
	    'platforms' => ['pc','wap','app'],
	    //支持多语言渠道的国家站   用于清除CDN缓存时加语言参数的多个链接一起清除
        'manyLanguage' => [ 'site_code' => ['zf-pc', 'zf-wap'],
	                        'pipeline' => ['ZFCH','ZFIL'],
                          ]
    ]
];

return yii\helpers\ArrayHelper::merge($siteCommonConfig, require(__DIR__ . '/site.' . YII_ENV . '.php'));
