<?php

namespace app\components;

use Globalegrow\Gateway\Client;
use yii\base\Behavior;

class MyBehavior extends Behavior
{

    //让行为响应组件的事件触发
//    public function events()
//    {
//
//    }

    // 网关校验
    public function gatewayVerify()
    {
        $params = app()->request->post();
        $sign = !empty($params) ? array_pop($params) : '';
        $cilent = new Client('', app()->params['gateway']['app_key'], app()->params['gateway']['sign']);

        return $sign === $cilent->getRequestSign($params);
    }
}
