<?php
namespace app\modules\soa\components;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * mq消费组件
 */
class MqComponent extends Component
{
    /**
     * @var array MQ消费组件定义
     */
    protected static $components = [
        //'ips' => __NAMESPACE__ . '\mp\IpsComponent',
    ];

    /**
     * 选品系统活动产品SKU同步
     *
     * @return array
     */
    public static function ipsGoodsSkuSync()
    {
        return static::receive('activityChild_GES', 'ips_goods_sku_sync');
    }

    /**
     * 消费obs推送mq
     * @return array
     */
    public static function obsGoodsSync()
    {
        return static::receive('themeGoods_GES', 'obs_goods_sync');
    }

    /**
     * MQ消费
     * @param string $queueName     队列名称
     * @param string $componentName 消费组件名称
     * @return array
     */
    protected static function receive($queueName, $componentName)
    {
        $result = ['receive' => []];

        try {
            app()->mq->receive(
                $queueName,
                function ($data, $client) use (&$result, $componentName, $queueName) {
                    $data = [
                        'name' => $componentName,
                        'data' => $data,
                    ];
                    $result['receive'][] = static::callback($queueName, $data, $client);
                }
            );
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return app()->helper->arrayResult(0, 'success', $result);
    }

    /**
     * 回调
     *
     * @param string $queueName     队列名称
     * @param array $data
     * @param \xcmq\module\Xcmq_Abstract $client
     * @return array
     */
    public static function callback($queueName, $data, $client)
    {
        $dataString = json_encode(ArrayHelper::toArray($data));
        try {
            Yii::info(sprintf('消费MQ队列[%s]接收到数据：%s', $queueName, $dataString), __METHOD__);

            $result = call_user_func(
                [static::getComponents($data['name']), 'run'],
                $data['data'],
                $client
            );

            Yii::info(sprintf('消费MQ队列[%s]处理数据：%s,返回:%s', $queueName, $dataString, json_encode($result)), __METHOD__);
            return $result;
        } catch (\Exception $e) {
            Yii::error(sprintf('消费MQ队列[%s]处理数据：%s,错误:%s', $queueName, $dataString, $e->getMessage()), __METHOD__);
            return app()->helper->arrayResult(-1, $e->getMessage());
        }
    }

    /**
     * 获取组件
     *
     * @param string $name
     * @return MqComponentInterface
     */
    protected static function getComponents($name)
    {
        if (!isset(static::$components[$name])) {
            static::$components[$name] = Yii::createObject(
                __NAMESPACE__ . '\\mq\\' . app()->helper->str->toUpperCamelCase($name, '_') . 'Component'
            );
        } elseif (is_string(static::$components[$name])) {
            static::$components[$name] = Yii::createObject(static::$components[$name]);
        }
        return static::$components[$name];
    }
}
