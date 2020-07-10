<?php
namespace app\modules\base\zf\components;

use app\modules\base\zf\traits\MagicPropertyTrait;
use app\modules\base\zf\traits\ErrorMessageTrait;

/**
 * test模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
