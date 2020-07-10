<?php
namespace app\modules\base\models;

use ego\base\ArrayAccess;
use app\models\ActiveRecord;
use ego\models\CacheTrait;

/**
 * `key => value`设置模型
 *
 * @property string $group_id
 * @property string $key
 * @property string $value
 */
class SettingModel extends ActiveRecord
{
    use CacheTrait;

    const GROUP = 'group_id';

    /**
     * @var array 日志配置
     */
    protected $logConfig = [
        'nameField' => 'key',
    ];

    /**
     * 获取设置值
     *
     * @param string $group
     * @param string $key
     * @return string|null
     */
    public static function getValue($group, $key)
    {
        $data = static::getValues($group, [$key]);
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * 获取所有配置值
     *
     * @param string $group
     * @param array $keys
     * @return ArrayAccess|\stdClass[]
     */
    public static function getValues($group, array $keys = [])
    {
        $data = static::getCache()->redis->hget(static::getCacheKey(), $group);
        if ($data) {
            $data = json_decode($data, true);
        } else {
            $data = static::find()
                ->select('value')
                ->indexBy('key')
                ->where([static::GROUP => $group])
                ->column();
            static::getCache()->redis->hset(static::getCacheKey(), $group, json_encode($data));
        }
        if ($keys) {
            $data = app()->helper->arr->pick($data, $keys);
        }
        return new ArrayAccess($data);
    }

    /**
     * 保存配置
     *
     * @param string $group
     * @param array $data
     */
    public static function saveSetting($group, array $data)
    {
        foreach ($data as $key => $value) {
            /** @var static $item */
            if ($item = static::findOne([static::GROUP => $group, 'key' => $key])) {
                $item->value = $value;
                $item->update(false);
            } else {
                $model = new static([
                    static::GROUP => $group,
                    'key' => $key,
                    'value' => (string)$value,
                ]);
                $model->insert(false);
            }
        }
        static::getCache()->redis->del(static::getCacheKey());
    }

    /**
     * @inheritdoc
     */
    public static function clearCache($id)
    {
    }
}
