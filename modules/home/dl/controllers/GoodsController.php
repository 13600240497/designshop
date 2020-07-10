<?php

namespace app\modules\home\dl\controllers;

/**
 * 商品管理
 *
 * Class GoodsController
 *
 * @property \app\modules\home\dl\components\GoodsComponent $GoodsComponent
 * @package app\modules\home\controllers
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
        $params = app()->request->get();
        if (!empty($params['api'])) {
            $diffSku = $this->GoodsComponent->checkGoodsExitsWithAPI($params);
        } else {
            $diffSku = $this->GoodsComponent->checkGoodsExists($params);
        }

        if (!empty($diffSku)) {
            return app()->helper->arrayResult(1, "SKU {$diffSku} 不存在", $diffSku);
        }

        return app()->helper->arrayResult(0, 'success', []);
    }
}
