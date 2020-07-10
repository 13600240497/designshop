<?php

namespace app\commands\models;


use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{
    /**
     * @var array 字符串处理缓存
     */
    protected static $caches = [];
    
    /**
     * @var array 获取表名缓存
     */
    private static $tableNameCache = [];
    
    /**
     * @var string 表前缀
     */
    public static $prefix = '';
    
    
    public static function tableName()
    {
        $class = static::class;
        if (!isset(self::$tableNameCache[ $class ])) {
            $name = substr($class, strrpos($class, '\\') + 1);
            if ('Model' == substr($class, -5)) {
                // app\modules\admin\models\AdminModel -> admin
                // app\modules\admin\models\AdminActionHistoryModel -> admin_action_history
                $name = substr($name, 0, -5);
            }
            self::$tableNameCache[ $class ] = static::revertCamelCase($name, '_');
        }
        
        return !empty(self::$prefix) ? self::$prefix . '_' . self::$tableNameCache[ $class ] : self::$tableNameCache[ $class ];
    }
    
    /**
     * 将驼峰式风格的字符串还原为用指定字符连接的小写字符串
     *
     * **AbcDefGhi** -> **abc-def-ghi**
     *
     * @param string $string
     * @param string $separator 转化后字符串的分隔符
     *
     * @return string
     */
    public static function revertCamelCase($string, $separator = '-')
    {
        $key = $string . $separator;
        if (isset(static::$caches['revertCamelCase'][ $key ])) {
            return static::$caches['revertCamelCase'][ $key ];
        }
        
        $string = preg_replace_callback(
            '/[A-Z]/',
            function ($matches) use ($separator) {
                return $separator . strtolower($matches[0]);
            },
            $string
        );
        $string = ltrim($string, $separator);
        
        return static::$caches['revertCamelCase'][ $key ] = $string;
    }
    
    /**
     * 根据id获取记录
     *
     * @param int $id
     * @param string $field
     * @return static|string|null
     */
    public static function getById($id, $field = null)
    {
        $item = static::findOne($id);
        if (!$item) {
            return null;
        } elseif ($field) {
            return $item[$field];
        } else {
            return $item;
        }
    }
}