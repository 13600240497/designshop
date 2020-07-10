<?php
namespace app\modules\admin\traits;

use ego\base\{Module, ServiceLocator};

/**
 * admin模块编辑器自动提示trait
 * @property \app\modules\admin\components\ResourceComponent $ResourceComponent
 * @property \app\modules\admin\components\UploadComponent $UploadComponent
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Admin->{$name};
        }
        return parent::__get($name);
    }
}
