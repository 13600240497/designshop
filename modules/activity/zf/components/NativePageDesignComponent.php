<?php


namespace app\modules\activity\zf\components;


use app\base\RequestUtils;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\base\NativeConstants;
use app\components\auto\AutoRefreshPage;
use app\modules\base\components\{
	AccessLogComponent, LanguageDataComponent, AdminSitePrivilegeComponent
};
use app\modules\common\models\{
	NativePageLayoutModel, NativePageUiComponentModel
};
use app\modules\common\zf\components\CommonPageDesignComponent;
use app\modules\common\zf\models\{
	ActivityGroupModel, ActivityModel, PageGroupModel, PageLanguageModel, PageModel
};
use app\modules\component\zf\components\{
	ExplainComponent, ExplainTplComponent
};
use app\modules\base\models\AdminModel;
use ego\base\JsonResponseException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 页面装修设计-整个页面相关
 *
 * @property \app\modules\activity\zf\components\PageComponent                $PageComponent
 * @property \app\modules\activity\zf\components\NativePagePrivilegeComponent $NativePagePrivilegeComponent
 * @property \app\modules\activity\zf\components\NativePageFallbackComponent $NativePageFallbackComponent
 */
class NativePageDesignComponent extends CommonPageDesignComponent
{

	/**
	 * 活动APP原生页面设计
	 *
	 * @param $group_id
	 * @param $lang
	 * @param $pipeline
	 *
	 * @return array|string
	 * @throws \Throwable
	 * @throws \app\components\auto\AutoRefreshException
	 * @throws \ego\base\JsonResponseException
	 */
	public function getIndexData ($group_id, $lang, $pipeline)
	{
		/** @var PageModel $pageModel */
		$pageModel = PageModel::getByGroupId($group_id, $pipeline);

		if (!$pageModel) {
			throw new JsonResponseException($this->codeFail, '页面不存在或已被删除');
		}

		$pageId = $pageModel->id;
		$activityInfo = ActivityModel::getNativeActivityInfo($pageModel->activity_id, $group_id);
		// 检查活动是否加锁，并判断权限
		if (false === ActivityModel::checkAuth($activityInfo)) {
			throw new JsonResponseException($this->codeFail, '只有活动创建者才具有此权限');
		}

		if (empty($activityInfo['langList'])) {
			throw new JsonResponseException($this->codeFail, '活动还未设置语言选项');
		}

		// 页面不允许多人同时编辑，用户进入装修页面会持有锁，防止除当前用户以为的其他用户编辑
		$userId = app()->user->admin->id;
		$websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($activityInfo['site_code']);
		$lockPageIds = $this->NativePagePrivilegeComponent->getLockPageIds($pageModel);
		if (!$this->NativePagePrivilegeComponent->lockNativePageDesign($websiteCode, $lockPageIds, $userId)) {
			$format = '账号"%s"正在装修，不支持多人同时装修，账号"%s”关闭装修页面%d分钟后即可装修';
			$lockUserId = $this->NativePagePrivilegeComponent->getCurrentDesignUserId($websiteCode, $lockPageIds);
			$adminModel = AdminModel::getById($lockUserId);
			$_seconds = (int)(NativePagePrivilegeComponent::LOCK_EXPIRE_TIME / 60);
			$_message = sprintf($format, $adminModel->username, $adminModel->username, $_seconds);
			throw new JsonResponseException($this->codeFail, $_message);
		}

		$activityInfo['langList'] = $activityInfo['langList'][$pipeline];
		$activityInfo['allLangList'] = ActivityModel::getPipelineAndLang($activityInfo['lang'], $activityInfo['site_code']);

		$activityGroupInfo = ActivityModel::getActivitiesByGroupId($activityInfo['group_id']);
		$activityGroupPipeline = array_column($activityGroupInfo, 'pipeline', 'site_code');
		foreach ($activityGroupPipeline as $key => &$value) {
			$value = explode(',', $value);
			if (SitePlatform::PLATFORM_TYPE_PC == SitePlatform::getPlatformTypeBySiteCode($key)) {
				unset($activityGroupPipeline[$key]);
			}
		}
		if (count($activityGroupPipeline) > 1) {
			list($array1, $array2) = array_values($activityGroupPipeline);
			$activityPipelineList = array_unique(array_merge($array1, $array2));
		} else {
			$activityPipelineList = array_unique(current(array_values($activityGroupPipeline)));
		}

		// 过滤没有权限的渠道
		$privilegeComponent = new AdminSitePrivilegeComponent();
		$hasPermissions = $privilegeComponent->getCurrentUserValidSiteSpecialPermissions($activityInfo['site_code'], $activityPipelineList);
		if (empty($hasPermissions) || !in_array($pipeline, $hasPermissions, true)) {
			throw new JsonResponseException($this->codeFail, '没有渠道装修权限！');
		}
		$filterPipelineList = [];
		foreach ($activityInfo['allLangList'] as $_pipelineInfo) {
			if (!in_array($_pipelineInfo['pipeline'], $hasPermissions))
				continue;

			$filterPipelineList[] = $_pipelineInfo;
		}
		$activityInfo['allLangList'] = $filterPipelineList;

		// 没有参数lang,默认一个可装修语言
		$pageSupportLangList = array_column($activityInfo['langList'], 'key');
		if (empty($lang)) {
			if (isset($pageSupportLangList[$pageModel->default_lang])) {
				$lang = $pageModel->default_lang;
			} else {
				$lang = $activityInfo['langList'][0]['key'];
			}
		}
		// 是否包含英语，页面上“复制英语页面”和“复制SKU”功能需要用到
		$hasEn = \in_array(app()->params['en_lang'], $pageSupportLangList, true);
		unset($pageSupportLangList);

		$siteSuffix = explode('-', $activityInfo['site_code'])[1];
		$siteConf = app()->params['sites'][$activityInfo['site_code']]['secondary_domain'][$pipeline];
		if (empty($siteConf[$lang])) {
			throw new JsonResponseException($this->codeFail, '{$pipeline}渠道下没有{$lang}语言');
		}

		// 页面组信息
		$groupRelations = $this->getPipelineGroupRelations($activityInfo['group_id'], $pageModel, $lang);
		$data = $this->getNativeDesignData($pageId, $activityInfo['site_code'], $pipeline, $lang);

		$siteDomain = app()->params['sites'][$activityInfo['site_code']]['domain'] ?? '';
		// 获取用户组件模板列表
		$placeType = RequestUtils::getPageTypeByModuleName();
		$uiTplList = $this->getUserUiTemplateList(app()->user->username, $activityInfo['site_code'], $placeType);

		// 自动刷新组件处理
		$autoRefreshPage = new AutoRefreshPage($pageModel, $lang);

		//设置商品价格货币类型，用于价格显示
		$this->setCurrencyCookie();

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($pageModel->id);
		if (SitePlatform::isAppPlatform($pageModel->site_code)) {
			$nativePageId = PageModel::getNativeAppPageId($pageModel->id, $pageModel->site_code, $pipeline, SitePlatform::isAppPlatform($pageModel->site_code));
			$nativePageModel = PageModel::getById($nativePageId);
		}

		$data = [
			'pageTitle'       => PageLanguageModel::getPageTitle($pageModel->id),
			'lang'            => $lang,
			'languages'       => (new LanguageDataComponent())->getLanguageDataForLang($lang),
			'siteDomain'      => $siteDomain,
			'groupId'         => $pageModel->group_id,
			'langName'        => app()->params['lang'][$lang]['name'],
			'defaultLang'     => $pageModel->default_lang,
			'hasEn'           => $hasEn,
			'uiTplList'       => $uiTplList,
			'customKey'       => $data['customKey'],
			'pageId'          => $pageId,
			'pipeline'        => $pipeline,
			'activityInfo'    => $activityInfo,
			'pageData'        => $data['pageData'],
			'preview_url'     => $this->getNativePagePreviewUrl(isset($nativePageModel) ? $nativePageModel->pid : $pageModel->pid, $lang),
			'relations'       => $groupRelations,
			'siteCode'        => $activityInfo['site_code'],
			'platform'        => $siteSuffix,
			'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
			'jsAsyncData'     => $autoRefreshPage->getAsyncDataJsVariable(),
		];

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 获取原生页组件数据
	 *
	 * @param $pageId
	 * @param $siteCode
	 * @param $pipeline
	 * @param $lang
	 *
	 * @return array
	 */
	public function getNativeDesignData ($pageId, $siteCode, $pipeline, $lang)
	{
		//自定义布局组件编码
		$customKey = empty($data['custom']) ? 0 : $data['custom']->component_key;

		$components = NativePageLayoutModel::getComponentsSort($pageId, $lang);
		$componentsData = NativePageUiComponentModel::getComponentsData($pageId, $lang, $components);
		$pageData = $skuData = [];
		if (!empty($componentsData) && is_array($componentsData)) {
			$explainComponent = new ExplainComponent();
			$explainComponent->promiseSetGoodsList($componentsData, $siteCode, $pipeline, $lang, true);
            $isNativeApp = SitePlatform::isAppPlatform($siteCode);
			foreach ($componentsData as &$component) {
				$component = array_map(function (&$item) {
					if (is_string($item) && json_decode($item, true)) {
						$item = json_decode($item, true);
					}

					return $item;
				}, $component);
				if (!empty($component['sku_data']) && is_array($component['sku_data'])) {
					foreach ($component['sku_data'] as &$skuItem) {
                        if ($isNativeApp && (NativeConstants::SKU_FROM_INPUT == $skuItem['type'])) {
                            $skuItem['goodsInfo'] = $explainComponent->getGoodsData($skuItem['sku'], $lang,
                                (object)['siteCode' => $siteCode], $pipeline);
                        }
					}
				}
				$pageData[] = [
					'id'              => $component['component_id'],
					'component_key'   => $component['component_key'],
					'component_title' => $component['component_name'],
					'template_id'     => $component['tpl_id'],
					'template_name'   => $component['tpl_name'],
					'template_title'  => $component['tpl_title'],
					'data'            => $component['setting_data'],
					'style'           => $component['style_data'],
					'goodsSKU'        => $component['sku_data'],
					'need_navigate'   => $component['need_navigate']
				];
			}
		}

		return ['customKey' => $customKey, 'pageData' => ['list' => $pageData, 'layouts' => $components]];
	}

	/**
	 * 获取页面对应 PC\M\APP 三端关系页面信息
	 *
	 * @param int       $activityGroupId
	 * @param PageModel $pageModel
	 * @param string    $lang
	 *
	 * @return array
	 */
	private function getPipelineGroupRelations ($activityGroupId, $pageModel, $lang)
	{
		$activityGroupModel = ActivityGroupModel::findOne($activityGroupId);
		$groupPipelineLangList = json_decode($activityGroupModel->support_list, true);
		$pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageModel->id);
		$groupPipelinePageIds = PageGroupModel::find()->select('page_id')
			->where(['page_group_id' => $pageGroupId, 'pipeline' => $pageModel->pipeline])
			->asArray()->column();
		$pageInfoList = PageModel::find()->select('site_code, group_id, is_native')
			->where(['id' => $groupPipelinePageIds, 'is_delete' => 0])
			->asArray()->all();
		list(, $pagePlatformCode) = SitePlatform::splitSiteCode($pageModel->site_code);

		$platformList = [];
		foreach ($pageInfoList as $pageInfo) {
			list(, $platformCode) = SitePlatform::splitSiteCode($pageInfo['site_code']);
			$url = Url::current([
				'group_id' => $pageInfo['group_id'],
				'lang'     => in_array($lang, $groupPipelineLangList[$platformCode][$pageModel->pipeline]) ? $lang : ''
			], true);
			if (!empty($pageInfo['is_native'])) {
				if ($pagePlatformCode != $platformCode) {
					continue;
				}
				$platformName = '移动端';
				$url = dirname($url) . '/native?' . parse_url($url)['query'];
			} else {
				$platformName = ($platformCode === SitePlatform::PLATFORM_CODE_WAP)
					? 'M端' : strtoupper($platformCode) . '端';
				if (SitePlatform::PLATFORM_CODE_PC === strtolower($platformCode)) {
					$url = str_replace('native-', '', dirname($url)) . '/index?' . parse_url($url)['query'];
				}
			}

			$platformList[$platformCode] = [
				'siteCode' => $pageInfo['site_code'],
				'name'     => $platformName,
				'url'      => $url
			];
		}

		list(, $currentPlatformCode) = SitePlatform::splitSiteCode($pageModel->site_code);
		return [
			'current' => $currentPlatformCode,
			'list'    => $platformList,
		];
	}

	/**
	 * 获取渠道间原生页面复制的可选渠道列表
	 *
	 * @param $pageId
	 * @param $lang
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getNativeCopyPipeline (int $pageId, string $lang)
	{
		if (empty($this->pageInfo)) {
			throw new JsonResponseException($this->codeFail, '无效的页面ID');
		}

		if (app()->user->isGuest) {
			throw new JsonResponseException($this->codeFail, '用户没有登录或登录信息过期，请重新登录');
		}


		$fromPipeline = PageModel::getActivityPipelineByPageId($pageId);
		$toPipeline = $fromPipeline;

		// 过滤没有layout组件的页面
		$pipelineLangList = PageModel::getNativeHasDesignPageLang($this->pageInfo->group_id);
		if (empty($pipelineLangList)) {
			$fromPipeline = [];
		} else {
			foreach ($fromPipeline as $key => $val) {
				$_pipeline = $val['pipeline'];
				if (!isset($pipelineLangList[$_pipeline])) {
					unset($fromPipeline[$key]);
					continue;
				}

				foreach ($val['langList'] as $key1 => $langInfo) {
					if (!\in_array($langInfo['key'], $pipelineLangList[$_pipeline], true)) {
						unset($fromPipeline[$key]['langList'][$key1]);
					}
				}

				if (empty($fromPipeline[$key]['langList'])) {
					unset($fromPipeline[$key]);
				} else {
					$fromPipeline[$key]['langList'] = array_values($fromPipeline[$key]['langList']);
				}
			}
		}

		// 首页模块并不是管理员的情况下，过滤掉没有权限的渠道
		$newToPipeline = [];
		$websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($this->pageInfo->site_code);
		$privilegeComponent = new AdminSitePrivilegeComponent();
		$hasPermissions = $privilegeComponent->getCurrentUserSiteSpecialPermissions($websiteCode);
		if (!empty($hasPermissions)) {
			foreach ($toPipeline as $key => $val) {
				if (in_array($val['pipeline'], $hasPermissions, true)) {
					$newToPipeline[] = $val;
				}
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
			'fromPipeline' => array_values($fromPipeline),
			'toPipeline'   => $newToPipeline
		]);
	}

	/**
	 * 获取装修页面编辑锁
	 *
	 * @param array $params GET请求参数
	 *                      - page_id 页面ID
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function acquirePageDesignLock ($params)
	{
		if (!isset($params['page_id'])) {
			throw new JsonResponseException($this->codeFail, '缺失参数');
		}

		$pageId = $params['page_id'];
		$pageModel = PageModel::getById($pageId);
		if (!$pageModel) {
			throw new JsonResponseException($this->codeFail, '缺失无效');
		}

		$userId = app()->user->admin->id;
		$websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageModel->site_code);
		$lockPageIds = $this->NativePagePrivilegeComponent->getLockPageIds($pageModel);
		if (!$this->NativePagePrivilegeComponent->lockNativePageDesign($websiteCode, $lockPageIds, $userId)) {
			throw new JsonResponseException($this->codeFail, 'fail');
		}

		return app()->helper->arrayResult($this->codeSuccess, 'ok');
	}

	/**
	 * 释放装修页面编辑锁
	 *
	 * @param array $params GET请求参数
	 *                      - page_id 页面ID
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function releasePageDesignLock ($params)
	{
		if (!isset($params['page_id'])) {
			throw new JsonResponseException($this->codeFail, '缺失参数');
		}

		$pageId = $params['page_id'];
		$pageModel = PageModel::getById($pageId);
		if (!$pageModel) {
			throw new JsonResponseException($this->codeFail, '缺失无效');
		}

		$userId = app()->user->admin->id;
		$websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageModel->site_code);
		$lockPageIds = $this->NativePagePrivilegeComponent->getLockPageIds($pageModel);
		$editUserId = $this->NativePagePrivilegeComponent->getCurrentDesignUserId($websiteCode, $lockPageIds);
		if ($editUserId && $editUserId === $userId) {
			$this->NativePagePrivilegeComponent->unlockNativePageDesign($websiteCode, $pageId);
			return app()->helper->arrayResult($this->codeSuccess, 'ok');
		}
		return app()->helper->arrayResult($this->codeFail, 'fail');
	}

	/**
	 * 跨渠道复制页面
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @throws Exception
	 * @throws \Throwable
	 */
	public function copyNativePipeline ($data)
	{
		if (empty($data['from']) || empty($data['to']) || empty($data['type']) || empty($data[static::FIELD_PAGE_ID])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		$fromJson = json_decode($data['from'], true);
		$toJson = json_decode($data['to'], true);

		if (!is_array($fromJson) || !is_array($toJson) || !isset($fromJson['pipeline'], $fromJson['lang'])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		$toJson = array_column($toJson, null, 'pipeline');
		foreach ($toJson as &$item) {
			$item['lang'] = explode(',', $item['lang']);
		}

		$fromPipelineCode = $fromJson['pipeline'];
		$fromLangCode = $fromJson['lang'];

		$data['type'] = (int)$data['type'];
		if (isset($toJson[$fromPipelineCode])) {
			if (\in_array($fromLangCode, $toJson[$fromPipelineCode]['lang'], true)) {
				throw new JsonResponseException($this->codeFail, '同步到的渠道/语言不能包含被同步渠道/语言');
			}
		}

		$allPipelineCodes = array_unique(array_merge(array_keys($toJson), [$fromPipelineCode]));
		$allPages = PageModel::find()->where([
			'group_id'  => $this->pageInfo['group_id'],
			'pipeline'  => $allPipelineCodes,
			'is_delete' => PageModel::NOT_DELETE
		])->asArray()->all();
		if (empty($allPages) || \count($allPages) !== \count($allPipelineCodes)) {
			throw new JsonResponseException($this->codeFail, '渠道对应的页面信息获取失败');
		}

		$allPages = array_column($allPages, null, 'pipeline');
		$pipelineConfig = $this->getNativeCopyPipeline($data[static::FIELD_PAGE_ID], '');
		if ($pipelineConfig['code'] !== $this->codeSuccess) {
			return $pipelineConfig;
		}

		$fromPipeline = array_column($pipelineConfig['data']['fromPipeline'], null, 'pipeline');
		$fromPageId = $allPages[$fromPipelineCode]['id'];
		if (!isset($fromPipeline[$fromPipelineCode])) {
			throw new JsonResponseException($this->codeFail, '选择的源渠道活动不支持或没有权限');
		}
		$supportLangList = array_column($fromPipeline[$fromPipelineCode]['langList'], null, 'key');
		if (!isset($supportLangList[$fromLangCode])) {
			throw new JsonResponseException($this->codeFail, '选择的源渠道语言活动不支持');
		}
		unset($fromPipeline);

		$toPipeline = array_column($pipelineConfig['data']['toPipeline'], null, 'pipeline');
		foreach ($toJson as $toItem) {
			if (!is_array($toItem) || !isset($toItem['pipeline'], $toItem['lang'])) {
				continue;
			}

			$toPipelineCode = $toItem['pipeline'];
			foreach ($toItem['lang'] as $toLangCode) {
				$supportLangList = array_column($toPipeline[$toPipelineCode]['langList'], null, 'key');
				if (!isset($supportLangList[$toLangCode])) {
					continue;
				}

				$params = [
					'from_page_id' => $fromPageId,
					'from_lang'    => $fromLangCode,
					'to_page_id'   => $allPages[$toPipelineCode]['id'],
					'to_lang'      => $toLangCode
				];

				// 访问日志记录关联页面id
				AccessLogComponent::addPageId($params['to_page_id']);

				if ($data['type'] === SiteConstants::PIPELINE_COPY_TYPE_PAGE) {
					$res = $this->copyNativePage($params);
					if (!empty($asyncPageId = $this->getAsyncNativePageId($params['to_page_id'], $toItem['pipeline'], $toLangCode))) {
						$params['to_page_id'] = $asyncPageId;
						$res = $this->copyNativePage($params);
					}
				} elseif ($data['type'] === SiteConstants::PIPELINE_COPY_TYPE_SKU) {
					$res = $this->copyNativeSku($params);
					if (!empty($asyncPageId = $this->getAsyncNativePageId($params['to_page_id'], $toItem['pipeline'], $toLangCode))) {
						$params['to_page_id'] = $asyncPageId;
						$res = $this->copyNativeSku($params);
					}
				} else {
					$res = app()->helper->arrayResult($this->codeFail, '当前type类型还未支持');
				}
				if ($res['code'] !== $this->codeSuccess) {
					return $res;
				}

			}
		}

		return app()->helper->arrayResult($this->codeSuccess, '复制成功');
	}

	/**
	 * 复制页面(英文的)
	 *
	 * @param      $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @throws Exception
	 * @throws \Throwable
	 */
	public function copyNativePage (array $data)
	{
		if (empty($data['from_lang']) || empty($data['from_page_id']) || empty($data['to_lang']) || empty($data['to_page_id'])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		//页面样式数据复制
		$oldPage = PageLanguageModel::findOne([
			static::FIELD_PAGE_ID => $data['from_page_id'],
			'lang'                => $data['from_lang']
		]);
		$newPage = PageLanguageModel::findOne([
			static::FIELD_PAGE_ID => $data['to_page_id'],
			'lang'                => $data['to_lang']
		]);
		if (!$oldPage || !$newPage) {
			throw new JsonResponseException($this->codeFail, '未能找到正确的pageLanguage数据');
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($data['to_page_id']);

		$newPage->background_color = $oldPage->background_color;
		$newPage->background_image = $oldPage->background_image;
		$newPage->background_position = $oldPage->background_position;
		$newPage->background_repeat = $oldPage->background_repeat;
		$newPage->custom_css = $oldPage->custom_css;

		//事物开始
		$tr = app()->db->beginTransaction();
		try {
			$newPage->save();
			$fromLayout = NativePageLayoutModel::findOne([
				static::FIELD_PAGE_ID => $data['from_page_id'],
				'lang'                => $data['from_lang']
			]);
			NativePageLayoutModel::saveFormData(
				[
					'page_id' => $data['to_page_id'],
					'lang'    => $data['to_lang'],
					'data'    => $fromLayout ? $fromLayout->data : ''
				]
			);
			$fromUi = NativePageUiComponentModel::findAll([
				static::FIELD_PAGE_ID => $data['from_page_id'],
				'lang'                => $data['from_lang']
			]);
			NativePageUiComponentModel::deleteAll([
				static::FIELD_PAGE_ID => $data['to_page_id'],
				'lang'                => $data['to_lang']
			]);
			if (!empty($fromUi)) {
				$fromUi = ArrayHelper::toArray($fromUi);
				$toUi = array_map(function ($item) use ($data) {
					return [
						'page_id'        => $data['to_page_id'],
						'lang'           => $data['to_lang'],
						'id'             => $item['component_id'],
						'component_key'  => $item['component_key'],
						'template_id'    => $item['tpl_id'],
						'template_title' => $item['tpl_title'],
						'template_name'  => $item['tpl_name'],
						'style'          => $item['style_data'],
						'goodsSKU'       => $item['sku_data'],
						'data'           => $item['setting_data']
					];
				}, $fromUi);
				NativePageUiComponentModel::saveFormData(['page_id' => $data['to_page_id'], 'lang' => $data['to_lang'], 'data' => $toUi]);
			}
			$tr->commit();

			return app()->helper->arrayResult($this->codeSuccess, '复制成功');
		} catch (Exception $e) {
			$tr->rollBack();

			return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '复制失败');
		}
	}

	/**
	 * 复制原生页面SKU
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @throws Exception
	 * @throws \Throwable
	 */
	public function copyNativeSku (array $data)
	{
		if (empty($data['from_lang']) || empty($data['from_page_id']) || empty($data['to_lang']) || empty($data['to_page_id'])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($data['to_page_id']);

		$fromPage = NativePageUiComponentModel::findAll([
			static::FIELD_PAGE_ID => $data['from_page_id'],
			'lang'                => $data['from_lang']
		]);
		$fromPage = !empty($fromPage) ? ArrayHelper::toArray($fromPage) : [];

		$toPage = NativePageUiComponentModel::findAll([
			static::FIELD_PAGE_ID => $data['to_page_id'],
			'lang'                => $data['to_lang']
		]);
		$toPage = !empty($toPage) ? ArrayHelper::toArray($toPage) : [];

		$this->checkNativePageSku($fromPage, $toPage);

		return app()->helper->arrayResult($this->codeSuccess, '同步成功');
	}

	/**
	 * 检查页面商品组件是否满足复制的需求
	 *
	 * @param array $fromPage
	 * @param array $toPage
	 *
	 * @throws JsonResponseException
	 */
	protected function checkNativePageSku (array $fromPage, array $toPage)
	{
		//获取排好序的商品ui组件
		$formUiInOrder = $toUiInOrder = $uiEqualList = [];
		$this->getIncludeSkuNativeUiInOrder($fromPage, $formUiInOrder);
		$this->getIncludeSkuNativeUiInOrder($toPage, $toUiInOrder);

		//检测商品组件个数是否相等
		$this->checkSkuUiEqual($formUiInOrder, $toUiInOrder, $uiEqualList);

		//复制SKU
		if (true !== ($copyRes = NativePageUiComponentModel::copySku($uiEqualList))) {
			throw new JsonResponseException($this->codeFail, '同步失败', [], $copyRes);
		}
	}

	/**
	 * 获取排好序的包含sku的ui组件
	 *
	 * @param array $uiList
	 * @param array $uiInOrder
	 */
	protected function getIncludeSkuNativeUiInOrder (array $uiList, array &$uiInOrder)
	{
		foreach ($uiList as $ui) {
			if (\in_array($ui['component_key'], $this->uiKeyIncludeSku, true)) {
				$uiInOrder[] = $ui;
			}
		}
	}

	/**
	 * 预览原生页面
	 *
	 * @param string $pid
	 * @param string $lang
	 *
	 * @return array|string
	 */
	public function nativePreview (string $pid, string $lang)
	{
		if (empty($pid) || empty($lang)) {
			return '参数不全';
		}

		if (!($pageModel = PageModel::getByPId($pid))) {
			return '页面不存在或已被删除';
		}
		if(SitePlatform::isAppPlatform($pageModel->site_code)) {
			$pageModel->site_code = SitePlatform::getSiteBySiteCode($pageModel->site_code) . '-' . SitePlatform::PLATFORM_CODE_WAP;
		}
		$html = (new ExplainTplComponent())->getHeadOrFooter($pageModel->site_code, $lang, 'activity', $pageModel->pipeline);
		$head = explode('<!-- geshop main start  -->', $html);
		$footer = explode('<!-- geshop main end  -->', $html);
		$data = $this->getNativeDesignData($pageModel->id, $pageModel->site_code, $pageModel->pipeline, $lang);
		$languages = (new LanguageDataComponent())->getLanguageDataForLang($lang);

		return app()->helper->arrayResult(
			$this->codeSuccess,
			$this->msgSuccess,
			[
				'head'      => $head[0],
				'footer'    => $footer[1],
				'pageData'  => $data,
				'languages' => $languages,
                'pageId'    => $pageModel->id,
                'siteCode'  => $pageModel->site_code,
                'pipeline'  => $pageModel->pipeline,
                'lang'      => $lang,
                'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
			]
		);
	}

    /**
     * 获取原生装修页面数据
     *
     * @param string $group_id
     * @param string $lang
     * @param string $pipeline
     * @return array
     * @throws JsonResponseException
     */
	public function getNativeIndexData($group_id, $lang, $pipeline)
    {
        /** @var PageModel $pageModel */
        $pageModel = PageModel::getByGroupId($group_id, $pipeline);

        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '页面不存在或已被删除');
        }

        return [
            'sopAddRuleUrl' => app()->params['soa']['sop']['addRuleUrl'] ?? '',
            'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
        ];
    }

	/**
	 * 批量发布页面
	 *
	 * @param        $batchData
	 * @param string $groupId
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function nativeBatchRelease ($batchData, string $groupId = '')
	{
		if (empty($batchData)) {
			throw new JsonResponseException($this->codeFail, '参数不完整');
		}
		// 先校验参数格式
		/** @var array $batchArr */
		$batchArr = json_decode($batchData, true);

		foreach ($batchArr as $item) {
			if (empty($item['pipeline']) || empty($item['lang'])) {
				throw new JsonResponseException($this->codeFail, '参数格式不正确');
			}
		}

		// 先将pipeline转成pageId
		$pages = PageModel::find()
			->select('id, site_code, pipeline')
			->where([
				'group_id' => !empty($groupId) ? $groupId : $this->pageInfo->group_id,
				'pipeline' => array_column($batchArr, 'pipeline'),
			])
			->asArray()
			->all();

		// 检查用户发布权限
        $siteCode = '';
		if (!empty($pages)) {
            $siteCode = $pages[0]['site_code'];
			$onlinePipelineCodes = array_column($pages, 'pipeline');
			$this->PageComponent->checkUserSpecialPublishPermissions($onlinePipelineCodes, $pages[0]['site_code']);
		}

		$pages = $pages ? array_column($pages, null, 'pipeline') : [];
		$data = $nativeAppIds = [];

		try {
			defined('PUSHSYNC') or define('PUSHSYNC', app()->swoole->init()->isConnected());
		} catch (\Exception $e) {
			app()->rms->reportS3PushError($e->getMessage());
			defined('PUSHSYNC') or define('PUSHSYNC', false);
		}

		$fallbackParamList = [];
		$isApp = SitePlatform::isAppPlatform($pages[$item['pipeline']]['site_code']);
		app()->arrayCache->set('redis_data_multi_array', json_encode([]));
		foreach ($batchArr as $item) {
			$item['page_id'] = $pages[$item['pipeline']]['id'] ?? 0;
			if (!$item['page_id']) {
				continue;
			}
			$res = $this->nativeRelease($item['page_id'], $item['lang']);
			if ($res['code'] !== $this->codeSuccess) {
				return $res;
			}
			$nativeAppId = PageModel::getNativeAppPageId(
				$item['page_id'],
				$pages[$item['pipeline']]['site_code'],
				$item['pipeline'],
				($isApp === true) ? false : true
			);

			$data[$item['page_id']] = $res['data'];
			$nativeAppIds[$nativeAppId] = $item['lang'];

			// 生成兜底数据参数
            $fallbackParamList[] = [
                'wap' => [
                    'site_code' => ($isApp === true) ? SitePlatform::getSiteCodeByPlatformCode(SitePlatform::PLATFORM_CODE_WAP) : $siteCode,
                    'page_id' => ($isApp === true) ? $nativeAppId : $item['page_id']
                ],
                'app' => [
                    'site_code' => ($isApp === true) ? $siteCode : SitePlatform::getSiteCodeByPlatformCode(SitePlatform::PLATFORM_CODE_APP),
                    'page_id' =>  ($isApp === true) ? $item['page_id'] : $nativeAppId
                ],
                'pipeline' => $item['pipeline'],
                'lang' => $item['lang'],
            ];
		}

		$pushPageArray = app()->arrayCache->get('redis_data_multi_array');
		if (!empty($pushPageArray)) {
			$pushPageArray = json_decode($pushPageArray, true);
			$pushState = $this->CrontabComponent->asyncPushPage($pushPageArray, ($isApp === true) ? false : true);
			if (true !== $pushState) {
				return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $pushState);
			}
		}

        $this->NativePageFallbackComponent->fallback($siteCode, $fallbackParamList);
		$this->obFlush('发布成功');

		if (!empty($nativeAppIds) && is_array($nativeAppIds)) {
			app()->arrayCache->set('redis_data_multi_array', json_encode([]));
			foreach ($nativeAppIds as $nId => $nLang) {
				$this->nativeRelease($nId, $nLang);
			}
			$pushPageArray = app()->arrayCache->get('redis_data_multi_array');
			if (!empty($pushPageArray)) {
				$pushPageArray = json_decode($pushPageArray, true);
				$pushState = $this->CrontabComponent->asyncPushPage($pushPageArray, ($isApp === true) ? true : false);
				if (true !== $pushState) {
					return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $pushState);
				}
			}
		}

		app()->response->isSent = true;
	}

	/**
	 * 先返回信息给前端，然后后端继续处理
	 *
	 * @param string $message 返回信息
	 */
	private function obFlush($message)
	{
		$content = json_encode(app()->helper->arrayResult($this->codeSuccess, $message));
		ob_end_clean();
		header('Connection: close');
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/json;charset=utf-8');// 如果前端要的是json则添加，默认是返回的html/text
		ob_start();
		echo $content;// 输出结果到前端
		header('Content-Length: ' . ob_get_length());
		ob_end_flush();
		flush();
		if (function_exists('fastcgi_finish_request')) { // yii或yaf默认不会立即输出，加上此句即可（前提是用的fpm）
			fastcgi_finish_request(); // 响应完成, 立即返回到前端,关闭连接
		}
	}

	/**
	 * 发布页面
	 *
	 * @param        $pageId
	 * @param        $lang
	 * @param string $type
	 *
	 * @return array
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function nativeRelease ($pageId, $lang, $type = '')
	{
		// TODO 按照语言发布，目前是发布语言下的所有页面
		ignore_user_abort(true);

		$checkRes = $this->beforeVerifyRelease($pageId);
		if ($checkRes['code']) {
			return $checkRes;
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($pageId);

		/** @var \app\modules\common\zf\models\PageModel $pageModel */
		$pageModel = $checkRes['data'];
		$pageModel->status = PageModel::PAGE_STATUS_HAS_ONLINE;
		$pageModel->auto_refresh = PageModel::AUTO_REFRESH;
		$pageModel->verify_user = app()->user->username;
		$pageModel->verify_time = time();

		//页面上线，生成上线文件并推送S3
		if (PageModel::PAGE_STATUS_HAS_ONLINE === $pageModel->status) {
			list($success, $errorMsg) = $this->batchCreateOnlineNativePageHtml([$pageId], $pageModel->activity_id
				, false, false, false, $type, false);
			if (!$success) {
				return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $errorMsg);
			}
			//活动上线
			if (false === ActivityModel::changeOnlineActivity(
					$pageModel->activity_id,
					PageModel::PAGE_STATUS_HAS_ONLINE)
			) {
				return app()->helper->arrayResult($this->codeFail, '发布失败：活动上线失败');
			}
		}

		if (false === $pageModel->update(true)) {
			return app()->helper->arrayResult($this->codeFail, '发布失败：操作失败');
		}

		$data = PageModel::getPageUrls($pageModel->activity_id, $pageModel->id, $lang);

		return app()->helper->arrayResult($this->codeSuccess, '发布成功', $data);
	}
}
