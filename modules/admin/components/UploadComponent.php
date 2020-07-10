<?php
namespace app\modules\admin\components;

use app\base\S3Upload;
use app\base\Upload;
use app\modules\admin\models\ResourceModel;
use Globalegrow\Gateway\Exceptions\Exception;
use Yii;

/**
 * 上传组件
 */
class UploadComponent extends Component
{
    /**
     * UploadComponent constructor.
     */
    public function init()
    {
        parent::init();
        $uploadPath = \yii::getAlias(app()->params['uploadsPath'] ?? '@app/runtime/uploads');
        \yii::setAlias('@resourcePath', $uploadPath);
    }

    /**
     * 文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function upload()
    {
        $upload = new Upload(\yii::getAlias('@resourcePath'));
        $file = $upload->uploadPath();
        if (0 != $file->error) {
            return app()->helper->arrayResult(1, $file->error);
        }
        $s3Upload = new S3Upload();
        $url = $s3Upload->saveToS3File($file->getPathname(), true);
        if (!$url) {
            return app()->helper->arrayResult(1, '文件上传S3失败');
        }
        $data = [
            'name' => $file->getClientFileName(),
            'url' => $url,
            'size' => $file->getSize()
        ];
        $imageSize = $this->getImageSize($file->getPathname());
        if ($imageSize) {
            $data = array_merge($data, $imageSize);
            $data['thumbnail_url'] = $this->uploadThumbnail($file->getFilename(), $s3Upload, $imageSize);
        }
        return app()->helper->arrayResult(0, '添加成功', $data);
    }

    /**
     * 多文件上传，只需前端素材采用文件框，名字为files
     *
     * @return array
     */
    public function multiFileUpload()
    {
        $upload = new Upload(\yii::getAlias('@resourcePath'));
        $files = $upload->multiFileUpload();
        $s3Upload = new S3Upload();

        $data = [];
        foreach ($files as $file) {
            if (0 != $file->error) {
                return app()->helper->arrayResult(1, $file->error);
            }
            $url = $s3Upload->saveToS3File($file->getPathname(), true);
            if (!$url) {
                return app()->helper->arrayResult(1, '文件上传S3失败');
            }
            $fileData = [
                'name' => $file->getClientFileName(),
                'url' => $url,
                'size' => $file->getSize()
            ];
            $fileData['parent_id'] = app()->request->post('parent_id', 0);
            if ($file->isImage()) {
                $imageSize = $this->getImageSize($file->getPathname());
                $fileData['type'] = 1;
                $fileData = array_merge($fileData, $imageSize);
                $fileData['thumbnail_url'] = $this->uploadThumbnail($file->getFilename(), $s3Upload, $imageSize);
            }
            $data[] = $fileData;
        }
        $isSave = $this->filesSaveDb($data);
        if (is_string($isSave)) {
            return app()->helper->arrayResult(1, $isSave);
        }
        return app()->helper->arrayResult(0, '添加成功', $data);
    }

    /**
     * 判断是否是图片，如果是图片返回尺寸
     *
     * @param string $pathName 图片的绝对路径
     * @return mixed
     */
    private function getImageSize($pathName)
    {
        try {
            $imageSize = getimagesize($pathName);
            if (empty($imageSize[0])) {
                throw new Exception('not images');
            }
            $data['width'] = $imageSize[0];
            $data['height'] = $imageSize[1];
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 生成缩略图并上传
     *
     * @param string $file 文件路径
     * @param S3Upload $s3Upload S3上传组件
     * @return string 返回上传后的路径
     */
    private function uploadThumbnail($file, $s3Upload)
    {
        $image = new ImageComponent();
        $thumbnail = $image->thumbnail($file, 100, 100);
        return $s3Upload->saveToS3File($thumbnail, true);
    }

    /**
     * 保存多文件到数据库
     *
     * @param $data
     * @return array|bool|string
     * @throws \Exception
     * @throws \Throwable
     */
    private function filesSaveDb($data)
    {
        foreach ($data as $file) {
            $resource = new ResourceModel($file);
            if (!$resource->insert()) {
                return $resource->flattenErrors(', ');
            }
        }
        return true;
    }
}
