<?php

namespace app\modules\common\zf\components;

use app\components\site\zf\PagePreview;
use app\modules\activity\zf\components\SyncPlatformComponent;
use app\modules\base\models\AdminSitePrivilegeModel;
use ego\enums\Platform;
use app\modules\common\zf\models\{
	PageGroupModel,
	PageLanguageModel,
	PageModel,
	ActivityModel,
	PageLayoutModel,
	PageOperateLogModel,
	PagePublishLogModel,
	PageSyncPlatformWaitDataModel,
	PageUiComponentDataModel,
	PageUiModel,
	PageUiTemplateModel
};

use app\modules\common\zf\traits\{
	CommonVerifyStatusTrait, CommonPublishTrait, CommonConvertTrait
};
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\component\models\UiTplModel;
use app\modules\base\components\AccessLogComponent;
use app\modules\component\zf\components\ManagerComponent;
use app\modules\component\models\ComponentModel;
use ego\base\JsonResponseException;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use yii\helpers\ArrayHelper;
use yii\db\Exception;
use yii\web\Response;

/**
 * 页面装修设计-整个页面相关
 *
 * @property \app\modules\activity\zf\components\PageComponent        $PageComponent
 * @property \app\modules\activity\zf\components\PageTplComponent     $PageTplComponent
 * @property \app\modules\activity\zf\components\GoodsManageComponent $GoodsManageComponent
 * @property \app\modules\common\zf\components\CommonCrontabComponent $CrontabComponent
 */
class CommonPageDesignComponent extends Component
{
	use CommonVerifyStatusTrait, CommonPublishTrait, CommonConvertTrait;

	//表单文件名
	const FORM_NAME = 'form.twig';

	//参数验证正确返回的消息
	const SUCCESS_MSG = '';

	//权限检查缓存key前缀
	const AUTH_CACHE_KEY_PRE = 'geshop::page::auth::';

	//权限检查缓存时间
	const AUTH_CACHE_TIME = 60;

	//page_id字段
	const FIELD_PAGE_ID = 'page_id';

	//权限检查缓存key前缀
	const PAGE_TPL_CACHE_KEY_PRE = 'geshop::page::tpl::cache::';

	//为了让在controller中不直接引用model，这里转换下
	const TYPE_PC = ActivityModel::TYPE_PC;
	const TYPE_MOBILE = ActivityModel::TYPE_MOBILE;
	const TYPE_APP = ActivityModel::TYPE_APP;

	/**
	 * @var array 包含SKU的ui组件key
	 */
	public $uiKeyIncludeSku ;

	/**
	 * @var \app\modules\common\zf\models\PageModel 页面信息
	 */
	public $pageInfo;

	//忽略权限检查的路由
	private $authCheckIgnoreRoute;

	//忽略修改页面自动刷新属性的路由
	private $changeRefreshIgnoreRoute;

	//新版本缓存前缀【防止各站点间页面冲突】
	public  $redisPrefix;


	public function init ()
	{
		parent::init();
		$this->authCheckIgnoreRoute = [
			'activity/zf/design/index',
			'activity/zf/design/preview',
			'home/zf/design/index',
			'home/zf/design/preview',
			'advertisement/zf/design/index',
			'advertisement/zf/design/preview',
			'activity/zf/design/snapshot',
			'home/zf/design/snapshot',
			'advertisement/zf/design/snapshot'
		];
		$this->changeRefreshIgnoreRoute = [
			'activity/zf/design/index',
			'activity/zf/design/preview',
			'activity/zf/design/release',
			'activity/zf/design/get-setting',
			'activity/zf/layout-design/get-layout-form',
			'activity/zf/ui-design/get-form',
		];

		$websiteCode = (defined('SITE_GROUP_CODE_FIXED') && !empty(SITE_GROUP_CODE_FIXED))
			? SITE_GROUP_CODE_FIXED
			: SITE_GROUP_CODE;
		$this->redisPrefix = $websiteCode . '::';

		$this->uiKeyIncludeSku = app()->params['conponentForSkuCopy'];

		$moduleId = strtolower(app()->controller->module->module->id);
		if (!empty($_REQUEST[static::FIELD_PAGE_ID]) && \in_array($moduleId, ['activity', 'advertisement'], true)) {
			$this->pageInfo = PageModel::getPageActivityInfo((int)$_REQUEST[static::FIELD_PAGE_ID]);
		}
		if (!empty($_REQUEST[static::FIELD_PAGE_ID]) && 'home' === $moduleId) {
			$this->pageInfo = PageModel::getById((int)$_REQUEST[static::FIELD_PAGE_ID]);
		}

		if (!empty($this->pageInfo)) {
			// 访问日志关联页面id
			AccessLogComponent::addPageId($this->pageInfo->id);
		}
	}

	/**
	 * 检查访问权限
	 *
	 * @param int    $pageId 页面ID
	 * @param string $route  访问路由
	 *
	 * @return bool
	 * @throws JsonResponseException
	 */
	public function checkAuth ($pageId, $route)
	{
		if ($this->ignoreCheckRoute($route)) {
			return true;
		}

		if (empty($pageId)) {
			throw new JsonResponseException($this->codeFail, 'page_id不能为空');
		}

		$redisKey = $this->redisPrefix . app()->redisKey->getDesignPageLockKey($pageId);
		$data = json_decode(app()->redis->get($redisKey), true);
		if (empty($data['id'])) {
			$user = ArrayHelper::toArray(app()->user->identity);
			if (!empty($user['admin'])) {
				app()->redis->setex($redisKey, static::AUTH_CACHE_TIME, json_encode($user['admin']));
			}
		} elseif ($data['id'] === app()->user->id) {
			app()->redis->expire($redisKey, static::AUTH_CACHE_TIME);
		} else {
			//只返回给前端有限的字段信息，敏感信息不返回
			$returnData = [
				'id'            => $data['id'],
				'department_id' => $data['department_id'],
				'username'      => $data['username'],
				'realname'      => $data['realname'],
				'user_no'       => $data['user_no']
			];
			$message = '当前页面有其他人正在操作：' . ($returnData['realname'] ?: $returnData['username'])
				. '，请于' . (int)(static::AUTH_CACHE_TIME / 60) . '分钟后再尝试操作';
			throw new JsonResponseException($this->codeFail, $message, $returnData);
		}

		return true;
	}

	/**
	 * 是否忽略权限检查
	 *
	 * @param string $route 访问路由
	 *
	 * @return bool
	 */
	private function ignoreCheckRoute ($route)
	{
		if (\in_array($route, $this->authCheckIgnoreRoute, true)) {
			return true;
		}

		return false;
	}

	/**
	 * 检查访问权限
	 *
	 * @param int    $pageId 页面ID
	 * @param string $route  访问路由
	 */
	public function changePageAutoRefresh ($pageId, $route)
	{
		if (!\in_array($route, $this->changeRefreshIgnoreRoute, true)) {
			$pageModel = PageModel::findOne($pageId);
			if ($pageModel) {
				$pageModel->auto_refresh = 0;
				$pageModel->save(true);
			}
		}
	}

	/**
	 * 获取首页需要的数据
	 *
	 * @param $pageId
	 * @param $type
	 * @param $siteCode
	 * @param $lang
	 * @param $place
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public function getDesignData ($pageId, $type, $siteCode, $lang, $place)
	{
		//获取可用的组件列表
		$component = new ManagerComponent();
		$data = $component->getAvailableList(
			[$type, ComponentModel::RANGE_RESPONSIVE],
			$place,
			$siteCode,
			$lang
		);

		//自定义布局组件编码
		$customKey = empty($data['custom']) ? 0 : $data['custom']->component_key;

		//解析页面
		$pageHtml = $this->parsePage($pageId, $lang);

		return ['data' => $data, 'customKey' => $customKey, 'pageHtml' => $pageHtml];
	}

	/**
	 * 获取用户组件模板列表
	 *
	 * @param string $username
	 * @param string $siteCode
	 * @param int    $placeType
	 * @param string $lang
	 */
	public function getUserUiTemplateList ($username, $siteCode, $placeType, $lang = null)
	{
		//获取模板列表（private 我的模板， public 系统模板）
		return PageUiTemplateModel::getUserTemplateList($username, $siteCode, $placeType, $lang);
	}

	/**
	 * 获取页面关联关系
	 *
	 * @param int    $pageId
	 * @param string $lang
	 *
	 * @return array
	 * @throws \yii\base\InvalidArgumentException
	 */
	public function getRelationList (int $pageId, string $lang)
	{
		$pageActivityInfo = PageModel::getPageActivityInfo($pageId);
		$siteSuffix = $pageActivityInfo ? explode('-', $pageActivityInfo->site_code)[1] : '';

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
			'current' => $siteSuffix,
			'list'    => $this->getConvertRelationList($pageId, $lang, $pageActivityInfo->site_code)
		]);
	}

	/**
	 * 预览页面
	 *
	 * @param string $pid  页面32位ID
	 * @param string $lang 语种
	 * @param bool   $useCache
	 *
	 * @return string
	 * @throws \Throwable
	 */
	public function preview ($pid, $lang, $useCache = false)
	{
		if (empty($pid) || empty($lang)) {
			return '参数不全';
		}

		if (!($pageModel = PageModel::getByPId($pid))) {
			return '页面不存在或已被删除';
		}

		$pagePreview = new PagePreview();

		return $pagePreview->getDesignPreview($pageModel, $lang, $useCache);
	}

	/**
	 * 【批量发布】发布活动页面
	 *
	 * @param string $batchData
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function activityBatchRelease ($batchData, $groupId = '')
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
		if (!empty($pages)) {
			$onlinePipelineCodes = array_column($pages, 'pipeline');
			$this->PageComponent->checkUserSpecialPublishPermissions($onlinePipelineCodes, $pages[0]['site_code']);
		}

		$pages = $pages ? array_column($pages, null, 'pipeline') : [];
		$data = [];

		try {
			defined('PUSHSYNC') or define('PUSHSYNC', app()->swoole->init()->isConnected());
		} catch (\Exception $e) {
			app()->rms->reportS3PushError($e->getMessage());
			defined('PUSHSYNC') or define('PUSHSYNC', false);
		}

		app()->arrayCache->set('redis_data_multi_array', json_encode([]));
		foreach ($batchArr as $item) {
			$item['page_id'] = $pages[$item['pipeline']]['id'] ?? 0;
			if (!$item['page_id']) {
				continue;
			}
           //解析页面生成静态文件(push-page的发布机制在这里)
			$res = $this->activityRelease($item['page_id'], $item['lang']);
			if ($res['code'] !== $this->codeSuccess) {
				return $res;
			}
			$data[$item['page_id']] = $res['data'];
		}

		$pushPageArray = app()->arrayCache->get('redis_data_multi_array');
		if (!empty($pushPageArray)) {
			$pushPageArray = json_decode($pushPageArray, true);
			//使用swoole推送机制
			$pushState = $this->CrontabComponent->asyncPushPage($pushPageArray, true);
			if (true !== $pushState) {
				return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $pushState);
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, '发布成功', $data);
	}


	/**
	 * 【三端批量发布】发布活动页面
	 *
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function activityFullBatchRelease ($params = [])
	{
		if (empty($params['page_id'])) {
			throw new JsonResponseException($this->codeFail, '参数不完整');
		}

		//查找相应的渠道和语言站


		$page_id = $params['page_id'];
		$page_group_id = PageGroupModel::getPageGroupIdByPageId($page_id);

		//要取勾选的渠道和端口
		$page_wait_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id' => $page_group_id]);
		$select_pipeline = [];
		$select_platform = [];
		if (!empty($page_wait_data)) {
			$select_pipeline = explode(',', $page_wait_data->pipeline);
			$select_platform = explode(',', $page_wait_data->platform);
		}

		//和用户的权限取交集

		//如果没有查到redis，则去表查询
		if (!app()->user->admin->is_super) {
			$admin = AdminSitePrivilegeModel::findOne(['user_id' => app()->user->admin->id, "website_code" => 'zf']);
			if (!empty($admin)) {
				$select_pipeline = array_intersect($select_pipeline, json_decode($admin->special_permissions, 1));
			} else {
				$select_pipeline = [];
			}
		}

		//记录日志
		$log_platform = [];
		$platform = $select_platform;
		foreach ($platform as $item) {
			$tmp['data'] = $item;
			$tmp['operate'] = '';
			$log_platform[] = $tmp;
		}

		$log_pipeline = [];
		$pipeline = $select_pipeline;
		foreach ($pipeline as $item) {
			$tmp['data'] = $item;
			$tmp['operate'] = '';
			$log_pipeline[] = $tmp;
		}

		$log_params['platform'] = $log_platform;
		$log_params['pipeline'] = $log_pipeline;

		$log_params['form_data'] = json_decode($page_wait_data['form_data'], 1);
		$log_params['page_id'] = $params['page_id'];
		//记录操作日志
		(new SyncPlatformComponent())->saveLog(PageOperateLogModel::TYPE_SYNC, $log_params);

		//获取平台的类型 数字
		$select_platform = array_map(function ($item) {
			return SitePlatform::getPlatformTypeByPlatformCode($item);
		}, $select_platform);


		//所有关联的页面id
		$page_ids = PageGroupModel::find()
			->where(['page_group_id' => $page_group_id])
			->andWhere(['in', 'pipeline', $select_pipeline])
			->andWhere(['in', 'platform_type', $select_platform])
			->asArray()->all();
		$page_ids = array_column($page_ids, 'page_id');
		$pages = PageModel::find()->where(['id' => $page_ids])->asArray()->all();
		$page_pipelines = array_column($pages, 'pipeline', 'id');
		//查找用户语言
		$batchArr = [];
		foreach ($page_pipelines as $key => $page_pipeline) {
			$page_lang = PageLanguageModel::find()->where(['page_id' => $key])->asArray()->one();
			if (empty($page_lang)) {
				continue;
			}
			$tmp['pipeline'] = $page_pipeline;
			$tmp['lang'] = $page_lang['lang'];
			$batchArr[] = $tmp;
		}
		$batchArr = array_unique($batchArr, SORT_REGULAR);

		// 先校验参数格式
		/** @var array $batchArr */
//        $batchArr = json_decode($batchData, true);
		foreach ($batchArr as $item) {
			if (empty($item['pipeline']) || empty($item['lang'])) {
				throw new JsonResponseException($this->codeFail, '参数格式不正确');
			}
		}

		$page_groups = PageGroupModel::find()->select("page_id as id,pipeline,platform_type")
			->where([
				'page_id' => $page_ids,
			])->asArray()->all();
//        $pages = $pages ? array_column($pages, null, 'pipeline') : [];
		$data = [];
		$err_msg = [];
		foreach ($batchArr as $item) {
			foreach ($page_groups as $page_group) {
				if ($page_group['pipeline'] == $item['pipeline']) {
					$item['page_id'] = $page_group['id'] ?? 0;
					if (!$item['page_id']) {
						continue;
					}

					//校验赠品，预促销，加价购，秒杀组件
					$page_layouts = PageLayoutModel::find()->where(['page_id' => $item['page_id']])
						->asArray()->all();
					$page_layouts = array_column($page_layouts, 'id');
					$page_uis = PageUiModel::find()->where(['layout_id' => $page_layouts])->all();
					$goods = new CommonGoodsComponent();
					foreach ($page_uis as $page_ui) {
						if (in_array($page_ui->tpl_id, [397, 398, 201, 202])) {
							//赠品,加价购
							$tmp['site_code'] = PageModel::getSiteCodeById($item['page_id']);
							$tmp['pipeline'] = $page_group['pipeline'];
							$tmp['lang'] = $item['lang'];
							//查找用户的数据
							$page_ui_datas = PageUiComponentDataModel::find()->where(['component_id' => $page_ui->id])->all();
							if (empty($page_ui_datas)) {
								continue;
							}
							foreach ($page_ui_datas as $page_ui_data) {
								if ($page_ui_data->key == "goodsID") {
									$skus = $page_ui_data->value;
								}
							}
							if (in_array($page_ui->tpl_id, [201, 202])) {
								$value = empty($skus) ? "" : json_decode($skus, 1);
								$tmp['skus'] = empty($value) ? "" : implode(',', array_column($value, "ids"));
							} else {
								$tmp['skus'] = empty($skus) ? "" : implode(',', json_decode($skus, 1));
							}
							if (empty($tmp['skus'])) {
								continue;
							}

							$tmp['pageId'] = $params['page_id'];
							$tmp['api'] = "fullgiftlist";
							if (!empty($content['lang'])) {
								//找到所有的语言
								$content['lang'] = $item['lang'];
							}
							$content['activityid'] = $tmp['skus'];
							$content['pageno'] = 1;
							$content['pagesize'] = 20;
							$content['pipeline'] = $item['pipeline'];
							$tmp['content'] = json_encode($content);

							$diff_skus = $goods->checkGoodsExitsWithAPI($tmp);
							if (!empty($diff_skus)) {
								//需要删除数据
								$del_data = array_diff(explode(',', $tmp['skus']), explode(',', $diff_skus));
								$del_data = empty($del_data) ? "" : json_encode($del_data);
								PageUiComponentDataModel::updateAll(['value' => $del_data], ['component_id' => $page_ui->id, 'key' => 'goodsID']);
							}

						} elseif (in_array($page_ui->tpl_id, [209, 203])) {
							//秒杀商品校验
							$tmp['site_code'] = PageModel::getSiteCodeById($item['page_id']);
							$tmp['pipeline'] = $page_group['pipeline'];
							$tmp['lang'] = $item['lang'];
							//查找用户的数据
							$page_ui_datas = PageUiComponentDataModel::find()->where(['component_id' => $page_ui->id])->all();
							if (empty($page_ui_datas)) {
								continue;
							}
							foreach ($page_ui_datas as $page_ui_data) {
								if ($page_ui_data->key == "goodsSKUTab") {
									$skus = $page_ui_data->value;
								}
							}

							$tmp['pageId'] = $params['page_id'];
							$tmp['api'] = "isseckill";
							$lists = empty($skus) ? "" : json_decode($skus, 1);
							if (empty($lists)) {
								continue;
							}
							$no_effected = [];

							foreach ($lists as &$list) {
								if (!empty($list['lists'])) {
									$tmp['skus'] = $list['lists'];
									if (!empty($content['lang'])) {
										//找到所有的语言
										$content['lang'] = $item['lang'];
									}
									$content['goodsSn'] = $tmp['skus'];

									$content['pipeline'] = $item['pipeline'];
									$tmp['content'] = json_encode($content);

									$diff_skus = $goods->checkIsseckillGoodsExitsWithAPI($tmp);
									//收集不存在的sku
									$no_effected += empty($diff_skus) ? [] : explode(',', $diff_skus);

								}
							}

							//去掉不存在的sku
							foreach ($page_ui_datas as &$page_ui_data) {

								if ($page_ui_data->key == 'skuTab') {
									$value = json_decode($page_ui_data->value, 1);
									foreach ($value as &$value_item) {
										$list = explode(',', $value_item['lists']);
										foreach ($list as $k => &$sku) {
											if (in_array($sku, $no_effected)) {
												unset($list[$k]);
											}
										}
										$list = implode(',', array_values($list));
										$value_item["lists"] = $list;
									}
									$page_ui_data->value = json_encode($value);
									$page_ui_data->save();
								} elseif ($page_ui_data->key == "goodsSKUTab") {
									$value = json_decode($page_ui_data->value, 1);
									foreach ($value as &$value_item) {
										$list = explode(',', $value_item['lists']);
										foreach ($list as $k => &$sku) {
											if (in_array($sku, $no_effected)) {
												unset($list[$k]);
											}
										}
										$list = implode(',', array_values($list));
										$value_item["lists"] = $list;
									}
									$page_ui_data->value = json_encode($value);
									$page_ui_data->save();
								} elseif ($page_ui_data->key == "goodsSKUSort") {
									$value = json_decode($page_ui_data->value, 1);
									foreach ($value as &$value_item) {
										$list = explode(',', $value_item['lists']);
										foreach ($list as $k => &$sku) {
											if (in_array($sku, $no_effected)) {
												unset($list[$k]);
											}
										}
										$list = implode(',', array_values($list));
										$value_item["lists"] = $list;
									}
									$page_ui_data->value = json_encode($value);
									$page_ui_data->save();
								}
							}

						} elseif (in_array($page_ui->tpl_id, [235, 236])) {
							//预促销
							$page_ui_data = PageUiComponentDataModel::find()->where(['component_id' => $page_ui->id, "key" => "goodsSKU"])->one();
							if (!empty($page_ui_data)) {
								$sku = json_decode($page_ui_data->value);
								//校验
								$tmp['site_code'] = PageModel::getSiteCodeById($item['page_id']);;
								$tmp['pipeline'] = $page_group['pipeline'];
								$tmp['lang'] = $page_ui_data->lang;
								$tmp['skus'] = $sku;
								$tmp['pageId'] = $params['page_id'];
								$tmp['api'] = "prepromotion";
								$content['lang'] = $tmp['lang'];
								$content['goodsSn'] = $sku;
								$content['pipeline'] = $tmp['pipeline'];
								$tmp['content'] = json_encode($content);

								$diff_skus = $goods->checkGoodsExitsWithAPI($tmp);
								if (!empty($diff_skus)) {
									//需要删除数据
									$del_data = array_diff(explode(',', $sku), explode(',', $diff_skus));
									$del_data = empty($del_data) ? "" : json_encode(implode(',', $del_data));
									PageUiComponentDataModel::updateAll(['value' => $del_data], ['component_id' => $page_ui->id, 'key' => 'goodsSKU']);
								}
							}
						}
					}

					$res = $this->activityRelease($item['page_id'], $item['lang'], '');
					if ($res['code'] !== $this->codeSuccess) {
						//收集错误信息
						$pipeline = $item['pipeline'];
						$pipeline_name = app()->params['site']['zf']['pipeline'][$pipeline];
						$err_msg[] = SitePlatform::getPlatformNameByCode(SitePlatform::getPlatformCodeByPlatformType($page_group['platform_type'])) . "端口下的" . $pipeline_name . "渠道发布失败!";
//                        return $res;
                    }
                    $data[ $item['page_id'] ] = $res['data'];
                }

            }

        }
        if (!empty($err_msg)) {
            $err_msg = implode('<br />', $err_msg);

            return app()->helper->arrayResult($this->codeFail, $err_msg, $data);
        }

        return app()->helper->arrayResult($this->codeSuccess, '发布成功', $data);
    }

    /**
     * 活动页面发布(生成page的html文件)
     *
     * @param        $pageId
     * @param string $lang
     * @param string $type
     *
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function activityRelease($pageId, $lang, $type = '')
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
            list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], $pageModel->activity_id
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

    /**
     * 【批量】首页面上线(生成page的html文件)
     *
     * @param int    $pageId
     * @param string $pipeline 渠道列表，多个用英文逗号分隔
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function batchHomeOnline($pageId, $pipeline)
    {
        /** @var \app\modules\common\zf\models\PageModel $pageModel */
        $pageModel = PageModel::getById($pageId);
        if (empty($pageModel)) {
            return app()->helper->arrayResult($this->codeSuccess, '页面不存在！');
        }

        // 部分渠道设置为首页
        $conditions = ['group_id' => $pageModel->group_id];
        if (!empty($pipeline)) {
            $pipelineList = explode(',', $pipeline);
        }

        // 第一次必须全渠道发布
       /* $globalPageModel = ($pageModel->pipeline === 'ZF')
            ? $pageModel
            : PageModel::find()->where(['group_id' => $pageModel->group_id, 'pipeline' => 'ZF'])->one();*/

        if (PageModel::PAGE_STATUS_TO_BE_ONLINE == $pageModel->status) {
            $pipelines = PageModel::find()
                ->select('pipeline, status')
                ->where($conditions)
                ->andWhere("pipeline != '{$pageModel->pipeline}'")
                ->asArray()->all();
            $status = array_unique(array_column($pipelines, 'status'));
            if (in_array(PageModel::PAGE_STATUS_TO_BE_ONLINE, $status)) {
                $pipelines = array_column($pipelines, 'pipeline');
                $intersectPipeline = array_intersect($pipelines, $pipelineList);
                if (count($intersectPipeline) < count($pipelines)) {
                    throw new JsonResponseException($this->codeFail, '首次发布,需要发布所有渠道');
                }
            }
        }

        $pipelinePageModels = PageModel::find()->where($conditions)->andfilterWhere(['pipeline' => $pipelineList])->all();
        if (empty($pipelinePageModels)) {
            throw new JsonResponseException($this->codeFail, '没有要发布的渠道');
        }

        if (PageModel::HOME_B == $pageModel->home_type && $pageModel->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult($this->codeFail, '正在使用的首页B无法设置为首页A');
        }

        // 检查用户发布权限
        $this->checkUserHomePermissions($pipelinePageModels, $pageModel->site_code);

        // 先检查所有页面是否装修
        $this->checkHomeAllPageDesign($pipelinePageModels, $pageModel->site_code);

        // 先校验下数据
        foreach ($pipelinePageModels as $pipelinePageModel) {
            /** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
            $checkRes = $this->beforeVerifyRelease($pipelinePageModel->id);
            $pipelineName = $this->getPipelineName($pipelinePageModel->pipeline, $pipelinePageModel->site_code);
            if ($checkRes['code']) {
                throw new JsonResponseException($this->codeFail, '渠道【' . $pipelineName . '】上线失败：' . $checkRes['message']);
            }
        }

	    try {
		    defined('PUSHSYNC') or define('PUSHSYNC', app()->swoole->init()->isConnected());
	    } catch (\Exception $e) {
		    app()->rms->reportS3PushError($e->getMessage());
		    defined('PUSHSYNC') or define('PUSHSYNC', false);
	    }

        app()->arrayCache->set('redis_data_multi_array', json_encode([]));
        foreach ($pipelinePageModels as $pipelinePageModel) {
            /** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
            $res = $this->homeOnline($pipelinePageModel->id, $pipelinePageModel);
            $pipelineName = $this->getPipelineName($pipelinePageModel->pipeline, $pipelinePageModel->site_code);
            if ($res['code'] !== $this->codeSuccess) {
                throw new JsonResponseException($this->codeFail, '渠道【' . $pipelineName . '】上线失败：' . $res['message']);
            }
        }

        $pushPageArray = app()->arrayCache->get('redis_data_multi_array');
        if (!empty($pushPageArray)) {
            $pushPageArray = json_decode($pushPageArray, true);
            $pushState = $this->CrontabComponent->asyncPushPage($pushPageArray, true);
            if (true !== $pushState) {
                return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $pushState);
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '发布成功');
    }

    /**
     * 首页面上线(生成page的html文件)
     *
     * @param int $pageId
     * @param     $checkedPageModel
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function homeOnline($pageId, $checkedPageModel = null)
    {
	    ignore_user_abort(true);
	    set_time_limit(60);

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageId);

        if ($checkedPageModel === null) {
            $checkRes = $this->beforeVerifyRelease($pageId);
            if ($checkRes['code']) {
                return $checkRes;
            }

            /** @var \app\modules\common\zf\models\PageModel $pageModel */
            $pageModel = $checkRes['data'];
        } else {
            $pageModel = $checkedPageModel;
        }

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            $pageModel->verify_user = app()->user->username;
            $pageModel->verify_time = $_SERVER['REQUEST_TIME'];
            $pageModel->home_type = $pageModel::HOME_A;

            //页面上线，生成上线文件并推送S3
            list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], 0);
            if (!$success) {
                return app()->helper->arrayResult($this->codeFail, '首页设置失败：页面上线失败', [], $errorMsg);
            }

            $pageModel->update(true);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, '首页设置失败：操作失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '首页设置成功');
    }

    /**
     * 【批量】首页面上线(生成page的html文件)
     *
     * @param int    $pageId
     * @param string $pipeline 渠道列表，多个用英文逗号分隔
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function batchHomeOnlineB($pageId, $pipeline)
    {
        /** @var \app\modules\common\zf\models\PageModel $pageModel */
        $pageModel = PageModel::getById($pageId);
        if (empty($pageModel)) {
            return app()->helper->arrayResult($this->codeSuccess, '页面不存在！');
        }

        // 部分渠道设置为首页
        $conditions = ['group_id' => $pageModel->group_id];
        if (!empty($pipeline)) {
            $pipelineList = explode(',', $pipeline);
        }
        //第一次必须全渠道发布
        if (PageModel::PAGE_STATUS_TO_BE_ONLINE == $pageModel->status) {
            $pipelines = PageModel::find()
                ->select('pipeline, status')
                ->where($conditions)
                ->andWhere("pipeline != '{$pageModel->pipeline}'")
                ->asArray()
                ->all();
            $status = array_unique(array_column($pipelines, 'status'));
            if (in_array(PageModel::PAGE_STATUS_TO_BE_ONLINE, $status)) {
                $pipelines = array_column($pipelines, 'pipeline');
                $intersectPipeline = array_intersect($pipelines, $pipelineList);
                if (count($intersectPipeline) < count($pipelines)) {
                    throw new JsonResponseException($this->codeFail, '首次发布,需要发布所有渠道');
                }
            }
        }

        $pipelinePageModels = PageModel::find()->where($conditions)->andfilterWhere(['pipeline' => $pipelineList])->all();
        if (empty($pipelinePageModels)) {
            throw new JsonResponseException($this->codeFail, '没有要发布的渠道');
        }

        if (PageModel::HOME_A == $pageModel->home_type && $pageModel->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult($this->codeFail, '正在使用的首页A无法设置为首页B');
        }

        // 检查用户发布权限
        $this->checkUserHomePermissions($pipelinePageModels, $pageModel->site_code);

        // 先检查所有页面是否装修
        $this->checkHomeAllPageDesign($pipelinePageModels, $pageModel->site_code);

        // 先校验下数据
        foreach ($pipelinePageModels as $pipelinePageModel) {
            /** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
            $checkRes = $this->beforeVerifyRelease($pipelinePageModel->id);
            $pipelineName = $this->getPipelineName($pipelinePageModel->pipeline, $pipelinePageModel->site_code);
            if ($checkRes['code']) {
                throw new JsonResponseException($this->codeFail, '渠道【' . $pipelineName . '】上线失败：' . $checkRes['message']);
            }

            /** @var \app\modules\common\zf\models\PageModel $pageModel */
            $pageModel = $checkRes['data'];
            if ($pageModel->home_type == PageModel::HOME_A && $pageModel->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
                return app()->helper->arrayResult($this->codeFail, '渠道【' . $pipelineName . '】上线失败：正在使用的首页无法设置为首页B');
            }
        }

	    try {
		    defined('PUSHSYNC') or define('PUSHSYNC', app()->swoole->init()->isConnected());
	    } catch (\Exception $e) {
		    app()->rms->reportS3PushError($e->getMessage());
		    defined('PUSHSYNC') or define('PUSHSYNC', false);
	    }

        app()->arrayCache->set('redis_data_multi_array', json_encode([]));
        foreach ($pipelinePageModels as $pipelinePageModel) {
            /** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
            $res = $this->homeOnlineB($pipelinePageModel->id, $pipelinePageModel);
            $pipelineName = $this->getPipelineName($pipelinePageModel->pipeline, $pipelinePageModel->site_code);
            if ($res['code'] !== $this->codeSuccess) {
                throw new JsonResponseException($this->codeFail, '渠道【' . $pipelineName . '】上线失败：' . $res['message']);
            }
        }

        $pushPageArray = app()->arrayCache->get('redis_data_multi_array');
        if (!empty($pushPageArray)) {
            $pushPageArray = json_decode($pushPageArray, true);
            $pushState = $this->CrontabComponent->asyncPushPage($pushPageArray, true);
            if (true !== $pushState) {
                return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $pushState);
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '发布成功');
    }

    /**
     * 首页面上线(生成page的html文件)
     *
     * @param int $pageId
     * @param     $checkedPageModel
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function homeOnlineB($pageId, $checkedPageModel = null)
    {
        ignore_user_abort(true);
        set_time_limit(60);

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageId);

        if ($checkedPageModel === null) {
            $checkRes = $this->beforeVerifyRelease($pageId);
            if ($checkRes['code']) {
                return $checkRes;
            }

            /** @var \app\modules\common\zf\models\PageModel $pageModel */
            $pageModel = $checkRes['data'];
            if ($pageModel->home_type === PageModel::HOME_A) {
                return app()->helper->arrayResult($this->codeFail, '正在使用的首页无法设置为首页B');
            }
        } else {
            $pageModel = $checkedPageModel;
        }

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            $pageModel->verify_user = app()->user->username;
            $pageModel->verify_time = $_SERVER['REQUEST_TIME'];
            $pageModel->home_type = PageModel::HOME_B;

            //页面上线，生成上线文件并推送S3
            list($success, $errorMsg) = $this->batchCreateOnlinePageHtml(
                [$pageId], 0, false, false, false, '', true
            );
            if (!$success) {
                return app()->helper->arrayResult($this->codeFail, '首页B设置失败：页面上线失败', [], $errorMsg);
            }

            $pageModel->update(true);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, '首页B设置失败：操作失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '首页B设置成功');
    }

    /**
     * 首页面发布
     *
     * @param $pageId
     *
     * @return array
     * @throws \Throwable
     * @throws \Exception
     */
    public function setHomePage(int $pageId, string $pipeline)
    {
        $pageModel = PageModel::getById($pageId);
        if (empty($pageModel)) {
            return app()->helper->arrayResult($this->codeSuccess, '页面不存在！');
        }

        $lockKey = app()->redisKey->getHomePageRollbackLockKey($pageModel->site_code);
        if (app()->redis->sismember($lockKey, $pageId)) {
            throw new JsonResponseException($this->codeFail, '页面由于版本回滚已被锁定，请联系相关人员解除锁定');
        }

        // 部分渠道设置为首页
        if (!empty($pipeline)) {
            $pipelineList = explode(',', $pipeline);
        }

        // 除正在使用状态的页面可以单个渠道设为首页，其他状态都必须渠道
       /* $globalPageModel = ($pageModel->pipeline === 'ZF')
            ? $pageModel
            : PageModel::find()->where(['group_id' => $pageModel->group_id, 'pipeline' => 'ZF'])->one();*/
        if (PageModel::PAGE_STATUS_HAS_ONLINE !== $pageModel->status) {
            $supportPipelineCodeList = array_keys(PipelineUtils::getConfigHomeSupportPipelineList($pageModel->site_code));
            $intersectPipeline = array_intersect($supportPipelineCodeList, $pipelineList);
            if (count($intersectPipeline) < count($supportPipelineCodeList)) {
                $pagePipelineList = PageModel::find()
                    ->select('pipeline')
                    ->where(['group_id' => $pageModel->group_id])
                    ->column();

                $diffPipelineList = array_diff($supportPipelineCodeList, $pagePipelineList);
                if (empty($diffPipelineList)) {
                    throw new JsonResponseException($this->codeFail, '设为首页需选择所有渠道!');
                } else {
                    $configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($pageModel->site_code);
                    $pagePipelineNames = [];
                    foreach ($diffPipelineList as $_pipelineCode) {
                        $pagePipelineNames[] = $configPipelineLangList[ $_pipelineCode ]['name'] ?? '';
                    }
                    $message = sprintf('设为首页需选择所有渠道，当前首页有新增%s渠道，请先编辑首页添加渠道信息!', join(',', $pagePipelineNames));
                    throw new JsonResponseException($this->codeFail, $message);
                }
            }
        }

		$pipelinePageModels = PageModel::getLanguagePages($pageModel->group_id, $pipelineList);
		if (empty($pipelinePageModels)) {
			throw new JsonResponseException($this->codeFail, '没有要发布的渠道');
		}

		// 检查用户发布权限
		$this->checkUserHomePermissions($pipelinePageModels, $pageModel->site_code);
		$errArr = $publishLogs = [];
		foreach ($pipelinePageModels as $pipelinePageModel) {
			// 先校验下数据
			$checkRes = $this->beforeVerifyRelease($pipelinePageModel->page_id);
			$pipelineName = $this->getPipelineName($pipelinePageModel->pipeline, $pipelinePageModel->site_code);
			if ($checkRes['code']) {
				throw new JsonResponseException($this->codeFail, '渠道【' . $pipelineName . '】上线失败：' . $checkRes['message']);
			}
			$publishLog = PagePublishLogModel::getPagePushS3LocalPath($pipelinePageModel->page_id, $pipelinePageModel->lang);
			if (
				empty($publishLog['local_path']) || empty($publishLog['s3_url'])
				|| false === strstr($publishLog['local_path'], '_preview')
				|| false === app()->s3->getObject($publishLog['local_path'])
			) {
				array_push(
					$errArr,
					!empty(config("site.zf.pipeline.{$pipelinePageModel->pipeline}"))
						? config("site.zf.pipeline.{$pipelinePageModel->pipeline}")
						: $pipelinePageModel->pipeline
				);
			} else {
				$publishLog['pipeline'] = $pipelinePageModel->pipeline;
				$publishLog['home_type'] = $pipelinePageModel->home_type;

				array_push($publishLogs, $publishLog);
			}
		}

		if (!empty($errArr)) {
			$errMsg = implode(',', $errArr) . '页面未发布';

			return app()->helper->arrayResult($this->codeFail, $errMsg);
		}

		if (!empty($publishLogs) && is_array($publishLogs)) {
			$copyFail = [];
			foreach ($this->yieldCopyObject($publishLogs) as $yieldLog) {
				list($s3Res, $log) = $yieldLog;
				if (\is_string($s3Res)) {
					array_push($copyFail, $log['pipeline']);
				} else {
					if (PageModel::HOME_B == $log['home_type']) {
						PageModel::offlineHomeOnlinePageB($log['site_code'], $log['page_id']);
					} else {
						PageModel::offlineHomeOnlinePageA($log['site_code'], $log['page_id']);
					}
					PageModel::onlineNewHomePage($log['page_id'], PageModel::PAGE_STATUS_HAS_ONLINE);
					PagePublishLogModel::onlineActionLog($log['id']);
				}
			}
		}

		$message = !empty($copyFail) ? implode(',', $copyFail) . '首页设置失败' : '首页设置成功';

		return app()->helper->arrayResult($this->codeSuccess, $message);
	}

	private function yieldCopyObject (array $publishLogs)
	{
		foreach ($publishLogs as $log) {
			$localPath = pathinfo($log['local_path']);
			if (PageModel::HOME_B == $log['home_type']) {
				$copyPath = "{$localPath['dirname']}/index_new.{$localPath['extension']}";
			} else {
				$copyPath = "{$localPath['dirname']}/index.{$localPath['extension']}";
			}
			$s3Res = app()->s3->copyObject($log['s3_url'], $copyPath);

			yield [$s3Res, $log];
		}
	}

	/**
	 * 检查用户发布权限
	 *
	 * @param array  $pipelinePageModels
	 * @param string $siteCode
	 *
	 * @throws JsonResponseException
	 */
	private function checkUserHomePermissions ($pipelinePageModels, $siteCode)
	{
		if (app()->user->isGuest) {
			throw new JsonResponseException($this->codeFail, '没有登录或登录已过期');
		}

		// 超级管理员不需求检查
		if (app()->user->admin->is_super) {
			return;
		}

		// 获取页面支持的所有语言
		$configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($siteCode);

		$allPipelineCodes = [];
		foreach ($pipelinePageModels as $pipelinePageModel) {
			/** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
			$allPipelineCodes[] = $pipelinePageModel->pipeline;
		}

		// 检查是否有首页渠道发布权限
		$privilegeComponent = new AdminSitePrivilegeComponent();
		$validPipelineCodes = $privilegeComponent->getCurrentUserValidSiteHomePermissions($siteCode, $allPipelineCodes);
		if (count($allPipelineCodes) != count($validPipelineCodes)) {
			$noPermissionPipelines = array_diff($allPipelineCodes, $validPipelineCodes);
			if (!empty($noPermissionPipelines)) {
				$pipelineNames = [];
				foreach ($noPermissionPipelines as $pipelineCode) {
					$pipelineNames[] = $configPipelineLangList[$pipelineCode]['name'] ?? '';
				}

				$message = sprintf('没有渠道%s的发布权限，如果第一次发布需要所有渠道权限!', join('、', $pipelineNames));
				throw new JsonResponseException($this->codeFail, $message);
			}
		}
	}

	/**
	 * 检测页面是否装修
	 *
	 * @param array  $pipelinePageModels
	 * @param string $siteCode
	 *
	 * @throws JsonResponseException
	 */
	private function checkHomeAllPageDesign ($pipelinePageModels, $siteCode)
	{
		// 获取页面支持的所有语言
		$configPipelineLangList = PipelineUtils::getSiteHomePipelineLangList($siteCode);
		$pageIds = [];
		foreach ($pipelinePageModels as $pipelinePageModel) {
			/** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
			$pageIds[] = $pipelinePageModel->id;
		}

		// 获取页面所有语言时候有layout组件
		$rows = PageLayoutModel::find()->select('page_id, lang')
			->where(['page_id' => $pageIds])
			->groupBy(['page_id', 'lang'])
			->asArray()
			->all();

		$pageDesignLanguages = [];
		foreach ($rows as $_row) {
			$pageDesignLanguages[$_row['page_id']][$_row['lang']] = true;
		}

		$noDesignPipelines = [];
		foreach ($pipelinePageModels as $pipelinePageModel) {
			if (!isset($configPipelineLangList[$pipelinePageModel->pipeline]))
				continue;

			/** @var \app\modules\common\zf\models\PageModel $pipelinePageModel */
			$configPipelineInfo = $configPipelineLangList[$pipelinePageModel->pipeline];
			foreach ($configPipelineInfo['lang_list'] as $_langInfo) {
				if (!isset($pageDesignLanguages[$pipelinePageModel->id][$_langInfo['code']])) {
					$noDesignPipelines[] = $configPipelineInfo['name'] . '-' . $_langInfo['name'];
				}
			}
		}

		if (!empty($noDesignPipelines)) {
			$message = sprintf('%s 页面为空页面，所有页面必须不可为空，发布失败。', join('、', $noDesignPipelines));
			throw new JsonResponseException($this->codeFail, $message);
		}
	}

	/**
	 * 获取渠道名称
	 *
	 * @param $pipeline
	 * @param $siteCode
	 *
	 * @return mixed
	 */
	private function getPipelineName ($pipeline, $siteCode)
	{
		$site = explode('-', $siteCode)[0];
		$pipelines = app()->params['site'][$site]['pipeline'];

		return $pipelines[$pipeline] ?? $pipeline;
	}

	/**
	 * 页面设置（设置自定义样式等）
	 *
	 * @param int   $id 页面ID
	 * @param array $data
	 * @param bool  $runValidation
	 *
	 * @return array
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function setting ($id, $data, $runValidation = true)
	{
		if (!$id || empty($data['lang']) || empty($data['general'])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		/** @var \app\modules\common\models\PageLanguageModel $pageLanguageModel */
		$pageLanguageModel = PageLanguageModel::findOne([
			static::FIELD_PAGE_ID => $id,
			'lang'                => trim($data['lang'])
		]);

		$generalData = json_decode($data['general'], true);
		$customData = !empty($data['custom']) ? json_decode($data['custom'], true) : [];
		$generalStyle = [
			'style_type'  => $generalData['style_type'] ?? 0,
			'bg_color'    => !empty($generalData['background_color']) ? trim($generalData['background_color']) : '',
			'bg_image'    => !empty($generalData['background_image']) ? trim($generalData['background_image']) : '',
			'bg_position' => !empty($generalData['background_position']) ? trim($generalData['background_position']) : '',
			'bg_repeat'   => !empty($generalData['background_repeat']) ? trim($generalData['background_repeat']) : '',
			'custom_css'  => !empty($generalData['custom_css']) ? trim($generalData['custom_css']) : ''
		];
		$multiTimeStyle = empty($customData['list']) ? [] : $customData['list'];

		!$pageLanguageModel && $pageLanguageModel = new PageLanguageModel();
		$pageLanguageModel->page_id = $id;
		$pageLanguageModel->lang = trim($data['lang']);
		$pageLanguageModel->background_color = $generalStyle['bg_color'];
		$pageLanguageModel->background_image = $generalStyle['bg_image'];
		$pageLanguageModel->background_position = $generalStyle['bg_position'];
		$pageLanguageModel->background_repeat = $generalStyle['bg_repeat'];
		$pageLanguageModel->custom_css = $generalStyle['custom_css'];
		$pageLanguageModel->style_type = $generalStyle['style_type'];
		$pageLanguageModel->multi_time_style = json_encode($multiTimeStyle);

		if (!$pageLanguageModel->save($runValidation)) {
			return app()->helper->arrayResult($this->codeFail, $pageLanguageModel->flattenErrors(', '));
		}

		return app()->helper->arrayResult($this->codeSuccess, '保存成功');
	}

	/**
	 * 获取页面设置（设置自定义样式等）
	 *
	 * @param int    $pageId 页面ID
	 * @param string $lang   语言代码简称
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getSetting ($pageId, $lang)
	{
		if (!$pageId || empty($lang)) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}
		$pageLanguageModel = PageLanguageModel::findOne([
			static::FIELD_PAGE_ID => $pageId,
			'lang'                => trim($lang)
		]);

		//只返回部分字段，有些关键信息不能返回
		$data = $pageLanguageModel ? [
			'general' => [
				'style_type'          => $pageLanguageModel->style_type,
				'background_color'    => $pageLanguageModel->background_color,
				'background_image'    => $pageLanguageModel->background_image,
				'background_position' => $pageLanguageModel->background_position,
				'background_repeat'   => $pageLanguageModel->background_repeat,
				'custom_css'          => $pageLanguageModel->custom_css
			],
			'list'    => json_decode($pageLanguageModel->multi_time_style, true)

		] : [];

		return app()->helper->arrayResult($this->codeSuccess, '查询成功', $data);
	}


	/**
	 * 复制英文页面SKU
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @throws Exception
	 * @throws \Throwable
	 */
	public function copySku ($data)
	{
		if (empty($data['from_lang']) || empty($data['from_page_id']) || empty($data['to_lang']) || empty($data['to_page_id'])) {
			throw new JsonResponseException($this->codeFail, '参数不正确');
		}

		// 访问日志记录关联页面id
		AccessLogComponent::addPageId($data['to_page_id']);

		$enPage = $this->getPageLayoutAndUiByPageId($data['from_page_id'], $data['from_lang']);
		$langPage = $this->getPageLayoutAndUiByPageId($data['to_page_id'], $data['to_lang']);

		$this->checkPageSku($enPage, $langPage, ['fromLang' => $data['from_lang'], 'toLang' => $data['to_lang']]);

		return app()->helper->arrayResult($this->codeSuccess, '同步成功');
	}

	/**
	 * 检查页面商品组件是否满足复制的需求
	 *
	 * @param array $enPage
	 * @param array $langPage
	 * @param array $langEqual
	 *
	 * @throws JsonResponseException
	 */
	protected function checkPageSku (array $enPage, array $langPage, array $langEqual)
	{
		list($enLayout, $enUi) = $enPage;
		list($langLayout, $langUi) = $langPage;

		//获取排好序的商品ui组件
		$enUiInOrder = $langUiInOrder = $uiEqualList = [];
		$this->getIncludeSkuUiInOrder($enLayout, $enUi, $enUiInOrder);
		$this->getIncludeSkuUiInOrder($langLayout, $langUi, $langUiInOrder);

		//检测商品组件个数是否相等
		$this->checkSkuUiEqual($enUiInOrder, $langUiInOrder, $uiEqualList);

		//复制SKU
		if (true !== ($copyRes = PageUiComponentDataModel::copySku($uiEqualList, $langEqual))) {
			throw new JsonResponseException($this->codeFail, '同步失败', [], $copyRes);
		}
	}

	/**
	 * 获取排好序的包含sku的ui组件
	 *
	 * @param array $orderedLayouts
	 * @param array $uiListByLayoutPosition
	 * @param array $uiInOrder
	 */
	protected function getIncludeSkuUiInOrder (array $orderedLayouts, array $uiListByLayoutPosition, array &$uiInOrder)
	{
		if (!empty($orderedLayouts)) {
			foreach ($orderedLayouts as $layout) {
				if (!empty($uiListByLayoutPosition[$layout['id']])) {
					/** @var array[] $uiListByLayoutPosition */
					foreach ($uiListByLayoutPosition[$layout['id']] as $uiList) {
						/** @var array $uiList */
						$uiList = $this->getOrderedComponents($uiList);
						foreach ($uiList as $ui) {
							if (\in_array($ui['component_key'], $this->uiKeyIncludeSku, true)) {
								$uiInOrder[] = $ui;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * 检查SKU的ui是否个数相等
	 *
	 * @param array $enUiInOrder
	 * @param array $langUiInOrder
	 * @param array $uiEqualList
	 *
	 * @throws JsonResponseException
	 */
	protected function checkSkuUiEqual (array $enUiInOrder, array $langUiInOrder, array &$uiEqualList)
	{
		if (empty($enUiInOrder)) {
			throw new JsonResponseException($this->codeFail, '被同步页面没有商品组件');
		}

		if (empty($langUiInOrder)) {
			throw new JsonResponseException($this->codeFail, '当前语言页面没有商品组件');
		}

		$hasEqualed = [];
		foreach ($enUiInOrder as $enUi) {
			foreach ($langUiInOrder as $langUi) {
				if ($enUi['component_key'] === $langUi['component_key']
					&& !\in_array($langUi['id'], $hasEqualed, false)
				) {
					$uiEqualList[] = [
						'fromId'    => $enUi['id'],
						'fromTplId' => $enUi['tpl_id'],
						'toId'      => $langUi['id'],
						'toTplId'   => $langUi['tpl_id']
					];
					$hasEqualed[] = $langUi['id'];
					break;
				}
			}

		}

		if (\count($enUiInOrder) !== \count($langUiInOrder) || \count($enUiInOrder) !== \count($uiEqualList)) {
			throw new JsonResponseException($this->codeFail, '商品类组件数量不一致，不能同步');
		}
	}

	/**
	 * 获取渠道间页面复制的可选渠道列表
	 *
	 * @param $pageId
	 * @param $lang
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function getCopyPipeline ($pageId, $lang)
	{
		if (empty($this->pageInfo)) {
			throw new JsonResponseException($this->codeFail, '无效的页面ID');
		}

		if (app()->user->isGuest) {
			throw new JsonResponseException($this->codeFail, '用户没有登录或登录信息过期，请重新登录');
		}


		$fromPipeline = PageModel::getActivityPipelineByPageId($pageId);
		$toPipeline = $fromPipeline; //$this->filterGlobalSite($fromPipeline);

		// 过滤没有layout组件的页面
		$pipelineLangList = PageModel::getHasDesignPageLang($this->pageInfo->group_id);
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
		if (('home' == app()->controller->module->module->id) && !app()->user->admin->is_super) {
			$websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($this->pageInfo->site_code);
			$privilegeComponent = new AdminSitePrivilegeComponent();
			$hasHomePermissions = $privilegeComponent->getCurrentUserSiteHomePermissions($websiteCode);
			if (!empty($hasHomePermissions)) {
				foreach ($toPipeline as $key => $val) {
					if (in_array($val['pipeline'], $hasHomePermissions, true)) {
						$newToPipeline[] = $val;
					}
				}
			}
		} else {
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
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
			'fromPipeline' => array_values($fromPipeline),
			'toPipeline'   => $newToPipeline
		]);
	}

	/**
	 * 过滤全球站
	 *
	 * @param array $pipelines
	 *
	 * @return array
	 */
	private function filterGlobalSite (array $pipelines)
	{
		if (empty($pipelines)) {
			return [];
		}

		foreach ($pipelines as $key => $pipeline) {
			if ($pipeline['pipeline'] === 'ZF') {
				unset($pipelines[$key]);
			}
		}

		return array_values($pipelines);
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
	public function copyPipeline ($data)
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
		$pipelineConfig = $this->getCopyPipeline($data[static::FIELD_PAGE_ID], '');
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
					$res = $this->copyPage($params, true);
				} elseif ($data['type'] === SiteConstants::PIPELINE_COPY_TYPE_SKU) {
					$res = $this->copySku($params);
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
	 * @param bool $copySku 是否包含复制SKU
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @throws Exception
	 * @throws \Throwable
	 */
	public function copyPage ($data, $copySku = false)
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
			//删除组件数据绑定
			//删掉原来的绑定关系
			$ui = new CommonUi();
			$ui->pageId = $data['to_page_id'];
			$page_layouts = PageLayoutModel::find()->select("id")->where(['page_id' => $data['to_page_id']])->asArray()->all();
			foreach ($page_layouts as $page_layout) {
				$ui_ids = PageUiModel::find()->where(['layout_id' => $page_layout])->asArray()->all();
				$ui_ids = empty($ui_ids) ? [] : array_column($ui_ids, 'lang', 'id');
				foreach ($ui_ids as $id => $lang) {
					$ui->delUserData($id);
					$ui->cancelUiComponentBindRelation($id, $lang);
					$sync_data['del_info'][] = [
						'geshop_component_ui_id' => $id
					];
				}
			}

			$this->clearComponent($data['to_page_id'], $data['to_lang']);
			$this->copyComponent($data['from_page_id'], $data['from_lang'], $data['to_page_id'], $data['to_lang'], $copySku);
			$tr->commit();


			//同步删除IPS子活动
			$activity = ActivityModel::getActivityByPageId($data['to_page_id']);
			$sync_data['geshop_activity_id'] = $activity->id;

			\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

			//同步创建ips活动
			//查找目标页面需要绑定到ips的组件
			$datas = [];
			$page_layout = PageLayoutModel::find()->where(['page_id' => $data['to_page_id']])->asArray()->one();
			$layout_id = $page_layout['id'];
			$lang = $page_layout['lang'];
			$page_ui_lists = PageUiModel::find()->where(['layout_id' => $layout_id])->asArray()->all();
			foreach ($page_ui_lists as $page_ui_list) {
				$page_ui = PageUiModel::findOne($page_ui_list['id']);
				if (empty($page_ui)) {
					continue;
				}
				$ui_datas = PageUiComponentDataModel::find()->where(['component_id' => $page_ui_list['id'], "tpl_id" => $page_ui->tpl_id])->asArray()->all();
				$is_need_sync = false;
				$is_ips = false;
				$is_rule = false;
				$ipsFilterInfo = [];
				foreach ($ui_datas as $ui_data) {
					if ($ui_data['key'] == "goodsDataFrom" &&
						$ui_data['value'] == 2
					) {
						$is_ips = true;
					}
					if ($ui_data['key'] == "ipsMethods" &&
						$ui_data['value'] == 3
					) {
						$is_rule = true;
					}
					if ($ui_data['key'] == "ipsFilterInfo") {
						if (!empty(json_decode($ui_data['value'], 1))) {
							$ipsFilterInfo = json_decode($ui_data['value'], 1);
						}
					}
				}
				$is_need_sync = $is_ips && $is_rule;
				if ($is_need_sync && $ipsFilterInfo) {
					//规则选品需同步
					$tmp['page_id'] = $data['to_page_id'];
					$tmp['lang'] = $data['to_lang'];
					$tmp['id'] = $page_ui_list['id'];
					$tmp['tpl_id'] = $page_ui_list['tpl_id'];
					$tmp['is_auto_activity'] = 2;
					$tmp['ips_activity_child_id'] = $ipsFilterInfo["ips_activity_child_id"];
					$datas[] = $tmp;
				}

			}
			\app\modules\common\components\CommonActivityComponent::batchCreateActivityToIps($datas);

			return app()->helper->arrayResult($this->codeSuccess, '复制成功');
		} catch (Exception $e) {
			$tr->rollBack();

			return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '复制失败');
		}
	}

	/**
	 * 清除页面组件
	 *
	 * @param $pageId  页面ID
	 * @param $lang    页面语言
	 */
	private function clearComponent ($pageId, $lang)
	{
		PageLayoutModel::deletePageLayouts($pageId, $lang);
	}

	/**
	 * 复制页面组件
	 *
	 * @param int    $fromPageId 页面ID
	 * @param string $fromLang   页面语言
	 * @param int    $toPageId   页面ID
	 * @param string $toLang     页面语言
	 * @param bool   $copySku    是否包含复制SKU
	 *
	 * @return bool
	 */
	private function copyComponent ($fromPageId, $fromLang, $toPageId, $toLang, $copySku)
	{
		list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->PageTplComponent->getPageInfo($fromPageId, $fromLang);
		//丢弃掉商品标题组合组件
		$this->GoodsManageComponent->discardGoodsTitleCompositeUiComponentWhenPageCopy($layoutInfo, $uiInfo);
		if (empty($layoutInfo) || empty($uiInfo)) {
			return true;
		}

		$pageArr = [
			'layout'      => json_encode($layoutInfo),
			'layout_data' => json_encode($layoutData),
			'ui'          => json_encode($uiInfo),
			'ui_data'     => json_encode($uiData)
		];
		$this->PageTplComponent->copyPage($toPageId, $pageArr, $toLang, $copySku);

		// 复制组件对应IPS关联信息
		$fromUiIds = [];
		foreach ($uiInfo as $layoutId => $positionInfo) {
			$layoutIds[] = $layoutId;
			foreach ($positionInfo as $_uiList) {
				$fromUiIds = array_merge($fromUiIds, array_column($_uiList, 'id'));
			}
		}

		$uiInfo = $this->PageTplComponent->getPageInfo($toPageId, $toLang)[2];
		if (!empty($uiInfo)) {

			/** @var \app\modules\common\zf\models\PageModel $fromPageModel */
			$fromPageModel = PageModel::findOne($fromPageId);
			$ipsPageInfo = [
				'siteCode' => $fromPageModel->site_code,
				'pageId'   => $toPageId,
				'lang'     => $toLang,
			];

			\app\modules\soa\models\SoaIpsGoodsModel::delPageUiComponentWithIpsInfo($ipsPageInfo);

			$toUiIds = [];
			foreach ($uiInfo as $layoutId => $positionInfo) {
				$layoutIds[] = $layoutId;
				foreach ($positionInfo as $_uiList) {
					$toUiIds = array_merge($toUiIds, array_column($_uiList, 'id'));
				}
			}

			if (count($fromUiIds) === count($toUiIds)) {
				// 如果商品SKU来源选品系统,复制关联选品活动信息
				$ipsComponent = new \app\modules\soa\components\IpsComponent();
				$ipsPageInfo = [
					'siteCode' => $fromPageModel->site_code,
					'pageId'   => $fromPageModel->id,
					'lang'     => $fromLang,
					'pipeline' => $fromPageModel->pipeline,
					'toPageId' => $toPageId,
					'toLang'   => $toLang,
				];

				for ($i = 0; $i < count($fromUiIds); $i++) {
					$ipsPageInfo['toLang'] = $toLang;
					$ipsComponent->copyRelatedIpsActivityIfSkuFromIps($ipsPageInfo, $fromUiIds[$i], $toUiIds[$i]);
				}
			}
		}

		return true;

	}

	/**
	 * checkLang
	 *
	 * @param $lang
	 *
	 * @throws JsonResponseException
	 */
	public function checkLang ($lang)
	{
		$langConf = app()->params['lang'];
		if (!array_key_exists($lang, $langConf)) {
			throw new JsonResponseException($this->codeFail, '非法的lang参数');
		}
	}

	/**
	 * 页面模板-查看页面
	 *
	 * @param array $params  参数数组
	 *                       ['id']         int    页面模板id
	 *                       ['pid']        string 页面32位长度pid
	 *                       ['site_code']  string 站点简称
	 *                       ['lang']       string 语种
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function look ($params)
	{
		list($id, $pid, $siteCode, $lang, $pipeline) = $params;
		if (!isset($id, $pid, $siteCode, $lang)) {
			return app()->helper->arrayResult($this->codeFail, '参数错误');
		}

		//获取页面模板html数据
		$pagePreview = new PagePreview();
		$html = $pagePreview->getPageTemplatePreview($id, $pid, $lang, $pipeline);
		if (\is_array($html)) {
			return $html;
		}

		app()->response->format = Response::FORMAT_HTML;

		return $html;
	}

	/**
	 * 页面模板-查看页面
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function clearCache (int $id)
	{
		$key = $this->redisPrefix . app()->redisKey->getPageTplRedisKey($id);

		app()->redis->del($key);

		return app()->helper->arrayResult($this->codeSuccess, '清理成功');
	}

	/**
	 * 页面模板存入redis
	 *
	 * @param int    $id   页面模板路径文件
	 * @param string $html html文件数据
	 *
	 * @return int
	 */
	public function setPageHtmlToRedis ($id, $html)
	{
		$key = $this->redisPrefix . app()->redisKey->getPageTplRedisKey($id);
		$time = 864000; // 缓存时间 10天

		return app()->redis->setex($key, $time, $html);
	}

	/**
	 * 设置货币COOKIE用于活动页面设计预览时正常显示商品价格
	 */
	public function setCurrencyCookie ()
	{
		if (!headers_sent()) {
			$expire = time() + 60 * 60 * 24 * 30; // 30天
			setcookie("bizhong", 'USD', $expire, '/', DOMAIN); // 用于ZF站点
		}
	}

	/**
	 * 校验组件
	 *
	 * @param array $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 * @author yuanwenguang 2019/3/20 10:46
	 */
	public function checkComponent ($data = [])
	{
		//校验组件是否是组件
		$tpl_ids = array_column($data, 'tpl_id');

		$tpls = UiTplModel::find()->where(['in', 'id', $tpl_ids])->asArray()->all();
		$exsit_tpls = array_column($tpls, 'id');
		$no_effect_tpls = array_diff($tpl_ids, $exsit_tpls);

		if (!empty($no_effect_tpls)) {
			throw new JsonResponseException($this->codeFail, '模板' . implode(',', $no_effect_tpls) . '不存在');
		}


		//校验组件是否是数据不完整

		//多时段秒杀组件-tab模板；如果秒杀时间段、商品sku均未输入则不校验，如果输入sku未输入秒杀时间，则点击加号时提示“请输入秒杀时间段”、
		//具体的秒杀时间段的限制规则同现有相同即可
		//排行榜--01-折扣榜:如果商品分类数据ID未填写，则点击加号时不做校验，直接显示下个组件，如果商品分离id填写，则需要对tab分类名称做校验，提示信息为“请输入TAB分类名
		//轮播排行榜,赠品,加价购

		//商品组件
		$good_list_tpl_ids = ['127', '150', '254', '253'];

		//商品分类id校验规则


		//组件tab秒杀模板
		$tabspikes = UiTplModel::find()
			->select('id')
			->where(['name_en' => 'tabspike'])
			->andWhere(['status' => UiTplModel::STATUS_USED])
			->asArray()->all();
		$tabspikes = array_column($tabspikes, 'id');

		//排行榜(折扣，热卖，新品)组件模板
		$discount_lists = UiTplModel::find()
			->select('id')
			->where(['in', 'name_en', ['discountList', 'hotSaleList', 'newProductList']])
			->andWhere(['status' => UiTplModel::STATUS_USED])
			->asArray()->all();
		$discount_lists = array_column($discount_lists, 'id');

		//加购价组件模板

		$add_price_buy_list = ['201', '202'];

		$error_str = [];
		foreach ($data as $components) {
			$error_str[$components['tpl_id']] = [];

			//校验tab秒杀组件  [{'tpl_id':11,'goodsSKUTab':[{'list':''},{'list':''}]}]
			if (in_array($components['tpl_id'], $tabspikes)) {
				if (isset($components["goodsSKUTab"])) {
					foreach ($components["goodsSKUTab"] as $goods_tab) {
						if (!empty($goods_tab['list'])) {
							if (empty($goods_tab['dataStartTime'])) {
								array_push($error_str[$components['tpl_id']], 'dataStartTime');
							}

							if (empty($goods_tab['dataEndTime'])) {
								array_push($error_str[$components['tpl_id']], 'dataEndTime');
							}

							if (empty($goods_tab['timeRange'])) {
								array_push($error_str[$components['tpl_id']], 'timeRange');
							}
						}
					}
				}
			}

			//校验排行榜组件 [{'tpl_id':11,'goodsIds':[{"category":"11","cateid":"11"},{"category":"22","cateid":"22"}]}]
			if (in_array($components['tpl_id'], $discount_lists)) {
				if (isset($components['goodsIds'])) {
					foreach ($components['goodsIds'] as $good_id) {
						if (!empty($good_id['cateid'])) {
							if (empty($good_id['category'])) {
								array_push($error_str[$components['tpl_id']], 'category');
							}
						}
					}
				}
			}

			//校验加购价组件数据完整性 [{'tpl_id':11,'goodsIds':[{"ids":"11","name":"11"},{"ids":"22","name":"22"}]}]
			if (in_array($components['tpl_id'], $add_price_buy_list)) {
				if (isset($components['goodsIds'])) {
					foreach ($components['goodsIds'] as $good_id) {
						if (!empty($good_id['ids'])) {
							if (empty($good_id['name'])) {
								array_push($error_str[$components['tpl_id']], 'name');
							}
						}
					}
				}
			}

		}

		return $error_str;
	}

	/**
	 * 获取需要同步的原生页面
	 *
	 * @param array $data
	 */
	public function getAsyncNativePageId (int $sourceId, string $sourcePipeline, string $sourceLang)
	{
		$groupResult = PageGroupModel::find()
			->select('page_group_id, platform_type')
			->where(['page_id' => $sourceId])
			->asArray()
			->one();
		$platform = ($groupResult['platform_type'] == Platform::WAP) ? Platform::IOS : Platform::WAP;
		$pageResult = PageGroupModel::find()
			->alias('pg')
			->select('p.id')
			->innerJoin(PageModel::tableName() . ' as p', 'pg.page_id = p.id')
			->innerJoin(PageLanguageModel::tableName() . ' as pl', 'pg.page_id = pl.page_id')
			->where(
				[
					'pg.page_group_id' => $groupResult['page_group_id'],
					'pg.platform_type' => $platform,
					'p.pipeline'       => $sourcePipeline,
					'pl.lang'          => $sourceLang
				]
			)
			->asArray()
			->one();

		return !empty($pageResult['id']) ? $pageResult['id'] : 0;
	}
}
