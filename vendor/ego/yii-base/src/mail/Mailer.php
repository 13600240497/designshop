<?php
namespace ego\mail;

use yii;

/**
 * 邮件发送
 */
class Mailer extends yii\swiftmailer\Mailer
{
    /**
     * @var string 邮件模板模型类
     */
    public $mailTemplateModelClass = null;
    /**
     * @var string 邮件发送历史模型类
     */
    public $mailHistoryModelClass = null;
    /**
     * 只给特定的email发送邮件
     *
     * 防止非线上环境发送不必要的邮件
     *
     * ```php
     * [
     *      '@globalegrow.com' => '@globalegrow.com',
     *      'foo@example.com' => 'foo@example.com'
     * ]
     * ```
     * @var array
     */
    public $only = [];
    /**
     * @var MailHistoryModelInterface
     */
    private $mailHistoryModel = null;
    /**
     * @var SmartyRenderer
     */
    private $renderer = null;
    /**
     * @var string 邮件模板uuqid
     */
    private $uuqid = null;
    /**
     * @var string 发送开始时间，`microtime(true)`
     */
    private $sendStartTime = null;
    /**
     * @var string 发送结束时间，`microtime(true)`
     */
    private $sendEndTime = null;
    /**
     * @var string 错误信息
     */
    private $error = null;

    /**
     * @inheritdoc
     * @return string|message
     */
    public function send($message)
    {
        if ($this->only && ($to = $message->getTo()) && 1 == count($to)
            && !app()->helper->str->has(key($to), $this->only, '|')
        ) {
            $this->error = '发送email不在指定范围内：' . join('、', $this->only);
            $this->afterSend($message, false);
            return $this->error;
        }
        try {
            return parent::send($message);
        } catch (\Exception $e) {
            $this->error = get_class($e) . ': ' . $e->getMessage();
            $this->afterSend($message, false);
            return $this->error;
        }
    }

    /**
     * @inheritdoc
     * @return Message
     */
    public function compose($view = null, array $params = [])
    {
        return parent::compose($view, $params);
    }

    /**
     * 获取邮件模板渲染对象
     *
     * @return SmartyRenderer
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->renderer = Yii::createObject(SmartyRenderer::class);
            $this->renderer->getDbResource()->setModel(
                Yii::createObject($this->mailTemplateModelClass)
            );
        }
        return $this->renderer;
    }

    /**
     * 根据邮件模板发送邮件
     *
     * @param Message $message
     * @param string $uuqid
     * @param array $smartyVars
     * @return bool
     */
    public function sendByUuqid($message, $uuqid, array $smartyVars = [])
    {
        $message->setSmartyVars($smartyVars);
        $this->uuqid = $uuqid;
        $mailTemplateData = $this->getRenderer()->getDbResource()->getMailTemplateData($uuqid);
        $this->getRenderer()->smarty->clearAllAssign();
        $smartyVars['app'] = app();
        $this->getRenderer()->smarty->assign($smartyVars);
        $message->setHtmlBody(
            $this->getRenderer()->smarty->fetch($uuqid)
        )->setSubject(app()->helper->str->format(
            $mailTemplateData['subject'],
            $smartyVars['subject_params'] ?? [],
            '{$',
            '}'
        ));
        return $this->send($message);
    }

    /**
     * 获取发送消耗的时间
     *
     * @return float
     */
    public function getSendUsedTime()
    {
        return round($this->sendEndTime - $this->sendStartTime, 4);
    }

    /**
     * 获取发送邮件的模板uuqid
     *
     * @return string
     */
    public function getUuqid()
    {
        return $this->uuqid;
    }

    /**
     * 获取发送错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function beforeSend($message)
    {
        $this->sendStartTime = microtime(true);
        return parent::beforeSend($message);
    }

    /**
     * @inheritdoc
     */
    public function afterSend($message, $isSuccessful)
    {
        $this->error = $isSuccessful ? '' : $this->error;
        $this->sendEndTime = microtime(true);
        if ($this->mailHistoryModel) {
            $this->mailHistoryModel->addMailHistory($this, $message, $isSuccessful);
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->only) {
            $this->only = array_filter($this->only);
        }
        if ($this->mailHistoryModelClass) {
            $this->mailHistoryModel = Yii::createObject($this->mailHistoryModelClass);
        }
    }
}
