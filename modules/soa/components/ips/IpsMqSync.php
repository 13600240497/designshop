<?php
namespace app\modules\soa\components\ips;

use app\base\SiteConstants;
use app\modules\soa\models\SoaIpsActivitySkuModel;
use app\modules\soa\models\SoaIpsGoodsModel;

class IpsMqSync
{
    /** @var \app\modules\soa\components\IpsComponent */
    public $ipsComponent;

    /** @var array IPS 子活动SKU信息 */
    public $ipsUpdatedResult = [];

    /** @var ZaFulIpsMqSync */
    private $zaFulIpsMqSync;

    /** @var DefaultIpsMqSync */
    private $defaultIpsMqSync;

    /** @var DlIpsMqSync */
    private $DlIpsMqSync;

    public function __construct($ipsComponent)
    {
        $this->ipsComponent = $ipsComponent;
        $this->zaFulIpsMqSync = new ZaFulIpsMqSync($this);
        $this->defaultIpsMqSync = new DefaultIpsMqSync($this);
        $this->DlIpsMqSync = new DlIpsMqSync($this);
    }

    /**
     * MQ同步业务
     *
     * @param array $ipsActivityChildIds 选品变更SKU子活动列表
     * @param boolean $isPublishPage 是否需要重新发布更新页面
     * @return array 更新结果
     * @throws \Exception
     */
    public function mqSync($ipsActivityChildIds, $isPublishPage = false)
    {
        $relatedIpsMapping = SoaIpsGoodsModel::find()
            ->where(['ips_activity_id' => $ipsActivityChildIds])
            ->groupBy('ips_activity_id')
            ->indexBy('ips_activity_id')
            ->asArray()
            ->all();

        foreach ($ipsActivityChildIds as $activityChildId) {
            // 跳过没有关联的活动
            if (!isset($relatedIpsMapping[$activityChildId])) {
                continue;
            }

            // 获取IPS子活动最新SKU信息
            $this->ipsUpdatedResult[$activityChildId] = $this->ipsComponent->getSingleActivityGoodsSku($activityChildId);
        }

        // UI组件自动刷新组件直接使用缓存数据，这里直接更新SKU就可以完成
        $cachedIpsActivityIds = SoaIpsActivitySkuModel::find()->select('ips_activity_id')
            ->where(['ips_activity_id' => $ipsActivityChildIds])
            ->asArray()->column();
        foreach ($cachedIpsActivityIds as $activityChildId) {
            if (isset($this->ipsUpdatedResult[$activityChildId])) {
                continue;
            }

            $this->ipsComponent->getSingleActivityGoodsSku($activityChildId);
        }


        $allResult = [];
        if (!empty($this->ipsUpdatedResult)) {
            try {
                // 更新所有选品子活动关联组件数据
                $allResult['ui'] = $this->updateAllActivityRelatedUiComponentGoodsSku();
            } catch (\Exception $e) {
                $allResult['ui'] = 'UI组件更新失败: '. $e->getMessage();
            }
        }

        // 推送所有要更新页面
        if ($isPublishPage) {
            $allResult['online'] = $this->onlineAllPages();
        }

        return $allResult;
    }

    /**
     * 更新关联组件数据
     */
    protected function updateAllActivityRelatedUiComponentGoodsSku()
    {
        // 获取需求更新SKU关联组件
        $fzIpsGoodsModelList = $zfIpsGoodsModelList = [];
        $ipsActivityIds = array_keys($this->ipsUpdatedResult);
        $soaIpsGoodsModelList = SoaIpsGoodsModel::find()
            ->where(['ips_activity_id' => $ipsActivityIds])
            ->groupBy('website_code, component_id')
            ->all();

        // 按站点区分关联数据
        if (!empty($soaIpsGoodsModelList)) {
            /** @var \app\modules\soa\models\SoaIpsGoodsModel  $soaIpsGoodsModel */
            foreach ($soaIpsGoodsModelList as $soaIpsGoodsModel) {
                if (!empty($soaIpsGoodsModel->website_code)
                    && (SiteConstants::SITE_GROUP_CODE_ZF == $soaIpsGoodsModel->website_code))
                {
                    // ZF站点
                    $zfIpsGoodsModelList[] = $soaIpsGoodsModel;
                } elseif (!empty($soaIpsGoodsModel->website_code)
                    && (SiteConstants::SITE_GROUP_CODE_DL == $soaIpsGoodsModel->website_code))
                {
                    // D网
                    $dlIpsGoodsModelList[] = $soaIpsGoodsModel;
                } else {
                    // RG 等老
                    $fzIpsGoodsModelList[] = $soaIpsGoodsModel;
                }
            }
        }

        $uiResult = [];
        // ZaFul站点组件更新
        if (!empty($zfIpsGoodsModelList)) {
            try {
                $uiResult['zf'] = $this->zaFulIpsMqSync->updateAllActivityRelatedUiComponentGoodsSku($zfIpsGoodsModelList);
            } catch (\Exception $e) {
                $uiResult['zf'] = '更新组件错误: '. $e->getMessage();
            }
        }

        // RG站点组件更新
        if (!empty($fzIpsGoodsModelList)) {
            try {
                $uiResult['rg'] = $this->defaultIpsMqSync->updateAllActivityRelatedUiComponentGoodsSku($fzIpsGoodsModelList);
            } catch (\Exception $e) {
                $uiResult['rg'] = '更新组件错误: '. $e->getMessage();
            }
        }

        //D 网组件更新
        if (!empty($dlIpsGoodsModelList)) {
            try {
                $uiResult['dl'] = $this->DlIpsMqSync->updateAllActivityRelatedUiComponentGoodsSku($dlIpsGoodsModelList);
            } catch (\Exception $e) {
                $uiResult['dl'] = '更新组件错误: '. $e->getMessage();
            }
        }

        return $uiResult;
    }

    /**
     * 重新发布活动页面
     */
    protected function onlineAllPages()
    {
        $onlineResult = [];

        $messages = $this->zaFulIpsMqSync->onlinePages();
        !empty($messages) && $onlineResult['zf'] = $messages;

        $messages = $this->defaultIpsMqSync->onlinePages();
        !empty($messages) && $onlineResult['rg'] = $messages;

        $messages = $this->DlIpsMqSync->onlinePages();
        !empty($messages) && $onlineResult['dl'] = $messages;

        return $onlineResult;
    }

}