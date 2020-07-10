<?php
namespace app\modules\common\dl\models;

use app\models\ActiveRecord;

/**
 * DressLily 站点Model基础类
 * common 模块
 */
class AbstractBaseModel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::getDLTableName(parent::tableName());
    }

    /**
     * 获取 DressLily 站点表名称
     * @param string $tableName
     * @return string
     */
    protected static function getDLTableName($tableName)
    {
        return 'dl_' . $tableName;
    }
}
