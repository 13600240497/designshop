<?php

namespace app\modules\base\components;

use ego\base\JsonResponseException;
use yii\db\Exception;

use app\modules\base\models\{
    DepartmentModel, DepartmentRelationModel
};

/**
 * 部门管理
 * Class DepartmentComponent
 *
 * @package app\modules\base\components
 */
class DepartmentComponent extends Component
{
    private $departmentList = [];
    
    private $department = [];
    
    private $departmentOutline = [];
    
    private $siteConfigured = [];
    
    private $availableSite = [];
    
    const DEPARTMENT_ID = 'department_id';
    const DEPARTMENTIDS = 'departmentIds';
    const SUCCESS       = 'success';
    const UPDATE_USER   = 'update_user';
    const SITE_CODE     = 'site_code';
    const SELECTED      = 'selected';
    const DEPARTMENTID  = 'departmentId';
    
    /**
     * @file  : departmentList
     * @brief : 部门列表 树形结构 不分页
     * @return:  array
     * @throws JsonResponseException
     */
    public function departmentList()
    {
        $field = app()->request->get('field');
        $keyword = app()->request->get('keyword');
        $departmentIds = app()->request->get(self::DEPARTMENT_ID); //[]
        
        $params[ self::DEPARTMENTIDS ] = $departmentIds;
        
        if ($field && $keyword) {
            $params[ $field ] = $keyword;
        }
        
        // 是超级管理员，则允许空 departmentIds，否则只允许查询本部门及下属部门
        if (!app()->user->admin->is_super && empty($params[ self::DEPARTMENTIDS ])) {
            throw new JsonResponseException(1, '部门分级id错误');
        }
        
        $this->department2Tree();
        $this->departmentFilter($params);
        $this->queryDepartmentRelationInfo();
        
        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            [
                'list' => $this->departmentList
            ]
        );
    }
    
    /**
     * 部门树结构
     *
     */
    private function department2Tree()
    {
        $departments = DepartmentModel::find()
            ->select('id,parent_id,name,sys_update_time,update_user')
            ->asArray()
            ->all();
        $data = array_combine(array_column($departments, 'id'), $departments);
        $this->departmentList = app()->arrayTree->array2tree($data, true);
    }
    
    /**
     * 部门过滤/筛选
     *
     * @param $params
     */
    private function departmentFilter($params)
    {
        $departments = $this->departmentList;
        if (!empty($params[ self::DEPARTMENTIDS ])) {
            foreach ($params[ self::DEPARTMENTIDS ] as $val) {
                $tmp = $departments['children'] ?? $departments;
                $departments = $tmp[ $val ];
            }
            $departments = [$departments];
        }
        
        $departments = app()->arrayTree->tree2array($departments, true);
        $departments = array_filter($departments, function ($v) use ($params) {
            if (isset($params['name']) && $params['name'] && (false === strpos($v['name'], $params['name']))) {
                return false;
            }
            if (isset($params[ self::UPDATE_USER ]) && $params[ self::UPDATE_USER ]
                && (false === strpos($v[ self::UPDATE_USER ], $params[ self::UPDATE_USER ]))
            ) {
                return false;
            }
            
            return true;
        });
        $this->departmentList = $departments;
    }
    
    /**
     * 查询 部门与站点、分类扩展关系
     */
    private function queryDepartmentRelationInfo()
    {
        $result = DepartmentRelationModel::find()
            ->select('department_id, site_code')
            ->where(['in', self::DEPARTMENT_ID, array_keys($this->departmentList)])
            ->asArray()
            ->all();
        
        $info = [];
        foreach ($result as $value) {
            $info[ $value[ self::DEPARTMENT_ID ] ][ self::SITE_CODE ][] = $value[ self::SITE_CODE ];
        }
        
        foreach ($this->departmentList as $key => &$val) {
            $val['sys_update_time'] = strtotime($val['sys_update_time']);
            $val += ($info[ $key ] ?? [self::SITE_CODE => []]);
        }
        
        $this->departmentList = app()->arrayTree->array2tree($this->departmentList);
    }
    
    /**
     * 部门分级组件
     */
    public function departmentOutlineWidget()
    {
        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            $this->outlineWidget()
        );
    }
    
    /**
     * 部门分级组件
     *
     */
    private function outlineWidget()
    {
        $data = DepartmentModel::departmentNodeCache();
        $departmentId = app()->user->admin->is_super ? 0 : app()->user->admin->department_id;
        
        $department_ids = [];
        if ($departmentId && isset($data[ $departmentId ])) {
            $department_ids = $nodes = explode(',', $data[ $departmentId ]['node']);
            
            $data = app()->arrayTree->array2tree($data, true);
            if ($firstNode = array_shift($nodes)) {
                $data = app()->arrayTree->tree2array([$firstNode => $data[ $firstNode ]], true);
                $this->filterSelectedNodes($nodes, $data);
            }
        }
        
        $data = app()->arrayTree->array2tree($data);
        $this->departmentOutline = $data;
        
        $department_ids = array_map(function ($v) {
            return (int) $v;
        }, $department_ids);
        
        return ['outline' => $this->departmentOutline, 'department_ids' => $department_ids];
    }
    
    /**
     * 过滤角色权限外的节点
     *
     * @param $nodes
     * @param $data
     */
    private function filterSelectedNodes($nodes, &$data)
    {
        foreach ($nodes as $key => $id) {
            foreach ($data as &$val) {
                $val[ self::SELECTED ] = true;
                if ($val['node'] != $val['id']) {
                    $node = explode(',', $val['node']);
                    if ((count($node) > $key + 1) && !in_array($id, $node)) {
                        $val[ self::SELECTED ] = false;
                    }
                }
            }
            unset($val);
            $data = array_filter($data, function ($v) {
                return ($v[ self::SELECTED ]);
            });
        }
    }
    
    /**
     * @file  : departmentInfo
     * @brief : 部门信息
     * @return:  array
     * @throws JsonResponseException
     */
    public function departmentInfo()
    {
        $departmentId = app()->request->get('id');
        $params[ self::DEPARTMENTID ] = $departmentId;
        $this->department = DepartmentModel::getById($params[ self::DEPARTMENTID ]);
        if (empty($this->department)) {
            throw new JsonResponseException(2, '部门信息不存在');
        }
        $this->department = $this->department->toArray();
        
        $this->availableSite($params[ self::DEPARTMENTID ]);
        
        $site = $this->siteFilter();
        
        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            [
                'department' => $this->department,
                'site'       => array_values($site),
            ]
        );
    }
    
    /**
     * 读取站点、分类
     *
     * @param $departmentId
     */
    private function availableSite($departmentId)
    {
        // 是否一级部门
        $isTopLevelDepartment = false;
        if (empty($this->department['parent_id'])) {
            $isTopLevelDepartment = true;
        }
        
        $this->siteConfigured = DepartmentRelationModel::getSiteByDepartmentId($departmentId);
        $this->siteConfigured = array_combine(
            array_column($this->siteConfigured, self::SITE_CODE),
            $this->siteConfigured
        );
        
        // 编辑下级， 一级部门范畴则读取全部站点及分类，二级以下部门范畴则读取上级部门
        $siteRange = [];
        if ($isTopLevelDepartment) {
            $sites = array_keys(app()->params['sites']); //获取全部站点(配置文件中获取)
            foreach ($sites as $val) {
                $siteRange[] = [
                    self::SITE_CODE => $val
                ];
            }
        } else {
            $siteRange = DepartmentRelationModel::getSiteByDepartmentId($this->department['parent_id']);
        }
        $this->availableSite = $siteRange;
    }
    
    /**
     * 部门的站点权限过滤
     *
     * @return mixed
     */
    private function siteFilter()
    {
        foreach ($this->availableSite as &$site) {
            // 已选中的站点
            $site[ self::SELECTED ] = false;
            if (!empty($this->siteConfigured) &&
                (false !== array_key_exists($site[ self::SITE_CODE ], $this->siteConfigured))
            ) {
                $site[ self::SELECTED ] = true;
            }
        }
        
        return $this->availableSite;
    }
    
    /**
     * @file  : departmentEdit
     * @brief : 编辑部门，多站点批量更新
     *
     * @param       $id
     * @param array $data
     *
     * @return:  array
     * @throws Exception
     * @throws JsonResponseException
     */
    public function departmentEdit($id, array $data)
    {
        if (!$data || empty($data['data'])) {
            throw new JsonResponseException(1, '更新数据不能为空');
        } elseif (!DepartmentModel::getById($id)) {
            throw new JsonResponseException(2, '部门不存在');
        }
        
        //非超级管理员，自己不能编辑自己的部门信息
        if (!app()->user->admin->is_super && app()->user->admin->department_id == $id) {
            throw new JsonResponseException(3, '不能编辑本人信息');
        }
        
        $site = $data['data'];
        unset($data['id'], $data['data']);
        
        //查询子孙节点
        $subDepartmentIds = $this->getSubDepartmentIds($id);
        
        $dbTransaction = app()->db->beginTransaction();
        try {
            // 1. 先删除旧的数据
            if (DepartmentRelationModel::findOne([self::DEPARTMENT_ID => $id]) &&
                !DepartmentRelationModel::deleteAll([self::DEPARTMENT_ID => $id])
            ) {
                $dbTransaction->rollBack();
                throw new JsonResponseException(3, '编辑失败');
            }
            
            // 2. 更新
            $data = $cfgedSite = [];
            foreach ($site as $val) {
                if (empty($val[ self::SITE_CODE ])) {
                    continue;
                }
                
                $cfgedSite[ $val[ self::SITE_CODE ] ] = ''; // 已配置的站点、分类，用于更新子孙部门
                
                $data[] = [
                    $id,
                    $val[ self::SITE_CODE ],
                    time()
                ];
            }
            
            $colomns = [self::DEPARTMENT_ID, self::SITE_CODE, 'create_time'];
            if ($data && !DepartmentRelationModel::insertAll($colomns, $data)) {
                $dbTransaction->rollBack();
                throw new JsonResponseException(3, '编辑失败');
            }
            
            // 删除子孙部门的站点（不包含在父部门的站点）
            $tmp = [];
            foreach (array_keys($cfgedSite) as $val) {
                $tmp[] = "'$val'";
            }
            $condition = "department_id IN (" . implode(',', $subDepartmentIds) .
                ") AND site_code NOT IN (" . implode(',', $tmp) . ")";
            DepartmentRelationModel::deleteAll($condition);
            
            $dbTransaction->commit();
        } catch (Exception $e) {
            $dbTransaction->rollBack();
            throw new JsonResponseException(3, $e->getMessage() ?: '编辑失败');
        }
        
        return app()->helper->arrayResult(
            0,
            '编辑成功',
            null
        );
    }
    
    /**
     * 查询子孙节点
     *
     * @param $id
     *
     * @return array
     */
    private function getSubDepartmentIds($id)
    {
        $departmentLists = DepartmentModel::departmentNodeCache();
        $subDepartmentIds = [];
        if (!empty($departmentLists) && is_array($departmentLists)) {
            foreach ($departmentLists as $val) {
                $node = explode(',', $val['node']);
                if ($id != $node && in_array($id, $node)) {
                    array_push($subDepartmentIds, $val['id']);
                }
            }
        }
        
        return $subDepartmentIds;
    }
}
