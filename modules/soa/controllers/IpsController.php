<?php

namespace app\modules\soa\controllers;

/**
 * 选品系统对接API接口
 *
 * @property \app\modules\soa\components\IpsComponent $IpsComponent
 *
 * @author TianHaisen
 * @since 1.5.0
 */
class IpsController extends Controller
{

    /**
     * IPS - 获取活动信息
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionActivityList()
    {
        return $this->IpsComponent->getActivityList(app()->request->get());
    }

    /**
     * IPS - 获取活动子活动分组信息
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionActivityGroupList()
    {
        return $this->IpsComponent->getActivityGroupList(app()->request->get());
    }

    /**
     * IPS - 获取子活动信息
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionActivityChildList()
    {
        return $this->IpsComponent->getActivityChildList(app()->request->get());
    }

    /**
     *  IPS - 获取子活动已选SKU
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionActivityProduct()
    {
        return $this->IpsComponent->getSingleActivityProductList(app()->request->get());
    }

    /**
     * IPS - 获取子活动已选SKU
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionGetActivityGoodsSku()
    {
        return $this->IpsComponent->getActivityGoodsSku(app()->request->get());
    }

    /**
     * 测试MQ同步业务逻辑
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionTest()
    {
        return $this->IpsComponent->testMqSync(app()->request->get());
    }
}