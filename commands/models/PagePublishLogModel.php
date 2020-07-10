<?php

namespace app\commands\models;


class PagePublishLogModel extends BaseModel
{
    /**
     * 日志类型|1-缓存文件生成日志
     */
    const LOG_TYPE_CREATE = 1;
    
    /**
     * 日志类型|2-发布S3日志
     */
    const LOG_TYPE_PUBLISH = 2;
    
    /**
     * @var string 字段中文名注释分隔符
     */
    const COMMENT_SEPARATOR = '|';
    
    
    /**
     * 批量插入
     *
     * @param array $list 数据
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public static function insertAllData(array $list)
    {
        if (empty($list)) {
            return 0;
        }
        
        $columns = array_keys($list[0]);
        $data = [];
        foreach ($list as $item) {
            $data[] = array_values($item);
        }
        
        return static::getDb()->createCommand()->batchInsert(
            parent::tableName(),
            $columns,
            $data
        )->execute();
    }
    
    /**
     * 批量更新
     *
     * @param        $attributes
     * @param string $condition
     * @param array  $params
     *
     * @return int
     */
    public static function updateAllData($attributes, $condition = '', $params = [])
    {
        return self::updateAll($attributes, $condition);
    }
}