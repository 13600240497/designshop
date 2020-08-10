<?php
namespace ego\models;

use yii\helpers\Json;

/**
 * 模型数据缓存trait
 */
trait CacheTrait
{
    /**
     * @var bool 使用了`CacheTrait`，在`afterSave`及`afterDelete`后自动清除缓存
     */
    protected static $useCacheTrait = true;

    /**
     * 根据id获取记录
     *
     * @param int $id
     * @param string $field
     * @return static|string|null
     */
    public static function getById($id, $field = null)
    {
        /** @var ActiveRecord $item */
        $item = static::getCache()->redis->hget(static::getCacheKey(), $id);
        // 缓存缓存
        if ($item) {
            $item = new static(Json::decode($item));
            $item->setIsNewRecord(false);
        } else {
            $item = static::findOne($id);
            // 存在
            if ($item) {
                static::getCache()->redis->hset(static::getCacheKey(), $id, Json::encode($item));
            }
        }

        if (!$item) {
            return null;
        } elseif ($field) {
            return $item[$field];
        } else {
            return $item;
        }
    }
}
