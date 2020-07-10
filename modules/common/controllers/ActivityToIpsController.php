<?php
/**
 * 活动同步ips控制器
 *
 * User: yuanwenguang
 * Date: 2019/4/17
 * Time: 10:48
 */
namespace app\modules\common\controllers;
use app\modules\common\components\CommonActivityComponent;
use app\modules\common\components\CommonIpsComponent;

class ActivityToIpsController extends Controller{
    /**
     * 创建活动
     * @author yuanwenguang 2019/4/17 10:53
     */
    public function actionCreateActivityToIps(){
        return CommonActivityComponent::createActivityToIps(app()->request->post());
    }

    public function actionGetIpsPageAuth(){
        return CommonIpsComponent::getIpsPageAuth(app()->request->get());
    }

}