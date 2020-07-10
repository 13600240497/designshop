<?php

namespace app\modules\activity\zf\controllers;

/**
 * 三端同步组件数据
 *
 * Class SyncPlatformController
 * @property \app\modules\activity\zf\components\SyncPlatformComponent $SyncPlatformComponent
 * @package app\modules\activity\zf\controllers
 */
class SyncPlatformController extends Controller
{
    
    /**
     * beforeAction
     * @param \yii\base\Action $action
     * @return bool
     * @throws \ego\base\JsonResponseException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        $pageId = isset($_REQUEST['page_id']) ? (int)$_REQUEST['page_id'] : 0;
        if (true !== ($auth = $this->SyncPlatformComponent->checkAuth($pageId, $this->getRoute()))) {
            return $auth;
        }
        
        return true;
    }
    
    /**
     * 获取三端同步渠道列表数据
     *
     * @param $page_id
     * @param $site_code
     *
     * @return array
     */
    public function actionPlatformOptions($page_id, $site_code)
    {
        return $this->SyncPlatformComponent->getSyncPlatformOptions($page_id, $site_code);
    }
    
    /**
     * 获取三端同步组件模板列表
     *
     * @param $page_id
     * @param $platform
     * @param $pipeline
     *
     * @return array
     */
    public function actionComponentSelect($page_id, $platform, $pipeline)
    {
        return $this->SyncPlatformComponent->getSyncComponentSelect($page_id, $platform, $pipeline);
    }

    /**
     * 保存三端同步表单接口
     * @return array
     * @throws \ego\base\JsonResponseException
     * @author yuanwenguang 2019/3/25 16:53
     */
    public function actionBatchSaveFrom()
    {
        return $this->SyncPlatformComponent->batchSaveFrom(app()->request->post());
    }

    /**
     * 获取三端同步表单接口
     *
     * @return mixed
     * @author yuanwenguang 2019/3/22 13:42
     */
    public function actionGetBatchFromData()
    {
        return $this->SyncPlatformComponent->getBatchFromData(app()->request->post());
    }
    
    /**
     * 绑定三端组件数据
     *
     * @return array
     */
    public function actionBatchBind()
    {
        return $this->SyncPlatformComponent->batchBind(app()->request->post());
    }
    
    /**
     * 删除三端绑定组件
     *
     * @return array
     */
    public function actionDeleteBind()
    {
        return $this->SyncPlatformComponent->deleteBind(app()->request->post());
    }

    /**
     * 校验组件是否存在
     * @return array
     * @throws \ego\base\JsonResponseException
     * @author yuanwenguang 2019/4/1 13:56
     */
    public function actionCheckGoodsExists(){
        return $this->SyncPlatformComponent->checkGoodsExists(app()->request->post());
    }


}