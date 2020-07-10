<?php

namespace app\commands\component;

use app\commands\models\PageModel;
use app\commands\models\PagePublishLogModel;
use app\commands\models\SiteUpdateLogModel;
use GuzzleHttp\Client;
use yii\console\Exception;

class WorkerComponent
{
    //HTML文件后缀
    protected $htmlFileType = ['html', 'async', 'native'];

    public function putObject(array $data)
    {
        if (in_array($data['file_type'], $this->htmlFileType)) {
            $s3Res = app()->s3->putObject($data['local_path'], $data['s3_key'], null,
                [   'CacheControl' => 'max-age=' . app()->params['cdnCacheControl'],
                    'Expires'=> gmdate('D, d M Y H:i:s T', strtotime(app()->params['cdnExpires']))
                ]
            );
        } else {
            $s3Res = app()->s3PublishStatic->putObject($data['local_path'], $data['s3_key']);
        }

        $s3Url = \is_string($s3Res) ? '' : $s3Res->get('ObjectURL');
        if (empty($s3Res)) {
            app()->rms->reportS3PushError('文件推送s3失败：' . var_export($data));
        }

        return $s3Url;
    }

    /**
     * 手动推送页面
     *
     * @param array $data
     */
    public function asyncPushPage(array $data)
    {

        if ('serial' == $data['mode']) {
            $result = array_column($data['result'], null, 'file_type');
            $tasks = array_filter($result, function ($item) {
                return in_array($item, ['css', 'js', 'async', 'native']);
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($tasks) && is_array($tasks)) {
	            $successReturn = [];
                foreach ($tasks as $key => $task) {
                    $successReturn[ $key ] = $this->putObject($task);
                }

	            if (count($tasks) == count(array_filter($successReturn))) {
		            $successReturn['html'] = $this->putObject($result['html']);
		            $this->operatePagePushStatus($successReturn, $result);

		            return;
	            }
            } else {
	            $successReturn['html'] = $this->putObject($result['html']);
	            $this->operatePagePushStatus($successReturn, $result);
            }
        } else {
            if (!empty($this->putObject($data['result']))) {
                return;
            }
        }
    }

    /**
     * 一键刷新页面头尾
     *
     * @param array $data
     *
     * @return array
     */
    public function asyncRefreshPushPage(array $data)
    {
        $successReturn = [];
        $result = array_column($data, null, 'file_type');

        if (!empty($result['html']['data_key'])) {
            app()->redis->decr($result['html']['data_key']);
        }

        $record = [
            'page_id'      => $result['html']['page_id'],
            'lang'         => $result['html']['lang'],
            'message'      => '页面推送失败',
            'status'       => SiteUpdateLogModel::PAGE_FAILED,
            'success_time' => 0
        ];

        $tasks = array_filter($result, function ($item) {
            return in_array($item, ['css', 'js', 'async', 'native']);
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($tasks) && is_array($tasks)) {
            foreach ($tasks as $key => $task) {
                $successReturn[ $key ] = $this->putObject($task);
            }

            if (count($tasks) == count(array_filter($successReturn))) {
                $successReturn['html'] = $this->putObject($result['html']);
                $this->operatePagePushStatus($successReturn, $result);

                $record = [
                    'page_id'      => $result['html']['page_id'],
                    'lang'         => $result['html']['lang'],
                    'message'      => '页面推送成功',
                    'status'       => SiteUpdateLogModel::PAGE_SUCCESS,
                    'success_time' => time()
                ];
            }
        } else {
            $successReturn['html'] = $this->putObject($result['html']);
            $this->operatePagePushStatus($successReturn, $result);
            $record = [
	            'page_id'      => $result['html']['page_id'],
	            'lang'         => $result['html']['lang'],
	            'message'      => '页面推送成功',
	            'status'       => SiteUpdateLogModel::PAGE_SUCCESS,
	            'success_time' => time()
            ];
        }

        return $record;
    }

    /**
     * 更新一键刷新推送日志
     *
     * @param array $data
     * @param int   $logId
     * @throws
     */
    public function recordRefreshPageLog(array $data, int $logId)
    {
        if (!empty($data) && is_array($data) && !empty($logId)) {
            $siteUpdateLog = SiteUpdateLogModel::getById($logId);
            if (!empty($siteUpdateLog->result)) {
                list($totalNum, $successNum, $failNum) = explode('，', $siteUpdateLog->result);
                $successCount = $failCount = 0;
                foreach ($data as $item) {
                    if (SiteUpdateLogModel::PAGE_SUCCESS == $item['status']) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                $total = substr($totalNum, strpos($totalNum, '：') + 3, strlen($totalNum));
                $success = substr($successNum, strpos($successNum, '：') + 3, strlen($successNum)) + $successCount;
                $fail = substr($failNum, strpos($failNum, '：') + 3, strlen($failNum)) + $failCount;
                $message = "{$totalNum}，推送成功数：" . $success . "，推送失败数：" . $fail;
                $siteUpdateLog->result = $message;
                if (($success + $fail) >= $total ) {
                    $siteUpdateLog->status = SiteUpdateLogModel::STATUS_COMPLETED;
                    $siteUpdateLog->complete_time = time();
                }
                if (!empty($siteUpdateLog->detail)) {
                    $detail = json_decode($siteUpdateLog->detail, true);
                    $data = array_merge($detail, $data);
                }
                $siteUpdateLog->detail = json_encode(array_values($data));

                $siteUpdateLog->update();
            }

	        \Yii::$app->db->close();
        }
    }

    /**
     * 操作页面发布状态
     *
     * @param array $successReturn
     * @param array $data
     * @throws
     */
    public function operatePagePushStatus(array $successReturn, array $data)
    {
        if (!empty($successReturn) && !empty($data)) {
            foreach ($data as $item_k => $item) {
                $item['s3_url'] = $successReturn[ $item_k ];
                $this->putObjectToS3($item);
                if (!empty($item['clear_url'])) $this->clearCDNCache($item['clear_url']);
                if (isset($item['clear_url_app'])) $this->clearCDNCache($item['clear_url_app']);
                if (isset($item['clear_url_language'])) $this->clearCDNCache($item['clear_url_language']);
            }
            if (empty($data['html']['activity_id'])) {//首页
                PageModel::$prefix = $this->getTablePrefixBySiteCode($data['html']['site_code']);
                if (false === PageModel::changeHomePageState($data['html']['page_id'])) {
                    app()->rms->reportS3PushError('更新首页发布状态失败：' . var_export($data));
                }
            }

	        \Yii::$app->db->close();
        }
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
        $logData = $data['log_data'];

        clearstatcache();//清理filesize等函数的缓存
        $fileSize = filesize($data['local_path']);
        $fileHash = hash_file('md5', $data['local_path']);

        PagePublishLogModel::$prefix = $this->getTablePrefixBySiteCode($data['site_code']);
        //更新本地文件生成记录的file_size和file_hash
        $resUpdate = pagePublishLogModel::updateAllData([
            'file_size' => $fileSize,
            'file_hash' => $fileHash
        ], [
            'log_type'  => pagePublishLogModel::LOG_TYPE_CREATE,
            'page_id'   => $data['page_id'],
            'lang'      => $data['lang'],
            'file_type' => $data['file_type'],
            'version'   => $data['version']
        ]);

        //上传成功后，记录日志
        $logData['s3_url'] = $data['s3_url'];
        $logData['file_size'] = $fileSize;
        $logData['file_hash'] = $fileHash;
        $resSave = pagePublishLogModel::insertAllData([$logData]);

        if (!$resSave || !$resUpdate) {
            // TODO 告警
            app()->rms->reportS3PushError('文件上传后更新本地记录出错：' . var_export($data));
        }
    }

    /**
     * 根据站点编码获取数据表前缀
     *
     * @param string $siteCode
     *
     * @return bool|string
     */
    private function getTablePrefixBySiteCode(string $siteCode)
    {

        $site = substr($siteCode, 0, strpos($siteCode, '-'));

        return in_array($site, ['zf', 'gb', 'dl','rg','suk']) ? $site : '';
    }

    /**
     * 清理CDN缓存
     *
     * @param string $url 待清理的URL
     */
    public function clearCDNCache($url)
    {
        $api = app()->params['clearCDNAPI'];
        echo date('Y-m-d H:i:s' , time()) . '开始CDN缓存清理返回原始值'.$url .PHP_EOL;
        if (empty($api)) {
            //未配置clearCDNAPI的则无需清理CDN缓存
            return;
        }
        /*if (strpos($url, '.css') !== false) {
            return;
        }
        if (strpos($url, '.js') !== false) {
            return;
        }
        if (stripos($url, 'html') !== false) {//防止首页清掉整站缓存
            $api .= '*';
        }*/
        $api .= $url;

        $responseData = [];
        try {
            $response = (new Client())->get($api);
            $response && $responseData = json_decode($response->getBody() . '', true);

            \Yii::info('完成CDN缓存清理返回原始值：' . $api . '----' . json_encode($responseData), __METHOD__);
            echo date('Y-m-d H:i:s' , time()) . '完成CDN缓存清理返回原始值：' . $api  . '----' . json_encode($responseData) .PHP_EOL;
        } catch (\Exception $e) {
            \Yii::error('CDN缓存清理Exception：' . $e->getMessage(), __METHOD__);
            echo 'CDN缓存清理Exception：' . $e->getMessage() .PHP_EOL;
        }

        if (!(isset($responseData['results']) && isset($responseData['results'][1])
            && $responseData['results'][1]['result'] && $responseData['results'][1]['result']['status']
            && $responseData['results'][1]['result']['status'] === 'success')
        ) {
            \Yii::error('CDN缓存清理失败：' . $api . '---' . json_encode($responseData), __METHOD__);
        }
    }
}
