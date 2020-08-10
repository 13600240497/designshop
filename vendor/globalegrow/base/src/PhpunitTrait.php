<?php
namespace Globalegrow\Base;

trait PhpunitTrait
{
    /**
     * 执行一个对象的`private`或者`protected`方法
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function invokeMethod($object, $method, array $args = [])
    {
        $m = (new \ReflectionClass($object))->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($object, $args);
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
        $p = new \ReflectionProperty($class, $name);
        $p->setAccessible(true);

        if (2 === func_num_args()) {
            return $p->getValue($class);
        }
        $p->setValue($class, $value);
        return $class;
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
        $argsNum = func_num_args();
        $bind = \Closure::bind(
            function () use ($name, $value, $argsNum) {
                if (2 !== $argsNum) {
                    static::${$name} = $value;
                }
                return static::${$name};
            },
            null,
            $class
        );

        return $bind();
    }

    /**
     * 访问一个对象的`private`或者`protected`静态方法
     *
     * @param string $class
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function invokeStaticMethod($class, $method, array $args)
    {
        $bind = \Closure::bind(
            function () use ($method, $args) {
                return call_user_func_array('static::' . $method, $args);
            },
            null,
            $class
        );

        return $bind();
    }
}
