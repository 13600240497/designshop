<?php
/**
 * 测试环境配置
 */
return [
	'components' => [
		'redis' => [
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
			'options' => [
				'replication' => 'sentinel',
				'service' => 'sentinel-192.168.6.176-26388',
			]
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
			'options' => [
				'replication' => 'sentinel',
				'service' => 'sentinel-192.168.6.176-26388',
			]
		],
		/*'db' => [
			'class' => 'yii\db\Connection',
			'charset' => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn' => 'mysql:host=10.40.6.148;dbname=geshop_test',
			'username' => 'root',
			'password' => 'NvGHHsQvo3!90YS@',
		]*/
		'db' => [
			'class' => 'yii\db\Connection',
			'charset' => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn' => 'mysql:host=10.60.49.163;dbname=geshop_test',
			'username' => 'geshop',
			'password' => 'geshop_test',
		]
	],
	'timeZone' => 'Asia/Shanghai',
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
