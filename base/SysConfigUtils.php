<?php
namespace app\base;

use yii\helpers\Json;
use app\modules\base\models\SysConfigModel;

/**
 * 系统配置
 *
 * @package app\base
 */
class SysConfigUtils
{
  /** @var array 配置缓存 */
  private static $cachedSettings = null;

  /**
   * 获取单个配置项的值
   *
   * @param string $key 配置项键名
   * @param mixed $defaultValue 默认值
   * @return mixed
   */
  public static function get($key, $defaultValue = null)
  {
    self::loadSettings();
    return self::$cachedSettings[$key] ?? $defaultValue;
  }

  /**
   * 获取多个keys值
   *
   * @param array $keys 多个key值
   * @param string $removePrefix 要删除的前缀字符串
   * @return array
   */
  public static function getByKeys(array $keys, $removePrefix = null)
  {
    self::loadSettings();

    $configs = array_intersect_key(self::$cachedSettings, array_flip($keys));
    if (!empty($removePrefix)) {
      $_configs = [];
      $start = strlen($removePrefix);
      foreach ($configs as $key => $val) {
        $key = substr($key, $start);
        $_configs[$key] = $val;
      }
      $configs = $_configs;
    }

    return $configs;
  }

  /**
   * 获取所有配置
   *
   * @return array
   */
  public static function getAllOption()
  {
    self::loadSettings();
    return self::$cachedSettings;
  }

  /**
   * 更新系统配置选项
   *
   * @param array $options
   * @return int
   */
  public static function updateOptions(array $options)
  {
    self::loadSettings();

    // 比较需要更新的配置项
    $toUpdates = [];
    foreach ($options as $key => $val) {
      if (key_exists($key, self::$cachedSettings)) {
        if ($val !== null && self::$cachedSettings[$key] !== $val) {
          $toUpdates[$key] = $val;
        }
      }
    }

    // 更新配置项并清除缓存
    if (!empty($toUpdates)) {
      // 更新数据库
      SysConfigModel::updateOptions($toUpdates);

      // 更新本地缓存
      foreach ($options as $key => $val) {
        self::$cachedSettings[$key] = $val;
      }

      // 删除redis缓存
      app()->redis->del(app()->redisKey->getSysConfigKey());
      return count($toUpdates);
    }
    return 0;
  }

  /**
   * 加载系统配置
   */
  private static function loadSettings()
  {
    if (null === self::$cachedSettings) {
      self::$cachedSettings = self::getSettingsFromCache();
    }
  }

  /**
   * 从缓存中获取系统配置
   *
   * @return array
   */
  private static function getSettingsFromCache()
  {
    $cacheKey = app()->redisKey->getSysConfigKey();
    $configString = app()->redis->get($cacheKey);
    if (empty($configString)) {
      $options = SysConfigModel::findCacheOptions();
      app()->redis->set($cacheKey, Json::encode($options));
    } else {
      $options = Json::decode($configString, true);
    }
    return $options;
  }
}