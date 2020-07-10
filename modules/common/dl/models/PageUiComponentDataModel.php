<?php

namespace app\modules\common\dl\models;

use app\modules\component\models\UiModel;

/**
 * PageUiComponentDataModel模型
 *
 * @property int    $id
 * @property int    $component_id
 * @property string $lang
 * @property string $key
 * @property string $value
 * @property int    $is_public
 * @property int    $is_m
 * @property int    $is_app
 * @property int    $tpl_id
 */
class PageUiComponentDataModel extends AbstractBaseModel
{
    //是否公共数据|1-是
    const IS_PUBLIC = 1;

    //转M时是否保留|1-是
    const IS_M = 1;

    //转APP时是否保留|1-是
    const IS_APP = 1;

    //数据库中存储sku的字段名称
    const KEY_SKU = 'goodsSKU';

    // 同步SKU需要copy的字段名
    const COPY_FIELDS = [self::KEY_SKU, 'navList'];

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
            [['id', 'component_id', 'is_public', 'is_m', 'is_app', 'tpl_id'], 'integer'],
            [['component_id', 'key', 'value', 'tpl_id'], 'required'],
        ];
    }

    /**
     * 获取某个组件单个字段值
     *
     * @param int $componentId 组件ID
     * @param string $lang 语言简码; 如：en
     * @param int $tplId 组件模板ID
     * @param string $key 组件数据字段名称
     *
     * @return string 公共字段值; NULL没有找到
     */
    public static function getFieldValue($componentId, $lang, $tplId, $key)
    {
        $format = "component_id='%d' AND `lang`='%s' AND `key`='%s' AND (tpl_id='%d' OR is_public=1)";
        $condition = sprintf($format, $componentId, $lang, $key, $tplId);
        $row = static::find()->where($condition)->asArray()->one();
        return empty($row) ? NULL : json_decode($row['value'], true);
    }

    /**
     * 设置某个组件单个字段值
     *
     * @param int $componentId 组件ID
     * @param string $lang 语言简码; 如：en
     * @param int $tplId 组件模板ID
     * @param string $key 组件数据字段名称
     * @param object $value  组件数据字段值
     *
     * @return string 公共字段值; NULL没有找到
     */
    public static function setFieldValue($componentId, $lang, $tplId, $key, $value)
    {
        $format = "component_id='%d' AND `lang`='%s' AND `key`='%s' AND (tpl_id='%d' OR is_public=1)";
        $condition = sprintf($format, $componentId, $lang, $key, $tplId);
        static::updateAll(['value' => json_encode($value)], $condition);
    }

    /**
     * 获取某个组件单个公共字段值
     *
     * @param int $componentId
     * @param string $lang
     * @param string $key
     *
     * @return string 公共字段值; NULL没有找到
     */
    public static function getPublicFieldValue($componentId, $lang, $key)
    {
        $row = static::find()->where([
            'component_id' => $componentId,
            'lang'  => $lang,
            'key'   => $key,
            'is_public' => static::IS_PUBLIC
        ])->asArray()->one();
        return empty($row) ? NULL : json_decode($row['value'], true);
    }

    /**
     * 设置某个组件单个公共字段值
     *
     * @param int $componentId
     * @param string $lang
     * @param string $key
     * @param object $value
     *
     * @return string 公共字段值; NULL没有找到
     */
    public static function setPublicFieldValue($componentId, $lang, $key, $value)
    {
        static::updateAll([
                'value' => json_encode($value)
            ], [
                'component_id' => $componentId,
                'lang'  => $lang,
                'key'   => $key,
                'is_public' => static::IS_PUBLIC
        ]);
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
     * 删除布局组件数据
     * @param int|array $ids
     * @param string    $lang
     * @return mixed
     */
    public static function deletePageUiComponentData($ids, $lang='')
    {
        $where = '';
        if (is_numeric($ids)) {
            $where .= '`component_id`=' . $ids ;
        } elseif (is_array($ids)) {
            $where .= '`component_id` IN (' . implode(',', $ids) . ')';
        } else {
            return false;
        }

        if (!empty($lang)) {
            $where .= ' AND lang="'.$lang.'"';
        }

        return static::deleteAll($where);
    }

    /**
     * 获取布局组件数据
     * @param int     $id     page_ui_component_data . component_id
     * @param string  $lang   语种
     * @param int     $tplId  模板id
     * @return mixed
     */
    public static function getDataList($id, $lang, $tplId)
    {
        if (empty($id) || !is_numeric($id) || empty($lang) || empty($tplId)) {
            return [];
        }

        //布局查询条件
        $condition = 'component_id=' . $id . ' AND lang="' . $lang . '" AND (tpl_id=' . $tplId . ' OR is_public=1)';
        $rs = static::find()
            ->select('`component_id`, `key`, `value`')
            ->where($condition)
            ->asArray()
            ->all();

        if (empty($rs)) {
            return [];
        }

        $result = array();
        foreach ($rs as $v) {
            $result[ $v['key'] ] = json_decode($v['value'], true);
        }

        return $result;
    }

    /**
     * 复制布局组件数据
     * @param $fromId
     * @param $toId
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function copyPageUiComponentData($fromId, $toId)
    {
        /** @var array $list */
        $list = static::find()
            ->where(['component_id' => $fromId])
            ->asArray()
            ->all();
        if (empty($list)) {
            return true;
        }

        foreach ($list as &$v) {
            $v['component_id'] = $toId;
            unset($v['id']);
        }
        unset($v);

        $column = array_keys($list[0]);
        if (!self::insertAll($column, $list)) {
            return false;
        }

        return true;
    }

    /**
     * 获取组件数据
     */
    public static function getUiComponentFullData($componentId, $lang, $tplId)
    {
        $where = 'd.component_id='.$componentId.' AND d.lang="'.$lang.'" AND (d.tpl_id='.$tplId.' OR d.is_public=1)';
        $list = static::find()->alias('d')
                ->select('d.*, ui.need_navigate')
                ->leftJoin(PageUiModel::tableName() . ' as u', 'u.id = d.component_id')
                ->leftJoin(UiModel::tableName() . ' as ui', 'u.component_key = ui.component_key')
                ->where($where)
                ->asArray()
                ->all();

        if (empty($list)) {
            return [];
        }

        $return = array();
        foreach ($list as $v) {
            $return[ $v['component_id'] ]['need_navigate'] = $v['need_navigate'];
            $return[ $v['component_id'] ]['tpl_id'] = $v['tpl_id'];
            $return[ $v['component_id'] ]['data'][ $v['key'] ] = json_decode($v['value'], true);
        }

        return $return;
    }

    /**
     * 复制SKU
     * @param array $uiEqualList
     * @param array $langEqual
     * @return bool
     */
    public static function copySku(array $uiEqualList, array $langEqual)
    {
        foreach ($uiEqualList as $ui) {

            // 同步UI组件自动刷新异步数据格式
            /** @var PageUiModel[] $pageUiModels */
            $pageUiModels = PageUiModel::find()->where(['id' => [$ui['fromId'], $ui['toId']]])->indexBy('id')->all();
            $toPageModel = $pageUiModels[$ui['toId']];
            $toPageModel->async_data_format = $pageUiModels[$ui['fromId']]->async_data_format;
            if (!$toPageModel->save(false)) {
                return $toPageModel->flattenErrors(', ');
            }
            unset($toPageModel, $pageUiModels);

            // 这里数据库查询只能放在foreach循环里，因为条件差异很大（每条记录的条件都不一样），无法在foreach外全部查出
            /** @var static[] $fromSkuKeys */
            $fromSkuKeys = static::find()->where([
                'component_id' => $ui['fromId'],
                'lang' => $langEqual['fromLang'],
                'key' => self::COPY_FIELDS,
            ])->andWhere('tpl_id = ' . $ui['fromTplId'] . ' OR is_public = ' . static::IS_PUBLIC)
                ->indexBy('key')->all();
            if (empty($fromSkuKeys)) {
                continue;
            }

            $toSkuKeys = static::find()->where([
                'component_id' => $ui['toId'],
                'lang' => $langEqual['toLang'],
                'key' => self::COPY_FIELDS,
            ])->andWhere('tpl_id = ' . $ui['toTplId'] . ' OR is_public = ' . static::IS_PUBLIC)
                ->indexBy('key')->all();

            foreach ($fromSkuKeys as $key => $fromSku) {
                $toSku = $toSkuKeys[$key] ?? null;

                if (!$toSku) {
                    $toSku = new static();
                    $toSku->component_id = $ui['toId'];
                    $toSku->lang = $langEqual['toLang'];
                    $toSku->key = $key;
                    $toSku->is_public = $fromSku->is_public;
                    $toSku->is_m = $fromSku->is_m;
                    $toSku->is_app = $fromSku->is_app;
                }
                $toSku->value = $fromSku->value;
                $toSku->tpl_id = $ui['toTplId'];
                if (!$toSku->save(true)) {
                    return $toSku->flattenErrors(', ');
                }
            }

        }

        return true;
    }
}
