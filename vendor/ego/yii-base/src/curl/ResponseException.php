<?php
namespace ego\curl;

use Exception;
use GuzzleHttp\Psr7\Response;

/**
 * curl响应异常
 */
class ResponseException extends Exception
{
    /**
     * @var Response 响应
     */
    public $response = null;

    /**
     * @inheritdoc
     */
    public function __construct($message, $code, Exception $previous = null, Response $response = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
}
