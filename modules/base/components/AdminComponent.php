<?php

namespace app\modules\base\components;

use app\modules\base\models\{
    DepartmentModel, AdminModel, AdminRelationModel, DepartmentRelationModel, RoleModel, RolePrivilegeModel
};
use app\modules\base\models\AdminSitePrivilegeModel;
use app\base\{
    Pagination
};
use app\base\SitePlatform;
use ego\base\JsonResponseException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use linslin\yii2\curl;

/**
 * 管理员组件
 * @property \app\modules\base\components\AdminSitePrivilegeComponent $AdminSitePrivilegeComponent
 * @package app\modules\base\components
 */
class AdminComponent extends Component
{
    public $adminInfo;

    private $departmentList = [];

    private $adminList = [];

    private $editedUserInfo = [];

    private $total = 0;

    private $errors = [];

    const FIELD = 'field';
    const KEYWORD = 'keyword';
    const DEPARTMENTIDS = 'departmentIds';
    const IS_SUPER = 'is_super';
    const IS_LEADER = 'is_leader';
    const STATUS = 'status';
    const DEPARTMENT_ID = 'department_id';
    const DEPARTMENT_NAME = 'department_name';
    const ADMIN_ID = 'admin_id';
    const ROLE_ID = 'role_id';
    const SITE_CODE = 'site_code';
    const CREATE_USER = 'create_user';
    const USERNAME = 'username';
    const ROLES = 'roles';
    const SITES = 'sites';
    const NEED_AUTO_CREATE = 'need_auto_create';
    const ROLE_NAME = 'role_name';

    /**
     * @inheritdoc
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $this->adminInfo = AdminModel::getById(app()->user->id);
        $this->adminInfo = $this->adminInfo ? ArrayHelper::toArray($this->adminInfo) : [];
    }

    /**
     * 管理员列表
     * @return array
     * @throws JsonResponseException
     */
    public function adminList()
    {
        $params = [
            self::FIELD => app()->request->get(self::FIELD),
            self::KEYWORD => app()->request->get(self::KEYWORD),
            self::DEPARTMENTIDS => app()->request->get(self::DEPARTMENT_ID) ?? [], // []
            self::IS_SUPER => app()->request->get(self::IS_SUPER),
            self::STATUS => app()->request->get(self::STATUS)
        ];

        //是超级管理员，则允许空 departmentIds，否则只允许查询本部门及下属部门
        if (!$this->adminInfo[self::IS_SUPER] && empty($params[self::DEPARTMENTIDS])) {
            throw new JsonResponseException(1, '部门分级id错误');
        }

        //部门数据
        $this->departmentList = DepartmentModel::departmentNodeCache();

        $this->queryAdminList($params);

        //部门、站点角色
        $this->formatAdminList();

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list' => $this->adminList,
                'total' => $this->total,
            ]
        );
    }

    /**
     * 查询列表数据
     * @param $params
     */
    private function queryAdminList($params)
    {
        //过滤部门
        if (!empty($params[self::DEPARTMENTIDS])) {
            $departmentListFiltered = array_filter($this->departmentList, function ($v) use ($params) {
                return empty(array_diff($params[self::DEPARTMENTIDS], explode(',', $v['node'])));
            });
            $params[self::DEPARTMENTIDS] = array_keys($departmentListFiltered);
        }

        $query = AdminModel::find()
            ->filterWhere(['in', self::DEPARTMENT_ID, $params[self::DEPARTMENTIDS]])
            ->andFilterWhere([
                self::IS_SUPER => $params[self::IS_SUPER],
                self::STATUS => $params[self::STATUS],
            ])
            ->andFilterWhere(['like', $params[self::FIELD], $params[self::KEYWORD]]);

        //分页
        $this->total = $query->count();
        $pagination = Pagination::new($this->total);
        $this->adminList = $query->orderBy('create_time DESC, username')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();

        // ZF站点显示渠道权限
//        if (isZufulSite()) {
            $websiteCode = SitePlatform::getCurrentSiteGroupCode();
            $userIds = array_column($this->adminList, 'id');
            $privilegeModels = AdminSitePrivilegeModel::findByUserIdsAndWebsiteCode($userIds, $websiteCode);

            $privilegeInfoList = $privilegeModels ? array_column(ArrayHelper::toArray($privilegeModels), NULL, 'user_id') : [];
            $allPermissions = $this->AdminSitePrivilegeComponent->getSiteAllPermissions($websiteCode);
            foreach ($this->adminList as &$_adminInfo) {
                $_userId = $_adminInfo['id'];
                if (isset($privilegeInfoList[$_userId])) {
                    $_adminInfo['home_permissions'] = $this->getSitePermissions($allPermissions, $privilegeInfoList[$_userId]['home_permissions']);
                    $_adminInfo['special_permissions'] = $this->getSitePermissions($allPermissions, $privilegeInfoList[$_userId]['special_permissions']);
                } else {
                    $_adminInfo['home_permissions'] = '';
                    $_adminInfo['special_permissions'] = '';
                }
            }
//        }
    }

    /**
     * 获取指定位置站点权限
     * @param array $allPermissions
     * @param array $permissions
     * @return array
     */
    private function getSitePermissions($allPermissions, $permissions)
    {
        if (!empty($permissions)) {
            $_permissionList = json_decode($permissions);
            if (!empty($_permissionList)) {
                $_hasPermissions = [];
                foreach ($_permissionList as $_code) {
                    $_hasPermissions[$_code] = $allPermissions[$_code] ?? '';
                }
                return $_hasPermissions;
            }
        }
        return [];
    }

    /**
     * 处理列表数据
     */
    private function formatAdminList()
    {
        $this->addDepartmentName(); //用户列表添加部门名称
        $userIds = array_column($this->adminList, 'id');

        //用户-角色
        $result = AdminRelationModel::find()
            ->select('admin_id, role_id, admin_relation.site_code, role.name')
            ->innerJoin('role', 'role.id = admin_relation.role_id')
            ->where([self::ADMIN_ID => $userIds])
            ->asArray()
            ->all();
        $adminRoles = [];
        foreach ($result as $v) {
            if (!$v[self::ROLE_ID]) {
                continue;
            }
            $adminRoles[$v[self::ADMIN_ID]][$v[self::SITE_CODE]][self::SITE_CODE] = $v[self::SITE_CODE];
            $adminRoles[$v[self::ADMIN_ID]][$v[self::SITE_CODE]]['name'][] = $v['name'];
        }

        foreach ($this->adminList as & $value) {
            if (false !== array_key_exists($value['id'], $adminRoles)) {
                $value['siteRole'] = array_values($adminRoles[$value['id']]);
            }
        }
    }

    /**
     * 为用户列表添加部门名称
     */
    private function addDepartmentName()
    {
        foreach ($this->adminList as &$val) {
            //部门名称
            $val[self::DEPARTMENT_NAME] = [];
            if (isset($this->departmentList[$val[self::DEPARTMENT_ID]])) {
                $nodes = explode(',', $this->departmentList[$val[self::DEPARTMENT_ID]]['node']);

                foreach ($nodes as $node) {
                    if (isset($this->departmentList[$node])) {
                        $val[self::DEPARTMENT_NAME][] = $this->departmentList[$node]['name'];
                    }
                }
            }
            $val[self::DEPARTMENT_NAME] = implode('-', $val[self::DEPARTMENT_NAME]);
        }
    }

    /**
     * 管理员详情
     * @return array
     * @throws JsonResponseException
     */
    public function getAdminInfo()
    {
        $adminId = app()->request->get('id');

        if (empty($adminId)) {
            throw new JsonResponseException($this->codeFail, '传参错误：用户id错误');
        }

        $this->editedUserInfo = AdminModel::find()
            ->select('id, department_id, username, realname, status, is_super, is_leader')
            ->where(['id' => $adminId])
            ->asArray()
            ->one();
        if (empty($this->editedUserInfo)) {
            throw new JsonResponseException($this->codeFail, '用户信息不存在');
        }

        $this->departmentList = DepartmentModel::departmentNodeCache();
        $department = $this->departmentList[$this->editedUserInfo[self::DEPARTMENT_ID]] ?? [];
        if (empty($department)) {
            throw new JsonResponseException($this->codeFail, '用户部门信息为空');
        }

        $nodes = explode(',', $department['node']);
        $this->editedUserInfo[self::DEPARTMENT_NAME] = [];
        foreach ($nodes as $val) {
            $this->editedUserInfo[self::DEPARTMENT_NAME][] = $this->departmentList[$val]['name'] ?? '';
        }
        $this->editedUserInfo[self::DEPARTMENT_NAME] = implode('-', $this->editedUserInfo[self::DEPARTMENT_NAME]);

        $siteRange = $this->getUserSiteRange();

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'info' => $this->editedUserInfo,
                'site' => array_values($siteRange)
            ]
        );
    }

    /**
     * 读取用户的站点、角色信息 范畴及选中项
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getUserSiteRange()
    {
        //超级管理员不需要编辑站点分类角色信息
        return 1 === (int)$this->editedUserInfo[self::IS_SUPER] ? [] : $this->getEditableSiteRange();
    }

    /**
     * 获取可编辑的站点、角色信息
     * @return array
     */
    private function getEditableSiteRange()
    {
        if ($this->adminInfo[self::IS_SUPER]) {
            //if 当前用户是超级管理员，则读取被编辑用户的部门站点、分类，及超级管理员建的角色，作为范畴
            $siteRange = DepartmentRelationModel::find()
                ->select(self::SITE_CODE)
                ->where([self::DEPARTMENT_ID => $this->editedUserInfo[self::DEPARTMENT_ID]])
                ->groupBy(self::SITE_CODE)
                ->asArray()
                ->all();
        } else {
            //else 读取当前用户的站点、角色, 作为范畴
            $siteRange = AdminRelationModel::find()->alias('ar')
                ->leftJoin(RoleModel::tableName() . ' as r', 'ar.role_id = r.id')
                ->select('r.site_code')
                ->where(['ar.admin_id' => $this->adminInfo['id'], 'r.is_delete' => RoleModel::NOT_DELETE])
                ->groupBy('r.site_code')
                ->asArray()
                ->all();
        }

        //角色的可选范围为自己创建的角色（可编辑）、其他人分配给用户的角色（只能查看）
        $roles = RoleModel::find()->alias('r')
            ->leftJoin(
                AdminRelationModel::tableName() . ' as ar',
                'ar.role_id = r.id AND ar.admin_id = ' . $this->editedUserInfo['id']
            )->select([
                'r.id as role_id',
                'r.name as role_name',
                'r.site_code',
                'IF(r.create_user = "' . $this->adminInfo[self::USERNAME] . '", 1, 0) as has_auth',
                'IF(ar.admin_id = ' . $this->editedUserInfo['id'] . ', 1, 0) as selected'
            ])->where(['r.create_user' => $this->adminInfo[self::USERNAME]])
            ->orFilterWhere(['ar.admin_id' => $this->editedUserInfo['id']])
            ->groupBy('r.id')
            ->asArray()
            ->all();

        //按站点划分所有角色
        $roleListInSiteCode = [];
        //按站点划分已分配的角色
        $selectedInSiteCode = [];
        //按站点划分其他人分配的角色
        $otherRolesInSiteCode = [];
        foreach ($roles as $role) {
            $role[self::ROLE_ID] = (int)$role[self::ROLE_ID];
            $role['has_auth'] = (int)$role['has_auth'];
            $role['selected'] = (int)$role['selected'];
            $roleListInSiteCode[$role[self::SITE_CODE]][] = $role;
            $role['selected'] && $selectedInSiteCode[$role[self::SITE_CODE]][] = $role[self::ROLE_ID];
            if (!$role['has_auth'] && !isset($otherRolesInSiteCode[$role[self::SITE_CODE]])) {
                $otherRolesInSiteCode[$role[self::SITE_CODE]] = false;
            }
        }

        foreach ($siteRange as &$value) {
            $value[self::ROLES] = !empty($roleListInSiteCode[$value[self::SITE_CODE]]) ?
                array_column($roleListInSiteCode[$value[self::SITE_CODE]], null, 'role_id') : [];
            $value['rolesSelected'] = $selectedInSiteCode[$value[self::SITE_CODE]] ?? [];
            $value['selected'] = !empty($value['rolesSelected']);
            $value['has_auth'] = isset($otherRolesInSiteCode[$value[self::SITE_CODE]]) ? 0 : 1;
        }
        unset($value);

        return $siteRange;
    }

    /**
     * 用户编辑
     * @return array
     * @throws JsonResponseException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function adminEdit()
    {
        //检查用户权限，非管理员和部门leader不可操作
        $this->checkEditAuth();

        $data = app()->request->post();

        if (!$data['id'] || !isset($data[self::IS_LEADER]) || !isset($data[self::STATUS])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        if (!$model = AdminModel::getById($data['id'])) {
            throw new JsonResponseException($this->codeFail, '用户账号不存在');
        }

        //部门数据
        $this->departmentList = DepartmentModel::departmentNodeCache();
        //检查是否对当前用户有操作权限，防止跨部门操作数据
        $this->checkEditUserAuth($model->department_id, $model->is_super, $model->is_leader);

        //检查sites数据是否合法，即不能删除别人创建的角色


        $model->status = $data[self::STATUS];
        $model->is_leader = $data[self::IS_LEADER];

        $dbTransaction = app()->db->beginTransaction();
        try {
            //更新admin表
            if (false === $model->update()) {
                throw new Exception('用户信息修改失败');
            }

            //删除旧的数据
            if (false === AdminRelationModel::deleteAll([self::ADMIN_ID => $data['id']])) {
                throw new Exception('修改失败，用户关系删除失败');
            }

            if (!empty($data[self::SITES])) {
                //更新数据
                if (!AdminRelationModel::updateAdminRelation($data['id'], $data[self::SITES])) {
                    throw new Exception('修改失败，用户关系更新失败');
                }

                //清理用户缓存
                app()->user->clearUserRedisCache($model->id, array_column($data[self::SITES], 'site_code'));
            }
            $dbTransaction->commit();
        } catch (Exception $e) {
            $dbTransaction->rollBack();
            throw new JsonResponseException($this->codeFail, $e->getMessage() ?: '修改失败，数据更新失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '修改成功');
    }

    /**
     * 检查是否有编辑权限
     * @throws JsonResponseException
     */
    private function checkEditAuth()
    {
        if (!$this->adminInfo[self::IS_SUPER] && !$this->adminInfo[self::IS_LEADER]) {
            throw new JsonResponseException($this->codeFail, '无权操作，只有管理员或部门Leader才有权限编辑');
        }
    }

    /**
     * 检查对用户是否有编辑权限，防止跨部门操作
     * @param int $departmentId 待检查用户的部门ID
     * @param int $isSuper 是否超级管理员
     * @param int $isLeader 是否部门leader
     * @throws JsonResponseException
     */
    private function checkEditUserAuth($departmentId, $isSuper, $isLeader)
    {
        if ($isSuper) {
            throw new JsonResponseException($this->codeFail, '超级管理员不可被编辑');
        }

        if (!($this->adminInfo[self::IS_SUPER]
            || ($this->departmentList[$departmentId]
                && $this->departmentList[$this->adminInfo[self::DEPARTMENT_ID]]
                && false !== stripos(
                    $this->departmentList[$departmentId]['node'],
                    $this->departmentList[$this->adminInfo[self::DEPARTMENT_ID]]['node']
                )
            ))
        ) {
            throw new JsonResponseException($this->codeFail, '待编辑用户不在您拥有的操作权限范围内');
        }

        if (!$this->adminInfo[self::IS_SUPER] && $isLeader
            && (int)$departmentId === (int)$this->adminInfo[self::DEPARTMENT_ID]) {
            throw new JsonResponseException($this->codeFail, '您无权编辑同部门leader的信息');
        }
    }

    /**
     * 获取用户有权限的站点列表
     * @return array
     * @throws JsonResponseException
     */
    public function siteList()
    {
        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            MenuComponent::getUserSites(app()->user->admin->is_super)
        );
    }

    /**
     * 初始化平台用户
     * @param int $pageNo
     * @param int $pageSize
     * @throws JsonResponseException
     */
    public function initUsers(int $pageNo = 1, int $pageSize = 100)
    {
        if (1 === $pageNo) {
            $key = app()->redisKey->getSyncUsersRedisKey();
            if (null === app()->redis->set($key, 1, 'EX', 180, 'NX')) {
                $ttl = app()->redis->ttl($key);
                throw new JsonResponseException($this->codeFail, '用户信息同步中，请勿重复操作，大概剩余：' . $ttl . 's');
            }

            ignore_user_abort(true);
            set_time_limit(60 * 10);

            $this->obFlush('初始化用户信息任务已发送，请稍后去“系统管理》权限管理》用户管理”处查看');
        }
        $curl = new curl\Curl();

        $data = array(
            'sn' => app()->params['url']['project_sn'],         //系统编码
            //'start_time' => '2016-06-30 00:00:00',            //更新时间起始值
            //'end_time' => '2017-07-01 23:59:59',              //更新时间结束值
            'per_page' => $pageSize,                            //每页拉取条目数(最大值为1000)
            'page' => $pageNo                                   //当前第几页
        );
        $params = $this->getInitUsersParams($data);

        $response = $curl->setPostParams($params)->post(app()->url->sso('interface/user/get-users'));

        \Yii::info('获取到的用户完整信息：' . $response, __METHOD__);

        if (200 !== $curl->responseCode) {
            throw new JsonResponseException(
                $this->codeFail,
                'sso用户信息获取失败:' . $curl->responseCode . $response
            );
        }

        $response && $result = json_decode($response, true);

        /** @var array[] $result */
        if (!empty($result['list'])) {
            foreach ($result['list'] as $userData) {
                if (true !== ($res = $this->insertOrUpdateUser($userData))) {
                    $this->errors[] = $res;
                }
            }
        }

        if ($result['total'] > $pageNo * $pageSize) {
            $this->initUsers($pageNo + 1, $pageSize);
        } else {
            \Yii::info('initUsers初始化用户错误信息：' . json_encode($this->errors), __METHOD__);
            // 设置isSent为true来阻止response->send()方法，否则会报HeadersAlreadySentException，因为在obFlush()中已经有输出了
            app()->response->isSent = true;
        }
    }

    /**
     * 更新用户信息
     * @param int $day 更新最近多少天内更新的用户
     * @return array
     * @throws JsonResponseException
     */
    public function updateUsers(int $day)
    {
        $curl = new curl\Curl();

        $params = array(
            'from_site_code' => app()->params['url']['project_sn'],         //系统编码
            'near_day' => $day                                              //获取最近多少天内更新的用户
        );

        $response = $curl->get(app()->url->sso('interface/user-list/updated', http_build_query($params)));

        \Yii::info('获取到的用户完整信息：' . $response, __METHOD__);

        if (200 !== $curl->responseCode) {
            throw new JsonResponseException(
                $this->codeFail,
                'sso用户信息获取失败:' . $curl->responseCode . $response
            );
        }

        $response && $result = json_decode($response, true);

        /** @var array[][] $result */
        $totalCount = 0;
        if (!empty($result['data']['user_list'])) {
            $totalCount = \count($result['data']['user_list']);
            foreach ($result['data']['user_list'] as $userData) {
                if (true !== ($res = $this->insertOrUpdateUser($userData))) {
                    $this->errors[] = $res;
                }
            }
        }

        $failCount = \count($this->errors);
        if (!empty($this->errors)) {
            \Yii::info('initUsers初始化用户错误信息：' . json_encode($this->errors), __METHOD__);
        }

        return app()->helper->arrayResult($this->codeSuccess, '更新成功', [
            'total' => $totalCount,
            'success' => $totalCount - $failCount,
            'fail' => $failCount
        ]);

    }

    /**
     * 新增或更新用户信息
     * !!!注意data中的username不要使用trim去空格，因为发现用户中心存在带有空格和不带空格的同名用户，若用trim会导致用户信息被覆盖
     * @param $data
     * @return array|bool
     */
    private function insertOrUpdateUser($data)
    {
        if (!isset($data['username']) || !isset($data['department_id']) || !isset($data['is_group_leader'])
            || !isset($data['user_no']) || !isset($data['is_lock'])) {
            return [
                'message' => '用户信息不完整',
                'data' => $data
            ];
        }

        if (!$user = AdminModel::findOne(['username' => $data['username']])) {
            $user = new AdminModel();
            $user->username = $data['username'];
            $user->logins = 0;
            $user->create_time = time();
            $user->status = AdminModel::STATUS_ENABLED;
            $user->department_id = $data['department_id'];
        }
        $user->user_no = trim($data['user_no']);
        $user->realname = !empty($data['name']) ? $data['name'] : $data['username'];
        $user->is_leader = $data['is_group_leader'];
        (int)$data['is_lock'] && $user->status = AdminModel::STATUS_UCLOCKED;//UC锁定状态

        try {
            if (!$user->save(true)) {
                return [
                    'message' => $user->flattenErrors(', '),
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'data' => $data
            ];
        }

        return true;
    }

    /**
     * 先返回信息给前端，然后后端继续处理
     *
     * @param string $message 返回信息
     */
    private function obFlush($message)
    {
        $content = json_encode(app()->helper->arrayResult($this->codeSuccess, $message));
        ob_end_clean();
        header('Connection: close');
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json;charset=utf-8');// 如果前端要的是json则添加，默认是返回的html/text
        ob_start();
        echo $content;// 输出结果到前端
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
        flush();
        if (\function_exists('fastcgi_finish_request')) { // yii或yaf默认不会立即输出，加上此句即可（前提是用的fpm）
            fastcgi_finish_request(); // 响应完成, 立即返回到前端,关闭连接
        }
    }

    /**
     * 获取初始化用户接口的参数
     * @param array $data
     * @return array
     */
    private function getInitUsersParams($data)
    {
        return [
            'data' => json_encode($data),
            'token' => $this->generateToken($data)
        ];
    }

    /**
     * 生成token
     * @param array $data
     * @return string
     */
    private function generateToken(array $data)
    {
        return md5(app()->params['url']['token_secret'] . json_encode($data));
    }
}
