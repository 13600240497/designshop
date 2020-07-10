<?php
namespace app\modules\base\dl\traits;

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
            return app()->Base_dl->{$name};
        }

        return parent::__get($name);
    }
}
