<?php
/**
 * Created by PhpStorm.
 * User: tenjiashun
 * Date: 2018/11/28
 * Time: 20:59
 */

namespace app\modules\test\zf\controllers;

/**
 * 数据处理类
 * @property \app\modules\test\zf\components\ImportComponent $ImportComponent
 * @package app\modules\test\zf\controllers
 */
class DataController extends Controller
{
    /**
     * 处理三端合一之前的页面
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfActivity()
    {
        return $this->ImportComponent->importActivity();
    }

    /**
     * 导入zf_activity表数据
     * @return array
     * @throws \Throwable
     */
    public function actionNoGroupPages()
    {
        return $this->ImportComponent->noGroupPages();
    }

    /**
     * 导入zf_activity_group表数据
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfActivityGroup()
    {
        return $this->ImportComponent->importActivityGroup();
    }

    /**
     * 导入zf_page_convert_relation表数据
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfPageConvertRelation()
    {
        return $this->ImportComponent->importPageConvertRelation();
    }

    /**
     * 导入zf_page_template表数据
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfPageTemplate()
    {
        return $this->ImportComponent->importPageTemplate();
    }

    /**
     * 导入zf_page表数据
     * @param int $start
     * @param int $end
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfPage(int $start, int $end)
    {
        return $this->ImportComponent->importPage($start, $end);
    }

    /**
     * 导入zf_page_group表数据
     * @return array
     * @throws \Throwable
     */
    public function actionImportZfPageGroup()
    {
        return $this->ImportComponent->importPageGroup();
    }
}
