<?php

namespace app\modules\activity\gb\controllers;

/**
 * 商品管理
 *
 * Class GoodsController
 *
 * @property \app\modules\activity\gb\components\GoodsComponent $GoodsComponent
 * @package app\modules\activity\gb\controllers
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
        if (true === $this->GoodsComponent->checkGoodsExists(app()->request->get())) {
            return app()->helper->arrayResult(0, []);
        }
    }
}
