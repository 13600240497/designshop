<?php
namespace app\modules\base\components;

use app\base\Pagination;
use app\modules\base\models\AdminLogModel;
use app\modules\base\models\SettingModel;

/**
 * 管理员操作日志组件
 */
class AdminLogComponent extends Component
{
    /**
     * @var array 匹配字段
     */
    protected $matchFields = [
        'admin_name',
        'request_route',
    ];
    /**
     * @var array 请求路由对应的操作名称
     */
    protected $requestRoute2name;
    /**
     * @var AdminLogModel 管理员日志模型
     */
    protected $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->model = new AdminLogModel();
    }

    /**
     * 列表
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function lists()
    {
        $query = $this->model->find()->filterWhere([
            'record_table' => app()->request->get('record_table'),
        ]);
        $field = app()->request->get('field', 'request_route');
        $keyword = app()->request->get('keyword');
        if ($keyword) {
            if (\in_array($field, ['request_route', 'detail'])) {
                $query->filterWhere(['like', $field, $keyword]);
            } else {
                $query->filterWhere([$field => $keyword]);
            }
        }
        if ($time = app()->request->get('create_time_from')) {
            $query->andWhere(['>=', 'create_time', $time]);
        }
        if ($time = app()->request->get('create_time_to')) {
            $query->andWhere(['<=', 'create_time', $time]);
        }
        $fields = $this->model->getTableSchema()->columnNames;
        $fields = array_combine($fields, $fields);
        unset($fields['detail']);
        $fields['detail_length'] = 'LENGTH(detail)';
        $total = (int)$query->count();
        $pagination = Pagination::new($total);
        $data = $query->limit($pagination->limit)
            ->offset($pagination->offset)
            ->select($fields)
            ->orderBy('id desc')
            ->asArray()
            ->all();
        foreach ($data as &$item) {
            /** @var AdminLogModel $item */
            $item = $this->toListItem($item);
        }
        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list' => $data,
                'pagination' => [
                    'pageNo' => $pagination->page+1,
                    'pageSize' => $pagination->pageSize,
                    'totalCount' => $total
                ],
                'fields' => $this->getMatchFields(),
                'tables' => $this->getTableLabels(),
            ]
        );
    }

    /**
     * 获取详细信息
     *
     * @param int $id
     * @return array
     */
    public function detail($id)
    {
        /** @var AdminLogModel $localModel */
        $localModel = $this->model->find()->where(['id' => $id])
            ->select('detail,labels,record_table,detail2diff')
            ->one();
        if (!$localModel) {
            return app()->helper->arrayResult(1, '日志详情不存在');
        } elseif (!$localModel->detail2diff) {
            $localModel->detail = '<pre>' . $localModel->detail . '</pre>';
        } elseif ($localModel->labels) {
            $localModel->detail= app()->diff->changes2detail(
                $localModel->detail,
                json_decode($localModel->labels, true)
            );
        } else {
            $schema = $this->model->getDb()->getTableSchema($localModel->record_table);
            $columns = $schema ? $schema->columns : [];
            $localModel->detail= app()->diff->changes2detail(
                $localModel->detail,
                $this->model->getAllLabelsByComment((array) $columns)
            );
        }

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'detail' => $localModel->detail,
            ]
        );
    }

    /**
     * 设置操作日志模型
     *
     * @param \app\modules\admin\modelsLogModel $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 将一条管理员记录转化为列表数据
     *
     * @param AdminLogModel|array $item
     * @return array
     */
    protected function toListItem($item)
    {
        if ($item instanceof \app\modules\admin\modelsLogModel) {
            $item = $item->toArray();
        }
        $item['ip'] = long2ip($item['ip']);
        $item['title'] = $this->getTitle($item);
        return $item;
    }

    /**
     * 获取匹配字段
     *
     * @return array
     */
    public function getMatchFields()
    {
        $fields = [];
        foreach ($this->matchFields as $field) {
            $fields[$field] = $this->model->getColumnInfo($field, 'labelName');
        }
        return $fields;
    }

    /**
     * 获取表名
     *
     * @return array
     */
    protected function getTableLabels()
    {
        $data = $this->model->getTableLabels();
        $data['no_name'] = '无名';
        return $data;
    }

    /**
     * 获取日志标题
     *
     * @param array $item
     * @return string
     */
    protected function getTitle($item)
    {
        $labels = $this->getTableLabels();
        // xxx生成了首页
        if ('no_name' == $item['record_table']) {
            return sprintf(
                '<b>%s</b> <i>%s了</i>%s',
                $item['admin_name'],
                $this->getActionName($item),
                $item['record_name']
            );
        }
        // xxx生成了yyy#10000：zzz
        return sprintf(
            '<b>%s</b> <i>%s了</i>%s#%d%s',
            $item['admin_name'],
            $this->getActionName($item),
            $labels[$item['record_table']] ?? $item['record_table'],
            $item['record_id'],
            '' === $item['record_name'] ? '' : '：' . $item['record_name']
        );
    }

    /**
     * 获取操作名称
     *
     * @param array $item
     * @return string
     */
    protected function getActionName($item)
    {
        $localRequestRoute2name = $this->getRequestRoute2name();
        $action = explode('/', $item['request_route']);
        $action = end($action);
        if ($item['is_insert']) {
            return '添加';
        } elseif (isset($localRequestRoute2name[$key = $item['record_table'].'.'.$item['request_route']])
            || isset($localRequestRoute2name[$key = $item['record_table'].'.'.$action])
            || isset($localRequestRoute2name[$key = $item['request_route']])
        ) {
            return $localRequestRoute2name[$key];
        } else {
            return $localRequestRoute2name[$action] ?? $action;
        }
    }

    /**
     * 获取请求路由对应的操作名称
     *
     * @return array
     */
    protected function getRequestRoute2name()
    {
        if (null === $this->requestRoute2name) {
            $this->requestRoute2name = [];
            $data = SettingModel::getValue(
                'base',
                'request_route2name'
            );
            $data = array_filter(
                array_map('trim', explode("\n", $data))
            );
            foreach ($data as $item) {
                if (false !== strpos($item, '=>')) {
                    $item = array_map('trim', explode('=>', $item));
                    $this->requestRoute2name[$item[0]] = $item[1];
                }
            }

        }
        return $this->requestRoute2name ?: [];
    }
}
