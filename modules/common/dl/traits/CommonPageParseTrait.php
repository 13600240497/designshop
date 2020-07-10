<?php

namespace app\modules\common\dl\traits;

use app\components\auto\AutoRefreshPage;
use app\base\SitePlatform;
use app\base\Translate;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\common\dl\models\{
    ActivityModel, PageModel, PageLanguageModel, PagePublishCacheModel, PageLayoutModel,
    PageLayoutDataModel, PageUiModel, PageTplModel, PageUiComponentDataModel
};

use app\modules\common\dl\components\{
    CommonPageConfig, CommonUi, CommonLayout
};

use app\modules\component\dl\components\{
    ExplainComponent, ExplainTplComponent
};

use app\base\SiteConstants;
use app\base\SysConfigUtils;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/12
 * Time: 15:03
 */
trait CommonPageParseTrait
{
    //key和tpl_id之间的连接符
    private $delimiterInKeyAndTplId = '-';

    //最后一个节点的next_id是0
    private $lastNodeNextId = 0;

    //是否需要中断解析
    public $needBreakParse = false;

    //页面content包裹的DIV的ID
    public $contentDivId = 'geshop-page-content';

    //layout解析失败html模板
    public $layoutParseErrorTpl = /** @lang html */
        '<div class="geshop-layout-box layout-drag geshop-container"
            data-key="%s" id="%s"><div class="geshop-row">%s</div></div>';

    //ui解析失败html模板
    public $uiParseErrorTpl = /** @lang html */
        '<div class="geshop-component-box component-drag" data-key="%s" data-id="%s">%s</div>';

    //页面组件包含的可拖拽的CSS样式Class
    public static $dragClass = ['component-drag', 'draggable="true"', 'layout-drag'];

    /**
     * 页面解析
     *
     * @param int    $pageId         页面ID
     * @param string $lang           语言代码简称
     * @param bool   $needBreakParse 是否需要在异常时中断页面解析（预览和生成页面时需要中断）
     * @param bool   $useCache       是否采用缓存来更新
     * @param bool   $build          是否组装html和css、js
     *
     * @return string|array
     * @throws \Throwable
     */
    public function parsePage($pageId, $lang, $needBreakParse = false, $useCache = false, $build = true)
    {
        $this->needBreakParse = $needBreakParse;

        $data = ['html' => '', 'js' => '', 'css' => ''];
        if ($useCache) {
            $publishCacheModel = PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
            if ($publishCacheModel) {
	            $data = [
		            'html' => $publishCacheModel['html'],
		            'css'  => $publishCacheModel['css'],
		            'js'   => $publishCacheModel['js']
	            ];
            } else {
                //获取缓存失败，则不使用缓存
                $useCache = false;
            }
        }

        //不使用缓存，或找不到缓存
        if (!$useCache) {
            list($orderedLayouts, $uiListByLayoutPosition, $uiKeyList) =
                $this->getPageLayoutAndUiByPageId($pageId, $lang);
            
            //获取页面单纯的html
            $data['html'] = $this->getPageHtml($orderedLayouts, $uiListByLayoutPosition);

            //获取页面的js和css，并拼装
            if (!empty($uiKeyList) && !empty($orderedLayouts)) {
                $siteCode = !empty($orderedLayouts[0]['site_code']) ? $orderedLayouts[0]['site_code'] : '';
                $data = array_merge($data, $this->getPageStatics($uiKeyList, $siteCode));
            }
        }

        $this->needBreakParse = false;

        return !$build ? $data :
            /** @lang html */
            '<style type="text/css">' . $data['css'] . '</style>'
            . $data['html']
            . '<script defer="defer">' . $data['js'] . '</script>';
    }

    /**
     * 页面解析
     *
     * @param array $params = [
     *      int    $pageId          页面ID
     *      string $lang            语言代码简称
     *      string $version         版本号
     *      bool   $needBreakParse  是否需要在异常时中断页面解析（预览和生成页面时需要中断）
     *      bool   $useCache        是否采用缓存来更新
     *      bool   $updateGoods     只使用useCache更新线上产品
     * ]
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function parsePageInArray(array $params)
    {
        list($pageId, $lang, $version, $needBreakParse, $useCache, $updateGoods, $updateUiGoods) = $params;
        $this->needBreakParse = $needBreakParse ?? false;
        $data = [
            'html' => '',
            'css'  => '',
            'js'   => ''
        ];

        if ($useCache) {
            $publishCacheModel = PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
            if ($publishCacheModel) {
                $data = [
                    'html'     => $publishCacheModel['html'],
                    'css'      => $publishCacheModel['css'],
                    'js'       => $publishCacheModel['js'],
                    'customJs' => $publishCacheModel['customJs'],
                ];
            } else {
                //获取缓存失败，则不使用缓存
                $useCache = false;
            }

            if($updateGoods){
                if(!$useCache){ //如果缓存不存在不更新
                    throw new JsonResponseException(1, '发布缓存为空,更新线上组件产品失败');
                }
                if($updateUiGoods){
                    $uilist = $this->getUiGoodsData(json_decode($publishCacheModel['uilist'],true));//获取组件最新产品
                    $data['uilist'] = json_encode($uilist);
                }else{
                    $uilist = json_decode($publishCacheModel['uilist'],true);
                    $data['uilist'] = $publishCacheModel['uilist'];
                }

                $data['html'] = $this->getPageHtml(json_decode($publishCacheModel['layout'],true), $uilist);
                $data['layout'] = $publishCacheModel['layout'];
                $data['version'] = $version;
                $data['page_id'] = $pageId;
                $data['lang'] = $lang;

                if (true !== ($resSave = PagePublishCacheModel::savePageContentCache($data))
                    && $this->needBreakParse) {
                    app()->response->format = Response::FORMAT_JSON;
                    throw new JsonResponseException(1, $resSave);
                }
            }
        }

        //不使用缓存，或找不到缓存
        if (!$useCache) {
            //获取页面单纯的html
            list($orderedLayouts, $uiListByLayoutPosition, $uiKeyList)
                = $this->getPageLayoutAndUiByPageId($pageId, $lang);
            $data['html'] = $this->getPageHtml($orderedLayouts, $uiListByLayoutPosition);
            $data['layout'] = json_encode($orderedLayouts); //排好序的Layout组件列表
            $data['uilist'] = json_encode($uiListByLayoutPosition); //按LayoutId和position分好组的UI组件列表
            //获取页面的js和css
            if (!empty($uiKeyList) && !empty($orderedLayouts)) {
                $siteCode = !empty($orderedLayouts[0]['site_code']) ? $orderedLayouts[0]['site_code'] : '';
                $pageStatics = $this->getPageStatics($uiKeyList, $siteCode);
                $data = array_merge($data, $pageStatics);
            }

            //!!!保存缓存记录，这里html缓存保存原始的html，没有经过replace和package处理的!!!
            //!!!因为其后有的逻辑需要处理，有的不需要处理，由业务自行判断!!!
            $cacheData = $data;
            $cacheData['version'] = $version;
            $cacheData['page_id'] = $pageId;
            $cacheData['lang'] = $lang;
            //获取自定义的js头部信息
            $pageModel = PageModel::getById($pageId);
            $siteCode = $pageModel->site_code;
            $activityPageType = PageModel::getActivityPageType($pageModel);
            $cssVersion = app()->params['css_version'];
            $cacheData['customJs'] = $this->getHeadExtraCss($pageId, $lang, $cssVersion, $siteCode, $activityPageType, true);

            if (true !== ($resSave = PagePublishCacheModel::savePageContentCache($cacheData))
                && $this->needBreakParse) {
                app()->response->format = Response::FORMAT_JSON;
                throw new JsonResponseException(1, $resSave);
            }
        }

        $data['html'] = str_replace(self::$dragClass, '', $data['html']);
        //页面的content用div包起来
        $data['html'] = $this->packageContent($data['html']);
        unset($data['customCss']);

        $this->needBreakParse = false;

        return $data;
    }

    /**
     * 页面解析(直接读取版本号对应的记录)
     *
     * @param array $params = [
     *      int    $pageId          页面ID
     *      string $lang            语言代码简称
     *      string $version         版本号
     *      string $newVersion      新的版本号
     *      bool   $needBreakParse  是否需要在异常时中断页面解析（预览和生成页面时需要中断）
     * ]
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws JsonResponseException
     */
    public function parsePageInArrayByVersion(array $params)
    {
        list($pageId, $lang, $version, $newVersion, $needBreakParse) = $params;
        $this->needBreakParse = $needBreakParse ?? false;
        $data = [
            'html' => '',
            'css'  => '',
            'js'   => ''
        ];

        $publishCacheModel = PagePublishCacheModel::findOne([
            'version' => $version,
            'page_id' => $pageId,
            'lang' => $lang
        ]);
        if ($publishCacheModel) {
            $data = [
                'html' => $publishCacheModel->html,
                'css'  => $publishCacheModel->css,
                'js'   => $publishCacheModel->js
            ];
            $data['html'] = str_replace(self::$dragClass, '', $data['html']);
            //页面的content用div包起来
            $data['html'] = $this->packageContent($data['html']);
            unset($data['customCss']);

            // 保存新的缓存记录
            $cacheData = $data;
            $cacheData['version'] = $newVersion;
            $cacheData['page_id'] = $pageId;
            $cacheData['lang'] = $lang;
            if (true !== ($resSave = PagePublishCacheModel::savePageContentCache($cacheData))
                && $this->needBreakParse) {
                throw new JsonResponseException($this->codeFail, $resSave);
            }
        } elseif ($this->needBreakParse) {
            throw new JsonResponseException($this->codeFail, '未找到对应版本的缓存记录');
        }

        $this->needBreakParse = false;

        return $data;
    }

    /**
     * 页面模板解析
     *
     * @param int  $id             页面模板ID
     * @param bool $needBreakParse 是否需要在异常时中断页面解析（预览和生成页面时需要中断）
     *
     * @return string
     * @throws \Throwable
     */
    public function parsePageTemplate($id, $needBreakParse = false)
    {
        $this->needBreakParse = $needBreakParse;
        list($layout, $uiList, $uiKeyList) = $this->formatData(PageTplModel::findOne($id));

        //获取页面单纯的html
        $pageHtml = $this->getPageHtml($layout, $uiList, true);

        //获取页面的js和css，并拼装
        if (!empty($uiKeyList) && !empty($layout)) {
            $siteCode = !empty($layout[0]['site_code']) ? $layout[0]['site_code'] : '';
            $pageHtml .= $this->buildStatics($uiKeyList, $siteCode);
        }
        $this->needBreakParse = false;

        return $pageHtml;
    }

    /**
     * 根据layout组件ID查询组件下的uiData
     *
     * @param int $layoutId layout组件ID
     * @param string $lang 语言代码简称
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function getUiDataByLayoutId($layoutId, $lang)
    {
        $uiListByLayoutPosition = [];

        //一次获取所有相关的UI组件，后面再进行拼接
        $uiList = $this->getPageUIByLayoutIds([$layoutId], $lang);
        if ($uiList) {
            //按layout_id分好组的ui列表
            array_map(function ($value) use (&$uiListByLayoutPosition) {
                $uiListByLayoutPosition[ $value['layout_id'] ][ $value['position'] ][] = $value;
            }, $uiList);
        }

        $ui = new CommonUi();
        $ui->needStatic = true;
        $ui->siteCode = $this->pageInfo->site_code;
        $ui->activityId = $this->pageInfo->activity_id;
        $ui->pageId = $this->pageInfo->id;

        return $this->getUiData($uiListByLayoutPosition, $layoutId, $ui);
    }

    /**
     * packageContent
     *
     * @param string $content 页面content部分html代码
     *
     * @return string
     */
    public function packageContent($content)
    {
        return /** @lang html */
            '<div id="' . $this->contentDivId . '">' . $content . '</div>';
    }

    /**
     * 根据页面ID获取头尾Html
     *
     * @param int    $pageId          页面ID
     * @param string $lang            语言代码简称
     * @param array  $componentStatic ['css' => '', 'js' => '']
     *
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    public function getHeadAndFooterByPageId($pageId, $lang, array $componentStatic = [])
    {
        $data = '';
        $pageConfig = new CommonPageConfig();
        if ($pageData = PageModel::getPageInfo($pageId, $lang, true)) {
            if (!empty($pageData['site_code'])) {
                $pageConfig->siteCode = $pageData['site_code'];
            }
            $pageConfig->lang = $lang;
            $pageConfig->pageId = $pageData['id'];
            $pageConfig->contentDivId = $this->contentDivId;
            $pageConfig->activityId = $pageData['activity_id'];
            if (!empty($pageConfig->activityId)) {
                $activityModel = ActivityModel::findOne(['id' => $pageData['activity_id']]);
                $pageConfig->siteCode = $activityModel ? $activityModel->site_code : '';
                $pageConfig->mold = $activityModel->mold;
            }
            if (isset($pageData['pageLanguages'][0])) {
                $pageLang = $pageData['pageLanguages'][0];
                $pageConfig->title = !empty($pageLang['seo_title']) ? $pageLang['seo_title'] : $pageLang['title'];
                $pageConfig->keywords = $pageLang['keywords'];
                $pageConfig->description = $pageLang['description'];
                $pageConfig->style_type = (int)$pageLang['style_type'];
                $pageConfig->background_color = $pageLang['background_color'];
                $pageConfig->background_image = $pageLang['background_image'];
                $pageConfig->background_position = $pageLang['background_position'];
                $pageConfig->background_repeat = $pageLang['background_repeat'];
                $pageConfig->statisticsCode = $pageLang['statistics_code'];
                $pageConfig->customCss = $pageLang['custom_css'];
                $pageConfig->multi_time_style = json_decode($pageLang['multi_time_style'], true);
                $pageConfig->share_image = $pageLang['share_image'] ?? '';
                $pageConfig->share_title = $pageLang['share_title'] ?? '';
                $pageConfig->share_desc  = $pageLang['share_desc'] ?? '';
                $pageConfig->share_link  = $pageLang['share_link'] ?? '';
            }

            !empty($componentStatic['css']) && $pageConfig->componentCss = $componentStatic['css'];
            !empty($componentStatic['js']) && $pageConfig->componentJs = $componentStatic['js'];

            $explainTpl = new ExplainTplComponent();
            $data = $explainTpl->getTplContent($pageConfig);
        }

        return $data;
    }

    /**
     * 根据模板ID获取头尾
     * @param $tplId
     * @param $lang
     * @return bool
     * @throws \yii\base\ViewNotFoundException
     */
    public function getHeadAndFooterByTplId($tplId, $lang)
    {
        $data = '';
        if ($tpl = PageTplModel::findOne($tplId)) {
            /** @var \app\modules\common\dl\components\CommonPageConfig $pageConfig */
            $pageConfig = new CommonPageConfig();
            $pageConfig->siteCode = $tpl->site_code;
            $pageConfig->lang = $lang;

            //对于html内容，需要将头尾拼上
            $componentStatic = ['css' => '', 'js' => ''];
            $cssVersion = app()->params['css_version'];
            $componentStatic['css'] = $this->getHeadExtraCss(0, $lang, $cssVersion, $tpl->site_code, $tpl->place);
            $componentStatic['js'] = $this->getHeadExtraJs($cssVersion, $lang, $tpl->site_code, $tpl->place);

            //!!!预览页特殊处理，预览页JS和CSS未打包，所以JS基础文件要放在页面组件前面，这里直接和CSS放在一起就行!!!
            $componentStatic['js'] .= '<script src="//geshopcss.logsss.com/vue/vue.min.js"></script>';
            $componentStatic['js'] .= '<script src="' . app()->url->assets->clientJs('initial', false) . '"></script>';
            $componentStatic['css'] .= $componentStatic['js'];
            $componentStatic['js'] = '';


            !empty($componentStatic['css']) && $pageConfig->componentCss = $componentStatic['css'];

            $explainTpl = new ExplainTplComponent();
            $data = $explainTpl->getTplContent($pageConfig, app()->controller->module->module->id);
        }

        return $data;
    }

    /**
     * 根据页面id获取页面布局关系
     *
     * @param int    $pageId 页面ID
     * @param string $lang   语言代码简称
     *
     * @return array
     * @throws \yii\db\Exception
     */
    private function getPageLayoutAndUiByPageId($pageId, $lang)
    {
        $uiListByLayoutPosition = [];
        $uiKeyList = [];

        //获取链接好的layout
        $orderedLayouts = $this->getOrderedComponents($this->getPageLayoutByPageId($pageId, $lang));
        if ($orderedLayouts) {
            //一次获取所有相关的UI组件，后面再进行拼接
            $uiList = $this->getPageUIByLayoutIds(array_column($orderedLayouts, 'id'), $lang);
            if ($uiList) {
                //按layout_id分好组的ui列表，并获取去重后的UI组件的key
                array_map(function ($value) use (&$uiListByLayoutPosition, &$uiKeyList) {
                    $uiListByLayoutPosition[ $value['layout_id'] ][ $value['position'] ][] = $value;
                    $keyAndTplId = $value['component_key'] . $this->delimiterInKeyAndTplId . ($value['tpl_id'] ?? 0);
                    if (!\in_array($keyAndTplId, $uiKeyList, true)) {
                        $uiKeyList[] = $keyAndTplId;
                    }
                }, $uiList);
            }
        }
        
        return [$orderedLayouts, $uiListByLayoutPosition, $uiKeyList];
    }

    /**
     * 根据页面id获取页面布局关系
     *
     * @param int $pageId 页面ID
     * @param string $lang 页面语言
     * @return array
     */
    public function getPageLayoutByPageId($pageId, $lang)
    {
        $moduleId = strtolower(app()->controller->module->module->id);
        if (\in_array($moduleId, ['activity', 'advertisement'], true) ) {
            $layoutList = $this->activityPageLayoutByPageId($pageId, $lang);
        } elseif ('home' === $moduleId) {
            $layoutList = $this->homePageLayoutByPageId($pageId, $lang);
        } else {
            $layoutList = [];
        }

        return ArrayHelper::toArray($layoutList);
    }

    /**
     * 活动页面布局关系
     *
     * @param $pageId
     * @param $lang
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    private function activityPageLayoutByPageId($pageId, $lang)
    {
        return PageLayoutModel::find()->alias('l')
            ->select('l.*, d.data, d.custom_css, d.background_color, d.background_img, a.site_code, p.activity_id')
            ->leftJoin(
                PageLayoutDataModel::tableName() . ' as d',
                'l.id = d.component_id AND d.lang = "' . $lang . '"'
            )->leftJoin(
                PageModel::tableName() . ' as p',
                'l.page_id = p.id'
            )->leftJoin(
                ActivityModel::tableName() . ' as a',
                'a.id = p.activity_id'
            )->where(['l.page_id' => $pageId, 'l.lang' => $lang])
            ->groupBy('l.id')
            ->all();
    }

    /**
     * 首页布局关系
     *
     * @param $pageId
     * @param $lang
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    private function homePageLayoutByPageId($pageId, $lang)
    {
        return PageLayoutModel::find()->alias('l')
            ->select('l.*, d.data, d.custom_css, d.background_color, d.background_img, p.site_code, p.activity_id')
            ->leftJoin(
                PageLayoutDataModel::tableName() . ' as d',
                'l.id = d.component_id'
            )->leftJoin(
                PageModel::tableName() . ' as p',
                'l.page_id = p.id'
            )->where(['l.page_id' => $pageId, 'l.lang' => $lang])
            ->groupBy('l.id')
            ->all();
    }

    /**
     * 根据页面id获取页面布局关系
     *
     * @param int|array $layoutIds 布局ID数组
     * @param string    $lang     语种
     * @return array
     * @throws \yii\db\Exception
     */
    public function getPageUIByLayoutIds($layoutIds, $lang)
    {
        $uiList = $this->getUiList($layoutIds);
        $list = $this->getUiDataByComponentId(ArrayHelper::toArray($uiList), $lang);

        $return = array();
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['lang'] = $v['lang'] ?? $lang;
                $list[$k]['data'] = $v['data'] ?? '{}';
                $return[$v['id']] = $list[$k];
            }
        }

        return $return;
    }

    /**
     * 根据页面id获取页面布局关系
     *
     * @param int|array $layoutIds 布局ID数组
     * @return array
     * @throws \yii\db\Exception
     */
    private function getUiList($layoutIds)
    {
        if (empty($layoutIds)) {
            return [];
        }

        return PageUiModel::find()->alias('pu')
            ->select('pu.*, ui.need_navigate, ut.is_vue_ssr')
            ->leftJoin(UiModel::tableName() . ' as ui', 'pu.component_key = ui.component_key')
            ->leftJoin(UiTplModel::tableName() . ' as ut', 'pu.tpl_id = ut.id')
            ->where([
                'pu.layout_id' => $layoutIds
            ])
            ->asArray()
            ->all();

    }

    /**
     * 获取有序的页面组件关系数据
     *
     * @param int page_id
     *
     * @return array $orderedComponents
     */
    public function getOrderedComponents($componentList)
    {
        if (!$componentList) {
            return [];
        }

        //返回的有序的页面组件关系数据
        $orderedComponents = [];

        //以下一节点值作为组件数组的key
        $componentList = array_column($componentList, null, 'next_id');

        //没有最后一个节点
        if (!isset($componentList[ $this->lastNodeNextId ])) {
            return $orderedComponents;
        }

        //最后一个节点id
        $last_id = $componentList[ $this->lastNodeNextId ]['id'];
        $orderedComponents[] = $componentList[ $this->lastNodeNextId ];
        unset($componentList[ $this->lastNodeNextId ]);

        //根据最后一个节点，反向查找所有的节点，并按顺序加入数组$layout_ids
        while (isset($componentList[ $last_id ])) {
            $last_node = $componentList[ $last_id ];
            array_unshift($orderedComponents, $last_node);

            //添加一个节点id之后，要从布局数组中移除当前节点，并将$cur_id换成前一个节点的id
            unset($componentList[ $last_id ]);
            $last_id = $last_node['id'];
        }

        return $orderedComponents;
    }

    /**
     * 获取有序的页面组件关系数据（按position划分）
     *
     * @param array $componentList
     *
     * @return array $orderedComponents
     */
    public function getOrderedComponentsByPosition($componentList)
    {
        if (!$componentList) {
            return [];
        }

        $componentListByPosition = [];
        foreach ($componentList as $item) {
            $componentListByPosition[$item['position']][] = $item;
        }
        ksort($componentListByPosition);

        foreach ($componentListByPosition as $key => $val) {
            $componentListByPosition[$key] = $this->getOrderedComponents($val);
        }

        return $componentListByPosition;
    }

    /**
     * 解析Layout组件
     *
     * @param \app\modules\common\components\CommonLayout $layout
     * @param array                                       $layoutArr 布局组件信息数组
     * @param array                                       $uiData    布局下的UI的html数组
     *
     * @return string
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    private function parseLayout($layout, $layoutArr, $uiData)
    {
        $layout->instanceId = $layoutArr['id'];
        $layout->key = $layoutArr['component_key'];
        $layout->lang = $layoutArr['lang'];
        $layout->data = $uiData;
        $layout->layoutData = json_decode($layoutArr['data'], true);
        $layout->customData = [
            'custom_css'       => $layoutArr['custom_css'],
            'background_color' => $layoutArr['background_color'],
            'background_img'   => $layoutArr['background_img']
        ];
        if (!$componentHtml = $layout->renderComponent($layout)) {//组件解析
            if ($this->needBreakParse) {
                app()->response->format = Response::FORMAT_JSON;
                throw new JsonResponseException(1, 'layout组件解析失败：' . $layout->errors);
            }

            return sprintf(
                $this->layoutParseErrorTpl,
                $layout->key,
                $layout->instanceId,
                'layout组件解析失败：' . $layout->errors
            );
        }

        return $componentHtml;
    }

    /**
     * 解析UI组件
     *
     * @param \app\modules\common\dl\components\CommonUi $ui
     * @param array $uiArr UI组件信息数组
     *
     * @return string
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    private function parseUI($ui, $uiArr)
    {
        $ui->instanceId = $uiArr['id'];
        $ui->key = $uiArr['component_key'];
        $ui->data = json_decode($uiArr['data'], true);
        $ui->data['need_navigate'] = $uiArr['need_navigate'] ?? '0';//标识ui组件是否需要导航
        $ui->data['is_auto_refresh_ui'] = empty($uiArr['async_data_format']) ? false : true; // 标识ui组件是否自动刷新组件

        if (!isset($ui->data['nav_menu']) || !empty($ui->data['nav_menu'])) {
            $ui->data['navData'] = $this->getAvailableNavigation($uiArr['id'], $uiArr['lang']);
            if (!isset($ui->data['nav_menu'])) {
                foreach (array_keys($ui->data['navData']) as $v) {
                    $ui->data['nav_menu'][] = (string) $v;
                }
            }
        }

        $ui->lang = $uiArr['lang'];
        $ui->tplId = $uiArr['tpl_id'];
        $ui->layout_id = $uiArr['layout_id'];
        $ui->isVueSsr = $uiArr['is_vue_ssr'] ?? 0; // 是否使用node渲染 0：否 1：是
        if (!$componentHtml = $ui->renderComponent($ui)) {//组件解析
            if ($this->needBreakParse) {
                app()->response->format = Response::FORMAT_JSON;
                throw new JsonResponseException(1, 'ui组件解析失败：' . $ui->errors);
            }

            return sprintf(
                $this->uiParseErrorTpl,
                $ui->key,
                $ui->instanceId,
                'ui组件解析失败：' . $ui->errors
            );
        }

        return $componentHtml;
    }

    /**
     * 根据组件ID获取组件对应的静态JS和CSS
     *
     * @param array $componentKeys 组件的component_key和tpl_id
     *
     * @return array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    private function getJSAndCssByComponentKeys($componentKeys)
    {
        $explain = new ExplainComponent();
        $staticsList = [];
        foreach ($componentKeys as $key) {
            $explode = explode($this->delimiterInKeyAndTplId, $key);
            if (false !== ($config = $explain->getStaticConfig($explode[0], $explode[1]))) {
                $staticsList['css'][] = $config['css'];
                $staticsList['js'][] = $config['js'];
            }
        }

        return $staticsList;
    }

    /**
     * 获取page页面单纯的html，不包含js和css
     *
     * @param array $orderedLayouts         排好序的Layout组件列表
     * @param array $uiListByLayoutPosition 按LayoutId和position分好组的UI组件列表
     * @param bool $isPageTplExplain 是否页面模板解析(页面模板解析有很多参数缺失，无需校验的)
     *
     * @return string
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    private function getPageHtml($orderedLayouts, $uiListByLayoutPosition, $isPageTplExplain = false)
    {
        //页面解析之后的html字符串
        $pageHtml = '';
        $ui = new CommonUi();
        $layout = new CommonLayout();
        if (!empty($orderedLayouts)) {
            $ui->siteCode = $orderedLayouts[0]['site_code'] ?? '';
            $ui->activityId = $orderedLayouts[0]['activity_id'] ?? 0;
            $ui->pageId = $orderedLayouts[0]['page_id'] ?? 0;
            $isPageTplExplain && $ui->isPageTplExplain = $isPageTplExplain;
            $layout->siteCode = $orderedLayouts[0]['site_code'] ?? '';
            $layout->activityId = $orderedLayouts[0]['activity_id'] ?? 0;
            $layout->pageId = $orderedLayouts[0]['page_id'] ?? 0;
            $isPageTplExplain && $layout->isPageTplExplain = $isPageTplExplain;

            // 添加layout的楼层信息，供数据埋点使用，仅在整个页面渲染时添加，单个layout的渲染不加（计算太复杂）
            $layout->layoutList = $ui->layoutList = array_column($orderedLayouts, 'id');
            foreach ($orderedLayouts as $layoutArr) {
                $layout->layoutId = $ui->layoutId = $layoutArr['id'];
                $uiData = $this->getUiData($uiListByLayoutPosition, $layoutArr['id'], $ui);
                $pageHtml .= $this->parseLayout($layout, $layoutArr, $uiData);
            }
        }

        return $pageHtml;
    }

    /**
     * 获取填充到Layout组件中的UiData
     *
     * @param array                                   $uiListByLayoutPosition 按LayoutId和position分好组的UI组件列表
     * @param int                                     $layoutId               layout组件ID
     * @param \app\modules\common\components\CommonUi $ui                     ui组件
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    private function getUiData($uiListByLayoutPosition, $layoutId, $ui)
    {
        $uiData = [];
        if (!empty($uiListByLayoutPosition[ $layoutId ])) {
            $ui->uiList = []; // 初始化，防止foreach循环堆积数据
            /** @var array[] $uiListByLayoutPosition */
            // 添加layout的楼层信息，供数据埋点使用，仅在整个页面渲染时添加，单个layout的渲染不加（计算太复杂）
            foreach ($uiListByLayoutPosition[ $layoutId ] as $uiListByPosition) {
                array_push($ui->uiList, ...$this->getOrderedComponents($uiListByPosition));
            }
            $ui->uiList = array_column($ui->uiList, 'id');

            foreach ($uiListByLayoutPosition[ $layoutId ] as $uiListByPosition) {
                $uiListByOrder = $this->getOrderedComponents($uiListByPosition);
                foreach ($uiListByOrder as $uiArr) {
                    //UI组件的解析只返回单纯的HTML
                    $uiData[ $uiArr['position'] ] = !empty($uiData[ $uiArr['position'] ]) ?
                        $uiData[ $uiArr['position'] ] . $this->parseUI($ui, $uiArr) : $this->parseUI($ui, $uiArr);
                }
            }
        }

        return $uiData;
    }

    /**
     * 获取页面的css和js内容
     *
     * @param array $componentKeys 组件的component_key
     * @param string $siteCode
     *
     * @return array
     * @throws \Throwable
     */
    private function getPageStatics($componentKeys, $siteCode)
    {
        $staticsList = $this->getJSAndCssByComponentKeys($componentKeys);
        $site = strpos($siteCode, '-') ? mb_substr($siteCode, 0, strpos($siteCode, '-')) : '';

        $cssHtml = $jsHtml = '';
        if (!empty($staticsList['css'])) {
            foreach ((array) $staticsList['css'] as $css) {
                if (file_exists($css) && false !== ($cssContent = file_get_contents($css))) {
                    $cssHtml .= trim($cssContent);
                }
                $cssHtml .= $this->getSiteComponentStatics($css, $site);
            }
        }
        if (!empty($staticsList['js'])) {
            foreach ((array) $staticsList['js'] as $js) {
                if (file_exists($js) && false !== ($jsContent = file_get_contents($js))) {
                    $jsHtml .= trim($jsContent);
                }
                $jsHtml .= $this->getSiteComponentStatics($js, $site);
            }
        }

        return ['css' => $cssHtml, 'js' => $jsHtml];
    }

    /**
     * 获取页面各站点组件特定的css和js内容
     *
     * @param $path
     * @param $site
     *
     * @return string
     */
    public function getSiteComponentStatics($path, $site)
    {
        $dirName = pathinfo($path)['dirname'];
        //$siteDir = substr_replace($dirName, $site, strrpos($dirName, '/') + 1, strlen($dirName));
        $siteDir = $dirName . '/' . $site;
        $siteStatic = str_replace($dirName, $siteDir, $path);

        if (file_exists($siteStatic) && false !== ($siteStaticContent = file_get_contents($siteStatic))) {
            return trim($siteStaticContent);
        }

        return '';
    }

    /**
     * 组装静态资源
     *
     * @param array $componentKeys 组件的component_key和tpl_id
     * @param string $siteCode
     *
     * @return string
     * @throws \Throwable
     */
    private function buildStatics($componentKeys, $siteCode)
    {
        $statics = $this->getPageStatics($componentKeys, $siteCode);

        return '<style type="text/css">' . $statics['css'] . '</style>'
            . '<script defer="defer">' . $statics['js'] . '</script>';
    }

    /**
     * 获取附加的额外CSS
     *
     * @param int $pageId !!!pageId=0的场景是页面模板预览的时候
     * @param string $lang
     * @param string $cssVersion
     * @param string $siteCode
     * @param int $activityPageType 参考SiteConstants类常量 ACTIVITY_PAGE_TYPE_*
     * @param bool $isPublish
     *
     * @return string
     * @see \app\base\SiteConstants
     */
    public function getHeadExtraCss($pageId, $lang, $cssVersion, $siteCode, $activityPageType, $isPublish = false)
    {
        $redirectUrl = '';// 模板页的预览不需要跳转地址
        $multiTimeStyle = '""';
        if ($pageId) {
            $pageLanguage = PageLanguageModel::findOne([
                'page_id' => $pageId,
                'lang' => $lang
            ]);

            if ($pageLanguage) {
                $redirectUrl = $pageLanguage->redirect_url;
                $multiTimeStyle = "'". $pageLanguage->multi_time_style ."'";
            }
        }
        $platform = explode('-', $siteCode);
        $platform = $platform[1] ?: '';

        /**
         * ！！！这段JS的位置不要移动！！！
         * 这里在CSS中添加的JS文件是用来做手机端访问Web端页面时自动跳转到wap端页面去的，
         * 放在这里是为了让这段JS在头部加载，能更快的执行
         */
        $jsPageType = $isPublish ? 3 : 2; // 2 预览页面 3 发布页面
        $envType = 1;
        if ('test' === YII_ENV) {
            $envType = 2;
        } elseif ('prerelease' === YII_ENV) {
            $envType = 3;
        } elseif ('product' === YII_ENV) {
            $envType = 4;
        }

        // 站点域名
        $_url = app()->params['sites'][ $siteCode ]['secondary_domain'][$lang] ?? '';
        $siteDomain = empty($_url) ? '' : parse_url($_url, PHP_URL_HOST);

        // 组件API兜底是否启用
        $isUiApiFallbackEnabled = (int)SysConfigUtils::get('sys.ui.sync_api_fallback_enabled', 0);
        // 是否在组件里直接使用API的兜底数据，不请求站点接口
        $isDirectUseApiFallbackData = (int)SysConfigUtils::get('sys.ui.dl.direct_use_api_fallback_data', 0);
        $jsIsDirectUseFallback = (($isUiApiFallbackEnabled === 1) && ($isDirectUseApiFallbackData === 1)) ? 1 : 0;

        $css = /** @lang html */
            '<script type="text/javascript">var HTTPS_REDIRECT_LINK = "' . $redirectUrl . '";'
            . ' var GESHOP_LANG = "' . $lang . '"; var GESHOP_SITECODE = "' . $siteCode . '";'
            . ' var GESHOP_SITE_DOMAIN = "' . $siteDomain . '";'
            . ' var GESHOP_ENV_TYPE = "' . $envType . '";'
            . ' var GESHOP_PAGE_TYPE = "' . $jsPageType . '";'
            . ' var GESHOP_PID = "' . $pageId . '";'
            . ' var GESHOP_PUBLISHED_TIME = "' . time() . '";'
            . ' var GESHOP_PLATFORM = "' . $platform . '";var GESHOP_MULTI_TIME_STYLE = ' . $multiTimeStyle . ';'
            . ' var GESHOP_INTERFACE = ' . json_encode($this->getInterfaceConfig($siteCode)) . ';'
            . ' var GESHOP_IS_PRERELEASE = ' . (YII_ENV === 'prerelease' ? 'true' : 'false') . ';'
            . ' var GESHOP_STATIC = "' . (new CommonPageConfig())->staticDomain . '";'
            . ' var GESHOP_IS_DIRECT_USE_FALLBACK = ' . $jsIsDirectUseFallback . ';'
            . Translate::getUiComponentJsTransMessage($lang,SitePlatform::getSiteGroupCodeBySiteCode($siteCode))
            . '</script>';

        if ($isPublish && SitePlatform::isPcPlatform($siteCode)) {
            // 只有发布时且为PC端才需要这个，且直接加载内容，不使用引入文件形式
            $redirectJs = app()->basePath . '/htdocs/resources/sitesPublic/js/redirect.js';
            if (file_exists($redirectJs)) {
                $css .= '<script type="text/javascript">'
                    . htmlspecialchars_decode(file_get_contents($redirectJs))
                    . '</script>';//要将实体转回字符
            }
        }

        if ($isPublish) {
            // 2018-09-29 滕家顺 此处css代码已被合并到组件CSS文件的头部，详情见CommonPublishTrait->createStatics()
            return $css;
        }

        $staticDomain = (new CommonPageConfig())->staticDomain;
        $cssGridFormat = '<link rel="stylesheet" href="%s/frontend/sites/stylesheets/geshop-grid-responsive.css?version=%s">';
        $cssGridHtml = sprintf($cssGridFormat, $staticDomain, $cssVersion);

        return $css . $cssGridHtml;
    }

    /**
     * 获取附加的额外JS
     *
     * @param string $version
     * @param string $lang
     * @param string $siteCode
     * @param int $activityPageType 参考SiteConstants类常量 ACTIVITY_PAGE_TYPE_*
     * @param bool $isPublish 是否发布(这里有2处调用，发布和预览)
     *
     * @return string
     * @see \app\base\SiteConstants
     */
    public function getHeadExtraJs($version, $lang, $siteCode, $activityPageType, $isPublish = false)
    {
        if ($isPublish) {
            // 2018-09-29 滕家顺 此处js代码已被合并到组件JS文件的头部，详情见CommonPublishTrait->createStatics()
            return '';
        }

        $staticDomain = (new CommonPageConfig())->staticDomain;
        $jsFilenameSuffix = ($activityPageType === SiteConstants::ACTIVITY_PAGE_TYPE_HOME) ? 'home' : 'activity';
        $jsHtml = sprintf('<script src="%s/frontend/geshop/library/LAB.js?version=%s"></script>', $staticDomain, $version);

        $jsCommonFormat = '<script src="%s/resources/sitesPublic/%s/js/gs_common_%s.min.js?version=%s"></script>';
        //是否为电子站点
        $jsHtml .= sprintf($jsCommonFormat, $staticDomain, 'default', $jsFilenameSuffix, $version);

        // 2019-03-22 新版本VUE组件核心函数
        // $platformCode = SitePlatform::getPlatformCodeBySiteCode($siteCode);
        // $js_vue_main = sprintf('<script src="//geshopcss.logsss.com/vue/vue.min.js"></script>', $staticDomain, $version);
        // $js_vue_vendor = sprintf('<script src="%s/develop/client.bundle.js"></script>', $staticDomain, $version);
        // $jsHtml .= $js_vue_main . $js_vue_vendor;

        return $jsHtml;
    }

    /**
     * 获取站点的接口配置
     * @param $siteCode
     * @return array
     */
    public function getInterfaceConfig($siteCode)
    {
        $file = app()->basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sites'
            . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'interface.' . YII_ENV . '.php';
        $interface = require($file);
        $allConf = $interface[$siteCode] ?? [];
        $conf = [];
        if (!empty($allConf)) {
            foreach ($allConf as $key => $val) {
                if (!empty($val['isJsonp'])) {
                    // 正式环境只需要展示url一个值就行，其他诸如method、description、example都无需展示
                    $conf[$key] = app()->env->isProduct() ? [
                        'url' => $val['url']
                    ] : $val;
                }
            }
        }
        
        return $conf;
    }

    /**
     * 获取下线页面html
     *
     * @param string $siteCode  站点code
     * @param int $pageId       活动子页面ID
     * @param string $lang      语言代码简称
     * @param bool $isAppSite   是否为app站点
     *
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    private function getOfflineHtml($siteCode, $pageId, $lang, $isAppSite=fasle)
    {
        $pageConfig = new CommonPageConfig();
        $pageConfig->lang = $lang;
        $pageConfig->siteCode = $siteCode;
        $explainTplComponent = new ExplainTplComponent();

        $content = NULL;
        if ($isAppSite) {
            //使用下线模板
            $content = $explainTplComponent->getOfflineContent($pageConfig, true, []);
        } else {
            //子页面下线提示框
            $pageLangInfo = PageLanguageModel::find()->where(['page_id' => $pageId, 'lang' => $lang])->asArray()->one();
            $tplData = [
                'isToHome' => empty($pageLangInfo['end_url']) ? true : false,
                'countDownSeconds' => 5
            ];

            if ($tplData['isToHome']) {
                //跳转至首页
                $homeUrl = '/';
                if (isset(app()->params['sites'][$siteCode]['secondary_domain'][$lang])) {
                    $homeUrl = str_replace('promotion', '', app()->params['sites'][$siteCode]['secondary_domain'][$lang]);
                }
                $tplData['endUrl'] = $homeUrl;
            } else {
                //跳转至指定页面
                $tplData['endUrl'] = $pageLangInfo['end_url'];
            }
            $content = $explainTplComponent->getOfflineContent($pageConfig, false, $tplData);
        }
        return $content;
    }

    /**
     * 获取可选的导航菜单
     *
     * @param $uiComponentId
     * @param $lang
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getAvailableNavigation($uiComponentId, $lang)
    {
        $uiComponentInfo = PageUiModel::findOne($uiComponentId);
        $pageLayoutInfo = !$uiComponentInfo ? [] : PageLayoutModel::findOne((int) $uiComponentInfo->layout_id);

        return $pageLayoutInfo ? $this->getNeedNavigateUiTitle($pageLayoutInfo->page_id, $lang) : [];
    }

    /**
     * @param $pageId
     * @param $lang
     *
     * @return array
     * @throws \yii\db\Exception
     */
    private function getNeedNavigateUiTitle($pageId, $lang)
    {
        $goodsListTitle = [];

        //获取链接好的layout
        $orderedLayouts = $this->getOrderedComponents($this->getPageLayoutByPageId($pageId, $lang));
        if ($orderedLayouts) {
            foreach ($orderedLayouts as $orderedLayout) {
                //获取当前布局组件所有相关的UI组件
                $uiList = $this->getPageUIByLayoutIds($orderedLayout['id'], $lang);
                $uiList = $this->getOrderedComponents($uiList); //按照顺序重新排列

                $navigateTitle = $this->getNavigateTitle($uiList);
                $goodsListTitle += $navigateTitle;

            }
        }

        return $goodsListTitle;
    }

    /**
     * 获取导航标题
     *
     * @param array $uiList
     *
     * @return array
     */
    private function getNavigateTitle($uiList)
    {
        $navigateTitle = [];
        if (empty($uiList)) {
            return $navigateTitle;
        }

        foreach ($uiList as $uiData) {
            if ((int) $uiData['need_navigate'] === 1) {
                $uiArray = !empty($uiData['data']) ? json_decode($uiData['data'], true) : [];
                $navigateTitle[ $uiData['id'] ] = !empty($uiArray['titleNavText']) ? $uiArray['titleNavText'] :
                    (!empty($uiArray['titleText']) ? $uiArray['titleText'] : '请设置导航标题');
            }
        }

        return $navigateTitle;
    }

    /**
     * 格式化页面模板数据
     *
     * @param \app\modules\common\models\PageTplModel $item
     *
     * @return array
     */
    private function formatData($item)
    {
        $layout = json_decode($item->layout, true);
        $layoutData = json_decode($item->layout_data, true);
        $uiList = json_decode($item->ui, true);
        $uiData = json_decode($item->ui_data, true);

        if (!empty($layout)) {
            /** @var array $layout */
            foreach ($layout as $k => $v) {
                $layout[$k]['site_code'] = $item->site_code;
                $layout[$k]['lang'] = $item->lang;
                $layout[$k]['data'] = $layoutData[$k]['data'];
                $layout[$k]['custom_css'] = (string) $item->custom_css;
                $layout[$k]['background_color'] = '';
                $layout[$k]['background_img'] = '';
                $layout[$k]['page_id'] = 0;
                $layout[$k]['activity_id'] = 0;
            }
        }

        $uiKeyList = [];
        if (!empty($uiList)) {
            list($uiList, $uiKeyList) = $this->handleUiList($uiList, $uiData, $item->lang);
        }

        return [$layout, $uiList, $uiKeyList];
    }

    /**
     * 处理uiList数据
     *
     * @param array $list
     * @param array $data
     * @param string $lang
     *
     * @return array
     */
    private function handleUiList(array $list, array $data, string $lang)
    {
        $uiKeyList = [];
        foreach ($list as $k => &$v) {
            $tmp = [];
            if (\is_array($data[ $k ])) {
                foreach ($data[ $k ] as $val) {
                    $tmp += $val;
                }
            }

            /** @var array $v */
            foreach ($v as &$vv) {
                /** @var array $vv */
                foreach ($vv as &$vvv) {
                    if (!isset($vvv['position'])) {
                        // 兼容旧数据，没有列的默认第一列
                        $vvv['position'] = '1';
                    }

                    !isset($vvv['is_vue_ssr']) && $vvv['is_vue_ssr'] = 0; // 兼容旧数据
                    $vvv['layout_id'] = $k;
                    $vvv['lang'] = $lang;
                    $uiKeyList[] = $vvv['component_key'] . '-' . $tmp[ $vvv['id'] ]['tpl_id'];
                    $vvv = isset($tmp[ $vvv['id'] ]) ? array_merge($vvv, $tmp[ $vvv['id'] ]) : $vvv;
                }
            }
        }
        unset($v, $vv, $vvv);
        $uiKeyList = array_unique($uiKeyList);

        return [$list, $uiKeyList];
    }

    /**
     * 替换原有布局组件数据
     *
     * @param array  $list
     * @param string $lang 语种
     * @return array
     */
    public function getUiDataByComponentId($list, $lang='')
    {
        if (empty($list)) {
            return [];
        }

        $conditionArr = [];
        foreach ($list as $item) {
            $conditionArr[] = '(component_id = ' . $item['id'] . ' AND (tpl_id = ' . $item['tpl_id'] . ' OR `is_public`=1))';
        }
        $condition = '(' . implode(' OR ', $conditionArr) . ')';

        if (!empty($lang)) {
            $condition .= ' AND lang="'.$lang.'"';
        }

        $rs = PageUiComponentDataModel::find()
            ->select('`component_id`, `lang`, `key`, `value`')
            ->where($condition)
            ->asArray()
            ->all();
        if (empty($rs)) {
            return $list;
        }

        $result = array();
        foreach ($rs as $v) {
            if (!isset($result[ $v['component_id'] ]['lang'])) {
                $result[ $v['component_id'] ]['lang'] = $v['lang'];
            }
            $result[ $v['component_id'] ][ $v['key'] ] = json_decode($v['value'], true);
        }

        foreach ($list as $k => $v) {
            $list[ $k ]['lang'] = $lang;
            $list[ $k ]['data'] = '{}';
            if (isset($result[ $v['id'] ])) {
                $list[ $k ]['lang'] = $result[ $v['id'] ]['lang'];
                unset($result[ $v['id'] ]['lang']);
                $list[ $k ]['data'] = json_encode($result[ $v['id'] ], JSON_UNESCAPED_UNICODE);
            }
        }

        return $list;
    }

    /**
     * 获取组件最新产品数据
     * @param    array   $uiList
     * @return   array
     */
    private function getUiGoodsData($uiList)
    {
        foreach ($uiList as $key => $value) {
            foreach ($value as $k => $row) {
                foreach ($row as $y => $item) {
                    $tplId = $item['tpl_id'];
                    $rows = PageUiComponentDataModel::find()
                        ->select('`key`, `value`')
                        ->where([
                            'component_id' => $item['id'],
                            'lang'         => $item['lang'],
                            'key'          => ['goodsSKU', 'ipsGoodsSKU']
                        ])
                        ->andWhere(['or', 'tpl_id='. $tplId , 'is_public=1'])
                        ->orderBy(['is_public' => SORT_DESC]) // 私有数据优先
                        ->asArray()
                        ->all();

                    if ($rows) {
                        $data = json_decode($item['data'], true);
                        foreach ($rows as $row) {
                            $data[$row['key']] = trim($row['value'], '"');
                        }
                        $uiList[ $key ][ $k ][ $y ]['data'] = json_encode($data);
                    }
                }
            }
        }
        return $uiList;
    }
}
