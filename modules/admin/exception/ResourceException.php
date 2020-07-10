<?php
namespace app\modules\admin\exception;

/**
 * 功能：
 */
class ResourceException extends \yii\base\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ResourceException';
    }
}
