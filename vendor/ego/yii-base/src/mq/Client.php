<?php
namespace ego\mq;

use Yii;
use Globalegrow\RabbitMQ\Exceptions\ReceiveException;
use Globalegrow\RabbitMQ\Exceptions\SendException;

// XcmqClient需要用到这两个常量
if (!defined('XCMQ_CONF_FILE')) {
    define('XCMQ_CONF_FILE', Yii::getAlias('@app/config/mq/mq.' . YII_ENV . '.php'));
}
defined('XLOGGER_CONF_PATH') || define('XLOGGER_CONF_PATH', __DIR__ . '/config');

/**
 * mq客户端
 */
class Client extends \Globalegrow\RabbitMQ\Client
{
    /**
     * @inheritdoc
     */
    public function send($queueName, $data)
    {
        Yii::info([$queueName, $data], __METHOD__);
        try {
            return parent::send($queueName, $data);
        } catch (SendException $e) {
            Yii::warning([$queueName, $data], __METHOD__);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function receive($queueName, callable $callback)
    {
        try {
            return parent::receive($queueName, $callback);
        } catch (ReceiveException $e) {
            Yii::warning($queueName, __METHOD__);
            throw $e;
        }
    }

    /**
     * 设置调用是否抛出异常
     *
     * @param bool $slient
     * @return $this
     */
    public function slient($silent = true)
    {
        return $this->silent($silent);
    }
}
