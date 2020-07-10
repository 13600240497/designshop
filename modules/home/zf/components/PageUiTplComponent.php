<?php

namespace app\modules\home\zf\components;

use yii\web\Response;
use app\base\BizException;
use app\modules\common\zf\components\CommonPageUiTplComponent;

/**
 * 组件模板管理组件
 */
class PageUiTplComponent extends CommonPageUiTplComponent
{
    /**
     * @inheritdoc
     */
    public function add($params)
    {
        try {
            $data = parent::add($params);
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data ?: []);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function edit($params)
    {
        try {
            $data = parent::edit($params);
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data ?: []);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($params)
    {
        try {
            $data = parent::delete($params);
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data ?: []);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getList($params)
    {
        $rules = [
            ['site_code', 'required'],
            [['site_code', 'lang', 'ui_key'], 'string'],
            [['view_type', 'place_type', 'pageNo', 'pageSize'], 'integer']
        ];

        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        try {
            $data = parent::getList($params);
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data ?: []);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getUiComponentList($params)
    {
        try {
            $data = parent::getUiComponentList($params);
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data ?: []);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }

    /**
     * 组件预览
     *
     * @param array $params 传参数组
     *  - page_id int 页面ID
     *  - ui_id int 组件ID
     *
     * @return array
     */
    public function uiPreview($params)
    {
        app()->response->format = Response::FORMAT_HTML;
        try {
            $uiHtml = parent::getUiPreviewHtml($params);
            return $uiHtml;
        } catch (BizException $e) {
            return $e->getMessage();
        }

    }

    /**
     * 获取组件显示截图的保存到S3的URL地址
     *
     * @param array $params 传参数组
     *  - page_id int 页面ID
     *  - ui_id int 组件ID
     * @return array
     */
    public function uiPreviewPicUrl($params)
    {
        try {
            $picS3Url = parent::getUiPreviewPicUrl($params);
            if (!$picS3Url) {
                return app()->helper->arrayResult($this->codeFail, '快照生成失败', '快照生成失败');
            }

            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $picS3Url);
        } catch (BizException $e) {
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }
}