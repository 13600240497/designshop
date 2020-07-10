<?php
namespace app\modules\soa\traits;

use ego\base\{Module, ServiceLocator};

/**
 * soa模块编辑器自动提示trait
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Soa->{$name};
        }
        return parent::__get($name);
    }
}
