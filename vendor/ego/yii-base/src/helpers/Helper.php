<?php
namespace ego\helpers;

use app\components\DbDebug;
use yii\base\{Component, InvalidValueException, InvalidParamException};
use yii\helpers\VarDumper;
use Closure;

/**
 * 基础助手类
 *
 * 这里的方法，适合所有项目
 *
 * @property Str $str
 * @property Arr $arr
 */
class Helper extends \Globalegrow\Base\Helper
{
    /**
     * @var array
     */
    protected $helpers = [];

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->getHelper($name) ?: parent::__get($name);
    }

    /**
     * 返回一个结果数组
     *
     * @param int $code 错误码
     * @param string $message 提示信息
     * @param mixed $data 数据
     * @param mixed $localData 非生产环境下的数据
     * @return array
     */
    public static function arrayResult($code, $message, $data = null, $localData = null)
    {
        $data = parent::arrayResult($code, $message, $data);
        if (!app()->env->isProduct()) {
            $data['localData'] = $localData;
        }
        if (SHOEDBSQL) {
            $data['sql'] = DbDebug::getSqlList();
            $data['localData'] = $localData;
        };
        return $data;
    }

    /**
     * 获取助手
     *
     * @param string $name
     * @return Arr|Str|null
     */
    protected function getHelper($name)
    {
        if (isset($this->helpers[$name])) {
            return $this->helpers[$name];
        }

        if (class_exists($class = __NAMESPACE__ . '\\' . ucfirst($name))) {
            return $this->helpers[$name] = new $class();
        }
        return null;
    }
}
