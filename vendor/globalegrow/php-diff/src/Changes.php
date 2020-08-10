<?php
namespace Globalegrow\PhpDiff;

use Globalegrow\Base\Arr;
use Globalegrow\Base\Assertion;
use Globalegrow\Base\Component;
use Globalegrow\Base\Datetime;
use Globalegrow\Base\Helper;

/**
 * php比较两个字符串差异组件
 */
class Changes extends Component
{
    /**
     * @var array|string 比较差异的字段，默认只对比新值中的键名
     */
    public $diffFields = [];
    /**
     * @var array|string 详细比较的字段
     */
    public $diffDetailFields = [];
    /**
     * @var array|string 时间字段，将进行**Y-m-d H:i:s**格式化
     */
    public $timeFields = 'create_time';
    /**
     * @var array|string 忽略的字段
     */
    public $ignoreFields = 'update_time';
    /**
     * @var array|string 敏感字段，将使用`*`号替代
     */
    public $sensitiveFields = 'password';

    /**
     * 获取差异
     *
     * @param array $old
     * @param array $new
     * @return array
     */
    public function get(array $old = null, array $new = null)
    {
        $new = $new ?: [];
        $old = $old ?: [];
        if ($old === $new) {
            return [];
        }

        $diffFields = $this->diffFields ?: array_keys($new);
        $datetime = new Datetime();
        $changes = [];
        foreach($new as $field => &$value) {
            Assertion::assertTrue(Helper::isScalar($value));
            if (!in_array($field, $diffFields) // 不比较
                || ($this->ignoreFields && in_array($field, $this->ignoreFields)) // 忽略
                || (isset($old[$field]) && $value == $old[$field]) // 相同
                || (!isset($old[$field]) && '' === $value)
            ) {
                unset($new[$field], $new[$field]);
                continue;
            }
            if ($this->timeFields && in_array($field, $this->timeFields)) {
                if (!empty($value) && is_numeric($value)) {
                    $value = $datetime->cn->setTimestamp($value)->format('Y-m-d H:i:s');
                }
                if (!empty($old[$field]) && is_numeric($old[$field])) {
                    $old[$field] = $datetime->cn->setTimestamp($old[$field])->format('Y-m-d H:i:s');
                }
            } elseif (in_array($field, $this->sensitiveFields)) {
                $value = str_repeat('*', 10);
                if (isset($old[$field])) {
                    $old[$field] = $value;
                }
            }

            $changes[$field] = [
                'new'   => $value,
                'old'   => isset($old[$field]) ? $old[$field] : '',
                'diff'  => $this->diffDetailFields
                           && in_array($field, $this->diffDetailFields)
                           && ($value != (isset($old[$field]) ? $old[$field] : ''))
            ];
        }

        if ($changes && $this->diffDetailFields) {
            $changes = $this->sort($changes);
        }
        return $changes;
    }

    /**
     * 将有详细比较差异的放在最后
     *
     * @param $changes
     * @return array
     */
    protected function sort($changes)
    {
        uasort($changes, function($a, $b) {
            return strcmp($a['diff'], $b['diff']);
        });
        return $changes;
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->diffFields = Arr::trim($this->diffFields ?: []);
        $this->diffDetailFields = Arr::trim($this->diffDetailFields ?: []);
        $this->timeFields = Arr::trim($this->timeFields ?: []);
        $this->ignoreFields = Arr::trim($this->ignoreFields ?: []);
        $this->sensitiveFields = Arr::trim($this->sensitiveFields ?: []);
    }
}
