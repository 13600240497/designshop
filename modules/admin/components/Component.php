<?php
namespace app\modules\admin\components;

use app\modules\admin\traits\MagicPropertyTrait;
use app\modules\activity\traits\ErrorMessageTrait;

/**
 * admin模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
