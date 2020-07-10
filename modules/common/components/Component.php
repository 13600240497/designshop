<?php
namespace app\modules\common\components;

use app\modules\common\traits\CommonMagicPropertyTrait;
use app\modules\common\traits\CommonErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use CommonMagicPropertyTrait;
    use CommonErrorMessageTrait;
}
