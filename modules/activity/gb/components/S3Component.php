<?php

namespace app\modules\activity\gb\components;

use app\modules\common\gb\components\CommonCrontabComponent;
use ego\base\JsonResponseException;
use Yii;

/**
 * 同步S3的resources目录
 */
class S3Component extends Component
{
    /**
     * @var string 待同步的目录名
     */
    private $syncDir = 'resources';

    /**
     * 同步上传文件
     * @return array
     * @throws JsonResponseException
     */
    public function syncResource()
    {
        $key = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getSyncResourcesRedisKey();
        if (null === app()->redis->set($key, 1, 'EX', 180, 'NX')) {
            $ttl = app()->redis->ttl($key);
            throw new JsonResponseException($this->codeFail, '同步任务正在进行中，请勿重复操作，大概剩余：' . $ttl . 's');
        }

        $this->obFlush('resources目录同步任务已提交，文件较多，请等待3分钟后再查看');

        ignore_user_abort(true);
        set_time_limit(60 * 10);
        $resourceDir = app()->basePath . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . $this->syncDir;
        $fileList = $this->getFileList($resourceDir);

        $errorCount = 0;
        if (!empty($fileList)) {
            foreach ($fileList as $file) {
                $filePath = $this->getS3KeyByPath($file);
                $s3Res = app()->s3PublishStatic->putObject($file, $filePath);
                Yii::info('静态资源推送s3结果：' . $s3Res, __METHOD__);
                if (\is_string($s3Res)) {
                    $errorCount++;
                }
            }
        }

        if ($errorCount) {
            return app()->helper->arrayResult($this->codeFail, $this->msgFail, ['errorCount' => $errorCount]);
        }

        return app()->helper->arrayResult($this->codeSuccess, '同步成功');
    }

    /**
     * 获取所有文件列表
     * @param string $dir 文件夹路径
     * @return array
     */
    private function getFileList($dir)
    {
        $files = array();
        if (is_dir($dir) && ($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                        $list = $this->getFileList($dir . DIRECTORY_SEPARATOR . $file);
                        !empty($list) && array_push($files, ...$list);
                    } else {
                        $files[] = $dir . DIRECTORY_SEPARATOR . $file;
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * 根据文件在本地存储的绝对路径，获取对应在S3上的存储key
     * @param string $path 静态资源的本地路径
     * @return string
     */
    private function getS3KeyByPath($path)
    {
        $explode = explode(DIRECTORY_SEPARATOR, $path);

        return app()->params['s3PublishConf']['staticKeyPre'] . '/' .
            implode(DIRECTORY_SEPARATOR, \array_slice($explode, array_search($this->syncDir, $explode, true)));
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
        if (\function_exists('fastcgi_finish_request')) { // yii或yaf默认不会立即输出，加上此句即可（前提是用的fpm）
            fastcgi_finish_request(); // 响应完成, 立即返回到前端,关闭连接
        }
    }
}
