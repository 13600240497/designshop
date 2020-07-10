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
		            'host' => apolloConfig('components.redis.parameters.0.host'),
		            'port' => apolloConfig('components.redis.parameters.0.port'),
	            ],
	            [
		            'host' => apolloConfig('components.redis.parameters.1.host'),
		            'port' => apolloConfig('components.redis.parameters.1.port'),
	            ],
	            [
		            'host' => apolloConfig('components.redis.parameters.2.host'),
		            'port' => apolloConfig('components.redis.parameters.2.port'),
	            ],
            ],
            'options'    => [
	            'replication' => apolloConfig('components.redis.options.replication'),
	            'service'     => apolloConfig('components.redis.options.service'),
            ]
        ],
        'db'              => [
            'class'               => 'yii\db\Connection',
            'charset'             => 'utf8',
            'enableSchemaCache'   => true,
            'schemaCacheDuration' => 3600,
            'dsn'                 => sprintf('mysql:host=%s;dbname=%s', apolloConfig('components.db.host'), apolloConfig('components.db.name')),
            'username'            => apolloConfig('components.db.username'),
            'password'            => apolloConfig('components.db.password'),
        ],
        //从库，只用于查询
        'db_slave'        => [
            'class'               => 'yii\db\Connection',
            'charset'             => 'utf8',
            'enableSchemaCache'   => true,
            'schemaCacheDuration' => 3600,
            'dsn'                 => sprintf('mysql:host=%s;dbname=%s', apolloConfig('components.db_slave.host'), apolloConfig('components.db_slave.name')),
            'username'            => apolloConfig('components.db_slave.username'),
            'password'            => apolloConfig('components.db_slave.password'),
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
	            'version'     => apolloConfig('components.s3.config.version'),
	            'region'      => apolloConfig('components.s3.config.region'),
	            'credentials' => [
		            'key'    => apolloConfig('components.s3.config.credentials.key'),
		            'secret' => apolloConfig('components.s3.config.credentials.secret'),
	            ],
            ],
            'defaultBucket' => apolloConfig('components.s3.defaultBucket'),
        ],
        's3PublishStatic' => [
            'class'         => 'ego\aws\S3Client',
            'config'        => [
	            'version'     => apolloConfig('components.s3PublishStatic.config.version'),
	            'region'      => apolloConfig('components.s3PublishStatic.config.region'),
	            'credentials' => [
		            'key'    => apolloConfig('components.s3PublishStatic.config.credentials.key'),
		            'secret' => apolloConfig('components.s3PublishStatic.config.credentials.secret'),
	            ],
            ],
            'defaultBucket' => apolloConfig('components.s3PublishStatic.defaultBucket'),
        ],
        'cdn'             => [
            'class' => 'ego\base\Cdn',
            'api'   => apolloConfig('components.cdn.api')
        ],
        'datetime'        => Globalegrow\Base\Datetime::class,
        'rms'             => [
            'class'        => 'app\components\Rms',
            'url'          => apolloConfig('components.rms.url'),
            'metadataFile' => apolloConfig('components.rms.metadataFile'),
            'metadataUrl'  => apolloConfig('components.rms.metadataUrl'),
            'redisKey'     => apolloConfig('components.rms.redisKey'),
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
		    'staticDomain' => apolloConfig('params.s3UploadsConf.staticDomain'),
		    'staticKeyPre' => apolloConfig('params.s3UploadsConf.staticKeyPre'),
	    ],
	    's3PublishConf' => [
		    'staticDomain' => apolloConfig('params.s3PublishConf.staticDomain'),
		    'staticKeyPre' => apolloConfig('params.s3PublishConf.staticKeyPre'),
	    ],
	    //CDN缓存清理接口
	    'clearCDNAPI'   => apolloConfig('components.cdn.api'),
        'cdnCacheControl' => apolloConfig('components.cdn.control'),//cdn缓存时间
        'cdnExpires' => apolloConfig('components.cdn.expires'),//cdn过期时间
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
