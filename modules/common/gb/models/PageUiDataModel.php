<?php

namespace app\modules\common\gb\models;

use app\modules\component\models\UiModel;
use yii\helpers\ArrayHelper;

/**
 * PageLayoutComponent模型
 *
 * @property int    $id
 * @property int    $component_id
 * @property string $lang
 * @property string $data
 * @property string $share_data
 * @property string $custom_css
 * @property int    $tpl_id
 * @property int    $select_tpl_id
 * @property array  $logConfig
 */
class PageUiDataModel extends BaseModel
{
    /**
     * 组件类型：2-UI组件
     */
    const COMPONENT_TYPE = 2;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'component_id';
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gb_page_ui_component_data';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['component_id', 'integer'],
            [['component_id', 'lang', 'data'], 'required'],
            [['component_id', 'lang', 'tpl_id'], 'validateKey'],
        ];
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'component_key',
            'layout_id',
            'next_id',
            'position',
            'need_navigate',
            'lu_id',
            'pipeline'
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
                'component_id' => $this->component_id,
                'lang'         => $this->lang,
                'tpl_id'       => $this->tpl_id,
            ]))
            && (!$this->id || (int) $item->id !== (int) $this->id)
        ) {
            $this->addError('component_id', 'component_id&&lang主键冲突');

            return false;
        }

        return true;
    }

    /**
     * 当前uiData下的dataList
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDatas()
    {
        return $this->hasMany(PageUiComponentDataModel::class, [
            'component_id' => 'component_id',
            'lang' => 'lang',
            'tpl_id' => 'tpl_id'
        ]);
    }

    /**
     * 获取组件数据
     *
     * @param int    $componentId 组件ID
     * @param string $lang        语言代码简称
     * @param int    $tplId       組件模板ID
     *
     * @return array
     */
    public static function getComponentData($componentId, $lang, $tplId)
    {
        $data = [];

        if ($item = static::findOne(['component_id' => $componentId, 'lang' => $lang, 'tpl_id' => $tplId])) {
            if (!empty($item->share_data)) {
                $data = array_merge(json_decode($item->data, true), json_decode($item->share_data, true));
            } else {
                $data = json_decode($item->data, true);
            }
        }

        return $data;
    }

    /**
     * 获取组件完整数据
     *
     * @param int|array $componentIds 组件ID
     * @param string|array $lang 语言代码简称
     * @param array $convertCondition pc转m，m转app的筛选条件
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getComponentFullData($componentIds, $lang, array $convertCondition = [])
    {
        $sql = static::find()->alias('d')
            ->select('u.*, d.component_id, d.lang, ui.need_navigate, d.tpl_id, d.select_tpl_id, d.custom_css,
                group_concat(' . PageUiComponentDataModel::tableName() . '.id) as lu_id')
            ->joinWith(['datas'])
            ->leftJoin(
                PageUiModel::tableName() . ' as u',
                'u.id = d.component_id'
            )->leftJoin(
                UiModel::tableName() . ' as ui',
                'u.component_key = ui.component_key'
            )->where(['d.component_id' => $componentIds, 'd.lang' => $lang])
            ->andWhere('d.tpl_id = d.select_tpl_id')
            ->groupBy('d.id')->createCommand()->getRawSql();

        $data = static::getDb()->createCommand($sql)->queryAll();

        if ($data) {
            //去除空值后组装成一维ID数组
            $luData = [];
            $luIds = explode(',', implode(',', array_filter(array_column($data, 'lu_id'))));
            if (!empty($luIds)) {
                $luData = PageUiComponentDataModel::find()
                    ->where(['id' => $luIds])
                    ->andFilterWhere($convertCondition)
                    ->all();
                $luData = $luData ? ArrayHelper::toArray($luData) : [];
            }
            self::buildComponentFullData($data, $luData);
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * 组装component数据
     * @param array $uiData
     * @param array $luData
     */
    private static function buildComponentFullData(array &$uiData, array $luData)
    {
        $luDataInUiId = [];
        if (!empty($luData)) {
            foreach ($luData as $lu) {
                $luDataInUiId[$lu['component_id']][] = [
                    'key' => $lu['key'],
                    'value' => json_decode($lu['value'], true)
                ];
            }
        }

        //$luDataInUiId为空也需要循环，为了填充data字段，保持字段完整性
        foreach ($uiData as &$ui) {

            $ui['data'] = isset($luDataInUiId[$ui['id']]) ? array_column($luDataInUiId[$ui['id']], 'value', 'key') : [];
        }
    }

    /**
     * 获取组件模板ID
     *
     * @param int    $componentId  组件ID
     * @param string $componentKey 组件key
     * @param string $lang         语言代码简称
     *
     * @return int
     */
    public static function getTplId($componentId, $componentKey, $lang)
    {
        $tplId = 0;

        if ($item = static::findOne(['component_id' => $componentId, 'lang' => $lang])) {
            $tplId = (int) $item->tpl_id;
        }
        if (!$tplId && ($uiComponent = UiModel::findOne(['component_key' => $componentKey]))) {
            //如果未选择模板，则取默认模板ID
            $tplId = $uiComponent->tpl_id;
        }

        return $tplId;
    }

    /**
     * 获取批量插入的字段(批量插入不会自动加上create_user等字段的值)
     *
     * @return array
     */
    public function getInsertAttributes()
    {
        $full = parent::attributes();

        //将id删掉
        unset($full[0]);

        return $full;
    }

    /**
     * 批量插入
     *
     * @param array $columns 列数
     * @param array $data    数据
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public static function insertAll(array $columns, array $data)
    {
        return static::getDb()->createCommand()->batchInsert(
            static::tableName(),
            $columns,
            $data
        )->execute();
    }

    /**
     * 获取组件信息
     *
     * @param int    $componentId 组件ID
     * @param string $lang        语言代码简称
     * @param int    $tplId       组件模板ID
     *
     * @return array|\app\modules\common\models\PageUiDataModel
     */
    public static function getItem($componentId, $lang, $tplId)
    {
        if (empty($componentId) || empty($lang)) {
            return [];
        }

        $item = static::findOne(['component_id' => $componentId, 'lang' => $lang, 'tpl_id' => $tplId]);
        if (!empty($item)) {
            return (array)$item;
        }

        $where = 'component_id='.$componentId.' AND lang="'.$lang.'" AND tpl_id=select_tpl_id';
        $item = static::find()->where($where)->one();
        if (!empty($item)) {
            return $item;
        }

        return (array)static::findOne(['component_id' => $componentId, 'lang' => $lang]);
    }
}
