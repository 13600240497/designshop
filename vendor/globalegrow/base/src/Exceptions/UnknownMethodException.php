<?php
namespace Globalegrow\Base\Exceptions;

/**
 * 未知方法异常
 *
 * 在类方法不存在时抛出
 */
class UnknownMethodException extends \BadMethodCallException implements ExceptionInterface
{
}
