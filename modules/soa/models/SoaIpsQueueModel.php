<?php
namespace app\modules\soa\models;

use app\models\ActiveRecord;

/**
 * SoaIpsQueueModel 模型
 *
 * @property int    $id 自增ID
 * @property string $message MQ消息内容
 * @property int    $add_time 添加时间
 * @property int    $run_time 处理开始时间
 * @property int    $end_time 处理结束时间
 * @property int    $status 状态(0: 待处理； 1: 处理成功; 2: 处理失败)
 * @property string $result 处理结果信息
 */
class SoaIpsQueueModel extends ActiveRecord
{
    /** @var int 状态 - 待处理 */
    const STATUS_READY = 0;
    /** @var int 状态 - 处理中 */
    const STATUS_RUNNING = 1;
    /** @var int 状态 - 处理成功 */
    const STATUS_SUCCESS = 2;
    /** @var int 状态 - 处理失败 */
    const STATUS_FAIL = 3;

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
            [['message', 'add_time', 'status'], 'required']
        ];
    }

    /**
     * 保存队列消息
     *
     * @param string $message
     * @return bool
     */
    public static function saveMessage($message)
    {
        $row = new static();
        $row->message = $message;
        $row->add_time = time();
        $row->status = self::STATUS_READY;
        return $row->insert(true);
    }

    /**
     * 获取下一个要处理的数据
     * @return static
     */
    public static function getNext()
    {
        return self::find()->where(['status' => self::STATUS_READY])->limit(1)->orderBy(['id' => SORT_ASC])->one();
    }
}

