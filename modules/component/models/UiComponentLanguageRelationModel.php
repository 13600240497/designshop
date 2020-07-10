<?php

namespace app\modules\component\models;


use yii\db\Exception;

class UiComponentLanguageRelationModel extends ComponentModel
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ui_component_language_relation';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key_id', 'component_id', 'tpl_id', 'range', 'component_key', 'component_name'], 'required'],
            [['key_id', 'component_id', 'tpl_id', 'range'], 'integer'],
            [['component_key', 'component_name'], 'string']
        ];
    }
    
    /**
     * 保存组件多语言关联key
     *
     * @param array $keys
     * @param int   $tplId
     *
     * @return bool
     */
    public static function saveLanguageRelation(array $keys, int $tplId)
    {
        $insert = [];
        $result = static::find()->select('id, lang_key')
            ->where(['tpl_id' => $tplId])
            ->asArray()
            ->all();
        $transaction = app()->db->beginTransaction();
        try {
            if (!empty($result)) {
                $delete = array_filter($result, function ($item) use ($keys) {
                    return !in_array($item['lang_key'], $keys);
                });
                if (!empty($delete)) {
                    if (false === static::deleteAll(['id' => array_column($delete, 'id')])) {
                        $transaction->rollBack();
                        
                        return false;
                    }
                }
                $hasKeys = array_column($result, 'lang_key');
                $keys = array_filter($keys, function ($item) use ($hasKeys) {
                    return !in_array($item, $hasKeys);
                });
            }
            
            if (!empty($keys) && is_array($keys)) {
                foreach ($keys as $key) {
                    $insert[] = [$key, $tplId];
                }
                
                if (!empty($insert) && is_array($insert)) {
                    if (false === static::insertAll(['lang_key', 'tpl_id'], $insert)) {
                        $transaction->rollBack();
                        
                        return false;
                    }
                }
            }
            
            $transaction->commit();
            
            return true;
        } catch (Exception $exception) {
            $transaction->rollBack();
            
            return false;
        }
    }
    
    /**
     * 获取组件绑定的UI组件模板
     *
     * @param string $key
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getKeyUiComponent(string $key)
    {
        return static::find()->alias('lr')
            ->select('ud.component_key, u.name AS component_name, ud.name AS tpl_name, u.range')
            ->innerJoin(UiTplModel::tableName() . ' AS ud', 'lr.tpl_id = ud.id')
            ->innerJoin(UiModel::tableName() . ' AS u', 'ud.component_key = u.component_key')
            ->where(['lr.lang_key' => $key, 'ud.is_delete' => UiModel::NOT_DELETE])
            ->asArray()
            ->all();
    }
    
    /**
     * 获取模板使用的多语言key
     *
     * @param int $tplId
     *
     * @return array
     */
    public static function getUiComponentKeys(int $tplId)
    {
        $result = static::find()
            ->select('lang_key')
            ->where(['tpl_id' => $tplId])
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'lang_key') : [];
    }
}