<?php

namespace app\modules\base\components;

use ego\base\JsonResponseException;
use Yii;

use app\modules\base\models\{
    MenuModel, AdminRelationModel, RoleModel
};
use yii\base\Controller;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * 菜单组件
 * Class MenuComponent
 *
 * @package app\modules\base\components
 */
class MenuComponent extends Component
{
    /**
     * @var array geshop modules need menus
     */
    const GESHOP_MODULES = [
        'admin',
        //'api',
        'base',
        'component',
        'activity',
        'test',
    ];
    const SUCCESS        = 'success';
    const ROUTE          = 'route';
    const SITES          = 'sites';
    const STATUS         = 'status';
    const SHORT_NAME     = 'short_name';

    /**
     * 菜单列表
     * @return array
     */
    public function lists()
    {
        $menuList = MenuModel::menuList();
        $listData = app()->arrayTree->tree2array(
            app()->arrayTree->array2tree($menuList)
        );

        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            $listData
        );
    }

    /**
     * 添加菜单
     * @param array $data
     * @param bool  $runValidation
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function add(array $data, $runValidation = true)
    {
        unset($data['id']);
        $menuModel = new MenuModel();
        $data['sort'] = $data['order'];
        $menuModel->load(array_map('trim', $data), '');
        if ($menuModel->insert($runValidation)) {
            return app()->helper->arrayResult(
                0,
                '添加成功',
                MenuModel::getById($menuModel->id)
            );
        }

        return app()->helper->arrayResult(1, $menuModel->flattenErrors(', '));
    }

    /**
     * 编辑菜单
     * @param       $id
     * @param array $data
     * @param bool  $runValidation
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws JsonResponseException
     */
    public function edit($id, array $data, $runValidation = true)
    {
        if (empty($data)) {
            throw new JsonResponseException(1, '更新数据不能为空');
        }
        if (!$menuModel = MenuModel::getById($id)) {
            throw new JsonResponseException(2, '菜单不存在');
        }
        $data['sort'] = $data['order'];
        $menuModel->load(array_map('trim', $data), '');
        if (false === ($result = $menuModel->update($runValidation))) {
            throw new JsonResponseException(3, $menuModel->flattenErrors(', '), null, $result);
        }

        return app()->helper->arrayResult(
            0,
            '修改成功',
            MenuModel::getById($menuModel->id),
            $result
        );
    }

    /**
     * 删除菜单
     * @param $id
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws JsonResponseException
     */
    public function delete($id)
    {
        $menuModel = MenuModel::getById($id);
        if (!$menuModel) {
            throw new JsonResponseException(1, '菜单不存在');
        }

        $menuModel->is_delete = 1;
        if (false === ($result = $menuModel->update())) {
            throw new JsonResponseException(2, $menuModel->flattenErrors(', ') ?: '删除失败');
        }

        return app()->helper->arrayResult(
            0,
            '删除成功',
            null,
            $result
        );
    }

    /**
     * 获取菜单的下拉框选项数据
     * @return array
     */
    public function getTopMenu()
    {
        return MenuModel::getSelectOptionsData();
    }

    /**
     * 获取控制器路由
     * @return array
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhpRoutes()
    {
        $data = $this->getAppRoutes();
        $exists = [];
        $list = $this->lists();
        /** @var array[] $list */
        foreach ($list['data'] as $item) {
            $exists[ $item[ self::ROUTE ] ] = $item[ self::ROUTE ];
            unset($data[ $item[ self::ROUTE ] ]);
        }
        uasort($data, 'strcmp');

        return [
            'available' => array_filter(
                $data,
                function ($item) {
                    return false === strpos($item, 'public');
                }
            ),
            'assigned'  => $exists,
        ];
    }

    /**
     * Get list of application routes
     * @return array
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    private function getAppRoutes()
    {
        $result = [];
        foreach (app()->getModules() as $id => $child) {
            if (($child = app()->getModule($id)) !== null) {
                if (!\in_array($child->getUniqueId(), static::GESHOP_MODULES, true)) {
                    continue;
                }
                foreach ($child->controllerMap as $k => $type) {
                    $this->getControllerActions($type, $k, $child, $result);
                }
                $namespace = trim($child->controllerNamespace, '\\') . '\\';
                $this->getControllerFiles($child, $namespace, '', $result);
            }
        }

        return $result;
    }

    /**
     * Get list controller under module
     * @param \yii\base\Module $module
     * @param string $namespace
     * @param string $prefix
     * @param array $result
     * @return mixed
     * @throws \yii\base\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getControllerFiles($module, $namespace, $prefix, &$result)
    {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace), false);
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path, 0) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_dir($path . '/' . $file) && preg_match('%^[a-z0-9_/]+$%i', $file . '/')) {
                $this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
            } elseif (strcmp($file, 'Controller.php') !== 0 && strcmp(substr($file, -14), 'Controller.php') === 0) {
                $baseName = substr(basename($file), 0, -14);
                $id = app()->helper->str->revertCamelCase($baseName);
                $className = $namespace . $baseName . 'Controller';
                if (strpos($className, '-') === false && class_exists($className)
                    && is_subclass_of($className, Controller::class)
                ) {
                    $this->getControllerActions($className, $prefix . $id, $module, $result);
                }
            }
        }

        return;
    }

    /**
     * Get list action of controller
     * @param mixed            $type
     * @param string           $id
     * @param \yii\base\Module $module
     * @param array            $result
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getControllerActions($type, $id, $module, &$result)
    {
        /* @var $controller \yii\base\Controller */
        $controller = Yii::createObject($type, [$id, $module]);

        $prefix = $controller->uniqueId . '/';
        foreach ($controller->actions() as $id => $value) {
            $result[ $prefix . $id ] = $prefix . $id;
        }
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                $name = app()->helper->str->revertCamelCase(substr($name, 6));
                $id = $prefix . ltrim(str_replace(' ', '-', $name), '-');
                $result[ $id ] = $id;
            }
        }
    }

    /**
     * 获取站点的菜单
     * @return array
     * @throws JsonResponseException
     */
    public function getSiteMenus()
    {
        $adminInfo = app()->user->admin;
        if (1 !== $adminInfo->status) {
            throw new JsonResponseException(-1, '用户状态未开启或已关闭');
        }

        $activityType = app()->request->get('activity_type', SiteConstants::ACTIVITY_TYPE_SPECIAL);
        $sites = self::getUserSites($adminInfo->is_super);
        if (isset($sites['code']) && -1 === $sites['code']) {
            return $sites;
        }

        //GB站点广告落地页面只有PC和M端
        if ($activityType == SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT) {
            $sites = array_filter($sites, function($val) {
                $_siteCode = $val['short_name'];
                $_platformCode = SitePlatform::getPlatformCodeBySiteCode($_siteCode);
                if (isGearbestSite($_siteCode)
                    && !in_array($_platformCode, [SitePlatform::PLATFORM_CODE_PC, SitePlatform::PLATFORM_CODE_WAP])) {
                    return false;
                }
                return true;
            });
        }

        //按站点分组
        $siteGroups = self::buidGroupSites($sites);
        $defaultSiteCode = SitePlatform::getCurrentSiteGroupDefaultSiteCode();
        if ($defaultSiteCode && \in_array($defaultSiteCode, array_column($sites, 'short_name'), true)) {
            $currentSiteCode =$defaultSiteCode;
        } else {
            $currentSiteCode = $sites[0][ self::SHORT_NAME ] ?? '';
        }

        list($currentSiteGroupCode) = SitePlatform::splitSiteCode($currentSiteCode);

        $data = [
            self::SITES             => $sites,
            'siteGroups'            => $siteGroups,
            'currentSiteGroupCode'  => $currentSiteGroupCode,
            'userId'                => app()->user->id,
            'userName'              => app()->user->username,
            'realName'              => app()->user->realname,
            'isSuper'               => app()->user->admin->is_super,
            'isLeader'              => app()->user->admin->is_leader,
            'departmentId'          => app()->user->admin->department_id,
        ];

        $permissionInfo = $this->getPermissions($currentSiteCode);
        if (isset($permissionInfo['code']) && -1 === $permissionInfo['code']) {
            return $permissionInfo;
        }

        return app()->helper->arrayResult(
            0,
            self::SUCCESS,
            array_merge($data, $permissionInfo)
        );
    }

    /**
     * 按站点分组
     *
     * @param array $sites 站点数组数据
     *  - name          站点名称
     *  - short_name    站点简码
     *  - status        状态
     *
     * @return array 分组数据组
     *  - code                  站点组简码
     *  - name                  站点组名称
     *  - sites                 包含站点
     *  - sites.pc              各端站点(pc,wap,app)
     *  - sites.pc.name         站点名称
     *  - sites.pc.short_name   站点简码
     *  - sites.pc.status       状态
     */
    public static function buidGroupSites($sites)
    {
        $siteGroups = [];
        foreach ($sites as $siteInfo) {
            list($siteGroupCode, $platformCode) = SitePlatform::splitSiteCode($siteInfo['short_name']);

            if (!isset($siteGroups[$siteGroupCode])) {
                $siteGroups[$siteGroupCode] = [
                    'code' => $siteGroupCode,
                    'name' => strtoupper($siteGroupCode),
                    'sites' => []
                ];
            }

            if (!isset($siteGroups[$siteGroupCode]['sites'][$platformCode])) {
                $siteInfo['platform_name'] = SitePlatform::getPlatformNameByCode($platformCode);
                $siteGroups[$siteGroupCode]['sites'][$platformCode] = $siteInfo;
            }
        }

        return array_values($siteGroups);
    }

    /**
     * 根据用户获取站点信息
     * @param int $isSuper 是否超级管理员
     * @return array|\yii\db\ActiveRecord[]
     * @throws JsonResponseException
     */
    public static function getUserSites($isSuper)
    {
        /** @var array $allSitesInfo */
        $allSitesInfo = app()->params[self::SITES];
        $adminSiteCodes = (1 !== (int)$isSuper) ? self::getAdminSiteCodes() : [];

        $sites = [];
        foreach ($allSitesInfo as $key => $value) {
            if ((1 === (int)$isSuper || \in_array($key, $adminSiteCodes, true)) && 1 === (int)$value[self::STATUS]) {
                //安全考虑这里只返回部分字段，接口地址之类的信息不会返回给前端
                $sites[] = [
                    'name' => $value['name'],
                    self::SHORT_NAME => $key,
                    'status' => $value['status']
                ];
            }
        }

        return $sites;
    }

    /**
     * 获取默认站点site_code
     * @param int $isSuper 是否超级管理员
     * @return mixed|string
     * @throws JsonResponseException
     */
    public static function getDefaultSiteCode($isSuper)
    {
        $sites = self::getUserSites($isSuper);
        return !empty($sites) ? $sites[0][self::SHORT_NAME] : '';
    }

    /**
     * 获取当前站点组下默认站点简码(site_code)
     * @param int $isSuper 是否超级管理员
     * @return string 站点简码(site_code), NULL 站点没有配置或者没有站点权限
     */
    public static function getCurrentDefaultSiteCode($isSuper)
    {
        $sites = self::getUserSites($isSuper);
        $defaultSiteCode = SitePlatform::getCurrentSiteGroupDefaultSiteCode();
        if (!empty($sites) && !empty($defaultSiteCode)
            && \in_array($defaultSiteCode, array_column($sites, 'short_name'), true)) {
            return $defaultSiteCode;
        }
        return NULL;
    }

    /**
     * 用户站点
     * @return array
     * @throws JsonResponseException
     */
    private static function getAdminSiteCodes()
    {
        $result = AdminRelationModel::find()->alias('ar')
            ->leftJoin(RoleModel::tableName() . ' as r', 'ar.role_id = r.id')
            ->select('r.site_code')
            ->where(['ar.admin_id' => app()->user->id, 'r.is_delete' => RoleModel::NOT_DELETE])
            ->groupBy('r.id')
            ->asArray()
            ->all();
        if (empty($result)) {
            throw new JsonResponseException(-1, '用户站点数据为空');
        }

        return array_column($result, 'site_code');
    }

    /**
     * 获取站点管理员的菜单+操作权限
     * @param string $siteCode
     * @return array
     * @throws JsonResponseException
     */
    private function getPermissions($siteCode = '')
    {
        $menuList = MenuModel::menuList();
        $admin = app()->user->admin;
        // 普通用户
        if (0 === (int)$admin['is_super']) {
            $menuIds = AdminRelationModel::getUserRolesPrivileges(app()->user->id, $siteCode);

            if (empty($menuIds)) {
                throw new JsonResponseException(-1, '用户菜单数据为空');
            }

            //先取出拥有的
            $pids = array_column($menuList, 'parent_id');
            $permissions = array_filter($menuList, function ($v) use ($menuIds, $pids) {
                return $v['status'] && (\in_array((int)$v['id'], $menuIds, true) || $v['is_public'])
                    && ($v['parent_id'] === 0 || \in_array($v['parent_id'], $pids, false));
            });
        } else {
            $permissions = $menuList;
        }

        // 操作
        $actionPermissions = array_filter(
            $permissions,
            function ($item) {
                return (0 === (int)$item['type'] && 1 === (int)$item[ self::STATUS ]);
            }
        );
        // 菜单
        $permissions = array_filter(
            $permissions,
            function ($item) {
                return (1 === (int)$item['type'] && 1 === (int)$item[ self::STATUS ]);
            }
        );
        list($websiteCode, ) = explode('-', $siteCode, 2);
        array_walk($permissions, function (&$item, $key, $websiteCode){
            $item['route'] = strpos('?' , $item['route']) === false ? $item['route'] . '?site_group_code='. $websiteCode :
                $item['route'] . '&site_group_code=' . $websiteCode;
        }, $websiteCode);

        return [
            'permissions'       => array_filter(
                app()->arrayTree->array2tree($permissions),
                function ($item) {
                    return 0 === (int)$item['parent_id'];
                }
            ),
            'actionPermissions' => array_column($actionPermissions, self::ROUTE),
        ];
    }

    /**
     * 获取页面标题
     * @return string
     */
    public static function getPageTitle()
    {
        $route = app()->requestedRoute;
        $routeInfo = MenuModel::getByRoute($route);

        if (!$routeInfo) {
            return '';
        }

        $names = MenuModel::find()->where(['id' => explode(',', $routeInfo->node)])->asArray()->all();
        $names = array_column($names, 'name');

        krsort($names);

        return implode('-', $names);
    }
}
