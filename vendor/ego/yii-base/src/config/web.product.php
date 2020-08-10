<?php
return [
    'components' => [
        'log' => [
            'targets' => [
                'file' => [
                    'logVars' => [],
                    'except' => [
                        'debug',
                        'yii\web\HttpException:404',
                    ],
                    'maxFileSize' => Globalegrow\Base\Env::isPreRelease() ? 5012: 102400,
                    'maxLogFiles' => Globalegrow\Base\Env::isPreRelease() ? 10: 5,
                ],
            ],
        ],
        'mailer' => [
            // 线上环境，必须配置帐号密码信息
            'transport' => [
                'host' => 'host',
                'username' => 'username',
                'password' => 'password',
            ],
        ],
    ],
];
