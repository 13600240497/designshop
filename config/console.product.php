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
	'components'          => [
		'redis'           => [
			'class'      => Globalegrow\YiiPredis\Connection::class,
			'parameters' => [
				[
					'host'     => '10.176.243.91',
					'port'     => 6395,
					'password' => '6e1KWyC29w',
				],
				[
					'host'     => '10.176.243.79',
					'port'     => 6394,
					'password' => '6e1KWyC29w',
				],
				[
					'host'     => '10.93.157.44',
					'port'     => 6380,
					'password' => '6e1KWyC29w',
				],
			],
			'options'    => [
				'replication' => 'sentinel',
				'service'     => 'sentinel-10.176.243.91-6394',
			],
		],
		'db'              => [
			'class'               => 'yii\db\Connection',
			'charset'             => 'utf8',
			'enableSchemaCache'   => true,
			'schemaCacheDuration' => 3600,
			'dsn'                 => 'mysql:host=10.93.179.6;dbname=geshop_db',
			'username'            => 'geshop_m_user',
			'password'            => 'mns91dW0q#zv',
		],
		//从库，只用于查询
		'db_slave'        => [
			'class'               => 'yii\db\Connection',
			'charset'             => 'utf8',
			'enableSchemaCache'   => true,
			'schemaCacheDuration' => 3600,
			'dsn'                 => 'mysql:host=10.93.179.6;dbname=geshop_db',
			'username'            => 'geshop_m_user',
			'password'            => 'mns91dW0q#zv',
		],
		'log'             => [
			'targets' => [
				'file' => [
					'class'  => 'ego\log\FileTarget',
					'levels' => ['error', 'warning', 'info'],
					'except' => [
						'yii\db\*',
					],
				],
			],
		],
		'phplog'          => [
			'class' => 'ego\log\Phplog',
		],
		// 亚马逊s3配置
		's3'              => [
			'class'         => 'ego\aws\S3Client',
			'config'        => [
				'version'     => 'latest',
				'region'      => 'us-east-1',
				'credentials' => [
					'key'    => 'AKIAJ6BHMQEYKUWWXGEQ',
					'secret' => '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',
				],
			],
			'defaultBucket' => 'geshop',
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
			'class'        => 'app\components\Rms',
			'url'          => 'http://www.rms110.com/api-source?project_code=M1804003',
			'metadataFile' => '/tmp/metadata_tags',
			'metadataUrl'  => 'xxx',
			'redisKey' => 'geshop:rms:error_log_mq',
		],
		'env'             => [
			'class' => Globalegrow\Base\Env::class,
			'env'   => YII_ENV,
		],
		'errorHandler'    => [
			'class' => 'app\commands\component\CustomErrorHandler',
		],
	],
	'params'              => [
		's3UploadsConf' => [
			'staticDomain' => 'https://geshopimg.logsss.com',
			'staticKeyPre' => '/uploads/',
		],
		's3PublishConf' => [
			'staticDomain' => 'https://geshopcss.logsss.com',
			'staticKeyPre' => '/imagecache/geshop',
		],
		//CDN缓存清理接口
		'clearCDNAPI'   => 'http://purge.faout.com:8080/clear?url=',
	]
];

//if (YII_ENV_DEV) {
// configuration adjustments for 'dev' environment
$config['bootstrap'][] = 'gii';
$config['modules']['gii'] = [
	'class' => 'yii\gii\Module',
];
//}

return $config;
