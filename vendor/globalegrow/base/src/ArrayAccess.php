<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\UnknownPropertyException;

/**
 * ArrayAccess递归地将一个数组转化为对象，同时该对象支持数组方式使用
 *
 * ArrayAccess实现了`Countable`、`Iterator`、`ArrayAccess`，`Serializable`，`JsonSerializable`接口
 *
 * ```php
 *      $configA = [
 *          'components' => [
 *              'db' => [
 *                  'host' => 'localhost',
 *                  'username' => 'root',
 *                  'password' => '',
 *                  'dbname' => 'test',
 *                  'port' => 3306,
 *                  'driverOptions' => [
 *                      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
 *                  ],
 *              ],
 *          ],
 *      ];
 *      $aa = new ArrayAccess($config);
 * ```
 *
 * ### foreach
 * ```php
 *      foreach ($aa as $key => $value) {
 *      }
 * ```
 *
 * ### 使用对象方式
 * ```php
 *      $host = $aa->components->db->host;
 *      $aa->components->host = '127.0.0.1';
 * ```
 *
 * ### 使用数组方式
 * ```php
 *      $host = $aa['components']['db']['host'];
 *      $aa['components']['db']['host'] = '127.0.0.1';
 * ```
 *
 * ### 转化为数组
 * ```php
 *      $aa->components->toArray();
 * ```
 *
 * ### 合并
 * ```php
 *      $aa->merge([
 *          'components' => [
 *              'db' => [
 *                  'password' => '123456'
 *              ],
 *          ],
 *      ];
 *      $aa->components->db->password; // -> 123456
 * ```
 */
class ArrayAccess extends Component implements
    \Countable,
    \Iterator,
    \ArrayAccess,
    \Serializable,
    \JsonSerializable
{
    /**
     * @var array 存储的数据
     */
    private $data = [];

    /**
     * 构造函数
     *
     * @param array|static $data
     */
    public function __construct($data = [])
    {
        $this->initData($data);
        parent::__construct([]);
    }

    /**
     * 魔术方法`__clone`
     */
    public function __clone()
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $data[$key] = clone $value;
            } else {
                $data[$key] = $value;
            }
        }
        $this->data = $data;
    }

    /**
     * 魔术方法`__get`
     *
     * @param string $key
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        throw new UnknownPropertyException(sprintf('未定义"%s"', $key));
    }

    /**
     * 递归的将对象元素转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * 返回数据所有键名
     *
     * @param mixed $searchValue
     * @param bool $strict
     * @return array
     * @see `array_keys`
     */
    public function keys($searchValue = null, $strict = false)
    {
        if (func_num_args()) {
            return array_keys($this->data, $searchValue, $strict);
        }
        return array_keys($this->data);
    }

    /**
     * 存在指定键名
     *
     * @param string $key
     * @return bool
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param array|bool $argA 参数a，当是一个**bool**值时，表示是否保留数字键名
     * @param array $argN 参数N
     */
    public function merge($argA, ...$argN)
    {
        if (is_bool($argA)) {
            $preserveNumericKeys = $argA;
        } else {
            $preserveNumericKeys = false;
            array_unshift($argN, $argA);
        }
        $this->data = Arr::merge($preserveNumericKeys, $this->toArray(), ...$argN);
        $this->initData($this->data);
    }

    /**
     * 魔术方法`__set`
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * 魔术方法`__isset`
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * 魔术方法`__unset`
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * 实现`Countable::count`
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * 实现`ArrayAccess::offsetExists()`
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * 实现`ArrayAccess::offsetGet()`
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }
        return null;
    }

    /**
     * 实现`ArrayAccess::offsetSet()`
     *
     * @param  mixed $offset
     * @param  mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * 实现`ArrayAccess::offsetUnset()`
     *
     * @param  mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * 实现`Iterator::current()`
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * 实现`Iterator::key()`
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * 实现`Iterator::next()`
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * 实现`Iterator::rewind()`
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * 实现`Iterator::rewind()`
     *
     * @return bool
     */
    public function valid()
    {
        return null !== $this->key();
    }

    /**
     * 实现`Serializable::serialize()`
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * 实现`Serializable::unserialize()`
     *
     * @param  string $serialized
     * @return array
     */
    public function unserialize($serialized)
    {
        return $this->data = unserialize($serialized);
    }

    /**
     * 实现`JsonSerializable::jsonSerialize()`
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * 构造函数
     *
     * @param array|static $data
     */
    private function initData($data)
    {
        $data = Arr::toArray($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($value);
            } else {
                $this->data[$key] = $value;
            }
        }
    }
}
