<?php
/**
 * 开发环境配置
 */
return [
	'bootstrap'  => [
		//'yii-debug'
	],
	'components' => [
		'redis'    => [
			'parameters' => [
				[
					'host' => '192.168.33.10',
					'port' => 6379,
				],
				/*[
					'host' => '192.168.6.176',
					'port' => 26391,
				],
				[
					'host' => '192.168.6.176',
					'port' => 26392,
				],*/
			],
			/*'options'    => [
				'replication' => 'sentinel',
				'service'     => 'sentinel-192.168.6.176-26388',
			],*/
		],
		'apiRedis' => [
			'parameters' => [
				[
					'host' => '192.168.6.176',
					'port' => 26390,
				],
				[
					'host' => '192.168.6.176',
					'port' => 26391,
				],
				[
					'host' => '192.168.6.176',
					'port' => 26392,
				],
			],
			'options'    => [
				'replication' => 'sentinel',
				'service'     => 'sentinel-192.168.6.176-26388',
			],
		],
		//本地调试的时候用
		/*'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
            'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
            'dsn' => 'mysql:host=10.40.6.148;dbname=geshop',
            'username' => 'root',
            'password' => 'NvGHHsQvo3!90YS@',
		]*/


		'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
            'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
            'dsn' => 'mysql:host=192.168.33.10;dbname=designshop',
            'username' => 'root',
            'password' => 'mysql',
        ]

	],
	'timeZone'   => 'Asia/Shanghai',

	'params'     => [
		'url'           => [
			'admin'  => 'http://' . FULL_DOMAIN,
			'assets' => 'http://' . FULL_DOMAIN
		],
		'wkPath'        => '/vagrant_data/app/wkhtmltox/bin/wkhtmltoimage',
		'appFullDomain' => 'api.geshop.net',
		'appDeveloper'  => 'local',
	]
];
