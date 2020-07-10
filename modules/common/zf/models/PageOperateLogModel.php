<?php

namespace app\modules\common\zf\models;

use app\base\User;
use app\models\ActiveRecord;
use app\modules\base\models\AdminModel;

/**
 * PageSyncPlatformWaitDataModel模型
 *
 * @property int    $id
 * @property string $page_group_id
 * @property int $type
 * @property string $content
 * @property string $create_user
 * @property string $create_time
 * @property string $update_user
 * @property string $update_time
 */
class PageOperateLogModel extends AbstractBaseModel
{
    const TYPE_SAVE = 1;
    const TYPE_BIND = 2;
    const TYPE_SYNC = 3;

    static $operateType = [
        self::TYPE_SAVE => "保存",
        self::TYPE_BIND => "绑定",
        self::TYPE_SYNC => "同步",
    ];

    const TYPE_ADD = 1;
    const TYPE_DEL = 2;

    static $user_type = [
        self::TYPE_ADD => "增加",
        self::TYPE_DEL => "删除",
    ];

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zf_page_operate_log';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_group_id', 'content','type'], 'required'],
        ];
    }
    
    public function getUser(){
        return $this->hasOne(AdminModel::className(),['username'=>'create_user'])->select(['username','realname']);
    }
}
