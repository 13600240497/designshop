<?php
namespace app\modules\gbad;

use app\base\SitePlatform;

/**
 * test模块
 */
class Module extends \app\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //GB站点语言配置
        SitePlatform::setGbLanguageNames();
    }


}