<?php
namespace app\modules\base\components;

use app\base\SysConfigUtils;
use yii\helpers\Json;

/**
 * 系统配置组件
 */
class SysConfigComponent extends Component
{

  /**
   * 更新系统配置
   *
   * @param array $params
   * @return array
   */
  public function updateOptions($params)
  {
    if (empty($params['options'])) {
      return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, ['updateCount' => 0]);
    }

    $options = Json::decode($params['options'], true);
    $updateCount = SysConfigUtils::updateOptions($options);
    return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, ['updateCount' => $updateCount]);
  }
}