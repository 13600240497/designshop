<?php
namespace ego\curl;

use yii;
use yii\base\Component;

/**
 * curl日志
 */
class LogItem extends Component
{
    /**
     * @var float 请求开始时间，`microtime()`
     */
    public $startTime;
    /**
     * @var string 请求方法
     */
    public $method;
    /**
     * @var string 请求base url
     */
    public $baseUrl;
    /**
     * @var string 请求uri
     */
    public $uri;
    /**
     * @var array 请求参数
     */
    public $params;
    /**
     * @var string 响应
     */
    public $response;
    /**
     * @var string 错误信息，guzzlehttp请求失败时有值
     */
    public $error;
    /**
     * @var string 对应的服务类名
     */
    public $class;
    /**
     * @var string 日志分类
     */
    public $logCategory;
    /**
     * @var array 敏感字段，写日志时将使用*替代，比如登录时的密码
     */
    public $sensitiveFields = [];
    /**
     * @var int 记录响应的最大长度
     */
    public $maxResponseLength = 10240;

    /**
     * 记录日志
     *
     * @param int $level
     */
    public function log($level)
    {
        Yii::getLogger()->log($this->getMessage(), $level, $this->logCategory);
    }

    /**
     * 获取日志消息
     *
     * @return array
     */
    protected function getMessage()
    {
        return [
            'time' => bcsub(app()->helper->microtime(), $this->startTime, 6),
            'base_url' => $this->baseUrl,
            'uri' => $this->uri,
            'method' => $this->method,
            'class' => $this->class,
            'error' => $this->error,
            'params' => json_encode($this->processParams($this->params)),
            'response' => app()->helper->str->substr(
                $this->response,
                $this->maxResponseLength,
                '...'
            ),
        ];
    }

    /**
     * 处理请求参数
     *
     * @param array
     * @return array
     */
    protected function processParams($params)
    {
        if ($params && is_array($params) && $this->sensitiveFields) {
            foreach ($this->sensitiveFields as $item) {
                if (isset($params[$item])) {
                    $params[$item] = '**********';
                }
            }
        }
        return $params;
    }
}
