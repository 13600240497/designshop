<?php
namespace ego\session;

use yii;
use yii\base\InvalidValueException;

/**
 * session trait
 *
 * session cookie的值默认22-40位的小写字母
 */
trait SessionTrait
{
    /**
     * @var string session_id检测正则，默认为22-40位的小写字母
     */
    public $sessionIdRegexp = '/^[a-z0-9]{22,40}$/';

    /**
     * session_id检测
     *
     * @throws InvalidValueException session id值不匹配配置的正则时
     */
    protected function checkSessionId()
    {
        if (!empty($this->sessionIdRegexp)
            && !preg_match($this->sessionIdRegexp, $this->getId())
        ) {
            $error = sprintf(
                'session id"%s"不匹配正则"%s"',
                $this->getId(),
                $this->sessionIdRegexp
            );
            Yii::warning([$error, isset($_SESSION) ? $_SESSION : null], __METHOD__);
            $this->regenerateID(true);
            throw new InvalidValueException($error);
        }
    }
}
