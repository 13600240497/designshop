<?php
namespace Globalegrow\Base\Exceptions;

/**
 * 非法的值异常
 *
 * 在值不在允许的类型或者范围内时抛出
 */
class InvalidValueException extends \UnexpectedValueException implements ExceptionInterface
{
}
