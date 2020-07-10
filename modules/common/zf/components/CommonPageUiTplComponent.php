<?php

namespace app\modules\common\zf\components;

use app\components\auto\AutoRefreshUi;
use yii\helpers\ArrayHelper;
use app\base\Upload;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\base\RequestUtils;
use app\base\BizException;
use app\components\site\zf\PagePreview;
use app\modules\common\zf\models\{
    PageModel, PageUiTemplateModel, PageUiModel
};
use app\modules\component\models\UiModel;
use app\modules\component\models\ComponentModel;
use app\modules\base\models\AdminModel;
use app\modules\common\zf\traits\CommonPublishTrait;

/**
 * 页面模板管理组件
 * @property \app\modules\common\zf\components\CommonPageUiComponentDataComponent $commonPageUiComponentDataComponent
 */
class CommonPageUiTplComponent extends Component
{
    use CommonPublishTrait;

    /**
     * 新增组件模板
     *
     * @param array $params 传参数组
     *  - page_id int 页面ID
     *  - ui_id int 组件ID
     *  - name string 模板名称
     *  - pic_url string 模板预览图
     *  - view_type int  查看类型 1-公有类型， 2-私有类型
     *
     * @return array
     * @throws BizException
     */
    public function add($params)
    {
        if (!isset($params['page_id'], $params['ui_id'], $params['name'], $params['pic_url'], $params['view_type'])
                || !in_array($params['view_type'], [PageUiTemplateModel::VIEW_TYPE_PUBLIC, PageUiTemplateModel::VIEW_TYPE_PRIVATE])) {
            throw new BizException('参数错误!');
        }

        $pageId = $params['page_id'];
        /** @var  \app\modules\common\zf\models\PageModel $pageModel */
        $pageModel = PageModel::findOne($pageId);
        if (!$pageModel) {
            throw new BizException('找不到活动页面!');
        }

        /** @var  \app\modules\common\zf\models\PageUiModel $pageUiModel */
        $pageUiModel = PageUiModel::getById($params['ui_id']);
        if (!$pageUiModel) {
            throw new BizException('找不到组件!');
        }

        $placeType = RequestUtils::getPageTypeByModuleName();
        if (SiteConstants::ACTIVITY_PAGE_TYPE_UNKNOW == $placeType) {
            throw new BizException('找不到模块!');
        }

        $this->checkTplName($params['name']);

        // 获取组件数据
        $uiListData = $this->getUiDataByComponentId([['id' => $pageUiModel->id, 'tpl_id' => $pageUiModel->tpl_id]]);
        if (!isset($uiListData[0]['data']) || empty($uiListData[0]['data'])) {
            throw new BizException('当前组没有配置数据，请先配置组件数据，在保存模板!');
        }
        $uiData = json_decode($uiListData[0]['data'], true);

        // 清除导航数据
        if (isset($uiData['navNewData'])) unset($uiData['navNewData']);
        if (isset($uiData['nav_menu'])) unset($uiData['nav_menu']);
        
        // 保存组件模板
        /** @var  \app\modules\common\zf\models\PageUiTemplateModel $templateModel */
        $templateModel = new PageUiTemplateModel();
        list($websiteCode, $platformCode) = SitePlatform::splitSiteCode($pageModel->site_code);
        $templateModel->website_code = $websiteCode;
        $templateModel->platform_code = $platformCode;
        $templateModel->page_id = $pageId;
        $templateModel->place_type = $placeType;
        $templateModel->lang = $pageUiModel->lang;
        $templateModel->name = $params['name'];
        $templateModel->pic_url = $params['pic_url'];
        $templateModel->ui_key = $pageUiModel->component_key;
        $templateModel->tpl_id = $pageUiModel->tpl_id;
        $templateModel->ui = json_encode(ArrayHelper::toArray($pageUiModel));
        $templateModel->ui_data = json_encode($uiData);
        $templateModel->view_type = $params['view_type'];
        $templateModel->is_delete = PageUiTemplateModel::NOT_DELETE;
        $templateModel->create_user = $templateModel->update_user = app()->user->username;
        $templateModel->create_time = $templateModel->update_time = time();

        if (!$templateModel->save(true)) {
            throw new BizException('模板添加失败!');
        }

        return ['id' => $templateModel->id];
    }

    /**
     * 上传图片并上传到S3,返回s3地址
     * @return string
     * @throws BizException
     */
    protected function uploadTplPicToS3()
    {
        $uploadPath = \yii::getAlias('@app/runtime');
        $uploadPath .= DIRECTORY_SEPARATOR . 'uploads/ui-tpl/logo';
        $upload = new Upload($uploadPath);
        $file = $upload->uploadS3();
        if (!$file) {
            throw new BizException($file->error);
        }

        return $file['url'];
    }

    /**
     * 编辑组件模板
     *
     * @param array $params 传参数组
     * - id int 组件模板ID
     * - name
     * - view_type
     *
     * @return array
     * @throws BizException
     */
    public function edit($params)
    {
        if (!isset($params['id'], $params['name'], $params['view_type'])
                || !in_array($params['view_type'], [PageUiTemplateModel::VIEW_TYPE_PUBLIC, PageUiTemplateModel::VIEW_TYPE_PRIVATE])) {
            throw new BizException('参数错误!');
        }

        $templateModel = PageUiTemplateModel::findone($params['id']);
        if (!$templateModel) {
            throw new BizException('模板不存在!');
        }

        if (!$this->checkAuthority($templateModel->create_user)) {
            throw new BizException('没有操作权限!');
        }

        $this->checkTplName($params['name'], $params['id']);

        $templateModel->name = $params['name'];
        $templateModel->view_type = $params['view_type'];
        $templateModel->update_user = app()->user->username;
        $templateModel->update_time = time();
        if (!$templateModel->save(true)) {
            throw new BizException('更新失败!');
        }

        return ['id' => $params['id']];
    }

    /**
     * 删除组件模板
     *
     * @param array $params 传参数组
     * - id int 组件模板ID
     *
     * @return array
     * @throws BizException
     */
    public function delete($params)
    {
        if (!isset($params['id'])) {
            throw new BizException('参数错误!');
        }

        $templateModel = PageUiTemplateModel::findone($params['id']);
        if (!$templateModel) {
            throw new BizException('模板不存在!');
        }

        if (!$this->checkAuthority($templateModel->create_user)) {
            throw new BizException('没有操作权限!');
        }

        $templateModel->is_delete = PageUiTemplateModel::DELETED;
        if (!$templateModel->save(true)) {
            throw new BizException('删除失败!');
        }

        return ['id' => $params['id']];
    }

    /**
     * 组件模板列表
     *
     * @param array $params 传参数组
     * - site_code string 站点简码，如： rg_pc/rg_wap
     * - lang
     * - view_type
     * - place_type
     * - ui_key
     * - pageNo
     * - pageSize
     *
     * @return array
     * @throws BizException
     */
    public function getList($params)
    {
        if (!isset($params['site_code']) || empty($params['site_code'])) {
            throw new BizException('参数错误!');
        }

        list($websiteCode, $platformCode) = SitePlatform::splitSiteCode($params['site_code']);
        $username = app()->user->username;

        $listParams = [
            'username'      => $username,
            'page_no'       => $params['pageNo'] ?? 1,
            'page_size'     => $params['pageSize'] ?? 20,
            'website_code'  => $websiteCode,
            'platform_code' => $platformCode,
            'place_type'    => $params['place_type'] ?? 0,
            'view_type'     => $params['view_type'] ?? 0,
            'ui_key'        => $params['ui_key'] ?? NULL,
            'keyword'       => $params['keyword'] ?? NULL,
        ];

        $result = ['pageNo' => $listParams['page_no'], 'pageSize' => $listParams['page_size']];
        $tplResultList = PageUiTemplateModel::getTplPageList($listParams);
        if (empty($tplResultList)) {
            $result['totalCount'] = 0;
            $result['list'] = [];
            return $result;
        }

        $uiComponentList = self::getUiComponentList(['site_code' => $params['site_code']]);
        $uiComponentList = array_column($uiComponentList, 'name', 'key');

        list($rowsCount, $tplModelList) = $tplResultList;
        $tplList = ArrayHelper::toArray($tplModelList);
        $users = array_unique(array_column($tplList, 'create_user'));
        $userList = AdminModel::getByUserNames($users);
        $realNames = array_column($userList, 'realname', 'username');
        $langList = (array)app()->params['lang'];
        foreach ($tplList as &$v) {
            $_createUser = $v['create_user'];
            $v['lang'] = [
                'key' => $v['lang'],
                'value' => isset($langList[$v['lang']]) ? $langList[$v['lang']]['name'] : ''
            ];
            $v['ui_name'] = $uiComponentList[$v['ui_key']] ?? '';
            $v['real_name'] = isset($realNames[$_createUser]) ? $realNames[$_createUser] : $_createUser;
            $v['opt'] = (int)$this->checkAuthority($_createUser);
            $v['platform_name'] = SitePlatform::getPlatformNameByCode($v['platform_code']);
            $v['preview_url'] = $this->getUiPreviewUrl(['tpl_id' => $v['id']]);
            unset($v['platform_code'], $v['ui_data']);
        }
        unset($users, $userList, $realNames);

        $result['totalCount'] = $rowsCount;
        $result['list'] = $tplList;
        return $result;
    }

    /**
     * 获取站点的组件列表
     *
     * @param array $params 传参数组
     *  - site_code string 站点简码，如： rg_pc/rg_wap
     *
     * @return array
     * @throws BizException
     */
    public function getUiComponentList($params)
    {
        if (!isset($params['site_code']) || empty($params['site_code'])) {
            throw new BizException('参数错误!');
        }

        list($websiteCode, $platformCode) = SitePlatform::splitSiteCode($params['site_code']);
        $rangeType = (SitePlatform::PLATFORM_CODE_PC == $platformCode) ? ComponentModel::RANGE_PC : ComponentModel::RANGE_WAP;
        $listParams = [
            'type'      => 2, // 组件类型(1 布局 2 UI)
            'status'    => 3,
            'place'     => $params['place'] ?? NULL,
            'range'     => $rangeType,
            'siteGroup' => $websiteCode,
            'pageSize'  => 200
        ];
        $uiModel = new UiModel();
        $pageUiList = $uiModel->getList($listParams);
        if (empty($pageUiList)) {
            return [];
        }

        $uiList = [];
        foreach ($pageUiList['list'] as $v) {
            $uiList[] = ['key' => $v['component_key'], 'name' => $v['name'], 'place' => $v['place']];
        }
        return $uiList;
    }

    /**
     * 保存组件模板数据到当前组件
     *
     * @param int $uiTplId
     * @param CommonUi $ui
     *
     * @throws BizException
     */
    public function saveUiDataFromTpl($uiTplId, $ui)
    {
        if (!is_object($ui) || !($ui instanceof CommonUi)) {
            throw new BizException('参数错误!');
        }

        /** @var \app\modules\common\zf\models\PageUiTemplateModel $templateModel */
        $templateModel = PageUiTemplateModel::findOne($uiTplId);
        if (!$templateModel) {
            throw new BizException('找不到组件模板!');
        }

        if (($ui->key != $templateModel->ui_key)) {
            throw new BizException('组件模板不匹配!');
        }

        if (!empty($templateModel->ui)) {
            $uiInfo = json_decode($templateModel->ui, true);
            if (isset($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT])
                && !empty($uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT]))
            {
                $ui->model->async_data_format = $uiInfo[AutoRefreshUi::KEY_UI_ASYNC_DATA_FORMAT];
                if (!$ui->model->save(false)) {
                    throw new BizException('更新组件数据错误!');
                }
            }
        }

        if (!empty($templateModel->ui_data)) {
            $postData = [
                'id'            => $ui->instanceId,
                'private_data'  => $templateModel->ui_data,
                'public_data'   => '{}',
                'site_code'     => $ui->siteCode,
                'page_id'       => $ui->pageId,
                'pipeline'      => $ui->pipeline,
                'tpl_id'        => $templateModel->tpl_id,
                'lang'          => $ui->lang // 不区分语言，使用组件语言
            ];

            if (!$ui->saveFormData($postData)) {
                throw new BizException('保存组件模板数据错误！');
            }

            // 更新使用次数
            PageUiTemplateModel::updateAllCounters(['used_count' => 1], ['id' => $uiTplId]);
        }
    }

    /**
     * 获取组件显示截图的保存到S3的URL地址
     *
     * @param array $params 传参数组
     *  - page_id int 页面ID
     *  - ui_id int 组件ID
     *
     * @return string
     * @throws BizException
     */
    public function getUiPreviewPicUrl($params)
    {
        $url = $this->getUiPreviewUrl($params, true);
        $url .= '&is_client=1';
        return \app\base\Snapshot::getUiTplUrl($url);
    }

    /**
     * 组件预览
     *
     * @param array $params 传参数组
     * - page_id int 页面ID
     * - ui_id int 组件ID
     * - tpi_id int 组件模板ID
     *
     * @return string
     * @throws BizException
     */
    public function getUiPreviewHtml($params)
    {
        return $this->getUiTplPreviewHtml($params);
    }

    /**
     * 组件模板预览
     *
     * @param array $params 传参数组
     *  - tpi_id int 组件模板ID
     *
     * @return string
     * @throws BizException
     */
    private function getUiTplPreviewHtml($params)
    {
        if (!isset($params['tpl_id'])) {
            throw new BizException('参数错误!');
        }

        $pagePreview = new PagePreview();
        return $pagePreview->getUiTemplatePreview($params['tpl_id']);
    }


    /**
     * 校验用户是否有操作权限
     * @param string $username 用户名
     * @return boolean  true-有操作权限； false-无操作权限
     */
    private function checkAuthority($username)
    {
        if (empty($username)) {
            return false;
        }

        $isSuper = (int)app()->user->get('is_super');  //超级管理员：1是，0否
        if ($isSuper === 1 || ($isSuper === 0 && $username === app()->user->username)) {
            return true;
        }
        return false;
    }


    /**
     * 检查名称
     * 1、名称不能为空
     * 2、字符长度不大于 50字符
     * 3、不能重复
     *
     * @param string $name 模板名称
     * @param int $id 模板id
     * @throws BizException
     */
    private function checkTplName($name, $id = 0)
    {
        if (empty($name)) {
            throw new BizException('名称不能为空!');
        } elseif (mb_strlen($name, 'utf-8') > 50) {
            throw new BizException('名称不能超过50个字符!');
        }

        $info = PageUiTemplateModel::getTplByName($name);
        if ($info && (empty($id) || $info['id'] != $id)) {
            throw new BizException('该模板名称已被使用，请更新名称!');
        }
    }

    /**
     * 获取组件显示预览地址
     *
     * @param array $params 传参数组
     *  - page_id int 页面ID
     *  - ui_id int 组件ID
     * @param boolean $isFull 是否绝对地址
     *
     * @return string
     * @throws BizException
     */
    public function getUiPreviewUrl($params, $isFull=false)
    {
        $url = '/'. RequestUtils::getModuleName();
        $url .= '/zf/page-ui-tpl/ui-preview';
        if (isset($params['page_id'], $params['ui_id'])) {
            $url .= '?page_id='. $params['page_id'] .'&ui_id='. $params['ui_id'];
        } elseif (isset($params['tpl_id'])) {
            $url .= '?tpl_id='. $params['tpl_id'];
        } else {
            throw new BizException('参数错误2!');
        }

        if ($isFull) {
            $url = rtrim(app()->params['url']['admin'], '/') . $url;
        }

        return $url;
    }
}