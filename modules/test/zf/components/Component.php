<?php
namespace app\modules\test\zf\components;

use app\modules\test\zf\traits\MagicPropertyTrait;
use app\modules\test\zf\traits\ErrorMessageTrait;

/**
 * test模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
