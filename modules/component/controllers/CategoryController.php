<?php
namespace app\modules\component\controllers;

use app\modules\component\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * component 组件分类管理控制器
 */
class CategoryController extends Controller
{
    use MagicPropertyTrait;

    /**
     * 分类主页
     */
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        echo $this->render('index');
    }

    /**
     * 分类列表数据接口
     */
    public function actionList()
    {
        $params = app()->request->get();
        $rules = [
            [['type', 'place', 'pageNo', 'pageSize'], 'integer']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }
        
        $result = $this->CategoryComponent->getList($params);
        return app()->helper->arrayResult(0, 'success', $result);
    }

    /**
     * 新增组件分类
     */
    public function actionAdd()
    {
        $params = app()->request->post();
        $rules = [
            [['type', 'name', 'place'], 'required'],
            [['type', 'place'], 'integer']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }
        
        $res = $this->CategoryComponent->add($params);
        if ($res) {
            return app()->helper->arrayResult(0, '添加成功', $res);
        } else {
            return app()->helper->arrayResult(100, '添加失败', $this->CategoryComponent->errors);
        }

    }

    /**
     * 编辑组件分类
     */
    public function actionEdit()
    {
        $params = app()->request->post();
        $rules = [
            [['id', 'name'], 'required']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }
        
        $res = $this->CategoryComponent->edit($params);
        if ($res) {
            return app()->helper->arrayResult(0, '修改成功', $res);
        } else {
            return app()->helper->arrayResult(100, '修改失败', $this->CategoryComponent->errors);
        }

    }
}
