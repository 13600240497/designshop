<?php
namespace Globalegrow\RabbitMQ;

use Globalegrow\Base\Arr;

/**
 * mq配置
 */
class Config
{
    /**
     * 生成配置
     *
     * @param array $queues
     * @return array
     */
    public static function build(array $queues)
    {
        $result = [];
        foreach ($queues as $queueName => $config) {
            if (!isset($config['connect_timeout'])) {
                $timeout = isset($_GET['connect_timeout']) ? $_GET['connect_timeout'] : 60;
                $timeout = $timeout > 0 && $timeout < 60 ? $timeout : 60;
                $config['connect_timeout'] = $timeout;
            }
            if (isset($config['queue'])) {
                $config['queue'] = [
                    $queueName => $config['queue']
                ];
            }
            $result[$queueName] = Arr::merge(
                [
                    'queue' => [
                        $queueName => [
                            'queue_name' => $queueName,
                            'durable' => true,
                            'auto_delete' => false,
                            'routingkey' => $queueName
                        ],
                    ],
                ],
                static::getDefaultConfig(),
                $config
            );
        }
        return $result;
    }

    /**
     * 默认配置
     *
     * @return array
     */
    protected static function getDefaultConfig()
    {
        return [
            'mq' => 'rabbitmqorg',
            'exchange' => [
                [
                    'exchange_name' => 'amq.direct',
                    'durable' => true,
                    'auto_delete' => false,
                    'exchange_type' => 'direct',
                ],
            ],
            'amqp_ex_type' => 'direct',
            'delivery_mode' => 2,
            'format' => 'json',
            'connect_timeout' => null,
        ];
    }
}
