<?php
namespace app\modules\base\dl\components;

use app\modules\base\dl\traits\MagicPropertyTrait;
use app\modules\base\dl\traits\ErrorMessageTrait;

/**
 * test模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
