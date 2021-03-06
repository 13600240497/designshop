<?php
namespace app\modules\test\zf\traits;

use ego\base\{Module, ServiceLocator};

/**
 * test模块编辑器自动提示trait
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Test_zf->{$name};
        }

        return parent::__get($name);
    }
}
