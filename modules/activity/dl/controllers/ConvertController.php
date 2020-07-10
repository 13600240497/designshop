<?php

namespace app\modules\activity\dl\controllers;

/**
 * M版一键转APP
 *
 * @property \app\modules\activity\dl\components\ConvertAppComponent $ConvertAppComponent
 * @property \app\modules\activity\dl\components\ConvertWapComponent $ConvertWapComponent
 */
class ConvertController extends Controller
{
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
     * 一键Wap转APP
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionWebConvertApp()
    {
        return $this->ConvertAppComponent->webConvertApp(app()->request->post());
    }
}
