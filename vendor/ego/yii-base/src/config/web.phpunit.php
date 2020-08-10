<?php
return [
    'components' => [
        'mailer' => [
            // 默认不真正发送邮件
            'useFileTransport' => true,
        ],
        's3' => [
            // 这里站点地图的配置
            'config' => [
                'credentials' => [
                    'key' => "AKIAICJISH46WHYXNTPQ",
                    'secret' => "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",
                ],
            ],
            'defaultBucket' => 'test-webpublicfile'
        ],
        'redis' => [
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
            'options' => [
                'replication' => 'sentinel',
                'service' => 'sentinel-192.168.6.176-26388',
            ],
        ],
        'site' => [
            'code2names' => [
                'GLB' => 'girlbest',
            ],
            'code' => 'GLB',
        ]
    ],
    'params' => [
        'service' => [
            'url' => 'http://www.yii-base.com.mashanling.dev65.egocdn.com',
            'tcp_address' => '127.0.0.1:10005',
            'tcp_token' => '127.0.0.1:10005',
            'tcp_version' => '1.1.1',
        ],
    ]
];
