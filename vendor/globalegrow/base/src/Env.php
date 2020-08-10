<?php
namespace Globalegrow\Base;

/**
 * 运行环境组件
 */
class Env extends Component
{
    /**
     * @var string 运行环境
     */
    public $env;

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        if (empty($config['env'])) {
            $config['env'] = isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'product';
        }
        parent::__construct($config);
    }

    /**
     * 魔术方法`__toString`
     *
     * @return string 运行环境字符串
     */
    public function __toString()
    {
        return (string) $this->env;
    }

    /**
     * 开发环境？
     *
     * @return bool
     */
    public function isDev()
    {
        return $this->is('dev');
    }

    /**
     * 测试环境？
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->is('test');
    }

    /**
     * 生产环境？
     *
     * @return bool
     */
    public function isProduct()
    {
        return $this->is('product');
    }

    /**
     * 预发布环境？
     *
     * @return bool
     */
    public static function isPreRelease()
    {
        return isset($_COOKIE['staging']) && 'true' === $_COOKIE['staging'];
    }

    /**
     * 线下环境（非生产环境）？
     *
     * @return bool
     */
    public function isLocal()
    {
        return !$this->isProduct();
    }

    /**
     * phpunit单元测试环境？
     *
     * @return bool
     */
    public function isPhpunit()
    {
        return $this->is('phpunit');
    }

    /**
     * 指定的环境匹配当前运行环境？
     *
     * @param string $env 环境
     * @return bool
     */
    protected function is($env)
    {
        return 0 === strpos($this->env, $env);
    }
}
