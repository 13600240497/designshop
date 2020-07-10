<?php

namespace app\modules\common\dl\models;

/**
 * ActivityGroup模型
 *
 * @property int    $id
 * @property int    $activity_group_id
 * @property int    $page_group_id
 * @property int    $platform_type
 * @property int    $page_id
 */
class PageGroupModel extends AbstractBaseModel
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
    public function rules()
    {
        return [
            [['page_group_id', 'platform_type', 'platform_type', 'page_id'], 'required'],
            [['activity_group_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * 生成子页面分组ID
     * @return string
     */
    public static function generatePageGroupId()
    {
        return md5(microtime() . random_int(0, 100));
    }

    /**
     * 查询子页面分组下所有子页面
     * @param int $groupId
     * @return array
     */
    public static function getPageListByPageGroupId($groupId)
    {
        return static::find()->where(['page_group_id' => $groupId])->all();
    }

    /**
     * 获取子页面分组ID
     * @param int $pageId
     * @return int
     */
    public static function getPageGroupIdByPageId($pageId)
    {
        $model = static::find()->select('page_group_id')->where(['page_id' => $pageId])->asArray()->one();
        return empty($model) ? 0 : $model['page_group_id'];
    }
}