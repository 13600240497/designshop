<?php

namespace app\modules\component\components;

use app\modules\common\components\CommonPageUiComponentDataComponent;
use app\modules\component\models\CategoryModel;
use app\modules\component\models\ComponentTplSiteRelationModel;
use app\modules\component\models\UiComponentLanguageRelationModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\base\Upload;
use ego\base\JsonResponseException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 组件模板管理
 */
class ComponentTplComponent extends Component
{
    //模板模型
    private $tplModel;
    
    //模板下线状态
    public $unableStatus = 0;
    
    //模板文件名
    private $tpl = 'index.twig';
    
    /*
    *模板图片目录
     */
    private $tplPath = 'uploads/component/tpl';
    
    /**
     * 新增组件模板
     *
     * @param array $params = [
     *                      string $key    [组件编码]
     *                      string $pic    [模板预览图]
     *                      string $name   [组件中文名称]
     *                      string $nameEn [组件英文名称]
     *                      ]
     *
     * @return bool|array
     * @throws \yii\db\Exception
     * @throws JsonResponseException
     */
    public function add(array $params)
    {
        if (app()->env->isLocal()) {
            throw new JsonResponseException($this->codeFail, '非正式环境关闭组件模板注册功能，具体流程见：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=86050773');
        }
        $this->tplModel = new UiTplModel();
        $data = [
            'component_key' => $params['key'],
            'pic'           => $params['pic'],
            'name'          => $params['name'],
            'name_en'       => $params['name_en'],
            'is_async'    => !empty($params['is_async']) ? $params['is_async'] : 0,
            'is_vue_ssr'    => !empty($params['is_vue_ssr']) ? $params['is_vue_ssr'] : 0
        ];
        if (false === ($ui = $this->checkComponentKey($data['component_key']))) {
            return false;
        }
        
        $data['place'] = (int) $ui->place; // 模板的place从组件中获取，否则会出现组件和模板的place不匹配的现象
        if (!$this->validate($data) || !$this->checkSiteGroups($params)) {
            return false;
        }
        $this->tplModel->create_user = app()->user->username;
        try {
            $this->tplModel->save();
        } catch (\Exception $e) {
            $this->errors = $e->getMessage();
            
            return false;
        }
        
        $relation = ComponentTplSiteRelationModel::saveSiteRelation(
            $this->tplModel->id,
            ComponentTplSiteRelationModel::TYPE_UI, // 目前只有UI组件有模板这一说法
            $ui->range,
            $params['siteGroups']
        );
        if (true !== $relation) {
            $this->errors = $relation;
            
            return false;
        }
    
        if (!empty($params['lang_keys'])) {
            $langKeys = explode(',', $params['lang_keys']);
            if (false === UiComponentLanguageRelationModel::saveLanguageRelation($langKeys, $this->tplModel->id)) {
                $this->errors = '绑定组件多语言key失败';
            
                return false;
            }
        }
        
        return ['id' => $this->tplModel->id];
    }
    
    /**
     * 编辑组件模板
     *
     * @param array $params = [
     *                      int   $id   [分类ID]
     *                      string $name [组件中文名称]
     *                      string $name_en [组件英文名称]
     *                      string $pic [模板预览图]
     *                      ]
     *
     * @return  bool
     * @throws \yii\db\Exception
     */
    public function edit(array $params)
    {
        $this->tplModel = UiTplModel::findOne($params['id']);
        if (!$this->tplModel) {
            $this->errors = '数据不存在';
            
            return false;
        }
        if (!$this->checkSiteGroups($params) || false === ($ui = $this->checkComponentKey($this->tplModel->component_key))) {
            return false;
        }
        
        $this->tplModel->name = $params['name'] ?: $this->tplModel->name;
        $this->tplModel->name_en = $params['name_en'] ?: $this->tplModel->name_en;
        $this->tplModel->pic = $params['pic'] ?: $this->tplModel->pic;
        $this->tplModel->is_async = isset($params['is_async']) ? intval($params['is_async']) : intval($this->tplModel->is_async);
        $this->tplModel->is_vue_ssr = isset($params['is_vue_ssr']) ? intval($params['is_vue_ssr']) : intval($this->tplModel->is_vue_ssr);
        $this->tplModel->update_user = app()->user->username;
        
        try {
            $this->tplModel->save();
        } catch (\Exception $e) {
            $this->errors = $e->getMessage();
            
            return false;
        }
        
        $relation = ComponentTplSiteRelationModel::saveSiteRelation(
            $this->tplModel->id,
            ComponentTplSiteRelationModel::TYPE_UI, // 目前只有UI组件有模板这一说法
            $ui->range,
            $params['siteGroups']
        );
        if (true !== $relation) {
            $this->errors = $relation;
            
            return false;
        }
        
        if (!empty($params['lang_keys'])) {
            $langKeys = explode(',', $params['lang_keys']);
            if (false === UiComponentLanguageRelationModel::saveLanguageRelation($langKeys, $params['id'])) {
                $this->errors = '绑定组件多语言key失败';
                
                return false;
            }
        }
        
        // 删除config配置的缓存
        $redisKeys = $this->getAllConfigKey((int) $params['id']);
        app()->redis->del(...$redisKeys);
        
        return true;
    }
    
    /**
     * 获取组件config的所有缓存key
     *
     * @param int $id
     *
     * @return array
     */
    private function getAllConfigKey(int $id)
    {
        return [
            app()->redisKey->getUiConfigRedisKey($id, 'fields'),
            app()->redisKey->getUiConfigRedisKey($id, 'languages')
        ];
    }
    
    /**
     * 获取组件config的所有字段field
     *
     * @return array
     */
    private function getAllConfigField()
    {
        return [
            'fields',
            'languages'
        ];
    }
    
    /**
     * 组件模板列表
     *
     * @param array $params = [
     *                      int    $pageNo   [页码]
     *                      int    $pageSize [每页数]
     *                      string $key      [组件编码]
     *                      int    $status   [组件状态]
     *                      string $name     [模板名称]
     *                      ]
     *
     * @return array
     */
    public function getList(array $params)
    {
        $this->tplModel = new UiTplModel();
        
        return $this->tplModel->tplList($params);
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
        $this->tplModel->attributes = $data;
        if ($this->tplModel->validate()) {
            return true;
        }
        
        $this->errors = $this->tplModel->errors;
        
        return false;
    }
    
    /**
     * 检查站点参数
     *
     * @param $data
     *
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
     * 检查组件key参数
     *
     * @param $data
     *
     * @return UiModel|bool|null
     */
    private function checkComponentKey($componentKey)
    {
        if (empty($componentKey)) {
            $this->errors = '组件key不能为空';
            
            return false;
        }
        
        $ui = UiModel::find()->alias('u')->select('u.*, c.place')
            ->leftJoin(CategoryModel::tableName() . ' as c', 'u.category_id = c.id')
            ->where([
                'u.component_key' => trim($componentKey),
                'u.is_delete'     => UiModel::NOT_DELETE
            ])->one();
        if (!$ui) {
            $this->errors = '组件key对应的组件不存在或已被删除';
            
            return false;
        }
        
        return $ui;
    }
    
    /**
     * 下线组件所有模板
     *
     * @param string $key
     *
     * @throws Exception
     */
    public function offlineComponentAllTpl($key)
    {
        $tplModelList = UiTplModel::getComponentAllTpl($key);
        if ($tplModelList) {
            foreach ($tplModelList as $tplModel) {
                $tplModel->status = UiTplModel::STATUS_NOT_USED;
                $tplModel->update_user = app()->user->username;
                if (!$tplModel->save()) {
                    throw new Exception('修复模板状态失败!');
                }
            }
        }
    }
    
    /**
     * 修改模板状态
     *
     * @param  int $id     [模板ID]
     * @param  int $status [变更状态]
     *
     * @return bool
     */
    public function changeStatus($id, $status)
    {
        $this->tplModel = UiTplModel::findOne($id);
        if (!$this->tplModel) {
            $this->errors = '数据不存在';
            
            return false;
        }
        
        if (!$this->checkValid($status)) {
            return false;
        }
        
        $managerComponent = new ManagerComponent();
        // 只有在有上线模板的情况下，才检测组件是否上线。新增组件问题，上线需要默认模板
        if ($managerComponent->hasOnlineTpl($this->tplModel->component_key)) {
            if (!$managerComponent->isOnline($this->tplModel->component_key)) {
                $this->errors = '所属组件没有上线，请先上线组件！';
                
                return false;
            }
        }
        
        $this->tplModel->status = $status;
        $this->tplModel->update_user = app()->user->username;
        if ($this->tplModel->save()) {
            return true;
        }
        
        $this->errors = '更新失败';
        
        return false;
    }
    
    /**
     * 检测模板合法性
     *
     * @param object $info   [组件信息]
     * @param int    $status [更新状态]
     *
     * @return bool
     */
    private function checkValid($status)
    {
        if (empty($this->tplModel)) {
            $this->errors = '组件不存在';
            
            return false;
        }
        if ($this->tplModel->status === (int) $status) {
            $this->errors = '状态已更新';
            
            return false;
        }
        if ((int) $status === $this->unableStatus) {
            return true;
        }

        // APP原生组件模板上线不用检查组件文件是否存在
        $uiModel = UiModel::find()->where(['component_key' => $this->tplModel->component_key])->one();
        if ($uiModel && $uiModel->range === 4) {
            return true;
        }

        $this->currType = $this->type[2];
        $path = $this->basedir . $this->currType['path'] . '/' . $this->tplModel->component_key . '/' .
            $this->tplModel->name_en;
        if (!is_file($path . '/' . $this->tpl)) {
            $this->errors = '组件文件不存在';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 图片上传
     */
    public function picUpload()
    {
        $uploadPath = \yii::getAlias('@app/runtime');
        $uploadPath .= DIRECTORY_SEPARATOR . $this->tplPath;
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
     * 删除组件模板
     *
     * @param string $ids
     *
     * @return bool
     */
    public function delete(string $ids)
    {
        if (empty($ids)) {
            $this->errors = '模板ID不能为空';
            
            return false;
        }
        
        $nodes = explode(',', $ids);
        $defaultTpl = UiModel::find()->where([
            'is_delete' => UiModel::NOT_DELETE,
            'tpl_id'    => $nodes
        ])->asArray()->all();
        $defaultTpl && $defaultTpl = array_column($defaultTpl, 'tpl_id');
        
        $numError = $defaultError = [];
        foreach ($nodes as $item) {
            !is_numeric($item) && $numError[] = $item;
            \in_array($item, $defaultTpl, false) && $defaultError[] = $item;
        }
        
        if (!empty($numError)) {
            $this->errors = implode(',', $numError) . ' 必须为数字或数字字符串';
            
            return false;
        }
        if (!empty($defaultError)) {
            $this->errors = '被设置为默认模板的无法直接删除，请先解除默认设置';
            
            return false;
        }
        
        if (UiTplModel::updateAll(['is_delete' => 1], ['id' => $nodes])) {
            return true;
        }
        
        $this->errors = '删除失败';
        
        return false;
    }
    
    /**
     * 获取组件配置(当不使用缓存时，会用查询到的值刷新缓存)
     *
     * @param int $id    组件模板ID
     * @param int $cache 是否使用缓存，1-使用，0-不使用
     *
     * @return array
     */
    public function getConfig(int $id, int $cache)
    {
        $data = [];
        $fields = $this->getAllConfigField();
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $data[ $field ] = CommonPageUiComponentDataComponent::getConfig($id, $field, !(bool) $cache);
            }
        }
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
    }
}
