<?php
namespace app\modules\test;


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
        $this->modules = [
            'gb' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'app\modules\test\gb\Module',
            ],
            'zf' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'app\modules\test\zf\Module',
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
        app()->view->renderers['twig']['functions'] = [
            't' => '\app\modules\\'.$id.'\Module::t'
        ];
    }

    /**
     * 定义module内部的翻译方法
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/test/' . $category, $message, $params, $language);
    }
}
