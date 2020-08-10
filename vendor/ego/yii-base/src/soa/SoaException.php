<?php
namespace ego\soa;

use Exception;
use GuzzleHttp\Psr7\Response;
use yii\web\HttpException;

/**
 * SOA异常组件
 */
class SoaException extends HttpException
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
        parent::__construct(200, $message, $code, $previous);
        $this->response = $response;
    }
}
