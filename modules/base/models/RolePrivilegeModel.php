<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 角色权限模型
 * Class RolePrivilegeModel
 * @package app\modules\base\models
 */
class RolePrivilegeModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'role_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'privilege_id'], 'required'],
            ['role_id', 'integer']
        ];
    }
}
