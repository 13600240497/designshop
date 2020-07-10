<?php

namespace app\modules\component\components;

use app\modules\component\models\CategoryModel;
use ego\base\JsonResponseException;

/**
 * 模块内管理分类
 */
class CategoryComponent extends Component
{
    //分类模型属性
    private $categoryModel;

    /**
     * 新增组件分类
     *
     * @param int $type [组件类型]
     * @param string $name [分类名称]
     *
     * @return  int id
     * @throws JsonResponseException
     */
    public function add(array $params)
    {
        if (app()->env->isLocal()) {
            throw new JsonResponseException($this->codeFail, '非正式环境关闭组件分类注册功能，具体流程见：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=86050773');
        }
        $this->categoryModel = new CategoryModel();
        if (!$this->validate($params)) {
            return false;
        }
        if ($this->checkName($params['type'], $params['name'])) {
            return false;
        }
        $this->categoryModel->create_user = app()->user->username;
        $this->categoryModel->place = $params['place'];
        $this->categoryModel->save();
        if ($this->categoryModel->id > 0) {
            return ['id' => $this->categoryModel->id];
        }
        $this->errors = '系统错误,稍后再试';

        return false;
    }

    /**
     * 编辑组件分类
     *
     * @param int    $id   [分类ID]
     * @param string $name [分类名称]
     *
     * @return  bool
     */
    public function edit(array $params)
    {
        $this->categoryModel = CategoryModel::findone($params['id']);
        if (!$this->categoryModel) {
            $this->errors = '数据不存在';

            return false;
        }
        if ($this->checkName($this->categoryModel->type, $params['name'])) {
            return false;
        }
        $this->categoryModel->name = $params['name'];
        $this->categoryModel->place = $params['place'];
        $this->categoryModel->update_user = app()->user->username;
        if ($this->categoryModel->save()) {
            return true;
        }
        $this->errors = '系统错误,稍后再试';

        return false;
    }

    /**检查名称是否重复
     *
     * @param int    $type 分类类型
     * @param string $name 分类名
     *                     return bool
     */
    private function checkName($type, $name)
    {
        if ($this->categoryModel->getInfoByName($type, $name)) {
            $this->errors = '分类名重复';

            return true;
        }

        return false;
    }

    /**
     * 组件列表
     *
     * @return array
     */
    public function getList(array $params)
    {
        $this->categoryModel = new CategoryModel();

        return $this->categoryModel->categoryList($params);
    }

    /**
     * 数据合法验证
     *
     * @param  array $data [请求输入数据]
     *
     * @return bool
     */
    private function validate($data)
    {
        $this->categoryModel->attributes = $data;
        if ($this->categoryModel->validate()) {
            return true;
        } else {
            $this->errors = $this->categoryModel->errors;

            return false;
        }

    }
}
