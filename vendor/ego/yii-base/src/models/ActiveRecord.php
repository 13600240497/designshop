<?php
namespace ego\models;

use yii\db\ColumnSchema;

/**
 * 基础ActiveRecord
 *
 * @property int $id
 * @property int $create_time
 * @property int $update_time
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @var string 字段中文名注释分隔符
     */
    const COMMENT_SEPARATOR = '|';
    /**
     * @var array 获取表名缓存
     */
    private static $tableNameCache = [];
    /**
     * @var array 获取表名label
     */
    private static $tableLabels = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $class = static::class;
        if (!isset(self::$tableNameCache[$class])) {
            $name = substr($class, strrpos($class, '\\') + 1);
            if ('Model' == substr($class, -5)) {
                // app\modules\admin\models\AdminModel -> admin
                // app\modules\admin\models\AdminActionHistoryModel -> admin_action_history
                $name = substr($name, 0, -5);
            }
            self::$tableNameCache[$class] = app()->helper->str->revertCamelCase($name, '_');
        }
        return self::$tableNameCache[$class];
    }

    /**
     * 获取缓存实例
     *
     * @return \yii\redis\Cache
     */
    public static function getCache()
    {
        return app()->cache;
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

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if ($insert && $this->getColumnInfo('create_time')) {
            empty($this['create_time']) && $this['create_time'] = time();
        } elseif (!$insert && $this->getColumnInfo('update_time')) {
            $this['update_time'] = time();
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        static::clearCache($this);
        /** @var UnlimitedTrait $this */
        if (isset(static::$useUnlimitedTrait)) {
            $old = [];
            $new = [
                'id' => $this->id,
                'parent_id' => $this->parent_id
            ];
            if (!$insert) {
                $old = [
                    'id' => $this->id,
                    'parent_id' => $changedAttributes['parent_id'] ?? $this->parent_id,
                    'node' => $this->node
                ];
            }
            if (true !== ($node = $this->updateNode($old, $new))) {
                $this->node = $node;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        static::clearCache($this);
    }

    /**
     * 清除缓存
     *
     * @param int|array|static|ActiveRecord $id
     * @return bool
     */
    public static function clearCache($id)
    {
        if (!isset(static::$useCacheTrait)) {
            return false;
        } elseif ($id instanceof static) {
            $id = $id->id;
        }
        $arguments = (array) $id;
        array_unshift($arguments, static::getCacheKey());
        static::getCache()->redis->executeCommand('HDEL', $arguments);
        return true;
    }

    /**
     * 缓存键名
     *
     * @param int|string $value
     * @return string
     */
    public static function getCacheKey($value = null)
    {
        $key = static::getCache()->keyPrefix . static::tableName();
        if (null !== $value) {
            $key .= ':' . $value;
        }
        return $key;
    }

    /**
     * 将错误转化为一维数组或者字符串
     *
     * @param string|null $join
     * @param string|null $field
     * @return array|string
     */
    public function flattenErrors($join = null, $field = null)
    {
        $errors = app()->helper->arr->flatten($this->getErrors($field));
        if (null === $join) {
            return $errors;
        } else {
            return join($join, $errors);
        }
    }

    /**
     * 获取字段信息
     *
     * @param string $name 字段名
     * @param string $key 获取字段什么信息，当值为**labelName**时，将通过字段注释获取字段的中文名称
     * @return \yii\db\ColumnSchema|string|null
     */
    public function getColumnInfo($name, $key = null)
    {
        // 中文名称
        if ('labelName' == $key) {
            $labels = $this->attributeLabels();
            if (isset($labels[$name])) { // 优先模型中的定义
                return $labels[$name];
            }
        }

        $columns = $this->getTableSchema()->columns;
        if (isset($columns[$name])) {
            $columnInfo = $columns[$name];
            if (null === $key) {
                return $columnInfo;
            } elseif ('labelName' == $key) { // 中文名称
                return $this->getLabelByComment($columnInfo);
            } else {
                return $columnInfo->{$key};
            }
        }
        return null;
    }

    /**
     * 通过字段注释获取表所有字段的中文名称
     *
     * @param ColumnSchema[] $columns
     * @return array
     */
    public static function getAllLabelsByComment(array $columns = null)
    {
        $labels = [];
        if (null === $columns) {
            $columns = static::getTableSchema()->columns;
        }
        foreach ($columns as $column) {
            $labels[$column->name] = static::getLabelByComment($column);
        }
        return $labels;
    }

    /**
     * 通过字段注释获获取字段的中文名称
     *
     * @param ColumnSchema $columnInfo
     * @return string
     */
    public static function getLabelByComment(ColumnSchema $columnInfo)
    {
        // 获取注释中的以"|"隔开的第一部份，没有“|”时则取全部注释
        if ($columnInfo->comment) {
            return explode(static::COMMENT_SEPARATOR, $columnInfo->comment)[0];
        } else {
            return $columnInfo->name;
        }
    }

    /**
     * 获取库中所有表的labels
     *
     * @return array
     */
    public static function getTableLabels()
    {
        if (self::$tableLabels) {
            return self::$tableLabels;
        }
        if (!$data = static::getCache()->get('tableLabels')) {
            $tables = static::getDb()->schema->tableNames;
            foreach ($tables as $item) {
                $data[$item] = static::getTableLabel($item);
            }
            static::getCache()->set('tableLabels', $data, 86400);
        }
        return self::$tableLabels = $data;
    }

    /**
     * 获取指定表的label
     *
     * ```sql
     *  CREATE TABLE xxx(...) COMMMENT '用户|foo|2017-04-13' -> 用户
     *
     *  CREATE TABLE xxx(...) -> xxx
     * ```
     * @param string $table
     * @return string
     */
    protected static function getTableLabel($table)
    {
        $info = static::getDb()->createCommand('SHOW CREATE TABLE ' . $table)->queryOne()['Create Table'] ?? '';
        preg_match("/COMMENT='(.+)'\$/", $info, $match);
        if (empty($match[1])) {
            return $table;
        } else {
            return explode('|', $match[1])[0];
        }
    }
}
