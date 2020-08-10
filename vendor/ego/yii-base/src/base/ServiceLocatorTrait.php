<?php
namespace ego\base;

use yii\base\InvalidParamException;

trait ServiceLocatorTrait
{
    /**
     * @var array 获取类部分命名空间
     */
    protected $getSubNamespaceByClassName = [];

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->isSupportedClassSuffix($name)) {
            return $this->getByCalledClass($name, static::class);
        } else {
            return parent::__get($name);
        }
    }

    /**
     * 支持的类后缀？
     *
     * @param string $name 类名
     * @return bool
     */
    public static function isSupportedClassSuffix($name)
    {
        return ctype_upper($name{0})
            && app()->helper->str->endWith($name, static::getSupportedClassSuffixes())
            && !in_array($name, static::getSupportedClassSuffixes());
    }

    /**
     * 根据调用所在类获取属性对应的类
     *
     * 如在`app\modules\user\components\LoginComponent->UserModel`将返回
     * `app\modules\user\models\UserModel`
     *
     * @param string $propertyName 属性名
     * @param string $calledClass 调用所在类
     * @return object
     */
    protected function getByCalledClass($propertyName, $calledClass)
    {
        // app\modules\user\components\LoginComponent->LoginService -> app\services\user\LoginService
        if (app()->helper->str->endWith($propertyName, 'Service')) {
            $className = sprintf(
                'app\services\\%s\\%s',
                explode('\\', $calledClass)[2],
                $propertyName
            );
        } else {
            // app\modules\user\ServiceLocator -> app\modules\user
            // app\modules\admin\modules\seo\ServiceLocator -> app\modules\admin\modules\seo
            $className = substr($calledClass, 0, strrpos($calledClass, '\\'));
            $className .= '\\' . $this->getSubNamespaceByClassName($propertyName);
        }

        if (!$this->has($className)) {
            $this->set($className, ['class' => $className]);
        }
        return $this->get($className);
    }

    /**
     * 根据类名获取类部分命名空间
     *
     * - `NameComponent` -> `components\NameComponent`
     * - `NameService` -> `validators\NameService`
     *
     * @param string $className 类名
     * @return string 类部分命名空间
     * @throws InvalidParamException 不支持的类后缀时
     */
    protected function getSubNamespaceByClassName($className)
    {
        if (isset($this->getSubNamespaceByClassName[$className])) {
            return $this->getSubNamespaceByClassName[$className];
        }

        foreach (static::getSupportedClassSuffixes() as $item) {
            if (app()->helper->str->endWith($className, $item)) {
                // UserModel -> models\UserModel
                $result = strtolower($item) . 's\\' . $className;
                break;
            }
        }

        if (isset($result)) {
            return $this->getSubNamespaceByClassName[$className] = $result;
        } else {
            throw new InvalidParamException(sprintf('不支持的类名"%s"', $className));
        }
    }

    /**
     * 支持通过属性获取到类的类后缀
     *
     * @return array
     */
    protected static function getSupportedClassSuffixes()
    {
        return [
            'Component',
            'Service',
            'Model',
        ];
    }
}
