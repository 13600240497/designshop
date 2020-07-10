<?php

namespace app\base;

use yii\helpers\FileHelper;
use app\base\S3Upload;
/**
 * 页面快照
 */
class Snapshot
{

    /**
     * @param int $tplType
     * @return string
     */
    private static function getPath($tplType=1)
    {
        $path = (1 == $tplType) ? '/htdocs/uploads/page-tpl/' : '/runtime/uploads/ui-tpl/';
        $uploadPath = app()->basePath . $path;
        self::initDir($uploadPath);
        return $uploadPath;
    }

    /**
     * 获取组件快照
     * @param   string    $url
     * @return  string
     */
    public static function getUiTplUrl($url)
    {
        $wkPath = app()->params['wkPath'];
        if(empty($wkPath) || empty($url)){
            return '';
        }
        $filename = md5(microtime() . random_int(0, 100));
        $source = self::getPath(2) . $filename . '.jpg';
        $thumb = self::getPath(2)  . $filename .'-thumb' . '.jpg';
        $cmd = $wkPath .' -f jpg "'. $url .'" '. $source;
        shell_exec($cmd);
        if(is_file($source)){
            if(self::thumb($source,$thumb)){//创建缩略图
                $s3Upload = new S3Upload();
                $s3Upload->saveToS3File($source, true);
                $url = $s3Upload->saveToS3File($thumb, true);
                if (!$url) {
                    return '文件上传S3失败';
                }
                @unlink($thumb);
                @unlink($source);
                return app()->params['s3UploadsConf']['staticDomain'] . $url;
            }
        }
        return '';
    }

    /**
     * 获取网页快照
     * @param   string    $url
     * @return  string
     */
    public static function get($url)
    {
        $wkPath = app()->params['wkPath'];
        if(empty($wkPath) || empty($url)){
            return '';
        }
        $filename = md5(microtime() . random_int(0, 100));
        $source = self::getPath() . $filename . '.png';
        $thumb = self::getPath()  . $filename .'-thumb' . '.png';
        shell_exec("$wkPath $url $source");
        if(is_file($source)){
            if(self::thumb($source,$thumb)){//创建缩略图
                $s3Upload = new S3Upload();
                $s3Upload->saveToS3File($source, true);
                $url = $s3Upload->saveToS3File($thumb, true);
                if (!$url) {
                    return '文件上传S3失败';
                }
                @unlink($thumb);
                @unlink($source);
                return app()->params['s3UploadsConf']['staticDomain'] . $url;
            }
        }
        return '';
    }

    /**
     *  压缩图片 (png/jpg)
     * @param string $source 图片路径
     * @param string $target 压缩后保存路径
     * @return bool
    */
    private static function thumb($source, $target) {
        list($width, $height, $type) = getimagesize($source);

        $new_width = $width;//压缩后的图片宽
        $new_height = $height;//压缩后的图片高

        if($width >= 324){
              $per = 324 / $width;//计算比例
              $new_width = $width * $per;
              $new_height = $height * $per;
        }
        switch ($type) {
              case 2:
                    header('Content-Type:image/jpeg');
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromjpeg($source);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    //90代表的是质量、压缩图片容量大小
                    imagejpeg($image_wp, $target, 80);
                    imagedestroy($image_wp);
                    imagedestroy($image);
                break;
              case 3:
                  header('Content-Type:image/png');
                  $image_wp = imagecreatetruecolor($new_width, $new_height);
                  $image = imagecreatefrompng($source);
                  imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                  //90代表的是质量、压缩图片容量大小
                  imagejpeg($image_wp, $target, 80);
                  imagedestroy($image_wp);
                  imagedestroy($image);
                  break;
        }
        return true;
    }
    /**
     * 新建初始化目录
     * @param  string  $uploadPath
     */
    private static function initDir($uploadPath)
    {
        if (!is_dir($uploadPath)) {
            FileHelper::createDirectory($uploadPath);
        }
    }
}
