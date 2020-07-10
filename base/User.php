<?php
namespace app\base;

use app\modules\base\models\AdminModel;
use app\modules\base\models\AdminRelationModel;
use app\modules\base\models\RoleModel;

/**
 * 登录用户
 *
 * @property AdminModel|null $admin
 * @property string|null $username
 * @property string|null $realname
 */
class User extends \yii\web\User
{
    const USER_PRE = 'User::login';
    /**
     * 根据管理员对象或者id登录
     *
     * @param AdminModel|int $admin 管理员对象或者id
     */
    public function loginByAdmin($admin)
    {
        app()->redis->hset(self::USER_PRE, $admin->id, $admin->realname);
        $this->login(
            new Identity(is_numeric($admin) ? AdminModel::getById($admin) : $admin)
        );
    }

    /**
     * 获取登录管理员指定信息
     * @param string $field 获取的字段名
     * @return string|null
     * @throws \Throwable
     */
    public function get($field)
    {
        return $this->getAdmin() ? $this->getAdmin()[$field] : null;
    }

    /**
     * 获取登录管理员信息
     * @return AdminModel|null
     * @throws \Throwable
     */
    protected function getAdmin()
    {
        if ($this->getIdentity() && $this->getIdentity()->admin) {
            return $this->getIdentity()->admin;
        }

        return null;
    }

    /**
     * 获取管理员用户名
     * @return null|string
     * @throws \Throwable
     */
    protected function getUsername()
    {
        $admin = $this->getAdmin();
        if (!$admin) {
            return null;
        }

        return  $admin->username;
    }

    /**
     * 获取管理员真实姓名
     * @return null|string
     * @throws \Throwable
     */
    protected function getRealname()
    {
        $admin = $this->getAdmin();
        if (!$admin) {
            return null;
        }

        return  $admin->realname ?: $admin->username;
    }

    /**
     * 清理用户信息在redis中的缓存
     * @param int $userId 用户ID
     * @param array $siteCodes 站点code数组
     */
    public function clearUserRedisCache($userId, $siteCodes)
    {
        RoleModel::clearUserInfoRedisCache($userId);
        if (!empty($siteCodes)) {
            foreach ($siteCodes as $siteCode) {
                AdminRelationModel::clearUserMenuRedisCache($userId, $siteCode);
            }
        }
    }

    /**
     * 显示当前在线的人员
     * @return mixed
     */
    public function showUser()
    {
        return app()->redis->hgetall(self::USER_PRE);
    }

    /**
     * 用户退出
     * @param bool $destroySession
     * @return bool|void
     */
    public function logout($destroySession = true)
    {
        app()->redis->hdel(self::USER_PRE, $this->id);
        parent::logout($destroySession);
    }
}
