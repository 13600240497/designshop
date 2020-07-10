<?php
namespace app\modules\common\dl\traits;

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
            return app()->Activity_dl->{$name};
        }
        return parent::__get($name);
    }
}
