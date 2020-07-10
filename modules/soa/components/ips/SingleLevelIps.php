<?php
namespace app\modules\soa\components\ips;

use app\base\SiteConstants;
use app\base\SitePlatform;

use app\modules\soa\models\SoaIpsGoodsModel;
use ego\base\JsonResponseException;

class SingleLevelIps extends AbstractLevelIps
{
    /** @var string UI组件选品系统SKU保存数据字段 */
    const UI_COMPONENT_FIELD_KEY_GOODS_DATA_FROM = 'goodsDataFrom';

    /** @var int 单个规则SKU最大数量 */
    const MAX_SINGLE_RULE_SKU_NUM = 100;

    public function __construct($ipsComponent)
    {
        parent::__construct($ipsComponent);
    }

    /**
     * 在 ExplainComponent.php 渲染组件时尝试从IPS获取商品SKU
     * @return mixed 如果组件SKU数据来自IPS，返回SKU列表； 否则返回NULL
     */
    public function tryGetGoodsSkuFromIps()
    {
        // 如果商品SKU来源选品系统
        if ($this->isSkuFromIps()) {
            $isAsync = $tableSkuInfo['isAsync'] ?? 0; // 是否异步渲染
            if (1 !== (int)$isAsync) {
                if (array_key_exists(static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU, $this->uiData)) {
                    return $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU];
                }
            }
        }
        return NULL;
    }

    /**
     * 商品显示数量限制处理
     * @param array $goodsInfo
     */
    public function goodsNumLimitProcess(&$goodsInfo)
    {
        // 要过滤的商品信息数组
        $goodsInfoMapping = array_column($goodsInfo, NULL, 'goods_sn');

        // 如果商品SKU来源选品系统
        if ($this->isSkuFromIps()) {
            // IPS规则判断
            if ($this->isIpsAutoRule() == SoaIpsGoodsModel::RULE_TYPE_AUTO) {
                if (isset($this->uiData['ipsAutoInfo'])) {
                    $ipsAutoInfo = $this->uiData['ipsAutoInfo'];

                    // 根据规则配置的SKU限制数量，获取最终显示商品信息
                    if (!isset($ipsAutoInfo['level3']))
                        return;
                    
                    $showGoodsInfo = [];
                    foreach ($ipsAutoInfo['level3'] as $ruleInfo) {
                        if (!isset($ruleInfo['id']) || empty($ruleInfo['id']) || (0 === (int)$ruleInfo['id']))
                            continue;

                        $ipsActivityChildId = $ruleInfo['id'];

                        if (!isset($this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU][$ipsActivityChildId]))
                            continue;

                        if (!empty($ruleInfo['sku_num'])) {
                            // 根据配置显示限定数量的商品信息
                            $goodsSkuCount = 0;
                            $goodsSkuLimit = $ruleInfo['sku_num'];
                            $goodsSkuStr = $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU][$ipsActivityChildId];
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
                        $goodsInfo = $showGoodsInfo;
                    }
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
    public function fillUiComponentSaveFormSkuFieldDataFromIps($isMqSync = false, $ips_activity_child_id = 0)
    {
        $gIpsActivityList = [];

        // 如果商品SKU来源选品系统
        if ($this->isSkuFromIps()) {

            // IPS规则判断
            if ($this->isIpsAutoRule()==SoaIpsGoodsModel::RULE_TYPE_AUTO) {
                $isAsync = (int)($this->uiData['isAsync'] ?? 0); // 是否异步渲染
                // 自动规则
                $ipsRuleType = SoaIpsGoodsModel::RULE_TYPE_AUTO;
                $ipsActivitySkuInfoMapping = []; // 保存IPS规则SKU信息
                if (isset($this->uiData['ipsAutoInfo'])) {
                    $ipsAutoInfo = $this->uiData['ipsAutoInfo'];

                    $allSkuString = '';
                    foreach ($ipsAutoInfo['level3'] as $ruleInfo) {
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
                    foreach ($ipsAutoInfo['level3'] as $_ruleKey => $ruleInfo) {
                        if (!isset($ruleInfo['id']) || empty($ruleInfo['id']) || (0 === (int)$ruleInfo['id']))
                            continue;

                        $ipsActivitySkuInfo = $ipsActivitySkuInfoMapping[$ruleInfo['id']];
                        // IPS规则没有SKU
                        if (empty($ipsActivitySkuInfo[0])) {
                            continue;
                        }

                        $rulePrepareGoodsSku = []; // 规则预备有效SKU列表
                        if (empty($ruleInfo['sku_num'])) {
                            $goodsSkuArr = explode(SiteConstants::CHAR_COMMA, $ipsActivitySkuInfo[0]);
                            $goodsSkuCount = 0;
                            foreach ($goodsSkuArr as $goodsSku) {
                                if (isset($siteGoodsInfo[$goodsSku])) {
                                    $rulePrepareGoodsSku[] = $goodsSku;
                                    $goodsSkuCount++;
                                    if ($goodsSkuCount >= self::MAX_SINGLE_RULE_SKU_NUM) {
                                      break;
                                    }

                                }
                            }
                        } else {
                            // 获取有效的SKU信息
                            //!!!! 注意这里为了防止IPS自动规则SKU过多，而且显示的数量不的的情况，导致请求商品过多，优化处理
                            //!!!! 保留几个多余有效SKU，以便下架补充
                            $goodsSkuCount = 0;
                            $skuNumLimit = (int)$ruleInfo['sku_num'];
                            if ($skuNumLimit > self::MAX_SINGLE_RULE_SKU_NUM) $skuNumLimit = self::MAX_SINGLE_RULE_SKU_NUM; // 单个规则最大SKU个数限制
                            // 异步渲染组件不预留SKU
                            $maxPrepareSkuNum = $isAsync ? $skuNumLimit : $skuNumLimit + static::IPS_RULE_PREPARE_AUGMENT_NUM; // 最大预备SKU数量
                            $goodsSkuArr = explode(SiteConstants::CHAR_COMMA, $ipsActivitySkuInfo[0]);
                            if ($skuNumLimit > count($goodsSkuArr)) {
                                $message = sprintf(
                                    '选品系统 %s 下子活动 %s SKU数量不足!',
                                    $ipsAutoInfo['level1']['name'] ?? '',
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
                    $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU] = $uiRuleGoodsSKU;

                    $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = trim(
                        join(SiteConstants::CHAR_COMMA, $uiPrepareGoodsSku), SiteConstants::CHAR_COMMA
                    );
                }

            } elseif($this->isIpsAutoRule()==SoaIpsGoodsModel::RULE_TYPE_MANUAL) {
                // 手动规则
                $ipsRuleType = SoaIpsGoodsModel::RULE_TYPE_MANUAL;
                if (isset($this->uiData['gsSelectLevel2']) && is_numeric($this->uiData['gsSelectLevel2'])) {

                    // 获取IPS活动SKU和更新时间
                    $useCache = $isMqSync ? true : false;
                    $ipsActivityChildId = (int)$this->uiData['gsSelectLevel2'];
                    $ipsActivitySkuInfo = $this->ipsComponent->getSingleActivityGoodsSku($ipsActivityChildId, $useCache);

                    // GESHOP活动组件和IPS活动关联
                    $ipsActivityInfo = ['activityChildId' => $ipsActivityChildId, 'ruleType' => $ipsRuleType];
                    $gIpsActivityList[] = [$ipsActivityInfo, $ipsActivitySkuInfo];

                    // 获取IPS活动SKU列表,并排序
                    $goodsSkuList = $ipsActivitySkuInfo[0] ?? '';
//                    $oldOrderedSkuList = $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '';
//                    $goodsSkuList = $this->getOrderedActivityGoodsSkuList($oldOrderedSkuList, $goodsSkuList);

                    // 限制单个规则最大SKU数量100
                    $goodsSkuList = trim($goodsSkuList, SiteConstants::CHAR_COMMA);
                    $_goodsSkuList = explode(SiteConstants::CHAR_COMMA, $goodsSkuList);
                    if (count($_goodsSkuList) > self::MAX_SINGLE_RULE_SKU_NUM) {
                        // 获取站点商品信息
                        $siteGoodsInfo = $this->getSiteGoodsInfo($goodsSkuList);
                        $_goodsSkuList = array_keys($siteGoodsInfo);
                        if (count($_goodsSkuList) > self::MAX_SINGLE_RULE_SKU_NUM) {
                          $_goodsSkuList = array_slice($_goodsSkuList, 0, self::MAX_SINGLE_RULE_SKU_NUM);
                        }
                        $goodsSkuList = join(SiteConstants::CHAR_COMMA, $_goodsSkuList);
                    }

                    // 保存最终预备商品SKU到组件配置中
                    $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = trim($goodsSkuList, SiteConstants::CHAR_COMMA);
                }
            }else{
                if($isMqSync){
                    // 获取IPS活动SKU和更新时间
                    $useCache = $isMqSync ? true : false;
                    $ipsActivitySkuInfo = $this->ipsComponent->getSingleActivityGoodsSku((int)$ips_activity_child_id, $useCache);

                    // GESHOP活动组件和IPS活动关联
                    if($this->isIpsAutoRule()==SoaIpsGoodsModel::RULE_TYPE_IPS_RULE){
                        $ipsActivityInfo = ['activityChildId' => $ips_activity_child_id, 'ruleType' => SoaIpsGoodsModel::RULE_TYPE_AUTO];
                        $gIpsActivityList[] = [$ipsActivityInfo, $ipsActivitySkuInfo];

                        // 获取IPS活动SKU列表,并排序
                        $goodsSkuList = $ipsActivitySkuInfo[0] ?? '';
//                        $oldOrderedSkuList = $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '';
//                        $goodsSkuList = $this->getOrderedActivityGoodsSkuList($oldOrderedSkuList, $goodsSkuList);

                        // 限制单个规则最大SKU数量100
                        $goodsSkuList = trim($goodsSkuList, SiteConstants::CHAR_COMMA);
                        $_goodsSkuList = explode(SiteConstants::CHAR_COMMA, $goodsSkuList);
                        if (count($_goodsSkuList) > self::MAX_SINGLE_RULE_SKU_NUM) {
                            // 获取站点商品信息
                            $siteGoodsInfo = $this->getSiteGoodsInfo($goodsSkuList);
                            $_goodsSkuList = array_keys($siteGoodsInfo);
                            if (count($_goodsSkuList) > self::MAX_SINGLE_RULE_SKU_NUM) {
                                $_goodsSkuList = array_slice($_goodsSkuList, 0, self::MAX_SINGLE_RULE_SKU_NUM);
                            }
                            $goodsSkuList = join(SiteConstants::CHAR_COMMA, $_goodsSkuList);
                        }

                        // 保存最终预备商品SKU到组件配置中
                        $this->uiData[static::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] = $goodsSkuList;
                        $this->uiData['ipsFilterInfo']['ips_auto_sku'] = $goodsSkuList;
                        
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
     * 是否为自动规则
     * @return int
     */
    private function isIpsAutoRule()
    {
        // 自动规则是后面加入，老活动组件数据没有规则类型字段
        if (isset($this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_AUTO === (int)$this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_AUTO;
        } elseif (isset($this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_MANUAL === (int)$this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_MANUAL;
        } elseif (isset($this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
            && (SoaIpsGoodsModel::RULE_TYPE_IPS_RULE === (int)$this->uiData[self::UI_COMPONENT_FIELD_KEY_IPS_METHODS])
        ) {
            return SoaIpsGoodsModel::RULE_TYPE_IPS_RULE;
        } else {
            return SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER;
        }

    }

    /**
     * 组件SKU是否来自IPS
     * @return boolean
     */
    private function isSkuFromIps()
    {
        if (isset($this->uiData[static::UI_COMPONENT_FIELD_KEY_GOODS_DATA_FROM])
            && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$this->uiData[static::UI_COMPONENT_FIELD_KEY_GOODS_DATA_FROM])) {
            return true;
        }
        return false;
    }
}