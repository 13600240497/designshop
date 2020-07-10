<?php
namespace app\modules\activity\dl\components;

use app\modules\activity\dl\traits\MagicPropertyTrait;
use app\modules\activity\dl\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
