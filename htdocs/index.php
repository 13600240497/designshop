<?php

//加载性能监控
if (filter_has_var(INPUT_SERVER, 'ENV')) {
    $dirName = dirname(__DIR__);
    $dirName = explode('/', $dirName);
    $dirName = array_slice($dirName, 1, 2);
    $dirName = implode('/', $dirName);
    if ('test' == filter_input(INPUT_SERVER, 'ENV')) {
        if (strstr(filter_input(INPUT_SERVER, 'HTTP_HOST'), 'tideways_xhprof')) {
            require('/' . $dirName . '/gegui.gw-ec.com/php7/master/external/header.php');
        }
    } else {
        //require('/' . $dirName . '/gegui/external/header.php');
    }
}

/**
 * 入口文件
 */
//ini_set('display_errors', 'On');
//error_reporting(E_ALL & ~E_NOTICE);
$config = require(__DIR__ . '/../bootstrap/bootstrap.php');

// 用于跟踪具体一次执行全部日志
if (isset($_GET['env_debug']) || isset($_COOKIE['env_debug'])) {
    if (isset($config['components']['log']['targets']['file'])) {
        $logConfig = &$config['components']['log']['targets']['file'];
        //$logConfig['categories'] = ['app\*', 'yii\db\*'];
        $logConfig['levels'] = ['trace', 'info', 'warning', 'error'];
        $logConfig['logFile'] = '@runtime/logs/debug.log';
    }
}

(new app\base\Application($config))->run();
