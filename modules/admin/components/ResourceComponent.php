<?php

namespace app\modules\admin\components;

use app\base\Pagination;
use app\modules\admin\models\ResourceModel;
use app\components\CURDComponentTrait;
use app\modules\admin\exception\ResourceException;
use app\modules\base\models\AdminModel;

/**
 * 素材资源组件
 */
class ResourceComponent extends Component
{
    use CURDComponentTrait;

    public function init()
    {
        parent::init();
        $this->modelClass = ResourceModel::className();
    }

    /**
     * 获取
     *
     * @param int $id
     * @return array
     */
    public function info($id)
    {
        $model = ResourceModel::findOne($id);
        $model->includeS3DomainUrl();
        if (!$model) {
            return app()->helper->arrayResult(1, $this->dataNotFind);
        } else {
            return app()->helper->arrayResult(0, 'success', $model);
        }
    }

    /**
     * 个人创建的列表
     *
     * @param int  $id
     * @return array
     */
    public function lists($id = 0)
    {
        return $this->getList($id, false);
    }

    /**
     * 搜索个人创建的列表
     *
     * @param string $search
     * @return array
     */
    public function search($search)
    {
        return $this->getList(-1, $search);
    }

    /**
     * 全部的素材列表
     *
     * @param int  $id
     * @return array
     */
    public function listAll($id = 0)
    {
        return $this->getList($id, false, false);
    }

    /**
     * 全部目录列表
     * @param boolean $onlyShowMeCreate
     * @return array
     */
    public function folderTree($onlyShowMeCreate=true)
    {
        $conditions = [
            'is_delete' => 0,
            'type'      => ResourceModel::RESOURCE_TYPE_FOLDER
        ];
        $query = ResourceModel::find()->select('id, name, node, parent_id')->where($conditions);
        if ($onlyShowMeCreate) {
            $query->andWhere(['create_user' => app()->user->username]);
        }

        $folderList = [];
        $resList = $query->orderBy('id desc')->indexBy('id')->asArray()->all();
        if (!empty($resList)) {
            foreach ($resList as $res) {
                $parentId = (int)$res['parent_id'];
                $nodeIds = explode(',', $res['node']);
                $hasAllParent = true;
                foreach ($nodeIds as $nodeId) {
                    $nodeId = (int)$nodeId;
                    if ((0 !== $nodeId) && !isset($resList[$nodeId]))  {
                        $hasAllParent = false;
                        break;
                    }
                }

                if ($hasAllParent) {
                    $folderList[] = [
                        'id' => $res['id'],
                        'parent' => (0 === $parentId) ? '#' : $parentId,
                        'text' => $res['name']
                    ];
                }
            }
        }

        return app()->helper->arrayResult(0, 'success', $folderList);
    }

    /**
     * 搜索全部的素材列表
     *
     * @param string $search
     * @return array
     */
    public function searchAll($search)
    {
        return $this->getList(-1, $search, false);
    }

    /**
     * 获取列表
     *
     * @param string|bool $id   通过当前的目录ID获取
     * @param string|bool $search  按搜索词获取
     * @param bool $onlyShowMeCreate  是否筛选需要自己创建的素材
     * @return array
     */
    private function getList($id = -1, $search = false, $onlyShowMeCreate = true)
    {
        $where['is_delete'] = 0;
        $query = ResourceModel::find()->where($where);
        $id >= 0 && ($query->andWhere(['parent_id' => $id]));
        $search && ($query->andWhere(['like', 'name', $search]));

        if ($onlyShowMeCreate) {
            $query->andWhere(['create_user' => app()->user->username]);
        }
        return $this->queryList($query);
    }

    /**
     * 获取这个ID的兄弟list
     *
     * @param int $id
     * @return array
     */
    public function listsBrother($id = 0)
    {
        return $this->lists(ResourceModel::findOne($id)->parent_id);
    }

    /**
     * 面包屑
     *
     * @param int $dirId 当前目录id
     * @return array
     */
    public function crumbs($dirId = 0)
    {
        $resource = ResourceModel::findOne($dirId);
        if (!$resource) {
            return app()->helper->arrayResult(1, '面包屑不存在');
        }
        return app()->helper->arrayResult(0, 'success', $resource->getCrumbs());
    }

    /**
     * 移动资源到一个资源目录
     *
     * @param [] $resourceIds
     * @param int $toParent
     * @return array
     */
    public function moveResource($resourceIds, $toParent)
    {
        try {
            $this->moveResourceImp($resourceIds, $toParent);
        } catch (ResourceException $e) {
            return app()->helper->arrayResult(1, $e->getMessage());
        }
        return app()->helper->arrayResult(0, 'success', ['toParent' => $toParent]);
    }

    /**
     * 移动资源到一个资源目录
     *
     * @param [] $resourceIds
     * @param int $toParent
     * @return bool
     * @throws ResourceException
     */
    public function moveResourceImp($resourceIds, $toParent)
    {
        foreach ($resourceIds as $resourceId) {
            $resource = ResourceModel::findOne($resourceId);
            if ($resource->parent_id == $toParent) {
                throw new ResourceException('不能移动到原来目录');
            }
            $resource->parent_id = $toParent;
            if (!$resource->save()) {
                 throw new ResourceException($resource->flattenErrors(', '));
            }
        }
        return true;
    }

    /**
     * 查询获取资源列表
     * @param $query
     * @return array
     */
    private function queryList($query)
    {
        $pageSize = app()->request->get('pageSize',0);
        if($pageSize){
            $pagination = Pagination::new($query->count());
            $data = $query
                ->orderBy('type,id desc')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()->all();
            $page['pageNo'] = $pagination->page + 1;
            $page['pageSize'] = $pagination->pageSize;
            $page['totalCount'] = $pagination->totalCount;
            $result['pagination'] = $page;
        }else{
            $data = $query
                ->orderBy('type,id desc')
                ->asArray()->all();
        }
        $data = $this->addDomainToUrl($data);
        $result['list'] = $data;
        return app()->helper->arrayResult(0, 'success', $result);
    }

    /**
     * 遍历数组，增加每个url和thumbnail_url的域名
     *
     * @param $data
     * @return array
     */
    private function addDomainToUrl($data)
    {
        return array_map(function ($item) {
            $item['url'] && ($item['url'] = app()->params['s3UploadsConf']['staticDomain'] . $item['url']);
            $item['thumbnail_url'] && ($item['thumbnail_url'] = app()->params['s3UploadsConf']['staticDomain']
                . $item['thumbnail_url']);
            return $item;
        }, $data);
    }
}
