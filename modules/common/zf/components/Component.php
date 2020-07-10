<?php
namespace app\modules\common\zf\components;

use app\modules\common\zf\traits\CommonMagicPropertyTrait;
use app\modules\common\zf\traits\CommonErrorMessageTrait;

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use CommonMagicPropertyTrait;
    use CommonErrorMessageTrait;
}
