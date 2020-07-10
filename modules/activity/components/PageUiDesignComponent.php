<?php

namespace app\modules\activity\components;

use app\modules\common\components\CommonPageUiDesignComponent;

/**
 * 页面装修设计-UI组件部分
 *
 */
class PageUiDesignComponent extends CommonPageUiDesignComponent
{

    /**
     * 覆盖父类的方式
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
        return parent::addUiComponent($data);
    }

    /**
     * 覆盖父类的方式
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
        return parent::copyUiComponent($data);
    }

}
