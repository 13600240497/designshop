<?php
/**
 * xcmq配置
 *
 * 原始提供方：基础架构部->谢佐福
 *
 * 这里作了代码规范化
 */

$config = require VENDOR_PATH . '/globalegrow/rabbitmq/src/config/xcmq.php';
return array_map(
    function($item) {
        $item['base'] = Yii::getAlias('@runtime/logs');
        return $item;
    },
    $config
);