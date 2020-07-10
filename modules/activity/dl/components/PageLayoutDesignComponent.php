<?php

namespace app\modules\activity\dl\components;

use app\modules\common\dl\components\CommonPageLayoutDesignComponent;
use app\modules\activity\dl\components\GoodsManageComponent;

/**
 * 页面装修设计-Layout组件部分
 *
 */
class PageLayoutDesignComponent extends CommonPageLayoutDesignComponent
{

    /**
     * 覆盖父类方法，检查商品标题组合组件(DL站点没有商品标题组合组件)
     * @param array $data
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function copyLayoutComponent($data)
    {
        return parent::copyLayoutComponent($data);
    }
}
