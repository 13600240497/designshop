<?php
namespace ego\session;

use yii\redis\Session;

/**
 * redis session
 *
 * - session cookie的值默认22-40位的小写字母
 * - 使用`keyPrefix`和session cookie的值作为redis键名
 */
class Redis extends Session
{
    use SessionTrait;

    /**
     * @inheritdoc
     */
    public function open()
    {
        parent::open();
        $this->checkSessionId();
    }

    /**
     * @inheritdoc
     */
    protected function calculateKey($id)
    {
        return $this->keyPrefix . $id;
    }
}
