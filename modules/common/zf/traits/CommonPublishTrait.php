<?php

namespace app\modules\common\zf\traits;

use app\base\PipelineUtils;
use app\base\SiteConstants;
use app\components\auto\AutoRefreshUi;
use app\components\auto\AutoRefreshPage;
use app\components\auto\AutoRefreshUtils;
use app\components\fallback\api\PageAsyncApiManager;
use app\modules\common\zf\components\CommonCrontabComponent;
use app\modules\common\zf\models\{
	ActivityModel, PageModel, PageLanguageModel, PagePublishCacheModel, PagePublishLogModel
};

use app\modules\component\zf\components\ExplainComponent;
use Diff;
use Diff_Renderer_Html_SideBySide;
use yii\helpers\Json;
use app\base\SitePlatform;
use app\base\SysConfigUtils;
use app\base\NativeConstants;
use app\modules\activity\zf\components\PageComponent;
use ego\curl\BaseResponseCurl;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/12
 * Time: 15:03
 */
trait CommonPublishTrait
{
	use CommonPageParseTrait;

	//创建类型|上线文件
	private $createTypeOnline = 'online';

	//创建类型|下线文件
	private $createTypeOffline = 'offline';

	//HTML文件后缀
	private $htmlFileType = 'html';

	// 组件自动刷新，异步数据json文件类型
	private $asyncJsonFileType = AutoRefreshUi::PUBLISH_ASYNC_DATA_JSON_TYPE;

	// APP原生页面API接口数据json文件类型
	private $nativeJsonFileType = 'native';

	//历史文件名前缀
	private $oldFileNamePre = 'diff_';

	//字段名
	private $fieldLocalPath = 'local_path';

	//页面内容缓存时间
	private $pageContentCacheTimeout = 60 * 60;

	//创建页面过程中的错误信息
	public $createHtmlErrors = [];

	//新版本缓存前缀【防止各站点间页面冲突】
	public  $redisPrefix;

	/**
	 * @var \app\modules\common\models\ActivityModel 活动信息
	 */
	private $activityInfo;

	//页面信息数组
	private $pageArr = [];

	// 是否有组件刷新异步数据
	private $hasAsyncDataJson = false;

	// Growing IO 组件埋点数据缓存
	private $growingIoCache = [];


	public function init ()
	{
		parent::init();

		$websiteCode = (defined('SITE_GROUP_CODE_FIXED') && !empty(SITE_GROUP_CODE_FIXED))
			? SITE_GROUP_CODE_FIXED
			: SITE_GROUP_CODE;
		$this->redisPrefix = $websiteCode . '::';
	}

	/**
	 * 批量生成上线page页的html文件（若已存在则覆盖）
	 *
	 * @param array $pageIds       页面ID数组
	 * @param int   $activityId    活动ID
	 * @param bool  $useCache      是否采用缓存来更新
	 * @param bool  $updateGoods   是否只更新线上产品
	 * @param bool  $updateUiGoods 是否更新组件里的产品
	 * @param int   $type          发布类型 默认空全部发布 1不发布html
	 * @param bool  $isHomeB       是否AB测试首页的B页
	 * @param array $langList      发布语言
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function batchCreateOnlinePageHtml ($pageIds, $activityId, $useCache = false, $updateGoods = false, $updateUiGoods = false, $type = '', $isHomeB = false, $langList = [])
	{
		return $this->batchCreateHtml([
			$pageIds,
			$activityId,
			$this->createTypeOnline,
			$useCache,
			$updateGoods,
			$updateUiGoods,
			$type,
			$isHomeB,
			$langList
		]);
	}

	/**
	 * 批量生成上线page原生页的html文件（若已存在则覆盖）
	 *
	 * @param        $pageIds
	 * @param        $activityId
	 * @param bool   $useCache
	 * @param bool   $updateGoods
	 * @param bool   $updateUiGoods
	 * @param string $type
	 * @param array  $langList
	 *
	 * @return array
	 */
	public function batchCreateOnlineNativePageHtml ($pageIds, $activityId, $useCache = false, $updateGoods = false, $updateUiGoods = false, $type = '', $langList = [])
	{
		return $this->batchCreateNativeHtml([
			$pageIds,
			$activityId,
			$this->createTypeOnline,
			$useCache,
			$updateGoods,
			$updateUiGoods,
			$type,
			$langList
		]);
	}

	/**
	 * 批量生成下线page页的html文件（若已存在则覆盖）
	 *
	 * @param array $pageIds       页面ID数组
	 * @param int   $activityId    活动ID
	 * @param bool  $useCache      是否采用缓存来更新
	 * @param bool  $updateGoods   是否只更新线上产品
	 * @param bool  $updateUiGoods 是否更新组件里的产品
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function batchCreateOfflinePageHtml ($pageIds, $activityId, $useCache = true, $updateGoods = false, $updateUiGoods = false, $langList = [])
	{
		//下线页面默认采用上线页面的缓存来更新，所以最后一个参数传true
		return $this->batchCreateHtml([$pageIds, $activityId, $this->createTypeOffline, $useCache, $updateGoods, $updateUiGoods, '', false, $langList]);
	}

	/**
	 * 批量生成下线原生专题page页的html文件（若已存在则覆盖）
	 *
	 * @param       $pageIds
	 * @param       $activityId
	 * @param bool  $useCache
	 * @param bool  $updateGoods
	 * @param bool  $updateUiGoods
	 * @param array $langList
	 *
	 * @return array
	 */
	public function batchCreateOfflineNativePageHtml ($pageIds, $activityId, $useCache = true, $updateGoods = false, $updateUiGoods = false, $langList = [])
	{
		//下线页面默认采用上线页面的缓存来更新，所以最后一个参数传true
		return $this->batchCreateNativeHtml([$pageIds, $activityId, $this->createTypeOffline, $useCache, $updateGoods, $updateUiGoods, '', false, $langList]);
	}

	/**
	 * 批量生成page页的html文件（若已存在则覆盖）
	 *
	 * @param array $params = [
	 *                      array   $pageIds    页面ID数组
	 *                      int     $activityId 活动ID
	 *                      string  $createType 文件发布类型
	 *                      bool    $useCache   是否采用缓存来更新
	 *                      bool    $updateGoods 是否只更新线上产品
	 *                      ]
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	private function batchCreateHtml ($params)
	{
		list($pageIds, $activityId, $createType, $useCache, $updateGoods, $updateUiGoods, $type, $isHomeB, $langList) = $params;

		$errorCount = 0;
		if (!empty($pageIds)) {
			$envParams = $this->beforeBatchCreateHtml($pageIds, $activityId, $errorCount);
			if ($errorCount) {
				//前置条件失败，则直接返回
				return [!$errorCount, $this->createHtmlErrors];
			}

			$allLangList = $envParams[3];
			/** @var array $pageIds */
			foreach ($pageIds as $pageId) {
				$pipelineLangList = $allLangList[$this->pageArr[$pageId]['pipeline']];
				if (!empty($langList)) {
					foreach ($pipelineLangList as $key => $item) {
						if (!in_array($item['key'], $langList)) {
							unset($pipelineLangList[$key]);
						}
					}
				}

				if ($createType === $this->createTypeOnline
					&& !$this->createOnlinePageHtml($pipelineLangList, $pageId, $useCache, $updateGoods, $updateUiGoods, $type, $isHomeB)
				) {
					$errorCount++;
				}
				if ($createType === $this->createTypeOffline
					&& !$this->createOfflinePageHtml($pipelineLangList, $pageId, $useCache, $updateGoods, $updateUiGoods)
				) {
					$errorCount++;
				}
			}

			$this->afterBatchCreateHtml($envParams);
		}

		return [!$errorCount, $this->createHtmlErrors];
	}

	/**
	 * 批量生成page原生页的html文件（若已存在则覆盖）
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function batchCreateNativeHtml ($params)
	{
		list($pageIds, $activityId, $createType, $useCache, $updateGoods, $updateUiGoods, $type, $langList) = $params;

		$errorCount = 0;
		if (!empty($pageIds)) {
			$envParams = $this->beforeBatchCreateHtml($pageIds, $activityId, $errorCount);
			if ($errorCount) {
				//前置条件失败，则直接返回
				return [!$errorCount, $this->createHtmlErrors];
			}

			$allLangList = $envParams[3];
			/** @var array $pageIds */
			foreach ($pageIds as $pageId) {
				$pipelineLangList = $allLangList[$this->pageArr[$pageId]['pipeline']];
				if (!empty($langList)) {
					foreach ($pipelineLangList as $key => $item) {
						if (!in_array($item['key'], $langList)) {
							unset($pipelineLangList[$key]);
						}
					}
				}

				if ($createType === $this->createTypeOnline
					&& !$this->createOnlineNativePageHtml($pipelineLangList, $pageId, $useCache, $updateGoods, $updateUiGoods, $type)
				) {
					$errorCount++;
				}
				if ($createType === $this->createTypeOffline
					&& !$this->createOfflineNativePageHtml($pipelineLangList, $pageId, $useCache, $updateGoods, $updateUiGoods)
				) {
					$errorCount++;
				}
			}

			$this->afterBatchCreateHtml($envParams);
		}

		return [!$errorCount, $this->createHtmlErrors];
	}

	/**
	 * 获取发布期间全局的信息（activity、pages）
	 *
	 * @param array $params
	 * @param int   $errorCount
	 * @param array $langList
	 *
	 * @return array
	 */
	protected function setPublishGlobalInfo (array $params, int &$errorCount, array &$langList)
	{
		/** @var array $pageIds */
		list($pageIds, $activityId) = $params;

		//获取页面信息，以便后面使用
		$pageList = PageModel::find()->where(['id' => $pageIds])->asArray()->all();
		$pageLanguageList = PageLanguageModel::find()->where(['page_id' => $pageIds])->asArray()->all();
		if (!empty($pageList) && !empty($pageLanguageList)) {
			foreach ($pageList as $pageItem) {
				$this->pageArr[$pageItem['id']] = array_merge($this->pageArr[$pageItem['id']] ?? [], $pageItem);
				foreach ($pageLanguageList as $langItem) {
					if ((int)$pageItem['id'] === (int)$langItem['page_id']) {
						$this->pageArr[$pageItem['id']]['langList'][$langItem['lang']]['url_name'] = $langItem['url_name'];
					}
				}
			}
		}

		if (\count($pageIds) !== \count($this->pageArr)) {
			//页面未获取全则设置错误信息
			$this->setPageNotFoundError($pageIds, $errorCount);
		}
		//if (\count(array_filter(array_unique(array_column($this->pageArr, 'site_code')))) > 1) {
		//!!!为了高效的利用公共信息，batch开头的方法，一次只允许同站点（即site_code相同）页面一起调用!!!
		//$this->setSiteCodeCrossSiteError($pageIds, $errorCount);
		//}

		//记录活动信息，以便后面使用
		if (!empty($activityId)) {
			//活动页信息
			$this->activityInfo = ActivityModel::findOne($activityId);
			if (!$this->activityInfo || !isset(app()->params['sites'][$this->activityInfo->site_code])) {
				//页面未获取全则设置错误信息
				$this->setSiteCodeNotFoundError($pageIds, $errorCount);
			}

			$langList = ActivityModel::getLangListByLangString($this->activityInfo->lang);
		} else {
			//首页信息
			$firstPage = current($this->pageArr);

			//补全活动信息
			$this->activityInfo = new ActivityModel();
			$this->activityInfo->id = 0;
			$this->activityInfo->site_code = $firstPage['site_code'];
			$this->activityInfo->mold = 0;

			//获取支持语言
			$langList = [];
			$_langList = app()->params['sites'][$firstPage['site_code']]['home_secondary_domain'] ?? [];
			if (!empty($_langList)) {
				$pipelineLangArr = [];
				foreach ($_langList as $kk => $vv) {
					$pipelineLangArr[$kk] = array_keys($vv);
				}
				$this->activityInfo->lang = json_encode($pipelineLangArr);
				$langList = ActivityModel::getLangListByLangString($this->activityInfo->lang);
			}
		}

		// TODO 这里要处理去掉配置后遗留下来的语言项

		return $langList;
	}

	/**
	 * 创建文件前保存现场
	 *
	 * @param array $pageIds    页面Id数组
	 * @param int   $activityId 活动ID
	 * @param int   $errorCount 错误数
	 *
	 * @return array
	 */
	protected function beforeBatchCreateHtml ($pageIds, $activityId, &$errorCount)
	{
		//这里要记录format的值，方便后面还原回来，因为runAction中有render，会改变format的值，若不还原会报错的
		//language和basePath设置也是的，因为生成多语html时会修改这个值
		$format = app()->response->format;
		$oldLang = app()->language;
		$oldTrans = app()->i18n->translations['yii'];
		if (\is_array(app()->i18n->translations['yii'])) {
			app()->i18n->translations['yii']['basePath'] = '@runtime/languages/' . $activityId;
		} else {
			app()->i18n->translations['yii']->basePath = '@runtime/languages/' . $activityId;
		}

		$this->createHtmlErrors = $langList = [];

		//设置一些发布期间的全局信息
		$this->setPublishGlobalInfo([$pageIds, $activityId], $errorCount, $langList);

		return [$format, $oldLang, $oldTrans, $langList];
	}

	/**
	 * 创建文件完毕后恢复现场
	 *
	 * @param $params
	 */
	protected function afterBatchCreateHtml ($params)
	{
		list($format, $oldLang, $oldTrans) = $params;

		app()->response->format = $format;
		app()->language = $oldLang;
		app()->i18n->translations['yii'] = $oldTrans;

		//置空页面信息
		$this->activityInfo = null;
		//置空页面信息
		$this->pageArr = [];
	}

	/**
	 * 生成上线page页的html文件（若已存在则覆盖）
	 *
	 * @param array $langList    活动语言列表
	 * @param int   $pageId      页面ID
	 * @param bool  $useCache    是否采用缓存来更新
	 * @param bool  $updateGoods 是否只更新线上组件产品
	 * @param int   $type        发布类型 默认空正常发布 1不发布html
	 * @param bool  $isHomeB     是否AB测试首页的B页
	 *
	 * @return bool
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	private function createOnlinePageHtml ($langList, $pageId, $useCache, $updateGoods = false, $updateUiGoods = false, $type = '', $isHomeB = false)
	{
		if (!empty($langList)) {
			$version = app()->helper->microtime();
			$createResult = [];
			foreach ($langList as $lang) {
				//解析html文件(商品数据,自定义JS,CSS,头尾部内容)
				$result = $this->parsePageInArray(
					[
						$pageId,
						$lang['key'],
						$this->pageArr[$pageId]['site_code'],
						$this->pageArr[$pageId]['pipeline'],
						$version,
						true,
						$useCache,
						$updateGoods,
						$updateUiGoods
					]
				);

				$actionType = PagePublishLogModel::ACTION_TYPE_ONLINE;
				//创建和打包生成页面各种需要静态资源,并且把页面数据加入到redis队列
				$createRes = $this->createHtml([$pageId, $lang['key'], $result, $actionType, $version, $useCache, $isHomeB]);
				if (false === $createRes) {
					return false;
				}
				$createResult[] = $createRes;
			}

			return !empty($createResult) && $this->saveCreateResult($createResult, $type);
		}

		return true;
	}

	/**
	 * 生成上线page原生页的html文件（若已存在则覆盖）
	 *
	 * @param        $langList
	 * @param        $pageId
	 * @param        $useCache
	 * @param bool   $updateGoods
	 * @param bool   $updateUiGoods
	 * @param string $type
	 *
	 * @return bool
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\db\Exception
	 */
	private function createOnlineNativePageHtml ($langList, $pageId, $useCache, $updateGoods = false, $updateUiGoods = false, $type = '')
	{
		if (!empty($langList)) {
			$version = app()->helper->microtime();
			$createResult = [];
			foreach ($langList as $lang) {
				$result = $this->parseNativePageInArray(
					[
						$pageId,
						$lang['key'],
						$this->pageArr[$pageId]['site_code'],
						$this->pageArr[$pageId]['pipeline'],
						$version,
						true,
						$useCache,
						$updateGoods,
						$updateUiGoods
					]
				);
				$actionType = PagePublishLogModel::ACTION_TYPE_ONLINE;
				$createRes = $this->createNativeHtml(
					[
						$pageId,
						$this->pageArr[$pageId]['site_code'],
						$this->pageArr[$pageId]['pipeline'],
						$lang['key'],
						$version,
						$actionType,
						$result
					]
				);
				if (false === $createRes) {
					return false;
				}
				$createResult[] = $createRes;
			}

			return !empty($createResult) && $this->saveCreateResult($createResult, $type);
		}

		return true;
	}

	/**
	 * 生成下线page页的html文件（若已存在则覆盖）
	 *
	 * @param array $langList 活动语言列表
	 * @param int   $pageId   页面ID
	 * @param bool  $useCache 是否采用缓存来更新
	 *
	 * @return bool
	 * @throws \Throwable
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\db\Exception
	 */
	private function createOfflinePageHtml ($langList, $pageId, $useCache, $updateGoods, $updateUiGoods)
	{
		if (!empty($langList)) {
			$version = app()->helper->microtime();
			//强制使用缓存更新
			$useCache = true;
			$siteCode = $this->activityInfo->site_code;
			$createResult = [];
			foreach ($langList as $lang) {
				$appSiteSuffix = 'app';
				$result = [];
				if (0 === substr_compare($siteCode, $appSiteSuffix, -strlen($appSiteSuffix))) {
					//app不能页面跳转，只能使用下线模板
					$result['html'] = $this->packageContent($this->getOfflineHtml($siteCode, $pageId, $lang['key'], true));
				} else {
					//使用原上线页面内容
					$result = $this->parsePageInArray(
						[
							$pageId,
							$lang['key'],
							$this->pageArr[$pageId]['site_code'],
							$this->pageArr[$pageId]['pipeline'],
							$version,
							true,
							$useCache,
							$updateGoods,
							$updateUiGoods
						]
					);
					//将下线提示框的内容追加到原内容后面
					$result['html'] .= $this->getOfflineHtml($siteCode, $pageId, $lang['key'], false);
				}

				$actionType = PagePublishLogModel::ACTION_TYPE_OFFLINE;
				$createRes = $this->createHtml([$pageId, $lang['key'], $result, $actionType, $version, $useCache, false]);
				if (false === $createRes) {
					return false;
				}
				$createResult[] = $createRes;
			}
			!empty($createResult) && $this->saveCreateResult($createResult);
		}

		return true;
	}

	private function createOfflineNativePageHtml ($langList, $pageId, $useCache, $updateGoods, $updateUiGoods)
	{
		if (!empty($langList)) {
			$version = app()->helper->microtime();
			//强制使用缓存更新
			$useCache = true;
			$siteCode = $this->activityInfo->site_code;
			$createResult = [];
			foreach ($langList as $lang) {
				$appSiteSuffix = 'app';
				$result = [];
				if (0 === substr_compare($siteCode, $appSiteSuffix, -strlen($appSiteSuffix))) {
					//app不能页面跳转，只能使用下线模板
					$result['html'] = $this->packageContent($this->getOfflineHtml($siteCode, $pageId, $lang['key'], true));
				} else {
					//使用原上线页面内容
					$result = $this->parseNativePageInArray(
						[
							$pageId,
							$lang['key'],
							$this->pageArr[$pageId]['site_code'],
							$this->pageArr[$pageId]['pipeline'],
							$version,
							true,
							$useCache,
							$updateGoods,
							$updateUiGoods
						]
					);
					//将下线提示框的内容追加到原内容后面
					$result['html'] .= $this->getOfflineHtml($siteCode, $pageId, $lang['key'], false);
				}

				$actionType = PagePublishLogModel::ACTION_TYPE_OFFLINE;
				$createRes = $this->createHtml([$pageId, $lang['key'], $result, $actionType, $version, $useCache, false]);
				if (false === $createRes) {
					return false;
				}
				$createResult[] = $createRes;
			}
			!empty($createResult) && $this->saveCreateResult($createResult);
		}

		return true;
	}

	/**
	 * 保存页面生成后的结果数据
	 *
	 * @param array $params
	 * @param int   $type 发布类型 默认空  1为推广落地页不发布html
	 *
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	private function saveCreateResult ($params, $type = '')
	{
		foreach ($params as list($pageId, $lang, $logData, $redisData, $createRes)) {
			$siteGroup = explode('-', $this->pageArr[$pageId]['site_code'])[0];
			$pipeline = $this->pageArr[$pageId]['pipeline'];
			$pipelineDefaultLang = app()->params['site'][$siteGroup]['pipeline_default_lang'][$pipeline];
			//记录page的访问URL和本地文件路径
			if ($pageLangModel = PageLanguageModel::findOne(['page_id' => $pageId, 'lang' => $lang])) {

        // 组件API兜底是否启用
        $isUiApiFallbackEnabled = (int)SysConfigUtils::get('sys.ui.sync_api_fallback_enabled', 0);
        if ($isUiApiFallbackEnabled === 1) {
          // 组件异步接口兜底数据生成
          $pageAsyncApiManager = new PageAsyncApiManager($this->pageArr[$pageId], $lang);
          $pageAsyncApiManager->checkFallbackAsyncApi();
          unset($pageAsyncApiManager);
        }

				$publishPathPre = $this->getHtmlFilePathPreOnS3($this->activityInfo->site_code, $lang, $this->activityInfo->mold, $this->pageArr[$pageId]['pipeline']);
				if (empty($type)) {
					// ZF站点活动页面，如果是博客页面，替换点 blog 目录
					$_htmlFile = str_replace('/blog/', '/', $createRes['html_file']);
					$pageUrl = explode($publishPathPre, $_htmlFile)[1];
					/* 对于非默认语言的访问链接会加上lang=xxx的参数 */
					$pageLangModel->page_url = $pageUrl . ($pipelineDefaultLang === $lang ? '' : '?lang=' . $lang);
				} else {
					$pageLangModel->status = 0;
				}
				$pageLangModel->local_files = json_encode([
					'css'  => $createRes['css_file'],
					'js'   => $createRes['js_file'],
					'html' => $createRes['html_file']
				]);
				$pageLangModel->s3_files = json_encode([
					'css'  => $createRes['css_s3'],
					'js'   => $createRes['js_s3'],
					'html' => $createRes['html_s3']
				]);
				$pageLangModel->save(true);

				if (!empty($logData)) {
					$this->savePublishLog($logData);
				}

				if (!empty($redisData)) {
					if (!empty($type) && (int)$type === 1) {
						/** @var array $redisData */
						foreach ($redisData as $key => $row) {
							$row = json_decode($row, true);
							$row['type'] = 1;
							$redisData[$key] = json_encode($row);

						}
					}

					// 自动切换发布模式,swoole连接不正常就走队列模式
					if (defined('PUSHSYNC') && false === PUSHSYNC) {
						if (!$this->pushTaskToRedis($redisData)) {
							$this->createHtmlErrors[] = $this->createErrorItem($pageId, '推送任务进redis队列出错');

							return false;
						}
					} else {
						$arrayCache = app()->arrayCache->get('redis_data_multi_array');
						$arrayCache = json_decode($arrayCache, true);
						empty($arrayCache) && $arrayCache = [];
						array_push($arrayCache, $redisData);
						app()->arrayCache->set('redis_data_multi_array', json_encode($arrayCache));
					}
				}
			}
		}

		return true;
	}

	/**
	 * 推送任务到redis队列中
	 *
	 * @param array $data 存储到Redis中的值
	 *
	 * @return bool
	 */
	private function pushTaskToRedis ($data)
	{
		if (!empty($data) && \is_array($data)) {
			$redisLength = app()->redis->rpush($this->redisPrefix . app()->redisKey->getPushTaskRedisKey(), ...$data);
			\Yii::info('发布任务push进redis：' . \count($data) . '-' . json_encode($data), __METHOD__);

			return \is_int($redisLength);
		}

		return true;
	}

	/**
	 * 生成html文件
	 *
	 * @param array $params = [
	 *                      int     $pageId     页面ID
	 *                      string  $lang       语言代码简称
	 *                      array   $result     页面不同部位html数组
	 *                      int     $actionType 操作类型
	 *                      string  $version    版本号
	 *                      bool    $useCache   是否采用缓存来更新
	 *                      ]
	 *
	 * @return bool|array
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Exception
	 */
	private function createHtml ($params)
	{
		$logData = $redisData = [];
		list($pageId, $lang) = $params;
		$createRes = $this->createStatics($params, $redisData, $logData);
		if (false === $createRes) {
			return false;
		}

		return [$pageId, $lang, $logData, $redisData, $createRes];
	}

	/**
	 * 生成原生html文件
	 *
	 * @param array $params
	 *
	 * @return array|bool
	 * @throws \Exception
	 */
	private function createNativeHtml (array $params)
	{
		list($pageId, $siteCode, $pipeline, $lang, $version, $actionType, $result) = $params;

		$createRes = [
			'css_file'  => '',
			'css_s3'    => '',
			'js_file'   => '',
			'js_s3'     => '',
			'html_file' => '',
			'html_s3'   => '',
		];

        // 原生 APP&WAP 页面接口json数据文件生成
        list($jsonFile, $jsonS3) = $this->createNativeFile(
            [$pageId, $lang, $this->nativeJsonFileType, $result['uilist'], $actionType, $version, $siteCode, '', '', 0],
            $redisData,
            $logData
        );
        if ('' === $jsonFile) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, 'app原生页面API接口文件生成失败');
            return false;
        }

		if (SitePlatform::isAppPlatform($siteCode)) {
			$createRes['html_file'] = $jsonFile;
			$createRes['html_s3'] = $jsonS3;
		} else {

            // SEO 优化
            $pageModel = PageModel::getById($pageId);
            $activityPageType = PageModel::getActivityPageType($pageModel);
            $seoLinks = ($activityPageType == SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL)
                ? $this->getSEOPageUrlHtml($pageModel, $lang)
                : '';
            $componentStatic = ['css' => $seoLinks];

			//获取头尾html(将生成的css和js文件插入头尾位置)
			$headerAndFooter = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic, $pipeline);
			if (empty($headerAndFooter)) {
				$this->createHtmlErrors[] = $this->createErrorItem($pageId, '平台头尾未找到', [
					'lang'     => $lang,
					'siteCode' => $siteCode
				]);

				return false;
			}
			$html = $this->replaceSiteMainBody($headerAndFooter, $result['html']);
			list($htmlFile, $htmlS3) = $this->createNativeFile([
				$pageId,
				$lang,
				'html',
				$html,
				$actionType,
				$version,
				$siteCode,
				'',
				'',
				0
			], $redisData, $logData);
			if ('' === $htmlFile) {
				$this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面html文件生成失败');

				return false;
			}

			$createRes['html_file'] = $htmlFile;
			$createRes['html_s3'] = $htmlS3;
            $createRes[$this->nativeJsonFileType .'_file'] = $jsonFile;
            $createRes[$this->nativeJsonFileType .'_s3'] = $jsonS3;
		}

		return [$pageId, $lang, $logData, $redisData, $createRes];
	}

	/**
	 * 生成static静态文件
	 *
	 * @param array $params    = [
	 *                         int     $pageId     页面ID
	 *                         string  $lang       语言代码简称
	 *                         bool    $useCache   是否采用缓存来更新
	 *                         array   $result     页面不同部位html数组
	 *                         int     $actionType 操作类型
	 *                         string  $version    版本号
	 *                         string  $siteCode   站点siteCode
	 *                         ]
	 * @param array $redisData 存储到Redis中的数据数组
	 * @param array $logData   日志数据
	 *
	 * @return bool|array
	 * @throws \Exception
	 */
	private function createStatics ($params, &$redisData, &$logData)
	{
		static $styleData = [];

		$siteCode = $this->activityInfo->site_code;
		list($pageId, $lang, $result, $actionType, $version, , $isHomeB) = $params; // 第6个参数用不到，直接忽略即可

		//匹配出组件中的自定义样式，合并到head
		$this->matchComponentsCss($result);

		// 平台公用样式
		$platformCode = SitePlatform::getPlatformCodeBySiteCode($siteCode);
		$platformCssSuffix = (SitePlatform::PLATFORM_CODE_PC != $platformCode) ? 'm' : 'pc';
		$platformCss = app()->basePath . '/htdocs/frontend/geshop/stylesheets/geshop-components-default-' . $platformCssSuffix . '.css';
		if (!empty($styleData[md5($platformCss)])) {
			$result['css'] = $styleData[md5($platformCss)] . ($result['css'] ?? '');
		} elseif (file_exists($platformCss)) {
			$styleData[md5($platformCss)] = file_get_contents($platformCss);
			$result['css'] = $styleData[md5($platformCss)] . ($result['css'] ?? '');
		}

		// 活动公用样式
		$staticFileSuffix = (int)$this->activityInfo->id ? 'activity' : 'home';
		//生成css文件【20180929 将geshop-grid_home.css内容读取出来放到合并CSS文件前】
		// 2020-04-30 判断是否D网，D网用不同的文件
		if (explode('-', $siteCode)[0] == 'dl') {
			$gridCss = app()->basePath . '/htdocs/frontend/sites/stylesheets/geshop-grid-responsive.css';
		} else {
			$gridCss = app()->basePath . '/htdocs/resources/sitesPublic/default/css/geshop-grid_' . $staticFileSuffix . '.css';
		}

		if (!empty($styleData[md5($gridCss)])) {
			$result['css'] = $styleData[md5($gridCss)] . ($result['css'] ?? '');
		} elseif (file_exists($gridCss)) {
			$styleData[md5($gridCss)] = file_get_contents($gridCss);
			$result['css'] = $styleData[md5($gridCss)] . ($result['css'] ?? '');
		}

		list($cssFile, $cssS3) = !empty($result['css'])
			? $this->createFile(
				[$pageId, $lang, 'css', $result['css'], $actionType, $version, $siteCode, '', '', $isHomeB],
				$redisData,
				$logData
			) : [false, ''];
		if ('' === $cssFile) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面css文件生成失败');

			return false;
		}

		$commonJs = app()->basePath . '/htdocs/resources/sitesPublic/default/js/gs_common_' . $staticFileSuffix . '.min.js';
		if (!empty($styleData[md5($commonJs)])) {
			$result['js'] = file_get_contents($commonJs) . "\n\r" . ($result['js'] ?? '');
		} elseif (file_exists($commonJs)) {
			$styleData[md5($commonJs)] = file_get_contents($commonJs);
			$result['js'] = file_get_contents($commonJs) . "\n\r" . ($result['js'] ?? '');
		}

		$labJs = app()->basePath . '/htdocs/resources/javascripts/library/LAB.js';
		if (!empty($styleData[md5($labJs)])) {
			$result['js'] = $styleData[md5($labJs)] . ($result['js'] ?? '');
		} elseif (file_exists($labJs)) {
			$styleData[md5($labJs)] = file_get_contents($labJs);
			$result['js'] = $styleData[md5($labJs)] . ($result['js'] ?? '');
		}

		list($jsFile, $jsS3) = !empty($result['js'])
			? $this->createFile(
				[$pageId, $lang, 'js', $result['js'], $actionType, $version, $siteCode, '', '', $isHomeB],
				$redisData,
				$logData
			) : [false, ''];

		if ('' === $jsFile) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面js文件生成失败');

			return false;
		}

		// 自动刷新组件，异步数据json打包
		$jsonFiles = [false, ''];
		$pageModel = PageModel::findOne($pageId);
		try {
			$this->hasAsyncDataJson = false;
			$autoRefreshPage = new AutoRefreshPage($pageModel, $lang);
			$asyncJsonContent = $autoRefreshPage->getAllUiAsyncDataAsJson(true);
			if (!empty($asyncJsonContent)) {
				$jsonFiles = $this->createFile(
					[$pageId, $lang, $this->asyncJsonFileType, $asyncJsonContent, $actionType, $version, $siteCode, '', '', $isHomeB],
					$redisData,
					$logData
				);
				$this->hasAsyncDataJson = true;
			}
			unset($autoRefreshPage);
		} catch (\Exception $e) {
			\Yii::error('[发布页面] 解析自动刷新组件异步数据格式错误: ' . $e->getMessage(), __METHOD__);
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面异步数据json文件生成失败');

			return false;
		}

		// Growing IO 组件埋点数据生成
        if (isset($result['layout']) && isset($result['uilist']) && \app\base\GrowingIO::isSupport($siteCode)) {
            $_cacheKey = $pageId .'-'. $lang;
	        $this->growingIoCache[$_cacheKey] = \app\base\GrowingIO::doGenerateDefaultUiList(
	            $siteCode, $pageId, $lang, Json::decode($result['layout'], true),  Json::decode($result['uilist'], true)
            );
        }

		//先获取完整的html内容
		$result['html'] = $this->buildHtmlWithJsAndCss([$pageId, $lang, $siteCode, $result['html'], $cssS3, $jsS3, true, $this->activityInfo->mold, $isHomeB]);
		if (false === $result['html']) {
			return false;
		}

		list($htmlFile, $htmlS3) = $this->createFile([
			$pageId,
			$lang,
			'html',
			$result['html'],
			$actionType,
			$version,
			$siteCode,
			$cssS3,
			$jsS3,
			$isHomeB
		], $redisData, $logData);
		if ('' === $htmlFile) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面html文件生成失败');

			return false;
		}

		$staticsFiles = [
			'css_file'  => $cssFile,
			'css_s3'    => $cssS3,
			'js_file'   => $jsFile,
			'js_s3'     => $jsS3,
			'html_file' => $htmlFile,
			'html_s3'   => $htmlS3,
		];

		if (!empty($jsonFiles[0])) {
			$staticsFiles[$this->asyncJsonFileType . '_file'] = $jsonFiles[0];
			$staticsFiles[$this->asyncJsonFileType . '_s3'] = $jsonFiles[1];
		}

		return $staticsFiles;
	}

	/**
	 * 匹配出组件中的自定义样式，合并到head
	 *
	 * @param $data
	 */
	public function matchComponentsCss (&$data)
	{
		$pattern = /** @lang text */
			'/<!-- embed stylesheet begin -->\s*'
			. '<style type="text\/css">(.*?)<\/style>\s*<!-- embed stylesheet end -->/is';
		preg_match_all($pattern, $data['html'], $matches);
		$data['css'] = ($data['css'] ?? '') . (!empty($matches[1]) ? implode('', array_unique($matches[1])) : '');
		$data['html'] = preg_replace($pattern, '', $data['html']);
	}

	/**
	 * 创建缓存文件，并同步到S3上
	 *
	 * @param array $params    = [
	 *                         int $pageId 页面ID
	 *                         string $lang 语言代码简称
	 *                         string $fileType 文件后缀名
	 *                         string $content 文件内容
	 *                         int $actionType 操作类型
	 *                         string $siteCode 站点siteCode
	 *                         ]
	 * @param array $redisData 存储到Redis中的数据数组
	 * @param array $logData   日志数据
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function createFile ($params, &$redisData, &$logData)
	{
		list($pageId, $lang, $fileType, $content, $actionType, $version, $siteCode, $cssS3, $jsS3, $isHomeB) = $params;
		$pipeline = $this->pageArr[$pageId]['pipeline'];
		$localPath = $this->getLocalPath([$pageId, $lang, $fileType, $siteCode, $this->activityInfo->mold, $isHomeB, $pipeline]);

		// ZF站点活动页面，如果是博客页面，改变推送地址。
		$isBlog = $this->pageArr[$pageId]['is_blog'] ?? 0;
		if ($isBlog && in_array($fileType, [$this->asyncJsonFileType, $this->htmlFileType], true)) {
			if (strpos($localPath, '/app/') !== false) {
				$localPath = str_replace('/app/', '/blog/app/', $localPath);
			} else {
				if (app()->env->isPreRelease()) {
					$localPath = str_replace('/release/', '/blog/release/', $localPath);
				} else {
					$fileName = basename($localPath);
					$localPath = str_replace($fileName, 'blog/' . $fileName, $localPath);
				}
			}

			$blogRoot = dirname($localPath);
			!is_dir($blogRoot) && mkdir($blogRoot, 0777, true);
		}

		// 获取文件相对路径,如: /publish/www.zaful.com/en/test-zf-blog-42424.html
		$fileRelativePath = $this->getRelativePathByLocalPath($localPath);

		if (false === defined('PUSHSYNC') || (true === defined('PUSHSYNC') && false === PUSHSYNC)) {
			//内容写入Redis中
			$redisKey = $this->redisPrefix . app()->redisKey->getPublishContentKey([$pageId, $lang, $fileType, $version]);
			app()->redis->setex($redisKey, $this->pageContentCacheTimeout, $content);
		}

		if (false === file_put_contents($localPath, $content)) {
			\Yii::error('文件内容写入失败' . $localPath, __METHOD__);

			return [false, '文件内容写入失败：' . $localPath];
		}

		//记录日志
		$fileInfo = pathinfo($localPath);
		$user = app()->user->username ?? '';
		$time = time();
		$log = [
			'version'             => $version,
			'log_type'            => '',
			'page_id'             => (int)$pageId,
			'lang'                => $lang,
			'site_code'           => $siteCode,
			'action_type'         => $actionType,
			'file_name'           => $fileInfo['basename'],
			'file_type'           => $fileType,
			'file_size'           => 0,
			'file_hash'           => '',
			$this->fieldLocalPath => $fileRelativePath,
			's3_url'              => '',
			'diff'                => '',
			'ip'                  => app()->ip->get(true),
			'create_user'         => $user,
			'create_time'         => $time,
			'update_user'         => $user,
			'update_time'         => $time
		];

		$s3Key = $this->getS3KeyByLocalPath($localPath, $fileType);
		$item = [
			'route'               => app()->controller->getRoute(),
			$this->fieldLocalPath => $localPath,
			's3_key'              => $s3Key,
			'activity_id'         => (int)$this->pageArr[$pageId]['activity_id'],
			'page_id'             => $pageId,
			'lang'                => $lang,
			'version'             => $version,
			'file_type'           => $fileType,
			'site_code'           => $siteCode,
			'redis_key'           => $redisKey ?? '',
			'css_s3'              => $cssS3,
			'js_s3'               => $jsS3,
			'mold'                => $this->activityInfo->mold,
			'is_home_b'           => $isHomeB,
			'pipeline'            => $pipeline
		];
		$log['log_type'] = PagePublishLogModel::LOG_TYPE_PUBLISH;

		//!!!!因为发布和上传是分开的操作，所以这里S3文件URL不管传不传都需要计算出来
		if ($isBlog && in_array($fileType, [$this->asyncJsonFileType, $this->htmlFileType], true)) { // ZF站点博客页面
			$_blogS3Key = str_replace('/blog/', '/', $s3Key);
			$s3Url = $this->getS3UrlByS3Key([$_blogS3Key, $lang, $fileType, $siteCode, $this->activityInfo->mold, $pipeline]);
			$s3Url = PageComponent::getBlogPageUrl($s3Url);
		} else {
			$s3Url = $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $this->activityInfo->mold, $pipeline]);
		}

		$item['log_data'] = $log;
		$item['page_url'] = $s3Url;
		$redisData[] = json_encode($item);

		$log['log_type'] = PagePublishLogModel::LOG_TYPE_CREATE;
		$logData[] = $log;

		return [$fileRelativePath, $s3Url];
	}

	/**
	 * 创建缓存文件，并同步到S3上
	 *
	 * @param array $params    = [
	 *                         int $pageId 页面ID
	 *                         string $lang 语言代码简称
	 *                         string $fileType 文件后缀名
	 *                         string $content 文件内容
	 *                         int $actionType 操作类型
	 *                         string $siteCode 站点siteCode
	 *                         ]
	 * @param array $redisData 存储到Redis中的数据数组
	 * @param array $logData   日志数据
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function createNativeFile ($params, &$redisData, &$logData)
	{
		list($pageId, $lang, $fileType, $content, $actionType, $version, $siteCode, $cssS3, $jsS3, $isHomeB) = $params;
		$pipeline = $this->pageArr[$pageId]['pipeline'];
		$localPath = $this->getLocalPath([$pageId, $lang, $fileType, $siteCode, $this->activityInfo->mold, $isHomeB, $pipeline]);

        $isNativeApp = SitePlatform::isAppPlatform($siteCode);
		// 获取文件相对路径,如: /publish/www.zaful.com/en/test-zf-blog-42424.html
		$fileRelativePath = $this->getRelativePathByLocalPath($localPath);

		if ($fileType == $this->nativeJsonFileType) {
			$componentsData = is_array($content) ? $content : json_decode($content, true);
			//内容写入Redis中
            if ($isNativeApp) {
                $nativeRedisKey = app()->redisKey->getNativeAppJsonCacheKey($siteCode);
            } else {
                $nativeRedisKey = app()->redisKey->getNativeWapJsonCacheKey($siteCode);
            }

			$field = "{$pageId}::{$pipeline}::{$lang}";
			if (!empty($componentsData) && is_array($componentsData)) {
                foreach ($componentsData as &$component) {
                    $component = array_map(function (&$item) {
                        if (is_string($item) && json_decode($item, true)) {
                            $item = json_decode($item, true);
                        }
                        return $item;
                    }, $component);
                }
				$content = json_encode($componentsData);
				app()->apiRedis->hset($nativeRedisKey, $field, gzcompress($content, 9));
			} else {
				$content = '';
				if (app()->apiRedis->exists($nativeRedisKey, $field)) {
					app()->apiRedis->hdel($nativeRedisKey, $field);
				}
			}
		}
		if (false === defined('PUSHSYNC') || (true === defined('PUSHSYNC') && false === PUSHSYNC)) {
			//内容写入Redis中
			$redisKey = $this->redisPrefix . app()->redisKey->getPublishContentKey([$pageId, $lang, $fileType, $version]);
			app()->redis->setex($redisKey, $this->pageContentCacheTimeout, $content);
		}


		if (false === file_put_contents($localPath, $content)) {
			\Yii::error('文件内容写入失败' . $localPath, __METHOD__);

			return [false, '文件内容写入失败：' . $localPath];
		}

		//记录日志
		$fileInfo = pathinfo($localPath);
		$user = app()->user->username ?? '';
		$time = time();
		$log = [
			'version'             => $version,
			'log_type'            => '',
			'page_id'             => (int)$pageId,
			'lang'                => $lang,
			'site_code'           => $siteCode,
			'action_type'         => $actionType,
			'file_name'           => $fileInfo['basename'],
			'file_type'           => $fileType,
			'file_size'           => 0,
			'file_hash'           => '',
			$this->fieldLocalPath => $fileRelativePath,
			's3_url'              => '',
			'diff'                => '',
			'ip'                  => app()->ip->get(true),
			'create_user'         => $user,
			'create_time'         => $time,
			'update_user'         => $user,
			'update_time'         => $time
		];

		$s3Key = $this->getS3KeyByLocalPath($localPath, $fileType);
		$item = [
			'route'               => app()->controller->getRoute(),
			$this->fieldLocalPath => $localPath,
			's3_key'              => $s3Key,
			'activity_id'         => (int)$this->activityInfo->id,
			'page_id'             => $pageId,
			'lang'                => $lang,
			'version'             => $version,
			'file_type'           => $fileType,
			'site_code'           => $siteCode,
			'redis_key'           => $redisKey ?? '',
			'css_s3'              => $cssS3,
			'js_s3'               => $jsS3,
			'mold'                => $this->activityInfo->mold,
			'is_home_b'           => $isHomeB,
			'pipeline'            => $pipeline
		];
		$log['log_type'] = PagePublishLogModel::LOG_TYPE_PUBLISH;
		$s3Url = $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $this->activityInfo->mold, $pipeline]);

		$item['log_data'] = $log;
		$item['page_url'] = $s3Url;
		$redisData[] = json_encode($item);

		$log['log_type'] = PagePublishLogModel::LOG_TYPE_CREATE;
		$logData[] = $log;

		return [$fileRelativePath, $s3Url];
	}

	/**
	 * 将js和css拼接到html中
	 *
	 * @param array $params
	 *
	 * @return bool|string
	 * @throws \yii\base\ViewNotFoundException
	 */
	private function buildHtmlWithJsAndCss ($params)
	{
		list($pageId, $lang, $siteCode, $html, $cssS3, $jsS3, $isPublish, $mold, $isHomeB) = $params;
		/** @var \app\modules\common\zf\models\PageModel $pageModel */
		$pageModel = PageModel::getById($pageId);

		//$htmlPageUrl = $this->getPageLangUrl([$pageId, $lang, $this->htmlFileType, $siteCode, $mold, $isHomeB, $pageModel->pipeline]);

		$activityPageType = PageModel::getActivityPageType($pageModel);


        // growing io 组件埋点数据
        $_cacheKey = $pageId .'-'. $lang;
        $growingIoUiInfo = '';
        if (isset($this->growingIoCache[$_cacheKey])) {
            $growingIoUiInfo = sprintf('var GESHOP_GROWINGIO=%s;', $this->growingIoCache[$_cacheKey]);
            unset($this->growingIoCache[$_cacheKey]);
        }

        // 发布页面全局js
		$publishJs = /** @lang html */
			'<script type="text/javascript">'
			. ' var GESHOP_HAS_AUTO_REFRESH_UI = "' . ($this->hasAsyncDataJson ? 1 : 0) . '";' // 是否包含自动刷新组件
            . $growingIoUiInfo // growing io 组件埋点数据
			. '</script>';

		// SEO 优化
        $seoLinks = ($activityPageType == SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL)
            ? $this->getSEOPageUrlHtml($pageModel, $lang)
            : '';

		//对于html内容，需要将头尾拼上
		$componentStatic = ['css' => '', 'js' => ''];
		$cssVersion = app()->params['css_version'];
		$componentStatic['css'] = $seoLinks;
		$componentStatic['css'] .= $publishJs;
		$componentStatic['css'] .= $this->getHeadExtraCss($pageId, $lang, $cssVersion, $siteCode, $activityPageType, $isPublish);
		if (!empty($cssS3)) {
			$componentStatic['css'] .= /** @lang html */
				'<link rel="stylesheet" href="' . $cssS3 . '?version=' . $cssVersion . '">';
		}

		$componentStatic['js'] = $this->getHeadExtraJs($cssVersion, $lang, $siteCode, $activityPageType, $isPublish);
		if (!empty($jsS3)) {
			$componentStatic['js'] .= /** @lang html 解决站点组件JS未加载的问题 */
				'<script src="' . $jsS3 . '?version=' . $cssVersion . '"></script>';
		}
		$componentStatic['js'] .= '<script src="//geshopcss.logsss.com/vue/vue.min.js"></script>';
		$componentStatic['js'] .= '<script src="' . app()->url->assets->clientJs('initial', $isPublish) . '"></script>';

		//获取头尾html(将生成的css和js文件插入头尾位置)
		$headerAndFooter = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic, $pageModel->pipeline);
		if (empty($headerAndFooter)) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '平台头尾未找到', [
				'lang'     => $lang,
				'siteCode' => $siteCode
			]);

			return false;
		}

		return $this->replaceSiteMainBody($headerAndFooter, $html);
	}

    /**
     * 获取发布页面PC、M关联连接(SEO优化)
     *
     * @param \app\modules\common\zf\models\PageModel $pageModel
     * @param string $lang
     * @return string
     */
	private function getSEOPageUrlHtml($pageModel, $lang)
    {
        $siteCode = $pageModel->site_code;
        $pipeline = $pageModel->pipeline;
        if (SitePlatform::isAppPlatform($siteCode)) {
            return '';
        }

        $isPc = SitePlatform::isPCPlatform($siteCode);
        $pcPageId = $isPc
            ? $pageModel->id
            : PageModel::getGroupPlatformPageId($pageModel->id, $siteCode, $pipeline, SitePlatform::PLATFORM_TYPE_PC);

        $pcLangModel = null;
        if (!empty($pcPageId)) {
            $pcLangModel = PageLanguageModel::findOne([
                'page_id' => $pcPageId,
                'lang'    => $lang
            ]);
        }

        $websiteCode = SitePlatform::getSiteBySiteCode($siteCode);
        $pcSiteCode = SitePlatform::getPcPlatformSiteCode($websiteCode);
        $wapSiteCode = SitePlatform::getWapPlatformSiteCode($websiteCode);
	    if (!$pcSiteCode) $pcSiteCode = $wapSiteCode = SitePlatform::getWebPlatformSiteCode($websiteCode);

        // PC、M有关联
        if (!empty($pcLangModel) && !empty($pcLangModel->redirect_url)) {
            $configPcPipelines = PipelineUtils::getConfigSpecialSupportPipelineList($pcSiteCode);
            if (isset($configPcPipelines[$pipeline][$lang])) {
                $pcUrl = sprintf('%s/%s.html', rtrim($configPcPipelines[$pipeline][$lang], '/'), $pcLangModel->url_name);
                $seoLinks = sprintf('<link rel="canonical" href="%s" />', $pcUrl);
                if ($isPc) {
                    $seoLinks .= sprintf('<link rel="alternate" media="only screen and (max-width: 640px)" href="%s" />', $pcLangModel->redirect_url);
                }
                return $seoLinks;
            }
        }

        // PC、M没有关联
        $langModel = PageLanguageModel::findOne([
            'page_id' => $pageModel->id,
            'lang'    => $lang
        ]);
        $configPipelines = $isPc
            ? PipelineUtils::getConfigSpecialSupportPipelineList($pcSiteCode)
            : PipelineUtils::getConfigSpecialSupportPipelineList($wapSiteCode);
        $pageUrl = sprintf('%s/%s.html', rtrim($configPipelines[$pipeline][$lang], '/'),  $langModel->url_name);
        return sprintf('<link rel="canonical" href="%s" />', $pageUrl);
    }

	/**
	 * 将站点头尾内容里 main 标签替换为 geshop渲染html
	 *
	 * @param string $sitePageHtml
	 * @param string $renderHtml
	 *
	 * @return string
	 */
	protected function replaceSiteMainBody ($sitePageHtml, $renderHtml)
	{
		$main = '/<!--\s*geshop\s*main\s*start\s*-->/';
		preg_match($main, $sitePageHtml, $matches);
		if (!empty($matches[0])) {
			$renderHtml = str_replace($matches[0], $matches[0] . $renderHtml, $sitePageHtml);
		}

		return $renderHtml;
	}

	/**
	 * 从数据库获取缓存
	 *
	 * @param array $params
	 *
	 * @return string
	 * @throws \yii\base\ViewNotFoundException
	 */
	private function getContentCache ($params)
	{
		list($pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3, $mold, $isHomeB) = $params;
		$content = '';
		$publishCacheModel = PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
		if ($publishCacheModel) {
			$content = $publishCacheModel[$fileType];

			if ($fileType === $this->htmlFileType
				&& false !== ($result = $this->buildHtmlWithJsAndCss([$pageId, $lang, $siteCode, $content, $cssS3, $jsS3, true, $mold, $isHomeB]))
			) {
				//对于html内容，需要将头尾拼上
				$content = $result;
			}
		}

		return $content;
	}

	/**
	 * 存储文件到S3上
	 *
	 * @param array $data = [
	 *                    string $localPath 本地文件路径
	 *                    string $s3Key 文件在S3上存储key
	 *                    string $lang 语言代码简称
	 *                    array $logData 日志数据
	 *                    ]
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function putObjectToS3($data)
	{
		$redisKey = $data['redis_key'];
		$localPath = $data['local_path'];
		$s3Key = $data['s3_key'];
		$logData = $data['log_data'];
		$lang = $data['lang'];
		$pageId = (int)$data['page_id'];
		$siteCode = $data['site_code'];
		$fileType = $data['file_type'];
		$version = $data['version'];
		$cssS3 = $data['css_s3'];
		$jsS3 = $data['js_s3'];
		$activityId = (int)$data['activity_id'];
		$mold = (int)($data['mold'] ?? 0);
		$isHomeB = (bool)($data['is_home_b'] ?? false);
		$pipeline = $data['pipeline'] ?? '';

		//内容写入文件
		$content = app()->redis->get($redisKey);
		if(!empty($data['type']) && $data['type'] == 1 && $fileType == 'html'){//推广落地页html内容
			$content = $this->getHtmlContentCache([$pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3]);
		}
		if (empty($content) && 'native' != $fileType) {
			//内容过期后，从数据库去获取
			$content = $this->getContentCache([$pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3, $mold, $isHomeB]);
		}

		if (false === file_put_contents($localPath, $content) || !file_exists($localPath)) {
			\Yii::error('文件内容写入失败' . $localPath, __METHOD__);
			return [false, '文件内容写入失败：' . $localPath];
		}

		if (in_array($fileType, [$this->htmlFileType, $this->asyncJsonFileType, $this->nativeJsonFileType])) {
			$s3Res = app()->s3->putObject($localPath, $s3Key, null,  ['CacheControl' => 'max-age=' . app()->params['cdnCacheControl'],
                'Expires'=> gmdate('D, d M Y H:i:s T', strtotime(app()->params['cdnExpires']))
            ]);
		} else {
			$s3Res = app()->s3PublishStatic->putObject($localPath, $s3Key);
		}

		$s3Url = \is_string($s3Res) ? '' : $s3Res->get('ObjectURL');

		/** @var \Aws\Result $s3Res */
		\Yii::info('静态文件推送s3结果：' . $s3Url . '|||||||||||' . $s3Res, __METHOD__);

		$res = false;
		if (!empty($s3Url)) {
			$res = true;
			clearstatcache();//清理filesize等函数的缓存
			$fileSize = filesize($localPath);
			$fileHash = hash_file('md5', $localPath);

			//更新本地文件生成记录的file_size和file_hash
			$resUpdate = PagePublishLogModel::updateAll([
				'file_size' => $fileSize,
				'file_hash' => $fileHash
			], [
				'log_type' => PagePublishLogModel::LOG_TYPE_CREATE,
				'page_id' => $pageId,
				'lang' => $lang,
				'file_type' => $fileType,
				'version' => $version
			]);

			//上传成功后，记录日志
			$logData['s3_url'] = $s3Url;
			$logData['file_size'] = $fileSize;
			$logData['file_hash'] = $fileHash;

			$resSave = $this->savePublishLog([$logData]);

			//清理CDN缓存
			$clearUrl = empty($data['page_url'])
				? $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $mold, $pipeline])
				: $data['page_url'];
			$this->clearCDNCache($clearUrl);

			//发布首页还需清理首页的缓存
			if (!$activityId && $fileType === $this->htmlFileType) {
				$this->clearCDNCache($this->getSiteDomain($siteCode, $lang, $pipeline));
			}

			if (!$resSave || !$resUpdate) {
				return [false, '文件上传后更新本地记录出错'];
			}

			//清理redis缓存记录
			app()->redis->del($redisKey);
		}

		return [$res, $s3Url];
	}

	/**
	 * 获取站点域名，区分语言的
	 *
	 * @param $siteCode
	 * @param $lang
	 * @param $pipeline
	 *
	 * @return string
	 */
	public function getSiteDomain ($siteCode, $lang, $pipeline)
	{
		$domain = '';
		$secondaryDomain = app()->params['sites'][$siteCode]['secondary_domain'][$pipeline][$lang] ?? '';
		if (!empty($secondaryDomain)) {
			$parse = parse_url($secondaryDomain);
			if (!empty($parse['scheme']) && !empty($parse['host'])) {
				$domain = $parse['scheme'] . '://' . $parse['host'];
			}
		}

		return $domain;
	}

	/**
	 * 清理CDN缓存
	 *
	 * @param string $url 待清理的URL
	 */
	private function clearCDNCache($url)
	{
		$api = app()->params['clearCDNAPI'];
		if (empty($api) || empty($url)) {
			//未配置clearCDNAPI的则无需清理CDN缓存
			return;
		}

		$api .= $url;
		$responseData = [];
		try {
			$curl = new BaseResponseCurl();
			$response = $curl->slient()->request('get', $api);
			$response && $responseData = json_decode($response->getBody() . '', true);
			\Yii::info('CDN缓存清理返回原始值：' . $api . '----' . json_encode($responseData), __METHOD__);
		} catch (GuzzleException $e) {
			\Yii::error('CDN缓存清理GuzzleException：' . $e->getMessage(), __METHOD__);
		} catch (\Exception $e) {
			\Yii::error('CDN缓存清理Exception：' . $e->getMessage(), __METHOD__);
		}

		if (!(isset($responseData['results']) && isset($responseData['results'][1])
			&& $responseData['results'][1]['result'] && $responseData['results'][1]['result']['status']
			&& $responseData['results'][1]['result']['status'] === $this->msgSuccess)
		) {
			\Yii::error('CDN缓存清理失败：' . $api . '---' . json_encode($responseData), __METHOD__);
		}
	}

	/**
	 * 保存本地缓存文件生成日志记录、文件发布到S3上去的日志记录
	 *
	 * @param array $data
	 *
	 * @return int
	 * @throws \yii\db\Exception
	 */
	public function savePublishLog (array $data)
	{
		return PagePublishLogModel::insertAllData($data);
	}

	/**
	 * 获取文本比对结果
	 *
	 * @param string $oldContent 旧文本内容
	 * @param string $newContent 新文本内容
	 *
	 * @return string
	 */
	public function getDiffContent ($oldContent, $newContent)
	{
		// Include two sample files for comparison
		$a = explode("\n", $oldContent);
		$b = explode("\n", $newContent);

		// Options for generating the diff
		$options = [
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		];

		// Initialize the diff class
		$diff = new Diff($a, $b, $options);

		$renderer = new Diff_Renderer_Html_SideBySide;

		return $this->packageDiffContent($diff->render($renderer));
	}

	/**
	 * 将diff内容打包成html
	 *
	 * @param string $html diff内容html
	 *
	 * @return string
	 */
	private function packageDiffContent ($html)
	{
		return /** @lang html */
			'<html><head><title>文件内容差异比对</title></head><body>' . $html . '</body></html>';
	}

	/**
	 * 得到最后一次生成的文件记录
	 *
	 * @param int    $pageId   页面ID
	 * @param string $lang     语言代码简称
	 * @param string $fileType 文件后缀名
	 *
	 * @return array|null|\yii\db\ActiveRecord
	 */
	private function getLastFileContent ($pageId, $lang, $fileType)
	{
		return PagePublishLogModel::find()->where([
			'log_type'  => PagePublishLogModel::LOG_TYPE_CREATE,
			'page_id'   => $pageId,
			'lang'      => $lang,
			'file_type' => $fileType
		])->orderBy('id DESC')->asArray()->one();
	}

	/**
	 * 根据文件在S3上存储的key得到S3上访问URL
	 *
	 * @param array $params     = [
	 *                          string $s3Key    文件在S3上的路径
	 *                          string $lang     语言代码简称
	 *                          string $fileType 文件存储后缀
	 *                          string $siteCode 站点siteCode
	 *                          ]
	 * @param int   $activityId 活动ID
	 *
	 * @return string
	 */
	private function getS3UrlByS3Key ($params, $activityId = -1)
	{
		list($s3Key, $lang, $fileType, $siteCode, $mold, $pipeline) = $params;
		$isAsyncDataFileType = ($fileType === $this->asyncJsonFileType);
		$isAsyncDataFileType && $fileType = $this->htmlFileType;
		$nativeJsonFileType = ($fileType === $this->nativeJsonFileType);
		$nativeJsonFileType && $fileType = $this->htmlFileType;
		$siteConf = app()->params['sites'][$siteCode];

		if ($fileType === $this->htmlFileType) {
			if (-1 === $activityId) {
				$activityId = $this->activityInfo->id;
			}
			$delimiter = $this->getHtmlFilePathPreOnS3($siteCode, $lang, $mold, $pipeline);
			$domainKey = (0 === $activityId) ? 'home_secondary_domain' : 'secondary_domain';
			$domain = $siteConf[$domainKey][$pipeline][$lang];
		} else {
			$delimiter = app()->params['s3PublishConf']['staticKeyPre'] . '/'
				. $this->getStaticsFilePathPreOnS3($siteCode, $lang, $pipeline);
			$domain = app()->params['s3PublishConf']['staticDomain'] . $delimiter;
		}

		$explode = explode($delimiter, $s3Key);

		return $explode && isset($explode[1]) ? $domain . $explode[1] : '';
	}

	/**
	 * 获取页面url（发布之前的）
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getPageLangUrl ($params)
	{
		list($pageId, $lang, $fileType, $siteCode, $mold, $isHomeB, $pipeline) = $params;
		$localPath = $this->getLocalPath([$pageId, $lang, $fileType, $siteCode, $mold, $isHomeB, $pipeline]);
		$s3Key = $this->getS3KeyByLocalPath($localPath, $fileType);

		return $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $mold, $pipeline]);
	}

	/**
	 * 根据文件在本地存储的绝对路径，获取对应的在S3上存储的key
	 *
	 * @param string $path     文件在本地存储的绝对路径
	 * @param string $fileType 文件存储后缀
	 *
	 * @return string
	 */
	private function getS3KeyByLocalPath ($path, $fileType)
	{
		$keyPre = in_array($fileType, [$this->htmlFileType, $this->asyncJsonFileType, $this->nativeJsonFileType], true) ? '' : app()->params['s3PublishConf']['staticKeyPre'];

		return $keyPre . $this->getRelativePathByLocalPath($path);
	}

	/**
	 * 获取文件在本地存储的绝对路径
	 *
	 * @param array $params = [
	 *                      int    $pageId   页面ID
	 *                      string $lang     语言代码简称
	 *                      string $fileType 文件存储后缀
	 *                      string $siteCode 站点siteCode
	 *                      ]
	 *
	 * @return string
	 */
	private function getLocalPath ($params)
	{
		list($pageId, $lang, $fileType, $siteCode, $mold, $isHomeB, $pipeline) = $params;
		$isAsyncDataFileType = ($fileType === $this->asyncJsonFileType);
		$isAsyncDataFileType && $fileType = $this->htmlFileType;
		$isNativeApiJsonFileType = ($fileType === $this->nativeJsonFileType);
		$isNativeApiJsonFileType && $fileType = $this->htmlFileType;
		$path = $this->getPathByFileType($fileType, $lang, $siteCode, $mold, $pipeline);

		if ($isAsyncDataFileType) {
			return $path . AutoRefreshUtils::getAsyncDataJsFileName($pageId);
		}

		if ($isNativeApiJsonFileType) {
			return $path . AutoRefreshUtils::getAsyncDataNativeFileName($pageId, $pipeline, $lang);
		}

		if ($fileType === $this->htmlFileType && 'home' === app()->controller->module->module->id) {
			$urlName = $isHomeB
				? "index_new_preview_{$pipeline}_{$_SERVER['REQUEST_TIME']}"
				: "index_preview_{$pipeline}_{$_SERVER['REQUEST_TIME']}";
		} else {
			if ($fileType === $this->htmlFileType && !empty($this->pageArr[$pageId]['langList'][$lang]['url_name'])) {
				$suffix = !empty(app()->params['sites'][$siteCode]['isTest']) ? '_' . md5($pageId) : '';
				$urlName = $this->pageArr[$pageId]['langList'][$lang]['url_name'] . $suffix;
			} else {
				$urlName = md5($pageId . '.' . $fileType);
			}
		}
		$path .= $urlName . '.' . $fileType;

		//js和css需要重命名文件名来防止缓存
		if ($fileType !== $this->htmlFileType) {
			$fileName = pathinfo($path, PATHINFO_BASENAME);
			$path = str_replace($fileName, md5($fileName . microtime()) . '.' . $fileType, $path);
		}

		return $path;
	}

	/**
	 * 根据文件在本地绝对路径返回相对路径
	 *
	 * @param string $localPath 文件在本地绝对路径
	 *
	 * @return string
	 */
	private function getRelativePathByLocalPath ($localPath)
	{
		$explode = !empty($localPath) ? explode('runtime', $localPath) : [];

		return $explode && isset($explode[1]) ? $explode[1] : '';
	}

	/**
	 * 根据文件类型获取文件在本地缓存的路径
	 *
	 * @param string $fileType 文件类型
	 * @param string $lang     语言代码简称
	 * @param string $siteCode
	 * @param string $mold
	 * @param string $pipeline 渠道
	 *
	 * @return string
	 */
	private function getPathByFileType ($fileType, $lang, $siteCode, $mold, $pipeline)
	{
		$dir = $fileType === $this->htmlFileType
			? $this->getHtmlFilePathPreOnS3($siteCode, $lang, $mold, $pipeline)
			: $this->getStaticsFilePathPreOnS3($siteCode, $lang, $pipeline);
		$path = app()->basePath . DIRECTORY_SEPARATOR
			. 'runtime' . DIRECTORY_SEPARATOR
			. $dir . DIRECTORY_SEPARATOR;
		if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
			return '';
		}

		return $path;
	}

	/**
	 * 根据站点SiteCode和语言lang获取html文件在S3上存储路径的前缀
	 *
	 * @param        $siteCode
	 * @param        $lang
	 * @param        $mold
	 * @param string $pipeline 渠道
	 *
	 * @return mixed
	 */
	private function getHtmlFilePathPreOnS3 ($siteCode, $lang, $mold, $pipeline)
	{
		$pathKey = 's3PublishPath';
		if ('home' === app()->controller->module->module->id) {
			$pathKey = 's3HomePublishPath';
		}
		$siteConf = app()->params['sites'][$siteCode][$pathKey][$pipeline];

		return $siteConf[$lang];
	}

	/**
	 * 根据站点SiteCode和语言lang获取statics文件在S3上存储路径的前缀
	 *
	 * @param $siteCode
	 * @param $lang
	 * @param $pipeline
	 *
	 * @return mixed
	 */
	private function getStaticsFilePathPreOnS3 ($siteCode, $lang, $pipeline)
	{
		$s3StaticPathKey = 's3StaticPath';
		$siteConf = app()->params['sites'][$siteCode][$s3StaticPathKey][$pipeline];

		return $siteConf[$lang];
	}

	/**
	 * 添加自动刷新任务（若存在则会更新）
	 *
	 * @param int $pageId      页面ID
	 * @param int $refreshTime 自动刷新时间间隔
	 *
	 * @return bool
	 */
	public function zaddRefreshTask ($pageId, $refreshTime)
	{
		$key = $this->redisPrefix . app()->redisKey->getRefreshTaskRedisKey();
		try {
			if ($refreshTime) {
				app()->redis->zadd($key, time() + $refreshTime, $pageId);
			} else {
				app()->redis->zrem($key, $pageId);
			}
		} catch (\Exception $e) {
			\Yii::info('自动刷新队列处理错误：' . $pageId . '-' . $refreshTime, __METHOD__);

			return false;
		}

		return true;
	}

	/**
	 * 设置跨站点siteCode的错误信息
	 *
	 * @param array $pageIds
	 * @param int   $errorCount
	 */
	protected function setSiteCodeCrossSiteError (array $pageIds, int &$errorCount)
	{
		foreach ($pageIds as $pageId) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '每次发布仅能同一站点下一起发布');
		}
		$errorCount = \count($pageIds);
	}

	/**
	 * 设置未找到siteCode的错误信息
	 *
	 * @param array $pageIds
	 * @param int   $errorCount
	 */
	protected function setSiteCodeNotFoundError (array $pageIds, int &$errorCount)
	{
		foreach ($pageIds as $pageId) {
			$this->createHtmlErrors[] = $this->createErrorItem($pageId, '站点site_code错误');
		}
		$errorCount = \count($pageIds);
	}

	/**
	 * 设置未找到页面的错误信息
	 *
	 * @param array $pageIds
	 * @param int   $errorCount
	 */
	protected function setPageNotFoundError (array $pageIds, int &$errorCount)
	{
		$errors = 0;
		foreach ($pageIds as $pageId) {
			if (!isset($this->pageArr[$pageId])) {
				$errors++;
				$this->createHtmlErrors[] = $this->createErrorItem($pageId, '未找到页面ID对应的信息');
			}
		}
		$errorCount = $errors;
	}

	/**
	 * 组建error错误信息结构
	 *
	 * @param       $pageId
	 * @param       $message
	 * @param array $data
	 *
	 * @return array
	 */
	protected function createErrorItem ($pageId, $message, array $data = [])
	{
		return [
			'page_id' => $pageId,
			'message' => $message,
			'data'    => $data
		];
	}

	/**
	 * 从数据库获取缓存
	 *
	 * @param array $params
	 *
	 * @return string
	 * @throws \yii\base\ViewNotFoundException
	 */
	private function getHtmlContentCache ($params)
	{
		list($pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3) = $params;
		$content = '';
		$publishCacheModel = PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
		if ($publishCacheModel) {
			$content = '<meta charset="UTF-8">' . $publishCacheModel['customJs'] . '<--this is html start-->' . $publishCacheModel['html'];
		}

		return $content;
	}

}
