<?php
namespace Globalegrow\Base\Exceptions;

/**
 * 断言异常
 */
class AssertionException extends InvalidValueException
{
    /**
     * 设置文件及所在行数
     *
     * @param string $file
     * @param int $line
     */
    public function setFileLine($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }
}
