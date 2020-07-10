<?php
namespace app\modules\base\components;

use app\modules\base\traits\MagicPropertyTrait;
use app\modules\base\traits\ErrorMessageTrait;

/**
 * base模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
