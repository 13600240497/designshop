<?php
namespace app\modules\soa\components\ips;

use app\base\SiteConstants;
use app\base\SitePlatform;

use app\modules\soa\models\SoaIpsGoodsModel;
use ego\base\JsonResponseException;

class MultiLevelIps extends AbstractLevelIps
{
    public function __construct($ipsComponent)
    {
        parent::__construct($ipsComponent);
    }

    /**
     * 在 ExplainComponent.php 渲染组件时尝试从IPS获取商品SKU
     * @return array 如果组件SKU数据来自IPS，返回SKU列表； 否则返回NULL
     */
    public function tryGetGoodsSkuFromIps()
    {
        $goodSku = $this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU];
        foreach ($goodSku as &$tableSkuInfo) {
            // 如果商品SKU来源选品系统
            if ($this->isSkuFromIps($tableSkuInfo)) {
                $isAsync = $tableSkuInfo['isAsync'] ?? 0; // 是否异步渲染
                if (1 === (int)$isAsync) {
                    $tableSkuInfo['lists'] = '';
                } else {
                    $ipsMethods = (int)$tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS];
                    // 筛选器与与之前的数据结构不一样
                    if (in_array($ipsMethods, [SoaIpsGoodsModel::RULE_TYPE_IPS_RULE, SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER], true)) {
                        $tableSkuInfo['lists'] = $tableSkuInfo['ips'][static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU];
                    } else {
                        $tableSkuInfo['lists'] = $tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '';
                    }
                }
            }
        }

        return $goodSku;
    }

    /**
     * 商品显示数量限制处理
     * @param array $goodsInfo
     */
    public function goodsNumLimitProcess(&$goodsInfo)
    {
        if (empty($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])
            || !is_array($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])) {
            return;
        }

        foreach ($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU] as $_key => $tableSkuInfo) {
            // 如果商品SKU来源选品系统
            if ($this->isSkuFromIps($tableSkuInfo)) {
                // IPS规则判断
                if ($this->isIpsAutoRule($tableSkuInfo) == SoaIpsGoodsModel::RULE_TYPE_AUTO && !empty($goodsInfo[$_key]['lists'])) {
                    // 要过滤的商品信息数组
                    $goodsInfoMapping = array_column($goodsInfo[$_key]['lists'], NULL, 'goods_sn');

                    // 根据规则配置的SKU限制数量，获取最终显示商品信息
                    $showGoodsInfo = [];
                    foreach ($tableSkuInfo['ips']['level3'] as $ruleInfo) {
                        if (!isset($ruleInfo['id']) || empty($ruleInfo['id']) || (0 === (int)$ruleInfo['id']))
                            continue;

                        $ipsActivityChildId = $ruleInfo['id'];

                        if (!isset($tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU][$ipsActivityChildId]))
                            continue;

                        if (!empty($ruleInfo['sku_num'])) {
                            // 根据配置显示限定数量的商品信息
                            $goodsSkuCount = 0;
                            $goodsSkuLimit = $ruleInfo['sku_num'];
                            $goodsSkuStr = $tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU][$ipsActivityChildId];
                            $goodsSkuArr = explode(SiteConstants::CHAR_COMMA, $goodsSkuStr);
                            foreach ($goodsSkuArr as $goodsSku) {
                                if (isset($goodsInfoMapping[$goodsSku])) {
                                    $showGoodsInfo[] = $goodsInfoMapping[$goodsSku];
                                    $goodsSkuCount++;
                                    if ($goodsSkuCount >= $goodsSkuLimit) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // 将最终要显示的商品数组回填
                    if(!empty($showGoodsInfo)) {
                        $goodsInfo[$_key]['lists'] = $showGoodsInfo;
                    }
                }elseif ($this->isIpsAutoRule($goodsInfo) == SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER ||
                    $this->isIpsAutoRule($goodsInfo) == SoaIpsGoodsModel::RULE_TYPE_IPS_RULE){
                    //自动规则，删选器选品
                }
            }
        }
    }

    /**
     * 填充UI组件设计页面表单提交数据中的SKU
     * @param boolean $isMqSync 是否MQ数据同步
     * @param int $ips_activity_child_id
     * @throws JsonResponseException
     */
    public function fillUiComponentSaveFormSkuFieldDataFromIps($isMqSync=false, $ips_activity_child_id=0)
    {
        if (empty($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])
            || !is_array($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])) {
            return;
        }

        $gIpsActivityList = [];

        //处理选择的IPS分类
        foreach ($this->uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU] as &$tableSkuInfo) {
            // 如果商品SKU来源选品系统
            if ($this->isSkuFromIps($tableSkuInfo)) {

                // IPS规则判断
                if ($this->isIpsAutoRule($tableSkuInfo) == SoaIpsGoodsModel::RULE_TYPE_AUTO) {
                    // 自动规则
                    $ipsRuleType = SoaIpsGoodsModel::RULE_TYPE_AUTO;
                    $ipsActivitySkuInfoMapping = []; // 保存IPS规则SKU信息
                    $allSkuString = '';

                    foreach ($tableSkuInfo['ips']['level3'] as $ruleInfo) {
                        if (!isset($ruleInfo['id']) || empty($ruleInfo['id']) || (0 === (int)$ruleInfo['id']))
                            continue;

                        $ipsActivityChildId = $ruleInfo['id'];

                        // 获取IPS活动SKU和更新时间
                        $useCache = $isMqSync ? true : false;
                        $ipsActivitySkuInfo = $this->ipsComponent->getSingleActivityGoodsSku($ipsActivityChildId, $useCache);
                        $ipsActivitySkuInfoMapping[$ipsActivityChildId] = $ipsActivitySkuInfo;

                        // GESHOP活动组件和IPS活动关联
                        $ipsActivityInfo = ['activityChildId' => $ipsActivityChildId, 'ruleType' => $ipsRuleType];
                        $gIpsActivityList[] = [$ipsActivityInfo, $ipsActivitySkuInfo];

                        if (!empty($ipsActivitySkuInfo[0])) {
                            $allSkuString .= SiteConstants::CHAR_COMMA .$ipsActivitySkuInfo[0];
                        }
                    }

                    // 获取站点商品信息
                    $siteGoodsInfo = $this->getSiteGoodsInfo($allSkuString);

                    // 根据组件配置获取显示SKU列表
                    $uiRuleGoodsSKU = [];
                    $uiPrepareGoodsSku = []; // UI组件预备有效SKU列表
                    foreach ($tableSkuInfo['ips']['level3'] as $ruleInfo) {
                        if (!isset($ruleInfo['id']) || empty($ruleInfo['id']) || (0 === (int)$ruleInfo['id'])) {
                            continue;
                        }

                        $ipsActivitySkuInfo = $ipsActivitySkuInfoMapping[$ruleInfo['id']];
                        // IPS规则没有SKU
                        if (empty($ipsActivitySkuInfo[0])) {
                            continue;
                        }

                        $rulePrepareGoodsSku = []; // 规则预备有效SKU列表
                        if (empty($ruleInfo['sku_num'])) {
                            $goodsSkuArr = explode(SiteConstants::CHAR_COMMA, $ipsActivitySkuInfo[0]);
                            foreach ($goodsSkuArr as $goodsSku) {
                                if (isset($siteGoodsInfo[$goodsSku])) {
                                    $rulePrepareGoodsSku[] = $goodsSku;
                                }
                            }
                        } else {
                            // 获取有效的SKU信息
                            //!!!! 注意这里为了防止IPS自动规则SKU过多，而且显示的数量不的的情况，导致请求商品过多，优化处理
                            //!!!! 保留几个多余有效SKU，以便下架补充
                            $goodsSkuCount = 0;
                            $skuNumLimit = $ruleInfo['sku_num'];
                            $isAsync = $tableSkuInfo['isAsync'] ?? 0; // 是否异步渲染
                            $maxPrepareSkuNum = $skuNumLimit;
                            if (1 != $isAsync) {
                                $maxPrepareSkuNum = $skuNumLimit + static::IPS_RULE_PREPARE_AUGMENT_NUM; // 最大预备SKU数量
                            }
                            $goodsSkuArr = explode(SiteConstants::CHAR_COMMA, $ipsActivitySkuInfo[0]);
                            if ($skuNumLimit > count($goodsSkuArr)) {
                                $message = sprintf(
                                    '选品系统 %s 下子活动 %s SKU数量不足!',
                                    $tableSkuInfo['ips']['level1']['name'] ?? '',
                                    $ruleInfo['name'] ?? ''
                                );
                                throw new JsonResponseException(1, $message);
                            }

                            foreach ($goodsSkuArr as $goodsSku) {
                                if (isset($siteGoodsInfo[$goodsSku])) {
                                    $rulePrepareGoodsSku[] = $goodsSku;
                                    $goodsSkuCount++;
                                    if ($goodsSkuCount >= $maxPrepareSkuNum) {
                                        break;
                                    }
                                }
                            }
                        }

                        if (!empty($rulePrepareGoodsSku)) {
                            $uiRuleGoodsSKU[$ruleInfo['id']] = join(SiteConstants::CHAR_COMMA, $rulePrepareGoodsSku);
                            $uiPrepareGoodsSku = array_merge($uiPrepareGoodsSku, $rulePrepareGoodsSku);
                        }
                    }

                    // 保存最终预备商品SKU到组件配置中
                    $tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU] = $uiRuleGoodsSKU;
                    $tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = trim(
                        join(SiteConstants::CHAR_COMMA, $uiPrepareGoodsSku), SiteConstants::CHAR_COMMA
                    );

                } elseif($this->isIpsAutoRule($tableSkuInfo) == SoaIpsGoodsModel::RULE_TYPE_MANUAL) {
                    // 手动规则
                    $ipsRuleType = SoaIpsGoodsModel::RULE_TYPE_MANUAL;

                    // 获取IPS活动SKU和更新时间
                    $useCache = $isMqSync ? true : false;
                    $ipsActivityChildId = $tableSkuInfo['ips']['gsSelectLevel2'];
                    $ipsActivitySkuInfo = $this->ipsComponent->getSingleActivityGoodsSku($ipsActivityChildId, $useCache);

                    // GESHOP活动组件和IPS活动关联
                    $ipsActivityInfo = ['activityChildId' => $ipsActivityChildId, 'ruleType' => $ipsRuleType];
                    $gIpsActivityList[] = [$ipsActivityInfo, $ipsActivitySkuInfo];

                    // 排序
                    $oldOrderedSkuList = $this->getOrderedActivityGoodsSkuList(
                        $tableSkuInfo['ips'][static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '',
                        $ipsActivitySkuInfo[0]
                    );

                    // 保存最终预备商品SKU到组件配置中
                    $tableSkuInfo[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = trim($oldOrderedSkuList, SiteConstants::CHAR_COMMA);
                }else{
                    //规则增加，删选器选品
                    if($isMqSync){
                        // 手动规则
                        $ipsRuleType = SoaIpsGoodsModel::RULE_TYPE_MANUAL;

                        // 获取IPS活动SKU和更新时间
                        $useCache = $isMqSync ? true : false;
                        $ipsActivityChildId = (int)$ips_activity_child_id;
                        $ipsActivitySkuInfo = $this->ipsComponent->getSingleActivityGoodsSku($ipsActivityChildId, $useCache);
                        // GESHOP活动组件和IPS活动关联
                        if($this->isIpsAutoRule($tableSkuInfo) == SoaIpsGoodsModel::RULE_TYPE_IPS_RULE){
                            $ipsActivityInfo = ['activityChildId' => $ipsActivityChildId, 'ruleType' => SoaIpsGoodsModel::RULE_TYPE_AUTO];
                            // GESHOP活动组件和IPS活动关联
                            $gIpsActivityList[] = [$ipsActivityInfo, $ipsActivitySkuInfo];

                            // 排序
                            $oldOrderedSkuList = $this->getOrderedActivityGoodsSkuList(
                                $tableSkuInfo['ips'][static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '',
                                $ipsActivitySkuInfo[0]
                            );

                            // 保存最终预备商品SKU到组件配置中
                            $tableSkuInfo['ips']['ipsFilterInfo'] = ['ips_manual_sku'=>$oldOrderedSkuList];
                            $tableSkuInfo['ips'][static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = $oldOrderedSkuList;
                        }

                    }
                }
            }
        }

        // 保存UI组件和IPS所有使用活动关联关系
        if (!$isMqSync) {
            $this->relatedGoodsListUiComponent($gIpsActivityList);
        }
    }

    /**
     * 组件SKU是否来自IPS
     * @param array $tableSkuInfo Tab组件单个Tab项SKU配置信息
     * @return boolean
     */
    private function isSkuFromIps($tableSkuInfo)
    {
        if (is_array($tableSkuInfo)
            && isset($tableSkuInfo['skuFrom'], $tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS], $tableSkuInfo['ips'])
            && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$tableSkuInfo['skuFrom'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * 是否为自动规则
     * @param array $tableSkuInfo Tab组件单个Tab项SKU配置信息
     * @return int
     */
    private function isIpsAutoRule($tableSkuInfo)
    {
        // 自动规则是后面加入，老活动组件数据没有规则类型字段
        if (isset($tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_AUTO === (int)$tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_AUTO;
        } elseif (isset($tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_IPS_RULE === (int)$tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_IPS_RULE;
        } elseif (isset($tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER === (int)$tableSkuInfo[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER;
        } else {
            return SoaIpsGoodsModel::RULE_TYPE_MANUAL;
        }
    }
}