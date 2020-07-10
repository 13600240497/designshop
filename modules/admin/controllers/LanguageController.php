<?php
namespace app\modules\admin\controllers;

use yii\web\Response;

/**
 * 多语言控制器
 * @property \app\modules\admin\components\LanguageComponent $LanguageComponent
 */
class LanguageController extends Controller
{
    /**
     * 首页
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 列表
     * @return array
     */
    public function actionList()
    {
        return $this->LanguageComponent->lists();
    }

    /**
     * 新增
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionAdd()
    {
        return $this->LanguageComponent->add(app()->request->post());
    }

    /**
     * 编辑
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionEdit()
    {
        return $this->LanguageComponent->edit(
            app()->request->post('id'),
            app()->request->post()
        );
    }

    /**
     * 删除
     * @return array
     * @throws \Throwable
     */
    public function actionDelete()
    {
        return $this->LanguageComponent->delete(
            app()->request->post('id'),
            app()->request->post('content_id')
        );
    }

    /**
     * 详情
     * @return array
     */
    public function actionInfo()
    {
        return $this->LanguageComponent->info(
            app()->request->get('id'),
            app()->request->get('content_id')
        );
    }

    /**
     * 语言包导入
     * @return array
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionImport()
    {
        return $this->LanguageComponent->import($_FILES['name']);
    }

    /**
     * 语言包导出
     * @param int $activity_id 活动ID
     * @return array
     * @throws \PHPExcel_Exception
     */
    public function actionExport($activity_id = 0)
    {
        return $this->LanguageComponent->export($activity_id);
    }

    /**
     * 语言包生成
     * @return array
     */
    public function actionBuild()
    {
        return $this->LanguageComponent->build();
    }

    /**
     * 获取js语言包内容
     * @return string
     */
    public function actionGetJsLang()
    {
        return $this->LanguageComponent->getJsLang(
            app()->request->get('lang'),
            app()->request->get('activity_id', 0)
        );
    }

    /**
     * 获取语言key列表
     * @return array
     */
    public function actionLangList()
    {
        return $this->LanguageComponent->getLangList(app()->request->get());
    }

    /**
     * 从组件中提取待翻译的语言包
     * @return array
     * @throws \Exception
     */
    public function actionExtractComponent()
    {
        return $this->LanguageComponent->extractComponent(app()->request->post('component_key'));
    }

    /**
     *  获取渠道语言/端 列表
     * @return array
     */
    public function actionPipelineList()
    {
        return $this->LanguageComponent->getPipelineList();
    }

    /**
     *  获取服装国家站语言/端 列表
     * @return array
     */
    public function actionCountrySiteList()
    {
        return $this->LanguageComponent->getCountrySiteList(app()->request->get());
    }

}

