<?php
namespace ego\mail;

use yii;

/**
 * smarty渲染
 *
 * ```php
 *      app()->smartyRenderer->fetch(\ego\enums\Mail::REGISTER);
 * ```
 *
 * @property \Smarty $smarty
 * @see http://www.smarty.net/docs/zh_CN/resources.custom.tpl 自定义模板资源
 */
class SmartyRenderer extends yii\smarty\ViewRenderer
{
    /**
     * @var SmartyDbResource
     */
    protected $dbResource = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->smarty->registerResource('db', $this->getDbResource());
        $this->smarty->setDefaultResourceType('db');
    }

    /**
     * 获取smarty对象
     *
     * @return \Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * 获取自定义db资源
     *
     * @return SmartyDbResource
     */
    public function getDbResource()
    {
        if (null === $this->dbResource) {
            $this->dbResource = Yii::createObject(SmartyDbResource::class);
        }
        return $this->dbResource;
    }
}
