<?php
namespace app\modules\component\controllers;

/**
 * component 组件同步控制器
 * @property \app\modules\component\components\SyncComponent $SyncComponent
 */
class SyncController extends Controller
{
    /**
     * 默认组件列表主页
     * @throws \ego\base\JsonResponseException
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        return $this->SyncComponent->sync();
    }
    
    /**
     * 同步读语言包
     *
     * @return array|mixed
     */
    public function actionLanguagePackage()
    {
        return $this->SyncComponent->syncLanguagePackage();
    }
}
