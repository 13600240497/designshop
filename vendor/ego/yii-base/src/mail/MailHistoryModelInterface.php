<?php
namespace ego\mail;

use yii\mail\MessageInterface;

/**
 * 邮件发送历史模型接口
 */
interface MailHistoryModelInterface
{
    /**
     * 根据uuqid获取邮件模板信息
     *
     * @param Mailer $mailer
     * @param MessageInterface|Message $message
     * @param bool $isSuccess
     * @return mixed
     */
    public static function addMailHistory($mailer, $message, $isSuccess);
}
