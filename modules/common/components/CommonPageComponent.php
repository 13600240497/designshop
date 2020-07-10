<?php /** @noinspection ALL */

namespace app\modules\common\components;

use app\modules\common\models\{
	ActivityModel, PageLanguageModel, PageModel, PagePublishLogModel, SiteUpdateLogModel
};

use app\base\SiteConstants;
use app\modules\common\traits\{
	CommonVerifyStatusTrait, CommonPublishTrait
};

use app\modules\base\components\MenuComponent;
use app\modules\base\models\AdminModel;
use Yii;
use ego\base\JsonResponseException;
use app\base\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\soa\components\ObsComponent;
use app\modules\base\components\AccessLogComponent;

/**
 * 页面组件
 *
 * @property \app\modules\common\components\CommonPageTplComponent CommonPageTplComponent
 * @property \app\modules\common\components\CommonCrontabComponent CrontabComponent
 */
class CommonPageComponent extends Component
{
	use CommonVerifyStatusTrait;
	use CommonPublishTrait;

	/**
	 * 文件差异对比内容为空时的html结尾
	 */
	const EMPTY_DIFF_HTML_END = /** @lang html */
		'<body></body></html>';

	/**
	 * 文件差异对比内容为空时的默认替换html结尾
	 */
	const EMPTY_DIFF_HTML_END_DEFAULT = /** @lang html */
		'<body><h2>暂无文件差异</h2></body></html>';

	/**
	 * 一键更新头尾任务title
	 */
	const SITE_TASK_TITLE = '一键刷新头尾部';

	/**
	 * @var array 自动刷新时间间隔
	 */
	public $refreshList = [
		['key' => 0, 'name' => '不自动刷新'],
		['key' => 5 * 60, 'name' => '5分钟'],
		['key' => 10 * 60, 'name' => '10分钟'],
		['key' => 30 * 60, 'name' => '30分钟'],
		['key' => 60 * 60, 'name' => '1小时'],
		['key' => 6 * 60 * 60, 'name' => '6小时'],
		['key' => 12 * 60 * 60, 'name' => '12小时'],
		['key' => 24 * 60 * 60, 'name' => '1天']
	];

	/**
	 * 页面数据
	 *
	 * @param     $pageModel
	 * @param int $activityId
	 *
	 * @return mixed
	 */
	public function pageLists ($pageModel, $activityId = 0)
	{
		$conditions['p.is_delete'] = $pageModel::NOT_DELETE;
		if (!empty($activityId)) {
			$conditions['p.activity_id'] = intval($activityId);
		}

		return $pageModel::find()->alias('p')->select('p.*, a.realname as create_name,a2.realname as update_user')
			->leftJoin(AdminModel::tableName() . ' as a', 'p.create_user = a.username')
			->leftJoin(AdminModel::tableName() . ' as a2', 'p.update_user = a2.username')
			->where($conditions)
			->orderBy('p.id desc')
			->all();
	}

	/**
	 * 拼装数组
	 *
	 * @param array                                    $pageList     page列表数组
	 * @param array                                    $pageLangList pageLanguage列表数组
	 * @param \app\modules\common\models\ActivityModel $activity     活动信息
	 *
	 * @return mixed
	 */
	public function buildListData ($pageList, $pageLangList = null, $activity = null)
	{
		$siteCode = isset($activity->site_code) ? $activity->site_code : current($pageList)['site_code'];
		$module = $activity ? 'activity' : 'home';


		//判断是否有转M、转APP的按钮(对应站点是否存在，且用户是否对对应站点有权限)
		$siteList = MenuComponent::getUserSites(app()->user->admin->is_super);
		$siteList = $siteList ? array_column($siteList, 'short_name') : [];
		$sitePre = explode('-', $siteCode)[0];
		$hasToWap = $siteCode === $sitePre . '-pc'
			&& isset(app()->params['sites'][$sitePre . '-wap'])
			&& \in_array($siteCode, $siteList, true);
		$hasToApp = $siteCode === $sitePre . '-wap'
			&& isset(app()->params['sites'][$sitePre . '-app'])
			&& \in_array($siteCode, $siteList, true);

		foreach ($pageList as &$page) {
			$page['hasToWap'] = $hasToWap;
			$page['hasToApp'] = $hasToApp;
			$page['urls'] = [];
			$page['design_url'] = Url::to(["/{$module}/design/index", 'pid' => $page['pid']], true);
			if (!empty($activity)) {
				if (!app()->user->admin->is_super) {
					// 超级管理员才返回auto_refresh字段，用来判断页面编辑状态
					unset($page['auto_refresh']);
				}
				$page['activity_type'] = $activity->type;
				$page['is_lock'] = $activity->is_lock;
				$page['activity_create_user'] = $activity->create_user;
			} else {
				$page['preview'] = Url::to($page['page_url'], true);
				$page['qrcode'] = Url::to([
					"/{$module}/qr-code/create",
					'url' => $page['preview']
				], true);
				$page['status_name'] = PageModel::HOME_PAGE_STATUS_SHOW_NAME[$page['status']] ?? '';
				unset($page['page_url']);
			}
			$this->buildPageLangList($pageLangList, $page, $siteCode);

			$_langCode = isset($page['pageLanguages']) ? reset($page['pageLanguages'])['lang'] : '';
			$page['preview_url'] = Url::to([
				"/{$module}/design/preview", 'pid' => $page['pid'], 'lang' => $_langCode
			], true);
		}

		return $pageList;
	}

	/**
	 * 拼装活动页面数据
	 *
	 * @param $pageLangList
	 * @param $page
	 * @param $siteCode
	 */
	public function buildPageLangList ($pageLangList, &$page, $siteCode)
	{
		$ObsComponent = new ObsComponent();
		if (!empty($pageLangList) && is_array($pageLangList)) {
			$langConf = app()->params['lang'];
			$siteConf = app()->params['sites'][$siteCode];
			$domainKey = $this->getDomainKey();
			foreach ($pageLangList as $lang) {
				if ((int)$page['id'] === (int)$lang['page_id']) {
					$lang['lang_name'] = $langConf[$lang['lang']]['name'];
					if (!empty($page['activity_id'])) {//获取选中的obs页面
						$lang['themeList'] = $ObsComponent->getObsPageId($lang['page_id'], $lang['lang'], $page['activity_id']);
					}
					$page['pageLanguages'][] = $lang;
					$domain = $siteConf[$domainKey][$lang['lang']] ?? current($siteConf[$domainKey]);
					Yii::info('数据调试：' . $domainKey . '||||' . $lang['lang'] . '||||' . $domain, __METHOD__);
					if (!empty($lang['page_url'])) {
						$page['urls'][] = [
							'lang'      => $lang['lang'],
							'lang_name' => $lang['lang_name'],
							'page_url'  => $domain . $lang['page_url']
						];
					}
				}
			}
		}
	}

	/**
	 * 批量编辑页面属性|活动
	 *
	 * @param      $pageModel
	 * @param      $params
	 * @param bool $runValidation
	 *
	 * @return array
	 */
	public function doActivityBatchEdit ($pageModel, $params, $runValidation = true)
	{
		$tipsMsg = (empty($params['page_id'])) ? '添加成功' : '修改成功';
		// 事物开始
		$tr = app()->db->beginTransaction();
		try {
			// 更新page表记录
			if ((!$params['page_id'] && !$pageModel->insert($runValidation))
				|| ($params['page_id'] && false === $pageModel->update($runValidation))
			) {
				throw new Exception($pageModel->flattenErrors(', '));
			}
			$data = [$pageModel->id, $params['list']];
			// 更新page_language表记录
			if (true !== ($editRes = $this->editPage($data, $runValidation))) {
				throw new Exception($editRes);
			}

			if (!$this->zaddRefreshTask($pageModel->id, $params['refresh_time'])) {
				throw new Exception('自动刷新任务处理失败');
			}

			$tr->commit();

			// 访问日志记录关联页面id
			AccessLogComponent::addPageId($pageModel->id);

			return app()->helper->arrayResult($this->codeSuccess, $tipsMsg, $pageModel);
		} catch (Exception $e) {
			$tr->rollBack();

			return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: $tipsMsg);
		}
	}

	/**
	 * 批量编辑页面属性
	 *
	 * @param      $pageModel
	 * @param      $params
	 * @param bool $runValidation
	 *
	 * @return array
	 */
	public function doHomeBatchEdit ($pageModel, $params, $runValidation = true)
	{
		// 事物开始
		$tr = app()->db->beginTransaction();
		try {
			// 更新page表记录
			if ((!$params['page_id'] && !$pageModel->insert($runValidation))
				|| ($params['page_id'] && false === $pageModel->update($runValidation))
			) {
				throw new Exception($pageModel->flattenErrors(', '));
			}

			$data = [$pageModel->id, $params['list']];
			// 更新page_language表记录
			if (true !== ($editRes = $this->editPage($data, $runValidation))) {
				throw new Exception($editRes);
			}

			$tr->commit();

			// 访问日志记录关联页面id
			AccessLogComponent::addPageId($pageModel->id);

			return app()->helper->arrayResult($this->codeSuccess, '保存成功', $pageModel);
		} catch (Exception $e) {
			$tr->rollBack();

			return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '保存失败');
		}
	}

	/**
	 * 验证标题名称是否符合规范（仅限于小写字母、数字、中横线-，长度为3-64位字符，无需带后缀）
	 *
	 * @param int    $activityId 活动ID
	 * @param int    $pageId     页面ID
	 * @param string $urlName    url标题
	 * @param string $lang       语言简码
	 *
	 * @return bool|string
	 */
	public function checkUrlName ($activityId, $pageId, $urlName, $lang)
	{
		$num = preg_match('/[a-z0-9\-]{3,64}$/is', $urlName, $match);
		if (!$num || $match[0] !== (string)$urlName) {
			return 'url_name不符合规范（仅限于小写字母、数字、中横线-，长度为3-64位字符）';
		}

		if (!ActivityModel::checkUrlNameExist($activityId, $pageId, $urlName, $lang)) {
			return '站点当前语言"' . $lang . '"下已存在相同的url_name，请重命名';
		}

		return true;
	}

	/**
	 * 检查批量编辑页面属性的data参数
	 *
	 * @param int   $activityId 活动ID
	 * @param int   $pageId     页面ID
	 * @param array $data       data参数json_decode后的数组
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function checkBatchPageData ($activityId, $pageId, $data)
	{
		$activity = ActivityModel::findOne([
			'id'        => $activityId,
			'is_delete' => ActivityModel::NOT_DELETE
		]);

		//检查活动是否加锁，并判断权限
		if (false === ActivityModel::checkAuth($activity)) {
			throw new JsonResponseException($this->codeFail, '只有活动创建者才具有此权限');
		}

		if (!$activity) {
			throw new JsonResponseException($this->codeFail, '无效的活动ID');
		}
		if (true !== ($res = ActivityModel::isEnabled($activityId))) {
			throw new JsonResponseException($this->codeFail, $res['message']);
		}

		$langArr = explode(',', $activity->lang);
		$langList = array_keys($data);
		if (\count($langList) !== \count($langArr)) {
			// 只有新增时才校验语言项个数
			throw new JsonResponseException($this->codeFail, 'data下的语言项个数和活动所设置的语言个数不匹配');
		}

		$langUrlNames = [];
		if ($pageId) {
			$langUrlNames = PageLanguageModel::find()
				->select('lang, url_name')
				->where(['page_id' => $pageId])
				->indexBy('lang')
				->asArray()->all();
		}

		foreach ($data as $lang => $item) {
			if (!\in_array($lang, $langArr, true) || empty($item['title'])) {
				throw new JsonResponseException($this->codeFail, 'data下的语言项超出活动语言设置范畴');
			}

			//修改时如果url_name没有改变，不需要验证
			if (!empty($item['url_name']) && (empty($langUrlNames)
					|| (isset($langUrlNames[$lang]) && ($langUrlNames[$lang]['url_name'] != $item['url_name'])))
			) {
				if (true !== ($urlMsg = $this->checkUrlName($activityId, $pageId, $item['url_name'], $lang))) {
					throw new JsonResponseException($this->codeFail, $urlMsg);
				}
			}
		}

		if ($pageId) {
			$page = PageModel::findOne([
				'id'        => $pageId,
				'is_delete' => PageModel::NOT_DELETE
			]);
			if (!$page) {
				throw new JsonResponseException($this->codeFail, '无效的页面ID');
			}
			if ($page->activity_id !== (int)$activityId) {
				throw new JsonResponseException($this->codeFail, '活动ID和页面ID不匹配');
			}
		} else {
			$page = new PageModel();
			$page->activity_id = $activityId;
			$page->status = PageModel::PAGE_STATUS_TO_BE_ONLINE;
			$page->verify_status = PageModel::VERIFY_STATUS_NOT_COMMIT;
			$page->is_delete = PageModel::NOT_DELETE;
		}

		return [
			'activity' => $activity,
			'page'     => $page
		];
	}

	/**
	 * 编辑首页/活动页属性
	 *
	 * @param      $params
	 * @param bool $runValidation
	 *
	 * @return array|bool|string
	 */
	private function editPage ($params, $runValidation = true)
	{
		list($pageId, $list) = $params;

		foreach ($list as $lang => $item) {
			$pageLanguageModel = PageLanguageModel::findOne([
				'page_id' => $pageId,
				'lang'    => $lang
			]);
			if (!empty($item['share_place'])) {
				$item['share_place'] = json_encode($item['share_place']);
			}

			!$pageLanguageModel && $pageLanguageModel = new PageLanguageModel();
			$pageLanguageModel->load($item, '');
			$pageLanguageModel->page_id = $pageId;
			$pageLanguageModel->lang = $lang;
			$pageLanguageModel->tpl_id = !empty($item['tpl_id']) ? $item['tpl_id'] : intval($pageLanguageModel->tpl_id);
			if (false === $pageLanguageModel->save($runValidation)) {
				return $pageLanguageModel->flattenErrors(', ');
			}

			// 初始化页面模板数据
			if ($pageId && !empty($item['tpl_id'])) {
				$commonPageTplComponent = new CommonPageTplComponent();
				$commonPageTplComponent->initPageTpl($pageId, $item['tpl_id'], $lang);
			}
		}

		return true;
	}

	/**
	 * 删除自定义页面
	 *
	 * @param      $pageModel
	 * @param bool $runValidation
	 *
	 * @return array
	 */
	public function doDelete ($pageModel, $runValidation = true)
	{
		// 只修改状态
		$pageModel->is_delete = PageModel::IS_DELETE;
		if (false === $pageModel->update($runValidation)) {
			return app()->helper->arrayResult(2, '删除失败');
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($pageModel->id);

		ObsComponent::deletePage($pageModel->id); //删除obs关联表信息

		return app()->helper->arrayResult(0, '删除成功');
	}

	/**
	 * 活动审核(status可为2/4)
	 *
	 * @param int    $pageId 活动ID
	 * @param int    $status 活动要变更的状态
	 * @param string $lang   语言代码简称
	 *
	 * @return array
	 * @throws \Throwable
	 * @throws \Exception
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 */
	public function verify ($pageId, $status, $lang = '')
	{
		ignore_user_abort(true);

		$checkRes = $this->beforeVerifyPage($pageId, $status);
		if ($checkRes['code']) {
			return $checkRes;
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($pageId);

		/** @var \app\modules\common\models\PageModel $pageModel */
		$pageModel = $checkRes['data']['model'];
		$pageModel->status = $status;
		if (!empty($pageModel->activity_id)) {
			$pageModel->auto_refresh = $pageModel::AUTO_REFRESH;
		}
		$pageModel->verify_user = app()->user->username;
		$pageModel->verify_time = time();
		$operate = $this->pageVerifyOperate($pageModel, $pageId);
		if (!empty($operate)) {
			return app()->helper->arrayResult($this->codeFail, $operate['msg'], $operate['data']);
		}
		if (false === $pageModel->update(true)) {
			return app()->helper->arrayResult($this->codeFail, '操作失败');
		}
		$data = $pageModel::getPageUrls($pageModel->activity_id, $pageModel->id, $lang);

		return app()->helper->arrayResult($this->codeSuccess, '操作成功', $data);
	}

	/**
	 * 活动子页面上下线操作
	 *
	 * @param $pageModel
	 * @param $pageId
	 *
	 * @return array|string
	 */
	private function pageVerifyOperate ($pageModel, $pageId)
	{
		$activityId = isset($pageModel->activity_id) ? $pageModel->activity_id : 0;
		// 页面上线，生成上线文件并推送S3
		if ($pageModel::PAGE_STATUS_HAS_ONLINE === $pageModel->status) {
			list($success, $data) = $this->batchCreateOnlinePageHtml([$pageId], $activityId);
			if (!$success) {
				return ['msg' => '页面上线失败', 'data' => $data];
			}
			if (
				!empty($pageModel->activity_id)
				&& false === ActivityModel::changeOnlineActivity(
					$pageModel->activity_id,
					$pageModel::PAGE_STATUS_HAS_ONLINE
				)
			) {
				return ['msg' => '活动上线操作失败', 'data' => []];
			}
		}

		// 页面下线，生成下线文件并推送S3
		if ($pageModel::PAGE_STATUS_HAS_OFFLINE === $pageModel->status) {
			list($success, $data) = $this->batchCreateOfflinePageHtml([$pageId], $activityId);
			if (!$success) {
				return ['msg' => '页面下线失败', 'data' => $data];
			}
		}

		return '';
	}

	/**
	 * 获取页面信息
	 *
	 * @param int    $id   页面ID
	 * @param string $lang 语言代码简称
	 *
	 * @return array
	 */
	public function get ($id, $lang)
	{
		$data = PageModel::getPageInfo($id, $lang);

		if (empty($data)) {
			return app()->helper->arrayResult($this->codeFail, '页面信息获取失败', $data);
		}

		return app()->helper->arrayResult(0, 'success', $data);
	}

	/**
	 * 检查页面状态是否能刷新
	 *
	 * @param int $pageId 页面ID
	 * @param int $status 页面状态，传值时不会去查数据库
	 *
	 * @return bool|string
	 */
	public static function checkPageCanRefresh ($pageId, $status = 0)
	{
		if (!$status && ($model = PageModel::findOne($pageId))) {
			$status = $model->status;
		}

		if (!\in_array($status, [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_TO_BE_OFFLINE], true)) {
			return '页面还未上线，无法刷新';
		}

		return true;
	}

	/**
	 * 刷新页面
	 *
	 * @param int $pageId 页面ID
	 *
	 * @return array
	 */
	public function refresh ($pageId)
	{
		if (empty($pageId)) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}

		if (!$pageModel = PageModel::findOne(['id' => $pageId, 'is_delete' => PageModel::NOT_DELETE])) {
			throw new JsonResponseException($this->codeFail, '页面不存在或已被删除');
		}

		if (true !== ($checkRes = static::checkPageCanRefresh($pageId, $pageModel->status))) {
			throw new JsonResponseException($this->codeFail, $checkRes);
		}

		if (true !== ($checkActivity = ActivityModel::isEnabled($pageModel->activity_id, true))) {
			throw new JsonResponseException($this->codeFail, $checkActivity['message']);
		}

		// 生成静态文件
		list($success, $data) = $this->batchCreateOnlinePageHtml([$pageId], $pageModel->activity_id, true);
		if (!$success) {
			throw new JsonResponseException($this->codeFail, '刷新失败', $data);
		}

		return app()->helper->arrayResult($this->codeSuccess, '刷新成功');
	}

	/**
	 * 发布文件差异对比记录列表
	 *
	 * @param int    $pageId 页面ID
	 * @param string $lang   语言代码简称
	 *
	 * @return array
	 */
	public function diffList ($pageId, $lang)
	{
		$query = PagePublishLogModel::find()
			->where([
				'log_type' => PagePublishLogModel::LOG_TYPE_CREATE,
				'page_id'  => $pageId,
				'lang'     => $lang
			]);

		$count = $query->count();
		$pagination = Pagination::new($count);

		$list = $query
			->orderBy('id desc')
			->limit($pagination->limit)
			->offset($pagination->offset)
			->all();

		$data = $list ? ArrayHelper::toArray($list) : [];

		return app()->helper->arrayResult(
			0,
			'success',
			[
				'list'       => $data,
				'pagination' => [
					$pagination->pageParam     => (int)$pagination->page + 1,
					$pagination->pageSizeParam => (int)$pagination->pageSize,
					'totalCount'               => (int)$pagination->totalCount
				]
			]
		);
	}

	/**
	 * 查询文件差异详情
	 *
	 * @param int $id 差异记录ID
	 *
	 * @return string
	 */
	public function diffInfo ($id)
	{
		$log = PagePublishLogModel::findOne($id);

		$diff = $log ? json_decode($log['diff'], true) : [];

		$html = '';
		if (!empty($diff['pre_file']) && file_exists($diff['pre_file'])) {
			// 若没有下个历史文件时，则当前文件就是最后一个
			$nextFile = $diff['current_file'];
			// 查找下一个历史文件
			$params = [
				'log_type'  => $log->log_type,
				'page_id'   => $log->page_id,
				'lang'      => $log->lang,
				'file_type' => $log->file_type
			];
			if ($nextLog = PagePublishLogModel::getNextDiffLog($id, $params)) {
				$nextDiff = $nextLog ? json_decode($nextLog['diff'], true) : [];
				if (!empty($nextDiff['pre_file']) && file_exists($nextDiff['pre_file'])) {
					$nextFile = $nextDiff['pre_file'];
				}
			}
			$html = $this->getDiffContent(
				file_get_contents($diff['pre_file']),
				file_get_contents($nextFile)
			);

			if (false !== stripos($html, static::EMPTY_DIFF_HTML_END)) {
				$html = str_replace(static::EMPTY_DIFF_HTML_END, static::EMPTY_DIFF_HTML_END_DEFAULT, $html);
			}
		}

		return $html;
	}

	/**
	 * 获取自动刷新时间间隔列表选项
	 *
	 * @return array
	 */
	public function refreshList ()
	{
		return app()->helper->arrayResult(
			$this->codeSuccess,
			$this->msgSuccess,
			$this->refreshList
		);
	}

	/**
	 * 按站点刷新页面(即一键刷新站点头尾)
	 *
	 * @param string $siteCode      站点siteCode
	 * @param int    $logId         日志记录ID
	 * @param string $pageIdStrings 页面ID，多个用英文逗号分隔
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function refreshSitePage ($siteCode, $logId, $pageIdStrings)
	{
		$timestamp = time();
		if (!\in_array($siteCode, array_keys(app()->params['sites'], true))) {
			throw new JsonResponseException($this->codeFail, 'site_code参数超出范围');
		}

		$module = app()->controller->module->id;
		$place = $module === 'activity' ? SiteUpdateLogModel::PLACE_ACTIVITY : SiteUpdateLogModel::PLACE_HOME;
		$lockKey = app()->redisKey->getRefreshSiteTaskLockKey($siteCode, $module);
		if (app()->redis->exists($lockKey) && (app()->redis->get($lockKey) > 0)) {
			\Yii::info('任务正在执行中，请先等待。\n一键刷新站点头尾请求End：' . $timestamp, __METHOD__);
			throw new JsonResponseException($this->codeFail, $siteCode . '操作正在进行中，无需再次操作');
		} else {
			app()->redis->setex($lockKey, 30, 1);
		}

		$failedIds = SiteUpdateLogModel::getFailedPageIds($logId);
		$pageIdArrs = !empty($pageIdStrings) ? explode(',', $pageIdStrings) : [];
		$pageIdArrs = array_intersect($failedIds, $pageIdArrs);
		$count = PageModel::getActivityIdsCountBySite($siteCode, $pageIdArrs, $place);
		if (!empty($count)) {
			// 记录操作日志
			$logResult = '总页面数：' . $count . '，推送成功数：0，推送失败数：0';
			$siteUpdateLog = new SiteUpdateLogModel();
			$siteUpdateLog->site_code = $siteCode;
			$siteUpdateLog->place = $place;
			$siteUpdateLog->parent_id = $logId;
			$siteUpdateLog->page_ids = $pageIdStrings;
			$siteUpdateLog->status = SiteUpdateLogModel::STATUS_PROCESSING;
			$siteUpdateLog->result = $logResult;
			if (!$siteUpdateLog->insert(true)) {
				// 删除锁
				app()->redis->del($lockKey);
				throw new JsonResponseException($this->codeFail, '操作日志记录失败');
			}

			$this->obFlush('任务发送成功，请去日志中查看结果');

			ignore_user_abort(true);
			set_time_limit(300);

			\Yii::info('一键刷新站点头尾请求Start：' . $timestamp, __METHOD__);

			$returnData = [
				'success_count' => 0,
				'fail_count'    => 0,
				'detail'        => []
			];

			$pageSize = 100;
			$page = ceil($count / $pageSize);
			app()->redis->setex($lockKey, 3600 * 12, $count);
			for ($start = 0; $start < $page; $start++) {
				$list = PageModel::getActivityIdsBySite($siteCode, $pageIdArrs, $place, $start * $pageSize, $pageSize);
				if (!empty($list) && is_array($list)) {
					app()->arrayCache->set('redis_data_multi_array', json_encode([]));
					foreach ($list as $value) {
						try {
							list($success, $errorMsg) = $this->batchCreateOnlinePageHtml(
								[$value['page_id']],
								$value['activity_id'],
								true,
								true,
								false,
								'',
								SiteConstants::HOME_PAGE_TYPE_UNKNOWN,
								[$value['lang']]
							);
						} catch (\Exception $e) {
							$success = false;
							$errorMsg = [
								[
									'page_id'      => $value['page_id'],
									'lang'         => $value['lang'],
									'message'      => $e->getMessage(),
									'status'       => SiteUpdateLogModel::PAGE_FAILED,
									'success_time' => 0
								]
							];
						}

						if ($success) {
							$returnData['success_count']++;
						} else {
							//$langPageCount = PageLanguageModel::getAllLangPageForPageId($value['id']);
							app()->redis->decr($lockKey);
							$returnData['fail_count']++;
							!empty($errorMsg) && array_push($returnData['detail'], ...$errorMsg);
						}
					}

					$pushPageArray = app()->arrayCache->get('redis_data_multi_array');
					if (!empty($pushPageArray)) {
						$pushPageArray = json_decode($pushPageArray, true);
						if (true !== $this->CrontabComponent->asyncRefreshPage($pushPageArray, $lockKey, $siteUpdateLog->id)) {
							$returnData['fail_count'] += count($pushPageArray);
						}
					}
				}
			}

			$siteUpdateLog = SiteUpdateLogModel::getById($siteUpdateLog->id);
			list($totalNum, $successNum, $failNum) = explode('，', $siteUpdateLog->result);
			$failCount = substr($failNum, strpos($failNum, '：') + 3, strlen($failNum)) + $returnData['fail_count'];
			$message = "{$totalNum}，{$successNum}，推送失败数：" . $failCount;
			// 更新操作日志结果
			$siteUpdateLog->result = $message;
			if (!empty($siteUpdateLog->detail)) {
				$detailData = json_decode($siteUpdateLog->detail, true);
				$returnData['detail'] = array_merge($detailData, $returnData['detail']);
			}
			$siteUpdateLog->detail = json_encode($returnData['detail']);
			if ($returnData['fail_count'] == $count) {
				$siteUpdateLog->status = SiteUpdateLogModel::STATUS_COMPLETED;
				$siteUpdateLog->complete_time = time();
				app()->redis->del($lockKey);
			}
			$siteUpdateLog->update();

			if (!empty($logId) && !empty($pageIdArrs) && $returnData['success_count'] > 0) {
				// 重新发送的，则更新历史记录中的
				$successPageIds = array_diff($pageIdArrs, array_column($returnData['detail'], 'page_id'));
				SiteUpdateLogModel::updateParentLog($logId, $successPageIds);
			}

			\Yii::info('一键刷新站点头尾请求End：' . $timestamp . '----' . $message, __METHOD__);
		} else {
			$this->obFlush('没有需要刷新的页面.');
		}

		// 设置isSent为true来阻止response->send()方法，否则会报HeadersAlreadySentException，因为在obFlush()中已经有输出了
		app()->response->isSent = true;
	}

	/**
	 * 先返回信息给前端，然后后端继续处理
	 *
	 * @param string $message 返回信息
	 */
	private function obFlush ($message)
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
	 * 查询一键更新站点头尾任务列表
	 *
	 * @param string $siteCode 站点siteCode
	 *
	 * @return array
	 */
	public function refreshSiteLogList ($siteCode)
	{
		if (!\in_array($siteCode, array_keys(app()->params['sites'], true))) {
			throw new JsonResponseException($this->codeFail, 'site_code参数超出范围');
		}

		$query = SiteUpdateLogModel::find()->alias('l')->where(['l.site_code' => $siteCode]);

		$count = $query->count();
		$pagination = Pagination::new($count);

		$list = $query->select('l.*, a.realname as create_name')
			->leftJoin(AdminModel::tableName() . ' as a', 'l.create_user = a.username')
			->orderBy('l.id desc')
			->limit($pagination->limit)
			->offset($pagination->offset)
			->all();

		$data = [];
		if ($list) {
			$data = ArrayHelper::toArray($list);

			foreach ($data as $key => $item) {
				unset($data[$key]['detail']);
				$data[$key]['title'] = static::SITE_TASK_TITLE;
			}
		}

		return app()->helper->arrayResult(
			$this->codeSuccess,
			$this->msgSuccess,
			[
				'list'       => $data,
				'pagination' => [
					$pagination->pageParam     => (int)$pagination->page + 1,
					$pagination->pageSizeParam => (int)$pagination->pageSize,
					'totalCount'               => (int)$pagination->totalCount
				]
			]
		);
	}

	/**
	 * 查询一键更新站点头尾任务详情
	 *
	 * @param id $siteCode 站点siteCode
	 */
	public function refreshSiteLogDetail (int $id, int $status = 0)
	{
		$log = SiteUpdateLogModel::findOne($id);
		if (!$log) {
			throw new JsonResponseException($this->codeFail, '非法的ID');
		}

		$detail = !empty($log->detail) ? json_decode($log->detail, true) : [];
		if (!empty($detail) && !empty($detail[0])) {
			$pageIds = array_column($detail, 'page_id');
			$pageInfos = PageModel::getActivityInfosByPageIds($pageIds);
			$pageInfos = array_column($pageInfos, null, 'id');
			$this->buildLogDetail([$pageInfos, $status, $log->place], $detail);
		}
		$detail = array_values($detail);
		$count = \count($detail);
		$pagination = Pagination::new($count);

		return app()->helper->arrayResult(
			$this->codeSuccess,
			$this->msgSuccess,
			[
				'list'       => array_slice($detail, $pagination->page * $pagination->pageSize, $pagination->pageSize, false),
				'pagination' => [
					$pagination->pageParam     => (int)$pagination->page + 1,
					$pagination->pageSizeParam => (int)$pagination->pageSize,
					'totalCount'               => (int)$pagination->totalCount
				]
			]
		);
	}

	/**
	 * 处理日志详情
	 *
	 * @param $params
	 * @param $detail
	 */
	protected function buildLogDetail ($params, &$detail)
	{
		list($pageInfos, $status, $place) = $params;
		foreach ($detail as $k => $v) {
			if (!empty($pageInfos[$v['page_id']])
				&& (int)$pageInfos[$v['page_id']]['status'] !== PageModel::PAGE_STATUS_HAS_ONLINE
			) {
				//非上线页面过滤掉
				unset($detail[$k]);
				continue;
			}
			if ($status === SiteUpdateLogModel::PAGE_SUCCESS
				&& !(isset($v['status']) && (int)$v['status'] === $status)
			) {
				//查询成功的时，过滤掉失败的
				unset($detail[$k]);
				continue;
			}
			if ($status === SiteUpdateLogModel::PAGE_FAILED && isset($v['status'])
				&& (int)$v['status'] === SiteUpdateLogModel::PAGE_SUCCESS
			) {
				//查询失败的时，过滤掉成功的
				unset($detail[$k]);
				continue;
			}
			!isset($v['status']) && $detail[$k]['status'] = SiteUpdateLogModel::PAGE_FAILED;
			!isset($v['success_time']) && $detail[$k]['success_time'] = 0;
			$detail[$k]['page_name'] = $pageInfos[$v['page_id']]['title'];
			$detail[$k]['activity_id'] = $pageInfos[$v['page_id']]['activity_id'];
			$detail[$k]['activity_name'] = $pageInfos[$v['page_id']]['name'];
			$detail[$k]['lang'] = !empty($detail[$k]['lang']) ? app()->params['lang'][$detail[$k]['lang']]['name'] : '';
			$detail[$k]['place'] = $place;
		}
	}

	/**
	 * 首页权限加/解锁
	 *
	 * @param      $model
	 * @param bool $runValidation
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function doLock ($model, $runValidation = true)
	{
		if (1 === (int)$model->is_lock) {
			$actMsg = '加锁';
			$model->is_lock = PageModel::IS_LOCK;
		} else {
			$actMsg = '解锁';
			$model->is_lock = PageModel::UN_LOCK;
		}

		if (false === $model->update($runValidation)) {
			throw new JsonResponseException($this->codeFail, "{$actMsg}失败");
		}

		return app()->helper->arrayResult(0, "{$actMsg}成功");
	}

	/**
	 * 检查页面是否发布过
	 * !!!子类需要复写这个方法，本来应该定义为抽象方法的，但这个类在其它地方有被实例化，所以这里只标注下!!!
	 *
	 * @param PageModel $pageModel
	 * @param array     $data
	 */
	public function checkPagePublished ($pageModel, &$data)
	{
	}

	/**
	 * 获取domainKey，用来区分首页和活动页
	 * !!!子类需要复写这个方法，本来应该定义为抽象方法的，但这个类在其它地方有被实例化，所以这里只标注下!!!
	 */
	public function getDomainKey ()
	{
	}

	/**
	 * 获取页面和活动信息
	 * !!!子类需要复写这个方法，本来应该定义为抽象方法的，但这个类在其它地方有被实例化，所以这里只标注下!!!
	 *
	 * @param int $pageId
	 */
	public function getPageActivityInfo (int $pageId)
	{
	}

	/**
	 * 获取页面最新访问链接地址
	 *
	 * @param int $pageId
	 * @param int $needBtn 返回tips中是否包含btn按钮提示
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getPageNewestUrls (int $pageId, int $needBtn = 1)
	{
		$data = ['list' => [], 'tips' => '', 'total' => 0];
		$page = $this->getPageActivityInfo($pageId);
		if (!$page) {
			throw new JsonResponseException($this->codeFail, '无效的页面ID', $data);
		}
		$this->checkPagePublished($page, $data);

		if ($page->status === PageModel::PAGE_STATUS_HAS_ONLINE_B) {
			return $this->getHomebNewestUrls($pageId, $needBtn);
		}

		$files = PagePublishLogModel::getPageNewestPublishLog($pageId, $page->status);
		if (!$files) {
			throw new JsonResponseException($this->codeFail, '数据错误，未找到页面的发布记录', $data);
		}
		$data['total'] = count(array_unique(array_column($files, 'lang')));

		// 按照语言发布获取多个版本
		$versions = array_column($files, 'version');
		$pushs = PagePublishLogModel::getPageNewestPushLog($pageId, $versions);

		$siteCode = $page->site_code;
		$tips = $errorLang = $successLang = [];
		$langConf = app()->params['lang'];
		$siteConf = app()->params['sites'][$siteCode];
		$domainKey = $this->getDomainKey();
		$pushs = $pushs ? array_column($pushs, null, 'file_hash') : [];
		foreach ($files as $file) {
			if (empty($siteConf[$domainKey][$file['lang']])) {
				continue;
			}
			if (\array_key_exists($file['file_hash'], $pushs) && !\in_array($file['lang'], $successLang)
				&& !\in_array($file['lang'], $errorLang)
			) {
				$successLang[] = $file['lang'];
				$domain = $siteConf[$domainKey][$file['lang']];
				$data['list'][] = [
					'lang'      => $file['lang'],
					'lang_name' => $langConf[$file['lang']]['name'],
					'page_url'  => $domain . $file['page_url']
				];
			} elseif (!\in_array($file['lang'], $errorLang) && !\in_array($file['lang'], $successLang)) {
				$errorLang[] = $file['lang'];
				$tips[] = $langConf[$file['lang']]['name'];
			}
		}
		if (!empty($tips)) {
			$data['tips'] = implode('、', $tips) . '页面还在推送中' . ($needBtn ? '，若长时间未成功，请' : '');
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 获取AB测试页B的最新访问链接地址
	 *
	 * @param int $pageId
	 * @param int $needBtn 返回tips中是否包含btn按钮提示
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getHomebNewestUrls (int $pageId, int $needBtn = 1)
	{
		$data = ['list' => [], 'tips' => '', 'total' => 0];
		$page = $this->getPageActivityInfo($pageId);
		if (!$page) {
			throw new JsonResponseException($this->codeFail, '无效的页面ID', $data);
		}
		$this->checkPagePublished($page, $data);

		$files = PagePublishLogModel::getHomebNewestPublishLog($pageId, $page->status);
		if (!$files) {
			throw new JsonResponseException($this->codeFail, '数据错误，未找到页面的发布记录', $data);
		}
		$data['total'] = count(array_unique(array_column($files, 'lang')));

		// 按照语言发布获取多个版本
		$versions = array_column($files, 'version');
		$pushs = PagePublishLogModel::getPageNewestPushLog($pageId, $versions);

		$siteCode = $page->site_code;
		$tips = $errorLang = $successLang = [];
		$langConf = app()->params['lang'];
		$siteConf = app()->params['sites'][$siteCode];
		$domainKey = $this->getDomainKey();
		$pushs = $pushs ? array_column($pushs, null, 'file_hash') : [];
		foreach ($files as $file) {
			if (\array_key_exists($file['file_hash'], $pushs) && !\in_array($file['lang'], $successLang)
				&& !\in_array($file['lang'], $errorLang)
			) {
				$successLang[] = $file['lang'];
				$domain = $siteConf[$domainKey][$file['lang']];
				$data['list'][] = [
					'lang'      => $file['lang'],
					'lang_name' => $langConf[$file['lang']]['name'],
					'page_url'  => $domain . $file['page_url']
				];
			} elseif (!\in_array($file['lang'], $errorLang) && !\in_array($file['lang'], $successLang)) {
				$errorLang[] = $file['lang'];
				$tips[] = $langConf[$file['lang']]['name'];
			}
		}
		if (!empty($tips)) {
			$data['tips'] = implode('、', $tips) . '页面还在推送中' . ($needBtn ? '，若长时间未成功，请' : '');
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}
}
