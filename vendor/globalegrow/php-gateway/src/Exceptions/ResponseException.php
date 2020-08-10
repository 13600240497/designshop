<?php
namespace Globalegrow\Gateway\Exceptions;

/**
 * 响应异常
 *
 * 在请求http状态码为200，但是返回码不为0时抛出
 */
class ResponseException extends Exception
{
    /**
     * 获取`data`对象
     *
     * @return array
     */
    public function getData()
    {
        return isset($this->getClient()->getResponse()->data) ? $this->getClient()->getResponse()->data : null;
    }
}
