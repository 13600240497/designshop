<?php

namespace app\modules\activity\gb\controllers;

/**
 * M版一键转APP
 *
 * @property \app\modules\activity\gb\components\ConvertAppComponent $ConvertAppComponent
 * @property \app\modules\activity\gb\components\ConvertWapComponent $ConvertWapComponent
 */
class ConvertController extends Controller
{
    /**
     * 获取当前站点下当前用户创建的所有APP活动
     *
     * @return array
     */
    public function actionCreatorWapLists()
    {
        return $this->ConvertWapComponent->getCreatorWapLists(app()->request->get('platform',''));
    }
    
    /**
     * 获取当前站点下当前用户创建的所有APP活动
     *
     * @return array
     */
    public function actionCreatorAppLists()
    {
        return $this->ConvertAppComponent->getCreatorAppLists();
    }
    
    /**
     * 一键PC转Wap
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionPcConvertWap()
    {
        return $this->ConvertWapComponent->pcConvertWap(app()->request->post());
    }
    
    /**
     * 一键Wap转APP
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionWapConvertApp()
    {
        return $this->ConvertAppComponent->convertApp(app()->request->post());
    }
    
    /**
     * 安卓和IOS互转
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionAndroidConvertIos()
    {
        return $this->ConvertAppComponent->convertApp(app()->request->post(), 'app');
    }
}
