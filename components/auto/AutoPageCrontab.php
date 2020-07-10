<?php
namespace app\components\auto;

use Yii;
use app\common\constants\SiteConstants;
use app\modules\common\models\PageModel as FZPageModel;
use app\modules\common\dl\models\PageModel as DLPageModel;
use app\modules\common\zf\models\PageModel as ZFPageModel;

/**
 * UI自动刷新，定时任务
 *
 * 注意： 这里是公共类同时支持RG、ZF、DL站点，变量注解以ZF站点类为例
 *
 * @author TianHaisen
 * @since 1.9.3
 */
class AutoPageCrontab
{
    /** @var string 网站简码，如： rg/zf/dl */
    private $websiteCode;

    /** @var int 成功发布页面个数 */
    private $successNum = 0;

    /** @var int 失败发布页面个数 */
    private $failNum = 0;

    /** @var string 要单独更新的渠道编码(只有ZF站点该参数才有效)，默认null 表示全渠道更新 */
    private $pipelineCode = null;

    /**
     * 构造函数
     * @param string $websiteCode
     */
    public function __construct($websiteCode)
    {
        $this->websiteCode = $websiteCode;
    }

    /**
     * 解析数据，并将结果json文件推送到S3
     *
     * @param string $pipelineCode 要单独更新的渠道编码(只有ZF站点该参数才有效)，默认null 表示全渠道更新
     */
    public function refreshPageAsyncData($pipelineCode = null)
    {
        $this->pipelineCode = $pipelineCode;

        $startTime = microtime(true);
        $message = sprintf('开始 %s 站点组件自动刷新', strtoupper($this->websiteCode));
        Yii::info(AutoRefreshUtils::getLogMessagePrefix($message), __METHOD__);

        $this->doRefreshPageAsyncData();

        $runTime = (microtime(true) - $startTime) * 1000;
        $message = sprintf(
            '完成 %s 站点组件自动刷新, 成功 %d 失败 %d 本次任务执行了 %d 毫秒',
            strtoupper($this->websiteCode), $this->successNum, $this->failNum, $runTime
        );
        Yii::info(AutoRefreshUtils::getLogMessagePrefix($message), __METHOD__);
    }

    /**
     * 解析数据，并将结果json文件推送到S3
     */
    private function doRefreshPageAsyncData()
    {
        // 获取所有需求更新的页面列表
        $pageModelList = ZFPageModel::getUiAutoRefreshPages($this->pipelineCode);
        if (empty($pageModelList) || !is_array($pageModelList)) {
            return;
        }

        foreach ($pageModelList as $pageModel) {

            // 页面下所有语言
            foreach ($pageModel->pageLanguages as $pageLanguageModel) {
                try {
                    $autoRefreshPage = new AutoRefreshPage($pageModel, $pageLanguageModel->lang);

                    // 解析UI组件异步数据
	                if (!empty($pageModel->is_native)) {
		                $jsonBody = $autoRefreshPage->parseAllNativeUiData(true, true);
	                } else {
		                $jsonBody = $autoRefreshPage->getAllUiAsyncDataAsJson(true, true);
	                }

                    if (empty($jsonBody) || '[]' === $jsonBody) {
                        continue;
                    }

                    // 推送json文件到s3
                    $redisData = $this->getPublishRedisData($pageModel, $pageLanguageModel, $jsonBody);
                    app()->swoole->init()->send(['data' => [$redisData], 'action' => 'asyncPushPage', 'mode' => 'multi']);

                    $this->successNum++;
                    unset($autoRefreshPage);
                } catch (\Throwable $throwable) {
                    $this->failNum++;

                    $_errorMessage = sprintf(
                        "%s in %s line %d",
                        $throwable->getMessage(), $throwable->getFile(), $throwable->getLine()
                    );

                    $message = sprintf(
                        '定时更新任务处理站点 %s 页面ID %d 语言 %s 时错误: %s',
                        $pageModel->site_code, $pageModel->id, $pageLanguageModel->lang, $_errorMessage
                    );
                    $message = AutoRefreshUtils::getLogMessagePrefix($message);
                    Yii::error($message, __METHOD__);
                    // $this->alarmReport($message);
                }
            }
        }
    }

    /**
     * 获取文件推送数据
     *
     * @param ZFPageModel $pageModel 页面Model对象
     * @param \app\modules\common\zf\models\PageLanguageModel $pageLanguageModel 页面语言Model对象
     * @param string $jsonBody UI组件异步数据json
     * @return array
     * @throws AutoRefreshException
     */
    private function getPublishRedisData($pageModel, $pageLanguageModel, $jsonBody)
    {
        $user = app()->user->username ?? '';
        $time = time();
        $version = app()->helper->microtime();
        $actionType = 1; // 操作类型| 1-上线 2-下线
        $logType = 2; // 日志类型| 1-缓存文件生成日志  2-发布S3日志
        $mold = 1;  // 活动类型 1 普通活动 2 活动推广

        $langCode = $pageLanguageModel->lang;
        $localFilePath = $this->getPublishFileLocalPath($pageModel, $langCode);
        $absFilePath = $this->getRelativePathByLocalPath($localFilePath);
        $filename = basename($localFilePath);

        if (false === file_put_contents($localFilePath, $jsonBody)) {
            throw new AutoRefreshException('文件内容写入失败：' . $localFilePath);
        }

        $log = [
            'version'             => $version,
            'log_type'            => $logType,
            'page_id'             => (int) $pageModel->id,
            'lang'                => $pageLanguageModel->lang,
            'site_code'           => $pageModel->site_code,
            'action_type'         => $actionType,
            'file_name'           => $filename,
            'file_type'           => AutoRefreshUi::PUBLISH_ASYNC_DATA_JSON_TYPE,
            'file_size'           => 0,
            'file_hash'           => '',
            'local_path'          => $absFilePath,
            's3_url'              => '',
            'diff'                => '',
            'ip'                  => app()->ip->get(true),
            'create_user'         => $user,
            'create_time'         => $time,
            'update_user'         => $user,
            'update_time'         => $time
        ];

        $item = [
            'route'               => app()->controller->getRoute(),
            'local_path'          => $localFilePath,
            's3_key'              => $absFilePath,
            'page_url'            => $this->getUrl($pageModel, $langCode, $filename),
            'activity_id'         => (int) $pageModel->activity_id,
            'page_id'             => $pageModel->id,
            'lang'                => $pageLanguageModel->lang,
            'version'             => $version,
            'file_type'           => AutoRefreshUi::PUBLISH_ASYNC_DATA_JSON_TYPE,
            'site_code'           => $pageModel->site_code,
            'redis_key'           => '',
            'css_s3'              => '',
            'js_s3'               => '',
            'mold'                => $mold,
            'is_home_b'           => false,
        ];

        $item['pipeline'] = $pageModel->pipeline;

        $item['log_data'] = $log;
        return $item;
    }

    /**
     * 获取推送文件本地路径
     *
     * @param ZFPageModel $pageModel 页面Model对象
     * @param string $langCode 语言简码
     * @return string 文件本地绝对路径
     */
    private function getPublishFileLocalPath($pageModel, $langCode)
    {
        $publishConfig = $this->getSiteConfig($pageModel, 's3PublishPath');
        $publishUri = $publishConfig[ $langCode ];
        $publishUri = str_replace('\\', '/', trim($publishUri, '/'));
        $localPath = Yii::getAlias('@runtime/'. $publishUri);
        if (!is_dir($localPath) && !mkdir($localPath, 0777, true) && !is_dir($localPath)) {
            return '';
        }

        if (!empty($pageModel->is_native)) {
	        return $localPath .'/'. AutoRefreshUtils::getAsyncDataNativeFileName($pageModel->id, $pageModel->pipeline, $langCode);
        } else {
	        return $localPath .'/'. AutoRefreshUtils::getAsyncDataJsFileName($pageModel->id);
        }
    }

    /**
     * 获取推送文件URL
     *
     * @param ZFPageModel $pageModel 页面Model对象
     * @param string $langCode 语言简码
     * @param string $filename 文件名称
     * @return string 访问URL
     */
    private function getUrl($pageModel, $langCode, $filename)
    {
        $domainConfig = $this->getSiteConfig($pageModel, 'secondary_domain');
        $domain = $domainConfig[$langCode];

        return rtrim($domain, '/') .'/'. $filename;
    }

    /**
     * 获取站点配置
     *
     * @param ZFPageModel $pageModel 页面Model对象
     * @param string $key 配置项
     * @return array
     */
    private function getSiteConfig($pageModel, $key)
    {
        $siteCode = $pageModel->site_code;

        return app()->params['sites'][ $siteCode ][ $key ][ $pageModel->pipeline ] ?? [];

    }

    /**
     * 根据文件在本地绝对路径返回相对路径
     *
     * @param string $localPath 文件在本地绝对路径
     * @return string 文件相对路径,如: /publish/www.zaful.com/en/test-zf-blog-42424.html
     */
    private function getRelativePathByLocalPath($localPath)
    {
        $explode = !empty($localPath) ? explode('runtime', $localPath) : [];
        return $explode && isset($explode[1]) ? $explode[1] : '';
    }

    /**
     * 错误告警
     *
     * @param string $message 报警信息
     * @param array $data 报警数据
     */
    private function alarmReport($message, $data = [])
    {
        app()->rms->reportS3PushError($message);
    }

}
