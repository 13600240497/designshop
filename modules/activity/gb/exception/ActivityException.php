<?php
namespace app\modules\activity\gb\exception;

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
