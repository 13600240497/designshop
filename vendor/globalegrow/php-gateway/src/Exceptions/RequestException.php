<?php
namespace Globalegrow\Gateway\Exceptions;

/**
 * 请求异常
 *
 * 在请求http状态码不为200时抛出，比如网关异常401，服务器错误5xx
 */
class RequestException extends Exception
{
}
