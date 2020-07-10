<?php

namespace app\modules\activity\zf\components;

use ego\base\JsonResponseException;
use app\base\SitePlatform;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\components\CommonPageUiDesignComponent;


/**
 * 页面装修设计-UI组件部分
 *
 * @property NativePagePrivilegeComponent $NativePagePrivilegeComponent
 */
class PageUiDesignComponent extends CommonPageUiDesignComponent
{

    /**
     * 覆盖父类方式，增加装修用户检查
     *
     * @param array $params
     * @return array
     * @throws JsonResponseException
     */
    public function saveNativeForm(array $params)
    {
        $pageId = $params['page_id'] ?? 0;
        $pageModel = PageModel::getById($pageId);
        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '找不到页面');
        }

        // 检查当前用户是否是正在装修的用户
        $userId = app()->user->admin->id;
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageModel->site_code);
        $lockPageIds = $this->NativePagePrivilegeComponent->getLockPageIds($pageModel);
        $editUserId = $this->NativePagePrivilegeComponent->getCurrentDesignUserId($websiteCode, $lockPageIds);
        if (empty($editUserId) || ((int)$editUserId !== (int)$userId)) {
            throw new JsonResponseException($this->codeFail, '当前其他用户在装修，不能保存！');
        }

        return parent::saveNativeForm($params);
    }

}
