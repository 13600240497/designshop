<?php
namespace ego\web;

/**
 * 基础Controller，继承`yii\web\Controller`
 */
class Controller extends \yii\web\Controller
{
    /**
     * @var \ego\base\Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $hostInfo = app()->request->getHostInfo();
        $domain = $this->module->domain;
        if (!$domain) {
            return;
        } elseif (0 === strpos($domain, 'http') && 0 !== strpos($hostInfo, $domain)
            || false === strpos($domain, 'http') && false === strpos($hostInfo, $domain)
        ) {
            throw new InvalidRequestException('Access Denied');
        }
    }
}
