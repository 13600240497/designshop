<?php
/**
 * 引导文件
 */
header('Pragma: no-cache');
header('Cache-Control: no-cache, no-store');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');

if (version_compare(PHP_VERSION, '7.1.4', '<')) {
    exit('php7.1.4+ required');
} elseif (!defined('DOMAIN')) {
    // 域名定义
    $serverName = $_SERVER['HTTP_HOST'] ?? null;
    if (false !== strpos($serverName, ':')) {
        $serverName = explode(':', $serverName, 2)[0];
    }

    $serverArr = explode('.', $serverName, 2);
    // localhost            -> localhost
    // www.example.com      -> example.com
    // admin.example.com    -> example.com
    define('DOMAIN', $serverArr[1] ?? $serverName);
}

defined('FULL_DOMAIN') || define('FULL_DOMAIN', $serverName);
defined('YII_ENV') || define('YII_ENV', empty($_SERVER['ENV']) ? 'product' : $_SERVER['ENV']);
defined('YII_ENV_PROD') || define('YII_ENV_PROD', 0 === strpos(YII_ENV, 'product'));
defined('YII_DEBUG') || define('YII_DEBUG', 0 === strpos(YII_ENV, 'dev'));

defined('EGO_YII_BASE_PATH') || define('EGO_YII_BASE_PATH', dirname(__DIR__, 2)); // vendor/ego/yii-base
defined('VENDOR_PATH') || define('VENDOR_PATH', dirname(EGO_YII_BASE_PATH, 2)); // vendor
defined('APP_PATH') || define('APP_PATH', dirname(VENDOR_PATH));

require(VENDOR_PATH . '/autoload.php');
require(VENDOR_PATH . '/yiisoft/yii2/Yii.php');

// 配置
$config = yii\helpers\ArrayHelper::merge(
    ego\base\Application::loadConfig(__DIR__ . '/../config/web.php'),
    ego\base\Application::loadConfig(APP_PATH . '/config/web.php')
);

// 载入本地配置文件
if (is_file($file = APP_PATH . '/config/web.local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require($file));
} elseif (($developer = ego\base\Application::getDeveloper())
    && is_file($file = APP_PATH . '/config/web.' . $developer . '.php')
) {
    // @app/config/config.zhangsan.php
    // @app/config/config.lisi.php
    $config = yii\helpers\ArrayHelper::merge($config, require($file));
}

//GB站点语言配置
if (SITE_GROUP_CODE == 'gb' && is_file($file = APP_PATH . '/config/gb_web.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require($file));
}


return $config;

/**
 * `Yii::$app`别名
 *
 * @return app\base\Application
 */
function app()
{
    return Yii::$app;
}
