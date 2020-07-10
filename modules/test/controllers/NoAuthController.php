<?php
namespace app\modules\test\controllers;

use app\modules\test\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * 没有登录权限的控制权,不需要登录可直接访问的页面
 */
class NoAuthController extends \ego\web\Controller
{
    use MagicPropertyTrait;
}
