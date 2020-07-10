<?php
namespace app\modules\activity\zf\exception;

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
