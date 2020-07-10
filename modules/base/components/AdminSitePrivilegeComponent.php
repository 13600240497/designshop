<?php

namespace app\modules\base\components;

use app\base\SiteConstants;
use app\base\SitePlatform;
use ego\base\JsonResponseException;
use app\modules\base\models\AdminModel;
use app\modules\base\models\AdminSitePrivilegeModel;

/**
 * 管理用户站点数据权限组件
 *
 * @package app\modules\base\components
 */
class AdminSitePrivilegeComponent extends Component
{
    /** 权限位置 - 首页 */
    const PLACE_HOME = 'home';

    /** 权限位置 - 专题页 */
    const PLACE_SPECIAL = 'special';

    /**
     * 获取站点首页活动发布权限
     *
     * @param array $params get参数
     * @return array
     * @throws JsonResponseException
     */
    public function actionSitePrivileges($params)
    {
        $supportSites = SiteConstants::SITE_GROUP_CODE;
        if (empty($params['website_code'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        $websiteCode = $params['website_code'];
        if (!isset(app()->params['site'][$websiteCode])) {
            throw new JsonResponseException($this->codeFail, '无效参数：website_code');
        }

        $jsonData = [];
        $jsonData['name'] = $supportSites[$websiteCode] ?? '';
        // 国家站点获取支持渠道列表
        $jsonData['permissions'] = $this->getSiteAllPermissions($websiteCode);

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $jsonData);
    }

    /**
     * 保存用户首页活动发布权限
     * @param array $params post参数
     * @return array
     * @throws JsonResponseException
     */
    public function actionSaveUserSitePrivilege($params)
    {
        if (empty($params['user_id']) || empty($params['website_code']) || empty($params['permissions'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        $userId = $params['user_id'];
        $adminModel = AdminModel::getById($userId);
        if (!$adminModel) {
            throw new JsonResponseException($this->codeFail, '管理用户不存在');
        }

        $websiteCode = $params['website_code'];
        if (!isset(app()->params['site'][$websiteCode])) {
            throw new JsonResponseException($this->codeFail, '无效参数：website_code');
        }

        $permissions = json_decode($params['permissions'], true);
        if (empty($permissions)) {
            throw new JsonResponseException($this->codeFail, '无效参数:permissions');
        }

        $validPipelines = [];
        $configAllPipelines = app()->params['site'][ $websiteCode ]['pipeline'] ?? [];
        foreach ($permissions as $pipelineCode) {
            if (array_key_exists($pipelineCode, $configAllPipelines)) {
                $validPipelines[] = $pipelineCode;
            }
        }

        $place = $params['place'] ?? self::PLACE_HOME;
        if (empty($validPipelines)) {
            throw new JsonResponseException($this->codeFail, '无效参数:permissions');
        }

        $privilegeModel = AdminSitePrivilegeModel::findByUserIdAndWebsiteCode($userId, $websiteCode);
        if (!$privilegeModel) {
            $privilegeModel = new AdminSitePrivilegeModel();
            $privilegeModel->user_id = $userId;
            $privilegeModel->website_code = $websiteCode;
            $privilegeModel->home_permissions = $privilegeModel->special_permissions = '{}';
        } else {
            empty($privilegeModel->home_permissions) && $privilegeModel->home_permissions = '{}';
            empty($privilegeModel->special_permissions) && $privilegeModel->special_permissions = '{}';
        }

        $this->saveUserSiteHomePermissions($privilegeModel, $validPipelines, $place);
        return app()->helper->arrayResult($this->codeSuccess, '保存成功');
    }


    /**
     * 过滤掉当前登录用户没有专题页权限的渠道/语言
     *
     * @param string $siteCode
     * @param array $permissions
     * @return array
     */
    public function getCurrentUserValidSiteSpecialPermissions($siteCode, $permissions)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $hasPermissions = $this->getCurrentUserSiteSpecialPermissions($websiteCode);
        return array_intersect($permissions, $hasPermissions);
    }

    /**
     * 获取当前登录用户站点专题页语言权限
     * @param string $websiteCode 网站编码，如： zf/rg
     * @return array 有用户站点语言权限返回
     */
    public function getCurrentUserSiteSpecialPermissions($websiteCode)
    {
        return $this->getCurrentUserSitePermissions($websiteCode, self::PLACE_SPECIAL);
    }

    /**
     * 过滤掉当前登录用户没有首页权限的渠道/语言
     *
     * @param string $siteCode
     * @param array $permissions
     * @return array
     */
    public function getCurrentUserValidSiteHomePermissions($siteCode, $permissions)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $hasPermissions = $this->getCurrentUserSiteHomePermissions($websiteCode);
        return array_intersect($permissions, $hasPermissions);
    }

    /**
     * 获取当前登录用户站点首页语言权限
     * @param string $websiteCode 网站编码，如： zf/rg
     * @return array 有用户站点语言权限返回
     */
    public function getCurrentUserSiteHomePermissions($websiteCode)
    {
        return $this->getCurrentUserSitePermissions($websiteCode, self::PLACE_HOME);
    }

    /**
     * 获取站点支持渠道
     * @param string $websiteCode
     * @return array
     */
    public function getSiteAllPermissions($websiteCode)
    {
        $allPermissions = [];
        // 国家站点获取支持渠道列表
//        if (SiteConstants::SITE_GROUP_CODE_ZF == $websiteCode) {
            $allPermissions = app()->params['site'][ $websiteCode ]['pipeline'] ?? [];
//        }
        return $allPermissions;
    }

    /**
     * 是否拥有站点指定渠道/语言发布权限
     *
     * @param int $userId
     * @param string $siteCode
     * @param array $permissions
     * @param string $place 权限位置
     * @return boolean
     */
    private function hasSitePermissions($userId, $siteCode, $permissions, $place)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $hasPermissions = $this->getUserSitePermissions($userId, $websiteCode, $place);
        if (!empty($hasPermissions)) {
            $diffPermissions = array_diff($permissions, $hasPermissions);
            if (empty($diffPermissions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取当前登录用户站点语言权限
     *
     * @param string $websiteCode 网站编码，如： zf/rg
     * @param string $place 权限位置
     * @return array 有用户站点语言权限返回
     */
    private function getCurrentUserSitePermissions($websiteCode, $place)
    {
        if (app()->user->isGuest) {
            return [];
        }

        if (app()->user->admin->is_super) {
            $allPermissions = $this->getSiteAllPermissions($websiteCode);
            return array_keys($allPermissions);
        }

        $userId = app()->user->admin->id;
        return $this->getUserSitePermissions($userId, $websiteCode, $place);
    }

    /**
     * 获取用户站点语言权限
     *
     * @param int $userId 管理用户ID
     * @param string $websiteCode 网站编码，如： zf/rg
     * @param string $place 权限位置
     * @return array 有用户站点语言权限返回 array
     */
    private function getUserSitePermissions($userId, $websiteCode, $place)
    {
        $privilegeModel = AdminSitePrivilegeModel::findByUserIdAndWebsiteCode($userId, $websiteCode);
        if ($privilegeModel) {
            $hasPermissions = [];
            if (self::PLACE_HOME == $place) {
                if (!empty($privilegeModel->home_permissions)) {
                    $hasPermissions = json_decode($privilegeModel->home_permissions, true);
                }
            } else {
                if (!empty($privilegeModel->special_permissions)) {
                    $hasPermissions = json_decode($privilegeModel->special_permissions, true);
                }
            }

            if (!empty($hasPermissions))
                return $hasPermissions;
        }

        return [];
    }

    /**
     * 保存用户站点语言权限
     *
     * @param AdminSitePrivilegeModel $privilegeModel 用户站点数据权限model
     * @param array $permissions 拥有渠道/语言权限列表
     * @param string $place 权限位置
     * @throws JsonResponseException
     */
    private function saveUserSiteHomePermissions($privilegeModel, $permissions, $place)
    {
        if ($privilegeModel) {
            if (self::PLACE_HOME == $place) {
                $privilegeModel->home_permissions = json_encode($permissions);
            } else {
                $privilegeModel->special_permissions = json_encode($permissions);
            }

            if (!$privilegeModel->save(true)) {
                throw new JsonResponseException($this->codeFail, $privilegeModel->flattenErrors(', '));
            }
        }
    }
}
