<?php
namespace app\modules\component\models;


/**
 * 布局组件模型
 * @property int $id
 * @property string $category_id
 * @property int $is_custom
 * @property string $component_key
 * @property string $name
 * @property string $description
 * @property string $logo_url
 * @property int $status
 * @property int $place
 * @property int $verify_status
 * @property int $is_delete
 * @property string $create_user
 * @property string $siteGroups
 */
class LayoutModel extends ComponentModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'layout_component';
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
                    'is_custom'
                ],
                'required',
            ],

        ];
    }

}
