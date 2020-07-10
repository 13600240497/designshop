<?php

namespace app\modules\home\zf\controllers;

/**
 * 页面模板管理类
 * @property \app\modules\home\zf\components\PageUiTplComponent $PageUiTplComponent
 */
class PageUiTplController extends Controller
{
    /**
     * 模板列表首页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 新增模板
     * @return array
     */
    public function actionAdd()
    {
        return $this->PageUiTplComponent->add(app()->request->post());
    }

    /**
     * 组件预览
     * @return array
     */
    public function actionUiPreview()
    {
        return $this->PageUiTplComponent->uiPreview(app()->request->get());
    }

    /**
     * 获取组件快照
     * @return array
     */
    public function actionGetSnapshot()
    {
        return $this->PageUiTplComponent->uiPreviewPicUrl(app()->request->post());
    }
}