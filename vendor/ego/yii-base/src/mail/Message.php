<?php
namespace ego\mail;

use yii;

/**
 * 邮件message
 */
class Message extends yii\swiftmailer\Message
{
    /**
     * @var Mailer
     */
    public $mailer;
    /**
     * @var array 敏感变量，写日志时将使用*替代，比如登录时的密码
     */
    public $sensitiveSmartyVars = [];
    /**
     * @var string 发送的邮件内容
     */
    private $body = null;
    /**
     * @var array smarty变量
     */
    private $smartyVars = [];

    /**
     * 根据邮件模板发送邮件
     *
     * @param string $uuqid
     * @param array $smartyVars
     * @return bool
     */
    public function sendByUuqid($uuqid, array $smartyVars = [])
    {
        return $this->mailer->sendByUuqid($this, $uuqid, $smartyVars);
    }

    /**
     * 获取发送的邮件内容
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->body = $html;
        return parent::setHtmlBody($html);
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->body = $text;
        return parent::setTextBody($text);
    }

    /**
     * 设置smarty变量
     *
     * @param array $smartyVars
     * @return $this
     */
    public function setSmartyVars($smartyVars)
    {
        $this->smartyVars = $smartyVars;
        return $this;
    }

    /**
     * 获取smarty变量
     *
     * @return array
     */
    public function getSmartyVars()
    {
        return $this->processSmartyVars($this->smartyVars);
    }

    /**
     * 处理smarty变量
     *
     * @param array
     * @return array
     */
    protected function processSmartyVars($vars)
    {
        if ($vars && is_array($vars) && $this->sensitiveSmartyVars) {
            foreach ($this->sensitiveSmartyVars as $item) {
                if (isset($vars[$item])) {
                    $vars[$item] = '**********';
                }
            }
        }
        return $vars;
    }
}
