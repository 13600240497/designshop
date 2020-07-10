<?php
namespace app\modules\soa\components\ips;

use yii\helpers\ArrayHelper;
use app\modules\common\zf\traits\CommonPublishTrait;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PageUiModel;
use app\modules\common\zf\models\PageUiComponentDataModel;
use app\modules\common\zf\components\CommonPageUiComponentDataComponent;

class ZaFulIpsMqSync
{
    use CommonPublishTrait;

    /** @var IpsMqSync */
    public $ipsMqSync;

    /** @var array 需要重新发布的页面信息 */
    private $toPublishList;

    public function __construct($ipsMqSync)
    {
        $this->ipsMqSync = $ipsMqSync;
        $this->toPublishList = [];
    }

    /**
     * 更新关联组件数据
     *
     * @param \app\modules\soa\models\SoaIpsGoodsModel[] $zfIpsGoodsModelList
     * @return array 处理结果
     */
    public function updateAllActivityRelatedUiComponentGoodsSku(&$zfIpsGoodsModelList)
    {
        $zfIpsGoodsInfoList = ArrayHelper::toArray($zfIpsGoodsModelList);
        $pageIds = array_unique(array_column($zfIpsGoodsInfoList, 'page_id'));
        $componentIds = array_unique(array_column($zfIpsGoodsInfoList, 'component_id'));

        // 关联页面信息
        $pageModelMapping = PageModel::find()
            ->where([
                'is_delete' => PageModel::NOT_DELETE,
                'id'        => $pageIds
            ])
            ->andWhere(['>', 'activity_id', 0])
            ->andWhere(['>', 'create_time', strtotime('-6 months')])
            ->indexBy('id')
            ->all();

        // 关联组件信息
        $pageUiModelMapping = PageUiModel::find()->where(['id' => $componentIds])->indexBy('id')->all();

        $result = ['success' => [], 'fail' => []];
        foreach ($zfIpsGoodsModelList as $soaIpsGoodsModel) {

            // 跳过不存在或删除的页面或组件
            /** @var \app\modules\common\zf\models\PageModel $pageModel */
            $pageModel = $pageModelMapping[$soaIpsGoodsModel->page_id] ?? null;
            /** @var \app\modules\common\zf\models\PageUiModel $pageUiModel */
            $pageUiModel = $pageUiModelMapping[$soaIpsGoodsModel->component_id] ?? null;
            if (!$pageModel || !$pageUiModel) {
                continue;
            }

            // 事物开始
            $transaction = app()->db->beginTransaction();
            try {
                // 更新所有选品子活动关联组件数据
                $this->updateUiSku($pageModel, $pageUiModel, $soaIpsGoodsModel);
                $result['success'][$soaIpsGoodsModel->ips_activity_id] = $soaIpsGoodsModel->component_id;
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $_message = sprintf("%s in %s line %d", $e->getMessage(), $e->getFile(), $e->getLine());
                $message = sprintf('更新关联组件 %d 异常：%s', $soaIpsGoodsModel->component_id, $_message);
                \Yii::error($message, __METHOD__);
                $result['fail'][$soaIpsGoodsModel->ips_activity_id] = $message;
            }
        }

        return $result;
    }

    /**
     * 更新单个组件SKU
     *
     * @param \app\modules\common\zf\models\PageModel $pageModel
     * @param \app\modules\common\zf\models\PageUiModel $pageUiModel
     * @param \app\modules\soa\models\SoaIpsGoodsModel $soaIpsGoodsModel
     */
    protected function updateUiSku($pageModel, $pageUiModel, $soaIpsGoodsModel)
    {
        // 获取组件数据
        $uiData = PageUiComponentDataModel::getDataList($pageUiModel->id, $pageUiModel->lang, $pageUiModel->tpl_id);
        // 更新选品组件数据
        $ipsPageInfo = [
            'siteCode'  => $pageModel->site_code,
            'pageId'    => $pageModel->id,
            'lang'      => $pageUiModel->lang,
            'pipeline'  => $pageModel->pipeline
        ];
        $ipsUiInfo = ['id' => $soaIpsGoodsModel->component_id, 'key' => $soaIpsGoodsModel->component_key];
        $this->ipsMqSync->ipsComponent->tryFillUiComponentSaveFormSkuFieldDataFromIps(
            $ipsPageInfo, $ipsUiInfo, $uiData, true,$soaIpsGoodsModel->ips_activity_id
        );

        // 保存组件数据
        $uiComponentDataComponent = new CommonPageUiComponentDataComponent();
        if (!$uiComponentDataComponent->insertPageUiComponentData($pageUiModel->id, $uiData, $pageUiModel->tpl_id, $pageUiModel->lang)) {
            return;
        }

        // 没有上线的页面跳过发布
        if (PageModel::PAGE_STATUS_HAS_ONLINE !== $pageModel->status) {
            return;
        }

        // 记录待重新发布活动页面信息
        $activityId = isset($pageModel->activity_id) ? $pageModel->activity_id : 0;
        $_publishInfo = [$activityId, $soaIpsGoodsModel->page_id, $pageUiModel->lang];
        $publishKey = implode('-', $_publishInfo);
        if (!isset($this->toPublishList[$publishKey])) {
            $this->toPublishList[$publishKey] = $_publishInfo;
        }
    }

    /**
     * 推送页面
     */
    public function onlinePages()
    {
        if (empty($this->toPublishList)) {
            return null;
        }

        // 推送所有要更新页面
        $messages = [];
        foreach ($this->toPublishList as $publishInfo) {
            list($activityId, $pageId, $lang) = $publishInfo;
            try {
                // 更新所有选品子活动关联组件数据
                $pageMessage = $this->publishSinglePage($activityId, $pageId, $lang);
                if (empty($pageMessage)) {
                    $messages[] = sprintf('发布页面 %d 语言 %s 成功', $pageId, $lang);
                } else {
                    $messages[] = sprintf('发布页面 %d 语言 %s 失败: %s', $pageId, $lang, $pageMessage);
                }
            } catch (\Throwable $e) {
                $_message = sprintf("%s in %s line %d", $e->getMessage(), $e->getFile(), $e->getLine());
                $message = sprintf('发布页面 %d 语言 %s 异常: %s', $pageId, $lang, $_message);
                \Yii::error($message, __METHOD__);
                $messages[] = $message;
            }
        }

        return $messages;
    }

    private function publishSinglePage($activityId, $pageId, $lang)
    {
        list($success, $errorMessages) = $this->batchCreateOnlinePageHtml(
            [$pageId], $activityId, true, true, true, '', false, [$lang]
        );
        if (!$success) {
            return json_encode($errorMessages, JSON_UNESCAPED_UNICODE);
        }
        return null;
    }
}