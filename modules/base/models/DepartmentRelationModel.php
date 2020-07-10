<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 部门站点关系模型
 * Class DepartmentRelationModel
 * @package app\modules\base\models
 */
class DepartmentRelationModel extends ActiveRecord
{
    /**
     * @file  : getSiteByDepartmentId
     * @brief : 根据部门id获取站点
     * @param $id
     * @return:  array|\yii\db\ActiveRecord[]
     */
    public static function getSiteByDepartmentId($id)
    {
        return self::find()
            ->select('site_code')
            ->where(['department_id' => $id])
            ->asArray()
            ->all();
    }
}
