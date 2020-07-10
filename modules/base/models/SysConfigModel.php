<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 请求日志数据模型
 *
 * @property string $option_key
 * @property string $option_value
 * @property string $option_desc
 */
class SysConfigModel extends ActiveRecord
{
  /**
   * 初始化日志配置logConfig
   */
  public function init()
  {
    parent::init();
    $this->logConfig['nameField'] = 'option_key';
  }

  /**
   * 获取所有配置
   *
   * @return array
   */
  public static function findCacheOptions()
  {
    return static::find()
      ->select('option_value')
      ->indexBy('option_key')
      ->column();
  }

  /**
   * 更新选项值
   *
   * @param array $options 新的选项值
   */
  public static function updateOptions(array $options)
  {
    foreach ($options as $key => $val) {
      static::updateAll(['option_value' => $val], ['option_key' => $key]);
    }
  }
}