<?php
namespace ego\base;

use ego\helpers\Arr;
use yii\base\InvalidParamException;

/**
 * 基础Application，继承`yii\web\Application`
 *
 * @property Crypt $crypt
 * @property Cdn $cdn
 * @property Site $site
 * @property ArrayTree $arrayTree
 * @property PhpProfile $phpProfile
 * @property \ego\models\ValidatorModel $validatorModel
 * @property \ego\web\Request $request
 * @property \ego\mq\Client $mq
 * @property \ego\helpers\Helper $helper
 * @property \ego\mail\Mailer $mailer
 * @property \ego\aws\S3Client $s3
 * @property \ego\log\Phplog $phplog
 * @property \Globalegrow\Base\Assertion $assertion
 * @property \Globalegrow\Base\Datetime $datetime
 * @property \Globalegrow\Base\Env $env
 * @property \Globalegrow\Base\Ip $ip
 * @property \Globalegrow\Base\Debug $debug
 * @property \Globalegrow\YiiPredis\Connection $redis
 * @property \Globalegrow\PhpDiff\Diff $diff
 * @property \yii\redis\Cache $cache
 * @property \yii\caching\ArrayCache $arrayCache
 * @property \app\base\SwooleClient $swoole
 * @property \Globalegrow\YiiPredis\Connection $apiRedis
 */
abstract class Application extends \yii\web\Application
{
    /**
     * @var array 服务定位器
     */
    protected $serviceLocators = [];

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->getServiceLocator($name) ?: parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function end($status = 0, $response = null)
    {
        if (!defined('PHPUNIT_TEST')) {
            parent::end($status, $response);
        }
    }

    /**
     * 加载配置
     *
     * 该方法会加载指定的配置文件，如果存在当前环境的配置文件，一并加载，然后进行递归合并
     *
     * 配置中如果包含数字键名，后面的值将不会覆盖原来的值，而是附加到后面
     *
     * 比如有配置文件**@app/config/web.php**，则会加载以下文件：
     *
     * - **@app/config/web.php**
     * - **@app/config/web.<env>.php**（如果存在）
     *
     * @param string $configFile 配置文件
     * @param string|null $env 运行环境，默认取`$_SERVER[ENV_NAME]`，如果未设置，则取**product**
     * @return array 合并后的配置
     * @throws InvalidParamException 配置文件不存在时
     */
    public static function loadConfig($configFile, $env = null)
    {
        if (!is_file($configFile)) {
            throw new InvalidParamException(sprintf('配置文件"%s"不存在', $configFile));
        }

        $config = require($configFile);
        // @root/config/config.<env>.php
        $file = $file = substr_replace($configFile, ($env ?? YII_ENV) . '.', -3, 0);
        if (is_file($file)) {
            $config = Arr::merge($config, require $file);
        }

        return $config;
    }

    /**
     * 获取当前项目所处环境的开发员
     *
     * apache范域名解析：
     *
     * <VirtualHost *:80>
     *     UseCanonicalName Off
     *     # http://www.gearbest.com.mashanling.dev72.egocdn.com -> /home/wwwroot/dev72/mashanling/gearbest/htdocs
     *     # http://www.sammydress.com.mashanling.dev72.egocdn.com -> /home/wwwroot/dev72/mashanling/sammydress/htdocs
     *     # http://www.gearbest.com.fangxin.dev72.egocdn.com -> /home/wwwroot/dev72/fangxin/gearbest/htdocs
     *     # http://www.fangxin.com.mashanling.dev72.egocdn.com -> /home/wwwroot/dev72/fangxin/sammydress/htdocs
     *     # 倒数第3段为固定的dev72,倒数第4段为项目所在目录的名称,倒数第6段为开发员姓名
     *     virtualDocumentRoot "/home/wwwroot/%-3/%-4/%-6/htdocs"
     *     ServerAlias  *.dev72.egocdn.com
     *     setEnv DEVELOPER -6
     * </VirtualHost>
     *
     * @return string|null
     */
    public static function getDeveloper()
    {
        if (!isset($_SERVER['DEVELOPER'])) {
            return null;
        }
        if (is_numeric($_SERVER['DEVELOPER']) && isset($_SERVER['SERVER_NAME'])) {
            // http://www.gearbest.com.mashanling.dev72.egocdn.com -> mashanling
            // http://www.fangxin.com.mashanling.dev72.egocdn.com -> fangxin
            $_SERVER['DEVELOPER'] = array_slice(
                explode('.', $_SERVER['SERVER_NAME']),
                $_SERVER['DEVELOPER'],
                1
            );
            $_SERVER['DEVELOPER'] = $_SERVER['DEVELOPER'][0];
        }
        return $_SERVER['DEVELOPER'];
    }

    /**
     * @inheritdoc
     */
    public function getModule($id, $load = true)
    {
        return parent::getModule(
            'debug' === $id ? 'yii-debug' : $id,
            $load
        );
    }

    /**
     * @inheritdoc
     */
    public function setVendorPath($path)
    {
        parent::setVendorPath($path);
        \Yii::setAlias('@bower', '@vendor/bower-asset');
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->phpProfile->start();
    }

    /**
     * 服务定位器名称
     *
     * @param string $name 大写的模块名
     * @return ServiceLocator|\yii\base\Module
     */
    protected function getServiceLocator($name)
    {
        if (isset($this->serviceLocators[$name])) {
            if (is_string($this->serviceLocators[$name])) {
                $this->serviceLocators[$name] = \Yii::createObject(
                    $this->serviceLocators[$name]
                );
            }
            return $this->serviceLocators[$name];
        }

        if (!ctype_upper($name{0}) || !$this->hasModule(strtolower($name))) {
            return null;
        }
        return $this->serviceLocators[$name] = $this->getModule(strtolower($name));
    }
}
