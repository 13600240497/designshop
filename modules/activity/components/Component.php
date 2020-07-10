<?php
namespace app\modules\activity\components;

use app\modules\activity\traits\MagicPropertyTrait;
use app\modules\activity\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
