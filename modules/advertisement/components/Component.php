<?php
namespace app\modules\advertisement\components;

use app\modules\advertisement\traits\MagicPropertyTrait;
use app\modules\advertisement\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
