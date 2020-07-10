<?php

namespace app\modules\common\dl\components;

use app\modules\common\dl\models\{
    PageLayoutDataModel, PageUiDataModel, PageLayoutModel, PageUiModel, PageUiComponentDataModel
};

use app\modules\soa\components\IpsComponent;
use app\modules\component\dl\components\ExplainComponent;
use app\modules\common\dl\traits\CommonPublishTrait;
use app\modules\component\models\UiModel;
use app\modules\soa\components\ObsComponent;
use yii\base\Exception;
use Yii;

/**
 * activity模块基础组件
 */
class CommonCommonComponent extends Component
{
    use CommonPublishTrait;

    //layout组件
    const LAYOUT_COMPONENT = 1;

    //UI组件
    const LAYOUT_UI = 2;

    /**
     * next_id字段临时值，用来规避主键冲突的
     */
    const NEXT_ID_TEMP = -1;

    /**
     * next_id字段默认值
     */
    const NEXT_ID_DEFAULT = 0;

    /**
     * @var int 组件类型
     */
    public $componentType;

    /**
     * @var string 静态资源域名
     */
    public $staticDomain;

    /**
     * @var int 组件的页面位置ID
     */
    public $instanceId;

    /**
     * @var string 组件错误信息
     */
    public $errors;

    /**
     * @var array 组件解析数据
     */
    public $data;

    /**
     * @var string 组件编码
     */
    public $key;

    /**
     * @var string 组件解析语言
     */
    public $lang;

    /**
     * @var array 组件解析导航数据
     */
    public $navData;

    /**
     * @var string 自定义数据，包括（背景图片、背景颜色、自定义CSS）
     */
    public $customData;

    /** @var array 分享信息 */
    public $shareData;

    /**
     * @var array 组件布局设置数据
     */
    public $layoutData;

    /**
     * @var \app\modules\common\models\PageLayoutModel|\app\modules\common\models\PageUiModel 组件模型
     */
    public $model;

    /**
     * @var string 默认模板名称
     */
    public $tpl = 'index.twig';

    /**
     * @var bool 解析组件时是否需要返回静态JS和CSS
     */
    public $needStatic = false;

    /**
     * @var string 站点siteCode
     */
    public $siteCode;

    /**
     * @var int 活动ID
     */
    public $activityId;

    /**
     * @var int 页面ID
     */
    public $pageId;

    /**
     * @var bool 是否页面模板解析(页面模板解析有很多参数缺失，无需校验的)
     */
    public $isPageTplExplain = false;

    /**
     * @var array layout楼层信息
     */
    public $layoutList = [];

    /**
     * @var int layout组件ID（UI组件中有需要用到layoutId，故此处定义一个）
     */
    public $layoutId = 0;

    /**
     * @var array uiList（ui在当前layout内的序号list）
     */
    public $uiList = [];
    
    /**
     * @var int 是都node渲染
     */
    public $isVueSsr;
    
    /**
     * 初始化组件
     */
    public function init()
    {
        parent::init();
        $this->staticDomain = app()->params['s3PublishConf']['staticDomain']
            . app()->params['s3PublishConf']['staticKeyPre'];
        $this->customData = [
            'custom_css' => '',         //自定义CSS样式
            'background_color' => '',   //背景图片地址
            'background_img' => ''      //背景颜色
        ];
    }

    /**
     * 添加组件到页面
     * @param array $data
     * @param bool $runValidation
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function addComponent($data, $runValidation = true)
    {
        $this->model->load($data, '');
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            if ($this->model instanceof PageLayoutModel) {
                $this->model->lang = $data['lang'];
            } elseif (empty($this->model->tpl_id)) {
                //!!!如果是添加的ui组件且未设置模板，则设置默认的模板ID，这是为了防止后续模板ID为0引起的各种异常情况!!!
                $this->model->tpl_id = UiModel::getDefaultTplIdByKey($this->model->component_key);
            }
            $this->model->next_id = self::NEXT_ID_TEMP;
            if (!$this->model->save($runValidation)) {
                throw new Exception($this->model->flattenErrors(', '));
            }

            if (!empty($data['prev_id']) && (int)$data['prev_id']
                && !$this->updateNextId((int)$data['prev_id'], $this->model->id)) {
                throw new Exception('父节点的next_id字段更新异常');
            }

            $this->model->next_id = $data['next_id'];
            if (!$this->model->save($runValidation)) {
                throw new Exception($this->model->flattenErrors(', '));
            }

            if (!empty($data['layout_id'])) {
                //数据校验
                $checkRes = $this->checkUiIntegrityInLayout(0, $data['layout_id']);
                if (!$checkRes) {
                    throw new Exception('数据完整性验证失败');
                }
            }

            //存储Layout的自定义布局属性
            if (!empty($data['page_id']) && $this->componentType === static::LAYOUT_COMPONENT) {
                !$this->layoutData && $this->layoutData = [];
                $pageLayoutData = new PageLayoutDataModel();
                $pageLayoutData->component_id = $this->model->id;
                $pageLayoutData->data = json_encode($this->layoutData);
                $pageLayoutData->lang = $data['lang'];
                if (!$pageLayoutData->insert($runValidation)) {
                    throw new Exception('布局属性添加失败');
                }
            }

            $tr->commit();
            $this->instanceId = $this->model->id;
            $this->key = $this->model->component_key;
            return true;
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errors = $e->getMessage();
            return false;
        }
    }

    /**
     * 复制组件
     * @param array $data
     * @param bool $runValidation
     * @return bool
     * @throws \Throwable
     */
    public function copyComponent($data, $runValidation = true)
    {
        $this->model->load($data, '');
        $this->model->next_id = self::NEXT_ID_TEMP;
        if (!$this->model->save($runValidation)) {
            throw new Exception($this->model->flattenErrors(', '));
        }

        if (!empty($data['prev_id']) && (int)$data['prev_id']
            && !$this->updateNextId((int)$data['prev_id'], $this->model->id)) {
            throw new Exception('父节点的next_id字段复制异常');
        }

        $this->model->next_id = $data['next_id'];
        if (!$this->model->save($runValidation)) {
            throw new Exception($this->model->flattenErrors(', '));
        }

        $this->instanceId = $this->model->id;
        $this->key = $this->model->component_key;
        return true;
    }

    /**
     * 页面移动组件
     * @param array data 组件必须的数据
     * @param bool $runValidation
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function pageMoveComponent($data, $runValidation = true)
    {
        $oldLayoutId = 0;
        $compModel = $this->model::getById($data['id']);
        if (empty($compModel) || $compModel['lang'] !== $data['lang']) {
            $this->errors = '组件不存在';
            return false;
        }
        if (isset($data['position'])) {
            $oldLayoutId = $compModel->layout_id;
            $compModel->layout_id = $data['layout_id'];
            $compModel->position = $data['position'];
        }
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            $compModel->next_id = self::NEXT_ID_TEMP;
            if (!$compModel->save($runValidation)) {
                throw new Exception($compModel->flattenErrors(', '));
            }
            if (!$this->changeRelation($data)) {
                throw new Exception('节点移动失败');
            }
            $compModel->next_id = $data['next_id'];
            if (!$compModel->save($runValidation)) {
                throw new Exception($compModel->flattenErrors(', '));
            }

            if ($oldLayoutId) {
                //数据校验
                $checkRes = $this->checkUiIntegrityInLayout($oldLayoutId, $data['layout_id']);
                if (!$checkRes) {
                    throw new Exception('数据完整性验证失败');
                }
            }

            $tr->commit();
            return true;
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errors = $e->getMessage();
            return false;
        }
    }

    /**
     * 页面删除组件
     * @param $data
     * @param int $componentType 组件类型（1-Layout组件，2-UI组件）
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function pageRemoveComponent($data, $componentType)
    {
        $componentId = $data['id'];
        $compModel = $this->model::getById($data['id']);
        if (empty($compModel) || $compModel['lang'] !== $data['lang']) {
            $this->errors = '组件不存在';
            return false;
        }
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            //先删除当前节点
            if (!$compModel->delete()) {
                throw new Exception($compModel->flattenErrors(', '));
            }
            //再移动相关节点，把next_id替换成id
            $data['id'] = $data['next_id'];
            if (!$this->changeRelation($data)) {
                throw new Exception('节点移动失败');
            }

            if (isset($compModel->layout_id)) {
                //数据校验
                $checkRes = $this->checkUiIntegrityInLayout($compModel->layout_id, 0);
                if (!$checkRes) {
                    throw new Exception('数据完整性验证失败');
                }
            }

            if ($componentType === PageLayoutDataModel::COMPONENT_TYPE) {
                $uiComponentIds = PageUiModel::getUiIdsByLayoutId($componentId);
            } else {
                $uiComponentIds = [$componentId];
            }

            // 如果商品SKU来源选品系统，删除组件关联选品系统活动
//            $ipsComponent = new IpsComponent();
//            foreach ($uiComponentIds as $uiComponentId) {
//                $ipsComponent->delRelatedIpsActivityIfSkuFromIps($data['page_id'], $data['lang'], $uiComponentId);
//            }

            ObsComponent::deleteComponent($uiComponentIds,$data['page_id'],$data['lang']);//删除obs产品版块对应关系

            //删除组件数据
            if (!$this->deleteDataAfterDeleteComponent($componentId, $componentType)) {
                throw new Exception('组件数据删除失败');
            }

            $tr->commit();
            return true;
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errors = $e->getMessage();
            return false;
        }
    }

    /**
     * 组件删除后，删除组件的数据
     * @param int $componentId 组件ID
     * @param int $componentType 组件类型
     * @return bool
     */
    private function deleteDataAfterDeleteComponent($componentId, $componentType)
    {
        if ($componentType === PageLayoutDataModel::COMPONENT_TYPE) {
            $uiIds = PageUiModel::getUiIdsByLayoutId($componentId);
            if (false === PageLayoutDataModel::deleteAll(['component_id' => $componentId])
                || false === PageUiModel::deleteAll(['layout_id' => $componentId])
                || (!empty($uiIds) && false === PageUiComponentDataModel::deletePageUiComponentData($uiIds))
            ) {
                return false;
            }
        } else {
            if (false === PageUiComponentDataModel::deletePageUiComponentData($componentId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * UI组件关联关系变更(新增与移动)
     * @param  array $data 变更前后位置数据
     * @return bool 变更结果
     * @throws \Throwable
     * @throws \Exception
     */
    private function changeRelation($data)
    {
        $flag = true;
        if (isset($data['oldprev_id']) && (int)$data['oldprev_id'] !== 0) {
            $flag = $this->updateNextId($data['oldprev_id'], $data['oldnext_id']);
        }
        if ($flag && isset($data['prev_id']) && (int)$data['prev_id'] !== 0) {
            $flag = $this->updateNextId($data['prev_id'], $data['id']);
        }
        return $flag;
    }

    /**
     * 更新组件关联关系
     * @param  int $id 组件页面记录ID
     * @param  int $next_id 下一个组件记录ID
     * @param bool $runValidation
     * @return bool
     * @throws \Throwable
     * @throws \Exception
     */
    public function updateNextId($id, $next_id, $runValidation = true)
    {
        $compModel = $this->model::getById($id);
        $compModel->next_id = $next_id;
        if (!$compModel->update($runValidation)) {
            $this->errors = $compModel->flattenErrors(', ');
            return false;
        }
        return true;
    }

    /**
     * 检查Layout中ui组件的数据完整性
     * @param int $oldLayoutId 移动前layoutId
     * @param int $newLayoutId 移动后layoutId
     * @return bool
     */
    private function checkUiIntegrityInLayout($oldLayoutId, $newLayoutId)
    {
        if ($oldLayoutId && $positions = PageUiModel::getPositionsById($oldLayoutId)) {
            foreach ($positions as $position) {
                if (!$this->checkIntegrityByPosition($oldLayoutId, $position)) {
                    return false;
                }
            }
        }

        if ($newLayoutId && $newLayoutId !== $oldLayoutId && $positions = PageUiModel::getPositionsById($newLayoutId)) {
            foreach ($positions as $position) {
                if (!$this->checkIntegrityByPosition($newLayoutId, $position)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * checkIntegrityByPosition
     * @param int $layoutId layoutId
     * @param int $position 位置
     * @return bool
     */
    private function checkIntegrityByPosition($layoutId, $position)
    {
        $totalList = PageUiModel::find()->where([
            'layout_id' => $layoutId,
            'position' => $position
        ])->asArray()->all();
        $orderList = $this->getOrderedComponents($totalList);

        if (\count($totalList) !== \count($orderList)) {
            Yii::error('组件移动前的数据不完整', __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取组件渲染之后的html
     * @param  CommonCommonComponent $component 组件对象
     * @return string  html
     * @throws \Exception
     * @throws \yii\base\ViewNotFoundException
     */
    public function renderComponent($component)
    {
        $explainComponent = new ExplainComponent();
        if (!$componentHtml = $explainComponent->explainForTpl($component)) {
            $this->errors = $explainComponent->errors;
            return false;
        }
        // $componentHtml基本已定会有值，解析错误会有错误的html返回
        $this->errors = $explainComponent->errors;
        return $componentHtml;
    }
}
