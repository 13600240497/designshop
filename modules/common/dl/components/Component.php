<?php
namespace app\modules\common\dl\components;

use app\modules\common\dl\traits\CommonMagicPropertyTrait;
use app\modules\common\dl\traits\CommonErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use CommonMagicPropertyTrait;
    use CommonErrorMessageTrait;
}
