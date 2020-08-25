<?php
/**
 * 项目配置
 */
return [
	'id'         => 'geshop',
	'components' => [
		'session'         => [
			'class'        => 'ego\session\Redis',
			'keyPrefix'    => 'geshop:session:',
			'cookieParams' => [
				'domain' => DOMAIN,
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
		'redis'           => [
			'class' => Globalegrow\YiiPredis\Connection::class,
			// 在web.dev.php || web.test.php || web.product.php 中配置redis集群信息
			/*'parameters' => [
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
			], */
		],
		'apiRedis'           => [
			'class' => Globalegrow\YiiPredis\Connection::class
		],
		'swoole' => [
			'class' => \app\base\SwooleClient::class
		],
		'db'              => [
			'class'             => 'yii\db\Connection',
			'charset'           => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => 'mysql:host=10.40.6.148;dbname=geshop',
			'username'          => 'root',
			'password'          => 'NvGHHsQvo3!90YS@',
		],
		//从库，只用于查询
		'db_slave'        => [
			'class'             => 'yii\db\Connection',
			'charset'           => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => 'mysql:host=10.40.6.148;dbname=geshop',
			'username'          => 'root',
			'password'          => 'NvGHHsQvo3!90YS@',
		],
		//大数据系统数据中转数据库连接
		'bd_transfer'     => [
			'class'             => 'yii\db\Connection',
			'charset'           => 'utf8',
			'enableSchemaCache' => false !== strpos(YII_ENV, 'product'),
			'dsn'               => 'mysql:host=75.126.169.102;dbname=geshop_data',
			'username'          => 'bigdata_RW',
			'password'          => 'G0UdEXw$RzoqoeV0',
		],
		// 亚马逊s3配置
		's3'              => [
			'config'        => [
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
		'cache'           => [
			'keyPrefix' => 'geshop:',
		],
		'rcache'           => [ //可灵活固定KEY头的CACHE
			'class' => 'app\base\Cache',
			'keyPrefix' => 'geshop:'
		],
		'redisKey'        => [
			'class' => 'app\base\RedisKey'
		],
		'log'             => [
			'targets' => [
				'file' => [
					'except' => ['app\components\auto\*', 'app\components\fallback\*', 'app\components\monitor\*'],
				],
				'uiAutoRefresh' => [ // 组件自动刷新日志
					'class'          => 'ego\log\FileTarget',
					'levels'         => ['error', 'warning', 'info'],
					'logVars'        => [],
					'categories'     => ['app\components\auto\*'],
					'logFile'        => '@app/runtime/logs/ui-auto-refresh.log',
					'maxFileSize'    => 1024 * 20,
					'maxLogFiles'    => 20,
				],
				'fallback' => [ // 组件异步接口兜底
					'class'          => 'ego\log\FileTarget',
					'levels'         => ['error', 'warning', 'info'],
					'logVars'        => [],
					'categories'     => ['app\components\fallback\*'],
					'logFile'        => '@app/runtime/logs/fallback.log',
					'maxFileSize'    => 1024 * 20,
					'maxLogFiles'    => 20,
				],
				'monitor' => [ // 组件异步接口监控
					'class'          => 'ego\log\FileTarget',
					'levels'         => ['error', 'warning', 'info'],
					'logVars'        => [],
					'categories'     => ['app\components\monitor\*'],
					'logFile'        => '@app/runtime/logs/monitor.log',
					'maxFileSize'    => 1024 * 20,
					'maxLogFiles'    => 20,
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
					'class'          => 'ego\log\FileTarget',
					'levels'         => ['error', 'warning', 'info'],
					'categories'     => ['promise'], //并发调用接口的独立日志记录
					'logFile'        => '@app/runtime/logs/promise.log',
					'maxFileSize'    => 1024 * 20,
					'maxLogFiles'    => 20,
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
						't'                      => 'yii::t',
						'gb_component_trans'     => '\app\base\Translate::gbUiComponentTrans',
						'get_component_trans'     => '\app\base\Translate::getUiComponentTrans',
						'html_encoder_goods_promotions' => '\app\base\TwigFunctions::htmlEncoderGoodsPromotions',
						'json_decode'            => '\app\base\TwigFunctions::jsonDecode',
						'json_encode_no_unicode' => '\app\base\TwigFunctions::jsonEncodeNoUnicode'
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
	'modules'    => [
		'admin'             => 'app\modules\admin\Module',
		'base'              => 'app\modules\base\Module',
		'component'         => 'app\modules\component\Module',
		'activity'          => 'app\modules\activity\Module',
		'home'              => 'app\modules\home\Module',
		'test'              => 'app\modules\test\Module',
		'api'               => 'app\modules\api\Module',
		'soa'               => 'app\modules\soa\Module',
		'advertisement'     => 'app\modules\advertisement\Module',
		'gbad'              => 'app\modules\gbad\Module',
		'activity_gb'       => 'app\modules\activity\gb\Module',
		'activity_zf'       => 'app\modules\activity\zf\Module',
		'home_zf'           => 'app\modules\home\zf\Module',
		'base_zf'           => 'app\modules\base\zf\Module',
		'test_zf'           => 'app\modules\test\zf\Module',
		'activity_dl'       => 'app\modules\activity\dl\Module',
		'common'            => 'app\modules\common\Module',
		'home_dl'           => 'app\modules\home\dl\Module',
		'base_dl'           => 'app\modules\base\dl\Module',
	],
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
		'css_version'        => time(),
		// js版本号
		'js_version'         => time(),
		// 权限
		'rbac'               => [
			// 具有所有权限的角色名
			'super_role' => '超级管理员',
			// 不需要权限的路由
			'exclude'    => [
				'base/index/index',
				'base/login/login',
				'base/login/logout',
				'activity/activity/index',
				'activity/qr-code/create',
				'activity/crontab/refresh-page',
				'activity/crontab/offline-page',
				'activity/crontab/push-page',
				'activity/s3/sync-resource',
				'api/activity/page-multi-language',
				'soa/mq/ips-goods-sku-sync',
				'advertisement/crontab/push-data',//推送推广活动页面到网站
				'activity/crontab/update-online-goods',//更新上线页面产品数据
				'activity/crontab/get-online-page', //获取上线页面
				'soa/mq/obs-goods-sync',//消费obs更新MQ
			],
		],
		'site'               => require(__DIR__ . '/site/site.php'),
		'sites'              => require(__DIR__ . '/sites/sites.php'),
		'soa'                => require(__DIR__ . '/soa/soa.php'),
		's3UploadsConf'      => [
			'staticDomain' => 'https://geshoptest.s3.amazonaws.com',
			'staticKeyPre' => '/uploads/',
		],
		's3PublishConf'      => [
			'staticDomain' => 'https://geshopcss.logsss.com',
			'staticKeyPre' => '/imagecache/geshop-test',
		],
		'gateway'            => [
			'app_key' => 'gateway',
			'sign'    => '3e21ab62fb17400301d9f0156b6c3031'
		],
		//CDN缓存清理接口
		'clearCDNAPI'        => 'http://purge.faout.com:8080/clear?url=',
		//需要翻译的语系
		'lang'               => [
			'en' => [
				'code' => 'en-US',
				'name' => '英文'
			],
			'de' => [
				'code' => 'de-DE',
				'name' => '德文'
			],
			'es' => [
				'code' => 'es-ES',
				'name' => '西班牙文'
			],
			'ep' => [ // 兼容GB语言简码
				'code' => 'es-ES',
				'name' => '西班牙文'
			],
			'fr' => [
				'code' => 'fr-FR',
				'name' => '法文'
			],
			'it' => [
				'code' => 'it-IT',
				'name' => '意大利文'
			],
			'pt' => [
				'code' => 'pt-PT',
				'name' => '葡萄牙文'
			],
			'ru' => [
				'code' => 'ru-RU',
				'name' => '俄文'
			],
			'tr' => [
				'code' => 'tr-TR',
				'name' => '土耳其文'
			],
			'ar' => [
				'code' => 'ar-SA',
				'name' => '阿拉伯文'
			],
			'ie' => [
				'code' => 'en-ie',
				'name' => '爱尔兰语'
			],
			'th' => [
				'code' => 'th-TH',
				'name' => '泰语'
			],
			'id' => [
				'code' => 'id-ID',
				'name' => '印尼语'
			],
			'zh-tw' => [
				'code' => 'tw-CN',
				'name' => '繁体中文'
			],
			'vi' => [
				'code' => 'vi-VN',
				'name' => '越南语'
			],
			'ja' => [
				'code' => 'ja-JP',
				'name' => '日语'
			],
			'ro' => [
				'code' => 'ro-RO',
				'name' => '罗马尼亚语'
			],

		],
		// 英语语言简码
		'en_lang'            => 'en',
		//超级管理员帐号
		'superAdminAccounts' => [
			'zhanghua',
			'mashanling'
		],
		//不需要静态域名的路由
		'no_domain_route'    => [
			'activity/design/index',
			'activity/design/preview',
			'activity/page-tpl/preview',
			'activity/page-ui-tpl/ui-preview',
			'home/design/index',
			'home/design/preview',
			'advertisement/design/index',
			'advertisement/design/preview',
			'activity/zf/design/preview',
			'activity/zf/design/index',
			'activity/zf/page-tpl/preview',
			'activity/zf/page-ui-tpl/ui-preview',
			'home/zf/design/preview',
			'home/zf/design/index',
			'activity/dl/design/index',
			'activity/dl/design/preview',
			'activity/dl/ui-design/save-form',
			'activity/dl/page-tpl/preview',
			'activity/dl/page-ui-tpl/ui-preview',
			'home/dl/design/index',
			'home/dl/design/preview',
			'home/dl/page-tpl/preview',
			'home/dl/page-ui-tpl/ui-preview',
		],
		//基础元素组件文件路径
		'commponent_path'  => '/files/parts/components'
	],
];
