<?php
namespace app\base;

use yii\base\InvalidArgumentException;
use ego\base\ServiceLocatorTrait;
use app\base\Module;

class SubModule extends Module
{
    use ServiceLocatorTrait;

    public $domain = null;

    /**
     * 覆盖 ServiceLocatorTrait::getSubNamespaceByClassName 方式
     *
     * @inheritdoc
     * @Override
     */
    protected function getSubNamespaceByClassName($className)
    {
        if (isset($this->getSubNamespaceByClassName[$className])) {
            return $this->getSubNamespaceByClassName[$className];
        }

        foreach (static::getSupportedClassSuffixes() as $item) {
            if (app()->helper->str->endWith($className, $item)) {
                $result = sprintf('%s%ss\%s', static::getSubModuleName($item), strtolower($item), $className);
                break;
            }
        }

        if (isset($result)) {
            return $this->getSubNamespaceByClassName[$className] = $result;
        } else {
            throw new InvalidArgumentException(sprintf('不支持的类名"%s"', $className));
        }
    }

    public static function getSubModuleName($item)
    {
        if (defined('IN_GB_SUB_MODULE') && ($item != 'Model')) {
            return 'gb\\';
        }

        return '';
    }
}