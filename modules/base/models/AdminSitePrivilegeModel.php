<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 管理员模型
 * Class AdminModel
 * @property int $user_id
 * @property string $website_code
 * @property string $home_permissions
 * @property string $special_permissions
 */
class AdminSitePrivilegeModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'user_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'website_code', 'home_permissions', 'special_permissions'], 'required'],
            ['user_id', 'integer'],
            [['website_code', 'home_permissions', 'special_permissions'], 'string'],
        ];
    }

    /**
     * 获取用户站点数据权限
     *
     * @param int $userId 管理用户ID
     * @param string $websiteCode 站点简码，如： zf-pc/rg-wap
     * @return AdminSitePrivilegeModel
     */
    public static function findByUserIdAndWebsiteCode($userId, $websiteCode)
    {
        return static::find()->where(['user_id' => $userId, 'website_code' => $websiteCode])->one();
    }

    /**
     * 获取多个用户站点数据权限
     * @param int $userIds 管理用户ID
     * @param string $websiteCode 站点简码，如： zf-pc/rg-wap
     * @return array
     */
    public static function findByUserIdsAndWebsiteCode($userIds, $websiteCode)
    {
        return static::find()->where(['user_id' => $userIds, 'website_code' => $websiteCode])->all();
    }
    
    /**
     * 获取用户的专题渠道权限
     *
     * @param $userId
     * @param $websiteCode
     *
     * @return array|mixed
     */
    public static function getUserSpecialPermissions($userId, $websiteCode)
    {
        $result = self::find()->select('special_permissions')
            ->where(['user_id' => $userId, 'website_code' => $websiteCode])
            ->asArray()
            ->one();
        
        return !empty($result) ? json_decode($result['special_permissions'], true) : [];
    }
}