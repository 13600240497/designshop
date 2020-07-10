<?php

namespace app\modules\component\dl\components;

use app\base\Upload;
use app\modules\common\dl\models\PageTplModel;
use app\modules\component\models\ComponentSiteRelationModel;
use app\modules\component\models\UiTplModel;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;

/**
 * 模块内管理组件
 */
class ManagerComponent extends Component
{
    /*
    *组件logo图片目录
     */
    private $logoPath = 'uploads/component/logo';

    //组件解析模板名
    private $tpl = 'index.twig';

    //组件模型
    private $componentModel;

    //激活的组件状态
    private $enableStatus = [3, 4];

    private $unableStatus = 5;

    //激活的组件审核状态
    private $enableVerify = [5, 6, 7];

    //激活的组件删除状态
    private $enableDel = 0;

    /**
     * 初始化组件类型
     *
     * @param int $type [组件类型]
     *
     * @return  bool
     */
    private function initType($type)
    {
        if (!in_array((int) $type, [1, 2])) {
            $this->errors = '组件类型错误';

            return false;
        }
        if ((int) $type === $this->type[1]['num']) {
            $this->componentModel = $this->layoutModel;
        } else {
            $this->componentModel = $this->uiModel;
        }

        return true;
    }

    /**
     * 新增组件初始化
     *
     * @param int $type [组件类型]
     * @param array $data [请求输入数据]
     *
     * @return  int id
     * @throws \yii\db\Exception
     * @throws JsonResponseException
     */
    public function add($type, $data)
    {
        if (app()->env->isLocal()) {
            throw new JsonResponseException($this->codeFail, '非正式环境关闭组件注册功能，具体流程见：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=86050773');
        }
        if (!$this->initType($type)) {
            return false;
        }
        if (!$this->validate($data) || !$this->checkSiteGroups($data)) {
            return false;
        }
        $maxId = $this->componentModel->maxId();
        if (!$maxId) {
            $maxId = 0;
        }
        $key = $this->type[ $type ]['key'];
        $this->componentModel->component_key = $key . sprintf("%06d", (int) $maxId + 1); //组件编码
        $this->componentModel->create_user = app()->user->username;
        $this->componentModel->save();
        if ($this->componentModel->id > 0) {
            $relation = ComponentSiteRelationModel::saveSiteRelation(
                $this->componentModel->id,
                $type,
                $this->componentModel->range,
                $data['siteGroups']
            );
            if (true !== $relation) {
                $this->errors = $relation;
                return false;
            }
            return ['id' => $this->componentModel->id];
        }

        return false;
    }

    /**
     * 编辑组件
     *
     * @param int $id [组件ID]
     * @param int $type [组件类型]
     * @param array $data [更新数据]
     *
     * @return  bool
     * @throws \yii\db\Exception
     */
    public function edit($id, $type, $data)
    {
        if (!$this->initType($type)) {
            return false;
        }
        if (!$this->checkSiteGroups($data)) {
            return false;
        }
        $component = $this->componentModel->query
            ->where(['a.id' => $id])
            ->one();
        if (!$component) {
            $this->errors = '数据不存在';
            return false;
        }

        if ((int) $type === 2) {
            $component->tpl_id = $data['tid'] ? $data['tid'] : $component->tpl_id;
            $component->need_navigate = $data['need_navigate'] ?? $component->need_navigate;
        }
        $component->name = $data['name'] ? $data['name'] : $component->name;
        $component->description = $data['description'] ? $data['description'] : $component->description;
        $component->logo_url = $data['logo_url'] ? $data['logo_url'] : $component->logo_url;
        $component->category_id = $data['category_id'] ? $data['category_id'] : $component->category_id;
        $component->range = $data['range'] ? $data['range'] : $component->range;
        $component->update_user = app()->user->username;
        if ($component->save()) {
            $relation = ComponentSiteRelationModel::saveSiteRelation(
                $id,
                $type,
                $component->range,
                $data['siteGroups']
            );
            if (true !== $relation) {
                $this->errors = $relation;
                return false;
            }
            return true;
        }

        $this->errors = '系统错误,稍后再试';
        return false;
    }

    /**
     * 组件列表
     *
     * @param $params type|类型、key|名称、place|使用环境、pageNo|页码、pageSize|页数
     *
     * @return array|bool
     */
    public function getList(array $params)
    {
        if (!$this->initType($params['type'])) {
            return false;
        }

        $list = $this->componentModel->getList($params);
        $list['list'] = $list['list'] ? ArrayHelper::toArray($list['list']) : [];

        $componentKeys = $list['list'] ? array_column($list['list'], 'component_key') : [];
        $tplList = $this->getComponentTplList($componentKeys, $params['status'] ?? 0);

        $this->buildComponentList($list['list'], $tplList);

        return $list;
    }

    /**
     * 获取组件的模板列表
     *
     * @param array $componentKeys
     * @param int   $status
     *
     * @return array
     */
    private function getComponentTplList(array $componentKeys, int $status)
    {
        if (empty($componentKeys)) {
            return [];
        }

        $tplList = UiTplModel::find()->where([
            'component_key' => $componentKeys,
            'is_delete'     => UiTplModel::NOT_DELETE
        ])->andFilterWhere($status ? ['status' => $status] : [])->all();

        return $tplList ? ArrayHelper::toArray($tplList) : [];
    }

    /**
     * 组装组件和组件模板数据
     *
     * @param array $componentList
     * @param array $tplList
     */
    private function buildComponentList(array &$componentList, array $tplList)
    {
        if (!empty($componentList) && !empty($tplList)) {
            $tplInComponentKey = [];
            foreach ($tplList as $tpl) {
                $tplInComponentKey[ $tpl['component_key'] ][] = $tpl;
            }

            foreach ($componentList as $key => $component) {
                $componentList[ $key ]['tplList'] = $tplInComponentKey[ $component['component_key'] ] ?? [];
            }
        }
    }

    /**
     * 数据合法验证
     *
     * @param  array $data [请求输入数据]
     *
     * @return bool
     */
    private function validate($data)
    {
        $this->componentModel->attributes = $data;
        if ($this->componentModel->validate()) {
            return true;
        }

        $this->errors = $this->componentModel->errors;
        return false;
    }

    /**
     * 检查站点参数
     * @param $data
     * @return bool
     */
    private function checkSiteGroups($data)
    {
        if (empty($data['siteGroups'])) {
            $this->errors = '所属站点(siteGroups)不能为空';
            return false;
        }

        return true;
    }

    /**
     * 获取可用组件列表
     *
     * @param array $types 组件适用范围
     * @param $place
     * @param $siteCode
     * @param $lang
     * @return array
     */
    public function getAvailableList($types, $place, $siteCode, $lang)
    {
        $filter = [
            'a.status'        => $this->enableStatus,
            'a.is_delete'     => $this->enableDel,
            'a.verify_status' => $this->enableVerify,
            'a.range'         => $types,
            'c.place'         => $place == 3 ? 1 : $place, //推广页组件使用活动的
            'csr.site_code'   => $siteCode
        ];

        $layoutList = $this->layoutModel->query
            ->leftJoin(
                ComponentSiteRelationModel::tableName().' as csr',
                'csr.component_id = a.id AND csr.type = ' . ComponentSiteRelationModel::TYPE_LAYOUT
            )
            ->where($filter)
            ->groupBy('a.id')
            ->orderBy('a.is_custom ASC, id ASC')
            ->all();

        $uiList = $this->uiModel->query
            ->leftJoin(
                ComponentSiteRelationModel::tableName().' as csr',
                'csr.component_id = a.id AND csr.type = ' . ComponentSiteRelationModel::TYPE_UI
            )
            ->where($filter)
            ->andWhere('a.tpl_id != 0')
            ->groupBy('a.id')
            ->orderBy('a.is_custom ASC, id ASC')
            ->all();

        $customLayout = $this->layoutModel->checkCustom($place, ComponentSiteRelationModel::TYPE_LAYOUT, $siteCode);

        //获取模板列表（private 我的模板， public 系统模板）
        $tplList = PageTplModel::getTemplateList($place, $types, $lang, $siteCode);

        return ['layout' => $layoutList, 'ui' => $uiList, 'custom' => $customLayout, 'template' => $tplList];

    }

    /**
     * 修改组件状态
     *
     * @param  string $key    [组件编码]
     * @param  int    $status [变更状态]
     *
     * @return bool
     */
    public function changeStatus($key, $status)
    {
        if (substr($key, 0, 1) === $this->type[1]['key']) {
            $this->componentModel = $this->layoutModel;
        } else {
            $this->componentModel = $this->uiModel;
        }
        $res = $this->componentModel->query
            ->where(['a.component_key' => $key])
            ->one();
        if (empty($res)) {
            $this->errors = '组件不存在';

            return false;
        }
        if (!$this->checkValid($res, $status)) {
            return false;
        }
        $res->status = $status;
        $res->update_user = app()->user->username;
        if ($res->save()) {
            return true;
        } else {
            $this->errors = '更新失败';

            return false;
        }
    }

    /**
     * 检测组件合法性
     *
     * @param array $info   [组件信息]
     * @param int   $status [更新状态]
     *
     * @return bool
     */
    private function checkValid($info, $status)
    {
        if ($info['status'] === (int) $status) {
            $this->errors = '状态已更新';

            return false;
        }
        if ((int) $status === $this->unableStatus) {
            return true;
        }
        if (isset($info['need_navigate'])) {//ui组件
            if ($info['tpl_id'] === 0) {
                $this->errors = '组件默认模板未设置';

                return false;
            }
            $this->currType = $this->type[2];
            $tplInfo = UiTplModel::findOne($info['tpl_id']);
            $path = $this->basedir . $this->currType['path'] . '/' . $info['component_key'] . '/' . $tplInfo['name_en'];
        } else {
            $this->currType = $this->type[1];
            $path = $this->basedir . $this->currType['path'] . '/' . $info['component_key'];
        }
        if (!is_file($path . '/' . $this->tpl)) {
            $this->errors = '组件文件不存在：' . $path . '/' . $this->tpl;

            return false;
        }

        return true;
    }

    /**
     * logo上传
     */
    public function logoUpload()
    {
        $uploadPath = \yii::getAlias('@app/runtime');
        $uploadPath .= DIRECTORY_SEPARATOR . $this->logoPath;
        $upload = new Upload($uploadPath);
        $file = $upload->uploadS3();
        if (!$file) {
            return app()->helper->arrayResult(1, $file->error);
        }

        return app()->helper->arrayResult(0, 'success', [
            'url' => $file['url']
        ]);
    }
}
