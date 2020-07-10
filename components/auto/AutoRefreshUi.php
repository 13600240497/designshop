<?php
namespace app\components\auto;

use Yii;
use ego\base\JsonResponseException;
use app\components\base\SiteApi;

/**
 * 自动刷新组件
 *
 * 注意： 这里是公共类同时支持RG、ZF、DL站点，变量注解以ZF站点类为例
 *
 * @author TianHaisen
 * @since 1.9.3
 */
class AutoRefreshUi
{
    const KEY_UI_ASYNC_DATA_FORMAT      = 'async_data_format';  // UI组件自动刷新异步数据格式，数据库字段名称
    const PUBLISH_ASYNC_DATA_JSON_TYPE  = 'async';              // S3文件推送时，文件类型名称

    const KEY_SKU_FROM      = 'skuFrom';    // SKU来源
    const KEY_GOODS_SKU     = 'goodsSku';   // SKU列表
    const KEY_GOODS_INFO    = 'goodsInfo';  // 商品信息
    const KEY_IPS_METHODS   = 'ipsMethods'; // 选品系统规则类型
    const KEY_IPS_INFO      = 'ipsInfo';    // 选品信息
    const KEY_API_PARAMS    = 'apiParams';  // 商品接口参数
    const KEY_IS_ASYNC      = 'isSync';    // 是否异步组件(0 否，1 是),异步组件不用返回商品详情数据

    const GOODS_SKU_FROM_INPUT  = 1; // 商品SKU来源 - 手动输入
    const GOODS_SKU_FROM_IPS    = 2; // 商品SKU来源 - 选品系统

    /** @var int 单个规则SKU最大数量 */
    const MAX_SINGLE_RULE_SKU_NUM = 100;

    /** @var \app\modules\common\zf\models\PageModel 活动页面 */
    private $pageModel;

    /** @var \app\modules\common\zf\models\PageUiModel UI组件 */
    private $pageUiModel;

    /** @var array 组件异步数据 */
    private $asyncDataInfo;

    /** @var string 日志消息前缀 */
    private $messagePrefix;

    /** @var bool 是否Table组件 */
    private $isTableUi = false;

    /**
     * 构造函数
     *
     * @param \app\modules\common\zf\models\PageModel $pageModel 活动页面
     * @param \app\modules\common\zf\models\PageUiModel $pageUiModel 组件
     * @throws AutoRefreshException
     */
    public function __construct($pageModel, $pageUiModel)
    {
        if (empty($pageModel) || empty($pageUiModel)) {
            throw new AutoRefreshException('无效的参数!');
        }

        $this->messagePrefix = sprintf(
            '站点 %s 页面ID %d 组件ID %d ',
            $pageModel->site_code, $pageModel->id, $pageUiModel->id
        );

        $format = json_decode($pageUiModel->async_data_format, true);
        if ($format === null) {
            $message = $this->getLogMessage('异步数据格式不是有效json格式');
            throw new AutoRefreshException($message);
        }

        $this->pageModel = $pageModel;
        $this->pageUiModel = $pageUiModel;
        $this->asyncDataInfo = $format;
        $this->isTableUi = count($format) > 1;
    }

    /**
     * 对异步数据根据格式检查并进行预处理，用于UI组件配置数据保存时调用
     *
     * @see \app\modules\activity\zf\components\PageUiDesignComponent::saveForm
     * @throws JsonResponseException
     */
    public function prepareAsyncData()
    {
        try {
            $this->parseAllRules($this->asyncDataInfo, 1);
        } catch (\Exception $e) {
            throw new JsonResponseException(1, $e->getMessage());
        }
    }

    /**
     * 获取组件异步数据
     *
     * @return array 带外部数据的格式
     * @throws AutoRefreshException
     */
    public function getAsyncData()
    {
        $this->parseAllRules($this->asyncDataInfo, 2);
        return $this->asyncDataInfo;
    }

    /**
     * 根据组件异步数据格式解析获取外部数据
     *
     * @param array $dataRules 组件异步数据格式
     * @param int $type 规则处理类型,  1： 预处理规则; 2: 解析规则
     * @throws AutoRefreshException
     */
    private function parseAllRules(&$dataRules, $type)
    {
        if (!is_array($dataRules)) {
            return;
        }
        
        if (isset($dataRules[self::KEY_SKU_FROM])) {
            if (1 === $type) {
                $this->prepareSingleRule($dataRules);
            } else {
                try {
                    $this->parseSingleRule($dataRules);
                } catch (\Throwable $e) {
                    $message = sprintf('解析组件异步数据规则( %s )出错,异常: %s', json_encode($dataRules), $e->getMessage());
                    Yii::warning($this->getLogMessage($message), __CLASS__);
                    throw new AutoRefreshException($e->getMessage());
                }
            }
        } else {
            foreach ($dataRules as &$dataRule) {
                $this->parseAllRules($dataRule, $type);
            }
        }
    }

    /**
     * 对单个异步数据规则进行预处理，用于UI组件配置数据保存时调用
     *
     * @param array $dataRule 单个格式
     * @see \app\modules\activity\zf\components\PageUiDesignComponent::saveForm
     * @throws AutoRefreshException
     */
    private function prepareSingleRule(&$dataRule)
    {
        if (empty($dataRule) || !is_array($dataRule) || !isset($dataRule[self::KEY_SKU_FROM])) {
            throw new AutoRefreshException('异步数据格式错误');
        }

        $skuFrom = (int)$dataRule[self::KEY_SKU_FROM];
        if (self::GOODS_SKU_FROM_INPUT === $skuFrom) {
            if (!isset($dataRule[self::KEY_IPS_METHODS], $dataRule[self::KEY_IPS_INFO])) {
                $keys = join(',', [self::KEY_GOODS_SKU]);
                throw new AutoRefreshException('异步数据格式错误,手动输入SKU规则必须包含字段：'. $keys);
            }
        } elseif (self::GOODS_SKU_FROM_IPS === $skuFrom) {
            if (!isset($dataRule[self::KEY_IPS_METHODS], $dataRule[self::KEY_IPS_INFO])) {
                $keys = join(',', [self::KEY_IPS_METHODS, self::KEY_IPS_INFO]);
                throw new AutoRefreshException('异步数据格式错误,选品规则必须包含字段：'. $keys);
            }

            // 来源选品系统
            $ipsMethod = (int)$dataRule[self::KEY_IPS_METHODS];
            if (IpsSkuProvider::isValidIpsMethod($ipsMethod)) {
                IpsSkuProvider::prepareIpsRule($ipsMethod, $dataRule[self::KEY_IPS_INFO]);
            } else {
                $message = sprintf('无效的选品规则类型 %d', $ipsMethod);
                throw new AutoRefreshException($message);
            }
        }
    }

    /**
     * 解析组件异步数据格式中的单个格式
     *
     * @param array $dataRule 单个格式
     * @throws AutoRefreshException
     */
    private function parseSingleRule(&$dataRule)
    {
        if (empty($dataRule) || !is_array($dataRule) ||
            !isset($dataRule[self::KEY_SKU_FROM], $dataRule[self::KEY_GOODS_INFO]))
        {
            throw new AutoRefreshException('异步数据格式错误');
        }

        // 根据商品SKU来源，获取SKU详情
        $goodsInfo = [];
        $skuFrom = (int)$dataRule[self::KEY_SKU_FROM];
        $isSync = (int)($dataRule[self::KEY_IS_ASYNC] ?? 0);
        if (self::GOODS_SKU_FROM_INPUT === $skuFrom) {
            // 来源手动输入
            if (0 === $isSync) {
                $goodsSkuList = $dataRule[self::KEY_GOODS_SKU] ?? '';
                $apiParams = $dataRule[self::KEY_API_PARAMS] ?? [];
                if (!empty($goodsSkuList)) {
                    $goodsInfo = $this->getGoodsInfo($goodsSkuList, $apiParams);
                }
            }

        } elseif (self::GOODS_SKU_FROM_IPS === $skuFrom) {
            // 来源选品系统
            $ipsMethod = (int)$dataRule[self::KEY_IPS_METHODS];
            $apiParams = $dataRule[self::KEY_API_PARAMS] ?? [];
            if (IpsSkuProvider::isValidIpsMethod($ipsMethod)) {
                $ipsRulesSku = IpsSkuProvider::getGoodsSkuList($ipsMethod, $dataRule[self::KEY_IPS_INFO]);
                $goodsInfo = $this->getIpsGoodsInfo($ipsRulesSku, $apiParams);

                if (1 === $isSync) { // 异步组件只获取SKU，不要商品详情
                    if (!empty($goodsInfo)) {
                        $_goodsSkuList = array_column($goodsInfo, 'goods_sn');
                        $dataRule[self::KEY_GOODS_SKU] = join(',', $_goodsSkuList);
                        $goodsInfo = [];
                    }
                }

            } else {
                $message = sprintf('无效的选品规则类型 %d', $ipsMethod);
                throw new AutoRefreshException($message);
            }

        } else {
            $message = sprintf('无效的SKU来源 %d', $skuFrom);
            throw new AutoRefreshException($message);
        }

        $dataRule[static::KEY_GOODS_INFO] = empty($goodsInfo) ? [] : $goodsInfo;
    }

    /**
     * 获取选品SKU商品详情信息
     *
     * @param array $ipsRulesSku
     * @param array $extParams
     * @return array
     */
    protected function getIpsGoodsInfo($ipsRulesSku, $extParams)
    {
        $goodsInfo = [];

        foreach ($ipsRulesSku as $ruleSkuInfo) {
            $ipsActivityId = $ruleSkuInfo['id'];

            if (empty($ruleSkuInfo[self::KEY_GOODS_SKU])) {
                $message = sprintf('选品子活动ID %d 没有获取到SKU', $ipsActivityId);
                Yii::warning($this->getLogMessage($message), __CLASS__);
                continue;
            }

            $_goodsInfo = $this->getGoodsInfo($ruleSkuInfo[self::KEY_GOODS_SKU], $extParams);
            if (empty($_goodsInfo)) {
                $message = sprintf('选品子活动ID %d 没有可用的SKU,商品不存在或已下架', $ipsActivityId);
                Yii::warning($this->getLogMessage($message), __CLASS__);
                continue;
            }

            if (isset($ruleSkuInfo['skuLimit'])) {
                $limit = (int)$ruleSkuInfo['skuLimit'];
                $goodsNum = count($_goodsInfo);
                if ($limit > $goodsNum) {
                    $message = sprintf('选品子活动ID %d SKU数量不足', $ipsActivityId);
                    Yii::warning($this->getLogMessage($message), __CLASS__);
                }

                if (($limit > 0) && ($limit < $goodsNum)) {
                    $_goodsInfo = array_slice($_goodsInfo, 0, $limit);
                }
            }

            // 单个规则最大SKU限制数量
            if (!$this->isTableUi && count($_goodsInfo) > self::MAX_SINGLE_RULE_SKU_NUM) {
              $_goodsInfo = array_slice($_goodsInfo, 0, self::MAX_SINGLE_RULE_SKU_NUM);
            }

            $goodsInfo = array_merge($goodsInfo, $_goodsInfo);
        }

        return $goodsInfo;
    }

    /**
     * 根据商品SKU调用站点商品详情接口获取商品详情数据
     *
     * @param string $goodsSkuList
     * @param array $extParams
     * @return array
     */
    protected function getGoodsInfo($goodsSkuList, $extParams = [])
    {
        // 站点商品接口参数
        $params = [
            'siteCode' => $this->pageModel->site_code,
            'lang' => $this->pageUiModel->lang,
            'goodsSku' => $goodsSkuList
        ];
        if (isZufulSite($this->pageModel->site_code)) {
            $params['pipeline'] = $this->pageModel->pipeline;
        }

        // 获取站点商品详情信息
        $goodsInfo = SiteApi::getSiteGoodsInfo($params, $extParams);
        return empty($goodsInfo) ? [] : array_values($goodsInfo);
    }

    /**
     * 获取日志信息
     *
     * @param string $message
     * @return string
     */
    private function getLogMessage($message)
    {
        return AutoRefreshUtils::getLogMessagePrefix($this->messagePrefix . $message);
    }
}
