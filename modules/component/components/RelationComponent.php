<?php
namespace app\modules\component\components;

use app\base\Pagination;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\component\models\UiTplRelationModel;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;

/**
 * 组件模板管理
 */
class RelationComponent extends Component
{
    /**
     * 活动列表
     *
     * @param array $params
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function lists($params)
    {
        $query = UiTplRelationModel::find()->alias('r')
            ->select('r.*, st.name as tpl_name, su.id as component_id, su.name as component_name,
                tt.name as relation_tpl_name, tu.id as relation_component_id, tu.name as relation_component_name')
            ->leftJoin(UiTplModel::tableName() . ' as st', 'st.id = r.tpl_id')
            ->leftJoin(UiModel::tableName() . ' as su', 'su.component_key = st.component_key')
            ->leftJoin(UiTplModel::tableName() . ' as tt', 'tt.id = r.relation_tpl_id')
            ->leftJoin(UiModel::tableName() . ' as tu', 'tu.component_key = tt.component_key')
            ->where([
                'st.is_delete' => UiTplModel::NOT_DELETE,
                'su.is_delete' => UiModel::NOT_DELETE,
                'tt.is_delete' => UiTplModel::NOT_DELETE,
                'tu.is_delete' => UiModel::NOT_DELETE
            ])
            ->andFilterWhere(['like', 'su.name', $params['pc_component_name'] ?? ''])
            ->andFilterWhere(['like', 'tu.name', $params['wap_component_name'] ?? '']);

        $count = $query->count();
        $pagination = Pagination::new($count);

        $list = $query
            ->groupBy('r.id')
            ->orderBy('r.id desc')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all();

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list'       => $list ? ArrayHelper::toArray($list) : [],
                'pagination' => [
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }

    /**
     * 参数检查
     *
     * @param array $data
     *
     * @return UiTplRelationModel
     * @throws JsonResponseException
     */
    private function checkParams(array $data)
    {
        if (!isset($data['tpl_id'], $data['relation_tpl_id'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        $prevTpl = UiTplModel::getTplFullInfo($data['tpl_id']);
        if (!$prevTpl) {
            throw new JsonResponseException($this->codeFail, '未找到tpl_id对应的模板');
        }
        if (!$prevTpl->status) {
            throw new JsonResponseException($this->codeFail, 'tpl_id对应的模板已被禁用');
        }

        $nextTpl = UiTplModel::getTplFullInfo($data['relation_tpl_id']);
        if (!$nextTpl) {
            throw new JsonResponseException($this->codeFail, '未找到relation_tpl_id对应的模板');
        }
        if (!$nextTpl->status) {
            throw new JsonResponseException($this->codeFail, 'relation_tpl_id对应的模板已被禁用');
        }

        //检查组件的端属性是否一致
        if ($prevTpl->range === $nextTpl->range) {
            throw new JsonResponseException($this->codeFail, '相同端的组件不能做关联');
        }

        //一个组件能关联的组件个数最多只有1个
        if (!UiTplRelationModel::checkComponentRelationCount(
            $prevTpl->component_key,
            $nextTpl->component_key,
            $data['id'] ?? 0
        )) {
            throw new JsonResponseException($this->codeFail, '一个组件能关联的组件个数最多只能有1个');
        }

        return UiTplRelationModel::findOne([
            'tpl_id' => $data['tpl_id'],
            'relation_tpl_id' => $data['relation_tpl_id']
        ]);
    }

    /**
     * 新增组件模板关联关系
     *
     * @param array $data
     * @param bool $runValidation
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws JsonResponseException
     */
    public function add(array $data, $runValidation = true)
    {
        if ($relationModel = $this->checkParams($data)) {
            throw new JsonResponseException($this->codeFail, '已存在相同关联关系的记录');
        }

        $relationModel = new UiTplRelationModel();
        $relationModel->tpl_id = $data['tpl_id'];
        $relationModel->relation_tpl_id = $data['relation_tpl_id'];
        $relationModel->status = UiTplRelationModel::STATUS_USED;

        if (!$relationModel->insert($runValidation)) {
            return app()->helper->arrayResult($this->codeFail, '添加失败：' . $relationModel->flattenErrors(', '));
        }

        return app()->helper->arrayResult($this->codeSuccess, '添加成功');
    }

    /**
     * 编辑组件模板关联关系
     *
     * @param array $data
     * @param bool $runValidation
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws JsonResponseException
     * @throws \yii\db\StaleObjectException
     */
    public function edit(array $data, $runValidation = true)
    {
        if (!isset($data['id'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        if (!$oldRelationModel = UiTplRelationModel::findOne($data['id'])) {
            throw new JsonResponseException($this->codeFail, '未找到id对应的模板关联关系');
        }
        $relationModel = $this->checkParams($data);
        if ($relationModel && $oldRelationModel->id !== $relationModel->id) {
            throw new JsonResponseException($this->codeFail, '已存在相同关联关系的记录');
        }

        $oldRelationModel->tpl_id = $data['tpl_id'];
        $oldRelationModel->relation_tpl_id = $data['relation_tpl_id'];

        if (false === $oldRelationModel->update($runValidation)) {
            return app()->helper->arrayResult($this->codeFail, '修改失败：' . $oldRelationModel->flattenErrors(', '));
        }

        return app()->helper->arrayResult($this->codeSuccess, '修改成功');
    }

    /**
     * 启用/禁用关联关系的状态
     *
     * @param array $data
     * @param bool $runValidation
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeStatus(array $data, $runValidation = true)
    {
        if (!isset($data['id'], $data['status'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        if (!$oldRelationModel = UiTplRelationModel::findOne($data['id'])) {
            throw new JsonResponseException($this->codeFail, '未找到id对应的模板关联关系');
        }

        $oldRelationModel->status = $data['status'];
        $operate = $oldRelationModel->status ? '启用' : '禁用';

        if (false === $oldRelationModel->update($runValidation)) {
            return app()->helper->arrayResult(
                $this->codeFail,
                $operate . '失败：' . $oldRelationModel->flattenErrors(', ')
            );
        }

        return app()->helper->arrayResult($this->codeSuccess, $operate . '成功');
    }
}
