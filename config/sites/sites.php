<?php
/**
 * 站点开发环境配置
 */

function mergesiteconfig($siteconfig)
{
    $siteArray = [];
    foreach ($siteconfig as $element) {
        $file = __DIR__ . '/' . $element  .  '/' . $element . '.' . YII_ENV . '.php';
        $siteArray = yii\helpers\ArrayHelper::merge($siteArray, require($file));
    }
    return $siteArray;
}

return mergesiteconfig(require('moudle.php'));

