<?php
namespace app\modules\api\traits;

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
            return app()->Home->{$name};
        }
        return parent::__get($name);
    }
}
