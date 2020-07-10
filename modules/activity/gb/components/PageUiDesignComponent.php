<?php

namespace app\modules\activity\gb\components;

use app\modules\common\gb\components\CommonPageUiDesignComponent;

/**
 * 页面装修设计-UI组件部分
 *
 */
class PageUiDesignComponent extends CommonPageUiDesignComponent
{

    /**
     * 覆盖父类的方式，增加商品标题组合组件检查
     * @param array $data
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function addUiComponent($data)
    {
        if (isset($data['lang'], $data[static::FIELD_LAYOUT_ID], $data[static::FIELD_POSITION],
            $data['component_key'])) {
            $goodsManageComponent = new GoodsManageComponent();
            $siteCode = $this->pageInfo->site_code;
            $goodsManageComponent->checkGoodsTitleCompositeUiComponentWhenUiAdd(
                $siteCode,
                $this->pageInfo->id,
                $data['lang'],
                $data[ static::FIELD_LAYOUT_ID ],
                $data[ static::FIELD_POSITION ],
                $data['component_key']
            );
        }

        return parent::addUiComponent($data);
    }

    /**
     * 覆盖父类的方式，增加商品标题组合组件检查
     * @param array $data
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \ego\base\JsonResponseException
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function copyUiComponent($data)
    {
        if (isset($data['copy_id'])) {
            $goodsManageComponent = new GoodsManageComponent();
            $siteCode = $this->pageInfo->site_code;
            $goodsManageComponent->checkGoodsTitleCompositeUiComponentWhenUiCopy($siteCode, $data['copy_id']);
        }

        return parent::copyUiComponent($data);
    }

}
