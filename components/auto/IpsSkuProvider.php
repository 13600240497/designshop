<?php
namespace app\components\auto;

use app\modules\soa\models\SoaIpsActivitySkuModel;

/**
 * UI组件SKU来源 - 选品系统提供者
 *
 * @author TianHaisen
 * @since 1.9.3
 */
class IpsSkuProvider
{
    const RULE_TYPE_AUTO            = 1; // 规则类型 - 自动规则
    const RULE_TYPE_MANUAL          = 2; // 规则类型 - 手动规则
    const RULE_TYPE_FILTER_AUTO     = 3; // 规则类型 - 规则添加（自动）
    const RULE_TYPE_FILTER_MANUAL   = 4; // 规则类型 - 筛选器添加（手动）

    /**
     * 验证选品规则类型是否合法
     *
     * @param int $ipsMethod 选品规则类型
     * @return bool
     */
    public static function isValidIpsMethod($ipsMethod)
    {
        $IpsMethods = [
            self::RULE_TYPE_AUTO,
            self::RULE_TYPE_MANUAL,
            self::RULE_TYPE_FILTER_AUTO,
            self::RULE_TYPE_FILTER_MANUAL
        ];

        if (in_array($ipsMethod, $IpsMethods, true)) {
            return true;
        }
        return false;
    }

    /**
     * 对选品规则与处理, 将选品规则SKU缓存到数据库中，后续渲染时使用缓存数据
     * 注意： 缓存数据后续同步更新是通过MQ来更新的，选品子活动SKU更新后，MQ通知，我们系统消费更新数据缓存
     *
     * @param int $ipsMethod 选品规则类型
     * @param array $ipsInfo 选品规则信息
     * @see \app\modules\soa\components\IpsComponent::consumeMqFromDatabaseQueue
     * @throws AutoRefreshException
     */
    public static function prepareIpsRule($ipsMethod, $ipsInfo)
    {
        //$filterRules = [self::RULE_TYPE_FILTER_AUTO, self::RULE_TYPE_FILTER_MANUAL];
        if (self::RULE_TYPE_FILTER_AUTO === $ipsMethod) {
            // 筛选器自动规则的SKU直接返回的，所有这里直接将SKU缓存到数据库中，以方便后续MQ更新
            foreach ($ipsInfo as $ruleInfo) {
                $activityChildId = $ruleInfo['id'];
                $soaIpsActivitySkuModel = SoaIpsActivitySkuModel::getByIpsActivityId($activityChildId);
                if (!$soaIpsActivitySkuModel) {
                    $soaIpsActivitySkuModel = new SoaIpsActivitySkuModel();
                    $soaIpsActivitySkuModel->ips_activity_id = $activityChildId;
                }

                $soaIpsActivitySkuModel->sku_list = $ruleInfo[AutoRefreshUi::KEY_GOODS_SKU] ?? '';
                $soaIpsActivitySkuModel->sku_update_time = time();
                if (!$soaIpsActivitySkuModel->save(true)) {
                    $message = sprintf('选品规则类型 %d 选品子活动ID %d SKU缓存保存失败', $ipsMethod, $activityChildId);
                    throw new AutoRefreshException($message);
                }
            }
        } elseif (self::RULE_TYPE_FILTER_MANUAL === $ipsMethod) {
            // 筛选器手动规则的SKU不做预出来，后续IPS变化geshop不更新
            return;
        } else {
            /** @var \app\modules\soa\components\IpsComponent $ipsComponent */
            $ipsComponent = AutoRefreshUtils::getInstance('app\modules\soa\components\IpsComponent');
            foreach ($ipsInfo as $ruleInfo) {
                // 通过接口获取最新SKU列表， 这里第二参数传 false 不使用缓存，更新规则最新SKU并缓存数据库中
                $ipsComponent->getSingleActivityGoodsSku($ruleInfo['id'], false);
            }
        }
    }

    /**
     * 获取选品规则SKU
     *
     * @param int $ipsMethod 选品规则类型
     * @param array $ipsInfo 选品规则信息
     * @return array
     * @see IpsSkuProvider::prepareIpsRule
     */
    public static function getGoodsSkuList($ipsMethod, $ipsInfo)
    {
        $ruleSkuList = [];

        /** @var \app\modules\soa\components\IpsComponent $ipsComponent */
        $ipsComponent = AutoRefreshUtils::getInstance('app\modules\soa\components\IpsComponent');
        foreach ($ipsInfo as $ruleInfo) {
            $activityChildId = $ruleInfo['id'];

            if (self::RULE_TYPE_FILTER_MANUAL === $ipsMethod) {
                // 筛选器手动规则的SKU，直接使用固定SKU
                $goodsSku = $ruleInfo[AutoRefreshUi::KEY_GOODS_SKU] ?? '';
            } else {
                // 获取SKU列表，这里第二个参数 true，表示使用 prepareIpsRule 函数缓存的数据
                list($goodsSku,) = $ipsComponent->getSingleActivityGoodsSku($activityChildId, true);
            }

            $ruleSkuList[] = [
                'id'                         => $ruleInfo['id'],
                'skuLimit'                   => isset($ruleInfo['sku_num']) ? (int)$ruleInfo['sku_num'] : 0,
                AutoRefreshUi::KEY_GOODS_SKU => $goodsSku,
            ];
        }

        return $ruleSkuList;
    }


}