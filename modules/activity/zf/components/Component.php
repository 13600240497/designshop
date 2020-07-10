<?php
namespace app\modules\activity\zf\components;

use app\modules\activity\zf\traits\MagicPropertyTrait;
use app\modules\activity\zf\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
