<?php
return yii\helpers\ArrayHelper::merge(
    [
        'components' => [
            'log' => [
                'targets' => [
                    'file' => [
                        'logVars' => [],
                    ],
                ],
            ],
        ],
        'modules' => [
            'yii-debug' => [
                'class' => 'yii\debug\Module',
                'allowedIPs' => ['*'],
            ]
        ],
    ],
    require(__DIR__ . '/web.test.php')
);
