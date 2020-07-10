<?php

namespace app\base;


class Cache extends \yii\redis\Cache
{
    /**
     * getOrSet方法 加入前缀
     * @param string $prefix 固定的前缀
     * @param $key
     * @param $callable
     * @param null $duration
     * @param null $dependency
     */
    public function getOrSetWithPrefix($prefix = 'geshop::', $key, $callable, $duration = null, $dependency = null)
    {
        $this->keyPrefix = $prefix;
        \Yii::info('Cache::getOrSetWithPrefix::prefiKey::' .$this->keyPrefix);
        \Yii::info('Cache::getOrSetWithPrefix::redisKey::' .$this->buildKey($key));
        parent::getOrSet($key, $callable, $duration, $dependency);
    }
}