<?php
namespace app\modules\common\traits;

use ego\base\{Module, ServiceLocator};

/**
 * admin模块编辑器自动提示trait
 */
trait CommonMagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Activity->{$name};
        }
        return parent::__get($name);
    }
}
