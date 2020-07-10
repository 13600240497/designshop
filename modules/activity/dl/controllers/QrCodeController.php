<?php
/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/25
 * Time: 08:35
 */

namespace app\modules\activity\dl\controllers;

/**
 * 二维码处理类
 * @property \app\modules\activity\dl\components\QrCodeComponent QrCodeComponent
 * @package app\modules\activity\controllers
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
