<?php

namespace app\modules\component\models;

use app\base\SitePlatform;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\models\ActiveRecord;

/**
 * 组件模板模型
 *
 * @property int    $id
 * @property string $component_key
 * @property string $pic
 * @property string $name
 * @property string $name_en
 * @property int    $status
 * @property int    $is_async
 * @property int    $is_vue_ssr
 * @property int    $range
 * @property int    $place
 * @property string $create_user
 * @property string $update_user
 * @property int    $create_time
 * @property int    $update_time
 * @property string $siteGroups
 * @property array $lang_keys
 */
class UiTplModel extends ActiveRecord
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
     * 是否删除|1是
     */
    const IS_DELETE = 1;

    /**
     * 是否删除|0否
     */
    const NOT_DELETE = 0;

    /**
     * @var $query
     */
    public $query;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->query = static::find();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ui_component_tpl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'component_key',
                    'pic',
                    'name',
                    'name_en',
                    'place'
                ],
                'required',
            ],
            ['name_en', 'match', 'pattern' => '/[a-z]+/']
        ];
    }

    /**
     * 将字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'range', 'siteGroups', 'lang_keys', 'c_name'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 模板列表
     * @param array $params = [
     *      int    $pageNo   [页码]
     *      int    $pageSize [每页数]
     *      string $key      [组件编码]
     *      int    $status   [模板状态]
     *      string $name     [组件名称]
     * ]
     * @return array
     */
    public function tplList(array $params)
    {
        $queryList = static::find()->select('ut.*, group_concat(ctsr.site_code) as siteGroups')->alias('ut')
            ->leftJoin(
                ComponentTplSiteRelationModel::tableName() . ' as ctsr',
                'ut.id = ctsr.tpl_id AND ctsr.type = ' . ComponentTplSiteRelationModel::TYPE_UI
            )->where('ut.is_delete = ' . static::NOT_DELETE)
            ->andFilterWhere(['ut.component_key' => !empty($params['key']) ? $params['key'] : null])
            ->andFilterWhere(['like', 'ut.name', $params['name'] ?? ''])
            ->andFilterWhere(['ut.status' => $params['status'] !== '' ? $params['status'] : null])
            ->andFilterWhere(['ut.place' => !empty($params['place']) ? $params['place'] : null])
            ->groupBy('ut.id');
        !empty($params['siteGroup']) && $queryList = $queryList->having(['like', 'siteGroups', $params['siteGroup']]);
        $pageNo = !empty($params['pageNo']) ? $params['pageNo'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 20;

        $count = $queryList->count();
        if (0 === $count) {
            return [];
        }

        $list = $queryList
            ->offset(($pageNo - 1) * $pageSize)
            ->limit($pageSize)
            ->all();
        $list && $list = array_map(function ($item) {
            !empty($item->siteGroups) && $item->siteGroups = SitePlatform::getSiteGroupsBySiteCodes($item->siteGroups);
            $item->lang_keys = UiComponentLanguageRelationModel::getUiComponentKeys($item->id);
            return $item;
        }, $list);

        return [
            'totalCount' => (int)$count,
            'pageSize'   => (int)$pageSize,
            'pageNo'     => (int)$pageNo,
            'list'       => $list
        ];
    }

    /**
     * 获取组件可用的模板列表
     *
     * @param $componentKey
     * @param $siteCode
     *
     * @return array
     */
    public static function getComponentTplList($componentKey, $siteCode)
    {
        $list = static::find()->alias('ut')
            ->leftJoin(
                ComponentTplSiteRelationModel::tableName() . ' as ctsr',
                'ctsr.tpl_id = ut.id AND ctsr.type = ' . ComponentTplSiteRelationModel::TYPE_UI
            )->where([
                'ut.component_key' => $componentKey,
                'ut.status'        => static::STATUS_USED,
                'ut.is_delete'     => static::NOT_DELETE,
                'ctsr.site_code'   => $siteCode
            ])->groupBy('ut.id')->all();

        return $list ? ArrayHelper::toArray($list) : [];
    }

    /**
     * 获取组件所有模板
     * @param string $componentKey
     * @return static[]
     */
    public static function getComponentAllTpl($componentKey)
    {
        return static::find()->where([
            'component_key' => $componentKey,
            'status'        => static::STATUS_USED,
            'is_delete'     => static::NOT_DELETE,
        ])->all();
    }

    /**
     * 获取模板详细信息
     *
     * @param int $id
     *
     * @return array|null|\app\modules\component\models\UiTplModel
     */
    public static function getTplFullInfo(int $id)
    {
        return self::find()->alias('tpl')
            ->select('tpl.*, ui.range')
            ->leftJoin(UiModel::tableName() . ' as ui', 'tpl.component_key = ui.component_key')
            ->where([
                'tpl.id'        => $id,
                'tpl.is_delete' => self::NOT_DELETE,
                'ui.is_delete'  => UiModel::NOT_DELETE
            ])->one();
    }
    
    /**
     * 批量获取模板详细信息
     *
     * @param array $ids
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function batchTplFullInfo(array $ids)
    {
        return self::find()->alias('tpl')
            ->select('tpl.id as tpl_id, tpl.name as t_name, ui.component_key, ui.name as c_name, tpl.name_en')
            ->leftJoin(UiModel::tableName() . ' as ui', 'tpl.component_key = ui.component_key')
            ->where([
                'tpl.is_delete' => self::NOT_DELETE,
                'ui.is_delete'  => UiModel::NOT_DELETE
            ])
            ->andWhere(['in', 'tpl.id', $ids])
            ->orderBy(new Expression("FIND_IN_SET(tpl.id, '" . implode(',', $ids) ."')"))
            ->asArray()
            ->all();
    }
}
