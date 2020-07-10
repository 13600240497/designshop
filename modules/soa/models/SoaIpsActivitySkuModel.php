<?php
namespace app\modules\soa\models;

use app\models\ActiveRecord;
use app\base\SitePlatform;

/**
 * SoaIpsGoodsModel 模型
 *
 * @property int    $id
 * @property int    $ips_activity_id
 * @property int    $sku_update_time
 * @property string $sku_list
 * @property int    $create_time
 * @property int    $update_time
 */
class SoaIpsActivitySkuModel extends ActiveRecord
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
            [['ips_activity_id', 'sku_update_time'], 'required']
        ];
    }

    /**
     * 根据选品子活动ID查询记录
     *
     * @param int $activityId 选品子活动ID
     * @return static
     */
    public static function getByIpsActivityId($activityId)
    {
        return static::find()->where(['ips_activity_id' => $activityId])->one();
    }
}