<?php
namespace Globalegrow\Base;

use Globalegrow\Base\Exceptions\AssertionException;

/**
 * 断言
 */
class Assertion extends Component
{
    /**
     * 断言
     *
     * @param mixed $value 需要断言一定为**true**的值
     * @param string|null $message 断言失败时的提示信息，如果为**null**，则自动定位到调用该方法的php文件的那一行内容
     * @throws AssertionException 断言失败
     */
    public static function assertTrue($value, $message = null)
    {
        if (true === $value) {
            return;
        }

        $calledBacktrace = Helper::getCalledBacktrace();
        if (null === $message) {
            $message = '断言true失败: ';
            $message .= trim(file($calledBacktrace->file)[$calledBacktrace->line - 1]);
        }

        $exception = new AssertionException($message);
        $exception->setFileLine($calledBacktrace->file, $calledBacktrace->line);
        throw $exception;
    }
}
