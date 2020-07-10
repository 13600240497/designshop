<?php
namespace app\common\exception;

/**
 * 参数异常
 *
 * @author Haishen Tian
 * @since 1.0
 */
class ArgumentsException extends RuntimeException
{
    /**
     * 构造函数
     *
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}