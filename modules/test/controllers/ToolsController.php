<?php
namespace app\modules\test\controllers;

use yii\helpers\ArrayHelper;

/**
 * 工具控制器
 * @property \app\modules\test\components\ToolsComponent $ToolsComponent
 */
class ToolsController extends Controller
{
    /**
     * @link /test/tools/export-site-async-api
     */
    public function actionExportSiteAsyncApi()
    {
        $params = app()->request->get();
        return $this->ToolsComponent->exportSiteAsyncApi($params);
    }

    /**
     * @link /test/tools/export-ui-tpl-languages
     */
    public function actionExportUiTplLanguages()
    {
        return $this->ToolsComponent->exportUiTplLanguages();
    }

    /**
     * @link /test/tools/ui-languages
     */
    public function actionUiLanguages()
    {
        $this->ToolsComponent->getUiLanguages();
    }

    /**
     * @link /test/tools/order
     */
    public function actionOrder()
    {
        return $this->ToolsComponent->goodsOrder();
    }

    /**
     * @link /test/tools/lang-ro
     */
    public function actionLangRo()
    {
        return $this->ToolsComponent->langRo();
    }

    /**
     * @link /test/tools/clean-request-log
     */
    public function actionCleanRequestLog()
    {
        return $this->ToolsComponent->cleanRequestLog();
    }

    /**
     * @link /test/tools/gb-trans
     */
    public function actionGbTrans()
    {
        $this->ToolsComponent->gbTrans();
    }

    /**
     * @link /test/tools/site404
     */
    public function actionSite404()
    {
        $this->ToolsComponent->site404();
    }

    /**
     * 清理Twig运行缓存
     * @link /test/tools/del-twig-cache
     */
    public function actionDelTwigCache()
    {
        return $this->ToolsComponent->delTwigCache();
    }


}