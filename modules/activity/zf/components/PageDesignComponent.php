<?php

namespace app\modules\activity\zf\components;

use app\modules\common\gb\models\ActivityDataModel;
use yii\helpers\Url;
use app\base\SitePlatform;
use app\base\Translate;
use app\base\RequestUtils;
use app\components\auto\AutoRefreshPage;
use app\modules\base\components\AccessLogComponent;
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\component\zf\components\ExplainTplComponent;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\ActivityGroupModel;
use app\modules\common\zf\models\PageGroupModel;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\components\CommonPageDesignComponent;

/**
 * 页面装修设计-整个页面相关
 *
 */
class PageDesignComponent extends CommonPageDesignComponent
{

	/**
	 * 获取首页需要的数据
	 *
	 * @param string $group_id 页面分组ID
	 * @param string $lang     语言代码简称
	 * @param string $pipeline 国家编码
	 *
	 * @return array|string
	 */
	public function getIndexData ($group_id, $lang, $pipeline)
	{
		/** @var PageModel $pageModel */
		$pageModel = PageModel::getByGroupId($group_id, $pipeline);

		if (!$pageModel) {
			return '页面不存在或已被删除';
		}

		$pageId = $pageModel->id;
		$activityInfo = ActivityModel::getActivityInfo($pageModel->activity_id);
		//检查活动是否加锁，并判断权限
		if (false === ActivityModel::checkAuth($activityInfo)) {
			return '只有活动创建者才具有此权限';
		}

		if (empty($activityInfo['langList'])) {
			return '活动还未设置语言选项';
		}

		$activityInfo['langList'] = $activityInfo['langList'][$pipeline];
		$activityInfo['allLangList'] = ActivityModel::getPipelineAndLang($activityInfo['lang'], $activityInfo['site_code']);

		// 过滤没有权限的渠道
		$activityPipelineList = !empty($activityInfo['pipeline']) ? explode(',', $activityInfo['pipeline']) : [];
		$privilegeComponent = new AdminSitePrivilegeComponent();
		$hasPermissions = $privilegeComponent->getCurrentUserValidSiteSpecialPermissions($activityInfo['site_code'], $activityPipelineList);
		if (empty($hasPermissions) || !in_array($pipeline, $hasPermissions, true)) {
			return '没有渠道装修权限！';
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

		// 获取页面语言信息
		$pageArr = PageModel::getPageInfo($pageId, $lang);
		if (empty($pageArr['pageLanguages'])) {
			return '活动当前语言下的属性还未设置，请去页面编辑中设置';
		}
		$siteSuffix = explode('-', $activityInfo['site_code'])[1];
		$siteConf = app()->params['sites'][$activityInfo['site_code']]['secondary_domain'][$pipeline];
		if (empty($siteConf[$lang])) {
			return "{$pipeline}渠道下没有{$lang}语言";
		}

		// 页面组信息
		$groupRelations = $this->getPipelineGroupRelations($activityInfo['group_id'], $pageModel, $lang);

		//当前语言放在第一个
		$firstLanguage = [];
		foreach ($pageArr['pageLanguages'] as $key => $page) {
			if ($key == 0) {
				$firstLanguage = $page;
			}
			if ($page['lang'] == $lang) {
				$pageArr['pageLanguages'][0] = $page;
				$pageArr['pageLanguages'][$key] = $firstLanguage;
			}
		}
		$domain = $siteConf[$lang];
		$urls = array_column($pageArr['pageLanguages'], null, 'lang');
		$pageUrl = !empty($urls[$lang]['page_url']) ? $domain . $urls[$lang]['page_url'] : '';
		$type = (ActivityModel::TYPE_APP === (int)$activityInfo['type'])
			? ActivityModel::TYPE_MOBILE : $activityInfo['type'];

		$data = $this->getDesignData($pageId, $type, $activityInfo['site_code'], $lang, 1);

		$siteDomain = app()->params['sites'][$activityInfo['site_code']]['domain'] ?? '';
		// 获取用户组件模板列表
		$placeType = RequestUtils::getPageTypeByModuleName();
		$uiTplList = $this->getUserUiTemplateList(app()->user->username, $activityInfo['site_code'], $placeType);

		// 自动刷新组件处理
		$autoRefreshPage = new AutoRefreshPage($pageModel, $lang);

		//设置商品价格货币类型，用于价格显示
		$this->setCurrencyCookie();

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($pageId);

		return [
			'lang'            => $lang,
			'siteDomain'      => $siteDomain,
			'groupId'         => $group_id,
			'langName'        => $urls[$lang]['lang_name'],
			'defaultLang'     => $pageModel->default_lang,
			'hasEn'           => $hasEn,
			'data'            => $data['data'],
			'uiTplList'       => $uiTplList,
			'customKey'       => $data['customKey'],
			'pageId'          => $pageId,
			'pageInfo'        => $pageArr,
			'pipeline'        => $pipeline,
			'activityInfo'    => $activityInfo,
			'pageHtml'        => $data['pageHtml'],
			'preview_url'     => $this->getPagePreviewUrl($pageModel->pid, $lang),
			'relations'       => $groupRelations,
			'siteCode'        => $activityInfo['site_code'],
			'platform'        => $siteSuffix,
			'pageUrl'         => $pageUrl,
			'interfaceConfig' => $this->getInterfaceConfig($pageModel->site_code),
			'jsLanguage'      => Translate::getUiComponentJsTransMessage($lang),
			'jsAsyncData'     => $autoRefreshPage->getAsyncDataJsVariable(),
            'sopAddRuleUrl' => app()->params['soa']['sop']['addRuleUrl'] ?? '',
		];
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
		//兼容RG合并过来分组ID为0旧数据
		if (empty($activityGroupId)) {
			$platform = SitePlatform::getPlatformCodeBySiteCode($pageModel->site_code);
			$activityInfo = ActivityModel::findOne($pageModel->activity_id);
			$groupPipelineLangList = [$platform => json_decode($activityInfo->lang, true)];
		}else {
			$activityGroupModel = ActivityGroupModel::findOne($activityGroupId);
			$groupPipelineLangList = json_decode($activityGroupModel->support_list, true);
		}
		$pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageModel->id);
		$groupPipelinePageIds = PageGroupModel::find()->select('page_id')
			->where(['page_group_id' => $pageGroupId, 'pipeline' => $pageModel->pipeline])
			->asArray()->column();
		$pageInfoList = PageModel::find()->select('site_code, group_id, is_native')
			->where(['id' => $groupPipelinePageIds, 'is_delete' => 0])
			->asArray()->all();

		$platformList = [];
		foreach ($pageInfoList as $pageInfo) {
			list(, $platformCode) = SitePlatform::splitSiteCode($pageInfo['site_code']);
			$url = Url::current([
				'group_id' => $pageInfo['group_id'],
				'lang'     => in_array($lang, $groupPipelineLangList[$platformCode][$pageModel->pipeline]) ? $lang : ''
			], true);
			if (!empty($pageInfo['is_native'])) {
				$platformCode = 'native';
				if(!empty($platformList[$platformCode])) {
					break;
				}
				$platformName = '移动端';
				$url = str_replace('design', 'native-design', dirname($url)) . '/native?' . parse_url($url)['query'];
			} else {
				$platformName = ($platformCode === SitePlatform::PLATFORM_CODE_WAP)
					? 'M端' : strtoupper($platformCode) . '端';
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
	 * 预加载发布页面数据
	 *
	 * @param int $pageId
	 *
	 * @return array
	 */
	public function preloadReleaseData (string $groupId, string $pipeline)
	{
		// 先将pipeline转成pageId
		$page = PageModel::getByGroupId($groupId, $pipeline);
		if ($page && !empty($page->id)) {
			$pageLanguageList = PageLanguageModel::getAllPageLangList([$page->id]);
				if (!empty($pageLanguageList) && is_array($pageLanguageList)) {
				foreach ($pageLanguageList as &$langList) {
					$api = app()->params['sites'][$langList['site_code']]['headFooterMonitorDomain']['activity'][$langList['pipeline']][$langList['lang']] ?? '';
					if (!empty($api)) {
						$hfUrls[] = [
							'site_code' => $langList['site_code'],
							'api'       => $api
						];
					}
				}

				(new ExplainTplComponent())->promiseSetHeadOrFooter($hfUrls);
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess);
	}
}
