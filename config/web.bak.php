<?php
/**
 * 项目配置
 */
$_webConfig = [
	'id'         => 'geshop',
	'components' => [
		'session'         => [
			'class'        => 'ego\session\Redis',
			'keyPrefix'    => 'geshop:session:',
			'cookieParams' => [
				'domain' => FULL_DOMAIN,
			],
			'timeout'      => 86400
		],
		'phpProfile'      => [
			'site' => 'geshop',
		],
		'request'         => [
			'enableCsrfValidation' => false,
		],
		'phpProfileRedis' => [
			'class'    => 'yii\redis\Connection',
			'database' => null,
			'hostname' => '192.168.6.176',
			'port'     => 6380,
		],
		'phplog'          => [
			'class' => 'app\components\Phplog',
		],
		'diff'            => [
			'class' => 'Globalegrow\PhpDiff\Diff',
		],
		'db' => [
			'class' => 'app\components\MysqlWithTracing',
			'charset' => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => sprintf('mysql:host=%s;dbname=%s', apolloConfig('components.db.host'), apolloConfig('components.db.name')),
			'username'          => apolloConfig('components.db.username'),
			'password'          => apolloConfig('components.db.password'),
		],
		'db_slave' => [
			'class' => 'app\components\MysqlWithTracing',
			'charset' => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => sprintf('mysql:host=%s;dbname=%s', apolloConfig('components.db.host'), apolloConfig('components.db.name')),
			'username'          => apolloConfig('components.db.username'),
			'password'          => apolloConfig('components.db.password'),
		],
		'redis'           => [
			'class'      => Globalegrow\YiiPredis\Connection::class,
			'parameters' => [
				[
					'host'     => apolloConfig('components.redis.parameters.0.host'),
					'port'     => apolloConfig('components.redis.parameters.0.port'),
					'password' => apolloConfig('components.redis.parameters.0.password'),
				],
				[
					'host'     => apolloConfig('components.redis.parameters.1.host'),
					'port'     => apolloConfig('components.redis.parameters.1.port'),
					'password' => apolloConfig('components.redis.parameters.1.password'),
				],
				[
					'host'     => apolloConfig('components.redis.parameters.2.host'),
					'port'     => apolloConfig('components.redis.parameters.2.port'),
					'password' => apolloConfig('components.redis.parameters.2.password'),
				],
			],
			'options'    => [
				'replication' => apolloConfig('components.redis.options.replication'),
				'service'     => apolloConfig('components.redis.options.service'),
			],
		],
		'apiRedis'        => [
			'class'      => Globalegrow\YiiPredis\Connection::class,
			'parameters' => [
				[
					'host'     => apolloConfig('components.api_redis.parameters.0.host'),
					'port'     => apolloConfig('components.api_redis.parameters.0.port'),
					'password' => apolloConfig('components.api_redis.parameters.0.password'),
				],
				[
					'host'     => apolloConfig('components.api_redis.parameters.1.host'),
					'port'     => apolloConfig('components.api_redis.parameters.1.port'),
					'password' => apolloConfig('components.api_redis.parameters.1.password'),
				],
				[
					'host'     => apolloConfig('components.api_redis.parameters.2.host'),
					'port'     => apolloConfig('components.api_redis.parameters.2.port'),
					'password' => apolloConfig('components.api_redis.parameters.2.password'),
				],
			],
			'options'    => [
				'replication' => apolloConfig('components.api_redis.options.replication'),
				'service'     => apolloConfig('components.api_redis.options.service'),
			],
		],
		'swoole'          => [
			'class' => \app\base\SwooleClient::class
		],
		//大数据系统数据中转数据库连接
		'bd_transfer'     => [
			'class'             => 'app\components\MysqlWithTracing',
			'charset'           => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => sprintf('mysql:host=%s;dbname=%s', apolloConfig('components.bd_transfer.host'), apolloConfig('components.bd_transfer.name')),
			'username'          => apolloConfig('components.bd_transfer.username'),
			'password'          => apolloConfig('components.bd_transfer.password'),
		],
		// 亚马逊s3配置
		's3'              => [
			'config'        => [
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
		'cache'           => [
			'keyPrefix' => 'geshop:',
		],
		'rcache'          => [ //可灵活固定KEY头的CACHE
		                       'class'     => 'app\base\Cache',
		                       'keyPrefix' => 'geshop:'
		],
		'redisKey'        => [
			'class' => 'app\base\RedisKey'
		],
		'log'             => [
			'targets' => [
				'file'          => [
					'except' => ['app\components\auto\*', 'app\components\fallback\*', 'app\components\monitor\*'],
				],
				'uiAutoRefresh' => [ // 组件自动刷新日志
				                     'class'       => 'ego\log\FileTarget',
				                     'levels'      => ['error', 'warning', 'info'],
				                     'logVars'     => [],
				                     'categories'  => ['app\components\auto\*'],
				                     'logFile'     => '@app/runtime/logs/ui-auto-refresh.log',
				                     'maxFileSize' => 1024 * 20,
				                     'maxLogFiles' => 20,
				],
				'fallback'      => [ // 组件异步接口兜底
				                     'class'       => 'ego\log\FileTarget',
				                     'levels'      => ['error', 'warning', 'info'],
				                     'logVars'     => [],
				                     'categories'  => ['app\components\fallback\*'],
				                     'logFile'     => '@app/runtime/logs/fallback.log',
				                     'maxFileSize' => 1024 * 20,
				                     'maxLogFiles' => 20,
				],
				'monitor'       => [ // 组件异步接口监控
				                     'class'       => 'ego\log\FileTarget',
				                     'levels'      => ['error', 'warning', 'info'],
				                     'logVars'     => [],
				                     'categories'  => ['app\components\monitor\*'],
				                     'logFile'     => '@app/runtime/logs/monitor.log',
				                     'maxFileSize' => 1024 * 20,
				                     'maxLogFiles' => 20,
				],
				[
					'class'       => 'ego\log\FileTarget',
					'levels'      => ['warning'],
					'categories'  => ['task'],//定时任务的独立日志记录
					'logFile'     => '@app/runtime/logs/task.log',
					'maxFileSize' => 1024 * 2,
					'maxLogFiles' => 10,
				],
				[
					'class'       => 'ego\log\FileTarget',
					'levels'      => ['error', 'warning', 'info'],
					'categories'  => ['promise'], //并发调用接口的独立日志记录
					'logFile'     => '@app/runtime/logs/promise.log',
					'maxFileSize' => 1024 * 20,
					'maxLogFiles' => 20,
				],
			],
		],
		'errorHandler'    => [
			'class'       => 'app\components\CustomErrorHandler',
			'errorAction' => 'base/error/index',
		],
		'urlManager'      => [
			'enablePrettyUrl'     => true,
			'enableStrictParsing' => true,
			'showScriptName'      => false,
			'rules'               => require('rules.php'),
		],
		'url'             => 'app\components\Url',
		'rms'             => [
			'class'        => 'app\components\Rms',
			'url'          => apolloConfig('components.rms.url'),
			'token'        => apolloConfig('components.rms.token'),
			'metadataFile' => apolloConfig('components.rms.metadataFile'),
			'metadataUrl'  => apolloConfig('components.rms.metadataUrl'),
			'redisKey'     => apolloConfig('components.rms.redisKey'),
		],
		'user'            => [
			'class'         => 'app\base\User',
			'identityClass' => 'app\base\Identity',
			'loginUrl'      => '/base/login/login',
		],
		'authManager'     => [
			'class' => 'app\modules\base\models\RoleModel',
		],
		'arrayTree'       => ego\base\ArrayTree::class,
		'i18n'            => [
			'translations' => [
				'yii' => [
					'class'          => 'yii\i18n\PhpMessageSource',
					'basePath'       => '@app/messages',
					'sourceLanguage' => 'en-US',
				],
			],
		],
		'view'            => [
			'class'     => 'yii\web\View',
			'renderers' => [
				'twig' => [
					'class'     => 'yii\twig\ViewRenderer',
					'cachePath' => '@runtime/Twig/cache',
					'functions' => [
						't'                             => 'yii::t',
						'gb_component_trans'            => '\app\base\Translate::gbUiComponentTrans',
						'get_component_trans'           => '\app\base\Translate::getUiComponentTrans',
						'html_encoder_goods_promotions' => '\app\base\TwigFunctions::htmlEncoderGoodsPromotions',
						'json_decode'                   => '\app\base\TwigFunctions::jsonDecode',
						'json_encode_no_unicode'        => '\app\base\TwigFunctions::jsonEncodeNoUnicode'
					],
					// Array of twig options:
					'options'   => [
						'auto_reload' => true,
					],
					'globals'   => [
						'html' => ['class' => '\yii\helpers\Html'],
					],
					'uses'      => ['yii\bootstrap'],
				],
				// ...
			],
		],
	],
	'modules'    => apolloConfig('modules'),
	'timeZone'   => apolloConfig('time_zone'),
	'params'     => [
		// 项目所属平台
		'platform'           => ego\enums\Platform::PC,
		'url'                => [
			'admin'        => 'http://www.' . DOMAIN,
			'sso'          => 'http://user.hqygou.com',
			'assets'       => 'http://www.' . DOMAIN,
			'token_secret' => 'afasf*^d#&^h213sa152',
			'project_sn'   => 'geshop',
		],
		// css版本号
		'css_version'        => apolloConfig('params.css_version') ?? time(),
		// js版本号
		'js_version'         => apolloConfig('params.js_version') ?? time(),
		// 权限
		'rbac'               => [
			// 具有所有权限的角色名
			'super_role' => '超级管理员',
			// 不需要权限的路由
			'exclude'    => apolloConfig('params.rbac.exclude'),
		],
		'site'               => require(__DIR__ . '/site/site.php'),
		'sites'              => require(__DIR__ . '/sites/sites.php'),
		'soa'                => require(__DIR__ . '/soa/soa.php'),
		's3UploadsConf'      => [
			'staticDomain' => apolloConfig('params.s3UploadsConf.staticDomain'),
			'staticKeyPre' => apolloConfig('params.s3UploadsConf.staticKeyPre'),
		],
		's3PublishConf'      => [
			'staticDomain' => apolloConfig('params.s3PublishConf.staticDomain'),
			'staticKeyPre' => apolloConfig('params.s3PublishConf.staticKeyPre'),
		],
		'gateway'            => [
			'app_key' => apolloConfig('params.gateway.app_key'),
			'sign'    => apolloConfig('params.gateway.sign')
		],
		'conponentForSkuCopy' => [
			'U000001', 'U000031', 'U000041', 'U000055', 'U000056', 'U000061', 'U000061',
			'U000063', 'U000064', 'U000076', 'U000077', 'U000078', 'U000080', 'U000081', 'U000083', 'U000084',
			'U000086', 'U000087', 'U000090', 'U000109', 'U000110', 'U000113', 'U000120', 'U000128', 'U000129',
			'U000130', 'U000031', 'U000133', 'U000141', 'U000149', 'U000150', 'U000151', 'U000152', 'U000153',
			'U000162', 'U000163', 'U000164', 'U000165', 'U000166', 'U000177', 'U000178', 'U000182', 'U000190',
			'U000191', 'U000192', 'U000203', 'U000207', 'U000208', 'U000209', 'U000218', 'U000219', 'U000236',
			'U000237', 'U000245'
		],
		//CDN缓存清理接口
		'clearCDNAPI'        => apolloConfig('components.cdn.api'),
        'cdnCacheControl' => apolloConfig('components.cdn.control'),//cdn缓存时间
        'cdnExpires' => apolloConfig('components.cdn.expires'),//cdn过期时间
		//需要翻译的语系
		'lang'               => apolloConfig('params.lang'),
		// 英语语言简码
		'en_lang'            => 'en',
		//超级管理员帐号
		'superAdminAccounts' => [
			'zhanghua',
			'mashanling'
		],
		//不需要静态域名的路由
		'no_domain_route'    => apolloConfig('params.no_domain_route'),
		//基础元素组件文件路径
		'commponent_path'    => '/files/parts/components'
	],
];

// 测试环境没有设置密码,redis连接报错
$_redisPassword = apolloConfig('components.api_redis.options.parameters.password');
if (!empty($_redisPassword)) {
    $_webConfig['components']['apiRedis']['options']['parameters']['password'] = $_redisPassword;
}
unset($_redisPassword);

return $_webConfig;
