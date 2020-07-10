<?php
/**
 * 线上环境配置
 */
return [
	'params'     => [
		'url'           => [
			'admin' => 'http://geshop.' . DOMAIN,
			'sso'   => 'http://user.gw-ec.com',
		],
		'wkPath'        => '',
		'appFullDomain' => 'api.hqgeshop.com',
		'appDeveloper'  => 'staging',
	],
];
