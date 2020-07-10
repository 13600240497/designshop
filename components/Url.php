<?php
namespace app\components;

/**
 * url组件
 *
 * @property urls\Assets $assets
 * @method string admin($route = '', $querystring = null)
 * @method string sso($route = '', $querystring = null)
 */
class Url extends \ego\base\Url
{
    /**
     * @var urls\BaseUrl[] url组件
     */
    protected $components = [];

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->getUrlComponent($name) ?: parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $params)
    {
        if ($component = $this->getUrlComponent($name)) {
            return call_user_func_array([$component, 'to'], $params);
        }
        if (isset(app()->params['url'][strtolower($name)])) {
            array_unshift($params, $name);
            return call_user_func_array([$this, 'to'], $params);
        }

        return parent::__call($name, $params);
    }

    /**
     * 获取url
     *
     * @param string $key
     * @param string $action
     * @param string|array|null $querystring
     * @return string
     */
    protected function to($key, $action = '', $querystring = null)
    {
        if (null !== $action && '' !== $action) {
            $action = '/' . $action;
        }
        return $this->append(
            app()->params['url'][strtolower($key)] . $action,
            $querystring
        );
    }

    /**
     * 获取url组件
     *
     * @param string $name
     * @return urls\BaseUrl|null
     */
    protected function getUrlComponent($name)
    {
        if (isset($this->components[$name])) {
            return $this->components[$name];
        }
        if (class_exists($class = __NAMESPACE__ . '\urls\\' . ucfirst($name))) {
            return $this->components[$name] = new $class();
        }

        return null;
    }
}
