<?php

namespace app\modules\base\controllers;


use yii\web\Response;

/**
 * Class LanguageDataController
 *
 * @package app\modules\base\controllers
 * @property \app\modules\base\components\LanguageDataComponent $LanguageDataComponent
 */
class LanguageDataController extends Controller
{
    
    
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        
        return $this->render('index');
    }
    
    /**
     *  多语言key列表
     *
     * @return array
     */
    public function actionList()
    {
        return $this->LanguageDataComponent->lists(app()->request->get());
    }
    
    /**
     * 新增多语言key
     *
     * @return array
     */
    public function actionAddKeys()
    {
        return $this->LanguageDataComponent->addKeys(app()->request->post());
    }
    
    /**
     * 修改多语言文案
     *
     * @return array
     */
    public function actionEditKey()
    {
        return $this->LanguageDataComponent->editKeyValue(app()->request->post());
    }
    
    /**
     * 导入多语言包
     *
     * @return array
     */
    public function actionImportPackage()
    {
        return $this->LanguageDataComponent->importPackage(app()->request->post());
    }
    
    /**
     * 导出语言包
     */
    public function actionExportPackage()
    {
        return $this->LanguageDataComponent->exportPackage(app()->request->post());
    }
    
    /**
     * 获取所有多语言的key
     *
     * @return array
     */
    public function actionKeyOptions()
    {
        return $this->LanguageDataComponent->getKeysOptions();
    }
    
    /**
     * 获取组件绑定的UI组件模板
     *
     * @return array
     */
    public function actionKeyComponent(string $key)
    {
        return $this->LanguageDataComponent->getKeyUiComponent($key);
    }
}