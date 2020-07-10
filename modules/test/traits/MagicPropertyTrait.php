<?php
namespace app\modules\test\traits;

use ego\base\{Module, ServiceLocator};

/**
 * api模块编辑器自动提示trait
 *
 * @property \app\modules\test\components\RuleComponent $RuleComponent
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Test->{$name};
        }
        return parent::__get($name);
    }
}
