<?php

namespace app\modules\gbad\controllers;

/**
 * 商品管理
 *
 * @property \app\modules\gbad\components\GoodsComponent $GoodsComponent
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
        if (true != $diffSku && !empty($diffSku)) {
            return app()->helper->arrayResult(1, "SKU {$diffSku} 不存在", $diffSku);
        }
        
        return app()->helper->arrayResult(0, 'success', []);
    }
}
