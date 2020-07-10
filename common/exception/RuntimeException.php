<?php
namespace app\common\exception;

/**
 * 运行时异常
 *
 * @author Haishen Tian
 * @since 1.0
 */
class RuntimeException extends \Exception
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