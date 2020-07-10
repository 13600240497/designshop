<?php
namespace app\modules\soa\components\mq;

use app\modules\soa\components\Component;
use app\modules\soa\models\SoaIpsQueueModel;
use yii;

/**
 * 选品系统活动产品更新同步组件
 */
class IpsGoodsSkuSyncComponent extends Component implements MqComponentInterface
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
        $result = $this->allActivityRelatedUiComponentSkuSync($data);
        $client->ack();
        return $result;
    }

    /**
     * 消费MQ数据数据
     * @param string $data MQ数据，格式：{"data":{"activity_child":["186"]}}
     * @return array
     */
    public function allActivityRelatedUiComponentSkuSync($data)
    {
        $msgInfo = json_decode($data, true);
        if (!is_array($msgInfo) || !isset($msgInfo['data']) || !isset($msgInfo['data']['activity_child'])) {
            return app()->helper->arrayResult(1, '活动产品更新数据格式错误');
        }

        try {
            SoaIpsQueueModel::saveMessage($data);
            return app()->helper->arrayResult(0, '保存到数据等待定时任务处理');
        } catch (\Exception $e) {
            return app()->helper->arrayResult(1, $e->getMessage());
        }
    }

}