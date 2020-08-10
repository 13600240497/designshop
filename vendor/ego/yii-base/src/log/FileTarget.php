<?php
namespace ego\log;

use yii\helpers\VarDumper;
use yii\log\Logger;

/**
 * 文件日志
 */
class FileTarget extends \yii\log\FileTarget
{
    /**
     * @var string 日志分割符
     */
    const SEPARATOR = 'LOG-SEPARATOR';
    /**
     * @var string 时区
     */
    public $timezone = 'asia/shanghai';
    /**
     * @inheritdoc
     */
    public $maxFileSize = 5012; // KB
    /**
     * @inheritdoc
     */
    public $maxLogFiles = 10;
    /**
     * @inheritdoc
     */
    public $rotateByCopy = false;
    /**
     * @var \DateTimeImmutable
     */
    protected $datetime = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->datetime = app()->datetime->get($this->timezone);
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        $text = $message[0];
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        return $this->getMessagePrefix($message) . "\n " . // 接入elog，elog中以"["开头会当成一条日志，故加一个空格
               $text . "\n" . implode("\n", $traces) . "\n" . static::SEPARATOR;

    }

    /**
     * @inheritdoc
     */
    public function getMessagePrefix($message)
    {
        $format = '[{date}][{request_id}][{ip}]{prefix}[{requested_route}][{level}][{category}]';
        $format .= ' {request_method} {http_response_code} {url}({referer})';
        return app()->helper->str->format(
            $format,
            [
                'date' => $this->datetime->setTimestamp((int) $message[3])->format('Y-m-d H:i:s'),
                'request_id' => app()->request->getRequestId(),
                'ip' => $_SERVER['SERVER_ADDR'] ?? '--',
                'prefix' => parent::getMessagePrefix($message),
                'requested_route' => app()->requestedRoute,
                'level' => Logger::getLevelName($message[1]),
                'category' => $message[2],
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? '-',
                'http_response_code' => http_response_code() ?: 0,
                'url' => urldecode(app()->request->getHostInfo() . app()->request->getUrl()),
                'referer' => urldecode(app()->request->getReferrer()) ?: '-'
            ]
        );
    }
}
