<?php

namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * Class LanguagePackageController
 *
 * @package app\modules\base\controllers
 * @property \app\modules\base\components\LanguagePackageComponent $LanguagePackageComponent
 */
class LanguagePackageController extends Controller
{
    
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('//layouts/vue-entry.php');
    }
    
    /**
     * 获取语言包列表
     *
     * @param string $site_code
     *
     * @return array
     */
    public function actionList(string $site_code)
    {
        return $this->LanguagePackageComponent->lists($site_code);
    }
    
    /**
     * 新增语言
     *
     * @return array
     */
    public function actionAddLang()
    {
        $siteCode = app()->request->post('site_code');
        $langName = app()->request->post('name');
        $langCode = app()->request->post('code');
        
        return $this->LanguagePackageComponent->addPackageLang($siteCode, $langName, $langCode);
    }

    public function actionKeys()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }
}
