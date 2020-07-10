<?php

namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 部门模型
 * Class DepartmentModel
 * @package app\modules\base\models
 */
class DepartmentModel extends ActiveRecord
{
    const DEPARTMENT_NODE = 'department_node';

    /**
     * 部门节点 缓存
     *
     * @return array $data
     */
    public static function departmentNodeCache()
    {
        $data = static::getCache()->redis->get(static::getCacheKey(static::DEPARTMENT_NODE));
        if ($data) {
            return json_decode($data, true);
        }

        $departments = self::find()
            ->select('id,parent_id,name')
            ->asArray()
            ->all();
        foreach ($departments as & $department) {
            $department['id'] = (int)$department['id'];
            $department['parent_id'] = (int)$department['parent_id'];
        }
        $data = array_combine(array_column($departments, 'id'), $departments);
        $data = app()->arrayTree->addNode($data);
        static::getCache()->redis->setex(static::getCacheKey(static::DEPARTMENT_NODE), 600, json_encode($data));

        return $data;
    }
}
