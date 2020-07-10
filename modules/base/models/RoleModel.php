<?php

namespace app\modules\base\models;

use yii\rbac\CheckAccessInterface;
use app\models\ActiveRecord;
use ego\base\JsonResponseException;
use app\modules\base\components\MenuComponent;

/**
 * 角色模型
 * @property int $id
 * @property string $name
 * @property int $department_id
 * @property string $site_code
 * @property int $status
 * @property int $is_delete
 * @property int $parent_id
 * @property int $need_auto_create
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class RoleModel extends ActiveRecord implements CheckAccessInterface
{
    const DEPARTMENT_ID = 'department_id';

    //用户信息在redis中缓存的key前缀
    const REDIS_USER_KEY_PRE = 'geshop::userinfo::';

    //用户信息缓存时间，10分钟
    const REDIS_USER_TIMEOUT = 600;

    //状态可用
    const STATUS_ENABLED = 1;

    //状态不可用
    const STATUS_DISABLED = 0;

    //是超级管理员
    const IS_SUPER = 1;

    //是否删除|0-未删除
    const NOT_DELETE = 0;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'name';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', static::DEPARTMENT_ID, 'status'], 'required'],
            ['site_code', 'string'],
            [['need_auto_create', 'parent_id'], 'integer'],
            ['name', 'validateName'],
        ];
    }

    /**
     * 验证name，重名规则：同一个站点下，同一个人创建的角色不能重名
     * @return bool
     */
    public function validateName()
    {
        if (mb_strlen($this->name, 'UTF-8') > 50) {
            $this->addError('name', '角色名称长度不能超过50个字符');
            return false;
        }

        /** @var static $role */
        $role = self::find()
            ->select('id')
            ->where([
                'site_code' => $this->site_code,
                'create_user' => app()->user->username,
                'is_delete' => self::NOT_DELETE,
                'name' => $this->name,
            ])->one();
        if (!$role || (int)$this->id === (int)$role->id) {
            return true;
        }

        $this->addError('name', '角色名已被您在“' . $this->site_code . '”站点下使用，请修改角色名');
        return false;
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['privileges_num', 'user_num']);
    }

    /**
     *
     * @param int|string $userId
     * @param string $route
     * @param array $params
     *
     * @return bool
     * @throws JsonResponseException
     */
    public function checkAccess($userId, $route, $params = [])
    {
        $route = app()->controller->module->id . '/' . $route;
        if (false !== strpos($route, 'public')
            || \in_array('*', app()->params['rbac']['exclude'], true)
            || \in_array($route, app()->params['rbac']['exclude'], true)
        ) {
            return true;
        }

        $admin = self::checkAdmin($userId);
        if (\is_bool($admin)) {
            return $admin;
        }

        $menu = self::checkMenu($route);
        if (\is_bool($menu)) {
            return $menu;
        }

        //三端合一,默认使用PC站点
        $defaultSiteCode = MenuComponent::getCurrentDefaultSiteCode(app()->user->admin->is_super);
        if (empty($defaultSiteCode)) {
            throw new JsonResponseException(1, '找不到默认站点');
        }

        //管理员角色
        $menuIds = AdminRelationModel::getUserRolesPrivileges($userId, $defaultSiteCode);
        if (!empty($menuIds)) {
            return \in_array($menu->id, $menuIds, true);
        }

        return false;
    }

    /**
     * 获取用户在Redis中存储信息的key
     * @param $userId
     * @return string
     */
    public static function getUserRedisKey($userId)
    {
        return static::REDIS_USER_KEY_PRE . $userId;
    }

    /**
     * 校验用户角色权限
     * @param int $userId
     * @return bool|array
     */
    private static function checkAdmin($userId)
    {
        $key = static::getUserRedisKey($userId);
        $data = app()->redis->get($key);
        if (empty($data)) {
            $admin = AdminModel::findOne($userId);
            if ($admin) {
                $admin = $admin->toArray();
                //这里将用户信息存储redis中并保存10分钟，对应地方是在登录的时候取消缓存，以便在这重新获取
                app()->redis->setex($key, static::REDIS_USER_TIMEOUT, json_encode($admin));
            }
        } else {
            $admin = json_decode($data, true);
        }
        if (empty($admin) || static::STATUS_DISABLED === $admin['status']) {
            return false;
        }
        if (static::IS_SUPER === $admin['is_super']) {
            return true;
        }

        return $admin;
    }

    /**
     * 清除用户在redis中的缓存信息
     * @param int $userId
     */
    public static function clearUserInfoRedisCache($userId)
    {
        //和上面的checkAdmin方法是一对，一处增加一处清除
        $key = static::getUserRedisKey($userId);
        app()->redis->del($key);
    }

    /**
     * 检查用户菜单权限
     * @param string $route 路由
     * @return bool|\app\modules\base\models\MenuModel
     */
    private static function checkMenu($route)
    {
        $menu = MenuModel::getByRoute($route);
        if (!$menu || $menu->is_public) {
            //未定义的菜单或操作默认都有权限
            return true;
        }

        if (!$menu->status) {
            return false;
        }

        return $menu;
    }

    /**
     * 获取部门角色信息
     * @param int $departmentId
     * @return array
     */
    public static function getRoleDateByDp($departmentId)
    {
        return self::find()
            ->where([static::DEPARTMENT_ID => $departmentId])
            ->asArray()
            ->all();
    }
}
