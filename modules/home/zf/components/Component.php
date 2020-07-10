<?php
namespace app\modules\home\zf\components;

use app\modules\home\zf\traits\MagicPropertyTrait;
use app\modules\home\zf\traits\ErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
