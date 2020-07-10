<?php

namespace app\modules\activity\gb\components;

use app\modules\common\gb\components\CommonPageLayoutDesignComponent;

/**
 * 页面装修设计-Layout组件部分
 *
 */
class PageLayoutDesignComponent extends CommonPageLayoutDesignComponent
{

    /**
     * 覆盖父类方法，检查商品标题组合组件
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
        if (isset($data['copy_id'], $data['lang'])) {
            $goodsManageComponent = new GoodsManageComponent();
            $siteCode = $this->pageInfo->site_code;
            $goodsManageComponent->checkGoodsTitleCompositeUiComponentWhenLayoutCopy(
                $siteCode,
                $data['lang'],
                $data['copy_id']
            );
        }

        return parent::copyLayoutComponent($data);
    }
}
