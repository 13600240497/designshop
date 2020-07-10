<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;

/**
 * 请求日志数据模型
 *
 * @property int $id 自增ID
 * @property string $site_code 站点简码,如:zf-pc
 * @property string $api_key API接口在配置文件里的数组下标，如:goods_async_detail
 * @property string $fail_url API请求异常完整地址
 * @property string $fail_message 异常信息
 * @property string $fail_count 异常次数
 * @property string $process_status 处理状态(0: 未处理；1:处理中 2:处理完成)
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class SysApiMonitorModel extends ActiveRecord
{
  /** @var int 处理状态 - 未处理 */
  const PROCESS_STATUS_INITIAL = 0;
  /** @var int 处理状态 - 处理中 */
  const PROCESS_STATUS_DOING = 1;
  /** @var int 处理状态 - 处理完成 */
  const PROCESS_STATUS_FINISHED = 2;

  /**
   * 初始化日志配置logConfig
   */
  public function init()
  {
    parent::init();
    $this->logConfig['nameField'] = 'id';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['site_code', 'api_key', 'fail_url', 'fail_count', 'process_status'], 'required'],
      ['site_code', 'string'],
    ];
  }

  /**
   * @return static[]
   */
  public static function getAllFailApi()
  {
    return self::find()
      ->where(['process_status' => self::PROCESS_STATUS_INITIAL])
      ->all();
  }
}
