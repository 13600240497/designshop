<?php

namespace app\modules\component\gb\components;

use app\base\SitePlatform;
use app\modules\common\components\CommonCommonComponent;
use app\modules\common\components\CommonPageUiComponentDataComponent;
use app\modules\common\gb\components\CommonUi;
use app\modules\common\models\PageUiModel;
use app\modules\soa\components\GearbestComponent;
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
use app\modules\soa\components\ObsComponent;
use app\modules\activity\gb\components\ServiceTagComponent;

/**
 * 组件解析
 */
class ExplainComponent extends Component
{
    //是否自定义组件
    const IS_CUSTOM = 1;

    //判断是否是编辑环境
    const EDIT_ENV_ROUTE = [

        'activity/gb/design/index',
        'activity/gb/layout-design/copy-layout',
        'activity/gb/layout-design/save-layout-form',
        'activity/gb/ui-design/save-form',
        'activity/gb/ui-design/add-ui',
        'activity/gb/ui-design/copy-ui',
        'activity/gb/page-tpl/preview',
        'activity/gb/page-tpl/add',         //生成模板时，需要生成静态页面模板，可以查看预览

    ];

    //【V1.1.1版本】商品sku可以为空，为空时页面商品列表显示默认效果
    const NO_SKU_ROUTE = [
        'activity/page-tpl/add',
        'activity/page-tpl/preview',
        'activity/design/index',
        'activity/design/preview',
    ];

    //商品列表接口key
    const GOODS_LIST_KEY = 'goods';

    //商品详情接口key
    const GOODS_DETAIL_KEY = 'goodsDetail';

    //社区列表接口key
    const COMMUNITY_LIST_KEY = 'community';

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
/*        if (!is_file($path . '/' . $this->css) || !is_file($path . '/' . $this->js)) {
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
        if (!is_file($path . '/' . $this->tpl)) {
            $this->errors = '组件文件不存在';

            return false;
        }
        if (!$this->getData($component)) {//获取数据
            return false;
        }

        if ($component->needStatic && $this->tpl !== $this->formTpl) {//模板解析
            $str = $this->explainUi($path);
        } else {
            $str = $this->explainTpl($path);
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
        $this->layoutId = $component->layoutId;//layout楼层信息，供数据埋点使用，仅在整个页面渲染时添加
        if (!empty($this->layoutList) && !empty($this->layoutId)) {
            $this->layoutIndex = array_search($this->layoutId, $this->layoutList, false) + 1;//楼层从1开始
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

        //如果UI组件SKU来自选品系统
        $this->data['goodsInfo'] = [];
        if (
            (false === stristr(app()->controller->getRoute(), 'ui-design/get-form'))
            && $component instanceof CommonUi
        ) {
            try {
                (new ExplainDataComponent($component->siteCode, $this->lang, $component->key, $component->pipeline))
                    ->getRequestData($this->data);
                (new ServiceTagComponent())->processGoodsServiceTag($this->data['goodsInfo'], $component->pageId, $this->lang);
            } catch (\Exception $exception) {
                if (
                    false === stristr(app()->controller->getRoute(), 'design/index')
                    && false === stristr(app()->controller->getRoute(), 'design/preview')
                ) {
                    throw new JsonResponseException($exception->getCode(), $exception->getMessage());
                }
                //$this->gbSoaJsonException = $exception;(xiarenjie关闭20190116)
            }
        }

        $this->data['isEditEnv'] = \in_array(app()->controller->getRoute(), self::EDIT_ENV_ROUTE, true) ? 1 : 0;

        return true;
    }

    /**
     * 模板解析
     *
     * @param  string $path 当前组件路径
     *
     * @return string 模板解析字符串
     * @throws \yii\base\ViewNotFoundException
     */
    private function explainTpl($path)
    {
        list(, $platformCode) = SitePlatform::splitSiteCode($this->siteCode);
        $renderArr = [
            'pageInstanceId'  => $this->instanceId,
            'data'            => $this->data,
            'activityId'      => $this->activityId,
            'pageId'          => $this->pageId,
            'layoutData'      => $this->layoutData,
            'customData'      => $this->customData,
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
            'themeList'       => ObsComponent::getSectionId($this->instanceId, $this->lang, $this->pageId)
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            //如果GB站点SOA接口调用时发生异常(xiarenjie关闭20190116)
            /*if (!empty($this->gbSoaJsonException)) {
                $this->explainErrorMsg = '组件数据解析出错或接口异常，请检查组件配置或删除后再添加';
                throw $this->gbSoaJsonException;
            }*/

            $html = app()->view->renderFile($path . '/' . $this->tpl, $renderArr);
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainTpl)：' . $e->getTraceAsString(), __METHOD__);
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
     */
    private function explainUi($path)
    {
        list(, $platformCode) = SitePlatform::splitSiteCode($this->siteCode);
        $renderArr = [
            'pageInstanceId'  => $this->instanceId,
            'data'            => $this->data,
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
            'layoutIndex'     => $this->layoutIndex
        ];

        if (!empty($componentId = app()->request->get('debug_id')) && (int) $componentId === $this->instanceId) {
            // 添加调试打印方法
            app()->response->format = yii\web\Response::FORMAT_JSON;
            throw new JsonResponseException($this->codeSuccess, $this->msgSuccess, $renderArr);
        }

        try {
            //如果GB站点SOA接口调用时发生异常(xiarenjie关闭20190116)
            /*if (!empty($this->gbSoaJsonException)) {
                $this->explainErrorMsg = '组件数据解析出错，请检查组件配置或删除后再添加';
                throw $this->gbSoaJsonException;
            }*/

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
            $jsStr .= app()->view->renderFile($path . '/' . $this->js);
            // 获取页面各站点组件特定的css和js内容
            if (is_file($path . '/' . $this->js)) {
                $jsStr .= app()->view->renderFile($path . '/' . $this->js);
            }
            $jsStr .= '</script>';

            return $this->formatStr($cssStr . $tplStr) . $jsStr;
        } catch (\Exception $e) {
            Yii::error('组件渲染失败(explainUi)：' . $e->getTraceAsString(), __METHOD__);
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
