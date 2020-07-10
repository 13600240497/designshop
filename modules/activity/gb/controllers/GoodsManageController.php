<?php

namespace app\modules\activity\gb\controllers;

use yii\web\Response;

/**
 * 页面组件商品管理
 *
 * Class GoodsManageController
 *
 * @package app\modules\activity\gb\controllers
 *
 * @property  \app\modules\activity\gb\components\GoodsManageComponent $GoodsManageComponent
 */
class GoodsManageController extends Controller
{
    
    /**
     * 商品管理列表首页
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /**
     * 商品管理活动页面列表
     *
     * @return array
     */
    public function actionList()
    {
        return $this->GoodsManageComponent->lists(app()->request->get());
    }
    
    /**
     * 查看页面商品sku
     *
     * @return array
     */
    public function actionSkuList()
    {
        return $this->GoodsManageComponent->skuList(app()->request->get());
    }
    
    /**
     * 商品管理活动组页面预览
     *
     * @return array
     */
    public function actionGroupPreview()
    {
        return $this->GoodsManageComponent->groupPreview(app()->request->get('groupId'));
    }
    
    /**
     * 编辑商品管理活动组
     *
     * @return array
     */
    public function actionGroup()
    {
        return $this->GoodsManageComponent->editGroup(app()->request->get('groupId'));
    }
    
    /**
     * 添加商品管理数据
     *
     * @return array
     */
    public function actionAdd()
    {
        return $this->GoodsManageComponent->add(app()->request->post('content'));
    }
    
    /**
     * 活动列表
     *
     * @return array
     */
    public function actionActivityList()
    {
        return $this->GoodsManageComponent->activityList(app()->request->get('groupId'));
    }
    
    /**
     * 单个活动下所有页面列表
     *
     * @return array
     */
    public function actionActivityPageList()
    {
        return $this->GoodsManageComponent->activityPageList(
            app()->request->get('activity_id')
        );
    }
    
    /**
     * 检查SKU列表是否存在
     *
     * @return array
     */
    public function actionCheckGoodsExists()
    {
        return $this->GoodsManageComponent->checkGoodsExists(app()->request->post());
    }
    
    /**
     * 保存商品管理数据并返回预览地址
     *
     * @return array
     */
    public function actionSaveAndPreview()
    {
        return $this->GoodsManageComponent->saveAndPreview(
            app()->request->post('groupId'),
            app()->request->post('content')
        );
    }
    
    /**
     * 活动页面配置在没有保存到数据库的情况下，动态预览
     *
     * @return string
     * @deprecated 没有使用，但不删除，以便以后使用
     */
    public function actionPreview()
    {
        app()->response->format = Response::FORMAT_HTML;
        $pageHtml = $this->GoodsManageComponent->preview(
            app()->request->get('pid'),
            app()->request->post()
        );
        
        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }
    
    /**
     * 同步数据块到UI组件
     *
     * @return array
     */
    public function actionSync()
    {
        return $this->GoodsManageComponent->syncDataBlockUiComponent(
            app()->request->post('groupId'),
            app()->request->post('pageId'),
            app()->request->post('lang')
        );
    }
    
    /**
     * 编辑商品数据
     *
     * @return array
     */
    public function actionEdit()
    {
        return $this->GoodsManageComponent->edit(
            app()->request->post('groupId'),
            app()->request->post('content')
        );
    }
    
    /**
     * 删除商品管理数据
     *
     * @return array
     */
    public function actionDelete()
    {
        $id = app()->request->get('id', 0);
        
        return $this->GoodsManageComponent->deleteGoodsManage($id);
    }
}
