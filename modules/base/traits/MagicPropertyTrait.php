<?php
namespace app\modules\base\traits;

use ego\base\{Module, ServiceLocator};

/**
 * base模块编辑器自动提示trait
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Base->{$name};
        }
        return parent::__get($name);
    }
}
