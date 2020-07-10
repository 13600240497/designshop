<?php
namespace app\components\auto;

use app\modules\common\models\NativePageLayoutModel;
use app\modules\common\models\NativePageUiComponentModel;
use app\modules\component\zf\components\ExplainComponent;
use Yii;
use ego\base\JsonResponseException;
use app\base\SitePlatform;
use app\common\constants\SiteConstants;
use app\modules\common\models\PageUiModel as RGPageUiModel;
use app\modules\common\dl\models\PageUiModel as DLPageUiModel;
use app\modules\common\zf\models\PageUiModel as ZFPageUiModel;

/**
 * 自动刷新活动页面
 *
 * 注意： 这里是公共类同时支持RG、ZF、DL站点，变量注解以ZF站点类为例
 *
 * @author TianHaisen
 * @since 1.9.3
 */
class AutoRefreshPage
{
    const ASYNC_DATA_JS_VARIABLE_NAME = 'GESHOP_ASYNC_DATA_INFO'; // 活动页面组件异步数据JS变量名称

    /** @var \app\modules\common\zf\models\PageModel 活动页面Model对象 */
    private $pageModel;

    /** @var string 语言简码 */
    private $lang;

    /** @var array 所有自动刷新组件异步数据 */
    private $uiAsyncDataInfo = null;

    /** @var string 网站简码，如： rg/zf/dl */
    private $websiteCode;

    /** @var ZFPageUiModel[] 页面模板或UI组件模板缓存的组件列表 */
    private $tplUiModelList = null;

    /**
     * 构造函数
     *
     * @param \app\modules\common\zf\models\PageModel $pageModel 活动页面Model对象
     * @param string $lang 语言简码
     * @throws AutoRefreshException
     */
    public function __construct($pageModel, $lang)
    {
        if (empty($pageModel)) {
            throw new AutoRefreshException('无效的参数!');
        }

        $this->websiteCode = SitePlatform::splitSiteCode($pageModel->site_code)[0];
        $this->pageModel = $pageModel;
        $this->lang = $lang;
    }

    /**
     * 设置页面模板或UI组件模板缓存的组件列表
     * @param ZFPageUiModel[] $tplUiModelList 页面模板或UI组件模板缓存的组件列表
     */
    public function setTemplateUiList($tplUiModelList)
    {
        $this->tplUiModelList = $tplUiModelList;
    }

    /**
     * 解析所有自动刷新组件异步数据
     *
     * @param bool $throwOnException 遇到异常时是否抛出
     * @param bool $usePublishedCache 是否使用发布缓存组件数据
     * @throws AutoRefreshException
     */
    public function parseAllUiAsyncData($throwOnException = false, $usePublishedCache = false)
    {
        if (null === $this->uiAsyncDataInfo) {
            // 不是模板缓存组件
            if (null === $this->tplUiModelList) {
	            $pageUiModelList = [];
	            //优先从缓存获取页面组件
	            if ($usePublishedCache) $pageUiModelList = $this->getPublishedCacheAutoRefreshUiList();
	            if (empty($pageUiModelList)) $pageUiModelList = $this->getAutoRefreshUiList();
            } else {
                $pageUiModelList = $this->tplUiModelList;
            }

            $this->uiAsyncDataInfo = [];
            if (empty($pageUiModelList)) {
//                $message = sprintf(
//                    '站点 %s 页面ID %d 语言 %s 没有获取到组件数据!',
//                    $this->pageModel->site_code, $this->pageModel->id, $this->lang
//                );
//                Yii::error($message, __METHOD__);
                return;
            }

            foreach ($pageUiModelList as $pageUiModel) {
                try {
                    $autoRefreshUi = new AutoRefreshUi($this->pageModel, $pageUiModel);
                    $this->uiAsyncDataInfo[$pageUiModel->id] = $autoRefreshUi->getAsyncData();
                    unset($autoRefreshUi);
                } catch (\Throwable $e) {
                    if ($throwOnException) {
                        throw new AutoRefreshException($e->getMessage());
                    }
                    $this->uiAsyncDataInfo[$pageUiModel->id] = [];
                }
            }
        }
    }

	/**
	 * 解析所有原生页面自动刷新组件数据
	 *
	 * @param bool $throwOnException
	 * @param bool $usePublishedCache
	 *
	 * @return string
	 */
    public function parseAllNativeUiData($throwOnException = false, $usePublishedCache = false)
    {
	    if ($usePublishedCache) {
	    	$pageUiModelList = $this->getPagePublishedCacheData($this->pageModel->id, $this->lang);

		    if (!empty($pageUiModelList)) {
			    $uilist = json_decode($pageUiModelList['uilist'], true);
		    } else {
		    	$usePublishedCache = false;
		    }
	    }

	    if (false === $usePublishedCache) {
		    //获取页面单纯的html
		    $layout = NativePageLayoutModel::getComponentsSort($this->pageModel->id, $this->lang);
		    $uilist = NativePageUiComponentModel::getComponentsData($this->pageModel->id, $this->lang, $layout);
	    }
	    if (!empty($uilist) && is_array($uilist)) {
		    $explainComponent = new ExplainComponent();
		    foreach ($uilist as &$component) {
			    $component = array_map(function (&$item) {
				    if (is_string($item) && json_decode($item, true)) {
					    $item = json_decode($item, true);
				    }

				    return $item;
			    }, $component);
			    if (!empty($component['sku_data']) && is_array($component['sku_data'])) {
				    foreach ($component['sku_data'] as &$skuItem) {
					    $skuItem['goodsInfo'] = $explainComponent->getGoodsData(
						    $skuItem['sku'],
						    $this->lang,
						    (object)['siteCode' => $this->pageModel->site_code],
						    $this->pageModel->pipeline
					    );
				    }

				    $content = gzcompress(json_encode($uilist), 9);
				    //内容写入Redis中
				    $redisKey = app()->redisKey->getNativeAppJsonCacheKey($this->pageModel->site_code);
				    $field = "{$this->pageModel->id}::{$this->pageModel->pipeline}::{$this->lang}";
				    app()->redis->hset($redisKey, $field, $content);
			    }
		    }
	    }

		return $content ?? '';
    }

    /**
     * 获取所有自动刷新组件异步数据json格式
     *
     * @param bool $throwOnException 遇到异常时是否抛出
     * @param bool $usePublishedCache 是否使用发布缓存组件数据
     * @return string
     * @throws AutoRefreshException
     */
    public function getAllUiAsyncDataAsJson($throwOnException = false, $usePublishedCache = false)
    {
        $this->parseAllUiAsyncData($throwOnException, $usePublishedCache);
        if (empty($this->uiAsyncDataInfo)) {
            return null;
        }

        $jsonString = json_encode($this->uiAsyncDataInfo);
        if (false === $jsonString) {
            $message = sprintf('站点 %s 页面ID %d 异步数据编码错误!', $this->pageModel->site_code, $this->pageModel->id);
            throw new AutoRefreshException($message);
        }
        return $jsonString;
    }

    /**
     * 获取页面自动刷新组件异步数据JS定义
     *
     * @param bool $throwOnException 遇到异常时是否抛出
     * @param bool $usePublishedCache 是否使用发布缓存组件数据
     * @return string
     * @throws JsonResponseException
     */
    public function getAsyncDataJsVariable($throwOnException = false, $usePublishedCache = false)
    {
        try {
            $jsonString = $this->getAllUiAsyncDataAsJson($throwOnException, $usePublishedCache);
        } catch (\Throwable $e) {
            throw new JsonResponseException(1, $e->getMessage());
        }

        empty($jsonString) && $jsonString = '[]';
        return sprintf('var %s = %s;', static::ASYNC_DATA_JS_VARIABLE_NAME, $jsonString);
    }

    /**
     * 从发布缓存数据中获取自动刷新组件列表
     *
     * @return ZFPageUiModel[]
     */
    private function getPublishedCacheAutoRefreshUiList()
    {
        // 获取发布缓存页面数据
        $publishedCacheData = $this->getPagePublishedCacheData($this->pageModel->id, $this->lang);
        if (empty($publishedCacheData)) {
            return null;
        }

        // 获取组件数据
        $allUiList = isset($publishedCacheData['uilist']) ? json_decode($publishedCacheData['uilist'], true) : [];
        if (empty($allUiList)) {
            return null;
        }

        $pageUiModelList = [];
        foreach ($allUiList as $layoutUiList) {
            foreach ($layoutUiList as $positionUiList) {
                foreach ($positionUiList as $uiInfo) {
                    // 判断是否为自动刷新组件
                    if (isset($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT])
                        && !empty($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT]))
                    {
                        $pageUiModelList[] = $this->newSitePageUiModel($uiInfo);
                    }
                }
            }
        }

        return $pageUiModelList;
    }

    /**
     * 获取自动刷新组件列表
     *
     * @return ZFPageUiModel[]
     */
    private function getAutoRefreshUiList()
    {
	    $pageUiModelList = ZFPageUiModel::getPageAutoRefreshUiList($this->pageModel->id, $this->lang);

        return $pageUiModelList;
    }

    /**
     * new 一个新的页面UI组件对象
     *
     * @param array $data 组件数据
     * @return ZFPageUiModel
     */
    private function newSitePageUiModel($data)
    {
	    $pageUiModel = new ZFPageUiModel();

        $pageUiModel->setAttributes($data, false);
        return $pageUiModel;
    }

    /**
     * 获取页面发布的缓存数据
     *
     * @param int $pageId 页面ID
     * @param string $lang 语言简码
     * @return array
     */
    private function getPagePublishedCacheData($pageId, $lang)
    {
	    return \app\modules\common\zf\models\PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
    }
}
