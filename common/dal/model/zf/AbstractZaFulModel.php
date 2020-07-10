<?php
namespace app\common\dal\model\zf;

use app\common\dal\model\AbstractBaseModel;

abstract class AbstractZaFulModel extends AbstractBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zf_' . parent::tableName();
    }
}