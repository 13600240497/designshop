<?php
namespace app\modules\base\controllers;


/**
 * 系统配置
 *
 * @property \app\modules\base\components\SysConfigComponent $SysConfigComponent
 */
class ConfigController extends Controller
{

  /**
   * 更新配置
   *
   * url: base/config/update-options
   * @return array
   */
  public function actionUpdateOptions()
  {
    return $this->SysConfigComponent->updateOptions(app()->request->post());
  }
}