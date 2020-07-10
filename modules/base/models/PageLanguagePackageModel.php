<?php

namespace app\modules\base\models;


use app\models\ActiveRecord;
use app\base\Pagination;
use yii\db\Expression;

/**
 * Class PageLanguagePackageModel
 *
 * @package app\modules\base\models
 *
 * @property string $site_code
 * @property string $lang_name
 * @property string $lang
 *
 */
class PageLanguagePackageModel extends ActiveRecord
{
    
    /**
     * 获取语言包列表
     *
     * @param string $siteCode
     *
     * @return array
     */
    public static function getLangPackageList(string $siteCode)
    {
        $query = static::find()
            ->select('id, lang_name, lang, update_user, update_time')
            ->where(['site_code' => $siteCode]);
        $count = $query->count();
        
        $pagination = Pagination::new($count);
        $data = $query->orderBy('id ASC')
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
    
    /**
     * 获取站点所有语言
     *
     * @param string $siteCode
     *
     * @return array
     */
    public static function getSiteLanguageList(string $siteCode)
    {
        $result = static::find()
            ->select('lang, lang_name')
            ->where(['site_code' => $siteCode])
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'lang_name', 'lang') : [];
    }
    
    /**
     * 检查语言包是否存在
     *
     * @param string $siteCode
     * @param array  $langCode
     *
     * @return array
     */
    public static function checkExistsLangPackage(string $siteCode, array $langCode, string $fields = 'lang_name')
    {
        $result = static::find()->select($fields)
            ->where(['site_code' => $siteCode])
            ->andWhere(['in', 'lang', $langCode])
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, $fields) : [];
    }
    
    /**
     * 获取语言简码对应的中文名
     *
     * @param string $siteCode
     * @param array  $langArray
     *
     * @return array
     */
    public static function getLanguageZhName(string $siteCode, array $langArray)
    {
        $result = static::find()
            ->select('lang, lang_name')
            ->where(['lang' => $langArray, 'site_code' => $siteCode])
            ->orderBy(new Expression("FIND_IN_SET(lang, '" . implode(',', $langArray) . "')"))
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'lang_name', 'lang') : [];
    }
}