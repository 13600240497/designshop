<?php
namespace app\modules\home\components;

use app\modules\home\traits\MagicPropertyTrait;
use app\modules\home\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
