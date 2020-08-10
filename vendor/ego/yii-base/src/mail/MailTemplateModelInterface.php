<?php
namespace ego\mail;

/**
 * 邮件模板模型接口
 */
interface MailTemplateModelInterface
{
    /**
     * 根据uuqid获取邮件模板信息
     *
     * @param string $uuqid
     * @return MailTemplateModelInterface
     */
    public static function getByUuqid($uuqid);
}
