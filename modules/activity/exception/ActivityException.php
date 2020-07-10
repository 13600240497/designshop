<?php
namespace app\modules\activity\exception;

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
