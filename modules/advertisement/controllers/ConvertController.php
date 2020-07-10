<?php

namespace app\modules\advertisement\controllers;

/**
 * M版一键转APP
 *
 * @property \app\modules\advertisement\components\ConvertAppComponent $ConvertAppComponent
 * @property \app\modules\advertisement\components\ConvertWapComponent $ConvertWapComponent
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
        return $this->ConvertWapComponent->getCreatorWapLists();
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
        return $this->ConvertAppComponent->wapConvertApp(app()->request->post());
    }
}
