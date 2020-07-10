<?php
namespace app\modules\component\models;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * 组件模板关联关系模型
 * @property int $id
 * @property int $tpl_id
 * @property int $relation_tpl_id
 * @property int $status
 * @property string $create_user
 * @property string $update_user
 * @property int $create_time
 * @property int $update_time
 */
class UiTplRelationModel extends ComponentModel
{
    /**
     * 是否启用|0否
     */
    const STATUS_NOT_USED = 0;

    /**
     * 是否启用|1是
     */
    const STATUS_USED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ui_component_tpl_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tpl_id', 'relation_tpl_id', 'status'], 'required'],
            [['tpl_id', 'relation_tpl_id'], 'integer'],
            ['status', 'in', 'range' => [0, 1], 'message' => 'status只能是0或1'],
            ['tpl_id', 'validateKey'],
        ];
    }

    /**
     * 将字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'tpl_name',
            'component_id',
            'component_key',
            'component_name',
            'relation_tpl_name',
            'relation_component_id',
            'relation_component_key',
            'relation_component_name',
            'relation_component_count',
        ];
        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (($item = static::findOne([
                'tpl_id' => $this->tpl_id
            ]))
            && (!$this->id || (int) $item->id !== (int) $this->id)
        ) {
            $this->addError('tpl_id', '同一模板只能和一个模板关联');

            return false;
        }

        return true;
    }

    /**
     * 获取组件关联的其它组件key
     *
     * @param array $componentKeys
     *
     * @return array
     */
    public static function getComponentRelationKey(array $componentKeys)
    {
        $relations = self::find()->alias('r')
            ->select('su.component_key, tu.component_key as relation_component_key')
            ->leftJoin(UiTplModel::tableName() . ' as st', 'st.id = r.tpl_id')
            ->leftJoin(UiModel::tableName() . ' as su', 'su.component_key = st.component_key')
            ->leftJoin(UiTplModel::tableName() . ' as tt', 'tt.id = r.relation_tpl_id')
            ->leftJoin(UiModel::tableName() . ' as tu', 'tu.component_key = tt.component_key')
            ->where([
                'st.is_delete' => UiTplModel::NOT_DELETE,
                'su.is_delete' => UiModel::NOT_DELETE,
                'tt.is_delete' => UiTplModel::NOT_DELETE,
                'tu.is_delete' => UiModel::NOT_DELETE,
                'r.status' => self::STATUS_USED,
                'su.component_key' => $componentKeys
            ])
            ->asArray()
            ->all();

        return $relations ? array_column($relations, 'relation_component_key', 'component_key') : [];
    }

    /**
     * 获取组件模板关联的其它组件模板ID
     *
     * @param array $tplIds
     *
     * @return array
     */
    public static function getTplRelationId(array $tplIds)
    {
        $relations = self::find()->alias('r')
            ->select('r.tpl_id, r.relation_tpl_id')
            ->leftJoin(UiTplModel::tableName() . ' as st', 'st.id = r.tpl_id')
            ->leftJoin(UiModel::tableName() . ' as su', 'su.component_key = st.component_key')
            ->leftJoin(UiTplModel::tableName() . ' as tt', 'tt.id = r.relation_tpl_id')
            ->leftJoin(UiModel::tableName() . ' as tu', 'tu.component_key = tt.component_key')
            ->where([
                'st.is_delete' => UiTplModel::NOT_DELETE,
                'su.is_delete' => UiModel::NOT_DELETE,
                'tt.is_delete' => UiTplModel::NOT_DELETE,
                'tu.is_delete' => UiModel::NOT_DELETE,
                'r.status' => self::STATUS_USED,
                'r.tpl_id' => $tplIds
            ])
            ->orderBy(new Expression("FIND_IN_SET(r.tpl_id, '" . implode(',', $tplIds) ."')"))
            ->asArray()
            ->all();

        return $relations ? array_column($relations, 'relation_tpl_id', 'tpl_id') : [];
    }

    /**
     * 获取M端组件模板关联的其它组件模板ID（M端和APP端是一一对应关系）
     *
     * @param array $tplIds
     *
     * @return array
     */
    public static function getWapTplRelationId(array $tplIds)
    {
        $data = [];
        if (!empty($tplIds)) {
            foreach ($tplIds as $tplId) {
                $data[$tplId] = $tplId;
            }
        }

        return $data;
    }

    /**
     * 查询组件关联的组件个数（一个组件能关联的组件个数最多只有1个，但其他的多个模板可以关联多个，也需满足多对一，不能一对多）
     *
     * @param string $componentKey
     * @param string $relationKey
     * @param int $id
     *
     * @return bool
     */
    public static function checkComponentRelationCount(string $componentKey, string $relationKey, int $id)
    {
        $relations = self::find()->alias('r')
            ->select('tu.component_key, group_concat(r.id) as id')
            ->leftJoin(UiTplModel::tableName() . ' as st', 'st.id = r.tpl_id')
            ->leftJoin(UiModel::tableName() . ' as su', 'su.component_key = st.component_key')
            ->leftJoin(UiTplModel::tableName() . ' as tt', 'tt.id = r.relation_tpl_id')
            ->leftJoin(UiModel::tableName() . ' as tu', 'tu.component_key = tt.component_key')
            ->where([
                'st.is_delete' => UiTplModel::NOT_DELETE,
                'su.is_delete' => UiModel::NOT_DELETE,
                'tt.is_delete' => UiTplModel::NOT_DELETE,
                'tu.is_delete' => UiModel::NOT_DELETE,
                'su.component_key' => $componentKey
            ])->groupBy('tu.component_key')->asArray()->all();

        if ($relations) {
            $count = \count($relations);
            if ($count > 1
                || ($count === 1 && $relations[0]['component_key'] !== $relationKey
                    && ($id === 0 || ($id > 0 && substr_count($relations[0]['id'], ',') > 0)))
            ) {
                return false;
            }
        }

        return true;
    }
}
