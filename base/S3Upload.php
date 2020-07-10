<?php
namespace app\base;

use Yii;

/**
 * S3上传组件
 */
class S3Upload
{
    /**
     * @var string 上传到S3的路径
     */
    private $s3UploadsPath;

    /**
     * Upload constructor.
     *
     * @param string $uploadPath s3的上传地址
     */
    public function __construct($uploadPath = false)
    {
        $this->s3UploadsPath = $uploadPath;
        if (!$uploadPath) {
            $this->s3UploadsPath = app()->params['s3UploadsConf']['staticKeyPre'] ?? 'uploads/';
        }
    }

    /**
     * 上传到S3工具
     *
     * @param string $file 文件名称
     * @param boolean $returnLocalUrl 是否只返回短的URL
     * @return mixed|null
     * @throws \Exception
     */
    public function saveToS3File($file, $returnLocalUrl = false)
    {
        list($fileName, $fileAllName) = $this->fileResolve($file);
        $result = app()->s3->putObject(
            $fileAllName,
            $this->s3UploadsPath . $fileName
        )->get('ObjectURL');
        if ($returnLocalUrl) {
            return $this->s3UploadsPath . $fileName;
        }
        return $result;
    }

    /**
     * 删除S3上的文件
     *
     * @param string $file 文件名称
     * @return bool
     * @throws \Exception
     */
    public function deleteS3File($file)
    {
        list($fileName) = $this->fileResolve($file);
        app()->s3->deleteObject($this->s3UploadsPath . $fileName);
        return true;
    }

    /**
     * 文件解析,解析返回文件名和包含路径的文件名
     *
     * @param $file
     * @return array
     */
    public function fileResolve($file)
    {
        if (false === strpos($file, '/')) {
            $fileName = $file;
            $fileAllName = Yii::getAlias('@resourcePath' . $file);
        } else {
            $fileName = substr($file, strrpos($file, '/') + 1);
            $fileAllName = $file;
        }
        return [$fileName, $fileAllName];
    }
}
