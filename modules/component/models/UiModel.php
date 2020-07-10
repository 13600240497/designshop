<?php
namespace app\modules\component\models;


/**
 * UI组件模型
 * @property int $id
 * @property string $category_id
 * @property int $is_custom
 * @property string $component_key
 * @property string $relation_key
 * @property string $name
 * @property string $description
 * @property string $logo_url
 * @property int $status
 * @property int $range
 * @property int $place
 * @property int $verify_status
 * @property int $is_delete
 * @property string $create_user
 * @property string $need_navigate
 * @property int $tpl_id
 * @property string $siteGroups
 */
class UiModel extends ComponentModel
{
    /**
     * 是否删除|1是
     */
    const IS_DELETE = 1;

    /**
     * 是否删除|0否
     */
    const NOT_DELETE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ui_component';
    }

    /**
     * 将字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'place', 'siteGroups'
        ];
        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'category_id',
                    'name',
                    'description',
                    'logo_url',
                    'range',
                    'is_custom',
                    'need_navigate'
                ],
                'required',
            ],

        ];
    }

    /**
     * 根据组件唯一key标识获取组件默认模板ID
     *
     * @param string $componentKey 组件唯一key标识
     *
     * @return int
     */
    public static function getDefaultTplIdByKey(string $componentKey)
    {
        /** @var UiModel $ui */
        $ui = self::find()->select('tpl_id')->where(['component_key' => $componentKey, 'is_delete'=>0])->one();

        return $ui ? (int)$ui->tpl_id : 0;
    }
    
    public static function getComponentName(string $componentKey)
    {
        $result = self::find()->select('name')->where(['component_key' => $componentKey, 'is_delete'=>0])->one();
        
        return !empty($result->name) ? $result->name : '';
    }
}
