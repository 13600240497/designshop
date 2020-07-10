<?php

namespace app\modules\home\dl\controllers;

/**
 * M版一键转APP
 *
 * @property \app\modules\home\dl\components\ConvertAppComponent $ConvertAppComponent
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
     * M版一键转APP
     */
    public function actionWapConvertApp()
    {
        return $this->ConvertAppComponent->wapConvertApp(app()->request->post());
    }
}
