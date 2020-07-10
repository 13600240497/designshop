<?php
namespace app\modules\soa\components\mq;

/**
 * mq组件接口
 */
interface MqComponentInterface
{
    /**
     * 执行入口
     *
     * @param array $data
     * @param \xcmq\module\Xcmq_Abstract $client
     * @return array
     */
    public function run($data, $client);
}
