<?php

$config = [
	'id'                  => 'geshop',
	'bootstrap'           => ['log'],
	'basePath'            => dirname(__DIR__),
	'controllerNamespace' => 'app\commands',
	'aliases'             => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		'@tests' => '@app/tests',
	],
	'modules' => [
		'test' => [
			'class' => 'app\commands\modules\test\Module',
		],
	],
	'components'          => [
		'redis'           => [
			'class' => Globalegrow\YiiPredis\Connection::class,
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
			]
		],
		'db'              => [
			'class'             => 'yii\db\Connection',
			'charset'           => 'utf8',
			'enableSchemaCache' => true,
			'schemaCacheDuration' => 3600,
			'dsn'               => 'mysql:host=10.60.49.163;dbname=geshop_test',
			'username'          => 'geshop',
			'password'          => 'geshop_test',
		],
		// 亚马逊s3配置
		's3'              => [
			'class'         => 'ego\aws\S3Client',
			'config'        => [
				'version'     => 'latest',
				'region'      => 'us-east-1',
				'credentials' => [
					'key'    => 'AKIAI6IBIOMRTMQIYKBA',
					'secret' => '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',
				],
			],
			'defaultBucket' => 'geshoptest',
		],
		's3PublishStatic' => [
			'class'         => 'ego\aws\S3Client',
			'config'        => [
				'version'     => 'latest',
				'region'      => 'us-east-1',
				'credentials' => [
					'key'    => 'AKIAJ6BHMQEYKUWWXGEQ',
					'secret' => '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',
				],
			],
			'defaultBucket' => 'css.appinthestore.com',
		],
		'cdn'             => [
			'class' => 'ego\base\Cdn',
			'api'   => 'http://purge.faout.com:8080/clear?url='
		],
		'datetime'        => Globalegrow\Base\Datetime::class,
		'rms'             => [
			'class'    => 'app\components\Rms',
			'url'      => 'http://www.rms110.com.master.php7.egomsl.com/api-source?project_code=M1804003',
			'token'    => 'w8qwn9fJMEBKAQxKZXHE',
			'redisKey' => 'geshop:rms:error_log_mq',
		],
		'env' => [
			'class' => Globalegrow\Base\Env::class,
			'env' => YII_ENV,
		],
		'errorHandler'    => [
			'class' => 'app\commands\component\CustomErrorHandler',
		],
	],
	'params'              => [
		's3UploadsConf' => [
			'staticDomain' => 'https://geshoptest.s3.amazonaws.com',
			'staticKeyPre' => '/uploads/',
		],
		's3PublishConf' => [
			'staticDomain' => 'https://geshopcss.logsss.com',
			'staticKeyPre' => '/imagecache/geshop-test',
		],
		//CDN缓存清理接口
		'clearCDNAPI'   => 'http://purge.faout.com:8080/clear?url=',
	]
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
}

return $config;
