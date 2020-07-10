<?php
namespace app\modules\advertisement\exception;

/**
 * 功能：
 */
class AdvertisementException extends \yii\base\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'AdvertisementException';
    }
}
