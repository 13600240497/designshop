<?php
namespace ego\web;

/**
 * 基础Request，继承`yii\web\Request`
 */
class Request extends \yii\web\Request
{
    /**
     * @var string csrf cookie作用域
     */
    public $csrfCookieDomain = null;
    /**
     * @var int 请求id，每个请求都会分配一个全局的id
     */
    protected $requestId = null;

    /**
     * @inheritdoc
     */
    public function post($name = null, $defaultValue = null)
    {
        if ($this->isDebug()) {
            return $this->get($name, $defaultValue);
        } else {
            return parent::post($name, $defaultValue);
        }
    }

    /**
     * url参数中是否带有DEBUG
     *
     * @return bool
     */
    public function isDebug()
    {
        return isset($_GET['DEBUG']) && app()->env->isDev();
    }

    /**
     * @inheritdoc
     */
    public function getUserIp()
    {
        return app()->ip->get();
    }

    /**
     * 获取请求id
     *
     * @return int
     */
    public function getRequestId()
    {
        if (null === $this->requestId) {
            $this->requestId = md5(uniqid(
                $_SERVER['REMOTE_ADDR'] ?? '',
                true
            ));
        }
        return $this->requestId;
    }

    /**
     * @inheritdoc
     */
    protected function createCsrfCookie($token)
    {
        $cookie = parent::createCsrfCookie($token);
        $cookie->domain = $this->csrfCookieDomain;
        return $cookie;
    }
}
