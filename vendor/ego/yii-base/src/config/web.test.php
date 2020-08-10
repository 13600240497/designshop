<?php
return [
    'components' => [
        'mailer' => [
            'transport' => [
                'host' => 'testmail.s1.egomsl.com',
                'username' => 'testmail01@testmail.s1.egomsl.com',
                'password' => 'testmail01$%^',
            ],
            'only' => [
                '@globalegrow.com' => '@globalegrow.com',
            ]
        ],
        'site' => [
            'code2names' => [
                'TEST1' => '测试1',
                'TEST2' => '测试2',
                'TEST3' => '测试3',
            ],
        ],
        'log' => [
            'targets' => [
                'file' => [
                    'fileMode' => 0777,
                    'dirMode' => 0777,
                ],
            ],
        ],
    ],
];
