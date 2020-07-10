<?php
namespace app\components\site\dl;

use app\components\auto\AutoRefreshUi;
use app\components\auto\AutoRefreshPage;
use app\modules\common\dl\traits\CommonPublishTrait;
use app\modules\common\dl\models\PageModel;
use app\modules\common\dl\models\PageUiModel;
use app\modules\common\dl\models\PageTplModel;
use app\modules\common\dl\models\PageUiTemplateModel;

class PagePreview
{
    use CommonPublishTrait;

    /** @var AutoRefreshPage $autoRefreshPage */
    private $autoRefreshPage;

    /**
     * 装修页面预览
     *
     * @param PageModel $pageModel
     * @param string $langCode
     * @param bool $useCache
     * @return string
     */
    public function getDesignPreview($pageModel, $langCode, $useCache = false)
    {
        $this->autoRefreshPage = new AutoRefreshPage($pageModel, $langCode);
        $renderResult = $this->parsePage($pageModel->id, $langCode, true, $useCache, false);
        return $this->getPreviewPageHtml($pageModel, $langCode, $renderResult);
    }

    /**
     * 页面模板预览
     *
     * @param int    $id 页面模板id
     * @param string $pid 页面32位长度pid
     * @param string $lang 语种
     * @return string
     */
    public function getPageTemplatePreview($id, $pid, $lang)
    {
        $pageTplModel = PageTplModel::findOne($id);
        if (empty($pageTplModel)) {
            return '页面模板不存在';
        }

        /** @var PageModel $pageModel */
        $pageModel = PageModel::getByPId($pid);
        if (empty($pageTplModel)) {
            return '页面不存在';
        }

        list($layout, $uiList, $uiKeyList) = $this->formatData($pageTplModel);
        if (empty($layout) || empty($uiKeyList)) {
            return '';
        }

        // 解析自动刷新组件异步数据
        $this->autoRefreshPage = new AutoRefreshPage($pageModel, $lang);
        $this->autoRefreshPage->setTemplateUiList($this->getAutoUiList($uiList));

        // 获取页面的js和css，并拼装
        $renderResult = $this->getPageStatics($uiKeyList, $pageModel->site_code);

        // 获取页面单纯的html
        $renderResult['html'] = $this->getPageHtml($layout, $uiList, true);

        $html = $this->getPreviewPageHtml($pageModel, $lang, $renderResult);
        return $this->hideHeaderAndFooter($html);
    }

    /**
     * 组件模板预览
     *
     * @param int $tipId 组件模板ID
     * @return string
     */
    public function getUiTemplatePreview($tipId)
    {
        /** @var PageUiTemplateModel $templateModel */
        $templateModel = PageUiTemplateModel::findone($tipId);
        if (!$templateModel) {
            return '模板不存在!';
        }

        /** @var PageModel $pageModel */
        $pageModel = PageModel::findOne($templateModel->page_id);
        if (empty($pageModel)) {
            return '页面不存在';
        }

        $layoutId = -1;
        $layout = [
            [
                'site_code' => $pageModel->site_code,
                'activity_id' => 0,
                'page_id' => 0,
                'id' => $layoutId,
                'component_key' => 'L000001',
                'lang' => $templateModel->lang,
                'custom_css' => '',
                'background_color' => '',
                'background_img' => '',
                'data' => '[]'
            ]
        ];

        $uiInfo = json_decode($templateModel->ui, true);
        $uiInfo['id'] = -1;
        $uiInfo['next_id'] = 0;
        $uiInfo['position'] = 1;
        $uiInfo['layout_id'] = $layoutId;
        $uiInfo['data'] = $templateModel->ui_data;
        $uiList = [$layoutId => [1 => [$uiInfo]]];
        $uiKeyList = [$uiInfo['component_key'] .'-'. $uiInfo['tpl_id']];

        $lang = $templateModel->lang;
        // 解析自动刷新组件异步数据
        $this->autoRefreshPage = new AutoRefreshPage($pageModel, $lang);
        $this->autoRefreshPage->setTemplateUiList($this->getAutoUiList($uiList));

        // 获取页面的js和css，并拼装
        $renderResult = $this->getPageStatics($uiKeyList, $pageModel->site_code);

        // 获取页面单纯的html
        $renderResult['html'] = $this->getPageHtml($layout, $uiList, true);

        $html = $this->getPreviewPageHtml($pageModel, $lang, $renderResult);
        return $this->hideHeaderAndFooter($html);
    }

    /**
     * 获取页面模板里的自动刷新组件
     *
     * @param array $uiList
     * @return PageUiModel[]
     */
    protected function getAutoUiList($uiList)
    {
        $pageUiModelList = [];
        foreach($uiList as $layoutUiList) {
            foreach ($layoutUiList as $positionUiList) {
                foreach ($positionUiList as $uiInfo) {
                    // 判断是否为自动刷新组件
                    if (isset($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT])
                        && !empty($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT]))
                    {
                        $pageUiModel = new PageUiModel();
                        $pageUiModel->setAttributes($uiInfo, false);
                        $pageUiModelList[] = $pageUiModel;
                    }
                }
            }
        }

        return $pageUiModelList;
    }

    /**
     * 隐藏页面头尾
     * @param string $html
     * @return string
     */
    private function hideHeaderAndFooter($html)
    {
        $html = str_replace('<header', '<header style="display:none"', $html);
        $html = str_replace('<footer', '<footer style="display:none"', $html);
        return $html;
    }

    /**
     * 获取页面html
     *
     * @param PageModel $pageModel
     * @param string $langCode
     * @param array $renderResult
     * @return string
     */
    protected function getPreviewPageHtml($pageModel, $langCode, $renderResult)
    {
        $activityPageType = PageModel::getActivityPageType($pageModel);
        $pageId = $pageModel->id;
        $siteCode = $pageModel->site_code;
        $cssVersion = app()->params['css_version'];

        $pageStatic['css'] = $this->getHeadExtraCss($pageId, $langCode, $cssVersion, $siteCode, $activityPageType);
        $pageStatic['js'] = $this->getHeadExtraJs($cssVersion, $langCode, $siteCode, $activityPageType);

        // 组件自动刷新，异步数据js数据输出
        $jsAsyncDataInfo = $this->autoRefreshPage->getAsyncDataJsVariable();

        // 预览页面专有全局变量
        $css = /** @lang html */
            '<script type="text/javascript">'
            . $jsAsyncDataInfo
            . '</script>';
        $pageStatic['css'] .= $css;

        //!!!预览页特殊处理，预览页JS和CSS未打包，所以JS和CSS放到头尾位置!!!
        $pageStatic['css'] .= '<style type="text/css">' . $renderResult['css'] . '</style>';
        
        if ('dev' === YII_ENV) {
            $pageStatic['js'] .= '<script src="/resources/vue/vue.js"></script>';
        } else {
            $pageStatic['js'] .= '<script src="//geshopcss.logsss.com/vue/vue.min.js" rel="preload"></script>';
        }
        $pageStatic['js'] .= '<script src="' . app()->url->assets->clientJs('initial') . '" rel="preload"></script>';
        $pageStatic['js'] .= '<script defer="defer">' . $renderResult['js'] . '</script>';
        
        //获取头尾
        $sitePageHtml = $this->getHeadAndFooterByPageId($pageId, $langCode, $pageStatic);
        if (empty($sitePageHtml)) {
            return '平台头尾未找到';
        }

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(self::$dragClass, '', $renderResult['html']);
        //页面的content用div包起来
        $html = $this->packageContent($html);

        return $this->replaceSiteMainBody($sitePageHtml, $html);

    }

}
