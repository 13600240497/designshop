<?php

namespace app\modules\component\controllers;

use yii\web\Response;

/**
 * component 组件模板管理控制器
 *
 * @property \app\modules\component\components\ComponentTplComponent ComponentTplComponent
 */
class ComponentTplController extends Controller
{
    /**
     * 模板主页
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        echo $this->render('index');
    }

    /**
     * 模板列表数据接口
     */
    public function actionList()
    {
        $params = app()->request->get();
        $rules = [
            [['name', 'key'], 'string'],
            ['status', 'integer']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }

        $result = $this->ComponentTplComponent->getList($params);

        return app()->helper->arrayResult(0, 'success', $result);
    }

    /**
     * 新增组件模板
     * @throws \ego\base\JsonResponseException
     * @throws \yii\db\Exception
     */
    public function actionAdd()
    {
        $params = app()->request->post();
        $rules = [
            [['name_en', 'name', 'key', 'siteGroups'], 'required'],
            ['pic', 'string']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }

        $res = $this->ComponentTplComponent->add($params);
        if ($res) {
            return app()->helper->arrayResult(0, '添加成功', $res);
        }

        return app()->helper->arrayResult(100, '添加失败', $this->ComponentTplComponent->errors);
    }

    /**
     * 编辑组件模板
     */
    public function actionEdit()
    {
        $params = app()->request->post();
        $rules = [
            [['name_en', 'name', 'id', 'siteGroups'], 'required'],
            ['id', 'integer'],
            ['pic', 'string']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }

        $res = $this->ComponentTplComponent->edit($params);
        if ($res) {
            return app()->helper->arrayResult(0, '修改成功', $res);
        }

        return app()->helper->arrayResult(100, '修改失败', $this->ComponentTplComponent->errors);
    }

    /**
     * 删除组件模板
     *
     * @param string $id
     *
     * @return array
     */
    public function actionDelete(string $id)
    {
        if (true === $this->ComponentTplComponent->delete($id)) {
            return app()->helper->arrayResult(0, '删除成功', []);
        }

        return app()->helper->arrayResult(100, $this->ComponentTplComponent->errors);
    }

    /**
     * 修改模板上下线状态
     */
    public function actionChangeStatus()
    {
        $request = app()->request;
        if ($this->ComponentTplComponent->changeStatus($request->post('id'), $request->post('status'))) {
            return app()->helper->arrayResult(0, '修改成功');
        }

        $errors = $this->ComponentTplComponent->errors;
        $message = is_array($errors) ? join('|', $errors) : $errors;
        return app()->helper->arrayResult(100, '修改失败: '. $message, $errors);
    }

    /**
     * 组件模板图片上传
     */
    public function actionUploadPic()
    {
        return $this->ComponentTplComponent->picUpload();
    }

    /**
     * 获取组件配置
     * @param int $id
     * @param int $cache 是否使用缓存，1-使用，0-不使用
     * @return array
     */
    public function actionGetConfig(int $id, int $cache = 1)
    {
        return $this->ComponentTplComponent->getConfig($id, $cache);
    }
}
