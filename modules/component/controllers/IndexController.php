<?php
namespace app\modules\component\controllers;

use app\modules\component\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * component 组件管理控制器
 *
 * \app\modules\component\components\ComponentTplComponent ComponentTplComponent
 */
class IndexController extends Controller
{
    use MagicPropertyTrait;

    /**
     * 默认组件列表主页
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        echo $this->render('index');
    }

    /**
     * 组件列表数据接口
     */
    public function actionList()
    {
        $params = app()->request->get();
        //校验传参
        $rules = [
            [['pageNo', 'pageSize', 'type'], 'required'],
            [['range', 'pageNo', 'pageSize', 'type', 'status'], 'integer'],
            [['key', 'word'], 'string']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(100, implode('|', array_column($model->errors, 0)));
        }

        $result = $this->ManagerComponent->getList($params);
        if ($result) {
            return app()->helper->arrayResult(0, 'success', $result);
        }

        return app()->helper->arrayResult(100, 'fail', $this->ManagerComponent->errors);
    }

    /**
     * 新增组件初始化
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     */
    public function actionAdd()
    {
        $request = app()->request;
        $data = array();
        $type = $request->post('type');
        $data['category_id'] = $request->post('category_id');
        $data['range'] = $request->post('range');
        $data['name'] = $request->post('name');
        $data['description'] = $request->post('description');
        $data['logo_url'] = $request->post('logo_url');
        $data['range'] = app()->request->post('range');
        $data['siteGroups'] = app()->request->post('siteGroups', '');
        $data['need_navigate'] = (int)$request->post('need_navigate');
        $data['is_custom'] = (int)$request->post('is_custom');
        $data['icon'] = $request->post('icon');
        $res = $this->ManagerComponent->add($type, $data);
        if ($res) {
            return app()->helper->arrayResult(0, '添加成功', $res);
        } else {
            return app()->helper->arrayResult(100, '添加失败', $this->ManagerComponent->errors);
        }

    }

    /**
     * 组件编辑
     */
    public function actionEdit()
    {
        $data = array();
        $id = (int)app()->request->post('id');
        $type = (int)app()->request->post('type');
        $data['name'] = app()->request->post('name');
        $data['description'] = app()->request->post('description');
        $data['logo_url'] = app()->request->post('logo_url');
        $data['tid'] = app()->request->post('tid');
        $data['category_id'] = app()->request->post('category_id');
        $data['range'] = app()->request->post('range');
        $data['need_navigate'] = (int) app()->request->post('need_navigate');
        $data['siteGroups'] = app()->request->post('siteGroups');
	    $data['icon'] = app()->request->post('icon');
        $res = $this->ManagerComponent->edit($id, $type, $data);

        if ($res) {
            return app()->helper->arrayResult(0, '修改成功', $res);
        } else {
            return app()->helper->arrayResult(100, '修改失败:'. $this->ManagerComponent->errors);
        }

    }

    /**
     * 修改组件上下线状态
     */
    public function actionChangeStatus()
    {
        $request = app()->request;
        if ($this->ManagerComponent->changeStatus($request->post('key'), $request->post('status'))) {
            return app()->helper->arrayResult(0, '修改成功');
        } else {
            $message = is_array($this->ManagerComponent->errors) ? join('|', $this->ManagerComponent->errors) : $this->ManagerComponent->errors;
            return app()->helper->arrayResult(100, '修改失败: '. $message, $this->ManagerComponent->errors);
        }
    }

    /**
     * 组件logo图片上传
     */
    public function actionUploadLogo()
    {
        return $this->ManagerComponent->logoUpload();
    }
}
