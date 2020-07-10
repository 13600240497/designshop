<?php
namespace app\modules\test\controllers;

use yii\web\Response;
use app\modules\test\Module;

/**
 * 首页控制器
 */
class I18nController extends Controller
{
    public function actionIndex()
    {
        app()->language = 'zh-CN';
        app()->response->format = Response::FORMAT_HTML;
        // 自定义翻译
        $data = [
            'trans' => Module::t('yii', "I'm going to be translated in controller")
        ];
        return $this->render('index.twig', $data);
    }
}
