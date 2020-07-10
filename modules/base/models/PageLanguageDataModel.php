<?php

namespace app\modules\base\models;


use app\models\ActiveRecord;
use app\base\Pagination;
use yii\db\Exception;
use yii\db\Expression;

/**
 * 多语言包内容
 *
 * Class PageLanguageDataModel
 *
 * @package app\modules\base\models
 */
class PageLanguageDataModel extends ActiveRecord
{
    
    /**
     * 获取语言包内容列表
     *
     * @param string $lang
     *
     * @return array
     */
    public static function getLangDataList(array $lang, string $fields = '*', array $search = [])
    {
        $count = self::find()
            ->select('id')
            ->where(['lang' => $lang])
            ->andFilterWhere(['like', 'key', !empty($search['search_key']) ? $search['search_key'] : ''])
            ->andFilterWhere(['like', 'value', !empty($search['search_value']) ? $search['search_value'] : ''])
            ->groupBy('key')
            ->count();
        
        $pagination = Pagination::new($count);
    
        $data = self::find()
            ->select($fields)
            ->where(['lang' => $lang])
            ->andFilterWhere(['like', 'key', !empty($search['search_key']) ? $search['search_key'] : ''])
            ->andFilterWhere(['like', 'value', !empty($search['search_value']) ? $search['search_value'] : ''])
            ->groupBy('key')
            ->orderBy('id DESC')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();
        
        return [
            'list'       => $data ?? [],
            'pagination' => [
                $pagination->pageParam     => (int) $pagination->page + 1,
                $pagination->pageSizeParam => (int) $pagination->pageSize,
                'totalCount'               => (int) $pagination->totalCount
            ]
        ];
    }
    
    public static function getLangValues(string $key)
    {
        $result = self::find()
            ->select('lang, value')
            ->where(['key' => $key])
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'value', 'lang') : [];
    }
    
    /**
     * 检查多语言key是否存在
     *
     * @param array $lang
     * @param array $key
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function checkExistsLangData(array $lang, array $key)
    {
        return static::find()->select('lang, key')
            ->where(
                ['key' => $key, 'lang' => $lang]
            )
            ->asArray()
            ->all();
    }
    
    /**
     * 获取多语言keys
     *
     * @param string $lang
     *
     * @return array
     */
    public static function getLangAllKeysData(string $lang, string $fileds = 'key,lang_zh')
    {
        return static::find()->select($fileds)
            ->where(['lang' => $lang, 'is_delete' => 0])
            ->asArray()
            ->all();
    }
    
    /**
     * 根据key获取多语言文案
     *
     * @param string $key
     *
     * @return array
     */
    public static function getLangsForKey(string $key)
    {
        $result = static::find()->select('lang, value')
            ->where(['key' => $key])
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'value', 'lang') : [];
    }
    
    /**
     * 获取多语言内容
     *
     * @param array $langs
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDataForLangs(array $langs)
    {
        return static::find()
            ->select('key, `value`,`lang`')
            ->where(['lang' => $langs])
            ->orderBy(new Expression("FIND_IN_SET(lang, '" . implode(',', $langs) . "')"))
            ->asArray()
            ->all();
    }
    
    /**
     * 保存多语言包的文案
     *
     * @param array $data
     *
     * @return bool
     */
    public static function saveLanguagePackage(array $data)
    {
        $transaction = app()->db->beginTransaction();
        if (!empty($data['insert']) && is_array($data['insert'])) {
            $columns = array_keys(current($data['insert']));
            $insertValues = array_map(function ($item) {
                return array_values($item);
            }, $data['insert']);
            $insertValues = array_values($insertValues);
            try {
                self::insertAll($columns, $insertValues);
            } catch (Exception $exception) {
                $transaction->rollBack();
                
                return false;
            }
        }
        if (!empty($data['update']) && is_array($data['update'])) {
            foreach ($data['update'] as $update) {
                $attributes = [
                    'value'       => $update['value'],
                    'update_user' => app()->user->username,
                    'update_time' => time()
                ];
                $condition = ['key' => $update['key'], 'lang' => $update['lang']];
                try {
                    self::updateAll($attributes, $condition);
                } catch (Exception $exception) {
                    $transaction->rollBack();
                    
                    return false;
                }
            }
        }
        
        $transaction->commit();
        
        return true;
    }
    
    /**
     * 获取多语言key最后的操作时间
     *
     * @param string $key
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getLangKeyLastModify(string $key)
    {
        return static::find()
            ->select('update_user, update_time')
            ->where(['key' => $key])
            ->asArray()
            ->orderBy('update_time DESC')
            ->one();
    }
}