<?php
namespace app\modules\common\dl\models;

/**
 * GoodsManagePageModel 模型
 *
 * @property int    $id
 * @property string $group_id
 * @property string $site_code
 * @property int    $activity_id
 * @property int    $page_id
 */
class GoodsManagePageModel extends AbstractBaseModel
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
            [['group_id', 'site_code', 'activity_id', 'page_id'], 'required']
        ];
    }

    /**
     * 获取分组下所有端的活动页面
     * @param string $groupId 商品管理组ID
     * @return array|null
     */
    public static function getGoodsManagePageByGroupId($groupId)
    {
        return static::find()->where(['group_id' => $groupId])->all();
    }
}
