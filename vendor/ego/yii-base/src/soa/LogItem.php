<?php
namespace ego\soa;

/**
 * curl日志
 */
class LogItem extends \ego\curl\LogItem
{
    /**
     * @inheritdoc
     */
    protected function getMessage()
    {
        $message = parent::getMessage();
        $message['ip'] = $message['base_url'];
        $message['service'] = $message['method'];
        $message['method'] = $message['uri'];
        unset($message['uri'], $message['base_url']);
        return $message;
    }

    /**
     * @inheritdoc
     */
    protected function processParams($params)
    {
        $params['body'] = parent::processParams($params['body']);
        return $params;
    }

    /**
     * 记录日志
     *
     * @param int $level
     */
    public function log($level)
    {
        $this->logCategory = 'curl-soa';
        if ($this->response && 0 !== json_decode($this->response, true)['header']['code']) {
            $this->logCategory .= '-error';
        }
        \Yii::getLogger()->log($this->getMessage(), $level, $this->logCategory);
    }
}
