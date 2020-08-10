<?php
namespace Globalegrow\RabbitMQ;

use Globalegrow\Base\Component;
use Globalegrow\RabbitMQ\Exceptions\ReceiveException;
use Globalegrow\RabbitMQ\Exceptions\SendException;

// XcmqClient需要用到这两个常量
if (!defined('XCMQ_CONF_FILE')) {
    define('XCMQ_CONF_FILE', __DIR__ . '/config/config.example.php');
}
defined('XLOGGER_CONF_PATH') || define('XLOGGER_CONF_PATH', __DIR__ . '/config');

/**
 * mq客户端
 */
class Client extends Component
{
    /**
     * @var array 配置
     */
    protected $config = [];
    /**
     * @var bool 发生错误时不抛出异常？
     */
    protected $silent = false;

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->config = require(XCMQ_CONF_FILE);
    }

    /**
     * 发送mq
     *
     * @param string $queueName
     * @param mixed $data
     * @return mixed
     * @throws SendException
     */
    public function send($queueName, $data)
    {
        try {
            return $this->getXcmqClient($queueName)->sendMQ($data, $this->getRoutingKey($queueName));
        } catch (\Exception $e) {
            if ($this->silent) {
                return $e->getMessage();
            }
            throw new SendException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 获取mq队列
     *
     * @param string $queueName
     * @param callable $callback
     * @return mixed
     * @throws ReceiveException
     */
    public function receive($queueName, callable $callback)
    {
        try {
            return $this->getXcmqClient($queueName)->receiveMQ($queueName, $callback);
        } catch (\Exception $e) {
            if ($this->silent) {
                return $e->getMessage();
            }
            throw new ReceiveException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 获取mq客户端
     *
     * @param string $queueName
     * @return \xcmq\Xcmq
     */
    public function getXcmqClient($queueName)
    {
        return \XcmqClient::singleton($queueName);
    }

    /**
     * 设置调用是否抛出异常
     *
     * @param bool $silent
     * @return $this
     */
    public function silent($silent = true)
    {
        $this->silent = $silent;
        return $this;
    }

    /**
     * 获取routingKey
     *
     * @param string $queueName
     * @return string
     */
    protected function getRoutingKey($queueName)
    {
        return $this->config[$queueName]['queue'][$queueName]['routingkey'];
    }
}
