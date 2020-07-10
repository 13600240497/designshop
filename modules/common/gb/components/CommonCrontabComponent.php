<?php

namespace app\modules\common\gb\components;

use app\base\Pagination;
use app\base\SwooleClient;
use app\modules\common\gb\models\{
    PageModel
};
use app\modules\base\components\BigDataSyncComponent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Yii;

use app\modules\common\gb\traits\{
    CommonVerifyStatusTrait, CommonPublishTrait
};
use Exception;

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
    const REDIS_PREFIX           = 'gb::';
    
    //返回数据
    private $returnData;
    private $returnHeadFooterData;
    
    /**
     * init
     */
    public function init()
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
    public function refreshPage()
    {
        $timestamp = time();
        Yii::info('定时任务refreshPage请求Start：' . $timestamp, __METHOD__);
        $lockKey = self::REDIS_PREFIX . app()->redisKey->getRefreshTaskLockKey();
        if (null === app()->redis->set($lockKey, 1, 'EX', 60, 'NX')) {
            Yii::info('定时任务refreshPage请求End：' . $timestamp, __METHOD__);
            
            return app()->helper->arrayResult($this->codeFail, '已有定时任务正在消费队列，请等待1分钟后再试');
        }
        
        ignore_user_abort(true);
        set_time_limit(60);
        
        $data = $validPageInfos = [];
        $key = self::REDIS_PREFIX . app()->redisKey->getRefreshTaskRedisKey();
        $list = app()->redis->zrangebyscore($key, 0, $timestamp);
        if (!empty($list)) {
            $this->returnData['total_count'] = \count($list);
            //按活动ID分组组装数据
            $pageInfos = PageModel::getValidActivityIdsByPageIds($list);
            if (!empty($pageInfos)) {
                $validPageInfos = array_column($pageInfos, null, 'id');
                array_map(function ($page) use (&$data) {
                    $data[ (int) $page['activity_id'] ][] = (int) $page['id'];
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
    private function refreshPageData($params, &$data)
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
                        $options[] = $timestamp + (int) $validPageInfos[ $pageId ]['refresh_time'];
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
    public function offlinePage()
    {
        $timestamp = time();
        Yii::info('定时任务offlinePage请求Start：' . $timestamp, __METHOD__);
        $lockKey = self::REDIS_PREFIX . app()->redisKey->getOfflineTaskLockKey();
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
                $data[ $page['activity_id'] ][] = ['page_id' => $page['id'], 'pipeline' => $page['pipeline']];
            }, $list);
            //按活动下线
            foreach ($data as $activityId => $row) {
                $successIds = [];
                foreach ($row as $value) {
                    try {
                        list($success, $errorMsg) = $pageComponent->batchCreateOfflinePageHtml([$value['page_id']], $activityId, $value['pipeline']);
                        if ($success) {
                            $successIds[] = $value['page_id'];
                            $this->returnData['success_count'] += 1;
                        } else {
                            $this->returnData['fail_count'] += 1;
                            array_push($this->returnData['error_html'], ...$errorMsg);
                            continue;
                        }
                    } catch (Exception $e) {
                        $this->returnData['fail_count'] += 1;
                        $errorItem = ['page_id' => $value['page_id'], 'message' => $e->getMessage()];
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
     * 重新组装一下redis里取出来的数据
     *
     * @param array $redisData
     *
     * @return array
     */
    /*private function buildRedisData(array $redisData, string $dataKey)
    {
        return array_map(function ($item) use ($dataKey) {
            $item = \GuzzleHttp\json_decode($item, true);
            $content = app()->redis->get($item['redis_key']);
            if (empty($content)) {
                //内容过期后，从数据库去获取
                $content = $this->getContentCache(
                    [
                        $item['page_id'],
                        $item['lang'],
                        $item['site_code'],
                        $item['file_type'],
                        $item['css_s3'],
                        $item['js_s3']
                    ]
                );
            }
            if (false === file_put_contents($item['local_path'], $content) || !file_exists($item['local_path'])) {
                Yii::error('文件内容写入失败' . $item['local_path'], __METHOD__);
                
                return [false, '文件内容写入失败：' . $item['local_path']];
            }
            
            $item['data_key'] = $dataKey;
            
            return $item;
        }, $redisData);
    }
    
    private function arrayWalkRecursiveCdnUrls(array $urls)
    {
        if (2 == arrayLevel($urls)) {
            $newUrls = [];
            array_walk_recursive($urls, function ($item) use (&$newUrls) {
                array_push($newUrls, $item);
            });
        }
        
        return $newUrls ?? $urls;
    }*/
    
    /**
     * 处理redis队列，推送页面到S3
     *
     * @return array
     * @throws \Throwable
     */
    public function pushPage()
    {
        $timestamp = time();
        Yii::info('定时任务pushPage请求Start：' . $timestamp, __METHOD__);
        set_time_limit(65);
        
        $key = self::REDIS_PREFIX . app()->redisKey->getPushTaskRedisKey();
        $time = app()->request->get('time', 60);
        $time = $time > 0 ? $time : 60;
        while (time() - $_SERVER['REQUEST_TIME'] < $time) {
            $data = json_decode(app()->redis->lpop($key), true);
            try {
                if ($data) {
                    $this->returnData['total_count']++;
                    list($putRes, $msg) = $this->processData($data);
                    
                    if ($putRes) {
                        if (!empty($data['type']) && intval($data['type']) == 1) {//推广落地页
                            if ($data['file_type'] == 'html') {
                                app()->redis->rpush(self::REDIS_PREFIX . app()->redisKey->getPushAdvertisementRedisKey(),
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
                            PageModel::offlineHomeOnlinePage($data['site_code']);
                            PageModel::onlineNewHomePage($data['page_id'], PageModel::PAGE_STATUS_HAS_ONLINE);
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
    public function syncBigDataMySqlData()
    {
        set_time_limit(60);
        $startTimestamp = app()->helper->microtime();
        $message = sprintf('从大数据中转库同步数据开始： %s', $startTimestamp);
        Yii::info($message, __METHOD__);
        
        try {
            //同步数据
            $bigDataSyncComponent = new BigDataSyncComponent();
            list($lastRowId, $copyRowNum) = $bigDataSyncComponent->syncMySqlActivitySaleData();
            
            $useTime = bcsub(app()->helper->microtime(), $startTimestamp, 6);
            $message = sprintf('从大数据中转库同步数据结束： 本次从ID %d 开始,共同步 %d 条记录,执行了 %s 秒。', $lastRowId, $copyRowNum, $useTime);
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
    private function processData($data)
    {
        if ($data && isset($data['local_path']) && isset($data['s3_key'])
            && isset($data['lang']) && isset($data['log_data']) && isset($data['pipeline'])
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
    private function processPushPageError(Exception $e, string $key, array &$data)
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
    public function cleanPushCache()
    {
        $key = self::REDIS_PREFIX . app()->redisKey->getPushTaskRedisKey();
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
    public function getPushCacheInfo()
    {
        $key = self::REDIS_PREFIX . app()->redisKey->getPushTaskRedisKey();
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
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }
    
    /**
     * 查看refresh队列详情
     *
     * @return array
     */
    public function getRefreshCacheInfo()
    {
        $key = self::REDIS_PREFIX . app()->redisKey->getRefreshTaskRedisKey();
        $count = app()->redis->zcard($key);
        $pagination = Pagination::new($count);
        $start = ($pagination->page - 1) * $pagination->pageSize;
        $end = $start + $pagination->pageSize - 1;
        
        $list = app()->redis->zrange($key, $start, $end);
        $pageList = $list ? PageModel::getActivityInfosByPageIds($list) : [];
        $pageList = $pageList ? array_column($pageList, null, 'id') : [];
        $list = array_map(function ($item) use ($key, $pageList) {
            $itemData = [
                'page_id'            => (int) $item,
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
            if (isset($pageList[ $item ])) {
                $itemData['page_name'] = $pageList[ $item ]['title'];
                $itemData['page_status'] = (int) $pageList[ $item ]['status'];
                $itemData['page_is_delete'] = (int) $pageList[ $item ]['is_delete'];
                $itemData['refresh_gap'] = (int) $pageList[ $item ]['refresh_time'];
                $itemData['activity_id'] = (int) $pageList[ $item ]['activity_id'];
                $itemData['activity_name'] = $pageList[ $item ]['name'];
                $itemData['activity_status'] = (int) $pageList[ $item ]['activity_status'];
                $itemData['activity_is_delete'] = (int) $pageList[ $item ]['activity_is_delete'];
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
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }
    
    /**
     * 巡查各站点头尾部内容变化情况
     *
     * @return string
     */
    public function discoverHeadFooterReplace()
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
        
        return '';
    }
    
    /**
     * 检查头尾部是否有更新
     *
     * @param string $module
     * @param string $site
     * @param array  $conf
     *
     * @return  array
     */
    private function checkHeadFooterContent(string $site, string $module, array $conf)
    {
        $redisKey = self::REDIS_PREFIX . app()->redisKey->getSiteHeadFooterMd5Key();
        $hfList = app()->redis->hgetall($redisKey);
        $siteMsg = [];
        foreach ($conf as $lang => $domain) {
            $promises = trim((new Client())->get($domain)->getBody()->getContents());
            $siteCode = "{$module}-{$site}-{$lang}";
            if (!empty($promises)) {
                //检查头尾部是否有更新
                if (!empty($hfList[ $siteCode ]) && (md5($promises) != $hfList[ $siteCode ])) {
                    //发送告警
                    array_push($siteMsg, "{$siteCode}头尾部内容发生变化");
                    app()->redis->hset($redisKey, $siteCode, md5($promises));
                    $this->returnHeadFooterData['change_count']++;
                } elseif (empty($hfList[ $siteCode ]) && !empty($promises)) {
                    app()->redis->hset($redisKey, $siteCode, md5($promises));
                }
            } else {
                array_push($siteMsg, "{$siteCode}头尾部内容为空");
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
    public function updateOnlineGoods()
    {
        $timestamp = time();
        Yii::info('定时任务更新上线产品请求Start：' . $timestamp, __METHOD__);
        $lockKey = self::REDIS_PREFIX . app()->redisKey->getOnlineGoodsRedisKey();
        if (null === app()->redis->set($lockKey, 1, 'EX', 60, 'NX')) {
            Yii::info('定时任务更新上线产品请求End：' . $timestamp, __METHOD__);
            
            return app()->helper->arrayResult($this->codeFail, '已有定时任务正在消费队列，请等待1分钟后再试');
        }
        
        ignore_user_abort(true);
        set_time_limit(60);
        $redisKey = self::REDIS_PREFIX . app()->redisKey->getOnlinePagesRedisKey();
        $total = app()->redis->llen($redisKey);
        for ($i = 0; $i < $total; $i++) {
            $data = json_decode(app()->redis->lpop($redisKey), true);
            if (!empty($data)) {
                try {
                    foreach ($data as $activityId => $row) {
                        foreach ($row as $pipeline => $pageIds) {
                            list($success, $errorMsg) = (new CommonPageComponent())->batchCreateOnlinePageHtml($pageIds, $activityId, $pipeline, true, true);
                            $this->returnData['success_count'] += \count($pageIds) - \count($errorMsg);
                            $this->returnData['fail_count'] += \count($errorMsg);
                            
                            if (!$success && !empty($errorMsg)) {
                                array_push($this->returnData['error_html'], ...$errorMsg);
                            }
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
    public function getOnlinePages()
    {
        $timestamp = time();
        Yii::info('定时任务获取上线页面请求Start：' . $timestamp, __METHOD__);
        $lockKey = self::REDIS_PREFIX . app()->redisKey->getOnlinePagesLockRedisKey();
        if (null === app()->redis->set($lockKey, 1, 'EX', 800, 'NX')) {
            Yii::info('定时任务获取上线页面请求End：' . $timestamp, __METHOD__);
            
            //return app()->helper->arrayResult($this->codeFail, '已有定时任务正在执行，请等待15分钟后再试');
        }
        
        
        $data = [];
        $redisKey = self::REDIS_PREFIX . app()->redisKey->getOnlinePagesRedisKey();
        //获取已经上线的页面
        $pages = PageModel::find()->where(['status' => 2])->select('id,activity_id,pipeline,site_code')->asArray()->all();
        if (!empty($pages)) {
            foreach ($pages as $value) {
                $data[ $value['site_code'] ][ $value['activity_id'] ][ $value['pipeline'] ][] = $value['id'];
            }
            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    app()->redis->lpush($redisKey, json_encode([$key => $value]));
                }
            }
            $this->returnData['success_count'] = count($data);
        }
        
        Yii::info(
            '定时任务获取上线页面请求End：' . $timestamp . '----需要更新页面:' . json_encode($data),
            __METHOD__
        );
        
        return app()->helper->arrayResult($this->codeSuccess, '获取成功', $this->returnData);
    }
    
}
