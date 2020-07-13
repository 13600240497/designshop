<?php
/**
 * 测试环境配置
 */
return [
	'components' => [
		
	],
    'params'     => [
        'url'                => [
            'admin'  => 'http://test.' . DOMAIN,
            'assets' => 'http://test.' . DOMAIN
        ],
		'wkPath' => 'wkhtmltoimage',//网页快照扩展,
        'appFullDomain' => 'test.geshop-api.com.release_sop.php7.egomsl.com',
		'appDeveloper'  => 'test',
    ],
];
