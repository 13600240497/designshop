<?php

namespace app\modules\common\components;

use app\base\Pagination;
use app\modules\common\models\{ActivityModel,
	PageModel,
	PageLanguageModel,
	PagePublishCacheModel,
	PagePublishLogModel,
	SiteHeadFooterReplaceLogModel,
	SiteUpdateLogModel};
use app\base\RequestUtils;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\modules\base\components\BigDataSyncComponent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Yii;

use app\modules\common\traits\{
	CommonVerifyStatusTrait, CommonPublishTrait
};
use Exception;
use GuzzleHttp\Promise;
use yii\helpers\StringHelper;

/**
 * 定时任务组件
 */
class CommonCrontabComponent extends Component
{
	use CommonVerifyStatusTrait;
	use CommonPublishTrait;

	/**
	 * 单个页面push-page任务失败次数上限
	 */
	const PUSH_ERROR_UPPER_LIMIT = 1000;
	const REDIS_PREFIX = '::';

	//返回数据
	private $returnData;
	private $returnHeadFooterData;

	/**
	 * init
	 */
	public function init ()
	{
		parent::init();
		$this->returnData = [
			'total_count'   => 0,
			'success_count' => 0,
			'fail_count'    => 0,
			'del_count'     => 0,
			'error_html'    => []
		];
		$this->returnHeadFooterData = [
			'total_count'  => 0,
			'change_count' => 0
		];
	}

	/**
	 * 刷新页面
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function refreshPage ()
	{
		$timestamp = time();
		Yii::info('定时任务refreshPage请求Start：' . $timestamp, __METHOD__);
		$lockKey = app()->redisKey->getRefreshTaskLockKey();
		if (null === app()->redis->set($lockKey, 1, 'EX', 60, 'NX')) {
			Yii::info('定时任务refreshPage请求End：' . $timestamp, __METHOD__);

			return app()->helper->arrayResult($this->codeFail, '已有定时任务正在消费队列，请等待1分钟后再试');
		}

		ignore_user_abort(true);
		set_time_limit(60);

		$data = $validPageInfos = [];
		$key = app()->redisKey->getRefreshTaskRedisKey();
		$list = app()->redis->zrangebyscore($key, 0, $timestamp);
		if (!empty($list)) {
			$this->returnData['total_count'] = \count($list);
			//按活动ID分组组装数据
			$pageInfos = PageModel::getValidActivityIdsByPageIds($list);
			if (!empty($pageInfos)) {
				$validPageInfos = array_column($pageInfos, null, 'id');
				array_map(function ($page) use (&$data) {
					$data[(int)$page['activity_id']][] = (int)$page['id'];
				}, $pageInfos);
			}
			$this->refreshPageData([$validPageInfos, $timestamp, $key], $data);
		}

		//删除锁
		app()->redis->del($lockKey);

		Yii::info(
			'定时任务refreshPage请求End：' . $timestamp . '----errorHtml:' . json_encode($this->returnData['error_html']),
			__METHOD__
		);

		return app()->helper->arrayResult($this->codeSuccess, '刷新成功', $this->returnData);
	}

	/**
	 * 刷新页面数据
	 *
	 * @param array $params
	 * @param array $data
	 *
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	private function refreshPageData ($params, &$data)
	{
		list($validPageInfos, $timestamp, $key) = $params;
		//按活动刷新
		if (!empty($data)) {
			$pageComponent = new CommonPageComponent();

			/** @var array $pageIds */
			foreach ($data as $activityId => $pageIds) {
				list($success, $errorMsg) = $pageComponent->batchCreateOnlinePageHtml($pageIds, $activityId, true, true);
				$this->returnData['success_count'] += \count($pageIds) - \count($errorMsg);
				$this->returnData['fail_count'] += \count($errorMsg);

				if (!$success && !empty($errorMsg)) {
					array_push($this->returnData['error_html'], ...$errorMsg);
				} else {
					//更新redis中的score值
					$options = [];
					foreach ($pageIds as $pageId) {
						//!!!注意option中的顺序，score在前
						$options[] = $timestamp + (int)$validPageInfos[$pageId]['refresh_time'];
						$options[] = $pageId;
					}
					app()->redis->zadd($key, ...$options);
				}
			}
		} else {
			$this->returnData['success_count'] = 0;
			$this->returnData['fail_count'] = $this->returnData['total_count'];
		}
	}

	/**
	 * 对到时间下线而还未下线的活动下线（包括其下的页面）
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function offlinePage ()
	{
		$timestamp = time();
		Yii::info('定时任务offlinePage请求Start：' . $timestamp, __METHOD__);
		$lockKey = app()->redisKey->getOfflineTaskLockKey();
		if (null === app()->redis->set($lockKey, 1, 'EX', 60, 'NX')) {
			Yii::info('定时任务offlinePage请求End：' . $timestamp, __METHOD__);

			return app()->helper->arrayResult($this->codeFail, '已有定时任务正在消费队列，请等待1分钟后再试');
		}

		ignore_user_abort(true);
		set_time_limit(60);

		$data = [];
		$pageComponent = new CommonPageComponent();
		if ($list = PageModel::getNeedOfflinePages()) {
			$this->returnData['total_count'] = \count($list);
			$offlineActivityIds = $offlinePageIds = [];

			//按活动ID分组组装数据
			array_map(function ($page) use (&$data) {
				$data[$page['activity_id']][] = $page['id'];
			}, $list);
			//按活动下线
			foreach ($data as $activityId => $pageIds) {
				$successIds = [];
				foreach ($pageIds as $pageId) {
					try {
						list($success, $errorMsg) = $pageComponent->batchCreateOfflinePageHtml([$pageId], $activityId);
						if ($success) {
							$successIds[] = $pageId;
							$this->returnData['success_count'] += 1;
						} else {
							$this->returnData['fail_count'] += 1;
							array_push($this->returnData['error_html'], ...$errorMsg);
							continue;
						}
					} catch (Exception $e) {
						$this->returnData['fail_count'] += 1;
						$errorItem = ['page_id' => $pageId, 'message' => $e->getMessage()];
						array_push($this->returnData['error_html'], $errorItem);
						continue;
					}
				}

				//记录待下线的ID
				if (!empty($successIds)) {
					$offlineActivityIds[] = $activityId;
					foreach ($successIds as $pid) {
						$offlinePageIds[] = $pid;
					}
				}
			}

			//下线活动和页面
//            ActivityModel::getDb()->createCommand()->update(
//                ActivityModel::tableName(),
//                ['status' => ActivityModel::STATUS_HAS_OFFLINE],
//                'id IN (' . implode(',', $offlineActivityIds) . ')'
//            )->execute();
			if (!empty($offlinePageIds)) {
				PageModel::getDb()->createCommand()->update(
					PageModel::tableName(),
					['status' => PageModel::PAGE_STATUS_HAS_OFFLINE],
					'id IN (' . implode(',', $offlinePageIds) . ')'
				)->execute();
			}
		}

		//删除锁
		app()->redis->del($lockKey);

		Yii::info(
			'定时任务offlinePage请求End：' . $timestamp . '----errorHtml:' . json_encode($this->returnData['error_html']),
			__METHOD__
		);

		return app()->helper->arrayResult($this->codeSuccess, '下线成功', $this->returnData);
	}

	/**
	 * 推送页面到S3|异步
	 *
	 * @param array $args
	 * @param bool  $isSerial
	 *
	 * @return bool|string
	 */
	public function asyncPushPage (array $args, $isSerial = false)
	{
		if (!empty($args)) {
			$errorMsg = '';
			try {
				$data = array_map(function ($item) {
					return $this->buildRedisData($item);
				}, $args);

				app()->swoole->init()->send(
					[
						'data'   => $data,
						'action' => 'asyncPushPage',
						'mode'   => $isSerial ? 'serial' : 'multi'
					]
				);
			} catch (Exception $e) {
				$errorMsg .= $e->getMessage() . '|';
			}
		}

		return !empty($errorMsg) ? $errorMsg : true;
	}

	/**
	 * 一键刷新页面到S3|异步
	 *
	 * @param array  $args
	 * @param string $key
	 * @param int    $logId
	 *
	 * @return bool|string
	 */
	public function asyncRefreshPage (array $args, string $key, int $logId)
	{
		if (!empty($args) && !empty($key) && !empty($logId)) {
			$errorMsg = '';
			try {
				$data = array_map(function ($item) use ($key) {
					return $this->buildRedisData($item, $key);
				}, $args);

				app()->swoole->init('refresh')->send(
					[
						'action' => 'asyncRefreshPushPage',
						'log_id' => $logId,
						'data'   => $data
					]
				);
			} catch (Exception $e) {
				app()->rms->reportS3PushError($e->getMessage() . var_export($args));
				$errorMsg .= $e->getMessage() . '|';
			}
		}

		return !empty($errorMsg) ? $errorMsg : true;
	}

	/**
	 * 重新组装一下redis里取出来的数据
	 *
	 * @param array  $redisData
	 * @param string $dataKey
	 * @param int    $record
	 *
	 * @return array
	 */
	private function buildRedisData (array $redisData, string $dataKey = '', $record = 0)
	{
		return array_map(function ($item) use ($dataKey, $record) {
			$item = \GuzzleHttp\json_decode($item, true);
			if (!file_exists($item['local_path'])) {
				if (!empty($item['type']) && $item['type'] == 1 && $item['file_type'] == 'html') {//推广落地页html内容
					$content = $this->getHtmlContentCache(
						[
							$item['page_id'],
							$item['lang'],
							$item['site_code'],
							$item['file_type'],
							$item['css_s3'],
							$item['js_s3'],
						]
					);
				} else {
					//从数据库去获取
					$content = $this->getContentCache(
						[
							$item['page_id'],
							$item['lang'],
							$item['site_code'],
							$item['file_type'],
							$item['css_s3'],
							$item['js_s3'],
							$item['mold'],
							$item['is_home_b']
						]
					);
				}

				if (false === file_put_contents($item['local_path'], $content) || !file_exists($item['local_path'])) {
					Yii::error('文件内容写入失败' . $item['local_path'], __METHOD__);

					return [false, '文件内容写入失败：' . $item['local_path']];
				}
			}

			$item['record'] = $record;
			if (!empty($dataKey)) {
				$item['data_key'] = $dataKey;
			}
			//发布首页还需清理首页的缓存
			if (empty($item['activity_id']) && $item['file_type'] === $this->htmlFileType) {
				$item['clear_url'] = $this->getSiteDomain($item['site_code'], $item['lang']);
			} else {
				$item['clear_url'] = $this->getS3UrlByS3Key(
					[
						$item['s3_key'],
						$item['lang'],
						$item['file_type'],
						$item['site_code'],
						$item['mold']
					]
				);
			}

			return $item;
		}, $redisData);
	}

	/**
	 * 处理redis队列，推送页面到S3
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public function pushPage ()
	{
		$timestamp = time();
		Yii::info('定时任务pushPage请求Start：' . $timestamp, __METHOD__);
		set_time_limit(60);

		$key = app()->redisKey->getPushTaskRedisKey();
		$time = app()->request->get('time', 60);
		$time = $time > 0 ? $time : 60;
		while (time() - $_SERVER['REQUEST_TIME'] < $time) {
			$data = json_decode(app()->redis->lpop($key), true);
			try {
				if ($data) {
					// GB站点语言配置
					if (isset($data['site_code'])
						&& StringHelper::startsWith($data['site_code'], SiteConstants::SITE_GROUP_CODE_GB)
					) {
						SitePlatform::setGbLanguageNames();
					}

					$this->returnData['total_count']++;
					list($putRes, $msg) = $this->processData($data);

					if ($putRes) {
						if (!empty($data['type']) && intval($data['type']) == 1) {//推广落地页
							if ($data['file_type'] == 'html') {
								app()->redis->rpush(app()->redisKey->getPushAdvertisementRedisKey(),
									json_encode([
										'page_id' => $data['page_id'],
										'lang'    => $data['lang'],
										'version' => $data['version'],
									])
								);
							}

						}
						// 处理成功
						$this->returnData['success_count']++;
						if (empty($data['activity_id'])) {
							if ($data['is_home_b']) { // AB测试首页的B页处理
								PageModel::offlineHomeOnlinePageB($data['site_code']);
								PageModel::onlineNewHomePage($data['page_id'], PageModel::PAGE_STATUS_HAS_ONLINE_B);
							} else {
								PageModel::offlineHomeOnlinePage($data['site_code']);
								PageModel::onlineNewHomePage($data['page_id'], PageModel::PAGE_STATUS_HAS_ONLINE);
							}
						}
					} else {
						if (empty($data['activity_id'])) {
							PageModel::onlineNewHomePage($data['page_id'], PageModel::PAGE_STATUS_HAS_PUSH_FAIL);
						}
						// 处理失败，触发异常
						$data['errorCount'] = ($data['errorCount'] ?? 0) + 1;// 记录失败的次数
						$data['errorMsg'] = $msg;// 记录失败的原因
						throw new Exception('redis list error：' . $msg . ':' . json_encode($data));
					}
				} else {
					sleep(1);
				}
			} catch (Exception $e) {
				$this->processPushPageError($e, $key, $data);
			}
			$data = null;
		}

		Yii::info(
			'定时任务pushPage请求End：' . $timestamp . '----' . json_encode($this->returnData['error_html']),
			__METHOD__
		);

		return app()->helper->arrayResult($this->codeSuccess, '发布成功', $this->returnData);
	}

	/**
	 * 从大数据中转库同步数据
	 *
	 * @return array
	 */
	public function syncBigDataMySqlData ()
	{
		set_time_limit(60);
		$startTimestamp = app()->helper->microtime();
		$message = sprintf('从大数据中转库同步数据开始： %s', $startTimestamp);
		Yii::info($message, __METHOD__);

		try {
			//同步数据
			$bigDataSyncComponent = new BigDataSyncComponent();
			$tableSyncInfoList = $bigDataSyncComponent->syncMySqlActivitySaleData();
			$syncMessage = '';
			foreach ($tableSyncInfoList as $tableSyncInfo) {
				$syncMessage .= sprintf('从 %s 表 ID %d 开始同步 %d 条记录;', $tableSyncInfo[0], $tableSyncInfo[1], $tableSyncInfo[2]);
			}

			$useTime = bcsub(app()->helper->microtime(), $startTimestamp, 6);
			$message = sprintf('从大数据中转库同步数据结束： %s 共执行了 %s 秒。', $syncMessage, $useTime);
			Yii::info($message, __METHOD__);

			return app()->helper->arrayResult($this->codeSuccess, $message);
		} catch (\Exception $e) {
			$message = sprintf('从大数据中转库同步数据错误： %s', $e->getMessage());
			Yii::error($message, __METHOD__);

			return app()->helper->arrayResult($this->codeFail, $message);
		}
	}

	/**
	 * 处理队列数据
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws \Throwable
	 */
	private function processData ($data)
	{
		if ($data && isset($data['local_path']) && isset($data['s3_key'])
			&& isset($data['lang']) && isset($data['log_data'])
		) {
			return $this->putObjectToS3($data);
		}

		return [false, 'redis 数据格式不规范'];
	}

	/**
	 * 处理pushPage任务的错误
	 *
	 * @param Exception $e
	 * @param string    $key
	 * @param array     $data
	 */
	private function processPushPageError (Exception $e, string $key, array &$data)
	{
		$this->returnData['fail_count']++;
		if (!empty($data['errorCount']) && $data['errorCount'] > self::PUSH_ERROR_UPPER_LIMIT) {
			// 超过次数上限的，单独记录日志，不放回队列了
			Yii::info('定时任务pushPage对单个页面错误次数超过上限：' . json_encode($data), 'task');
		} else {
			// 将数据放回队列
			app()->redis->rpush($key, json_encode($data));
		}
		$errMsg = $e->getMessage() . '(' . $e->getFile() . ':' . $e->getLine() . ')';
		// 发送告警信息
		app()->rms->reportS3PushError($errMsg . var_export($data, true));
		$this->returnData['error_html'][] = $errMsg;
	}

	/**
	 * 清理push队列
	 *
	 * @return array
	 */
	public function cleanPushCache ()
	{
		$key = app()->redisKey->getPushTaskRedisKey();
		$count = app()->redis->llen($key);
		app()->redis->del($key);

		return app()->helper->arrayResult(
			$this->codeSuccess,
			'清理成功',
			['count' => $count]
		);
	}

	/**
	 * 查看push队列详情
	 *
	 * @return array
	 */
	public function getPushCacheInfo ()
	{
		$key = app()->redisKey->getPushTaskRedisKey();
		$count = app()->redis->llen($key);
		$pagination = Pagination::new($count);
		$start = ($pagination->page - 1) * $pagination->pageSize;
		$end = $start + $pagination->pageSize - 1;

		$list = app()->redis->lrange($key, $start, $end);
		$list = array_map(function ($item) {
			return json_decode($item, true);
		}, $list);

		return app()->helper->arrayResult(
			$this->codeSuccess,
			'查询成功',
			[
				'key'         => $key,
				'currentTime' => date('Y-m-d H:i:s'),
				'list'        => $list,
				'pagination'  => [
					$pagination->pageParam     => (int)$pagination->page + 1,
					$pagination->pageSizeParam => (int)$pagination->pageSize,
					'totalCount'               => (int)$pagination->totalCount
				]
			]
		);
	}

	/**
	 * 查看refresh队列详情
	 *
	 * @return array
	 */
	public function getRefreshCacheInfo ()
	{
		$key = app()->redisKey->getRefreshTaskRedisKey();
		$count = app()->redis->zcard($key);
		$pagination = Pagination::new($count);
		$start = ($pagination->page - 1) * $pagination->pageSize;
		$end = $start + $pagination->pageSize - 1;

		$list = app()->redis->zrange($key, $start, $end);
		$pageList = $list ? PageModel::getActivityInfosByPageIds($list) : [];
		$pageList = $pageList ? array_column($pageList, null, 'id') : [];
		$list = array_map(function ($item) use ($key, $pageList) {
			$itemData = [
				'page_id'            => (int)$item,
				'page_name'          => '',
				'page_status'        => '',
				'page_is_delete'     => '',
				'refresh_gap'        => '',
				'activity_id'        => '',
				'activity_name'      => '',
				'activity_status'    => '',
				'activity_is_delete' => '',
				'next_refresh_time'  => date('Y-m-d H:i:s', app()->redis->zscore($key, $item))
			];
			if (isset($pageList[$item])) {
				$itemData['page_name'] = $pageList[$item]['title'];
				$itemData['page_status'] = (int)$pageList[$item]['status'];
				$itemData['page_is_delete'] = (int)$pageList[$item]['is_delete'];
				$itemData['refresh_gap'] = (int)$pageList[$item]['refresh_time'];
				$itemData['activity_id'] = (int)$pageList[$item]['activity_id'];
				$itemData['activity_name'] = $pageList[$item]['name'];
				$itemData['activity_status'] = (int)$pageList[$item]['activity_status'];
				$itemData['activity_is_delete'] = (int)$pageList[$item]['activity_is_delete'];
			}

			return $itemData;
		}, $list);

		return app()->helper->arrayResult(
			$this->codeSuccess,
			'查询成功',
			[
				'key'         => $key,
				'currentTime' => date('Y-m-d H:i:s'),
				'list'        => $list,
				'pagination'  => [
					$pagination->pageParam     => (int)$pagination->page + 1,
					$pagination->pageSizeParam => (int)$pagination->pageSize,
					'totalCount'               => (int)$pagination->totalCount
				]
			]
		);
	}

	/**
	 * 巡查各站点头尾部内容变化情况
	 *
	 * @return array
	 */
	public function discoverHeadFooterReplace ()
	{
		$sitesConf = app()->params['sites'];
		try {
			$msgArray = [];
			foreach ($sitesConf as $site => $value) {
				if (!empty($value['headFooterMonitorDomain'])) {
					foreach ($value['headFooterMonitorDomain'] as $module => $conf) {
						$return = $this->checkHeadFooterContent($site, $module, $conf);
						$msgArray = array_merge($msgArray, $return);
					}
				}
			}
			//发送告警
			if (!empty($msgArray)) {
				app()->rms->reportHeadFooterError('站点：' . implode(', ', $msgArray));
			}

			return app()->helper->arrayResult($this->codeSuccess, '站点头尾部内容变更检查完成', $this->returnHeadFooterData);
		} catch (ServerException $exception) {//请求页面地址异常
			Yii::error($exception->getMessage());
		}

		return app()->helper->arrayResult($this->codeSuccess, '站点头尾部内容变更检查完成', $this->returnHeadFooterData);
	}

	/**
	 * 检查头尾部是否有更新
	 *
	 * @param string $module
	 * @param string $site
	 * @param array  $conf
	 *
	 * @return array
	 */
	private function checkHeadFooterContent (string $site, string $module, array $conf)
	{
		$redisKey = app()->redisKey->getSiteHeadFooterMd5Key();
		$hfList = app()->redis->hgetall($redisKey);
		$siteMsg = [];
		foreach ($conf as $lang => $domain) {
			$promises = trim((new Client())->get($domain)->getBody()->getContents());
			if (!empty($promises)) {
				$siteCode = "{$module}-{$site}-{$lang}";
				//检查头尾部是否有更新
				if (!empty($hfList[$siteCode]) && (md5($promises) != $hfList[$siteCode])) {
					//发送告警
					array_push($siteMsg, "{$siteCode}头尾部内容发生变化");
					app()->redis->hset($redisKey, $siteCode, md5($promises));
					$this->returnHeadFooterData['change_count']++;
				} elseif (empty($hfList[$siteCode]) && !empty($promises)) {
					app()->redis->hset($redisKey, $siteCode, md5($promises));
				}
			} else {
				array_push($siteMsg, "{$site}头尾部内容为空");
			}
			$this->returnHeadFooterData['total_count']++;
		}

		return $siteMsg;
	}

	/**
	 * 更新上线页面产品
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function updateOnlineGoods ()
	{
		$timestamp = time();
		Yii::info('定时任务更新上线产品请求Start：' . $timestamp, __METHOD__);
		$lockKey = app()->redisKey->getOnlineGoodsRedisKey();
		app()->redis->del($lockKey);
		if (null === app()->redis->set($lockKey, 1, 'EX', 60, 'NX')) {
			Yii::info('定时任务更新上线产品请求End：' . $timestamp, __METHOD__);

			return app()->helper->arrayResult($this->codeFail, '已有定时任务正在消费队列，请等待1分钟后再试');
		}

		ignore_user_abort(true);
		set_time_limit(60);
		$redisKey = app()->redisKey->getOnlinePagesRedisKey();
		$total = app()->redis->llen($redisKey);
		for ($i = 0; $i < $total; $i++) {
			$data = json_decode(app()->redis->lpop($redisKey), true);
			if (!empty($data)) {
				try {
					foreach ($data as $activityId => $pageIds) {
						list($success, $errorMsg) = (new CommonPageComponent())->batchCreateOnlinePageHtml($pageIds, $activityId, true, true);
						$this->returnData['success_count'] += \count($pageIds) - \count($errorMsg);
						$this->returnData['fail_count'] += \count($errorMsg);

						if (!$success && !empty($errorMsg)) {
							array_push($this->returnData['error_html'], ...$errorMsg);
						}
					}
				} catch (\Exception $e) {
					array_push($this->returnData['error_html'], ['message' => $e->getMessage(), 'page_id' => '1111', 'data' => []]);
				}

			}
		}


		//删除锁
		app()->redis->del($lockKey);

		Yii::info(
			'定时任务更新上线产品请求End：' . $timestamp . '----errorHtml:' . json_encode($this->returnData['error_html']),
			__METHOD__
		);

		return app()->helper->arrayResult($this->codeSuccess, '刷新成功', $this->returnData);
	}


	/**
	 * 更新上线页面产品
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function getOnlinePages ()
	{
		$timestamp = time();
		Yii::info('定时任务获取上线页面请求Start：' . $timestamp, __METHOD__);
		$lockKey = app()->redisKey->getOnlinePagesLockRedisKey();
		if (null === app()->redis->set($lockKey, 1, 'EX', 800, 'NX')) {
			Yii::info('定时任务获取上线页面请求End：' . $timestamp, __METHOD__);

			return app()->helper->arrayResult($this->codeFail, '已有定时任务正在执行，请等待15分钟后再试');
		}


		$data = [];
		$redisKey = app()->redisKey->getOnlinePagesRedisKey();
		//获取已经上线的页面
		$pages = PageModel::find()->alias('p')
			->leftJoin(ActivityModel::tableName() . ' as a', 'p.activity_id = a.id')
			->where(['p.status' => 2, 'mold' => 1])->select('p.id,p.activity_id,p.site_code')
			->andWhere(['>', 'p.update_time', time() - 15 * 3600 * 24])
			->asArray()->all();
		if (!empty($pages)) {
			foreach ($pages as $value) {
				$data[$value['site_code']][$value['activity_id']][] = $value['id'];
			}
			foreach ($data as $row) {
				foreach ($row as $key => $value) {
					app()->redis->lpush($redisKey, json_encode([$key => $value]));
				}
				$this->returnData['success_count'] += count($row);
			}

		}
		Yii::info(
			'定时任务获取上线页面请求End：' . $timestamp . '----需要更新页面:' . json_encode($data),
			__METHOD__
		);

		return app()->helper->arrayResult($this->codeSuccess, '获取成功', $this->returnData);
	}

	/**
	 * 推送html css js到网站
	 *
	 */
	public function pushData ()
	{
		$timestamp = time();
		Yii::info('定时任务pushAdvertisement请求Start：' . $timestamp, __METHOD__);
		set_time_limit(60);

		$key = app()->redisKey->getPushAdvertisementRedisKey();
		$time = app()->request->get('time', 60);
		$time = $time > 0 ? $time : 60;
		while (time() - $_SERVER['REQUEST_TIME'] < $time) {

			$data = json_decode(app()->redis->lpop($key), true);
			try {
				if (!empty($data)) {
					list($status, $errorMsg) = $this->pushWebData($data);
					if ($status) {
						$this->returnData['success_count'] += 1;
					} else {
						$this->returnData['fail_count'] += 1;
						$this->returnData['error_html'][] = $errorMsg;
					}
				} else {
					break;
				}
			} catch (Exception $e) {
				$this->processPushPageError($e, $key, $data);
			}
			$this->returnData['total_count'] += 1;
		}

		Yii::info(
			'定时任务pushAdvertisement请求End：' . $timestamp . '----' . json_encode($this->returnData['error_html']),
			__METHOD__
		);

		return app()->helper->arrayResult(1, '推送成功', $this->returnData);
	}

	/**
	 * 处理推送信息
	 *
	 * @param array $param
	 *   --     int      $page_id
	 *   --     string   $lang
	 *
	 * @return  array
	 *          status
	 *          message
	 */
	private function pushWebData ($param)
	{
		$page_id = $param['page_id'] ?? '';
		$lang = $param['lang'] ?? '';
		$version = $param['version'] ?? '';
		$pushLog = PagePublishLogModel::find()->where([
			'page_id'     => $page_id,
			'lang'        => $lang,
			'action_type' => 1,
			'log_type'    => 2,
			'version'     => $version,
		])->select('file_type,s3_url,site_code')->asArray()->all();
		if (empty($pushLog)) {
			return [0, ['page_id' => $page_id, 'message' => '获取推送日记失败']];
		}
		if (count($pushLog) < 3) {
			app()->redis->rpush(app()->redisKey->getPushAdvertisementRedisKey(), json_encode($param));

			return [0, ['page_id' => $page_id, 'message' => '没有推送完成']];
		}

		$site_code = $js = $css = $html = '';
		foreach ($pushLog as $key => $value) {

			switch ($value['file_type']) {
				case 'css':
					$css = $value['s3_url'];
					break;

				case 'js':
					$js = $value['s3_url'];
					break;

				case 'html':
					$html = $value['s3_url'];
					break;
			}
			$site_code = $value['site_code'];
		}
		if (!isset(app()->params['sites'][$site_code]['advertisement-push'])) {

			return [0, ['page_id' => $page_id, 'message' => '没有配置网站推送接口']];
		}
		$pageLanguage = PageLanguageModel::find()->where([
			'page_id' => $page_id,
			'lang'    => $lang,
		])->one();
		if (empty($pageLanguage)) {
			return [0, ['page_id' => $page_id, 'message' => '页面记录不存在']];
		}
		$platform = explode('-', $site_code)[1];
		$pushData = [
			'goods_sn' => $pageLanguage->goods_sn,
			'lang'     => $lang,
			'platform' => $platform,
			'html'     => $html,
			'css'      => $css,
			'js'       => $js,
			'customJs' => '',
		];

		$return = $this->WebService->slient()->asArray()->pushHtml($site_code, $pushData);
		if (empty($return['code'])) {

			$pageLanguage->status = 1;
			if (!$pageLanguage->update(true)) {
				return [0, $pageLanguage->flattenErrors(', ')];
			}

			return [1, ''];

		} else {//推送失败
			return [0, ['page_id' => $page_id, 'message' => $return['message']]];
		}

	}

	/**
	 * UI组件自动刷新任务
	 *
	 * @return array
	 */
	public function uiAutoRefresh ()
	{
		ini_set('memory_limit', '1G');

		$websiteCode = SiteConstants::SITE_GROUP_CODE_RG;
		$lockKey = 'geshop:task:' . $websiteCode . ':ui-auto-refresh:lock';
		if (null === app()->redis->set($lockKey, 1, 'EX', 3600, 'NX')) {
			return app()->helper->arrayResult(1, 'UI组件定时更新任务后台运行中，无需再次操作', $this->returnData);
		}

		try {
			app()->session->open();
			RequestUtils::closeConnectionAndFlush('UI组件定时更新任务切入后台运行，请去日志中查看结果');
			app()->response->isSent = true;

			set_time_limit(0);
			ignore_user_abort(true);

			$autoPageTask = new \app\components\auto\AutoPageCrontab($websiteCode);
			$autoPageTask->refreshPageAsyncData();
		} catch (\Throwable $throwable) {
			\Yii::error('UI组件定时更新任务错误： ' . $throwable->getMessage(), __METHOD__);
		} finally {
			app()->redis->del($lockKey); // 删除锁
		}
	}

	/**
	 * 消费UI组件异步接口兜底队列任务
	 *
	 * @return array
	 */
	public function uiAsyncApiFallback ()
	{
		ini_set('memory_limit', '1G');

		$websiteCode = SiteConstants::SITE_GROUP_CODE_RG;
		$lockKey = 'geshop:task:' . $websiteCode . ':ui-aync-api-fallback:lock';
		if (null === app()->redis->set($lockKey, 1, 'EX', 3600, 'NX')) {
			return app()->helper->arrayResult(1, '消费UI组件异步接口兜底队列任务后台运行中，无需再次操作', $this->returnData);
		}

		try {
			app()->session->open();
			RequestUtils::closeConnectionAndFlush('消费UI组件异步接口兜底队列任务切入后台运行，请去日志中查看结果');
			app()->response->isSent = true;

			set_time_limit(0);
			ignore_user_abort(true);

			$pageAsyncApiCrontab = new \app\components\fallback\api\PageAsyncApiCrontab($websiteCode);
			$pageAsyncApiCrontab->consumeUiAsyncApiFallbackQueue();
		} catch (\Throwable $throwable) {
			\Yii::error('消费UI组件异步接口兜底队列任务错误： ' . $throwable->getMessage(), __METHOD__);
		} finally {
			app()->redis->del($lockKey); // 删除锁
		}
	}

	/**
	 * 定时检查一键刷新头尾页任务执行情况
	 */
	public function checkRefreshTaskStatus ()
	{
		$sitePlatform = ['rg-pc', 'rg-wap', 'rg-app', 'rw-pc', 'rw-wap', 'rw-app'];
		$exceptionSite = [];
		foreach ($sitePlatform as $siteCode) {
			$logData = SiteUpdateLogModel::getSiteCurrentDateLog($siteCode);
			if (
				!empty($logData)
				&& $logData->status != SiteUpdateLogModel::STATUS_COMPLETED
				&& (($_SERVER['REQUEST_TIME'] - $logData->create_time) > 3600)
			) {
				app()->rms->reportHeadFooterError(
					var_export(
						[
							'site_code'   => $siteCode,
							'task_id'     => $logData->id,
							'page_ids'    => $logData->page_ids,
							'create_time' => date('Y-m-d H:i:s', $logData->create_time),
							'create_user' => $logData->create_user,
							'msg'         => '一键刷新头尾页任务异常.'
						],
						true
					)
				);
				array_push($exceptionSite, $siteCode);
			}
		}

		$message = !empty($exceptionSite) ? (implode(',', $exceptionSite) . '站点一键刷新任务异常.') : '一键刷新任务执行正常';

		return app()->helper->arrayResult($this->codeSuccess, $message);
	}
}
