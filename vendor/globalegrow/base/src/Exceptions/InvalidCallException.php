<?php
namespace Globalegrow\Base\Exceptions;

/**
 * 非法调用异常
 *
 * 在调用了一个不存在的方法时抛出
 */
class InvalidCallException extends \BadMethodCallException implements ExceptionInterface
{
}
