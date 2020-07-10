<?php
namespace app\modules\component\traits;

use ego\base\{Module, ServiceLocator};

/**
 * component模块编辑器自动提示trait
 * @property \app\modules\component\components\ManagerComponent $ManagerComponent
 * @property \app\modules\component\components\ExplainComponent $ExplainComponent
 * @property \app\modules\component\components\CategoryComponent $CategoryComponent
 * @property \app\modules\component\components\ExplainTplComponent $ExplainTplComponents
 */
trait MagicPropertyTrait
{
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this instanceof Module && ServiceLocator::isSupportedClassSuffix($name)) {
            return app()->Component->{$name};
        }
        return parent::__get($name);
    }
}
