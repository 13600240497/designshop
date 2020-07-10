<?php
namespace app\components;

use app\base\Pagination;

/**
 * 通用增删改查接口
 */
trait CURDComponentTrait
{
    /**
     * @var static 具体模型实例
     */
    protected $modelClass;
    protected $dataNotFind = '数据不存在';

    /**
     * 列表
     *
     * @return array
     */
    public function lists()
    {
        $pagination = Pagination::new(($this->modelClass)::find()->count());
        $data = ($this->modelClass)::find()
        ->orderBy('id')
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->asArray()
        ->all();
        return app()->helper->arrayResult(0, 'success', $data);
    }

    /**
     * 添加
     *
     * @param array $data
     * @param bool  $runValidation
     * @return array
     */
    public function add(array $data, $runValidation = true)
    {
        unset($data['id']);
        $model = new $this->modelClass();
        $model->load(array_map('trim', $data), '');
        if ($model->insert($runValidation)) {
            return app()->helper->arrayResult(
                0,
                '添加成功',
                ($this->modelClass)::getById($model->id)
            );
        } else {
            return app()->helper->arrayResult(1, $model->flattenErrors(', '));
        }
    }

    /**
     * 编辑
     *
     * @param int   $id
     * @param array $data
     * @param bool  $runValidation
     * @return array
     */
    public function edit($id, array $data, $runValidation = true)
    {
        if (!$data) {
            return app()->helper->arrayResult(1, '更新数据不能为空');
        } elseif (!$model = ($this->modelClass)::getById($id)) {
            return app()->helper->arrayResult(2, $this->dataNotFind);
        }

        $model->load(array_map('trim', $data), '');
        if (false === ($result = $model->update($runValidation))) {
            return app()->helper->arrayResult(
                3,
                $model->flattenErrors(', '),
                null,
                $result
            );
        } else {
            return app()->helper->arrayResult(
                0,
                '编辑成功',
                ($this->modelClass)::getById($model->id),
                $result
            );
        }
    }

    /**
     * 删除
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        $model = ($this->modelClass)::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, $this->dataNotFind);
        }
        $model->is_delete = 1;
        if (false === ($result = $model->update())) {
            return app()->helper->arrayResult(
                2,
                $model->flattenErrors(', ') ?: '删除失败'
            );
        } else {
            return app()->helper->arrayResult(
                0,
                '删除成功',
                null,
                $result
            );
        }
    }

    /**
     * 批量删除
     *
     * @param int $ids
     * @return array
     */
    public function deleteFromIds($ids)
    {
        $result = false;
        foreach ($ids as $id) {
            $result = $this->delete($id);
            if (0 != $result['code']) {
                return $result;
            }
        }
        return $result;
    }

    /**
     * 获取
     *
     * @param int $id
     * @return array
     */
    public function info($id)
    {
        $model = ($this->modelClass)::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, $this->dataNotFind);
        } else {
            return app()->helper->arrayResult(0, 'success', $model);
        }
    }
}
