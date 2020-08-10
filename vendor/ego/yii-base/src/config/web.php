<?php
return [
    'id' => 'yii',
    'language' => 'en',
    'basePath' => APP_PATH,
    'bootstrap' => ['log'],
    'components' => [
        'response' => [
            'formatters' => [
                'json' => 'ego\web\JsonResponseFormatter',
            ]
        ],
        'request' => [
            'class' => 'ego\web\Request',
            //'enableCsrfValidation' => false,
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '9nfRpkQ9RZYk8TzAVMsVeThtLePM9HdR',
            'csrfCookieDomain' => DOMAIN,
        ],
        'crypt' => [
            'class' => 'ego\base\Crypt',
            'key' => '0uhLdE`o*1R[Gqnb19Krj!L>kOzOIaec'
        ],
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'ego\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'phplog' => [
            'class' => 'ego\log\Phplog',
        ],
        'diff' => [
            'class' => 'ego\diff\Diff',
        ],
        'mailer' => [
            'class' => 'ego\mail\Mailer',
            'messageConfig' => [
                'class' => 'ego\mail\Message',
                'charset' => 'UTF-8',
                'sensitiveSmartyVars' => [
                    'password',
                    'newPassword',
                ],
            ],
            'transport' => [
                'class' => 'Swift_SmtpTransport',
            ],
        ],
        // 亚马逊s3配置
        's3' => [
            'class' => 'ego\aws\S3Client',
            'config' => [
                'version' => 'latest',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => null,
                    'secret' => null,
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.6.176',
            'database' => null,
            'port' => 26386,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'keyPrefix' => '',
        ],
        'phpProfile' => [
            'class' => ego\base\PhpProfile::class,
        ],
        'cdn' => [
            'class' => 'ego\base\Cdn',
            'api' => 'http://purge.faout.com:8080/clear?url='
        ],
        'site' => [
            'class' => 'ego\base\Site',
            'code' => defined('SITECODE') ? SITECODE : null,
            'code2names' => [
                'GB' => 'gearbest',
                'GLB' => 'girlbest',
                'GBES' => 'gbes',
                'GBIT' => 'gbit',
                'GBRU' => 'gbru',
                'GBDE' => 'gbde',
                'GBPT' => 'gbpt',
                'GBFR' => 'gbfr',
                'GBUK' => 'gbuk',
                'GBUS' => 'gbus',
                'GBMX' => 'gbmx',
                'GBBR' => 'gbbr',
                'GBGR' => 'gbgr',
                'GBNL' => 'gbnl',
                'GBTR' => 'gbtr',
                /*'BG' => 'buyinggoods',
                'BN' => 'boynewyork',
                'CB' => 'chinabrands',
                'DB' => 'digbest',
                'DF' => 'dressfo',
                'DL' => 'dresslily',
                'DM' => 'dealsmachine',
                'DN' => 'dizener',
                'DZ' => 'dezzal',
                'EV' => 'everbuying',
                'GB' => 'gearbest',
                'GLB' => 'girlbest',
                'GS' => 'gamiss',
                'IG' => 'igogo',
                'IM' => 'wzhouhui',
                'ND' => 'nastydress',
                'NT' => 'nextmia',
                'RG' => 'rosegal',
                'RW' => 'rosewholesale',
                'S1' => 'OMS',
                'S2' => 'PMS',
                'S3' => 'WMS',
                'S4' => 'WZH',
                'S5' => 'WZH_WMS',
                'S6' => 'Provider',
                'S7' => 'ERP',
                'SB' => 'ifs',
                'SD' => 'sammydress',
                'TD' => 'twinkledeals',
                'TG' => 'trendsgal',
                'VB' => 'volumebest',
                'YS' => 'yoshop',
                'ZF' => 'zaful',*/
            ]
        ],
        'helper' => ego\helpers\Helper::class,
        'validatorModel' => 'ego\models\ValidatorModel',
        'arrayCache' => 'yii\caching\ArrayCache',
        'mq' => ego\mq\Client::class,
        'env' => [
            'class' => Globalegrow\Base\Env::class,
            'env' => YII_ENV,
        ],
        'debug' => [
            'class' => Globalegrow\Base\Debug::class,
            'isDebug' => YII_DEBUG,
        ],
        'datetime' => Globalegrow\Base\Datetime::class,
        'assertion' => Globalegrow\Base\Assertion::class,
        'ip' => Globalegrow\Base\Ip::class,
        'arrayTree' => Globalegrow\Base\ArrayTree::class,
    ],
    'params' => [
        'service' => [
            // 接口url
            'url' => null,
            // guzzle client默认选项
            'default_guzzle_options' => [],
        ],
    ]
];
