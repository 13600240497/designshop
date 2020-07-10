<?php
namespace app\modules\soa\components;

use app\base\SiteConstants;
use app\base\SitePlatform;

use app\modules\soa\models\SoaIpsGoodsModel;
use app\modules\soa\models\SoaIpsActivitySkuModel;
use app\modules\soa\models\SoaIpsQueueModel;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\common\zf\models\PageUiComponentDataModel as ZFPageUiComponentDataModel;
use app\modules\soa\components\ips\SingleLevelIps;
use app\modules\soa\components\ips\MultiLevelIps;
use app\modules\soa\components\ips\IpsMqSync;

/**
 * 选品系统组件
 *
 * @property \app\services\soa\IpsService $IpsService
 *
 * @author TianHaisen
 * @since 1.5.0
 */
class IpsComponent extends Component
{
    /** @var array IPS子活动商品SKU接口缓存 */
    private static $ipsSkuAipResultCache = [];

    /** @var \app\modules\soa\components\ips\MultiLevelIps */
    private $multiLevelIps;

    /** @var \app\modules\soa\components\ips\SingleLevelIps */
    private $singleLevelIps;

    public function __construct($config = [])
    {
        $this->multiLevelIps = new MultiLevelIps($this);
        $this->singleLevelIps = new SingleLevelIps($this);
    }

    /**
     * 测试MQ同步数据
     *
     * @param array $params get参数
     */
    public function testMqSync($params)
    {
        if (isset($params['ips_activity_ids'])) {
            $mqData = [
                'data' => [
                    'activity_child' => explode(',', $params['ips_activity_ids'])
                ]
            ];
            $ipsGoodsSkuSyncComponent = new \app\modules\soa\components\mq\IpsGoodsSkuSyncComponent();
            $ipsGoodsSkuSyncComponent->allActivityRelatedUiComponentSkuSync(json_encode($mqData));
        }
    }


    /**
     * 获取选品单个活动的SKU,保持SKU在geshop的排序顺序。
     * UI组件IPS手动规则配置时调用。
     *
     * @param array $params Http GET参数
     * GET参数：
     * - page_id            页面ID
     * - lang               语言简码; 如：en
     * - id                 UI组件ID
     * - tpl_id             UI组件模板ID
     * - activity_child_id  选品系统(IPS)子活动ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getActivityGoodsSku($params)
    {
        $rules = [
            ['id', ['require', 'number'], ['require' => '无效的参数id', 'number' => '无效的参数id']],
            ['lang', 'require', '无效的参数lang'],
            ['tpl_id', ['require', 'number'], ['require' => '无效的参数tpl_id', 'number' => '无效的参数tpl_id']],
            ['activity_child_id', ['require', 'number'], ['require' => '无效的参数activity_child_id', 'number' => '无效的参数activity_child_id']],
        ];
        $this->checkRequestParams($params, $rules);

        $activityId = $params['activity_child_id'];
        list($goodsSkuList) = $this->getSingleActivityGoodsSku($activityId);

        $oldOrderedSkuList = $this->getIpsOrderedGoodsSkuListFromComponentData(
            $params['id'], $params['lang'], $activityId
        );
        $goodsSkuList = $this->singleLevelIps->getOrderedActivityGoodsSkuList($oldOrderedSkuList, $goodsSkuList);

        return $this->jsonSuccess(['sku' => $goodsSkuList]);
    }

    /**
     * 获取组件已经排序过的SKU列表
     *
     * @param int $componentId UI组件ID
     * @param string $lang 语言简码; 如：en
     * @param int $ipsActivityId 选品系统(IPS)子活动ID
     * @return string
     */
    private function getIpsOrderedGoodsSkuListFromComponentData($componentId, $lang, $ipsActivityId)
    {
        $oldOrderedSkuList = NULL;
        if (isZufulSite()) {
            $goodsSKU = ZFPageUiComponentDataModel::getPublicFieldValue($componentId, $lang, PageUiComponentDataModel::KEY_SKU);
        } else {
            $goodsSKU = PageUiComponentDataModel::getPublicFieldValue($componentId, $lang, PageUiComponentDataModel::KEY_SKU);
        }
        //if (is_string($goodsSKU) && json_decode($goodsSKU, true)) $goodsSKU = json_decode($goodsSKU, true);

        if (!empty($goodsSKU) && is_array($goodsSKU)) {
            foreach ($goodsSKU as $tableSkuInfo) {
                if (!isset($tableSkuInfo[SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_METHODS])) {
                    $tableSkuInfo[SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_METHODS] = SoaIpsGoodsModel::RULE_TYPE_MANUAL;
                }

                if (isset($tableSkuInfo['skuFrom'], $tableSkuInfo['ips'])
                    && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$tableSkuInfo['skuFrom'])
                    && ($tableSkuInfo[SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_METHODS] == SoaIpsGoodsModel::RULE_TYPE_MANUAL)
                    && ((int)$ipsActivityId === (int)$tableSkuInfo['ips']['gsSelectLevel2'])
                ) {
                    $oldOrderedSkuList = $tableSkuInfo[SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU] ?? '';
                    break;
                }
            }
        } else {
            if (isZufulSite()) {
                $oldOrderedSkuList = ZFPageUiComponentDataModel::getPublicFieldValue(
                    $componentId, $lang, SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU
                );
            } else {
                $oldOrderedSkuList = PageUiComponentDataModel::getPublicFieldValue(
                    $componentId, $lang, SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU
                );
            }
        }

        return $oldOrderedSkuList;
    }


    /**
     * 获取选品系统单个分类商品SKU信息, 该函数会静态缓存接口返回
     *
     * @param int $activityChildId IPS子活动ID
     * @param boolean $useCache 是否使用缓存
     *
     * @return array 商品SKU信息
     * 数组格式：
     * - 0 商品SKU列表，多SKU用英文逗号分隔
     * - 1 商品SKU列表最后更新时间
     * @throws \ego\base\JsonResponseException
     */
    public function getSingleActivityGoodsSku($activityChildId, $useCache = false)
    {
        if (isset(static::$ipsSkuAipResultCache[$activityChildId])) {
            return static::$ipsSkuAipResultCache[$activityChildId];
        }

        $skuInfo = null;
        if ($useCache) {
            // 从数据中读取子活动SKU信息
            $soaIpsActivitySkuModel = SoaIpsActivitySkuModel::getByIpsActivityId($activityChildId);
            if ($soaIpsActivitySkuModel) {
                $skuInfo = [$soaIpsActivitySkuModel->sku_list, $soaIpsActivitySkuModel->sku_update_time];
            }
        }

        if (empty($skuInfo)) {
            // 调用选品接口获取子活动SKU信息
            $result = $this->getSingleActivityProductList(['activity_child_id' => $activityChildId]);
            if (empty($result) || !isset($result['data']['list'], $result['data']['activity_child'])) {
                throw $this->newJsonException('选品系统子活动产品接口参数不完整');
            }

            $skuList = empty($result['data']['list']) ? [] : array_column($result['data']['list'], 'sku');
            $lastUpdateTime = $result['data']['activity_child']['sku_update_time'];
            $skuInfo = [join(SiteConstants::CHAR_COMMA, $skuList), $lastUpdateTime];

            // 将结果缓存
            $soaIpsActivitySkuModel = SoaIpsActivitySkuModel::getByIpsActivityId($activityChildId);
            if (!$soaIpsActivitySkuModel) {
                $soaIpsActivitySkuModel = new SoaIpsActivitySkuModel();
                $soaIpsActivitySkuModel->ips_activity_id = $activityChildId;
            }

            $soaIpsActivitySkuModel->sku_list = $skuInfo[0];
            $soaIpsActivitySkuModel->sku_update_time = $skuInfo[1];
            if (!$soaIpsActivitySkuModel->save(true)) {
                throw $this->newJsonException('保存缓存数据错误');
            }
        }

        static::$ipsSkuAipResultCache[$activityChildId] = $skuInfo;
        return static::$ipsSkuAipResultCache[$activityChildId];
    }

    /**
     * 消费选品系统活动SKU变更信息
     */
    public function consumeMqFromDatabaseQueue()
    {
        $soaIpsQueueModel = SoaIpsQueueModel::getNext();
        if (empty($soaIpsQueueModel)) {
            return;
        }

        $msgInfo = json_decode($soaIpsQueueModel->message, true);
        try {
            $soaIpsQueueModel->status = SoaIpsQueueModel::STATUS_RUNNING;
            $soaIpsQueueModel->run_time = time();
            $soaIpsQueueModel->update(false);

            $allResult =$this->consumeMqAllActivityRelatedUiComponentSkuSync($msgInfo['data']['activity_child']);

            $soaIpsQueueModel->status = SoaIpsQueueModel::STATUS_SUCCESS;
            $soaIpsQueueModel->result = json_encode($allResult, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $throwable) {

            $soaIpsQueueModel->status = SoaIpsQueueModel::STATUS_FAIL;
            $soaIpsQueueModel->result = $throwable->getMessage();
        } finally {
            $soaIpsQueueModel->end_time = time();
            $soaIpsQueueModel->update(false);
        }
    }

    /**
     * 消费选品系统SKU有更新的活动
     *
     * @param array $activityChildIds 有更新的活动数组
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function consumeMqAllActivityRelatedUiComponentSkuSync($activityChildIds)
    {
        $ipsMqSync = new IpsMqSync($this);
        return $ipsMqSync->mqSync($activityChildIds, false);
    }

    /**
     * 复制IPS关联活动当复制UI组件
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     * @param int $fromComponentId
     * @param int $toComponentId
     */
    public function copyRelatedIpsActivityIfSkuFromIps($pageInfo, $fromComponentId, $toComponentId)
    {
        SoaIpsGoodsModel::copyUiComponentWithIpsInfo($pageInfo, $fromComponentId, $toComponentId);
    }

    /**
     * 删除页面组件IPS关联信息
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     */
    public function delPageUiRelatedIpsActivityIfSkuFromIps($pageInfo)
    {
        SoaIpsGoodsModel::delPageUiComponentWithIpsInfo($pageInfo);
    }

    /**
     * 删除IPS关联活动当删除UI组件
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     * @param int $componentId
     */
    public function delRelatedIpsActivityIfSkuFromIps($pageInfo, $componentId)
    {
        SoaIpsGoodsModel::delUiComponentWithIpsInfo($pageInfo, $componentId);
    }

    /**
     * 在 ExplainComponent.php 渲染组件时尝试从IPS获取商品SKU
     * @param array $uiData UI组件配置数据
     * @return mixed 如果组件SKU数据来自IPS，返回SKU列表； 否则返回NULL
     */
    public function tryGetGoodsSkuFromIps(&$uiData)
    {
        //商品列表tab组件
        if ($this->isTabUiComponent($uiData)) {
            $this->multiLevelIps->setUiData($uiData);
            return $this->multiLevelIps->tryGetGoodsSkuFromIps();
        } else {
            $this->singleLevelIps->setUiData($uiData);
            return $this->singleLevelIps->tryGetGoodsSkuFromIps();
        }
    }

    /**
     * 商品显示数量限制处理
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     * @param array $uiInfo geshop UI组件信息
     * - id int UI组件ID
     * - key string 组件key
     * @param array $uiData
     * @param array $goodsInfo
     */
    public function tryGoodsNumLimitProcess($pageInfo, $uiInfo, &$uiData, &$goodsInfo)
    {
        //商品列表tab组件
        if ($this->isTabUiComponent($uiData)) {
            $this->multiLevelIps->setPageInfo($pageInfo);
            $this->multiLevelIps->setUiInfo($uiInfo);
            $this->multiLevelIps->setUiData($uiData);
            $this->multiLevelIps->goodsNumLimitProcess($goodsInfo);
        } else {
            $this->singleLevelIps->setPageInfo($pageInfo);
            $this->singleLevelIps->setUiInfo($uiInfo);
            $this->singleLevelIps->setUiData($uiData);
            $this->singleLevelIps->goodsNumLimitProcess($goodsInfo);
        }
    }

    /**
     * 填充UI组件设计页面表单提交数据中的SKU, UI组件保存组件配置时或MQ同步时调用
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     *
     * @param array $uiInfo geshop UI组件信息
     * - id int UI组件ID
     * - key string 组件key
     *
     * @param array $uiData geshop UI组件数据
     * @param boolean $isMqSync 是否MQ数据同步
     */
    public function tryFillUiComponentSaveFormSkuFieldDataFromIps($pageInfo, $uiInfo, &$uiData, $isMqSync = false,$ips_activity_child_id=false)
    {
        //商品列表tab组件
        if ($this->isTabUiComponent($uiData)) {
            $this->multiLevelIps->setPageInfo($pageInfo);
            $this->multiLevelIps->setUiInfo($uiInfo);
            $this->multiLevelIps->setUiData($uiData);
            $this->multiLevelIps->fillUiComponentSaveFormSkuFieldDataFromIps($isMqSync,$ips_activity_child_id);
        } else {
            $this->singleLevelIps->setPageInfo($pageInfo);
            $this->singleLevelIps->setUiInfo($uiInfo);
            $this->singleLevelIps->setUiData($uiData);
            $this->singleLevelIps->fillUiComponentSaveFormSkuFieldDataFromIps($isMqSync,$ips_activity_child_id);
        }
    }

    /**
     * 是否为商品Tab组件
     * @param array $uiData UI组件数据
     * @return bool
     */
    private function isTabUiComponent($uiData)
    {
        if (isset($uiData[PageUiComponentDataModel::KEY_SKU]) && is_array($uiData[PageUiComponentDataModel::KEY_SKU])) {
            return true;
        }
        return false;
    }

    /**
     * IPS - 接口 - 获取活动信息
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=74483473
     *
     * @param array $params get参数
     * - site_code      站点简码
     * - activity_id    IPS活动ID(非必填)
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getActivityList(array $params)
    {
        if (empty($params[SiteConstants::KEY_NAME_SITE_CODE])
                || !SitePlatform::isCurrentSiteGroupPlatformSite($params[SiteConstants::KEY_NAME_SITE_CODE])) {
            throw $this->newJsonException('无效的站点');
        }

        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($params[SiteConstants::KEY_NAME_SITE_CODE]);
        $webSiteCode = $this->IpsService->getIpsWebsiteCodeByGeshopSiteGroupCode($siteGroupCode);
        if (empty($webSiteCode)) {
            throw $this->newJsonException('选品系统没有对应站点');
        }

        $apiParams = [
            'website_code' => $webSiteCode
        ];
        if (!empty($params['activity_id']) && is_numeric($params['activity_id'])) {
            $apiParams['activity_id'] = (int)$params['activity_id'];
        }

        $result = $this->IpsService->slient()->asArray()->getActivityList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * IPS - 接口 - 获取活动子活动分组信息
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=74483842
     *
     * @param array $params get参数
     * - activity_id    IPS活动ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getActivityGroupList($params)
    {
        $rules = [
            ['activity_id', 'require', 'IPS活动ID不能为空'],
            ['activity_id', 'number', 'IPS活动ID为数字类型'],
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'activity_id' => $params['activity_id']
        ];
        $result = $this->IpsService->slient()->asArray()->getActivityGroupList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * IPS - 接口 - 获取子活动信息
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=74483494
     *
     * @param array $params get参数
     * - activity_child_group_id    IPS子活动分组ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getActivityChildList($params)
    {
        $rules = [
            [
                'activity_child_group_id',
                ['require', 'number'],
                ['require' => 'IPS子活动分组ID不能为空', 'number' => 'IPS子活动分组ID为数字类型']
            ]
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'activity_child_group_id' => $params['activity_child_group_id']
        ];
        $result = $this->IpsService->slient()->asArray()->getActivityChildList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * IPS - 接口 - 获取子活动已选SKU
     * @link http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=74483540
     *
     * @param array $params get参数
     * - activity_child_id    IPS子活动ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getSingleActivityProductList($params)
    {
        $rules = [
            ['activity_child_id', 'require', 'IPS子活动ID不能为空'],
            ['activity_child_id', 'number', 'IPS子活动ID为数字类型'],
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'activity_child_id' => $params['activity_child_id']
        ];
        $result = $this->IpsService->slient()->asArray()->getSingleActivityProductList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * IPS - 接口 - 获取子活动已选SKU
     *
     * @param array $params get参数
     * - activity_child_id  array  IPS子活动ID列表
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getMultiActivityProductList($params)
    {
        if (!is_array($params['activity_child_id'])) {
            throw $this->newJsonException('必须是数组');
        }

        $apiParams = [
            'activity_child_id' => $params['activity_child_id']
        ];
        $result = $this->IpsService->slient()->asArray()->getMultiActivityProductList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * 选品系统API接口返回检查
     *
     * @param array $result
     * 
     * @throws \ego\base\JsonResponseException
     */
    protected function checkIpsApiStandardResponse($result)
    {
        if (isset($result['message']) && ($result['message'] == '子活动不存在')) {
            throw $this->newJsonException('选品活动已下线，请重新添加！');
        }
        $this->checkApiStandardResponse($result, '选品系统');
    }
}