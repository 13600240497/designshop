<?php

namespace app\modules\admin\components;

use Imagine\Image\ManipulatorInterface;
use yii\imagine\Image;

/**
 * 图片处理组件
 */
class ImageComponent extends Component
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * 裁剪功能
     *
     * @param       $image
     * @param       $width
     * @param       $height
     * @param array $start
     */
    public function crop($image, $width, $height, $start = [0, 0])
    {
        $source = \Yii::getAlias('@resourcePath') . '/' . $image;
        $dist = \Yii::getAlias('@resourcePath') . '/crop_' . $height . 'x' . $width . '_' . $image;
        Image::crop($source, $width, $height, $start)->save($dist, ['jpeg_quality' => 100]);
    }

    /**
     * 缩略图
     *
     * @param $image
     * @param $width
     * @param $height
     * @return bool|string
     */
    public function thumbnail($image, $width, $height)
    {
        $source = \Yii::getAlias('@resourcePath') . '/' . $image;
        $dist = \Yii::getAlias('@resourcePath') . '/thumbnail_' . $height . 'x' . $width . '_' . $image;
        $image = Image::thumbnail(
            $source,
            $width,
            $height,
            ManipulatorInterface::THUMBNAIL_INSET
        )->save($dist, ['jpeg_quality' => 100]);
        if ($image) {
            return $dist;
        }
        return false;
    }
}
