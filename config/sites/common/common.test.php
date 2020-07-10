<?php
/**
 * 站点测试环境公用配置
 */
return [
    /****************************************测试站点配置START*****************************************/
    'test-pc'  => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
        'isTest'                  => 1,//是否测试站点
        'headFooterSiteCode'      => 'rw-pc',//测试站点头尾采用其他站点的siteCode
    ],
    'test-wap' => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
        'isTest'                  => 1,//是否测试站点
        'headFooterSiteCode'      => 'rw-wap',//测试站点头尾采用其他站点的siteCode
    ],
    'test-app' => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
        'isTest'                  => 1,//是否测试站点
        'headFooterSiteCode'      => 'rw-app',//测试站点头尾采用其他站点的siteCode
    ],
    /****************************************测试站点配置END*******************************************/
    'rw-pc'    => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'rosewholesale',
										'app_id'			  => '367615620051681',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosewholesale__',//tw账号
										'type'                => 'promotion',
									],
    ],
    'rw-wap'   => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'rosewholesale',
										'app_id'			  => '367615620051681',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosewholesale__',//tw账号
										'type'                => 'promotion',
									],
    ],
    'rw-app'   => [
        'status'                  => 0,
        'initialWidth'            => 250,
        'initialHeight'           => 250,
        'lazyWidth'               => 250,//前端懒加载时默认图片宽
        'lazyHeight'              => 250,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rw-pc/images/goods_default.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'rosewholesale',
										'app_id'			  => '367615620051681',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosewholesale__',//tw账号
										'type'                => 'promotion',
									],
    ],
    'suk-pc'   => [
        'status'                  => 1,
        'initialWidth'            => 362,
        'initialHeight'           => 480,
        'lazyWidth'               => 260,//前端懒加载时默认图片宽
        'lazyHeight'              => 346,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rg-pc/images/goods_default.png',//前端懒加载时默认图片地址
        'share'				 	  =>[//分享信息
            'site_name'           => 'RoseGal',
            'app_id'			  => '100924140245002',//fb账号ID
            'admins'			  => '100004662303870',
            'creator'			  => '@Rosegaloutfit',//tw账号
            'type'                => 'promotion',
        ],
    ],
    'suk-wap'   => [
        'status'                  => 1,
        'initialWidth'            => 362,
        'initialHeight'           => 480,
        'lazyWidth'               => 260,//前端懒加载时默认图片宽
        'lazyHeight'              => 346,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rg-pc/images/goods_default.png',//前端懒加载时默认图片地址
        'share'				 	  =>[//分享信息
            'site_name'           => 'RoseGal',
            'app_id'			  => '100924140245002',//fb账号ID
            'admins'			  => '100004662303870',
            'creator'			  => '@Rosegaloutfit',//tw账号
            'type'                => 'promotion',
        ],
    ],
    'rg-pc'    => [
        'status'                  => 1,
        'initialWidth'            => 362,
        'initialHeight'           => 480,
        'lazyWidth'               => 260,//前端懒加载时默认图片宽
        'lazyHeight'              => 346,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rg-pc/images/goods_default.png',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'RoseGal',
										'app_id'			  => '100924140245002',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosegaloutfit',//tw账号
										'type'                => 'promotion',
									],
    ],
    'rg-wap'   => [
        'status'                  => 1,
        'initialWidth'            => 362,
        'initialHeight'           => 480,
        'lazyWidth'               => 260,//前端懒加载时默认图片宽
        'lazyHeight'              => 346,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rg-pc/images/goods_default.png',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'RoseGal',
										'app_id'			  => '100924140245002',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosegaloutfit',//tw账号
										'type'                => 'promotion',
									],
    ],
    'rg-app'   => [
        'status'                  => 1,
        'initialWidth'            => 362,
        'initialHeight'           => 480,
        'lazyWidth'               => 260,//前端懒加载时默认图片宽
        'lazyHeight'              => 346,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/rg-pc/images/goods_default.png',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'RoseGal',
										'app_id'			  => '100924140245002',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@Rosegaloutfit',//tw账号
										'type'                => 'promotion',
									],
    ],
    'zf-pc'    => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 285,//前端懒加载时默认图片宽
        'lazyHeight'              => 379,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'ZAFUL',
										'app_id'			  => '1396335280417835',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@affiliate_zaful',//tw账号
										'type'                => 'promotion',
									],
    ],
    'zf-wap'   => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 168,//前端懒加载时默认图片宽
        'lazyHeight'              => 223,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'ZAFUL',
										'app_id'			  => '1396335280417835',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@affiliate_zaful',//tw账号
										'type'                => 'promotion',
									],
    ],
    'zf-app'   => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 285,//前端懒加载时默认图片宽
        'lazyHeight'              => 379,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'ZAFUL',
										'app_id'			  => '1396335280417835',//fb账号ID
										'admins'			  => '100004662303870',
										'creator'			  => '@affiliate_zaful',//tw账号
										'type'                => 'promotion',
									],
    ],
    'gb-pc' => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 285,//前端懒加载时默认图片宽
        'lazyHeight'              => 379,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'GearBest',
										'app_id'			  => '900125666754558',//fb账号ID
										'creator'			  => '3308610866',//tw账号
										'type'                => 'promotion',
									],
    ],
    'gb-wap' => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 168,//前端懒加载时默认图片宽
        'lazyHeight'              => 223,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'GearBest',
										'app_id'			  => '900125666754558',//fb账号ID
										'creator'			  => '3308610866',//tw账号
										'type'                => 'promotion',
									],
    ],
    'gb-ios' => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 168,//前端懒加载时默认图片宽
        'lazyHeight'              => 223,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'GearBest',
										'app_id'			  => '900125666754558',//fb账号ID
										'creator'			  => '3308610866',//tw账号
										'type'                => 'promotion',
									],
    ],
    'gb-android' => [
        'status'                  => 1,
        'initialWidth'            => 400,
        'initialHeight'           => 532,
        'lazyWidth'               => 168,//前端懒加载时默认图片宽
        'lazyHeight'              => 223,//前端懒加载时默认图片高
        'lazyImg'                 => '/resources/sites/loadingbg.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
										'site_name'           => 'GearBest',
										'app_id'			  => '900125666754558',//fb账号ID
										'creator'			  => '3308610866',//tw账号
										'type'                => 'promotion',
									],
	],

	/**************************************** DL *******************************************/
	'dl-web'    => [
		'status'                  => 1,
		'initialWidth'            => 362,
		'initialHeight'           => 480,
		'lazyWidth'               => 260,//前端懒加载时默认图片宽
		'lazyHeight'              => 346,//前端懒加载时默认图片高
		'lazyImg'                 => '/resources/sites/dl/images/loading.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
			'site_name'           => 'DressLily',
			'app_id'			  => '796365870462440',//fb账号ID
			'admins'			  => '100004662303870',
			'creator'			  => '@DressLilyOutfit',//tw账号
			'type'                => 'promotion',
		],
	],
	'dl-app'   => [
		'status'                  => 1,
		'initialWidth'            => 362,
		'initialHeight'           => 480,
		'lazyWidth'               => 260,//前端懒加载时默认图片宽
		'lazyHeight'              => 346,//前端懒加载时默认图片高
		'lazyImg'                 => '/resources/sites/dl/images/loading.gif',//前端懒加载时默认图片地址
		'share'				 	  =>[//分享信息
			'site_name'           => 'DressLily',
			'app_id'			  => '796365870462440',//fb账号ID
			'admins'			  => '100004662303870',
			'creator'			  => '@DressLilyOutfit',//tw账号
			'type'                => 'promotion',
		],
	],
];
