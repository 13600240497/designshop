<?php

namespace app\modules\common\components;

use Da\QrCode\Exception\UnknownWriterException;
use Da\QrCode\QrCode;
use yii\web\Response;

/**
 * 二维码组件
 */
class CommonQrCodeComponent extends Component
{
    /**
     * 二维码默认宽高
     */
    const DEFAULT_WIDTH = 200;

    /**
     * 二维码默认边距margin
     */
    const DEFAULT_MARGIN = 5;

    /**
     * 默认二维码content_type
     */
    const DEFAULT_CONTENT_TYPE = 'image/png';

    /**
     * 创建二维码
     * @param string $text 文本内容
     * @param int $width 高宽
     * @return string
     * @throws UnknownWriterException
     */
    public function create($text, $width)
    {
        !$width && $width = static::DEFAULT_WIDTH;
        $margin = static::DEFAULT_MARGIN;

        $qrCode = (new QrCode($text))
            ->setSize($width - 2*$margin)
            ->setMargin($margin)
            ->useForegroundColor(0, 0, 0);

        //取消自带的format，自带的没有图片格式的header
        app()->response->format = Response::FORMAT_RAW;
        //设置返回头
        header('Content-Type: ' . $qrCode->getContentType());
        return $qrCode->writeString();
    }
}
