<?php
/**
 * MQ预发布环境配置
 */
$ipsBase = [
    'host' => '10.177.33.130',
    'port' => 5672,
    'login' => 'sys_ges_product',
    'password' => 'R6xsndS7',
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
            'host' => '10.177.33.130',
            'port' => 5672,
            'login' => 'sys_ges_product',
            'password' => 'R6xsndS7',
            'vhost' => 'SYS_OBS'
        ],
        'connect_timeout' => 25,
    ],
]);
