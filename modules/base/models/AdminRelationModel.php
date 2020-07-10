<?php

namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * AdminRelation模型
 *
 * @property int $id
 * @property int $admin_id
 * @property int $role_id
 * @property string $site_code
 * @property int $create_time
 */
class AdminRelationModel extends ActiveRecord
{
    const ROLES = 'roles';
    const SITE_CODE = 'site_code';
    const ROLE_ID = 'role_id';

    //用户菜单权限在redis中缓存的key前缀
    const REDIS_USER_MENU_KEY_PRE = 'geshop::user::menu::';

    //用户菜单权限缓存时间，10分钟
    const REDIS_USER_MENU_TIMEOUT = 600;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'admin_id';
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        return [
            'id',
            'admin_id',
            'role_id',
            'site_code',
            'create_time',
            //其他表字段
            'department_id',
            'username',
            'realname',
            'is_leader',
            'is_super',
            'user_no',
            'status',
        ];
    }

    /**
     * 更新用户的站点、角色关系
     * @param int $adminId 用户ID
     * @param array $sites 站点信息数组
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function updateAdminRelation($adminId, $sites)
    {
        $data = [];
        $time = time();
        if (!empty($sites)) {
            foreach ($sites as $val) {
                if (!empty($val[static::SITE_CODE]) && !empty($val[static::ROLES])) {
                    /** @var array[] $val */
                    foreach ($val[static::ROLES] as $roleId) {
                        $data[] = [
                            $adminId,
                            $roleId,
                            $val[static::SITE_CODE],
                            $time
                        ];
                    }
                }
            }
        }

        $columns = ['admin_id', static::ROLE_ID, static::SITE_CODE, 'create_time'];

        return !($data && !self::insertAll($columns, $data));
    }

    /**
     * 获取用户在Redis中存储信息的key
     * @param int $userId 用户ID
     * @param string $siteCode 站点code
     * @return string
     */
    public static function getUserMenuRedisKey($userId, $siteCode)
    {
        return static::REDIS_USER_MENU_KEY_PRE . $userId . '_' . $siteCode;
    }

    /**
     * 获取用户在某站点下的菜单ID合集
     * @param $userId
     * @param $siteCode
     * @return array|bool
     */
    public static function getUserRolesPrivileges($userId, $siteCode)
    {
        $key = static::getUserMenuRedisKey($userId, $siteCode);
        $data = app()->redis->get($key);
        if (empty($data)) {
            $list = static::find()->alias('ar')->select('rp.privilege_id')
                ->leftJoin(
                    RoleModel::tableName() . ' as r',
                    'ar.role_id = r.id AND r.status = ' . RoleModel::STATUS_ENABLED
                    . ' AND r.is_delete = ' . RoleModel::NOT_DELETE
                )->leftJoin(
                    RolePrivilegeModel::tableName() . ' as rp',
                    'r.id = rp.role_id'
                )->where([
                    'ar.site_code' => $siteCode,
                    'ar.admin_id' => $userId
                ])->asArray()
                ->groupBy('rp.privilege_id')
                ->column();
            if ($list) {
                //返回的数组中的值需要是int型的，而asArray查出来的都是string
                $data = array_map(function ($val) {
                    return (int)$val;
                }, $list);
                //这里将用户菜单权限存储redis中并保存10分钟，对应地方是在登录和变更用户权限的地方取消缓存，以便在这重新获取
                app()->redis->setex($key, static::REDIS_USER_MENU_TIMEOUT, json_encode($data));
            }
        } else {
            $data = json_decode($data, true);
        }

        return $data ?: [];
    }

    /**
     * 清除用户菜单权限在redis中的缓存信息
     * @param int $userId 用户ID
     * @param string $siteCode 站点code
     */
    public static function clearUserMenuRedisCache($userId, $siteCode)
    {
        //和上面的getUserRolesPrivileges方法是一对，一处增加一处清除
        $key = static::getUserMenuRedisKey($userId, $siteCode);
        app()->redis->del($key);
    }

    /**
     * 获取权限row
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(RoleModel::class, ['id' => static::ROLE_ID]);
    }
}
