<?php
namespace app\modules\activity;

use yii\helpers\ArrayHelper;
/**
 * api模块
 */
class Module extends \app\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->modules = [
            'gb' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'app\modules\activity\gb\Module',
            ],
            'zf' => [
                'class' => 'app\modules\activity\zf\Module',
            ],
            'dl' => [
                'class' => 'app\modules\activity\dl\Module',
            ]
        ];
        $this->registerTranslations();
    }

    /**
     * 注册翻译组件
     */
    public function registerTranslations()
    {
        $id = $this->id;
        app()->i18n->translations['modules/'.$id.'/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/'.$id.'/messages',
            'fileMap' => [
                'modules/'.$id.'/yii' => 'yii.php',
            ],
        ];

        $viewFunctions = app()->view->renderers['twig']['functions'];
        $subModuleFunctions = [
            't' => '\app\modules\\'.$id.'\Module::t'
        ];
        app()->view->renderers['twig']['functions'] = ArrayHelper::merge($viewFunctions, $subModuleFunctions);
    }

    /**
     * 定义module内部的翻译方法
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/activity/' . $category, $message, $params, $language);
    }
}
