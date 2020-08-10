<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\UnknownPropertyException;

/**
 * 基础组件
 *
 * 一个组件是可配置的，即`public`属性和存在`setter`方法都可通过构造函数进行配置，
 * ```php
 *  class Db extends Component
 *  {
 *      public $dsn;
 *      private $debug;
 *
 *      public function setDebug($debug)
 *      {
 *          $this->debug = $debug;
 *      }
 *  }
 *
 *  $db = new Db([
 *      'dsn' => 'mysql:host=localhost;database=demo',
 *      'debug' => 'dev' === ENV
 *  ]);
 * ```
 */
class Component extends \stdClass
{
    /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        static::configure($this, $config);
        $this->init();
    }

    /**
     * 配置属性
     *
     * @param object $object
     * @param array $properties
     * @return object
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->{$name} = $value;
        }
        return $object;
    }

    /**
     * 魔术方法`__get`
     *
     * @param string $name
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        throw new UnknownPropertyException(sprintf(
            '获取未知属性: %s::%s',
            static::class,
            $name
        ));

    }

    /**
     * 魔术方法`__set`
     *
     * @param string $name
     * @param mixed $value
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            throw new UnknownPropertyException(sprintf(
                '设置未知属性: %s::%s',
                static::class,
                $name
            ));
        }
    }

    /**
     * 初始化
     *
     * @return $this
     */
    protected function init()
    {
        return $this;
    }
}
