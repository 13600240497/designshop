<?php
define('SHOEDBSQL', $_GET['debug'] ?? 0 == 1);

/**
 * 引导文件
 */

if (!defined('SITE_GROUP_CODE')) {
    if (isset($_GET['site_group_code'])) {
        define('SITE_GROUP_CODE', strtolower($_GET['site_group_code']));
    }
    else {
        $site_group_code = !empty($_COOKIE['site_group_code']) ? $_COOKIE['site_group_code'] : '';
        if(!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'site_group_code') !== false){//来源信息
            preg_match('/site_group_code\=(.*)/is', $_SERVER['HTTP_REFERER'], $referer);
            $referer = explode('&' , $referer[1]);
            $site_group_code = array_shift($referer);
            unset($referer);
        }
        define('SITE_GROUP_CODE', strtolower($site_group_code ?? ''));
        unset($site_group_code);
    }
}

if (!defined('SITECODE')) {
    if (isset($_GET['SITECODE'])) {
        define('SITECODE', strtolower($_GET['SITECODE']));
    } else {
        define('SITECODE', strtolower($_COOKIE['SITECODE'] ?? ''));
    }
}

/**
 * print info
 */
function dump(...$msg)
{
    if (app()->env->isDev()) {
        // 调用所在文件和所在行数
        $calledBacktrace = app()->helper->getCalledBacktrace(1);
        $fileLine = $calledBacktrace['file'] . ' : ' . $calledBacktrace['line'];
        echo $fileLine, "\n", str_repeat('-', strlen($fileLine)), "\n";

        echo "<pre>";
        var_dump($msg);
        app()->end();
    }
}

defined('APOLLO_CONFIG_PATH') || define('APOLLO_CONFIG_PATH', '/data/www/apollo-config');
require(__DIR__ . '/../base/CommonFunction.php');
return require(__DIR__ . '/../vendor/ego/yii-base/src/bootstrap/bootstrap.php');
