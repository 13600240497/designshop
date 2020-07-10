<?php
namespace app\models;

use app\modules\base\models\AdminLogModel;
use ego\diff\Changes;

/**
 * 基础ActiveRecord
 */
class ActiveRecord extends \ego\models\ActiveRecord
{
    /**
     * @var array 日志配置
     */
    protected $logConfig = [
        'nameField' => 'id',
    ];

    /**
     * 新实例
     *
     * @param array $attributes
     * @return static
     */
    public static function new(array $attributes = [])
    {
        return new static($attributes);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->getAllLabelsByComment();
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function beforeSave($insert)
    {
        if (!$insert
            && $this->getColumnInfo('update_user')
            && app()->user->id
        ) {
            $this['update_user'] = app()->user->username;
        }

        if ($insert
            && $this->getColumnInfo('create_user')
            && app()->user->id
        ) {
            empty($this['create_user']) && $this['create_user'] = app()->user->username;
            // 新增时，默认用create信息初始化update信息
            $this->getColumnInfo('update_user') && empty($this['update_user']) && $this['update_user'] = app()->user->username;
            $this->getColumnInfo('update_time') && empty($this['update_time']) && $this['update_time'] = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->log($insert ? 'insert' : 'update', $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->log('delete');
    }

    /**
     * 获取记录的名称
     *
     * @param array $changedAttributes
     * @return string
     */
    protected function getRecordName($changedAttributes)
    {
        $name = $this->logConfig['nameField'];
        if ($name && isset($changedAttributes[$name])) {
            return $changedAttributes[$name];
        }

        if ($name && isset($this->{$name})) {
            return $this->{$name};
        }

        return '';
    }

    /**
     * 添加日志
     * @param string $action
     * @param array $changedAttributes
     * @return bool
     * @throws \Throwable
     */
    protected function log($action, array $changedAttributes = null)
    {
        $requestId = \app\modules\base\components\AccessLogComponent::getRequestId();
        if (!$this->logConfig || empty($requestId)) {
            return false;
        }

        $log = $this->getAdminLogModel();
        $log->is_insert = 'insert' === $action ? 1 : 0;
        $log->action = $action;
        $log->request_id = $requestId;
        $log->detail = $this->getChangesData($changedAttributes);
        $log->record_table = $this->tableName();
        $log->record_id = \is_int($this->primaryKey) ? $this->primaryKey : 0;
        $log->record_name = $this->getRecordName($changedAttributes);
        return $log->insert(false);
    }

    /**
     * 获取日志比较差异数据
     *
     * @param array $changedAttributes
     * @return array
     */
    protected function getChangesData($changedAttributes)
    {
        return $changedAttributes ? $this->getChanges($changedAttributes)->get() : [];
    }

    /**
     * 获取`Diff`对象
     *
     * @param array $changedAttributes
     * @return Changes
     */
    protected function getChanges($changedAttributes)
    {
        $new = [];
        foreach ($changedAttributes as $field => $value) {
            $new[$field] = $this->{$field};
        }
        return new Changes([
            'old' => $changedAttributes,
            'new' => $new,
            'model' => $this
        ]);
    }

    /**
     * 获取管理员操作日志模型
     *
     * @return AdminLogModel
     */
    protected function getAdminLogModel()
    {
        return new AdminLogModel();
    }

    /**
     * 批量插入
     *
     * @param array $columns
     * ['name', 'age']
     * @param array $data
     * [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ]
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
}
