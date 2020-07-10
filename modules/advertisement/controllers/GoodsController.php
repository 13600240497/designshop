<?php

namespace app\modules\advertisement\controllers;

/**
 * 商品管理
 *
 * Class GoodsController
 *
 * @property \app\modules\advertisement\components\GoodsComponent $GoodsComponent
 * @package app\modules\advertisement\controllers
 */
class GoodsController extends Controller
{
    /**
     * 模板组件商品管理列表
     *
     * @return array
     */
    public function actionTplGoodsList()
    {
        return $this->GoodsComponent->tplGoodsData(app()->request->get());
    }
    
    /**
     * 校验传入的sku是否存在
     *
     * @return array
     */
    public function actionTplGoodsExists()
    {
        $diffSku = $this->GoodsComponent->checkGoodsExists(app()->request->get());
        if (!empty($diffSku)) {
            return app()->helper->arrayResult(1, "SKU {$diffSku} 不存在", $diffSku);
        }
        
        return app()->helper->arrayResult(0, 'success', []);
    }
}
