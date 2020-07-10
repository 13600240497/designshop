<?php

namespace app\modules\common\zf\components;

use app\modules\common\zf\models\{
    PageGroupModel,
    PageSyncPlatformWaitDataModel,
    PageUiDataModel,
    PageUiModel,
    PageUiComponentDataModel,
    GoodsManageDataBlockModel
};

use app\modules\component\models\UiTplModel;
use app\modules\soa\components\IpsComponent;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 页面内容组件管理
 *
 */
class CommonUi extends CommonCommonComponent
{
    //所在布局组件位置ID
    public $layout_id;
    
    //UI组件默认icon图片路径
    private $imageDefaultDir = '/resources/images/default';
    
    //UI组件默认icon图片扩展名
    private $imageDefaultExt = ['png', 'jpg', 'jpeg', 'gif'];
    
    //UI组件默认icon图片配置缓存时间|5分钟
    private $imageDefaultCacheExpireTime = 300;
    
    //UI组件默认数据
    public $default;
    
    //UI组件模板ID
    public $tplId;
    
    /**
     * 初始化组件类
     *
     */
    public function init()
    {
        parent::init();
        $this->componentType = static::LAYOUT_UI;
        $this->model = new PageUiModel();
        
        // 设置默认值
        $this->default = [
            'lazyWidth'     => '',//前端懒加载时默认图片属性（此处只是占位，具体赋值在ExplainComponent->getData()方法中）
            'lazyHeight'    => '',
            'initialWidth'  => '',
            'initialHeight' => '',
            'lazyImg'       => '',
        ];
        // 默认图片放在前，防止覆盖后面的内容
        $this->default = array_merge($this->getDefaultImageConfig(), $this->default);
    }
    
    /**
     * 获取默认图片的配置
     *
     * @return array
     */
    private function getDefaultImageConfig()
    {
        $key = app()->redisKey->getDefaultImageRedisKey();
        if (!empty($images = app()->redis->get($key))) {
            return json_decode($images, true);
        }
        
        $images = [];
        $dir = app()->basePath . '/htdocs' . $this->imageDefaultDir;
        
        if (is_dir($dir) && ($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..' && !is_dir($file)) {
                    $pathinfo = pathinfo($file);
                    if (\in_array($pathinfo['extension'], $this->imageDefaultExt, true)) {
                        $images[ $pathinfo['filename'] ] = $this->staticDomain . $this->imageDefaultDir
                            . '/' . $pathinfo['basename'];
                    }
                }
            }
            closedir($handle);
        }
        
        if (!empty($images)) {
            app()->redis->setex($key, $this->imageDefaultCacheExpireTime, json_encode($images));
        }
        
        return $images;
    }
    
    /**
     * 保存表单数据
     *
     * @param  array $data 组件必须的数据
     * @param bool   $runValidation
     *
     * @return string  html
     * @throws Exception
     */
    public function saveFormData($data, $runValidation = true)
    {
        $uiModel = $this->model::getById($data['id']);
        if (empty($uiModel)) {
            $this->errors = '组件不存在';
            
            return false;
        }
        
        $uiData = array_merge(json_decode($data['private_data'], true), json_decode($data['public_data'], true));
        $uiData = array_map(function ($item) {
            if (is_string($item) && json_decode($item)) {
                $item = is_array(json_decode($item, true)) || trim($item, '"') == "null"? json_decode($item, true) : $item;
            }
            
            return $item;
        }, $uiData);
        
        // 如果商品SKU来源选品系统,读取选品系统活动SKU不设置
        $ipsComponent = new IpsComponent();
        $ipsPageInfo = [
            'siteCode' => $data['site_code'],
            'pageId'   => $data['page_id'],
            'lang'     => $data['lang'],
            'pipeline' => $data['pipeline'],
        ];
        $ipsUiInfo = ['id' => $uiModel->id, 'key' => $uiModel->component_key];
        $ipsComponent->tryFillUiComponentSaveFormSkuFieldDataFromIps($ipsPageInfo, $ipsUiInfo, $uiData);
        
        //获取商品标题组合组件配置数据
        $configUiData = GoodsManageDataBlockModel::getGoodsTitleCompositeComponentConfigData($data['id'], $data['lang']);
        if (!empty($configUiData)) {
            $uiData = array_merge($uiData, $configUiData);
        }
        if (!empty($uiModel->bind_relation)) {
            list($uiData, $saveData) = $this->filterSaveFormField($uiModel->id, $uiModel->lang, $uiModel->tpl_id, $uiData);
        }
        
        //开启事物
        $tr = app()->db->beginTransaction();
        try {
            if (false === PageUiModel::updateAll(['tpl_id' => $data['tpl_id']], 'id=' . $data['id'])) {
                throw new Exception('ui組件操作失敗');
            }
            
            $pageUiData = new CommonPageUiComponentDataComponent();
            if (!$pageUiData->insertPageUiComponentData($data['id'], $uiData, $data['tpl_id'], $data['lang'])) {
                throw new Exception('ui组件数据操作失败');
            }
            $tr->commit();
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errors = $e->getMessage();
            
            return false;
        }
        
        $this->lang = $data['lang'];
        $this->instanceId = $data['id'];
        $this->key = $uiModel->component_key;
        $this->data = $saveData ?? $uiData;
        $this->tplId = $data['tpl_id'];
        
        return true;
        
    }
    
    /**
     * 过滤已经绑定的组件的字段
     *
     * @param $uiId
     * @param $tplId
     * @param $uiData
     *
     * @return array
     */
    private function filterSaveFormField($uiId, $lang, $tplId, $uiData)
    {
        $filterConfig = app()->params['site'][ SITE_GROUP_CODE ]['componentFilterField'];
        $tplInfo = UiTplModel::getTplFullInfo($tplId);
        $tplInfo = ArrayHelper::toArray($tplInfo);
        $filterField = $filterConfig[ $tplInfo['component_key'] ][ $tplInfo['id'] ];
        $returnData = $uiData;
        foreach ($uiData as $key => $data) {
            if (in_array($key, $filterField)) {
                unset($uiData[ $key ]);
                $returnData[ $key ] = PageUiComponentDataModel::getPublicFieldValue($uiId, $lang, $key);
            }
        }
        
        return [$uiData, $returnData];
    }
    
    /**
     * 复制UI组件
     *
     * @param      $data
     * @param bool $runValidation
     *
     * @return bool|int
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function copyUi($data, $runValidation = true)
    {
        /** @var \app\modules\common\zf\models\PageUiModel $copyUi */
        $copyUi = PageUiModel::findOne($data['copy_id']);
        if (!$copyUi) {
            throw new Exception('copy_id未找到对应的UI组件');
        }
        if ($data['prev_id']) {
            /** @var \app\modules\common\models\PageUiModel $prevInfo */
            $prevInfo = PageUiModel::findOne($data['prev_id']);
            if ($prevInfo->position !== (int) $data['position'] || $prevInfo->layout_id !== (int) $data['layout_id']) {
                throw new Exception('组件位置信息错误');
            }
        }

        $data['async_data_format'] = $copyUi->async_data_format;
        $data['component_key'] = $copyUi->component_key;
        $data['tpl_id'] = $copyUi->tpl_id;
        if (!$this->copyComponent($data, $runValidation)) {
            return false;
        }
        
        if (!$this->copyUiData($copyUi->id, $this->instanceId)) {
            return false;
        }
        
        // 如果商品SKU来源选品系统,复制关联选品活动信息
        $ipsComponent = new IpsComponent();
        $ipsPageInfo = [
            'siteCode' => $this->siteCode,
            'pageId'   => $this->pageId,
            'lang'     => $this->lang,
            'pipeline' => $this->pipeline,
        ];
        $ipsComponent->copyRelatedIpsActivityIfSkuFromIps($ipsPageInfo, $copyUi->id, $this->instanceId);
        
        $this->lang = $data['lang'];
        $uiArr = PageUiComponentDataModel::getUiComponentFullData($this->instanceId, $data['lang'], $copyUi->tpl_id);
        if (!empty($uiArr)) {
            $uiArr = $uiArr[ $this->instanceId ];
            $this->data = $uiArr['data'];
            $this->data['need_navigate'] = $uiArr['need_navigate'] ?? '0';//标识ui组件是否需要导航
            $this->tplId = $copyUi->tpl_id;
        }
        
        return true;
    }
    
    /**
     * 复制UI组件数据
     *
     * @param int $fromUiId 源UI组件ID
     * @param int $toUiId   目标UI组件ID
     *
     * @return bool
     * @throws Exception
     */
    private function copyUiData($fromUiId, $toUiId)
    {
        return PageUiComponentDataModel::copyPageUiComponentData($fromUiId, $toUiId);
    }
    
    /**
     * 取消组件数据三端绑定
     *
     * @param int    $uiId
     * @param string $lang
     */
    public function cancelUiComponentBindRelation(int $uiId, string $lang)
    {
        $result = PageUiModel::find()->select('bind_relation')
            ->where(['id' => $uiId, 'lang' => $lang])
            ->asArray()
            ->one();
        if (!empty($result['bind_relation'])) {
            PageUiModel::updateAll(['bind_relation' => ''], ['id' => explode(',', $result['bind_relation'])]);
        }
    }
    
    public function delUserData($uiId)
    {
        $page_id = $this->pageId;
        $page_group_id = PageGroupModel::getPageGroupIdByPageId($page_id);
        //拿出数据对照
        if (!empty($page_group_id)) {
            $page_sync_platform_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id' => $page_group_id]);
            if(!empty($page_sync_platform_data)){
                $form_data = empty($page_sync_platform_data->form_data) ? [] : json_decode($page_sync_platform_data->form_data, 1);
                $data = [];
                foreach ($form_data as $form_datum) {
                    $ui_id = json_decode($form_datum['ui_id'], 1);
                    $pc = empty($ui_id['pc']) ? [] : array_values($ui_id['pc']);
                    $m = empty($ui_id['m']) ? [] : array_values($ui_id['m']);
                    $app = empty($ui_id['app']) ? [] : array_values($ui_id['app']);
                    $ui_ids = array_merge($pc, $m);
                    $ui_ids = array_merge($ui_ids, $app);

                    if (!in_array($uiId, $ui_ids)) {
                        $data[] = $form_datum;
                    }
                }
                if (empty($data)) {
                    //如果已经没有交集了，则删除本条记录
                    $page_sync_platform_data->delete();
                } else {
                    $page_sync_platform_data->form_data = json_encode($data);
                    $page_sync_platform_data->save();
                }
            }
        }
    }
}
