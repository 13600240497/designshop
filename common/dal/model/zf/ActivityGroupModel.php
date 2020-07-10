<?php
namespace app\common\dal\model\zf;

/**
 * ActivityGroup模型
 *
 * @property int    $id
 * @property string $platform_list
 * @property string $lang_list
 * @property string $support_list
 */
class ActivityGroupModel extends AbstractZaFulModel
{
    /** @var int 没有关联端口(单一端口)时默认分组ID */
    const NO_RELATED_GROUP_ID = 0;

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
            [['platform_list', 'lang_list', 'support_list'], 'required']
        ];
    }

    /**
     * 根据分组ID获取多个活动组信息
     * @param array $groupIds 分组ID数组
     * @return array
     */
    public static function getActivityGroupByGroupIds($groupIds)
    {
        return static::find()->where(['id' => $groupIds])->all();
    }
}