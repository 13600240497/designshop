<?php
/**
 * mq配置样例
 */
return Globalegrow\RabbitMQ\Config::build([
    'phpunit' => [
        'base' => [
            'host' => '10.40.6.89',
            'port' => 5672,
            'login' => 'gb_test',
            'password' => 'gb_test',
            'vhost' => 'WEB_GB'
        ],
        'queue' => [
            'routingkey' => 'routingkey',
        ],
        'connect_timeout' => 3,
        // 更多配置
    ],
    // 不需要routingkey，默认与队列名相同
    'queue_name' => [
        'base' => [
            'host' => 'host',
            'port' => 'port',
            'login' => 'username',
            'password' => 'password',
            'vhost' => 'vhost'
        ],
    ],
    // 更多队列
]);