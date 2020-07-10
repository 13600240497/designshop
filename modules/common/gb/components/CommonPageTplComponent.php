<?php

namespace app\modules\common\gb\components;

use app\modules\common\gb\models\{
    PageLayoutDataModel, PageModel, PageLayoutModel, PageUiModel, PageTplModel
};
use app\modules\base\models\AdminModel;
use app\modules\common\gb\traits\CommonPublishTrait;
use app\base\Upload;
use app\base\SitePlatform;
use yii\helpers\ArrayHelper;

/**
 * 页面模板管理组件
 * @property \app\modules\common\components\CommonPageUiComponentDataComponent $commonPageUiComponentDataComponent
 */
class CommonPageTplComponent extends Component
{
    use CommonPublishTrait;

    public $errors;

    //模板模型
    private $tplModel;

    //默认模板状态
    private $defaultStatus = 1;

    //默认导航组件编码
    private $defaultNavComponent = ['U000027', 'U000029', 'U000030','U000142','U000143'];

    //适用范围 1PC端,2WAP端,3响应式
    private $rangeSetting = [
        '-pc' => 1,
        '-wap' => 2,
        '-android' => 2,
        '-ios' => 2
    ];

    /*
    *页面模板图片目录
    */
    private $Path = 'uploads/page/tpl';


    /**
     * 新增模板
     *
     * @param array $params 传参数组
     *                      ['name']       string      模板名称
     *                      ['pageId']     int         页面ID
     *                      ['lang']       string      模板语言
     *                      ['pic']        string      模板预览图
     *                      ['pid']        string      32位长度的pid，作为查看预览
     *                      ['type']       int         模板类型 1-公有类型， 2-私有类型
     *                      ['range']      int         适用范围 1-PC端,2-WAP端,3-响应式
     *
     * @return boolean|array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function add($params)
    {
        list($name, $pageId, $lang, $siteCode, $pic, $pid, $type) = $params;
        if (!isset($name, $pageId, $lang, $siteCode, $pid) || !in_array($type, [1, 2])) {
            $this->errors = '参数错误';

            return false;
        }

        /** @var  \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::findOne($pageId);
        $this->tplModel = new PageTplModel();
        if ($this->checkName($name)) {
            return false;
        }

        $siteCode = $pageModel->site_code;
        $range = $this->getRangeBySiteCode($siteCode);

        list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->getPageInfo($pageId, $lang);//获取页面数据
        $insertArr = [
            'name'        => $name,
            'lang'        => $lang,
            'site_code'   => $siteCode,
            'custom_css'  => $pageModel['custom_css'] ?? '',
            'layout'      => json_encode($layoutInfo),
            'layout_data' => json_encode($layoutData),
            'ui'          => json_encode($uiInfo),
            'ui_data'     => json_encode($uiData)
        ];
        $this->tplModel->attributes = $insertArr;
        if (!$this->tplModel->validate()) {
            $this->errors = $this->tplModel->errors;

            return false;
        }
        $place = PageTplModel::ACTIVITY_PLACE;

        switch (app()->controller->module->module->id) {
            case 'activity':
                $place = PageTplModel::ACTIVITY_PLACE;
                break;
            case 'home':
                $place = PageTplModel::HOME_PLACE;
                break;  
            case 'advertisement':
                $place = PageTplModel::ADVERTISEMENT_PLACR;
                break;
        }
        $this->tplModel->create_user = app()->user->username;
        $this->tplModel->create_time = $this->tplModel->update_time = time();
        $this->tplModel->lang = $lang;
        $this->tplModel->range = $range;
        $this->tplModel->pid = $pid;
        $this->tplModel->tpl_type = (int)$type;
        $this->tplModel->pic = $pic;
        $this->tplModel->place = $place;
        $this->tplModel->platform_type = SitePlatform::getPlatformTypeBySiteCode($siteCode);
        $this->tplModel->save();
        if ($this->tplModel->id > 0) {
            return ['id' => $this->tplModel->id];
        }
        $this->errors = '新增失败';

        return false;

    }

    /**
     * 编辑页面模板
     *
     * @param int $id [模板ID]
     * @param string $name [模板名称]
     * @param string $pic [模板预览图]
     * @param int $type [模板类型]
     *
     * @return  bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit($id, $name, $pic, $type)
    {
        $this->tplModel = PageTplModel::findone($id);
        if (!$this->tplModel) {
            $this->errors = '模板不存在';

            return false;
        }

        if (!$this->checkAuthority($this->tplModel->create_user)) {
            $this->errors = '没有操作权限';

            return false;
        }

        if ($this->checkName($name, $id)) {
            return false;
        }

        $this->tplModel->name = $name;
        $this->tplModel->pic = $pic;
        $this->tplModel->tpl_type = (int)$type;
        $this->tplModel->update_user = app()->user->username;
        $this->tplModel->update_time = time();
        if ($this->tplModel->save(false)) {
            return true;
        }
        $this->errors = '更新失败';

        return false;
    }

    /**
     * 删除页面模板
     *
     * @param int $id
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function delete($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->errors = '参数有误';

            return false;
        }

        $model = PageTplModel::findOne($id);
        if (!($model)) {
            $this->errors = '模板不存在';

            return false;
        }

        if (!$this->checkAuthority($model->create_user)) {
            $this->errors = '没有操作权限';

            return false;
        }

        $model->is_delete = 1;
        $model->name = 'delete_' . $model->name . '_' . $id;
        if ($model->save()) {
            return true;
        }

        $this->errors = '删除失败';

        return false;
    }

    /**
     * 模板列表
     *
     * @param  array $params 传参数组
     *                       ['pageNo']     int        页码
     *                       ['pageSize']   int        每页数量
     *                       ['name']       string     模板名称
     *                       ['lang']       string     模板语言
     *                       ['siteCode']   string     站点简称
     *                       ['type']       int        模板类型
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getList($params)
    {
        $params['range'] = $this->getRangeBySiteCode($params['site_code']);
        $params['platform_type'] = 0;
        if (!empty($params['site_code'])) {
            $params['platform_type'] = SitePlatform::getPlatformTypeBySiteCode($params['site_code']);
        }

        $this->tplModel = new PageTplModel();
        $return = $this->tplModel->getTplList($params);
        $langList = (array)app()->params['lang'];
        if (!empty($return['list'])) {
            $users = array_unique(array_column($return['list'], 'create_user'));
            $userList = AdminModel::getByUserNames($users);
            $username = array_column($userList, 'realname', 'username');
            foreach ($return['list'] as &$v) {
                $v['lang'] = [
                    'key' => $v['lang'],
                    'value' => isset($langList[$v['lang']]) ? $langList[$v['lang']]['name'] : ''
                ];
                $v['real_name'] = isset($username[$v->create_user]) ? $username[$v->create_user] : $v->create_user;
                $v['opt'] = (int)$this->checkAuthority($v->create_user);
                $v['platform_name'] = SitePlatform::getPlatformNameByType($v['platform_type']);
                unset($v['platform_type']);
            }
            unset($users, $userList, $username);
        }

        return $return;
    }

    /**
     * 修改默认模板
     *
     * @param  int $id [模板id]
     * @param  string $siteCode [站点简称]
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function changeDefaultTpl($id, $siteCode)
    {
        if (empty($id) || empty($siteCode)) {
            $this->errors = '参数错误';

            return false;
        }
        $this->tplModel = PageTplModel::findone($id);
        if (empty($this->tplModel)) {
            $this->errors = '模板不存在';

            return false;
        }
        $oldModel = new PageTplModel();
        $oldTplModel = $oldModel->getDefaultInfo($siteCode, $this->defaultStatus);

        $transaction = $this->tplModel->getDb()->beginTransaction();
        try {
            if ($oldTplModel) {
                $oldTplModel->is_default = 0;
                $oldTplModel->save(false);
            }
            $this->tplModel->is_default = $this->defaultStatus;
            $this->tplModel->site_code = $siteCode;
            $this->tplModel->update_user = app()->user->username;
            if (!$this->tplModel->save(false)) {
                $this->errors = '设置失败';

                return false;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->errors = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * 页面初始化模板数据
     *
     * @param int $pageId 页面ID
     * @param int $tplId 模板id
     * @param string $lang 语言代码简称
     * @return bool
     */
    public function initPageTpl($pageId, $tplId = 0, $lang)
    {
        $this->tplModel = new PageTplModel();
        $tplInfo = $this->tplModel->findOne($tplId);
        $tplInfo = ArrayHelper::toArray($tplInfo);
        $this->copyPage($pageId, $tplInfo, $lang);

        return true;
    }

    /**
     * 复制模板数据
     *
     * @param int $pageId 页面id
     * @param object $tplInfo 模板数据
     * @param string $lang 需复制语言(为空表示页面所有语言复制)
     */
    public function copyPage($pageId, $tplInfo, $copyLang = '')
    {
        $layout = json_decode($tplInfo['layout'], true);
        krsort($layout);
        $layoutData = json_decode($tplInfo['layout_data'], true);
        $ui = json_decode($tplInfo['ui'], true);
        $uiData = json_decode($tplInfo['ui_data'], true);
        $page = new PageModel();
        if (!$copyLang) {
            $pages = $page->getPageInfo($pageId);
        } else {
            $pages['pageLanguages'][]['lang'] = $copyLang;
        }
        foreach ($pages['pageLanguages'] as $lang) {
            $nextId = 0;
            //布局组件处理
            foreach ($layout as $key => $item) {
                $layoutModel = new PageLayoutModel();
                $layoutDataModel = new PageLayoutDataModel();
                //保存布局位置
                $layoutModel->component_key = $item['component_key'];
                $layoutModel->next_id = $nextId;
                $layoutModel->page_id = $pageId;
                $layoutModel->lang = $lang['lang'];
                $layoutModel->save();
                //保存布局数据
                $layoutDataModel->component_id = $layoutModel->id;
                $layoutDataModel->data = $layoutData[$key]['data'];
                $layoutDataModel->custom_css = $layoutData[$key]['custom_css'];
                $layoutDataModel->background_color = $layoutData[$key]['background_color'];
                $layoutDataModel->background_img = $layoutData[$key]['background_img'];
                $layoutDataModel->lang = $lang['lang'];
                $layoutDataModel->save();
                $layoutArr[$item['id']] = $layoutModel->id;
                $nextId = $layoutModel->id;

            }
            $this->handleUi($layoutArr, $ui, $uiData, $lang['lang']);
        }
    }

    /**
     *  //UI组件处理
     *
     * @param $layoutArr
     * @param $ui
     * @param $uiData
     * @param $languages
     */
    private function handleUi($layoutArr, $ui, $uiData, $languages)
    {
        //$newMenu 新导航需导航组件数组
        //$newIds 新导航需要修改的uiData记录ID
        list($id, $navMenu, $data) = $this->getNavData($ui, $uiData);
        $newMenu = $newIds = [];
        foreach ($ui as $key => $item) {//单个布局组件
            foreach ($item as $k => $val) {//布局组件单列
                $val = $this->getOrderedComponents($val);
                krsort($val);
                $nextId = 0;
                foreach ($val as $uiItem) {
                    $uiModel = new PageUiModel();
                    //保存UI位置
                    $uiModel->component_key = $uiItem['component_key'];
                    $uiModel->layout_id = $layoutArr[$key];
                    $uiModel->next_id = $nextId;
                    $uiModel->position = $k;
                    $uiModel->tpl_id = $uiItem['tpl_id'] ?? 0;
                    $uiModel->lang = $languages;
                    $uiModel->save();
                    $nextId = $uiModel->id;
                    //当前旧组件ID在需要导航数据内
                    if (in_array((int)$uiItem['id'], $navMenu)) {
                        $newMenu[] = $nextId;
                    }
                    //当前操作组件数据ID等于旧组件导航ID
                    if ((int)$uiItem['id'] === $id) {
                        $newIds[] = [
                            'id' => $uiModel->id,
                            'lang' => $languages,
                            'tplId' => $uiData[$key][$k][$uiItem['id']]['tpl_id']
                        ];
                    }
                    //保存ui数据
                    $pageUiComponentData = new CommonPageUiComponentDataComponent();
                    //过滤掉SKU
                    $uiDataNeedToCopy = json_decode($uiData[$key][$k][$uiItem['id']]['data'], true);
                    unset($uiDataNeedToCopy['goodsSKU']);
                    $pageUiComponentData->insertPageUiComponentData(
                        $uiModel->id,
                        $uiDataNeedToCopy,
                        $uiData[$key][$k][$uiItem['id']]['tpl_id'],
                        $languages
                    );
                }
            }
        }
        if (!empty($newMenu) && !empty($data)) {
            $newNav = json_decode($data['data'], true);
            $newNav['nav_menu'] = $newMenu;
            foreach ($newIds as $new) {
                //保存ui数据
                $pageUiComponentData = new CommonPageUiComponentDataComponent();
                $pageUiComponentData->insertPageUiComponentData($new['id'], $newNav, $new['tplId'], $new['lang']);

            }
        }
    }

    /**
     * 获取导航数据
     *
     * @param $ui
     * @param $uiData
     *
     * @return array
     */
    private function getNavData($ui, $uiData)
    {
        //抽取旧导航组件id
        $id = 0;
        foreach ($ui as $item) {
            foreach ($item as $value) {
                foreach ($value as $val) {
                    if (in_array($val['component_key'], $this->defaultNavComponent)) {
                        $id = (int)$val['id'];
                    }
                }
            }
        }
        //$navMenu 旧导航需导航组件数组
        //$data 旧导航组件数据
        $navMenu = $data = [];
        foreach ($uiData as $item) {
            foreach ($item as $value) {
                foreach ($value as $key => $val) {
                    if ($id === (int)$key) {
                        $data = $val;
                        $nav = json_decode($val['data'], true);
                        $navMenu = $nav['nav_menu'] ?? [];
                    }
                }
            }
        }

        return [$id, $navMenu, $data];
    }

    /**
     * 检查名称
     * 1、名称不能为空
     * 2、字符长度不大于 50字符
     * 3、不能重复
     *
     * @param string $name 模板名称
     * @param int $id 模板id
     *
     * @return bool
     */
    private function checkName($name, $id = 0)
    {
        if (empty($name)) {
            $this->errors = '名称不能为空';

            return true;
        } elseif (mb_strlen($name, 'utf-8') > 50) {
            $this->errors = '名称不能超过50个字符';

            return true;
        }

        $info = $this->tplModel->getInfoByName($name);
        if ($info && (empty($id) || $info['id'] != $id)) {
            $this->errors = '该模板名称已被使用，请更新名称';

            return true;
        }

        return false;
    }

    /**
     * 获取页面组件关联信息
     *
     * @param int $pageId 页面ID
     * @param string $lang 页面语言
     *
     * @return array 布局与UI组件的位置和数据
     */
    public function getPageInfo($pageId, $lang)
    {
        $layoutInfo = $layoutData = $uiData = $uiInfo = [];
        $pageInfo = $this->getPageLayoutAndUiByPageId($pageId, $lang);//获取页面组件关系与组件数据(从上到下)
        foreach ($pageInfo[0] as $key => $val) {
            $layoutInfo[$key] = ['component_key' => $val['component_key'], 'id' => $val['id']];
            $layoutData[$key] = [
                'data' => $val['data'],
                'custom_css' => $val['custom_css'],
                'background_color' => $val['background_color'],
                'background_img' => $val['background_img']
            ];
        }
        foreach ($pageInfo[1] as $key => $layout) {
            foreach ($layout as $pid => $position) {
                foreach ($position as $orderId => $item) {
                    $uiInfo[$key][$pid][$orderId] = [
                        'component_key' => $item['component_key'],
                        'id' => $item['id'],
                        'next_id' => $item['next_id'],
                        'tpl_id' => $item['tpl_id'],
                        'position' => $item['position']
                    ];
                    $uiData[$key][$pid][$item['id']] = [
                        'data' => $item['data'],
                        'tpl_id' => $item['tpl_id']
                    ];
                }
            }
        }

        return [$layoutInfo, $layoutData, $uiInfo, $uiData];
    }

    /**
     * 模板预览图上传
     */
    public function picUpload()
    {
        $uploadPath = \yii::getAlias('@app/runtime');
        $uploadPath .= DIRECTORY_SEPARATOR . $this->Path;
        $upload = new Upload($uploadPath);
        $file = $upload->uploadS3();
        if (!$file) {
            return app()->helper->arrayResult(1, $file->error);
        }

        return app()->helper->arrayResult(0, 'success', [
            'url' => $file['url']
        ]);
    }

    /**
     * 校验用户是否有操作权限
     *
     * @param string $username 用户名
     *
     * @return boolean  true-有操作权限； false-无操作权限
     * @throws \Exception
     * @throws \Throwable
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
     * 根据siteCode获取适用范围
     *
     * @param string $siteCode 站点简称
     *
     * @return boolean  int
     */
    private function getRangeBySiteCode($siteCode)
    {
        $str = strstr($siteCode, '-');

        return isset($this->rangeSetting[$str]) ? $this->rangeSetting[$str] : 100;
    }
}
