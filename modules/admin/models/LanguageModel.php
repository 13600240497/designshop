<?php

namespace app\modules\admin\models;

use app\models\ActiveRecord;
use app\modules\common\models\ActivityModel;

/**
 * 多语言键表
 *
 * @property int $id
 * @property int $activity_id
 * @property bool $is_js
 * @property string $alias
 * @property string $key_name
 * @property string $remark
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class LanguageModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'alias';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alias', 'key_name'], 'required'],
            ['is_js', 'in', 'range' => [0, 1], 'message' => '{attribute}只能为0和1'],
            [['is_js', 'activity_id'], 'default', 'value' => 0],
            ['remark', 'default', 'value' => ''],
            ['alias', 'validateAlias'],
        ];
    }

    /**
     * 验证alias
     *
     * @return bool
     */
    public function validateAlias()
    {
        if (($item = static::findOne([
                'alias' => $this->alias,
                'activity_id' => $this->activity_id
            ]))
            && (!$this->id || (int)$item->id !== (int)$this->id)
        ) {
            $this->addError('alias', '键名alias&&activity_id已经存在');
            return false;
        }
        return true;
    }

    /**
     * 当前键对应的内容
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(LanguageTransModel::class, ['language_id' => 'id']);
    }

    /**
     * 当前键对应的内容
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(ActivityModel::class, ['id' => 'activity_id']);
    }
}
