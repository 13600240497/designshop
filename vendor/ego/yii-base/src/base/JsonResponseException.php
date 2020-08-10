<?php
namespace ego\base;

use yii\base\ExitException;

/**
 * json正常响应异常
 *
 * 使用`throw new \ego\base\JsonResponseException`替代返回多个`app()->helper->arrayResult()`，解决代码复杂度问题
 */
class JsonResponseException extends ExitException
{
    /**
     * 返回一个结果数组
     *
     * @param int $code 错误码
     * @param string $message 提示信息
     * @param mixed $data 数据
     * @param mixed $localData 非生产环境下的数据
     */
    public function __construct($code, $message, $data = null, $localData = null)
    {
        app()->response->data = app()->helper->arrayResult($code, $message, $data, $localData);
        parent::__construct($code, $message);
    }
}
