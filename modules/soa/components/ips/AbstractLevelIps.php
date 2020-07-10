<?php
namespace app\modules\soa\components\ips;

use app\base\SiteConstants;
use app\base\SitePlatform;
use app\modules\soa\models\SoaIpsGoodsModel;

abstract class AbstractLevelIps
{
    /** @var string UI组件选品系统SKU保存数据字段 */
    const UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU = 'ipsGoodsSKU';

    /** @var string UI组件选品系统规则SKU保存数据字段 */
    const UI_COMPONENT_FIELD_KEY_IPS_AUTO_RULE_SKU = 'ipsAutoRuleSKU';

    /** @var string UI组件选品系统规则类型 */
    const UI_COMPONENT_FIELD_KEY_IPS_METHODS = 'ipsMethods';

    /** @var IPS自动规则预备SKU增加数量 */
    const IPS_RULE_PREPARE_AUGMENT_NUM = 5;

    /** @var \app\modules\soa\components\IpsComponent */
    protected $ipsComponent;

    /**
     * @var array geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     */
    protected $pageInfo = [];

    /**
     * @var array geshop UI组件信息
     * - id int UI组件ID
     * - key string 组件key
     */
    protected $uiInfo = [];

    /** @var array geshop UI组件数据 */
    protected $uiData = [];

    public function __construct($ipsComponent)
    {
        $this->ipsComponent = $ipsComponent;
    }

    public function setPageInfo($pageInfo)
    {
        $this->pageInfo = $pageInfo;
    }

    public function setUiInfo($uiInfo)
    {
        $this->uiInfo = $uiInfo;
    }

    public function setUiData(&$uiData)
    {
        $this->uiData = &$uiData;
    }


    /**
     * 根据geshop排序，获取最新排序后的SKU，新增SKU放在原排序最后
     *
     * @param string $oldOrderedSkuList 组件已经排序过的SKU列表
     * @param string $ipsGoodsSkuList 选品系统(IPS)子活动最新SKU列表
     *
     * @return string 排序后的SKU列表
     */
    public function getOrderedActivityGoodsSkuList($oldOrderedSkuList, $ipsGoodsSkuList)
    {
        if (empty($ipsGoodsSkuList))
            return $ipsGoodsSkuList;

        // 如果没有排序数据(改组件第一次使用选品系统SKU)，直接使用选品系统的排序
        if (empty($oldOrderedSkuList))
            return $ipsGoodsSkuList;

        $oldGoodsSkuArr = explode(SiteConstants::CHAR_COMMA, $oldOrderedSkuList);
        $srcGoodsSkuArr = explode(SiteConstants::CHAR_COMMA, $ipsGoodsSkuList);

        // 剔除删除的SKU，并保持原有排序
        $intersectSku = array_intersect($oldGoodsSkuArr, $srcGoodsSkuArr);
        // 获取新增的SKU
        $diffSku = array_diff($srcGoodsSkuArr, $oldGoodsSkuArr);

        // 获取新SKU列表，新增的SKU放到最后
        $newSkuList = $intersectSku;
        if (!empty($diffSku)) {
            $newSkuList = array_merge($intersectSku, $diffSku);
        }

        return join(SiteConstants::CHAR_COMMA, $newSkuList);
    }

    /**
     * 保存UI组件和IPS所有使用活动关联关系
     * @param array $ipsActivityList
     */
    protected function relatedGoodsListUiComponent($ipsActivityList)
    {
        if (empty($ipsActivityList) || !is_array($ipsActivityList))
            return;

        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($this->pageInfo['siteCode']);

        //查询当前组件已经关联的IPS子活动
        $soaIpsGoodsModelList = SoaIpsGoodsModel::find()
            ->where([
                'website_code'      => $websiteCode,
                'page_id'           => $this->pageInfo['pageId'],
                'lang'              => $this->pageInfo['lang'],
                'component_id'      => $this->uiInfo['id'],
            ])->asArray()->all();

        $relatedActivityIds = !empty($soaIpsGoodsModelList)
            ? array_unique(array_column($soaIpsGoodsModelList, 'ips_activity_id')) : [];

        // 保存关联关系
        $selectedActivityIds = [];
        foreach ($ipsActivityList as $ipsInfo) {
            list($ipsActivityInfo, $ipsActivitySkuInfo) = $ipsInfo;
            $this->relatedUiComponent($ipsActivityInfo, $ipsActivitySkuInfo);
            $selectedActivityIds[] = $ipsActivityInfo['activityChildId'];
        }

        //删除已取消的关联
        $toDelActivityIds = array_diff($relatedActivityIds, $selectedActivityIds);
        if (!empty($toDelActivityIds)) {
            SoaIpsGoodsModel::deleteAll([
                'website_code'      => $websiteCode,
                'page_id'           => $this->pageInfo['pageId'],
                'lang'              => $this->pageInfo['lang'],
                'component_id'      => $this->uiInfo['id'],
                'ips_activity_id'   => $toDelActivityIds
            ]);
        }
    }

    /**
     * IPS子活动关联活动页面组件,如果已经关联更新商品SKU信息
     *
     * @param array $ipsActivityInfo IPS子活动信息
     * - activityChildId IPS子活动Id
     * - ruleType 规则类型
     * @param array $activitySkuInfo 商品SKU信息,参考getSingleActivityGoodsSkuFromIps返回
     *
     * @throws \ego\base\JsonResponseException
     */
    protected function relatedUiComponent($ipsActivityInfo, $activitySkuInfo)
    {
        list($goodsSkuList, $lastUpdateTime) = $activitySkuInfo;
        $activityChildId = $ipsActivityInfo['activityChildId'];
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($this->pageInfo['siteCode']);

        $soaIpsGoodsModel = SoaIpsGoodsModel::find()
            ->where([
                'website_code'      => $websiteCode,
                'page_id'           => $this->pageInfo['pageId'],
                'lang'              => $this->pageInfo['lang'],
                'component_id'      => $this->uiInfo['id'],
                'ips_activity_id'   => $activityChildId
            ])->one();

        // 存在记录更新，不存在创建记录
        (!$soaIpsGoodsModel) && $soaIpsGoodsModel = new SoaIpsGoodsModel();
        $soaIpsGoodsModel->ips_activity_id = $activityChildId;
        $soaIpsGoodsModel->rule_type = $ipsActivityInfo['ruleType'];
        $soaIpsGoodsModel->website_code = $websiteCode;
        $soaIpsGoodsModel->page_id = $this->pageInfo['pageId'];
        $soaIpsGoodsModel->lang = $this->pageInfo['lang'];
        $soaIpsGoodsModel->component_id = $this->uiInfo['id'];
        $soaIpsGoodsModel->component_key = $this->uiInfo['key'];
        $soaIpsGoodsModel->goods_sku = $goodsSkuList;
        $soaIpsGoodsModel->last_update_time = $lastUpdateTime;

        if (!$soaIpsGoodsModel->save(true)) {
            throw $this->ipsComponent->newJsonException($soaIpsGoodsModel->flattenErrors(', '));
        }
    }

    protected function getSiteGoodsInfo($goodsSkuString)
    {
        if (empty($goodsSkuString))
            return [];

        $goodsInfo = [];
        if (isZufulSite($this->pageInfo['siteCode'])) {
            $explainComponent = new \app\modules\component\zf\components\ExplainComponent();
            $pipeline = $this->pageInfo['pipeline'] ?? '';
            $resultInfo = $explainComponent->getGoodsList(
                $goodsSkuString, $this->pageInfo['lang'], $this->pageInfo['siteCode'], $pipeline, true
            );

            if (!empty($resultInfo)) {
                $goodsInfo = array_column($resultInfo, NULL, 'goods_sn');
            }
        }elseif (isDresslilySite($this->pageInfo['siteCode'])){
            //D 网
            $explainComponent = new \app\modules\component\dl\components\ExplainComponent();
            $resultInfo = $explainComponent->getGoodsList(
                $goodsSkuString, $this->pageInfo['lang'], $this->pageInfo['siteCode'], true
            );

            if (!empty($resultInfo)) {
                $goodsInfo = array_column($resultInfo, NULL, 'goods_sn');
            }
        } else {
            $explainComponent = new \app\modules\component\components\ExplainComponent();
            $resultInfo = $explainComponent->getGoodsList(
                $goodsSkuString, $this->pageInfo['lang'], $this->pageInfo['siteCode'], true
            );

            if (!empty($resultInfo)) {
                $goodsInfo = array_column($resultInfo, NULL, 'goods_sn');
            }
        }

        return $goodsInfo;
    }
}