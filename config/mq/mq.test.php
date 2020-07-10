<?php
/**
 * mq测试环境配置
 */
$ipsBase = [
    'host' => '10.40.6.89',
    'port' => 5672,
    'login' => 'ges_test',
    'password' => 'ges_test',
    'vhost' => 'IPS'
];
return ego\mq\Config::build([
    // 选品系统活动产品SKU同步
    'activityChild_GES' => [
        'base' => $ipsBase,
        'connect_timeout' => 25,
    ],
    // OBS系统活动产品SKU同步
    'themeGoods_GES' => [
        'base' => [
            'host' => '10.40.6.89',
            'port' => 5672,
            'login' => 'ges_test',
            'password' => 'ges_test',
            'vhost' => 'SYS_OBS'
        ],
        'connect_timeout' => 25,
    ],
]);
