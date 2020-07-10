<?php

namespace app\modules\home\dl\controllers;

use app\base\RequestUtils;

/**
 * 页面模板管理类
 * @property \app\modules\home\dl\components\PageTplComponent $PageTplComponent
 * @property \app\modules\home\dl\components\PageDesignComponent $PageDesignComponent
 * @property \app\modules\home\dl\components\PageUiComponentDataComponent $PageUiComponentDataComponent
 *
 */
class PageTplController extends Controller
{
    /**
     * 模板列表首页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 模板列表数据
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionList()
    {
        $params = app()->request->get();
        $rules = [
            ['site_code', 'required'],
            [['name', 'lang', 'site_code'], 'string'],
            [['type', 'place', 'pageNo', 'pageSize'], 'integer']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }
        $params['type'] = isset($params['type']) ? $params['type'] : 0;

        $result = $this->PageTplComponent->getList($params);
        return app()->helper->arrayResult(0, 'success', $result);
    }

    /**
     * 新增模板
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionAdd()
    {
        $request = app()->request;
        $params = array(
            $request->post('name'),
            $request->post('pageId'),
            $request->post('lang'),
            $request->post('site_code'),
            $request->post('pic'),
            $request->post('pid'),
            $request->post('type', 1),
        );
        $res = $this->PageTplComponent->add($params);
        if (!$res) {
            return app()->helper->arrayResult(100, $this->PageTplComponent->errors, $this->PageTplComponent->errors);
        }
        return app()->helper->arrayResult(0, 'success', $res);
    }

    /**
     * 编辑模板
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEdit()
    {
        $request = app()->request;
        $id = $request->post('id');
        $name = $request->post('name');
        $pic = $request->post('pic');
        $type = $request->post('type', 1);
        $res = $this->PageTplComponent->edit($id, $name, $pic, $type);
        if ($res) {
            return app()->helper->arrayResult(0, 'success', $res);
        } else {
            return app()->helper->arrayResult(100, $this->PageTplComponent->errors, $this->PageTplComponent->errors);
        }
    }

    /**
     * 删除页面模板(暂不支持多条删除)
     *
     * @param int $id
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        if (true === $this->PageTplComponent->delete($id)) {
            return app()->helper->arrayResult(0, 'success', []);
        }

        return app()->helper->arrayResult(100, $this->PageTplComponent->errors, $this->PageTplComponent->errors);
    }

    /**
     * 修改默认模板
     */
    public function actionChangeTpl()
    {
        $res = $this->PageTplComponent->changeDefaultTpl(app()->request->get('id'), app()->request->get('site_code'));
        if ($res) {
            return app()->helper->arrayResult(0, 'success', $res);
        } else {
            return app()->helper->arrayResult(100, 'fail', $this->PageTplComponent->errors);
        }
    }

    /**
     * 模板预览图上传
     */
    public function actionUploadPic()
    {
        return $this->PageTplComponent->picUpload();
    }

    /**
     * 查看预览页面模板
     * @property \app\modules\home\dl\components\PageDesignComponent $PageDesignComponent
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionPreview()
    {
        $request = app()->request;
        $param = array(
            $request->get('id'),           //页面模板id【page_template.id】
            $request->get('pid'),          //页面32位长度的pid
            $request->get('site_code'),   //站点简称
            $request->get('lang')          //语种
        );

        return $this->PageDesignComponent->look($param);
    }

    /**
     * 确认切换模板
     * @property \app\modules\home\dl\components\PageUiComponentDataComponent $PageUiComponentDataComponent
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionConfirmTpl()
    {
        $request = app()->request;
        $param = array(
            $request->post('page_id'),
            $request->post('tpl_id'),
            $request->post('lang')
        );
        return $this->PageUiComponentDataComponent->confirmTpl($param);
    }

    /**
     * 清理模板缓存
     * @property \app\modules\activity\components\PageDesignComponent $PageDesignComponent
     * @param int $id
     * @return array
     */
    public function actionClearCache(int $id)
    {
        return $this->PageDesignComponent->clearCache($id);
    }

    /**
     * 获取模板快照
     *
     */
    public function actionGetSnapshot()
    {
        $url = app()->params['url']['admin'];
        $url .= '/'. RequestUtils::getModuleName();
        $url .= '/dl/design/snapshot?page_id='. app()->request->post('id','');
        $pic = \app\base\Snapshot::get($url);
        if ($pic) {
            return app()->helper->arrayResult(0, 'success', $pic);
        } else {
            return app()->helper->arrayResult(100, '快照生成失败', '快照生成失败');
        }
    }
}
