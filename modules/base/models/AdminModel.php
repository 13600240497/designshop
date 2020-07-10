<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 管理员模型
 * Class AdminModel
 * @property int $id
 * @property int $department_id
 * @property string $username
 * @property string $realname
 * @property int $is_leader
 * @property int $is_super
 * @property string $user_no
 * @property int $status
 * @property int $last_login_time
 * @property int $last_login_ip
 * @property int $logins
 * @property int $create_time
 * @package app\modules\base\models
 */
class AdminModel extends ActiveRecord
{
    /**
     * 用户状态|0-未启用
     */
    const STATUS_DISABLED = 0;

    /**
     * 用户状态|1-启用
     */
    const STATUS_ENABLED = 1;

    /**
     * 用户状态|2-UC锁定
     */
    const STATUS_UCLOCKED = 2;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'username';
    }

    /**
     * 根据用户名获取管理员
     * @param string $userName 用户名
     * @return AdminModel|null
     */
    public static function getByUserName($userName)
    {
        return static::findOne([
            'username' => $userName
        ]);
    }
    
    /**
     * 获取真实姓名
     *
     * @param $userName
     *
     * @return mixed|string
     */
    public static function getRealNameByUserName($userName)
    {
        $result = static::find()->select('realname')
            ->where(['username' => $userName])
            ->asArray()
            ->one();
        
        return !empty($result['realname']) ? $result['realname'] : '';
    }
    
    /**
     * 根据用户名批量获取管理员
     * @param array $userNames 用户名数组
     * @return array
     */
    public static function getByUserNames($userNames)
    {
        $list = static::find()->where([
            'username' => $userNames
        ])->asArray()->all();

        return $list ?: [];
    }

    /**
     * 根据用户ID获取用户名
     * @param int $userId 用户ID
     * @return string
     */
    public static function getUserName($userId)
    {
        $use = static::findOne($userId);
        if (!$use) {
            return '';
        }

        return $use->realname ?: $use->username;
    }
}
