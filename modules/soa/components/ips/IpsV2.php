<?php
namespace app\modules\soa\components\ips;

use app\base\SiteConstants;
use app\base\SitePlatform;

use yii\helpers\ArrayHelper;
use app\modules\soa\models\SoaIpsGoodsModel;
use app\modules\common\models\PageModel;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\soa\components\AiComponent;

class IpsV2
{

    /**
     * 获取选品系统单个分类商品SKU串，多SKU用英文逗号分隔
     *
     * @param array $siteCode geshop站点简码，如:zf-pc
     * @param int $activityChildId IPS子活动ID
     *
     * @return array 商品SKU信息
     * 数组格式：
     * - 0 商品SKU列表
     * - 1 商品SKU列表最后更新时间
     * @throws \ego\base\JsonResponseException
     */
    protected function getSingleActivityGoodsSkuFromIpsV2($siteCode, $activityChildId)
    {
        if (isset(static::$ipsSkuAipResultCache[$activityChildId])) {
            return static::$ipsSkuAipResultCache[$activityChildId];
        }

        $result = $this->getMultiActivityGoodsSkuFromIps($siteCode, [$activityChildId]);
        if (empty($result) || !isset($result[$activityChildId])) {
            return ['', 1];
        }

        static::$ipsSkuAipResultCache[$activityChildId] = $result[$activityChildId];
        return static::$ipsSkuAipResultCache[$activityChildId];
    }

    /**
     * 获取选品系统多个分类商品SKU串，多SKU用英文逗号分隔
     *
     * @param array $siteCode geshop站点简码，如:zf-pc
     * @param array $activityChildIds IPS子活动ID列表
     *
     * @return array 商品SKU信息
     * @throws \ego\base\JsonResponseException
     */
    public function getMultiActivityGoodsSkuFromIps($siteCode, $activityChildIds)
    {
        $result = $this->getMultiActivityProductList(['activity_child_id' => $activityChildIds]);
        if (empty($result) || !isset($result['data'])) {
            throw $this->newJsonException('选品系统子活动产品接口参数不完整');
        }

        $activityInfoList = [];
        $allGoodsList = [];
        foreach ($result['data'] as $_result) {
            if (!isset($_result['product'], $_result['sku_update_time'])) {
                throw $this->newJsonException('选品系统子活动产品接口参数不完整');
            }

            //合并多个活动商品信息
            if (!empty($_result['product'])) {
                $allGoodsList = array_merge($allGoodsList, $_result['product']);
            }
        }

        //调用人工智能过滤SKU
        $aiFilterGoodsList = [];
        if (!empty($allGoodsList)) {
            $aiComponent = new AiComponent();
            $aiFilterGoodsList = $aiComponent->filterSameSpuBestSaleSku($siteCode, $allGoodsList);
        }

        //获取过滤后SKU数据
        foreach ($result['data'] as $_result) {
            $skuListString = $this->filterSameSpuBestSaleSku($_result['product'], $aiFilterGoodsList);
            $lastUpdateTime = $_result['sku_update_time'];
            $key = $_result['id'];
            $activityInfoList[$key] = [$skuListString, $lastUpdateTime];
        }

        return $activityInfoList;
    }

    /**
     * 根据人工智能返回的同款销量最好SKU来过滤选品子活动SKU
     *
     * @param array $ipsGoodsList 选品系统商品列表
     * @param array $aiFilterGoodsList 人工智能按销售过滤后的数据SKU数据
     *
     * @return string
     */
    private function filterSameSpuBestSaleSku($ipsGoodsList, $aiFilterGoodsList)
    {
        if (empty($ipsGoodsList))
            return '';

        //按SPU分组
        $goodsSpuMapping = [];
        foreach ($ipsGoodsList as $goodsCode) {
            $spu = $goodsCode['spu'];
            if (!isset($goodsSpuMapping[$spu])) {
                $goodsSpuMapping[$spu] = [];
            }

            if (!in_array($goodsCode['sku'], $goodsSpuMapping[$spu]))
                $goodsSpuMapping[$spu][] = $goodsCode['sku'];
        }

        //同款获取销量最好的SKU
        foreach ($goodsSpuMapping as $spu => $spuGoodsSkuList) {
            if (count($spuGoodsSkuList) == 1) {
                $filterSkuList[$spu] = $spuGoodsSkuList[0];
            } else {
                //人工智能有销售数据的用人工智能的结果，没有直接使用数组第一个
                $filterSkuList[$spu] = $aiFilterGoodsList[$spu] ?? $spuGoodsSkuList[0];
            }
        }

        return join(SiteConstants::CHAR_COMMA, $filterSkuList);
    }

    /**
     * 新版本 -填充UI组件设计页面表单提交数据中的SKU
     *
     * @param int $pageId 活动页面ID
     * @param string $lang 语言简码
     * @param int $componentId UI组件ID
     * @param string $componentKey 组件key
     * @param array $fieldData UI组件数据
     */
    protected function tryFillUiComponentSaveFormSkuFieldDataFromIpsV2($pageId, $lang, $componentId, $componentKey, &$fieldData)
    {
        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::getById($pageId);

        //商品列表tab组件
        if (in_array($componentKey, self::GOODS_LIST_TAB_COMPONENT_KEYS)) {
            //查询当前组件已经关联的IPS子活动
            $soaIpsGoodsModelList = SoaIpsGoodsModel::find()
                ->where([
                    'page_id'           => $pageId,
                    'lang'              => $lang,
                    'component_id'      => $componentId
                ])->asArray()->all();

            $relatedActivityIds = !empty($soaIpsGoodsModelList)
                ? array_unique(array_column($soaIpsGoodsModelList, 'ips_activity_id')) : [];

            //处理选择的IPS分类
            $selectedActivityIds = [];
            if (!empty($fieldData[PageUiComponentDataModel::KEY_SKU])) {

                //获取选品子活动ID列表
                $ipsActivityChildIds = [];
                foreach ($fieldData[PageUiComponentDataModel::KEY_SKU] as $tableSkuInfo) {
                    // 如果商品SKU来源选品系统
                    if (isset($tableSkuInfo['skuFrom'], $tableSkuInfo['ips'])
                        && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$tableSkuInfo['skuFrom'])
                    ) {
                        $ipsActivityChildIds[] = $tableSkuInfo['ips']['gsSelectLevel2'];
                    }
                }

                //获取所有子活动SKU信息
                $ipsActivityChildIds = array_unique($ipsActivityChildIds);
                $subActivitySkuInfoMapping = $this->getMultiActivityGoodsSkuFromIps($pageModel->site_code, $ipsActivityChildIds);

                //关联所有子活动
                foreach ($fieldData[PageUiComponentDataModel::KEY_SKU] as &$tableSkuInfo) {
                    // 如果商品SKU来源选品系统
                    if (isset($tableSkuInfo['skuFrom'], $tableSkuInfo['ips'])
                        && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$tableSkuInfo['skuFrom'])
                    ) {
                        $ipsActivityChildId = $tableSkuInfo['ips']['gsSelectLevel2'];
                        $ipsActivitySkuInfo = $subActivitySkuInfoMapping[$ipsActivityChildId] ?? ['', 1];
                        $tableSkuInfo['lists'] = $ipsActivitySkuInfo[0];
                        $this->relatedUiComponent($ipsActivityChildId, $pageId, $lang, $componentId, $componentKey, $ipsActivitySkuInfo);
                        $selectedActivityIds[] = $ipsActivityChildId;
                    }
                }
            }

            //删除已取消的关联
            $toDelActivityIds = array_diff($relatedActivityIds, $selectedActivityIds);
            if (!empty($toDelActivityIds)) {
                SoaIpsGoodsModel::deleteAll([
                    'ips_activity_id'   => $toDelActivityIds,
                    'page_id'           => $pageId,
                    'lang'              => $lang,
                    'component_id'      => $componentId
                ]);
            }
        } else {
            // 如果商品SKU来源选品系统
            if (isset($fieldData['goodsDataFrom'], $fieldData['gsSelectLevel2'])
                && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$fieldData['goodsDataFrom'])) {

                $ipsActivityChildId = (int)$fieldData['gsSelectLevel2'];
                $ipsActivitySkuInfo = $this->getSingleActivityGoodsSkuFromIpsV2($pageModel->site_code, $ipsActivityChildId);
                $goodsSkuList = $this->relatedGoodsListUiComponent(
                    $ipsActivityChildId, $ipsActivitySkuInfo, $pageId, $lang, $componentId, $componentKey
                );
                $fieldData[PageUiComponentDataModel::KEY_SKU] = $goodsSkuList;
            }
        }
    }

}