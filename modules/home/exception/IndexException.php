<?php
namespace app\modules\home\exception;

/**
 * 功能：
 */
class IndexException extends \yii\base\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'IndexException';
    }
}
