<?php
/**
 * 线上环境配置
 */
return [
	'components' => [
		// 亚马逊s3配置
		's3'              => [
			'config'        => [
				'credentials' => [
					'key'    => "AKIAJ6BHMQEYKUWWXGEQ",
					'secret' => "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",
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
		'log'             => [
			'targets' => [
				'file' => [
					'except' => [
						'yii\db\*',
					],
				],
			],
		],
		'db'              => [
			'dsn'      => 'mysql:host=10.93.179.6;dbname=geshop_db',
			'username' => 'geshop_m_user',
			'password' => 'mns91dW0q#zv',
		],
		//从库，只用于查询
		'db_slave'        => [
			'dsn'      => 'mysql:host=10.93.179.6;dbname=geshop_db',
			'username' => 'geshop_m_user',
			'password' => 'mns91dW0q#zv',
		],
		//大数据系统数据中转数据库连接
		'bd_transfer'     => [
			'dsn'      => 'mysql:host=10.86.165.7;dbname=geshop_data',
			'username' => 'gp_data_ro',
			'password' => 'g0tgMKm#6saP9d0g',
		],
		'redis'           => [
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
					'host'     => '10.176.243.76',
					'port'     => 6396,
					'password' => '6e1KWyC29w',
				],
			],
			'options'    => [
				'replication' => 'sentinel',
				'service'     => 'sentinel-10.176.243.91-6394',
			],
		],
		'apiRedis' => [
			'parameters' => [
				[
					'host' => '10.177.52.140',
					'port' => 6381,
					'password' => '6e1KWyC29w',
				],
				[
					'host' => '10.177.52.238',
					'port' => 6382,
					'password' => '6e1KWyC29w',
				],
				[
					'host' => '10.171.164.247',
					'port' => 6388,
					'password' => '6e1KWyC29w',
				],
			],
			'options' => [
				'replication' => 'sentinel',
				'service' => 'sentinel-10.177.52.140-6380',
				'parameters' => [
					'password' => '6e1KWyC29w',
				]
			],
		],
		'rms'             => [
			'url'          => 'http://www.rms110.com/api-source?project_code=M1804003',
			'metadataFile' => '/tmp/metadata_tags',
			'metadataUrl'  => 'xxx',
		],
	],
	'params'     => [
		// css版本号
		'css_version' => 201911281207,
		// js版本号
		'js_version' => 201911281207,
		'url' => [
			'admin' => 'http://geshop.' . DOMAIN,
			'sso'   => 'http://user.gw-ec.com',
		],
		's3UploadsConf' => [
			'staticDomain' => 'https://geshopimg.logsss.com',
			'staticKeyPre' => '/uploads/',
		],
		's3PublishConf' => [
			'staticDomain' => 'https://geshopcss.logsss.com',
			'staticKeyPre' => '/imagecache/geshop',
		],
		'wkPath'        => 'wkhtmltoimage', //网页快照扩展
		'appFullDomain' => 'api.hqgeshop.com',
		'appDeveloper'  => 'production',
	],
];
