<?php
namespace app\modules\activity\dl\exception;

/**
 * 功能：
 */
class ActivityException extends \yii\base\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ActivityException';
    }
}
