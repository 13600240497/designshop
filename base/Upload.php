<?php

namespace app\base;

use FileUpload\FileUploadFactory;
use FileUpload\PathResolver;
use FileUpload\FileSystem;
use FileUpload\Validator\MimeTypeValidator;
use FileUpload\Validator\SizeValidator;
use Yii;
use yii\helpers\FileHelper;

/**
 * 上传组件
 */
class Upload
{
    private $uploadPath;

    /**
     * Upload constructor.
     *
     * @param string $uploadPath 上传的路径
     * @param bool $initDir 是否自动创建路径
     */
    public function __construct($uploadPath = false, $initDir = true)
    {
        $this->uploadPath = $uploadPath;
        if (!$uploadPath) {
            $this->uploadPath = \yii::getAlias(app()->params['uploadsPath'] ?? '@app/htdocs/uploads');
        }
        $initDir && $this->initDir();
    }

    /**
     * S3文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function uploadS3()
    {
        $file = $this->uploadPath();
        if (0 != $file->error) {
            return false;
        }
        $s3Upload = new S3Upload();
        $url = $s3Upload->saveToS3File($file->getPathname());
        if (!$url) {
            $file->error = '上传S3失败';
            return false;
        }
        return [
            'name' => app()->request->post('name', 'emptyName'),
            'url' => $url
        ];

    }

    /**
     * 文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function uploadReturnJson()
    {
        $info = $this->uploadPath();
        if (0 != $info->error) {
            return app()->helper->arrayResult(1, $info->error);
        }
        return app()->helper->arrayResult(0, 'success', ['url' => $info->getFilename()]);
    }

    /**
     * 文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function uploadPath()
    {
        $files = $this->multiFileUpload();
        return array_shift($files);
    }

    /**
     * 多文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function multiFileUpload()
    {
        $fileUpload = $this->initFileUpload();
        return $fileUpload->processAll()[0];
    }


    /**
     * 工厂方法生成文件上传对象
     *
     * @return \FileUpload\FileUpload
     */
    private function initFileUpload()
    {
        $factory = new FileUploadFactory(
            new PathResolver\Simple($this->uploadPath),
            new FileSystem\Simple(),
            [
                new MimeTypeValidator([
                    'image/png',
                    'image/jpeg',
                    'image/gif',
                    'image/x-ms-bmp',
                    'image/webp',
                    'audio/mpeg',
                    'audio/ogg',
                    'video/mp4',
                    'video/webm',
                    'application/octet-stream',
                ]),
                new SizeValidator('3M'),
            ]
        );
        if (empty($_FILES['files'])) {
            Yii::info('$_FILES["files"]对象为空：' . json_encode($_FILES) . json_encode($_SERVER), __METHOD__);
        }
        $fileUpload = $factory->create($_FILES['files'], $_SERVER);
        $randomGenerator = new \FileUpload\FileNameGenerator\Random();
        $fileUpload->setFileNameGenerator($randomGenerator);
        return $fileUpload;
    }

    /**
     * 新建初始化目录
     */
    private function initDir()
    {
        if (!is_dir($this->uploadPath)) {
            FileHelper::createDirectory($this->uploadPath);
        }
    }

    /**
     * 获取上传路径
     *
     * @return bool|string
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * 清理已上传的文件
     *
     * @return bool
     */
    public function clear()
    {
        $dir = $this->uploadPath;
        if (!is_dir($dir)) {
            return false;
        }

        return $this->del_dir($dir);
    }

    /**
     * 删除文件夹及其下的文件
     * @param $dir
     * @return bool
     */
    private function del_dir($dir)
    {
        //先删除目录下的文件
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $fullpath = $dir . '/' . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->del_dir($fullpath);
                }
            }
        }
        closedir($handle);

        //删除当前文件夹
        if (rmdir($dir)) {
            return true;
        }

        return false;
    }
}
