<?php

namespace app\modules\base\components;

use app\base\Pagination;
use app\base\SitePlatform;
use ego\base\JsonResponseException;
use yii\db\Exception;

use app\modules\base\models\{
    DepartmentModel, MenuModel, RoleModel, AdminModel, AdminRelationModel,
    RolePrivilegeModel
};
use app\modules\base\components\MenuComponent;

/**
 * 角色组件
 * Class RoleComponent
 *
 * @package app\modules\base\components
 */
class RoleComponent extends Component
{
    const ROLE_MANAGE_KEY = 'base/role/list';
    const STATUS          = 'status';
    const STARTTIME       = 'startTime';
    const ENDTIME         = 'endTime';
    const CREATE_USER     = 'create_user';
    const DEPARTMENT_ID   = 'department_id';
    const UPDATE_TIME     = 'update_time';
    const PRIVILEGE_ID    = 'privilege_id';
    const ROLE_ID         = 'role_id';
    const SUCCESS         = 'success';
    const IS_PUBLIC       = 'is_public';
    const IS_DELETE       = 'is_delete';
    const SITE_CODE       = 'site_code';
    const MENULIST        = 'menuList';
    const NAME            = 'name';

    const STATUS_RANGE = [0, 1];
    const INSERT = 0;
    const UPDATE = 1;

    //错误信息
    private $errors = [];

    /**
     * 角色列表
     * @return array
     * @throws JsonResponseException
     */
    public function roleList()
    {
        $field = app()->request->get('field');
        $keyword = app()->request->get('keyword');
        $startTime = app()->request->get('update_time_start');
        $endTime = app()->request->get('update_time_end');
        $status = app()->request->get(self::STATUS);

        $siteCode = app()->request->get('site_code');
        if (empty($siteCode) || !SitePlatform::isCurrentSiteGroupPlatformSite($siteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        $params = [
            self::STATUS => $status,
            self::STARTTIME => $startTime ?: null,
            self::ENDTIME => $endTime ?: null,
        ];

        //获取子孙部门id
        $childNodes = $this->queryCreatorChildDepartmentNode();

        $query = RoleModel::find()->alias('r')
            ->leftJoin(AdminRelationModel::tableName() . ' as ar', 'r.id = ar.role_id');

        if (!app()->user->admin->is_super) {
            //获取超级管理员列表
            $superUser = AdminModel::find()
                ->select('username')
                ->where(['is_super' => '1'])
                ->asArray()
                ->all();
            $superUser = array_column($superUser, 'username');

            //查询自己创建的及子孙部门创建的角色列表
            $query = $query->where(
                    [
                        'r.' . self::CREATE_USER => app()->user->username,
                        'r.' . self::DEPARTMENT_ID => app()->user->admin->department_id,
                    ]
                )
                ->orFilterWhere(['in', 'r.' . self::DEPARTMENT_ID, $childNodes])
                ->andFilterWhere(['not in', 'r.' . self::CREATE_USER, $superUser]);
        } else {
            //查询所有的
            $query = $query->where('1 = 1');
        }

        //只查询当前site_code下的角色列表
        $query = $query->andFilterWhere(['r.' . self::SITE_CODE => $siteCode])
            ->andFilterWhere(['r.' . self::STATUS => $params[self::STATUS]])
            ->andFilterWhere(['like', 'r.' . $field, $keyword])
            ->andFilterWhere(
                ['between', 'r.' . self::UPDATE_TIME, $params[self::STARTTIME], $params[self::ENDTIME]]
            );

        //分页
        $total = $query->groupBy('r.id')->count();
        $pagination = Pagination::new($total);

        $data = $query
            ->leftJoin(RolePrivilegeModel::tableName() . ' as rp', 'r.id = rp.role_id')
            ->leftJoin(AdminModel::tableName() . ' as a', 'r.create_user = a.username')
            ->select(
                'r.id, r.name, r.create_user, a.realname as create_name, r.status, r.update_user, r.update_time,
                r.site_code, count(distinct rp.privilege_id) as privileges_num, count(distinct ar.admin_id) as user_num'
            )
            ->groupBy('r.id')
            ->orderBy('r.update_time DESC')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            [
                'list' => $data,
                'pagination' => [
                    $pagination->pageParam => (int)$pagination->page + 1,
                    $pagination->pageSizeParam => (int)$pagination->pageSize,
                    'totalCount' => (int)$pagination->totalCount
                ]
            ]
        );
    }

    /**
     * 获取角色创建人的所在部门的子孙部门节点id
     * @return array
     */
    private function queryCreatorChildDepartmentNode()
    {
        //获取子孙部门id
        $departmentId = app()->user->admin->department_id;
        $departmentList = DepartmentModel::departmentNodeCache();

        $departmentNode = explode(',', $departmentList[$departmentId]['node'] ?? '');
        if (empty($departmentNode)) {
            return [];
        }
        $firstNode = array_shift($departmentNode);

        // 树状结构 获取同个一级部门下的所有部门，缩小范围
        $departmentList = app()->arrayTree->array2tree($departmentList, true)[$firstNode] ?? [];
        if (empty($departmentList)) {
            return [];
        }

        // 还原成并列结构
        $departmentList = app()->arrayTree->tree2array([$firstNode => $departmentList]);

        $childNodes = [];
        foreach ($departmentList as $val) {
            $node = explode(',', $val['node']);
            if (\in_array($departmentId, $node, false) && (int)$val['id'] !== $departmentId) {
                $childNodes[] = $val['id'];
            }
        }

        return $childNodes;
    }


    /**
     * 角色可拥有的所有权限
     * @return array
     */
    public function availablePrivileges()
    {
        $menuList = MenuModel::allocationMenuList();

        if (app()->user->admin->is_super) {
            // 超级管理员
            $isSuperAdmin = true;
            $menuList = app()->arrayTree->array2tree($menuList);

            $sites = array_keys(app()->params['sites']);
            $sitePriv = [];
            foreach ($sites as $item) {
                $sitePriv[] = [
                    self::SITE_CODE => $item,
                    self::MENULIST => $menuList,
                ];
            }
        } else {
            // 普通管理员
            $isSuperAdmin = false;

            $sitePriv = $this->getGeneralRoleMenu(app()->user->id);

            // 过滤出只拥有的菜单权限
            foreach ($sitePriv as &$val) {
                $val[self::PRIVILEGE_ID] = array_unique(explode(',', $val[self::PRIVILEGE_ID]));
                $val[self::MENULIST] = array_filter($menuList, function ($v) use ($val) {
                    return \in_array($v['id'], $val[self::PRIVILEGE_ID], false) || $v[self::IS_PUBLIC];
                });
                $val[self::MENULIST] = app()->arrayTree->array2tree($val[self::MENULIST]);
                unset($val[self::PRIVILEGE_ID]);
            }
        }

        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            [
                'sitePrivileges' => array_values($sitePriv),
                'isSuperAdmin' => $isSuperAdmin
            ]
        );
    }

    /**
     * 获取普通用户的角色菜单信息
     *
     * @param int $userId
     *
     * @return array
     */
    private function getGeneralRoleMenu(int $userId)
    {
        $pResult = RolePrivilegeModel::find()->alias('p')
            ->select('r.site_code, GROUP_CONCAT(p.privilege_id) AS privilege_id')
            ->leftJoin(RoleModel::tableName() . ' as r', 'p.role_id = r.id')
            ->leftJoin(AdminRelationModel::tableName() . ' as a', 'a.role_id = r.id')
            ->where([
                'a.admin_id' => $userId,
                'r.status' => RoleModel::STATUS_ENABLED,
                'r.is_delete' => RoleModel::NOT_DELETE
            ])->groupBy('r.site_code')
            ->asArray()
            ->all();

        return $pResult ? array_column($pResult, null, 'site_code') : [];
    }

    /**
     * 添加角色
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function roleAdd()
    {
        $data = app()->request->post();
        $this->checkRoleData(self::INSERT, $data);

        $errorCount = 0;
        $siteCodes = (array)$data[self::SITE_CODE];
        $privilegeIds = (array)$data[self::PRIVILEGE_ID];
        unset($data[self::PRIVILEGE_ID]);
        foreach ($siteCodes as $siteCode) {
            $data[self::SITE_CODE] = $siteCode;
            $dbTransaction = app()->db->beginTransaction();
            try {
                if (!$this->saveRoleData($data, $privilegeIds)) {
                    $errorCount++;
                    throw new Exception('保存失败');
                }
                $dbTransaction->commit();
            } catch (Exception $e) {
                $dbTransaction->rollBack();
            }
        }

        if ($errorCount) {
            if (\count($this->errors) === 1) {
                $msg = $this->errors[0]['message'];
            } else {
                $msg = '以下站点角色添加失败：' . implode('、', array_column($this->errors, self::SITE_CODE));
            }
            return app()->helper->arrayResult($this->codeFail, $msg, $this->errors);
        }

        return app()->helper->arrayResult($this->codeSuccess, '添加成功');
    }

    /**
     * 编辑角色
     * @return array
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     */
    public function roleEdit()
    {
        $data = app()->request->post();
        $this->checkRoleData(self::UPDATE, $data);

        $roleId = (int)$data['id'];
        $privilegeIds = (array)$data[self::PRIVILEGE_ID];
        unset($data[self::PRIVILEGE_ID]);
        if (!$model = RoleModel::findOne($roleId)) {
            throw new JsonResponseException($this->codeFail, '角色不存在');
        }

        if ($model->create_user !== app()->user->username) {
            throw new JsonResponseException($this->codeFail, '只能编辑自己创建的角色');
        }

        $dbTransaction = app()->db->beginTransaction();
        try {
            if (!$this->saveRoleData($data, $privilegeIds, $model)) {
                throw new Exception('保存失败');
            }
            $dbTransaction->commit();
        } catch (Exception $e) {
            $dbTransaction->rollBack();
            return app()->helper->arrayResult(
                $this->codeFail,
                $this->errors ? $this->errors[0]['message'] : '保存失败',
                $this->errors
            );
        }

        return app()->helper->arrayResult(
            0,
            '修改成功',
            $model
        );
    }

    /**
     * 保存角色数据
     * @param array[] $data 数据
     * @param array $privilegeIds 菜单ID
     * @param null|\app\modules\base\models\RoleModel $model
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    private function saveRoleData($data, $privilegeIds, $model = null)
    {
        $model = $model ?? new RoleModel();
        $id = $model->id;//用来判断是新增还是编辑
        unset($data['id']);
        $model->load(array_map('trim', $data), '');

        if (!$id) {
            //新增处记录update属性，用于list查询时排序
            $model->update_user = app()->user->username;
            $model->update_time = time();
        }

        if (!$model->save(true)) {
            $this->errors[] = [
                self::SITE_CODE => $model->site_code,
                'message' => $model->flattenErrors(', ')
            ];
            return false;
        }

        if ($id) {
            //编辑的话则删除RolePrivilegeModel表数据
            RolePrivilegeModel::deleteAll([self::ROLE_ID => $id]);
        }

        if (!empty($privilegeIds)) {
            $rolePrivilege = [];
            foreach ($privilegeIds as $value) {
                if ($value) {
                    $rolePrivilege[] = [$model->id, $value];
                }
            }
            $colomns = [self::ROLE_ID, self::PRIVILEGE_ID];
            if (!RolePrivilegeModel::insertAll($colomns, $rolePrivilege)) {
                $this->errors[] = [
                    self::SITE_CODE => $model->site_code,
                    'message' => '角色“' . $data[self::NAME] . '”权限关联关系添加失败'
                ];
                return false;
            }
        }

        return true;
    }

    /**
     * 添加角色数据检查
     * @param int $type 检查类型，0-新增，1-编辑
     * @param array $data 角色数据
     * @throws JsonResponseException
     */
    private function checkRoleData($type, &$data)
    {
        $this->checkEditAuth();

        if (!$data || !isset($data[self::PRIVILEGE_ID]) || !isset($data[self::STATUS]) || empty($data[self::NAME])
            || (self::INSERT === $type && empty($data[self::SITE_CODE]))
            || (self::UPDATE === $type && empty($data['id']))) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        if (!\in_array((int)$data[self::STATUS], self::STATUS_RANGE, true)) {
            throw new JsonResponseException($this->codeFail, '角色状态参数超出范围');
        }

        $data[self::DEPARTMENT_ID] = app()->user->admin->department_id;

        //string转成array
        $data[self::PRIVILEGE_ID] = array_unique(explode(',', $data[self::PRIVILEGE_ID]));

        if (self::INSERT === $type) {
            //site_code不能为空，而privilege_id可为空，即取消权限
            $data[self::SITE_CODE] = array_unique(explode(',', $data[self::SITE_CODE]));
            if (empty($data[self::SITE_CODE])) {
                throw new JsonResponseException($this->codeFail, '参数格式不正确');
            }
        }

        if (\in_array($this->findRoleManageRouteId(), $data[self::PRIVILEGE_ID], true)) {
            $data['need_auto_create'] = 1;
        }
    }

    /**
     * 检查是否有编辑权限
     * @throws JsonResponseException
     */
    private function checkEditAuth()
    {
        if (!app()->user->admin->is_super && !app()->user->admin->is_leader) {
            throw new JsonResponseException($this->codeFail, '无权操作，只有管理员或部门Leader才有权限');
        }
    }

    /**
     * 查找角色管理菜单的id
     * @return int|mixed
     */
    private function findRoleManageRouteId()
    {
        $menu = MenuModel::find()
            ->select('id')
            ->where(['route' => static::ROLE_MANAGE_KEY])
            ->asArray()
            ->one();

        return $menu ? (int)$menu['id'] : 0;
    }

    /**
     * 角色详情
     * @return array
     */
    public function roleInfo()
    {
        $roleId = app()->request->get('id');

        $roleInfo = RoleModel::find()
            ->select('id, name, department_id, site_code, status')
            ->where(['id' => $roleId])
            ->asArray()
            ->one();
        if (empty($roleInfo)) {
            return app()->helper->arrayResult(1, '角色信息不存在');
        }

        $privileges = RolePrivilegeModel::find()
            ->select(self::PRIVILEGE_ID)
            ->where([self::ROLE_ID => $roleId])
            ->asArray()
            ->column();
        $roleInfo[self::PRIVILEGE_ID] = $privileges;

        $menuList = MenuModel::menuList();
        $privilegeRange = RoleModel::find()
            ->select('id')
            ->where([
                self::CREATE_USER => app()->user->username,
                self::DEPARTMENT_ID => app()->user->admin->department_id,
                self::SITE_CODE => $roleInfo[self::SITE_CODE]
            ])
            ->asArray()
            ->one();
        $privilegeRange[self::PRIVILEGE_ID] = RolePrivilegeModel::find()
            ->select(self::PRIVILEGE_ID)
            ->where([self::ROLE_ID => $privilegeRange['id']])
            ->asArray()
            ->column();

        $roleInfo[self::MENULIST] = array_filter($menuList, function ($v) use ($privilegeRange) {
            return (
                \in_array($v['id'], $privilegeRange[self::PRIVILEGE_ID], false)
                && $v[self::STATUS]
                && !$v[self::IS_PUBLIC]
            );
        });
        $roleInfo[self::MENULIST] = app()->arrayTree->array2tree($roleInfo[self::MENULIST]);

        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            $roleInfo
        );
    }

    /**
     * 角色下用户列表
     * @return array
     */
    public function userList()
    {
        $roleId = app()->request->get('id', 0);

        $userList = AdminRelationModel::find()->alias('ar')
            ->leftJoin(AdminModel::tableName() . ' as a', 'ar.admin_id = a.id')
            ->select('a.id, a.department_id, a.username, a.realname, a.is_leader, a.is_super, ar.create_time')
            ->where(['ar.role_id' => $roleId])
            ->all();

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            $userList
        );
    }
}
