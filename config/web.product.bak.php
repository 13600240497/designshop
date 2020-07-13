<?php
/**
 * 线上环境配置
 */
return [
    'components' => [
        'log'             => [
            'targets' => [
                'file' => [
                    'except' => [
                        'yii\db\*',
                    ],
                ],
            ],
        ],
    ],
    'params'     => [
        'url' => [
            'admin' => 'http://geshop.' . DOMAIN,
            'sso'   => 'http://user.gw-ec.com',
        ],
        'wkPath'        => 'wkhtmltoimage', //网页快照扩展
        'appFullDomain' => 'api.hqgeshop.com',
        'appDeveloper'  => 'production',
    ],
];
