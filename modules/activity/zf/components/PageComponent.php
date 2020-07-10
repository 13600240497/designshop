<?php

namespace app\modules\activity\zf\components;

use app\modules\activity\zf\cache\PageCache;
use app\modules\common\zf\models\{
	ActivityModel,
	ActivityGroupModel,
	PageModel,
	PageGroupModel,
	PageLanguageModel,
	PageLayoutModel,
	PageOperateLogModel,
	PageUiModel,
	PageUiComponentDataModel
};
use app\base\SiteConstants;
use app\base\PipelineUtils;
use app\modules\common\zf\components\CommonPageComponent;
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\activity\zf\traits\PublishTrait;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use ego\enums\Platform;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use app\base\SitePlatform;
use yii\helpers\VarDumper;

/**
 * 页面组件
 */
class PageComponent extends CommonPageComponent
{
//	use PublishTrait;

	/** @var array Banner UI组件key */
	const BANNER_UI_COMPONENT_KEY = [
		SitePlatform::PLATFORM_CODE_PC  => 'U000025',
		SitePlatform::PLATFORM_CODE_WAP => 'U000026',
		SitePlatform::PLATFORM_CODE_APP => 'U000026'
	];

	/** @var string 子页面默认预览图片地址 */
	const PAGE_DEFAULT_PREVIEW_PIC_URL = '/resources/images/default/banner_default.png';

	/**
	 * 转换博客活动URL
	 *
	 * @param string $pageUrl 原博客页面URL,如：http://www.zaful.com/promotion/blog/test-zf-blog-42424.html
	 *
	 * @return string
	 */
	public static function getBlogPageUrl ($pageUrl)
	{
		if (strpos($pageUrl, '/blog') !== false) {
			$pageUrl = str_replace('/blog', '', $pageUrl);
		}
		return str_replace('/promotion', '/blog', $pageUrl);
	}

	/**
	 * 页面列表
	 *
	 * @param int $activityId 活动ID
	 *
	 * @return array
	 */
	public function lists ($activityId)
	{
		if (!$activityId) {
			return app()->helper->arrayResult(1, 'activity_id不能为空');
		}

		$lang = NULL;
		$pipelineList = [];
		$configAllLangList = app()->params['lang'];
		$hasDesignPermission = 0;
		$specialPermissions = [];
		$allSpecialPermissions = [];
		/** @var \app\modules\common\zf\models\ActivityModel $activity */
		$activity = ActivityModel::findOne($activityId);
		if ($activity && !empty($activity->pipeline)) {
			$configAllPipelineList = PipelineUtils::getConfigAllPipelineList($activity->site_code);
			$configSupportPipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($activity->site_code);
			//兼容RG无分组ID的老数据
			if (!$activity->group_id) {
				$platform = SitePlatform::getPlatformCodeBySiteCode($activity['site_code']);
				$activityGroupModel = new ActivityGroupModel();
				$activityGroupModel ->platform_list = $platform;
				$activityGroupModel ->lang_list = $activity['lang'];
				$activityGroupModel->support_list = json_encode([$platform => json_decode($activity['lang'])]);
			}else {
				$activityGroupModel = ActivityGroupModel::findOne($activity->group_id);
			}
			foreach (json_decode($activityGroupModel->lang_list, true) as $key => $item) {
				$pipelineList[$key] = [
					'code' => $key,
					'name' => $configAllPipelineList[$key] ?? ''
				];
				foreach ($item as $_langCode) {
					$pipelineList[$key]['lang_list'][$_langCode] = [
						'code'       => $_langCode,
						'name'       => $configAllLangList[$_langCode]['name'],
						'url_prefix' => $configSupportPipelineList[$key][$_langCode] ?? ''
					];
					$lang = $lang ?? $_langCode;
				}
			}

			$privilegeComponent = new AdminSitePrivilegeComponent();
			// 所有渠道权限
			$supportList = json_decode($activityGroupModel->support_list, true);
			foreach ($supportList as $_platformCode => $langList) {
				$_permissions = $privilegeComponent->getCurrentUserValidSiteSpecialPermissions(
					$activity->site_code, array_keys($langList)
				);

				$_platformPermissions = [];
				foreach ($_permissions as $_code) {
					$_platformPermissions[$_code] = [
						'code' => $_code,
						'name' => $configAllPipelineList[$_code] ?? ''
					];
				}
				$allSpecialPermissions[$_platformCode] = $_platformPermissions;
			}

			// 检查当前用户是否有装修权限
			$activityPipelineList = !empty($activity->pipeline) ? explode(',', $activity->pipeline) : [];
			$hasPermissions = $privilegeComponent->getCurrentUserValidSiteSpecialPermissions($activity->site_code, $activityPipelineList);
			if (!empty($hasPermissions)) {
				foreach ($hasPermissions as $_code) {
					$specialPermissions[$_code] = [
						'code' => $_code,
						'name' => $configAllPipelineList[$_code] ?? ''
					];
				}
			}
			$hasDesignPermission = empty($hasPermissions) ? 0 : 1;
		}

		$pageList = $this->pageLists(new PageModel(), $activityId);
		$data = [];
		if ($pageList) {
			$pageList = ArrayHelper::toArray($pageList);
			$pageIds = array_column($pageList, 'id');

			$pageLangList = PageLanguageModel::find()->alias('pl')
				->where(['pl.page_id' => $pageIds])
				->orderBy('pl.id asc')
				->all();

			if ($pageLangList) {
				$data = $this->buildListData($pageList, ArrayHelper::toArray($pageLangList), $activity);
				//获取子页面活动组语言列表
				list($pageListGroupInfoList, $pageListGroupLanguages) = $this->getPageListGroupInfoAndLanguagesIML(
					$activity, $pageIds, $pageLangList
				);

				// 获取列表渠道信息
				$pagesPipelineInfo = $this->getPageListPipelineInfo($activity, $pageList);

				//获取子页面预览图片地址，获取第一个Banner UI组件的图片地址，没有则使用默认图片
				$pagePreviewPicUrls = $this->getPageFirstBannerPicUrls($pageIds, $activity->site_code, $lang);
				foreach ($data as &$_pageInfo) {
					if (isset($pageListGroupInfoList[$_pageInfo['id']]))
						$_pageInfo['group_info'] = $pageListGroupInfoList[$_pageInfo['id']];
					$_pageInfo['group_languages'] = $pageListGroupLanguages[$_pageInfo['id']];
					$_pageInfo['preview_pic_url'] = $pagePreviewPicUrls[$_pageInfo['id']];

					// 页面渠道信息
					$_pipelineInfo = $pagesPipelineInfo[$_pageInfo['group_id']] ?? ['page_list' => []];
					if ($activity) {
						foreach ($_pipelineInfo['page_list'] as $_pipelineCode => &$_pipelinePageInfo) {
							$_pipelinePageInfo = ArrayHelper::merge(
								$_pipelinePageInfo,
								$pipelineList[$_pipelineCode] ?? []
							);
						}
					}
					$_pageInfo['pipeline_info'] = $_pipelineInfo;

				}
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
			'has_design_permission'   => $hasDesignPermission,
			'special_permissions'     => $specialPermissions,
			'all_special_permissions' => $allSpecialPermissions,
			'page_list'               => $data,
			'pipeline_list'           => $pipelineList,
		]);
	}

    /**
     * 获取子页面列表分组信息，封装成1个小时的缓存
     * @param $activity
     * @param $pageIds
     * @param $pageLangList
     * @return mixed
     */
    private function getPageListGroupInfoAndLanguages ($activity, $pageIds, $pageLangList)
    {
        $pageCache = new PageCache();
        app()->rcache->keyPrefix = $pageCache->getPrefix($activity->site_code, $activity->id);
        $key = $pageCache->generateKey($activity->site_code, $activity->id, join(',',$pageIds));
        app()->rcache->delete($key);
        return app()->rcache->getOrSet($key, function($cache) use ($key, $activity, $pageIds, $pageLangList) {
                \Yii::info('[getPageListGroupInfoAndLanguages]  redis缓存刷新, 每小时刷新一次, Key =>  '. $key);
                return $this->getPageListGroupInfoAndLanguagesIML($activity, $pageIds, $pageLangList);
        }, 60*60);
    }

	/**
	 * 获取子页面列表分组信息
	 *
	 * @param \app\modules\common\zf\models\ActivityModel $activity
	 * @param array                                       $pageIds
	 * @param array                                       $pageLangList
	 *
	 * @return array
	 */
	private function getPageListGroupInfoAndLanguagesIML ($activity, $pageIds, $pageLangList)
	{
		$pageGroupInfo = [];
		$pageGroupLanguages = [];
		$groupPageLangMap = [];
		$groupPageMap = [];

		//子页面分组信息
		$pageToGroupMap = PageGroupModel::find()->where(['page_id' => $pageIds])->indexBy('page_id')->asArray()->all();
		if (!empty($pageToGroupMap)) {
			$pageGroupIds = array_column($pageToGroupMap, 'page_group_id');
			$pageGroupModelList = PageGroupModel::find()->where(['page_group_id' => $pageGroupIds])->asArray()->all();
			$allPageIds = array_column($pageGroupModelList, 'page_id');
			$otherPageIds = array_diff($allPageIds, $pageIds);

			foreach ($pageGroupModelList as $pageGroupModel) {
				$groupPageMap[$pageGroupModel['page_group_id']][$pageGroupModel['page_id']] = $pageGroupModel;
			}

			$otherPageLangList = PageLanguageModel::find()->alias('pl')
				->where(['pl.page_id' => $otherPageIds])
				->orderBy('pl.id desc')
				->all();
			$allPageLangList = ArrayHelper::merge(
				ArrayHelper::toArray($pageLangList),
				ArrayHelper::toArray($otherPageLangList)
			);
		} else {
			$allPageLangList = ArrayHelper::toArray($pageLangList);
		}

		foreach ($allPageLangList as $langInfo) {
			$langInfo['langName'] = app()->params['lang'][$langInfo['lang']]['name'];
			$langInfo['share_place'] = $langInfo['share_place'] ? json_decode($langInfo['share_place'], true) : [];
			$groupPageLangMap[$langInfo['page_id']][$langInfo['lang']] = $langInfo;
		}

		$configAllPipelineList = PipelineUtils::getConfigAllPipelineList($activity->site_code);
		foreach ($pageIds as $pageId) {
			if (isset($pageToGroupMap[$pageId])) {
				$pageGroupId = $pageToGroupMap[$pageId]['page_group_id'];
				$pageGroupInfo[$pageId] = [
					'activity_group_id' => $pageToGroupMap[$pageId]['activity_group_id'],
					'page_group_id'     => $pageGroupId,
					'platform_list'     => []
				];

				foreach ($groupPageMap[$pageGroupId] as $_groupInfo) {
					$platformCode = SitePlatform::getPlatformCodeByPlatformType($_groupInfo['platform_type']);
					$pageGroupLanguages[$pageId][$platformCode][$_groupInfo['pipeline']] = [
						'key'      => $_groupInfo['pipeline'],
						'name'     => $configAllPipelineList[$_groupInfo['pipeline']] ?? '',
						'page_id'  => $_groupInfo['page_id'],
						'language' => $groupPageLangMap[$_groupInfo['page_id']],
					];

					$pageGroupInfo[$pageId]['platform_list'][$platformCode][$_groupInfo['pipeline']] = [
						'page_id' => (int)$_groupInfo['page_id']
					];
				}
			}
		}

		return [$pageGroupInfo, $pageGroupLanguages];
	}


	/**
	 * 获取子页面渠道信息
	 *
	 * @param array $params get参数
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function pipelineInfo (array $params)
	{
		if (empty($params['group_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的参数group_id');
		}

		$pageModelList = PageModel::find()->where(['group_id' => $params['group_id']])->all();
		if (empty($pageModelList)) {
			throw new JsonResponseException($this->codeFail, '渠道页面不存在');
		}

		/** @var \app\modules\common\zf\models\PageModel $pageModel */
		$pageModel = $pageModelList[0];

		/** @var \app\modules\common\zf\models\ActivityModel $activityModel */
		$activityModel = ActivityModel::findOne($pageModel->activity_id);

		$pipelineList = [];
		$configAllLangList = app()->params['lang'];
		if ($activityModel && !empty($activityModel->pipeline)) {
			$configAllPipelineList = PipelineUtils::getConfigAllPipelineList($activityModel->site_code);
			$configSupportPipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($activityModel->site_code);
			$activityGroupModel = ActivityGroupModel::findOne($activityModel->group_id);
			foreach (json_decode($activityGroupModel->lang_list, true) as $key => $item) {
				$pipelineList[$key] = [
					'code' => $key,
					'name' => $configAllPipelineList[$key] ?? ''
				];
				foreach ($item as $_langCode) {
					$pipelineList[$key]['lang_list'][$_langCode] = [
						'code'       => $_langCode,
						'name'       => $configAllLangList[$_langCode]['name'],
						'url_prefix' => $configSupportPipelineList[$key][$_langCode] ?? ''
					];
					$lang = $lang ?? $_langCode;
				}
			}
		}


		$pipelineInfo = $this->getPagePipelineInfo($activityModel, $pageModelList);

		// 过滤掉当前用户没有权限的渠道
		$activityPipelineList = !empty($activityModel->pipeline) ? explode(',', $activityModel->pipeline) : [];
		$privilegeComponent = new AdminSitePrivilegeComponent();
		$hasPermissions = $privilegeComponent->getCurrentUserValidSiteSpecialPermissions($activityModel->site_code, $activityPipelineList);

		$filterPageList = [];
		foreach ($pipelineInfo['page_list'] as $_pipelineCode => $_pipelinePageInfo) {
			if (!in_array($_pipelineCode, $hasPermissions))
				continue;

			$filterPageList[$_pipelineCode] = ArrayHelper::merge(
				$_pipelinePageInfo,
				$pipelineList[$_pipelineCode] ?? []
			);
		}
		$pipelineInfo['page_list'] = $filterPageList;
		if (empty($filterPageList)) {
			throw new JsonResponseException($this->codeFail, '没有权限发布!');
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $pipelineInfo);
	}

	/**
	 * 三端合一, 新增子页面
	 *
	 * @param array $params post参数
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @since v1.4.0
	 */
	public function groupAdd (array $params)
	{
		$nowTime = time();

		//公共数据检查
		if (empty($params['activity_id']) || !is_numeric($params['activity_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的活动ID');
		}

		if (empty($params['title'])) {
			throw new JsonResponseException($this->codeFail, '名称为必填项');
		}

		if (empty($params['end_time'])) {
			throw new JsonResponseException($this->codeFail, '请选择下线时间');
		}

		if (($params['end_time'] > 0) && ($params['end_time'] <= $nowTime)) {
			throw new JsonResponseException($this->codeFail, '下线时间无效');
		}

		if (empty($params['platforms'])) {
			throw new JsonResponseException($this->codeFail, '没有选择应用端口');
		}

		if (empty($params['lang_list'])) {
			throw new JsonResponseException($this->codeFail, '没有语言数据');
		}

		$postLangDataList = json_decode($params['lang_list'], true);
		if (!is_array($postLangDataList) || empty($postLangDataList)) {
			throw new JsonResponseException($this->codeFail, '无效的提交数据');
		}

		//检查活动是否存在
		/** @var \app\modules\common\zf\models\ActivityModel $activityModel */
		$activityModel = ActivityModel::find()
			->where([
				'id'        => $params['activity_id'],
				'is_delete' => ActivityModel::NOT_DELETE
			])->one();
		if (!$activityModel) {
			throw new JsonResponseException($this->codeFail, '活动不存在');
		}

		$validPlatformParams = $this->checkAndBuildData($activityModel, 0, $params, $postLangDataList);
		unset($postLangDataList);

		if (empty($validPlatformParams)) {
			throw new JsonResponseException($this->codeFail, '无效的提交数据');
		}

		$isBlog = $params['is_blog'] ?? 0; // 是否为博客页面
		$isRedirectCountry = $params['is_redirect_country'] ?? 0; // 是否国家站自动跳转
		$isNative = $params['is_native_theme'] ?? 0; // 是否为原生专题

		//事物开始
		$transaction = app()->db->beginTransaction();
		$addedPageList = [];
		$pageGroupId = PageGroupModel::generatePageGroupId();
		try {
			//保存数据
			foreach ($validPlatformParams as $platformCode => $platformParams) {
				if (empty($platformParams)) {
					continue;
				}

				foreach ($platformParams as $pipelineCode => $pipelineParams) {
					if (empty($pipelineParams['list']) || empty($pipelineParams['page_model'])) {
						continue;
					}

					//活动组内页面关联等处理
					$this->beforeSaveProcessor($validPlatformParams, $pipelineParams, $pipelineCode);

					//保存子页面数据
					/** @var \app\modules\common\zf\models\PageModel $pageModel */
					$pageModel = $pipelineParams['page_model'];
					unset($pipelineParams['page_model']);
					$pipelineParams['platform_code'] = strtolower($platformCode);

					$pageModel->site_code = $pipelineParams['site_code'] ?? '';
					$pageModel->refresh_time = $pipelineParams['refresh_time'];
					$pageModel->end_time = $pipelineParams['end_time'];
					$pageModel->version  = $pipelineParams['version'];
					$pageModel->pipeline = $pipelineCode;
					$pageModel->group_id = $pipelineParams['group_id'];
					$pageModel->is_blog = $isBlog;
					$pageModel->is_redirect_country = $isRedirectCountry;
					if ('pc' != strtolower($platformCode)) {
						$pageModel->is_native = $isNative;
					}

					$result = $this->doActivityBatchEdit($pageModel, $pipelineParams);
					if ($result['code'] == $this->codeFail) {
						$errorMessage = sprintf(
							"应用端口 %s 添加失败: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']
						);
						throw new Exception($errorMessage);
					}

					//保存页面分组
					/** @var \app\modules\common\zf\models\PageGroupModel $pageGroupModel */
					$pageGroupModel = new PageGroupModel();
					$pageGroupModel->activity_group_id = $activityModel->group_id;
					$pageGroupModel->page_group_id = $pageGroupId;
					$pageGroupModel->platform_type = SitePlatform::getPlatformTypeByPlatformCode($platformCode);
					$pageGroupModel->page_id = $pageModel->id;
					$pageGroupModel->pipeline = $pipelineCode;
					if (!$pageGroupModel->insert(true)) {
						throw new Exception('添加子页面分组失败');
					}

					$addedPageList[$platformCode] = ['page_id' => $pageModel->id];
				}
			}

			$transaction->commit();
			return app()->helper->arrayResult($this->codeSuccess, '添加成功', $addedPageList);
		} catch (\Exception $e) {
			$transaction->rollBack();
			$message = sprintf(
				"%s in %s line %d trace:\n%s",
				$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
			);
			\Yii::error($message, __METHOD__);
			return app()->helper->arrayResult($this->codeFail, $e->getMessage(), null, $validPlatformParams);
		}
	}

	/**
	 * 三端合一, 编辑子页面
	 *
	 * @param array $params post参数
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @since v1.6.0
	 */
	public function groupEdit (array $params)
	{
		$nowTime = time();

		//公共数据检查
		if (empty($params['page_id']) || !is_numeric($params['page_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的子页面ID');
		}

		if (empty($params['title'])) {
			throw new JsonResponseException($this->codeFail, '名称为必填项');
		}

		if (empty($params['end_time'])) {
			throw new JsonResponseException($this->codeFail, '请选择下线时间');
		}

		if (($params['end_time'] > 0) && ($params['end_time'] <= $nowTime)) {
			throw new JsonResponseException($this->codeFail, '下线时间无效');
		}

		if (empty($params['platforms'])) {
			throw new JsonResponseException($this->codeFail, '没有选择应用端口');
		}

		if (empty($params['lang_list'])) {
			throw new JsonResponseException($this->codeFail, '没有语言数据');
		}

		if (empty($params['version'])) {
			throw new JsonResponseException($this->codeFail, '没有数据版本');
		}

		$postLangDataList = json_decode($params['lang_list'], true);
		if (!is_array($postLangDataList) || empty($postLangDataList)) {
			throw new JsonResponseException($this->codeFail, '无效的提交数据');
		}

		$isRedirectCountry = $params['is_redirect_country'] ?? 0; // 是否国家站自动跳转

		//检查活动是否存在
		/** @var \app\modules\common\zf\models\PageModel $pageModel */
		$pageModel = PageModel::findOne([
			'id'        => $params['page_id'],
			'is_delete' => PageModel::NOT_DELETE
		]);
		if (!$pageModel) {
			throw new JsonResponseException($this->codeFail, '子页面不存在');
		}
		if ($pageModel->version < $params['version']) {
			throw new JsonResponseException($this->codeFail, '非法数据版本');
		}
		if ($pageModel->version > $params['version']) {
			throw new JsonResponseException($this->codeFail, '页面数据已过期,页面需刷新');
		}
		$is_blog = $pageModel->is_blog;

		/** @var \app\modules\common\zf\models\ActivityModel $activityModel */
		$activityModel = ActivityModel::find()
			->where([
				'id'        => $pageModel->activity_id,
				'is_delete' => ActivityModel::NOT_DELETE
			])->one();

		$validPlatformParams = $this->checkAndBuildData($activityModel, $params['page_id'], $params, $postLangDataList);
		unset($postLangDataList);

		if (empty($validPlatformParams)) {
			throw new JsonResponseException($this->codeFail, '无效的提交数据');
		}

		//事物开始
		$transaction = app()->db->beginTransaction();
		try {
			//保存数据
			foreach ($validPlatformParams as $platformCode => $platformParams) {
				if (empty($platformParams)) {
					continue;
				}

				foreach ($platformParams as $pipeline => $pipelineParams) {
					if (empty($pipelineParams['list']) || empty($pipelineParams['page_model'])) {
						continue;
					}

					$this->beforeSaveProcessor($validPlatformParams, $pipelineParams, $pipeline);
					//保存子页面数据
					/** @var \app\modules\common\zf\models\PageModel $pageModel */
					$pageModel = $pipelineParams['page_model'];
					unset($pipelineParams['page_model']);
					$pipelineParams['platform_code'] = strtolower($platformCode);

					$pageModel->end_time = $pipelineParams['end_time'];
					$pageModel->version  = $pipelineParams['version'] + 1;
					$pageModel->pipeline = $pipeline;
					$pageModel->is_redirect_country = $isRedirectCountry;
					$pageModel->is_blog = $is_blog;

					$result = $this->doActivityBatchEdit($pageModel, $pipelineParams);
					if ($result['code'] == $this->codeFail) {
						$errorMessage = sprintf(
							"应用端口 %s 修改失败: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']
						);
						throw new Exception($errorMessage);
					}

					//修改子活动信息需要同步到IPS
					$page_data = current($platformParams);
					$sync_data['geshop_activity_id'] = $page_data['activity_id'];
					$sync_data['geshop_activity_child_id'] = $pageModel->id;
					$sync_data['geshop_activity_child_name'] = current($page_data["list"])["title"] . "_" . $pipeline;
					$sync_data['geshop_offline_time'] = $page_data['end_time'];
					\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);
				}
			}

			$transaction->commit();
			return app()->helper->arrayResult($this->codeSuccess, '修改成功');
		} catch (\Exception $e) {
			$transaction->rollBack();
			$message = sprintf(
				"%s in %s line %d trace:\n%s",
				$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
			);
			\Yii::error($message, __METHOD__);
			return app()->helper->arrayResult($this->codeFail, $e->getMessage(), null, $validPlatformParams);
		}
	}

	/**
	 * 子页面数据保存前处理
	 *
	 * @param array  $validPlatformParams
	 * @param array  $platformParams
	 * @param string $pipeline
	 */
	private function beforeSaveProcessor ($validPlatformParams, &$platformParams, $pipeline = '')
	{
		//关联PC跳M链接(redirect_url)
		if (SitePlatform::isPcPlatform($platformParams['site_code'])
			&& isset($validPlatformParams[SitePlatform::PLATFORM_CODE_WAP][$pipeline])) {
			$wapSiteCode = $validPlatformParams[SitePlatform::PLATFORM_CODE_WAP][$pipeline]['site_code'];
			$pipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($wapSiteCode);
			$siteGroup = explode('-', $platformParams['site_code'])[0];
			$pipelineDefaultLang = app()->params['site'][$siteGroup]['pipeline_default_lang'][$pipeline];
			foreach ($platformParams['list'] as $lang => $langData) {
				if (isset($platformParams['list'][$lang]) && empty($platformParams['list'][$lang]['redirect_url'])) {
					$mobileActivityUrl = '';
					if (isset($pipelineList[$pipeline][$lang])
						&& isset($validPlatformParams[SitePlatform::PLATFORM_CODE_WAP][$pipeline]['list'][$lang])) {
						$mobileActivityUrl = trim($pipelineList[$pipeline][$lang], '/') . '/';
						$mobileActivityUrl .= $validPlatformParams[SitePlatform::PLATFORM_CODE_WAP][$pipeline]['list'][$lang]['url_name'];
						$mobileActivityUrl .= '.html';
					}

					$platformParams['list'][$lang]['redirect_url'] = $mobileActivityUrl . ($pipelineDefaultLang === $lang ? '' : '?lang=' . $lang);
				}
			}
		}

		//APP端没有活动结束跳转链接(end_url)
		if (SitePlatform::isAppPlatform($platformParams['site_code'])) {
			foreach ($platformParams['list'] as $lang => $langData) {
				$platformParams['list'][$lang]['end_url'] = '';
			}
		}
	}

	/**
	 * 添加和编辑公共函数
	 *
	 * @param \app\modules\common\models\ActivityModel $activityModel
	 * @param int                                      $pageId 0走添加逻辑,大于0走编辑逻辑
	 * @param array                                    $params
	 * @param array                                    $postLangDataList
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	private function checkAndBuildData ($activityModel, $pageId, $params, $postLangDataList)
	{
		$isModify = $pageId > 0; //是否编辑
		$activityGroupId = $activityModel->group_id;
		$supportPlatformList = NULL;
		$allSupportLangList = NULL;
		$platformSupportLangList = NULL;
		$groupActivityModelMap = [];
		$pageGroupInfoMap = [];

		//大于0表示有多个端口，0单端口
		if ($activityGroupId > 0) {
			/** @var \app\modules\common\zf\models\ActivityGroupModel $activityGroupModel */
			$activityGroupModel = ActivityGroupModel::getById($activityGroupId);
			$supportPlatformList = explode(SiteConstants::CHAR_COMMA, $activityGroupModel->platform_list);
			$allSupportLangList = json_decode($activityGroupModel->lang_list, true);
			$platformSupportLangList = json_decode($activityGroupModel->support_list, true);
			$activityModelList = ActivityModel::getActivitiesByGroupId($activityGroupId);
			if (count($supportPlatformList) != count($activityModelList)) {
				throw new JsonResponseException($this->codeFail, '活动组数据不一致');
			}

			/** @var \app\modules\common\models\ActivityModel $activityModel */
			foreach ($activityModelList as $_activityModel) {
				$platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
				$groupActivityModelMap[$platformCode] = $_activityModel;
			}

			//原生专题新增需要验证M与APP端的渠道,语言保持完全一致  2020-06-20
			/*if ($params['is_native_theme'] == 1 && isset($groupActivityModelMap['wap']) && isset($groupActivityModelMap['app'])) {
				if ($groupActivityModelMap['wap']->lang !== $groupActivityModelMap['app']->lang) {
					throw new JsonResponseException($this->codeFail, '原生专题移动端语言需保持一致');
				}
			}*/

			//编辑模式
			if ($isModify) {
				$pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageId);
				if ($pageGroupId) {
					$pageGroupModelList = PageGroupModel::getPageListByPageGroupId($pageGroupId);
					$pageGroupInfoList = ArrayHelper::toArray($pageGroupModelList);
					foreach ($pageGroupInfoList as $pageGroupInfo) {
						$_code = SitePlatform::getPlatformCodeByPlatformType($pageGroupInfo['platform_type']);
						$pageGroupInfoMap[$_code][$pageGroupInfo['pipeline']] = $pageGroupInfo;
					}
				}
			}

		} else {
			$platformCode = SitePlatform::getPlatformCodeBySiteCode($activityModel->site_code);
			$supportPlatformList = [$platformCode];
			$allSupportLangList = json_decode($activityModel->lang,true);
			$platformSupportLangList = [$platformCode => $allSupportLangList];
			$groupActivityModelMap[$platformCode] = $activityModel;

			//编辑模式
			if ($isModify) {
				$pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageId);
				if($pageGroupId) {
					$pageGroupModelList = PageGroupModel::getPageListByPageGroupId($pageGroupId);
					$pageGroupInfoList = ArrayHelper::toArray($pageGroupModelList);
					foreach ($pageGroupInfoList as $pageGroupInfo) {
						$_code = SitePlatform::getPlatformCodeByPlatformType($pageGroupInfo['platform_type']);
						$pageGroupInfoMap[$_code][$pageGroupInfo['pipeline']] = $pageGroupInfo;
					}
				}
			}
		}

		//组装数据
		$allPlatformParams = [];
		foreach ($supportPlatformList as $platformCode) {
			if (strpos($params['platforms'], $platformCode) === false) {
				continue;
			}

			$_supportLangList = $platformSupportLangList[$platformCode];
			$group_id = md5(microtime() . random_int(0, 100));
			$_activityModel = $groupActivityModelMap[$platformCode];
			foreach ($_supportLangList as $pipelineCode => $_langList) {
				if (empty($pageGroupInfoMap[$platformCode][$pipelineCode]) && $isModify) {
					continue;
				}

				$allPlatformParams[$platformCode][$pipelineCode] = [
					'site_code'    => SitePlatform::getSiteCodeByPlatformCode($platformCode),
					'activity_id'  => $_activityModel->id,
					'page_id'      => $isModify ? $pageGroupInfoMap[$platformCode][$pipelineCode]['page_id'] : 0,
					'refresh_time' => 0,
					'end_time'     => !empty($params['end_time']) ? $params['end_time'] : 0,
					'version'      => !empty($params['version']) ? $params['version'] : 1,
					'group_id'     => $group_id,
					'list'         => []
				];
			}
			unset($_supportLangList);
		}

		$langDataFieldName = [
			'title', 'url_name', 'seo_title', 'keywords', 'description', 'statistics_code',
			'redirect_url', 'share_image', 'share_title', 'share_desc', 'share_link', 'share_place'
		];
		foreach ($postLangDataList as $pipelineCode => $pipelineData) {
			//剔除不支持的渠道
			if (!isset($allSupportLangList[$pipelineCode])) {
				continue;
			}

			$defaultLangCode = $pipelineData['default_lang'];
			foreach ($pipelineData['languages'] as $langCode => $langData) {
				//剔除不支持的语言
				if (!in_array($langCode, $allSupportLangList[$pipelineCode], true)) {
					continue;
				}

				foreach ($supportPlatformList as $platformCode) {
					if (empty($allPlatformParams[$platformCode][$pipelineCode]) && $isModify) {
						continue;
					}

					// 专题活动名称从公共参数里取
					$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode]['title'] = $params['title'];
					$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode]['default'] = $langCode == $defaultLangCode ? 1 : 0;
					$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode]['pipeline'] = $pipelineCode;
					foreach ($langData as $key => $value) {
						if (in_array($key, $langDataFieldName, true)) {
							$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode][$key] =
								$value ?? $postLangDataList[$pipelineCode]['languages'][$defaultLangCode][$key];

							$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode][$key] =
								is_array($allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode][$key]) ?
									json_encode($allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode][$key]) :
									$allPlatformParams[$platformCode][$pipelineCode]['list'][$langCode][$key];
						}
					}
				}

				foreach ($langData['platform'] as $langPlatformCode => $langPlatformData) {
					if (empty($allPlatformParams[$langPlatformCode][$pipelineCode]) && $isModify) {
						continue;
					}

					$allPlatformParams[$langPlatformCode][$pipelineCode]['list'][$langCode]['end_url'] = $langPlatformData['end_url'] ?? '';
					if (0 === (int)$pageId) {
						$allPlatformParams[$langPlatformCode][$pipelineCode]['list'][$langCode]['tpl_id'] = $langPlatformData['tpl_id'] ?? 0;
					}
				}
			}
		}
		unset($postLangDataList);

		// 验证数据
		$validPlatformParams = [];
		foreach ($allPlatformParams as $platformCode => $platformParams) {
			// 剔除不支持的端口
			if (!in_array($platformCode, $supportPlatformList)) {
				continue;
			}

			$siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
			$configSitePipeline = app()->params['sites'][$siteCode]['secondary_domain'] ?? [];
			/** @var \app\modules\common\zf\models\ActivityModel $_activityModel */
			$_activityModel = $groupActivityModelMap[$platformCode];
			$activitySupportPipelineKeys = explode(SiteConstants::CHAR_COMMA, $_activityModel->pipeline);

			// 处理端下渠道信息
			foreach ($platformParams as $pipelineCode => $pipelineInfo) {

				// 剔除活动不支持的渠道
				if (!in_array($pipelineCode, $activitySupportPipelineKeys, true)) {
					unset($platformParams[$pipelineCode]);
					continue;
				}

				// ----检查参数合法性-----
				if (!\in_array($pipelineInfo['refresh_time'], array_column($this->refreshList, 'key'), true)) {
					$errorMsg = sprintf("应用端口 %s refresh_time不符合规范", SitePlatform::getPlatformNameByCode($platformCode));
					throw new JsonResponseException($this->codeFail, $errorMsg);
				}

				// 处理渠道下语言信息
				foreach ($pipelineInfo['list'] as $langCode => $pageLangInfo) {
					// 剔除配置不支持的语言
					if (!isset($configSitePipeline[$pipelineCode][$langCode])) {
						unset($platformParams[$pipelineCode]['list'][$langCode]);
						unset($pipelineInfo['list'][$langCode]);
						continue;
					}

					// 剔除活动不支持的语言
					if (!in_array($langCode, $platformSupportLangList[$platformCode][$pipelineCode])) {
						unset($platformParams[$pipelineCode]['list'][$langCode]);
						unset($pipelineInfo['list'][$langCode]);
						continue;
					}

					if (empty($pageLangInfo['title'])) {
						$errorMsg = sprintf("应用端口 %s 名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
						throw new JsonResponseException($this->codeFail, $errorMsg);
					}

					if (empty($pageLangInfo['seo_title'])) {
						$errorMsg = sprintf("应用端口 %s SEO标题为必填项", SitePlatform::getPlatformNameByCode($platformCode));
						throw new JsonResponseException($this->codeFail, $errorMsg);
					}

					if (empty($pageLangInfo['url_name'])) {
						$errorMsg = sprintf("应用端口 %s URL名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
						throw new JsonResponseException($this->codeFail, $errorMsg);
					}

					$platformParams[$pipelineCode]['list'][$langCode]['group_id'] = $pipelineInfo['group_id'];
				}

				// 忽略删除的活动
				$_activityModel = ActivityModel::findOne($pipelineInfo['activity_id']);
				if ($_activityModel->is_delete == ActivityModel::IS_DELETE) {
					continue;
				}

				try {
					$checkResult = $this->checkBatchPageData($pipelineInfo['activity_id'], $pipelineInfo['page_id'], $pipelineInfo['list'], $pipelineCode);
					$platformParams[$pipelineCode]['page_model'] = $checkResult['page'];
				} catch (JsonResponseException $e) {
					$errorMsg = sprintf("应用端口 %s %s", SitePlatform::getPlatformNameByCode($platformCode), $e->getMessage());
					throw new JsonResponseException($this->codeFail, $errorMsg);
				}
			}

			// 通过验证，要保存的数据
			$validPlatformParams[$platformCode] = $platformParams;
		}

		return $validPlatformParams;
	}

	/**
	 * 删除自定义页面
	 *
	 * @param int  $id
	 * @param bool $runValidation
	 *
	 * @return array
	 */
	public function delete ($id, $runValidation = true)
	{
		$model = PageModel::getById($id);
		if (!$model) {
			return app()->helper->arrayResult(1, '自定义页面不存在');
		}

		//检查活动是否加锁，并判断权限
		$activityInfo = ActivityModel::getActivityInfo($model->activity_id);
		if (false === ActivityModel::checkAuth($activityInfo)) {
			return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
		}

		// 先判断是否在线
		if ($model->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
			return app()->helper->arrayResult(1, '页面仍在线，请先做下线处理');
		}


		return $this->doDelete($model, $runValidation);
	}

	/**
	 * 获取多个页面下第一个Banner组件的图片地址，没有使用默认图片
	 *
	 * @param array  $pageIds  子页面id数组
	 * @param string $siteCode 站点简码
	 * @param string $lang     语言简码
	 *
	 * @return array
	 */
	private function getPageFirstBannerPicUrls ($pageIds, $siteCode, $lang)
	{
		//查询多个页面所有布局
		$pagesLayoutList = PageLayoutModel::find()
			->where(['page_id' => $pageIds, 'lang' => $lang])
			->indexBy('id')
			->asArray()
			->all();

		$pageBannerUrls = [];
		$pageLayoutPositionUiList = [];
		if (!empty($pagesLayoutList)) {
			$layoutIds = array_keys($pagesLayoutList);
			$bannerComponentIds = [];
			$uiComponentKey = $this->getSiteBannerUiComponetKeyBySiteCode($siteCode);

			//查询布局下的UI组件，按照position排序
			$uiList = PageUiModel::find()
				->where(['layout_id' => $layoutIds, 'lang' => $lang])
				->orderBy('position asc')
				->asArray()
				->all();

			if (!empty($uiList)) {
				//按页面分组并排序layout组件
				$pageOrderedLayoutList = [];
				foreach ($pagesLayoutList as $_layout) {
					$pageOrderedLayoutList[$_layout['page_id']][] = $_layout;
				}

				foreach ($pageOrderedLayoutList as $_pageId => $_layoutList) {
					$_orderedLayoutList = $this->getOrderedComponents($_layoutList);
					foreach ($_orderedLayoutList as $_layout) {
						$pageLayoutPositionUiList[$_pageId][$_layout['id']] = [];
					}
				}

				//按页面组件及栏位置分组UI组件
				foreach ($uiList as $ui) {
					$pageId = $pagesLayoutList[$ui['layout_id']]['page_id'];
					$bannerComponentIds[] = $ui['id'];
					$pageLayoutPositionUiList[$pageId][$ui['layout_id']][$ui['position']][] = $ui;
				}

				//查询对应的组件imgSrc数据
				$uiDataList = PageUiComponentDataModel::find()
					->select('component_id, value')
					->where(['component_id' => $bannerComponentIds, 'key' => 'imgSrc'])
					->asArray()
					->all();

				$uiDataMap = array_column($uiDataList, 'value', 'component_id');

				//排序并查找banner组件
				foreach ($pageLayoutPositionUiList as $_pageId => $_orderedLayoutList) {
					foreach ($_orderedLayoutList as $_layoutId => $_positionList) {
						foreach ($_positionList as $_uiList) {
							$_orderedUiList = $this->getOrderedComponents($_uiList);
							foreach ($_orderedUiList as $_ui) {
								if (($uiComponentKey == $_ui['component_key']) && !empty($uiDataMap[$_ui['id']])) {
									$_picUrl = json_decode($uiDataMap[$_ui['id']]);
									if (!empty($_picUrl)) {
										$pageBannerUrls[$_pageId] = $_picUrl;
										break 3;
									}
								}
							}
						}
					}
				}
			}


		}

		foreach ($pageIds as $_pageId) {
			if (empty($pageBannerUrls[$_pageId])) {
				$pageBannerUrls[$_pageId] = static::PAGE_DEFAULT_PREVIEW_PIC_URL;
			}
		}

		return $pageBannerUrls;
	}

	/**
	 * 删除渠道下面所有子页面
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function delPipelineAllPages ($params)
	{
		if (empty($params['activity_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的活动ID');
		}

		if (empty($params['group_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的渠道组ID');
		}

		$pageRowList = PageModel::find()->where([
			'is_delete'   => PageModel::NOT_DELETE,
			'activity_id' => $params['activity_id'],
			'group_id'    => $params['group_id'],
		])->asArray()->all();
		if (empty($pageRowList)) {
			return app()->helper->arrayResult($this->codeFail, '页面不存在');
		}

		//检查活动是否加锁，并判断权限
		$activityInfo = ActivityModel::getActivityInfo($pageRowList[0]['activity_id']);
		if (false === ActivityModel::checkAuth($activityInfo)) {
			return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
		}

		foreach ($pageRowList as $pageRow) {
			if ((int)$pageRow['status'] === PageModel::PAGE_STATUS_HAS_ONLINE) {
				return app()->helper->arrayResult($this->codeFail, '当前渠道下有页面仍在线，请先做下线处理!');
			}
		}

		$rows = PageModel::updateAll(
			['is_delete' => PageModel::IS_DELETE],
			[
				'activity_id'   => $params['activity_id'],
				'group_id'      => $params['group_id'],
			]
		);

		if (0 == $rows) {
			return app()->helper->arrayResult($this->codeFail, '删除失败');
		}

		if (!empty($pageRowList[0]['is_native'])) {
			$nativePageGroup = PageGroupModel::find()
				->select('page_group_id')
				->where(
					[
						'activity_group_id' => $activityInfo['group_id'],
						'platform_type'     => $activityInfo['type'],
						'page_id'           => $pageRowList[0]['id']
					]
				)
				->asArray()
				->one();
			$nativeType = (Platform::WAP == $activityInfo['type']) ? Platform::IOS : Platform::WAP;
			$nativePages = PageGroupModel::find()
				->select('page_id')
				->where(['page_group_id' => $nativePageGroup['page_group_id'], 'platform_type' => $nativeType])
				->asArray()
				->all();
			$nativePages = array_column($nativePages, 'page_id');
			$rows = PageModel::updateAll(
				['is_delete' => PageModel::IS_DELETE],
				['in', 'id', $nativePages]
			);
			if (0 == $rows) {
				return app()->helper->arrayResult($this->codeFail, '删除失败');
			}
		}


		//同步删除IPS子活动分组
		$sync_data['geshop_activity_id'] = $params['activity_id'];
		foreach ($pageRowList as $page_row) {
			$sync_data['del_info'][] = [
				'geshop_activity_child_id' => $page_row['id']
			];
		}
		\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);


		return app()->helper->arrayResult($this->codeSuccess, '删除成功');
	}

	/**
	 * 获取所有渠道最新访问链接地址
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getPipelineNewestUrls ($params)
	{
		if (empty($params['activity_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的活动ID');
		}

		if (empty($params['group_id'])) {
			throw new JsonResponseException($this->codeFail, '无效的渠道组ID');
		}

		$activityInfo = ActivityModel::getActivityInfo($params['activity_id']);
		if (empty($activityInfo)) {
			throw new JsonResponseException($this->codeFail, '活动不存在');
		}

		$needBtn = empty($params['btn']) ? 1 : (int)$params['btn'];
		$pageRowList = PageModel::find()->where([
			'is_delete'   => PageModel::NOT_DELETE,
			'activity_id' => $params['activity_id'],
			'group_id'    => $params['group_id'],
		])->asArray()->all();
		if (empty($pageRowList)) {
			throw new JsonResponseException($this->codeFail, '没有有效的活动页面');
		}
		if (SitePlatform::isAppPlatform($pageRowList[0]['site_code']) && !empty($pageRowList[0]['is_native'])) {
			$nativePageId =PageModel::getNativeAppPageId(
				$pageRowList[0]['id'], $pageRowList[0]['site_code'], $pageRowList[0]['pipeline'], false
			);
			$nativePage = PageModel::getById($nativePageId);
			$activityInfo = ActivityModel::getActivityInfo($nativePage->activity_id);
			if (empty($activityInfo)) {
				throw new JsonResponseException($this->codeFail, '活动不存在');
			}
			$pageRowList = PageModel::find()->where([
				'is_delete'   => PageModel::NOT_DELETE,
				'activity_id' => $nativePage->activity_id,
				'group_id'    => $nativePage->group_id,
			])->asArray()->all();
			if (empty($pageRowList)) {
				throw new JsonResponseException($this->codeFail, '没有有效的活动页面');
			}
		}
		$siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
		$configAllPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($siteGroupCode);

		$pipelineUrls = [];
		$missLangList = [];
		foreach ($pageRowList as $pageRow) {
			$pipelineCode = $pageRow['pipeline'];
			$pipelineName = $configAllPipelineList[$pipelineCode] ?? '';

			try {
				$pageStatus = (int)$pageRow['status'];
				$publishedStatus = [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_OFFLINE];
				if (in_array($pageStatus, $publishedStatus, true)) {
					$result = $this->getPageNewestUrls($pageRow['id'], $needBtn);
					if (!empty($result['data']['list'])) {
						$pipelineUrls['pipeline_list'][] = [
							'code'      => $pageRow['pipeline'],
							'name'      => $pipelineName,
							'lang_list' => $result['data']['list']
						];
					}

					if (!empty($result['data']['miss'])) {
						foreach ($result['data']['miss'] as $langName) {
							$missLangList[] = $pipelineName . '-' . $langName;
						}
					}
				}

			} catch (\Exception $e) {
				\Yii::error('获取活动链接错误：' . $e->getTraceAsString());

				if (isset($activityInfo['langList'][$pipelineCode])) {
					foreach ($activityInfo['langList'][$pipelineCode] as $_langInfo) {
						$missLangList[] = $pipelineName . '-' . $_langInfo['name'];
					}
				}
			}
		}

		if (!empty($missLangList)) {
			$pipelineUrls['tips'] = implode('、', $missLangList) . '页面还在推送中' . ($needBtn ? '，若长时间未成功，请' : '');
		}

		if (empty($pipelineUrls)) {
			throw new JsonResponseException($this->codeFail, '页面还未上线或下线过，无访问链接');
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $pipelineUrls);
	}


	/**
	 * 检查页面是否发布过
	 *
	 * @param PageModel $pageModel
	 * @param array     $data
	 *
	 * @throws JsonResponseException
	 */
	public function checkPagePublished ($pageModel, &$data)
	{
		if (!$pageModel->activity_id) {
			throw new JsonResponseException($this->codeFail, '首页请勿调用活动页接口', $data);
		}

		if ($pageModel->status !== PageModel::PAGE_STATUS_HAS_ONLINE
			&& $pageModel->status !== PageModel::PAGE_STATUS_HAS_OFFLINE) {
			throw new JsonResponseException($this->codeFail, '页面还未上线或下线过，无访问链接', $data);
		}
	}

	/**
	 * 根据站点简码获取Banner组件key
	 *
	 * @param string $siteCode
	 *
	 * @return string UI组件key，NULL如果没有对应站点的组件
	 */
	private function getSiteBannerUiComponetKeyBySiteCode ($siteCode)
	{
		$platformCode = SitePlatform::getPlatformCodeBySiteCode($siteCode);
		if (empty($platformCode))
			return NULL;

		return static::BANNER_UI_COMPONENT_KEY[$platformCode] ?? NULL;
	}

	/**
	 * 获取domainKey
	 */
	public function getDomainKey ()
	{
		return 'secondary_domain';
	}

	/**
	 * 获取页面和活动信息
	 *
	 * @param int $pageId
	 *
	 * @return PageModel|array|null
	 */
	public function getPageActivityInfo (int $pageId)
	{
		return PageModel::getPageActivityInfo($pageId);
	}

	/**
	 * 获取操作日志列表
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @author yuanwenguang 2019/4/1 14:33
	 */
	public function getOperateLogList ($params = [])
	{
		// 参数校验
		$rules = [
			[['page_id'], 'required'],
		];
		$model = app()->validatorModel->new($rules)->load($params);
		if (false === $model->validate()) {
			throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
		}
		$params['pageNo'] = empty($params['pageNo']) ? 1 : $params['pageNo'];
		$params['pageSize'] = empty($params['pageSize']) ? 10 : $params['pageSize'];

		//获取操作列表
		$page_group_id = PageGroupModel::getPageGroupIdByPageId($params['page_id']);

		$list = PageOperateLogModel::find()
			->where(['page_group_id' => $page_group_id]);
		$total = $list->count();
		$page_operate_logs = $list->with("user")
			->orderBy(['create_time' => SORT_DESC])
			->limit($params['pageSize'])
			->offset(($params['pageNo'] - 1) * $params['pageSize'])
			->asArray()
			->all();

		//解析content，返回前端
		foreach ($page_operate_logs as $k => &$page_operate_log) {
			$content = json_decode($page_operate_log['content'], 1);
			$page_operate_log['content'] = $content;

			//端口
			$platform = $content['platform'];
			$platform_operate = [];
			foreach ($platform as $platform_item) {
				$platform_operate[] = (!empty(PageOperateLogModel::$user_type[$platform_item['operate']]) ? PageOperateLogModel::$user_type[$platform_item['operate']] : '') . "" . SitePlatform::getPlatformNameByCode($platform_item['data']);
			}
			$platform_operate = implode('、', $platform_operate);

			//渠道
			$pipeline = $content['pipeline'];
			$pipeline_operate = [];
			foreach ($pipeline as $pipeline_item) {
				$pipeline_operate[] = (!empty(PageOperateLogModel::$user_type[$pipeline_item['operate']]) ? PageOperateLogModel::$user_type[$pipeline_item['operate']] : '') . "" . app()->params['site']['zf']['pipeline'][$pipeline_item['data']];
			}
			$pipeline_operate = implode('、', $pipeline_operate);

			//组件数据
			$form_data = $content['form_data'];
			$form_data_operate = [];
			if (!empty($form_data)) {
				foreach ($form_data as $form_data_item) {
					$ui_id = $form_data_item['single_ui_id']; // pc端的ui_id
					//找到模板
					$page_ui = PageUiModel::findOne($ui_id);

					if (!empty($page_ui)) {
						$tpl_id = $page_ui->tpl_id;
						$ui_tpl = UiTplModel::findOne($tpl_id);
						$ui = UiModel::findOne(['component_key' => $ui_tpl->component_key]);
						$form_data_operate[] = $ui->name . "-" . $ui_tpl->name;
					}
				}
			} else {
				unset($page_operate_logs[$k]);
			}

			$form_data_operate = implode('、', $form_data_operate);
			$page_operate_log['type'] = PageOperateLogModel::$operateType[$page_operate_log['type']];
			$page_operate_log['content'] = "端口：" . $platform_operate . "；渠道：" . $pipeline_operate . "；组件：" . $form_data_operate;
			unset($page_operate_log['create_user']);
			unset($page_operate_log['update_user']);
			unset($page_operate_log['update_time']);

		}
		$data['list'] = $page_operate_logs;
		$data['pagination'] = [
			'pageNo'     => $params['pageNo'],
			'pageSize'   => (int)$params['pageSize'],
			'totalCount' => (int)$total
		];

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 获取原生专题页面所有的deeplink链接
	 *
	 * @param string $pageId
	 * @param string $groupId
	 * @param string $siteCode
	 *
	 * @return array
	 */
	public function getNativeDeepLinks (int $pageId, string $groupId, string $siteCode)
	{
		$deepLinks = [];
		$pages = PageModel::getLanguageNativePages($groupId);
		if (!empty($pages)) {
			$pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageId);
			$langConf = app()->params['lang'];
			$allDefaultLang = config('site.zf.pipeline_default_lang');
			$configAllPipelineList = PipelineUtils::getConfigAllPipelineList($siteCode);
			$pages = ArrayHelper::toArray($pages);
			foreach ($pages as $item) {
				$nativeId = SitePlatform::isAppPlatform($siteCode)
					? $item['page_id']
					: PageModel::getNativeAppPageId($item['page_id'], $siteCode, $item['pipeline']);
				$deepLinks[] = [
					'id'            => $nativeId,
					'group_id'      => $pageGroupId,
					'pipeline_lang' => $configAllPipelineList[$item['pipeline']] . '-' . $langConf[$item['lang']]['name'],
					'deep_link'     => "zaful://action?actiontype=29&url={$nativeId}&source=deepLink",
					'is_default'    => empty($allDefaultLang[$item['pipeline']]) ? 1 : intval($allDefaultLang[$item['pipeline']] == $item['lang'])
				];
			}
			unset($pages);
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $deepLinks);
	}

	/**
	 * 获取H5专题页面所有的页面ID
	 *
	 * @param string $pageId
	 * @param string $groupId
	 * @param string $siteCode
	 *
	 * @return array
	 */
	public function getWebPageIds (string $groupId, string $siteCode)
	{
		$Links = [];
		$isNative = 0;//初始换活动类型
		$pages = PageModel::getLanguageNativePages($groupId, $isNative);
		if (!empty($pages)) {
			$langConf = app()->params['lang'];
			$allDefaultLang = config('site.zf.pipeline_default_lang');
			$configAllPipelineList = PipelineUtils::getConfigAllPipelineList($siteCode);
			$pages = ArrayHelper::toArray($pages);
			foreach ($pages as $item) {
				$Links[] = [
					'id'            => $item['page_id'],
					'pipeline_lang' => $configAllPipelineList[$item['pipeline']] . '-' . $langConf[$item['lang']]['name'],
					'is_default'    => empty($allDefaultLang[$item['pipeline']]) ? 1 : intval($allDefaultLang[$item['pipeline']] == $item['lang'])
				];
			}
			unset($pages);
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $Links);
	}
}
