<?php
namespace app\modules\soa\controllers;

use app\base\RequestUtils;
use app\modules\soa\components\MqComponent;
use app\modules\soa\components\IpsComponent;
use ego\base\JsonResponseException;

/**
 * mq控制器
 */
class MqController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        \yii\web\Controller::afterAction($action, $result);
    }

    /**
     * 选品系统活动产品SKU同步
     *
     * @return array
     */
    public function actionIpsGoodsSkuSync()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        return MqComponent::ipsGoodsSkuSync();
    }

    /**
     * MQ消费选品系统时只是将分类消息保存到数据库，具体消费逻辑在这里
     */
    public function actionIpsGoodsSkuSyncProcess()
    {
        ini_set('memory_limit', '1G');

        $lockKey = 'geshop:mq:ips-goods-sky-sync:lock';
        if (null === app()->redis->set($lockKey, 1, 'EX', 3600, 'NX')) {
            throw new JsonResponseException(1, '选品系统活动产品SKU同步任务后台运行中，无需再次操作');
        }

        app()->session->open();
        RequestUtils::closeConnectionAndFlush('选品系统活动产品SKU同步任务切入后台运行，请去日志中查看结果');
        app()->response->isSent = true;

        set_time_limit(0);
        ignore_user_abort(true);

        try {
            $ipsComponent = new IpsComponent();
            $ipsComponent->consumeMqFromDatabaseQueue();
        } catch (\Throwable $throwable) {
            \Yii::error('选品系统活动产品SKU同步任务错误： '. $throwable->getMessage(), __METHOD__);
        } finally {
            app()->redis->del($lockKey); // 删除锁
        }
    }

    /**
     * 消费obs更新记录
     * @return array
     */
    public function actionObsGoodsSync()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        return MqComponent::obsGoodsSync();
    }
}
