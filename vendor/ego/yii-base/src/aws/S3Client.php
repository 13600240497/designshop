<?php

namespace ego\aws;

use yii;
use yii\base\Component;
use Aws\S3\S3Client as AwsS3Client;
use Exception;

/**
 * 亚马逊s3存储组件
 */
class S3Client extends Component
{
    /**
     * @var array s3配置
     */
    public $config = [];
    /**
     * @var string 默认存储桶
     */
    public $defaultBucket = null;
    /**
     * @var AwsS3Client
     */
    protected $client = null;
    /**
     * @var bool 调用出错时不抛出抛出异常？
     */
    protected $slient = true;
    
    /**
     * 执行`AwsS3Client::getObject`
     *
     * @param            $s3path
     * @param null       $bucket
     * @param array|null $headers
     *
     * @return bool
     */
    public function getObject($s3path, $bucket = null, array $headers = null)
    {
        $arguments = [
            'Bucket'     => $bucket ?: $this->defaultBucket,
            'Key'        => ltrim(
                str_replace('\\', '/', $s3path),
                '/'
            )
        ];
        $headers = $headers ?: $this->getDefaultHeaders();
        $arguments = array_merge($headers, $arguments);
    
        try {
            $this->client->getObject($arguments);
        } catch (Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 执行`AwsS3Client::putObject`
     *
     * https://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectPUT.html
     *
     * @param string $sourceFile 文件绝对路径
     * @param string $s3path     s3存储路径
     * @param string $bucket     存储桶
     * @param array  $headers    文件头部信息
     *
     * @return \Aws\Result|string
     * @throws Exception
     */
    public function putObject($sourceFile, $s3path, $bucket = null, array $headers = null)
    {
        $arguments = [
            'Bucket'     => $bucket ?: $this->defaultBucket,
            'SourceFile' => $sourceFile,
            'Key'        => ltrim(
                str_replace('\\', '/', $s3path),
                '/'
            )
        ];
        //$headers = $headers ?: $this->getDefaultHeaders();
        //$arguments = array_merge($headers, $arguments);
        
        try {
            $result = $this->client->putObject($arguments);
            $this->client->waitUntil('ObjectExists', [
                'Bucket' => $arguments['Bucket'],
                'Key'    => $arguments['Key'],
            ]);
            Yii::info(
                $sourceFile . ' -> ' . $s3path . "\n{$result}",
                __METHOD__
            );
        } catch (Exception $e) {
            $result = get_class($e) . ': ' . $e->getMessage();
            Yii::warning(
                $sourceFile . ' -> ' . $s3path . "\n{$result}",
                __METHOD__
            );
            if (!$this->slient) {
                throw $e;
            }
        }
        
        return $result;
    }
    
    /**
     * 执行`AwsS3Client::deleteObject`
     *
     * https://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectDELETE.html
     *
     * @param string $s3path s3存储路径
     * @param string $bucket 存储桶
     *
     * @return \Aws\Result|string
     * @throws Exception
     */
    public function deleteObject($s3path, $bucket = null)
    {
        $arguments = [
            'Bucket' => $bucket ?: $this->defaultBucket,
            'Key'    => ltrim(
                str_replace('\\', '/', $s3path),
                '/'
            )
        ];
        try {
            $result = $this->client->deleteObject($arguments);
            Yii::info($s3path . "\n{$result}", __METHOD__);
        } catch (Exception $e) {
            $result = get_class($e) . ': ' . $e->getMessage();
            Yii::warning($s3path . "\n{$result}", __METHOD__);
            if (!$this->slient) {
                throw $e;
            }
        }
        
        return $result;
    }
    
    /**
     * 调用时抛出异常？
     *
     * @param bool $slient
     *
     * @return $this
     */
    public function slient($slient = true)
    {
        $this->slient = $slient;
        
        return $this;
    }
    
    /**
     * 获取上传文件的默认头部信息
     *
     * @return array
     */
    public function getDefaultHeaders()
    {
        return [
            'CacheControl' => 'max-age=315360000',
            'Expires'      => gmdate('D, d M Y H:i:s T', strtotime('+10 years'))
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->client = new AwsS3Client($this->config);
    }
    
    /**
     * 执行`AwsS3Client::copyObject`
     *
     * @param            $sourceFile
     * @param            $s3path
     * @param null       $bucket
     * @param array|null $headers
     *
     * @return \Aws\Result|string
     * @throws Exception
     */
    public function copyObject($sourceFile, $s3path, $bucket = null, array $headers = null)
    {
        $arguments = [
            'Bucket'     => $bucket ?: $this->defaultBucket,
            'Key'        => ltrim(
                str_replace('\\', '/', $s3path),
                '/'
            ),
            'CopySource' => $sourceFile
        ];
        $headers = $headers ?: $this->getDefaultHeaders();
        $arguments = array_merge($headers, $arguments);
       
        try {
            $result = $this->client->copyObject($arguments);
            $this->client->waitUntil('ObjectExists', [
                'Bucket' => $arguments['Bucket'],
                'Key'    => $arguments['Key'],
            ]);
            Yii::info(
                $sourceFile . ' -> ' . $s3path . "\n{$result}",
                __METHOD__
            );
        } catch (Exception $e) {
            $result = get_class($e) . ': ' . $e->getMessage();
            Yii::warning(
                $sourceFile . ' -> ' . $s3path . "\n{$result}",
                __METHOD__
            );
            if (!$this->slient) {
                throw $e;
            }
        }
        
        return $result;
    }
}
