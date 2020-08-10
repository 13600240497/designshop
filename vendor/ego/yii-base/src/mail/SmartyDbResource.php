<?php
namespace ego\mail;

use Smarty_Resource_Custom;
use yii\base\InvalidValueException;

/**
 * smarty db资源
 *
 * @see http://www.smarty.net/docs/zh_CN/resources.custom.tpl 自定义模板资源
 */
class SmartyDbResource extends Smarty_Resource_Custom
{
    /**
     * @var MailTemplateModelInterface
     */
    protected $model = null;
    /**
     * @var MailTemplateModelInterface[]
     */
    protected $data = [];

    /**
     * @inheritdoc
     */
    public function fetch($uuqid, &$source, &$mtime)
    {
        $data = $this->getMailTemplateData($uuqid);
        $source = $data['content'];
        $mtime = $data['update_time'] ?: $data['create_time'];
    }

    /**
     * 设置邮件模板对应的模型
     *
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param string $uuqid
     * @return MailTemplateModelInterface
     */
    public function getMailTemplateData($uuqid)
    {
        if (!isset($this->data[$uuqid])) {
            $this->data[$uuqid] = $this->model->getByUuqid($uuqid);
            if (!$this->data[$uuqid]) {
                throw new InvalidValueException(sprintf('邮件模板"%s"不存在', $uuqid));
            } elseif (!$this->data[$uuqid]['is_using']) {
                throw new InvalidValueException(sprintf('邮件模板"%s"未启用', $uuqid));
            }
        }
        return $this->data[$uuqid];
    }
}
