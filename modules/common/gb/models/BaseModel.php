<?php
namespace app\modules\common\gb\models;

use app\models\ActiveRecord;

/**
 * common 模块
 */
class BaseModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'gb_' . parent::tableName();
    }
}
