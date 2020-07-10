<?php
namespace app\modules\gbad\traits;

use ego\base\{Module, ServiceLocator};

/**
 * admin模块编辑器自动提示trait
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Gbad->{$name};
        }
        return parent::__get($name);
    }
}
