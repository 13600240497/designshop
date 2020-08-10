<?php
namespace Globalegrow\Base\Exceptions;

/**
 * 不合法的参数异常
 *
 * 在函数或者方法参数不正确（缺少参数、参数类型不对等）时抛出
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
