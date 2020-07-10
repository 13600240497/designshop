<?php
namespace app\modules\activity\gb\components;

use app\modules\activity\traits\{
    MagicPropertyTrait,ErrorMessageTrait
};

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;
}
