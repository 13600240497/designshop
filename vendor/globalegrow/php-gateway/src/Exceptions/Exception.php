<?php
namespace Globalegrow\Gateway\Exceptions;

use Globalegrow\Gateway\Client;
use Throwable;

/**
 * 网关异常
 */
class Exception extends \Exception
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * 构造函数
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param Client $client
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null, Client $client = null)
    {
        $this->client = $client;
        parent::__construct($message, (int) $code, $previous);
    }

    /**
     * 获取`Client`对象
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
