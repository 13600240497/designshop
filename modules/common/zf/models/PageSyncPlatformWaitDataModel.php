<?php

namespace app\modules\common\zf\models;

use app\models\ActiveRecord;

/**
 * PageSyncPlatformWaitDataModel模型
 *
 * @property int    $id
 * @property string $page_group_id
 * @property string $pipeline
 * @property string $platform
 * @property int    $is_cover
 * @property string $form_data
 * @property string $create_user
 * @property string $create_time
 * @property string $update_user
 * @property string $update_time
 */
class PageSyncPlatformWaitDataModel extends AbstractBaseModel
{
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
        return 'zf_page_sync_platform_wait_data';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_group_id', 'pipeline', 'platform', 'is_cover', 'form_data'], 'required'],
            [['is_cover'], 'integer']
        ];
    }
    
    /**
     * 取三段同步组件的绑定关系
     *
     * @param string $groupId
     *
     * @return array|mixed
     */
    public static function getUiComponentSyncRelation(string $groupId)
    {
        $result = self::find()->select('form_data')
            ->where(['page_group_id' => $groupId])
            ->asArray()
            ->one();
     
        return !empty($result['form_data']) ? json_decode($result['form_data'], true) : [];
    }
}
