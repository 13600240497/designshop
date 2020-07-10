<?php
namespace app\modules\soa\components\mq;

use app\modules\soa\components\Component;
use app\modules\soa\components\ObsComponent;
use yii;

/**
 * 选品系统活动产品更新同步组件
 */
class ObsGoodsSyncComponent extends Component implements MqComponentInterface
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run($data, $client)
    {
        $result = $this->handle($data);
        $client->ack();
        if (0 !== $result['code']) {
            Yii::error($result);
        }
        return $result;
    }

    /**
     * 消费MQ数据数据
     * @param string $data MQ数据，
     * @return array
     */
    public function handle($data)
    {   
        $ObsComponent = new ObsComponent();
        if (!is_array($data) || !isset($data['section_id'])) {
            Yii::warning(['活动产品更新数据格式错误', $data], 'obs_goods_sync');
            return app()->helper->arrayResult(1, '活动产品更新数据格式错误', $data);
        }

        try {
            $ObsComponent->updateMqGoodData($data['section_id']);
            $result = true;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage(), 'obs_goods_sync');
            $result = $e->getMessage();
        }

        return app()->helper->arrayResult(
            true === $result ? 0 : 2,
            true === $result ? 'success' : $result,
            $data
        );
    }

}