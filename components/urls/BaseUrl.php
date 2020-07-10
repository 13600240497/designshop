<?php
namespace app\components\urls;

use ego\base\Url;
use yii\helpers\StringHelper;

class BaseUrl extends Url
{
    /**
     * @var string 当前url组件对应的基础url地址
     */
    protected $baseUrl;

    /**
     * 获取url
     *
     * @param string $route 路由
     * @param string|array|null $querystring
     * @return string
     */
    public function to($route = null, $querystring = null)
    {
        if (null !== $route && '' !== $route) {
            $route = '/' . $route;
        }
        return $this->append(
            $this->baseUrl . $route,
            $querystring
        );
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setBaseUrl();
    }

    /**
     * 设置baseUrl
     */
    protected function setBaseUrl()
    {
        // User.php -> app()->params['url']['user']
        // Cart.php -> app()->params['url']['cart']
        $name = StringHelper::basename(get_class($this));
        $this->baseUrl = app()->params['url'][strtolower($name)];
    }
}
