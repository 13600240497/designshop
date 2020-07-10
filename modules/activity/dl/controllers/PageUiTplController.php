<?php

namespace app\modules\activity\dl\controllers;

/**
 * 组件模板管理类
 * @property \app\modules\activity\dl\components\PageUiTplComponent $PageUiTplComponent
 *
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
     * 上传预览图
     * @return array
     */
    public function actionUpload()
    {
        return $this->PageUiTplComponent->uploadPic();
    }

    /**
     * 编辑模板
     * @return array
     */
    public function actionEdit()
    {
        return $this->PageUiTplComponent->edit(app()->request->post());
    }

    /**
     * 删除页面模板(暂不支持多条删除)
     * @return array
     */
    public function actionDelete()
    {
        return $this->PageUiTplComponent->delete(app()->request->post());
    }

    /**
     * 组件模板列表
     * @return array
     */
    public function actionList()
    {
        return $this->PageUiTplComponent->getList(app()->request->get());
    }

    /**
     * 组件列表
     * @return array
     */
    public function actionUiComponentList()
    {
        return $this->PageUiTplComponent->getUiComponentList(app()->request->get());
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