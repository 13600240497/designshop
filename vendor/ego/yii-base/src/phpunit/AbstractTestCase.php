<?php
namespace ego\phpunit;

use Globalegrow\Base\PhpunitTrait;
use yii;
use yii\base\{Application, InvalidParamException};
use Closure;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;

/**
 * 测试基类
 */
abstract class AbstractTestCase extends TestCase
{
    use PhpunitTrait;

    /**
     * 创建app应用
     *
     * @return Application
     */
    abstract protected function createApp();

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->createApp();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->destroyApp();
    }

    /**
     * 消毁`Yii::$app`
     */
    protected function destroyApp()
    {
        Yii::$app = null;
    }

    /**
     * 获取或者设置一个对象`private`或者`protected`属性
     *
     * @param string $class
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function invokeProperty($class, $name, $value = null)
    {
        if (false !== strpos($name, '.')) {
            list($name, $key) = explode('.', $name);
        }
        $property = new ReflectionProperty($class, $name);
        $property->setAccessible(true);

        if (2 === func_num_args()) {
            $value = $property->getValue($class);
            if (isset($key)) {
                return app()->helper->arr->get($value, $key);
            }
            return $value;
        }
        if (isset($key)) {
            $v = $property->getValue($class);
            app()->helper->arr->set($v, $key, $value);
            $property->setValue($class, $v);
        } else {
            $property->setValue($class, $value);
        }
        return null;
    }

    /**
     * 获取或者设置一个对象的`private`或者`protected`静态属性
     *
     * @param string $class
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function invokeStaticProperty($class, $name, $value = null)
    {
        $argNum = func_num_args();
        $bind = Closure::bind(
            function() use($name, $value, $argNum) {
                if (false !== strpos($name, '.')) {
                    list($name, $key) = explode('.', $name);
                }
                if (2 === $argNum) {
                    /** @var array $value */
                    $value = static::${$name};
                    if (isset($key)) {
                        return app()->helper->arr->get($value, $key);
                    }
                    return $value;
                }
                if (isset($key)) {
                    /** @var array $v */
                    $v = static::${$name};
                    app()->helper->arr->set($v, $key, $value);
                    static::${$name} = $v;
                } else {
                    static::${$name} = $value;
                }
                return null;
            },
            null,
            $class
        );

        return $bind();
    }
}
