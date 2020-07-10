<?php

namespace app\modules\component\dl\components;

use app\base\SiteConstants;
use app\base\SitePlatform;
use app\modules\common\dl\components\CommonCommonComponent;
use app\modules\common\dl\components\CommonPageUiComponentDataComponent;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use ego\base\JsonResponseException;
use Globalegrow\Gateway\Client;
use Globalegrow\Gateway\Exceptions\RequestException;
use Globalegrow\Gateway\Exceptions\ResponseException;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use app\modules\activity\components\CommonComponent;
use GuzzleHttp\RequestOptions;
use app\modules\soa\components\IpsComponent;
use app\modules\common\dl\components\CommonPublishParams;
use GuzzleHttp\Cookie\CookieJar;

/**
 * 组件解析
 */
class ExplainComponent extends Component
{
    //是否自定义组件
    const IS_CUSTOM = 1;

    //判断是否是编辑环境
    const EDIT_ENV_ROUTE = [
        'home/dl/design/index',
        'home/dl/layout-design/copy-layout',
        'home/dl/layout-design/save-layout-form',
        'home/dl/ui-design/save-form',
        'home/dl/ui-design/add-ui',
        'home/dl/ui-design/copy-ui',
        'home/dl/page-tpl/preview',
        'home/dl/page-tpl/add',

        'activity/dl/design/index',
        'activity/dl/layout-design/copy-layout',
        'activity/dl/layout-design/save-layout-form',
        'activity/dl/ui-design/save-form',
        'activity/dl/ui-design/add-ui',
        'activity/dl/ui-design/copy-ui',
        'activity/dl/page-tpl/preview',
        'activity/dl/page-tpl/add',         //生成模板时，需要生成静态页面模板，可以查看预览

        //推广落地页
        'advertisement/dl/design/index',
        'advertisement/dl/layout-design/copy-layout',
        'advertisement/dl/layout-design/save-layout-form',
        'advertisement/dl/ui-design/save-form',
        'advertisement/dl/ui-design/add-ui',
        'advertisement/dl/ui-design/copy-ui',
        'advertisement/dl/page-tpl/preview',
        'advertisement/dl/page-tpl/add',         //生成模板时，需要生成静态页面模板，可以查看预览
    ];

    //【V1.1.1版本】商品sku可以为空，为空时页面商品列表显示默认效果
    const NO_SKU_ROUTE = [
        'activity/dl/page-tpl/add',
        'activity/dl/page-tpl/preview',
        'activity/dl/design/index',
        'activity/dl/design/preview',
    ];

    //商品列表接口key
    const GOODS_LIST_KEY = 'goods';

    //商品详情接口key
    const GOODS_DETAIL_KEY = 'goodsDetail';

    //社区列表接口key
    const COMMUNITY_LIST_KEY = 'community';

    //Node接口key
    const NODE_KEY = 'nodeApi';

    //页面渲染接口key
    const NODE_HTML_KEY = 'render';

    //组件页面位置ID
    private $instanceId;

    //组件编码
    private $key;

    //组件解析数据
    private $data;

    //组件布局数据
    private $layoutData = '';

    //自定义数据
    private $customData = [];

    // 分享信息
    public $shareData = [];

    //组件解析模板名
    private $tpl;

    //组件模板ID
    private $tplId;

    //组件模板文件夹名
    private $tplName;

    //组件表单名
    private $formTpl = 'form.twig';

    //组件样式文件名
    private $css = 'index.css';

    //组件JS文件名
    private $js = 'index.js';

    //组件解析语言
    private $lang;

    //静态资源域名
    public $staticDomain;

    //组件类型
    private $componentType;

    //站点简称
    private $siteCode;

    /**
     * @var int 活动ID
     */
    public $activityId;

    /**
     * @var int 页面ID
     */
    public $pageId;

    /**
     * @var bool 是否页面模板解析(页面模板解析有很多参数缺失，无需校验的)
     */
    public $isPageTplExplain = false;

    /**
     * @var array layout楼层信息
     */
    public $layoutList = [];

    /**
     * @var int 当前layout所在楼层
     */
    public $layoutIndex = 0;

    /**
     * @var int layout组件ID（UI组件中有需要用到layoutId，故此处定义一个）
     */
    public $layoutId = 0;

    /**
     * @var array uiList（ui在当前layout内的序号list）
     */
    public $uiList = [];

    /**
     * @var int 当前ui所在layout内的序号
     */
    public $uiIndex = 0;

    //组件解析错误提示信息
    private $explainErrorMsg = '组件数据解析出错，请删除后再添加';

    /**
     * @var JsonResponseException GB站点组件SOA异常
     */
    private $gbSoaJsonException = null;

    /**
     * @var bool 是否自定义组件
     */
    public $isCustom = false;

    /**
     * 获取组件静态资源
     *
     * @param string $key   组件编码
     * @param int    $tplId 模板ID
     *
     * @return array|bool 静态资源路径
     * @throws \Exception
     */
    public function getStaticConfig($key, $tplId = 0)
    {
        $this->key = $key;
        $this->tplId = $tplId;

        if (!$path = $this->getPathByKey($key)) {
            return false;
        }
 /*       if (!is_file($path . '/' . $this->css) || !is_file($path . '/' . $this->js)) {
            $this->errors = '组件文件不存在';

            return false;
        }*/

        return ['css' => $path . '/' . $this->css, 'js' => $path . '/' . $this->js];
    }

    /**
     * 组件模板解析
     *
     * @param  \app\modules\common\components\CommonCommonComponent $component 组件对象
     *
     * @return string 组件html
     * @throws \yii\base\ViewNotFoundException
     * @throws \Exception
     */
    public function explainForTpl($component)
    {
        if (!$this->initExplain($component)) {//初始化
            return false;
        }
        if (!$path = $this->getPathByKey($this->key)) {//定位路径
            return false;
        }

        if (0 == $component->isVueSsr && !is_file($path . '/' . $this->tpl)) {
            $this->errors = '组件文件不存在';

            return false;
        }

        if (!$this->getData($component)) {//获取数据
            return false;
        }

        if (2 == $component->componentType && 1 == $component->isVueSsr) {
            if ($component->needStatic && $this->tpl !== $this->formTpl) {
                $str = $this->nodeExplainUi($path);
            } else {
                $str = $this->nodeExplainTpl();
            }
        } else {
            if ($component->needStatic && $this->tpl !== $this->formTpl) {
                $str = $this->explainUi($path);
            } else {
                $str = $this->explainTpl($path);
            }
        }

        return $str;

    }

    /**
     * 初始化解析对象
     *
     * @param \app\modules\common\components\CommonCommonComponent $component
     *
     * @return bool
     */
    private function initExplain($component)
    {
        $this->instanceId = (int) $component->instanceId;
        $this->data = $component->data; //默认加载数据
        $this->customData = $component->customData; //布局组件配置数据(背景颜色,背景图片)
        $this->shareData = $component->shareData; //页面分享信息
        $this->tpl = $component->tpl; //默认模板名
        $this->tplId = $component->tplId ?? 0;//组件模板ID
        $this->key = $component->key;
        $this->lang = $component->lang;
        $this->layoutData = $component->layoutData;//自定义布局组件自定义数据(总宽,总列,单列宽度)
        $this->siteCode = $component->siteCode;//站点简称
        $this->componentType = $component->componentType;//组件类型
        $this->activityId = (int) $component->activityId;//活动ID（解析页面模板的组件时，activityId为0）
        $this->pageId = (int) $component->pageId;//页面ID（解析页面模板的组件时，pageId为0）
        $this->isPageTplExplain = $component->isPageTplExplain;//是否页面模板解析(页面模板解析有很多参数缺失，无需校验的)
        $this->layoutList = $component->layoutList;//layout楼层信息，供数据埋点使用，仅在整个页面渲染时添加
        $this->uiList = $component->uiList;//ui楼层信息，供数据埋点使用，仅在整个页面渲染时添加
        $this->layoutId = $component->layoutId;//layout楼层信息，供数据埋点使用，仅在整个页面渲染时添加
        if (!empty($this->layoutList) && !empty($this->layoutId)) {
            $this->layoutIndex = array_search($this->layoutId, $this->layoutList, false) + 1;//楼层从1开始
        }
        if (!empty($this->uiList) && !empty($this->instanceId)) {
            $this->uiIndex = array_search($this->instanceId, $this->uiList, false) + 1;//楼层从1开始
        }
        if ($this->isUi()) { // 判断UI下的组件是否为自定义组件，后续有特殊处理
            $model = UiModel::findOne(['component_key' => $component->key]);
            if ($model && $model->is_custom) {
                $this->isCustom = true;
                // add by tengjiashun 编号2018-09-14-16-16-00
                // 自定义UI组件的data数据要做下特殊处理，这里前后做下标记，方便后面在整个UI处理完毕后将自定义JS字符转成实体
                // 原因是form.twig模板中展示的js代码是放在input的value字段中，转义规则应用的是HTML的规则，而非JS的规则
                if ($this->tpl === $this->formTpl) {
                    // 只有form.twig才需要处理
                    $this->data['tpl_html'] = empty($this->data['tpl_html'])
                        ? '' : '/* geshop custom static strat */' . $this->data['tpl_html'] . '/* geshop custom static end */';
                    $this->data['tpl_css'] = empty($this->data['tpl_css'])
                        ? '' : '/* geshop custom static strat */' . $this->data['tpl_css'] . '/* geshop custom static end */';
                    $this->data['tpl_js'] = empty($this->data['tpl_js'])
                        ? '' : '/* geshop custom static strat */' . $this->data['tpl_js'] . '/* geshop custom static end */';
                }
            }
        }

        //对于预览地址activity/design/preview，不需要静态域名，直接使用相对路径即可
        $this->staticDomain = \in_array(app()->controller->getRoute(), app()->params['no_domain_route'], true)
            ? '' : app()->params['s3PublishConf']['staticDomain'] . app()->params['s3PublishConf']['staticKeyPre'];

        if (empty($this->tpl) || empty($this->key) || empty($this->instanceId) || empty($this->lang)
            || empty($this->siteCode) || empty($this->componentType)
            || (!$this->isPageTplExplain && (empty($this->pageId)))
        ) {
            $this->errors = '组件属性不完整';

            return false;
        }

        return true;
    }

    /**
     * 获取组件基础文件路径
     *
     * @param  string $key 组件编码
     *
     * @return string
     * @throws \Exception
     */
    private function getPathByKey($key)
    {
        if ($key[0] === $this->type[1]['key']) {
            $info = $this->layoutModel->getByKey($key);
            if (empty($info)) {
                $this->errors = '组件不存在';

                return false;
            }
            $this->currType = $this->type[1];
            $path = $this->basedir . $this->currType['path'] . '/' . $this->key;
        } else {
            $info = $this->uiModel->getByKey($key);
            if (empty($info)) {
                $this->errors = '组件不存在';

                return false;
            }
            $this->formatDataHtml($info);
            $this->currType = $this->type[2];
            if (empty($this->tplId)) {//赋值组件默认模板ID
                $this->tplId = $info->tpl_id;
            }
            $tplInfo = UiTplModel::findOne($this->tplId);
            if (!$tplInfo) {
                throw new Exception('非法的模板ID');
            }
            $this->tplName = $tplInfo->name_en;
            $path = $this->basedir . $this->currType['path'] . '/' . $this->key . '/' . $this->tplName;
        }

        return $path;
    }

    //自定义组件数据格式化为HTML
    private function formatDataHtml($info)
    {
        if ($info['is_custom'] === static::IS_CUSTOM && $this->tpl !== $this->formTpl
            && isset($this->data['tpl_css']) && isset($this->data['tpl_js'])
        ) {
            $this->data['tpl_css'] = '<style>' . $this->data['tpl_css'] . '</style>';
            $this->data['tpl_js'] = '<script>' . $this->data['tpl_js'] . '</script>';
        }
    }

    /**
     * 获取组件数据
     *
     * @param object $component 组件对象
     *
     * @return bool
     * @throws JsonResponseException
     */
    private function getData($component)
    {
        if (property_exists($component, 'default')) {//默认图片赋值
            $siteConf = app()->params['sites'][ $this->siteCode ];
            $component->default['lazyWidth'] = $siteConf['lazyWidth'];
            $component->default['lazyHeight'] = $siteConf['lazyHeight'];
            $component->default['initialWidth'] = $siteConf['initialWidth'];
            $component->default['initialHeight'] = $siteConf['initialHeight'];
            $component->default['lazyImg'] = $component->staticDomain . $siteConf['lazyImg'];
            $this->data['default'] = (isset($this->data['default']) && \is_array($this->data['default']))
                ? array_merge($this->data['default'], $component->default) : $component->default;
        }

        // 自动刷新组件不用获取商品数据
        if (isset($this->data['is_auto_refresh_ui']) && $this->data['is_auto_refresh_ui']) {
            $this->data['goodsInfo'] = [];
        } else {
            //如果UI组件SKU来自选品系统
            $ipsComponent = new IpsComponent();
            $goodsSkuInfo = $ipsComponent->tryGetGoodsSkuFromIps($this->data);
            if (null === $goodsSkuInfo) {
                if (!empty($this->data['goodsSKU'])) {
                    $this->data['goodsSKU'] = $this->processGoodsSku($this->data['goodsSKU']);
                    $goodsSkuInfo = $this->data['goodsSKU'];
                }
            }

            //赋值商品列表数据
            if (!empty($goodsSkuInfo)) {
                $goodsData = $this->getGoodsData($goodsSkuInfo, $this->lang, $component, $this->data);
                if (false === $goodsData) {
                    return false;
                }

                // 如果SKU来自选品系统，处理商品显示限制
                $ipsPageInfo = [
                    'siteCode' => $component->siteCode,
                    'pageId'   => $component->pageId,
                    'lang'     => $component->lang,
                ];
                $ipsUiInfo = ['id' => $component->instanceId, 'key' => $component->key];
                $ipsComponent->tryGoodsNumLimitProcess($ipsPageInfo, $ipsUiInfo, $this->data, $goodsData);

                $this->data['goodsInfo'] = $goodsData;
            }
        }

        $this->data['isEditEnv'] = \in_array(app()->controller->getRoute(), self::EDIT_ENV_ROUTE, true) ? 1 : 0;

        return true;
    }

    /**
     * @file  : processGoodsSku
     * @brief : 处理sku数据
     *
     * @param $goodsSku
     *
     * @return mixed
     */
    private function processGoodsSku($goodsSku)
    {
        if (!is_array($goodsSku) && false !== strpos($goodsSku, '{')) {//数组形式保存的直接decode
            $goodsSku = json_decode($goodsSku, true);
        }

        return $goodsSku;
    }

    /**
     * 获取商品数据
     *
     * @param $goodsSku
     * @param $lang
     * @param $component
     * @param $componentData 组件数据
     *
     * @return array|bool
     */
    private function getGoodsData($goodsSku, $lang, $component, $componentData)
    {
        if (empty($goodsSku) || (false !== stristr(app()->controller->getRoute(), 'ui-design/get-form'))) {
            return [];
        }

        if (\is_array($goodsSku)) {
            $goodsInfo = $goodsSku;
            foreach ($goodsInfo as &$listTab) {
                if(empty($listTab['lists'])){
                    continue;
                }

                if (isset($listTab['isAsync']) && (int)$listTab['isAsync'] === 1) {
                    $listTab['lists'] = [];
                } else {
                    $listTab['lists'] = $this->getGoodsList($listTab['lists'], $lang, $component->siteCode, $componentData);
                }
            }
            unset($listTab);
        } else {
            $goodsInfo = $this->getGoodsList($goodsSku, $lang, $component->siteCode, $componentData);
        }

        // D网网红首页组件没有商品数据不提示错误
        $isHomeKol = is_numeric(CommonPublishParams::$homePageType) && (CommonPublishParams::$homePageType === SiteConstants::HOME_PAGE_TYPE_KOL);
        if (empty($goodsInfo) && !$isHomeKol && !in_array(app()->controller->getRoute(), self::NO_SKU_ROUTE)) {
            yii::info("Testing " . var_export($goodsSku, true) . ' ' . var_export($component, true));
            $this->errors = '站点SKU不存在或SKU不可用!';

            return false;
        }

        return $goodsInfo;
    }

    /**
     * getGoodsDetail
     *
     * @param string $skuString     商品SKU
     * @param string $lang          语言代码简称
     * @param string $siteCode      站点siteCode
     * @param array  $componentData 组件数据
     * @param bool $throwOnException 遇到异常时是否抛出
     * @return array
     * @throws JsonResponseException
     */
    public function getGoodsList($skuString, $lang, $siteCode, $componentData = null, $throwOnException = false)
    {
        if (empty($siteCode) || empty(app()->params['sites'][ $siteCode ]) || empty($skuString)) {
            return [];
        }

        $siteConf = app()->params['sites'][ $siteCode ];
        // !!! 更换服装站点的新商品接口 by tengjiashun 2018-11-03 !!!
        $api = $siteConf['getdetail']['url'] ?? '';
        if (!$api) {
            return [];
        }

        $params = [
            'lang'    => $lang,
            'goodsSn' => $skuString
        ];

        if (!empty($componentData) && isset($componentData['newUserPrice'])
            && (1 === (int) $componentData['newUserPrice'])
        ) {
            $params['newUserPrice'] = 1;
        }

        $goodsInfo = $this->getGoodsInfo($api, $params, 'goodsInfo', $throwOnException);
        if (!empty($goodsInfo)) {
            // D网网红首页只显示有实际库存的商品
            if (is_numeric(CommonPublishParams::$homePageType) && (CommonPublishParams::$homePageType === SiteConstants::HOME_PAGE_TYPE_KOL)) {
                $_goodsInfo = [];
                foreach ($goodsInfo as $_key => $_goods) {
                    if (isset($_goods['real_stock_num']) && ((int)$_goods['real_stock_num'] > 0)) {
                        $_goodsInfo[$_key] = $_goods;
                    }
                }
                $goodsInfo = $_goodsInfo;
            }
        }


        if (!empty($goodsInfo) && stristr($siteCode, 'app')) {
            $this->convertGoodsAppLink($goodsInfo, $siteConf[ static::GOODS_LIST_KEY ]['link']);
        }

        return $goodsInfo;
    }

    /**
     * 获取社区列表数据
     *
     * @param $siteCode
     *
     * @return array
     */
    public function getCommunityList($siteCode)
    {
        if (empty($siteCode) || empty(app()->params['sites'][ $siteCode ])) {
            return [];
        }

        $siteConf = app()->params['sites'][ $siteCode ];
        $api = !empty($siteConf[ static::COMMUNITY_LIST_KEY ])
            ? $siteConf[ static::COMMUNITY_LIST_KEY ]['url'] : '';
        if (empty($api)) {
            return [];
        }

        $community = [];
        try {
            $responseData = ArrayHelper::toArray((new Client(
                $api,
                app()->params['gateway']['app_key'],
                app()->params['gateway']['sign']
            ))->send(''));

            if (isset($responseData['code']) && 0 === $responseData['code']) {
                $community = $responseData['data'] ?? [];
            } else {
                Yii::error('站点社区列表信息获取失败' . json_encode($responseData), __METHOD__);
            }
        } catch (RequestException $e) {
            Yii::error('站点社区列表RequestException：' . $e->getMessage(), __METHOD__);
        } catch (ResponseException $e) {
            Yii::error('站点社区列表ResponseException：' . $e->getMessage(), __METHOD__);
        }

        return $community;
    }

    public function getRenderHtml(string $siteCode, array $params)
    {
        if (empty($siteCode) || empty(app()->params['sites'][ $siteCode ])) {
            return [];
        }

        $siteConf = app()->params['sites'][ $siteCode ];
        $api = !empty($siteConf[ static::NODE_KEY ][ static::NODE_HTML_KEY ])
            ? $siteConf[ static::NODE_KEY ][ static::NODE_HTML_KEY ]['url'] : '';
        if (empty($api)) {
            return [];
        }
        $method = $siteConf[ static::NODE_KEY ][ static::NODE_HTML_KEY ]['method'];

        $html = '';
        try {
            $params = [RequestOptions::VERIFY => false];
            if (app()->env->isPreRelease()) {
                $cookieJar = CookieJar::fromArray(['staging' => 'true'], DL_DOMAIN);
                $params[ RequestOptions::COOKIES ] = $cookieJar;
            }
            $response = (new \GuzzleHttp\Client())->request(
                $method,
                FULL_DOMAIN . $api,
                [RequestOptions::JSON => $params]
            );
            $responseData = $response->getBody()->getContents();
            $responseData = json_decode($responseData, true);
            if (isset($responseData['code']) && 0 === $responseData['code']) {
                $html = $responseData['data'];
            } else {
                Yii::error('Node渲染接口信息获取失败' . json_encode($responseData), __METHOD__);
            }
        } catch (RequestException $e) {
            Yii::error('Node渲染接口RequestException：' . $e->getMessage(), __METHOD__);
        } catch (ResponseException $e) {
            Yii::error('ode渲染接口ResponseException：' . $e->getMessage(), __METHOD__);
        }

        return $html;
    }

    /**
     * APP站点转换商品链接
     *
     * @param $goodsInfo
     * @param $link
     */
    private function convertGoodsAppLink(&$goodsInfo, $link)
    {
        foreach ($goodsInfo as &$value) {
            $value['url_title'] = sprintf($link, $value['goods_id']);
        }
    }

    /**
     * 获取商品列表信息/商品详情
     *
     * @param string $api     接口API地址
     * @param array  $params  接口参数
     * @param string $dataKey 针对不通网站返回的data数据key不一致
     * @param bool $throwOnException 遇到异常时是否抛出
     * @return array
     * @throws \Exception
     */
    private function getGoodsInfo($api, $params, $dataKey, $throwOnException = false)
    {
        $goodsInfo = [];

        try {
            $responseData = ArrayHelper::toArray((new Client(
                $api,
                app()->params['gateway']['app_key'],
                app()->params['gateway']['sign']
            ))->send('', $params));

            if (isset($responseData['code']) && 0 === $responseData['code']) {
                $goodsInfo = $responseData['data'][ $dataKey ] ?? [];
                Yii::info('组件：' . $this->key . '--' . $this->instanceId . '；商品信息：' . json_encode($params)
                    . ' ；返回结果：' . json_encode(array_column($goodsInfo, 'goods_sn')), __METHOD__);
            } else {
                Yii::error('站点商品信息获取失败' . json_encode($responseData), __METHOD__);
            }
        } catch (RequestException $e) {
            Yii::error('站点商品信息RequestException：' . $api . json_encode($params) . $e->getMessage(), __METHOD__);
            if ($throwOnException) {
                throw new \Exception(sprintf('请求站点接口 %s 异常: %s', $api, $e->getMessage()));
            }
        } catch (ResponseException $e) {
            Yii::error('站点商品信息ResponseException：' . $api . json_encode($params) . $e->getMessage(), __METHOD__);
            if ($throwOnException) {
                throw new \Exception(sprintf('请求站点接口 %s 异常: %s', $api, $e->getMessage()));
            }
        }

        return $this->compatibleGoodsInfo($goodsInfo);
    }

    /**
     * 数据兼容处理
     *
     * @param array $goodsInfo
     *
     * @return array
     */
    private function compatibleGoodsInfo($goodsInfo)
    {
        if (!empty($goodsInfo)) {
            foreach ($goodsInfo as &$item) {
                // !!! 为兼容以前使用的字段名，这里做兼容处理 by tengjiashun 2018-11-03 !!!
                $item['catid'] = $item['cateid'] ?? '';
                $item['category'] = $item['catename'] ?? '';
                $item['warecode'] = $item['warehousecode'] ?? '';
                empty($item['left_time']) && $item['left_time'] = 0;
                empty($item['promote_start_date']) && $item['promote_start_date'] = '';
                empty($item['promote_end_date']) && $item['promote_end_date'] = '';
                empty($item['promote_start_time']) && $item['promote_start_time'] = 0;
                empty($item['promote_end_time']) && $item['promote_end_time'] = 0;
                empty($item['score_rank']) && $item['score_rank'] = 0;
                empty($item['review_total']) && $item['review_total'] = 0;
                if (!empty($item['promotions'])) {
                    /** @var array[] $item */
                    foreach ($item['promotions'] as $k => $promotion) {
                        $item['promotions'][ $k ] = htmlspecialchars_decode($promotion);
                    }
                }
            }
            unset($item);
        }

        return $goodsInfo;
    }

    /**
     * 模板解析
     *
     * @param  string $path 当前组件路径
     *
     * @return string 模板解析字符串
     * @throws \yii\base\ViewNotFoundException
     * @throws JsonResponseException
     */
    private function explainTpl($path)
    {
        list(, $platformCode) = SitePlatform::splitSiteCode($this->siteCode);
        $renderArr = [
            'pageInstanceId'  => $this->instanceId,
            'data'            => $this->data,
            'compKey'         => $this->key,
            'activityId'      => $this->activityId,
            'pageId'          => $this->pageId,
            'layoutData'      => $this->layoutData,
            'customData'      => $this->customData,
            'shareData'       => $this->shareData,
            'staticDomain'    => $this->staticDomain, // 静态资源域名
            'siteDomain'      => $this->getSiteDomain($this->siteCode, $this->lang), // 站点域名（区分了多语言的）
            'interfaceDomain' => $this->getinterfaceDomain($this->siteCode),// 接口域名
            'siteCode'        => $this->siteCode,
            'platform'        => $platformCode,
            'lang'            => $this->lang,
            'languages'       => !$this->tplId ? []
                : CommonPageUiComponentDataComponent::getConfig($this->tplId, 'languages'),
            'serverTime'      => time() * 1000, //转成js的时间戳
            'layoutId'        => $this->layoutId,
            'layoutList'      => $this->layoutList,
            'layoutIndex'     => $this->layoutIndex,
            'uiId'            => $this->componentType === CommonCommonComponent::LAYOUT_UI ? $this->instanceId : 0,
            'uiList'          => $this->uiList,
            'uiIndex'         => $this->uiIndex,
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = \yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            $html = app()->view->renderFile($path . '/' . $this->tpl, $renderArr);
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainTpl)：' . $e->getMessage(), __METHOD__);
            $this->errors = '组件渲染失败(explainTpl)：' . $e->getMessage();
            $html = $this->explainError($this->componentType);
        }

        $html = $this->formatStr($html);

        if ($this->isCustom && $this->tpl === $this->formTpl) {
            // add by tengjiashun 编号2018-09-14-16-16-00
            // 针对上面做的标记处理，这里进行还原，并将字符转为实体
            $stylesheet = '/\/\*\s*geshop\s*custom\s*static\s*strat\s*\*\/(.*?)\/\*\s*geshop\s*custom\s*static\s*end\s*\*\//is';
            preg_match_all($stylesheet, $html, $matches);
            if (!empty($matches[1])) {
                /** @var array[] $matches */
                foreach ($matches[0] as $key => $match) {
                    $html = str_replace($match, htmlspecialchars($matches[1][ $key ], ENT_QUOTES), $html);
                }
            }
        }

        return $html;
    }

    private function nodeExplainTpl()
    {
        $renderArr = [
            'is_edit_env'    => $this->data['isEditEnv'],
            'pageId'         => $this->pageId,
            'pageInstanceId' => $this->instanceId,
            'key'            => $this->key,
            'lang'           => $this->lang,
            'platform'       => SitePlatform::getPlatformCodeBySiteCode($this->siteCode),
            'sitecode'       => $this->siteCode,
            'theme'          => $this->tplName,
            'data'           => $this->data,
            'languages'      => !$this->tplId ? []
                : CommonPageUiComponentDataComponent::getConfig($this->tplId, 'languages')
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = \yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            $html = $this->getRenderHtml($this->siteCode, $renderArr);
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainTpl)：' . $e->getMessage(), __METHOD__);
            $this->errors = '组件渲染失败(explainTpl)：' . $e->getMessage();
            $html = $this->explainError($this->componentType);
        }

        $html = $this->formatStr($html);

        if ($this->isCustom && $this->tpl === $this->formTpl) {
            // add by tengjiashun 编号2018-09-14-16-16-00
            // 针对上面做的标记处理，这里进行还原，并将字符转为实体
            $stylesheet = '/\/\*\s*geshop\s*custom\s*static\s*strat\s*\*\/(.*?)\/\*\s*geshop\s*custom\s*static\s*end\s*\*\//is';
            preg_match_all($stylesheet, $html, $matches);
            if (!empty($matches[1])) {
                /** @var array[] $matches */
                foreach ($matches[0] as $key => $match) {
                    $html = str_replace($match, htmlspecialchars($matches[1][ $key ], ENT_QUOTES), $html);
                }
            }
        }

        return $html;
    }

    /**
     * UI组件解析
     *
     * @param  string $path 当前组件路径
     *
     * @return string 模板解析字符串
     * @throws \yii\base\ViewNotFoundException
     * @throws JsonResponseException
     */
    private function explainUi($path)
    {
        list(, $platformCode) = SitePlatform::splitSiteCode($this->siteCode);
        $renderArr = [
            'pageInstanceId'  => $this->instanceId,
            'data'            => $this->data,
            'shareData'       => $this->shareData,
            'compKey'         => $this->key,
            'activityId'      => $this->activityId,
            'pageId'          => $this->pageId,
            'staticDomain'    => $this->staticDomain, // 静态资源域名
            'siteDomain'      => $this->getSiteDomain($this->siteCode, $this->lang), // 站点域名（区分了多语言的）
            'interfaceDomain' => $this->getinterfaceDomain($this->siteCode),// 接口域名
            'siteCode'        => $this->siteCode,
            'platform'        => $platformCode,
            'lang'            => $this->lang,
            'languages'       => !$this->tplId ? []
                : CommonPageUiComponentDataComponent::getConfig($this->tplId, 'languages'),
            'serverTime'      => time() * 1000, //转成js的时间戳
            'layoutId'        => $this->layoutId,
            'layoutList'      => $this->layoutList,
            'layoutIndex'     => $this->layoutIndex,
            'uiId'            => $this->componentType === CommonCommonComponent::LAYOUT_UI ? $this->instanceId : 0,
            'uiList'          => $this->uiList,
            'uiIndex'         => $this->uiIndex,
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = \yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            $site = SitePlatform::getSiteBySiteCode($this->siteCode);
            $commonCommonComponent = new CommonCommonComponent();

            $cssStr = '<style type="text/css">';
            if (is_file($path . '/' . $this->css)) {
                $cssStr .= app()->view->renderFile($path . '/' . $this->css);
            }
            // 获取页面各站点组件特定的css和js内容
            $cssStr .= $commonCommonComponent->getSiteComponentStatics($path . '/' . $this->css, $site);
            $cssStr .= '</style>';
            $tplStr = app()->view->renderFile($path . '/' . $this->tpl, $renderArr);
            $jsStr = '<script>';
            if (is_file($path . '/' . $this->js)) {
                $jsStr .= app()->view->renderFile($path . '/' . $this->js);
            }
            // 获取页面各站点组件特定的css和js内容
            $jsStr .= $commonCommonComponent->getSiteComponentStatics($path . '/' . $this->js, $site);
            $jsStr .= '</script>';

            return $this->formatStr($cssStr . $tplStr) . $jsStr;
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainUi)：' . $e->getMessage(), __METHOD__);
            $this->errors = '组件渲染失败(explainUi)：' . $e->getMessage();
            $html = $this->explainError($this->componentType);

            return $this->formatStr($html);
        }
    }

    private function nodeExplainUi($path)
    {
        $renderArr = [
            'is_edit_env'    => $this->data['isEditEnv'],
            'pageId'         => $this->pageId,
            'pageInstanceId' => $this->instanceId,
            'key'            => $this->key,
            'lang'           => $this->lang,
            'platform'       => SitePlatform::getPlatformCodeBySiteCode($this->siteCode),
            'sitecode'       => $this->siteCode,
            'theme'          => $this->tplName,
            'data'           => $this->data,
            'languages'      => !$this->tplId ? []
                : CommonPageUiComponentDataComponent::getConfig($this->tplId, 'languages')
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = \yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            $site = SitePlatform::getSiteBySiteCode($this->siteCode);
            $commonCommonComponent = new CommonCommonComponent();

            $cssStr = '<style type="text/css">';
            $cssStr .= app()->view->renderFile($path . '/' . $this->css);
            // 获取页面各站点组件特定的css和js内容
            $cssStr .= $commonCommonComponent->getSiteComponentStatics($path . '/' . $this->css, $site);
            $cssStr .= '</style>';
            $tplStr = $this->getRenderHtml($this->siteCode, $renderArr);
            $jsStr = '<script>';
            $jsStr .= app()->view->renderFile($path . '/' . $this->js);
            // 获取页面各站点组件特定的css和js内容
            $jsStr .= $commonCommonComponent->getSiteComponentStatics($path . '/' . $this->js, $site);
            $jsStr .= '</script>';

            return $this->formatStr($cssStr . $tplStr) . $jsStr;
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainUi)：' . $e->getMessage(), __METHOD__);
            $this->errors = '组件渲染失败(explainUi)：' . $e->getMessage();
            $html = $this->explainError($this->componentType);

            return $this->formatStr($html);
        }
    }

    /**
     * 获取站点域名，区分语言的
     *
     * @param $siteCode
     * @param $lang
     *
     * @return string
     */
    private function getSiteDomain($siteCode, $lang)
    {
        $domain = '';
        $secondaryDomain = app()->params['sites'][ $siteCode ]['secondary_domain'][ $lang ] ?? '';
        if (!empty($secondaryDomain)) {
            $parse = parse_url($secondaryDomain);
            if (!empty($parse['scheme']) && !empty($parse['host'])) {
                $domain = $parse['scheme'] . '://' . $parse['host'];
            }
        }

        return $domain;
    }

    /**
     * 获取站点接口域名
     *
     * @param $siteCode
     *
     * @return string
     */
    private function getinterfaceDomain($siteCode)
    {
        $domain = '';
        $siteConfig = app()->params['sites'][ $siteCode ] ?? '';
        if (!empty($siteConfig)) {
            // 开发环境会有单独的配置
            $domain = $siteConfig['developdomain'] ?? $siteConfig['domain'];
        }

        return $domain;
    }

    /**
     * 模板解析错误时的替代内容
     *
     * @param int $type 模板内容
     *
     * @return string
     */
    private function explainError($type)
    {
        if ($type === CommonComponent::LAYOUT_COMPONENT) {//layout
            $html = '<div class="geshop-layout-box layout-drag geshop-container" data-key="'
                . $this->key . '" id="' . $this->instanceId . '" style="height:64px;">'
                . $this->explainErrorMsg . '</div>';
        } else {//ui
            $html = '<div class="geshop-component-box component-drag" data-key="'
                . $this->key . '" data-id="' . $this->instanceId . '" style="height:64px;">'
                . $this->explainErrorMsg . '</div>';
        }

        return $html;
    }

    /**
     * 格式化字符串
     *
     * @param  string $str 解析模板的字符串
     *
     * @return string
     */
    private function formatStr($str)
    {
        //将特殊HTML实体转化回字符
        $str = htmlspecialchars_decode($str, ENT_QUOTES);

        if ($this->isCustom || !$this->isUi()) {
            // 自定义UI和layout无需处理，layout若处理的话会将UI的html再处理一遍
            // 这里为了减少查询Layout下是否有自定义UI组件，直接不处理layout的html，这是可行的
            return $str;
        }

        // 去除空格和换行
        $str = str_replace(["\r\n", "\r", "\n", "\t"], '', $str);

        // 去除反斜杠
        return stripslashes($str);
    }

    /**
     * 是否UI组件
     *
     * @return bool
     */
    private function isUi()
    {
        return $this->componentType === CommonCommonComponent::LAYOUT_UI;
    }
}
