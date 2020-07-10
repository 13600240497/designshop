<?php

namespace app\modules\activity\gb\controllers;

/**
 * 二维码处理类
 * @property \app\modules\activity\gb\components\QrCodeComponent QrCodeComponent
 * @package app\modules\activity\gb\controllers
 */
class QrCodeController extends Controller
{
    /**
     * 根据URL生成二维码图片
     * @param string $url URL
     * @param int $width
     * @return string
     * @throws \Da\QrCode\Exception\UnknownWriterException
     */
    public function actionCreate($url, $width = 0)
    {
        return $this->QrCodeComponent->create($url, $width);
    }
}
