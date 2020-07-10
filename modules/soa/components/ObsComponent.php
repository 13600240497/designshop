<?php
namespace app\modules\soa\components;

use app\base\SiteConstants;
use app\base\SitePlatform;

use yii\helpers\ArrayHelper;
use app\modules\soa\models\SoaObsGoodsModel;
use app\modules\common\models\PageModel;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\soa\components\GearbestComponent;

/**
 * OBS系统组件
 *
 * @property \app\services\soa\ObsService $ObsService
 *
 * @author chenll
 * @since 1.6.2
 */
class ObsComponent extends Component
{
     /** @var array 商品列表tab组件key(PC,M) */
    const GOODS_LIST_TAB_COMPONENT_KEYS = ['U000041'];
    /**
     * 获取obs版块产品
     *
     * @param int $section_id 		版块ID
     * @return array 商品SKU信息 商品SKU列表

     * @throws \ego\base\JsonResponseException
     */
    public function getGoodsList($section_id)
    {

        $result = $this->getProductList(['section_id' => $section_id]);
        if (empty($result) || !isset($result['data'])) {
            throw $this->newJsonException('OBS系统版块产品不存在');
        }

        $goods = [];
        if(!empty($result['data']['data'])){
            foreach($result['data']['data'] as $row){
                $goods[] = $row['good_sn'].'_'.$row['warehouse_code'];
            }
        }

        return [ 
            join(',',$goods),
            !empty($result['data']['update_time']) ? $result['data']['update_time'] : 0
        ];
    }

    /**
     * OBS - 接口 - 获取主题
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getThemeList()
    {
        $result = $this->ObsService->slient()->asArray()->getThemeList();
        $this->checkIpsApiStandardResponse($result);
        return $this->jsonSuccess($result['data']);
    }

    /**
     * OBS - 接口 - 获取主题下页面
     *
     * @param array $params get参数
     * - theme_id    主题ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getPageList($params)
    {
        $rules = [
            ['theme_id', 'require', '主题ID不能为空'],
            ['theme_id', 'number', '主题ID为数字类型'],
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'theme_id' => $params['theme_id']
        ];
        $result = $this->ObsService->slient()->asArray()->getPageList($apiParams);
        $this->checkIpsApiStandardResponse($result);
        $pages = SoaObsGoodsModel::getPageList($params);
        
        if(!empty($pages)){
            $selected = [];
            foreach($pages as $value){
                $selected[] = $value['page_id'];
            }
            foreach($result['data'] as $key=>$row){
                if(in_array($row['id'], $selected)){
                    unset($result['data'][$key]);
                }
            }
        }
        
        return $this->jsonSuccess($result['data']);
    }

    /**
     * OBS - 接口 - 获取版块
     *
     * @param array $params get参数
     * - page_id    页面ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getSectionList($params)
    {
        $data = [];
        $rules = [
            [
                'page_id',
                ['require', 'number'],
                ['require' => '页面ID不能为空', 'number' => '页面ID为数字类型']
            ]
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'page_id' => $params['page_id']
        ];
        $params['uiId'] = !empty($params['uiId']) ? intval($params['uiId']) : '';
        $result = $this->ObsService->slient()->asArray()->getSectionList($apiParams);
        if(empty($result['data'])){
            return $this->jsonSuccess([]);
        }
        $this->checkIpsApiStandardResponse($result);
        $sections = SoaObsGoodsModel::getSectionList($params);
        $selected = [];
        if(!empty($sections)){
            foreach($sections as $value){
                $selected[] = $value;
            }
        }
        foreach($result['data'] as $key=>$row){
            if(!in_array($row['id'], $selected)){
                $data[] = $row;
            }
        }

        return $this->jsonSuccess($data);
    }

    /**
     * OBS - 接口 - 获取版块已选SKU
     *
     * @param array $params get参数
     * - section_id    版块ID
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function getProductList($params)
    {
        $rules = [
            ['section_id', 'require', '版块ID不能为空'],
            ['section_id', 'number', '版块ID为数字类型'],
        ];
        $this->checkRequestParams($params, $rules);

        $apiParams = [
            'section_id' => $params['section_id']
        ];
        $result = $this->ObsService->slient()->asArray()->getProductList($apiParams);
        $this->checkIpsApiStandardResponse($result);

        return $this->jsonSuccess($result['data']);
    }

    /**
     * 选品系统API接口返回检查
     * @param array $result
     * @throws \ego\base\JsonResponseException
     * @return void
     */
    protected function checkIpsApiStandardResponse($result)
    {
        if (empty($result['data'])) {
            throw $this->newJsonException('活动不存在');
        }
        $this->checkApiStandardResponse($result, 'OBS系统');
    }

    /**
     * mq更新版块ID下的产品
     * @param   int   $section_id   版块ID
     * @return bool
     * @throws \ego\base\JsonResponseException
     */
    public function updateMqGoodData($section_id)
    {
        $sections = SoaObsGoodsModel::find()
            ->where(['section_id' => $section_id])
            ->all();

        if(empty($sections)){
            return true;
        }
        list($goodsSkuList,$lastUpdateTime) = $this->getGoodsList($section_id);
        //获取关联页面信息
        $pageIds = array_unique(array_column(ArrayHelper::toArray($sections), 'pid'));
        $pageModelMap = PageModel::find()->where([
            'is_delete' => PageModel::NOT_DELETE,
            'id'        => $pageIds
        ])->indexBy('id')->asArray()->all();
        
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            foreach ($sections as $key => $value) {
                //如最后更新时间大于等于obs系统活动SKU最后更新时间，表示SKU已经为最新的，不用处理
                if ($value->last_update_time >= $lastUpdateTime) {
                    continue;
                }
                $pageModel = $pageModelMap[$value->pid];
                if (!$pageModel) {//页面被删除
                    continue;
                }
                
                //检查商品是否可用
                try{
                    $goodsList = (new GearbestComponent())->getBaseGoodsList([
                        'sku' => $goodsSkuList,
                        'type' => 0,
                        'pipeline'=> $value->pipeline,
                        'platforms' => SitePlatform::getPlatformCodeByPlatformType($value->platform),
                        'siteCode' => $value->site_code
                    ]);
                    $goodsSkuList = !empty($goodsList) ? str_replace('#','_',join(',',array_column($goodsList, 'goodsId'))) : '';
                }catch (\Exception $e) {
                    $goodsSkuList = '';
                }

                //记录最新SKU信息
                $value->goods_sku = $goodsSkuList;
                $value->last_update_time = $lastUpdateTime;
                if(!$value->update(true)){
                    return false;
                }
                //更新组件里的产品
                PageUiComponentDataModel::setPublicFieldValue(
                    $value->component_id,
                    $value->lang,
                    PageUiComponentDataModel::KEY_SKU,
                    $goodsSkuList
                );

            }
            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $this->newJsonException($e->getMessage());
        }
        return true;
    }

    /**
     * 删除版块组件关联记录
     * @param       array    $components    组件id
     * @param       int      $page_id       页面ID
     * @param       string   $lang          语言
     * @return      bool
     */
    public static function deleteComponent($components,$page_id,$lang)
    {
        $where = '';
        if(empty($components)){
            return false;
        }
        if (is_numeric($components)) {
            $where .= '`component_id`=' . $components ;
        } elseif (is_array($components)) {
            $where .= '`component_id` IN (' . implode(',', $components) . ')';
        } else {
            return false;
        }
        if(!empty($page_id)){
            $where .= ' and `pid` = '.$page_id;
        }
        if(!empty($lang)){
            $where .= ' and `lang` = "'.$lang.'"';
        }
        $list = SoaObsGoodsModel::find()->where($where)->all();
        if(!empty($list)){
            foreach ($list as  $value) {
                $value->section_id = '';
                $value->section_name = '';
                $value->component_id = '';
                $value->component_key = '';
                $value->update(true);
            }
        }
        return true;
    }

    /**
     * 填充UI组件设计页面表单提交数据中的SKU
     * @param int $pageId 活动页面ID
     * @param string $lang 语言简码
     * @param int $componentId UI组件ID
     * @param string $componentKey 组件key
     * @param array $fieldData UI组件数据
     * @throws
     * @return  bool
     */
    public function saveFormData($pageId, $lang, $componentId, $componentKey, &$fieldData)
    {
        
        if(in_array($componentKey, self::GOODS_LIST_TAB_COMPONENT_KEYS)){//商品列表tab组件
            return true;
        }else{
            
            if (isset($fieldData['goodsDataFrom'], $fieldData['obsId'])) {

                if((SiteConstants::GOODS_SKU_FROM_OBS === (int)$fieldData['goodsDataFrom'])){
                    $soaObsGoodsModel = SoaObsGoodsModel::find()
                    ->where([
                        'pid'           => $pageId,
                        'lang'          => $lang,
                        'component_id'  => $componentId
                    ])->one();
                    //保存组件版块关系表

                    $this->updateComponentData($fieldData['obsId'],$pageId, $lang,
                        $componentId, $componentKey,$fieldData['obsName'],$fieldData);

                    //删除已取消的关联
                    if (!empty($soaObsGoodsModel->section_id) && ($soaObsGoodsModel->section_id  != $fieldData['obsId'])) {
                        SoaObsGoodsModel::deleteAll([
                            'section_id'        => $soaObsGoodsModel->section_id,
                            'pid'               => $pageId,
                            'lang'              => $lang,
                            'component_id'      => $componentId
                        ]);
                    }
                }else{//切换数据来源 删除板块关系

                    $detail = SoaObsGoodsModel::find()
                    ->where([
                        'pid'           => $pageId,
                        'lang'          => $lang,
                        'component_id'  =>$componentId,
                    ])->one();
                    if(!empty( $detail)){
                        $detail->component_id = '';
                        $detail->section_name = '';
                        $detail->section_id = '';
                        $detail->component_key = '';
                        $detail->goods_sku = '';
                        $detail->last_update_time = '';
                        if(!$detail->update(true)){
                            throw $this->newJsonException($detail->flattenErrors(', '));
                        }
                    }
                }        
            }
        }
        return true;
    }

    /**
     * 保存页面
     * @param   array       $params
     *          int         $pid            页面ID
     *          string      $lang           语言
     *          int         $page_id        obs页面ID
     *          string      $page_name      obs页面名称
     *          int         $platform       设备平台
     *          int         $activity_id    活动ID
     *          string      $site_code       站点
     *          string      $pipeline       渠道
     * @throws \ego\base\JsonResponseException
     * @return bool
     */
    public function savePageData($params)
    {
        list($pid, $lang, $page_id, $page_name, $platform, $activity_id, $site_code, $pipeline) = $params;
        $data = SoaObsGoodsModel::find()->where(['pid'=>$pid,'lang'=>$lang,'site_code'=>$site_code,'pipeline'=>$pipeline])
            ->asArray()->all();
        if(empty($data) && empty($page_id)){
            return true;
        }
        $activity   = SoaObsGoodsModel::find()
        ->where(['activity_id'=>$activity_id])
        ->select('*')->asArray()->one();
        if(empty($activity)){
            throw $this->newJsonException('请先选择活动');
        }
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            if(empty($data)){
                if(empty($page_id)){
                    return true;
                }
                SoaObsGoodsModel::deleteAll(['theme_id'=>$activity['theme_id'],'activity_id'=>$activity_id,'page_id'=>'']);  
                $goodsModel = new SoaObsGoodsModel();
                $goodsModel->pid = $pid;
                $goodsModel->theme_id = $activity['theme_id'];
                $goodsModel->theme_name = $activity['theme_name'];
                $goodsModel->page_id = $page_id;
                $goodsModel->page_name = $page_name;
                $goodsModel->lang = $lang;
                $goodsModel->platform = $platform;
                $goodsModel->activity_id = $activity_id;
                $goodsModel->site_code = $site_code;
                $goodsModel->pipeline = $pipeline;
                if (!$goodsModel->save(true)) {
                    throw $this->newJsonException($goodsModel->flattenErrors(', '));
                }  
            }else{
                $update = false;
                foreach ($data as  $row) {
                    if($page_id != $row['page_id']){ //更新了页面 需要删除组件产品

                        if(!empty($row['component_id']) && !empty($row['lang'])){
                            //更新组件里的产品为空
                            PageUiComponentDataModel::setPublicFieldValue(
                                $row['component_id'],
                                $row['lang'],
                                PageUiComponentDataModel::KEY_SKU,
                                ''
                            );
                        }
                        $update = true;      
                    }
                }
                if($update === true){
                    SoaObsGoodsModel::deleteAll(['pid'=>$pid,'lang'=>$lang]);
                    $goodsModel = new SoaObsGoodsModel();
                    $goodsModel->pid = $pid;
                    $goodsModel->theme_id = $activity['theme_id'];
                    $goodsModel->theme_name = $activity['theme_name'];
                    $goodsModel->page_id = !empty($page_id) ?? '';
                    $goodsModel->page_name = !empty($page_name) ?? '';
                    $goodsModel->lang = $lang;
                    $goodsModel->platform = $platform;
                    $goodsModel->activity_id = $activity_id;
                    $goodsModel->site_code = $site_code;
                    $goodsModel->pipeline = $pipeline;
                    if (!$goodsModel->save(true)) {
                        throw $this->newJsonException($goodsModel->flattenErrors(', '));
                    }
                }  
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $this->newJsonException($e->getMessage());
        }
        return true;
    }


    /**
     * 保存活动
     * @param   int     $theme_id       obs主题ID
     * @param   int     $activity_id    活动ID
     * @param   string  $theme_name     活动名称
     * @throws \ego\base\JsonResponseException
     * @return bool
     */
    public function saveActivity($theme_id,$activity_id,$theme_name)
    {
        $data = SoaObsGoodsModel::find()
            ->where(['activity_id'=>$activity_id])
            ->asArray()->all();
        $transaction = app()->db->beginTransaction();
        try {    
            if(!empty($data)){//活动已经绑定
                $update = false;
                foreach($data as $row){
                    if($row['theme_id'] != $theme_id){ //更新主题
                        //更新组件里的产品
                        if(!empty($row['component_id']) && !empty($row['lang'])){
                            PageUiComponentDataModel::setPublicFieldValue(
                                $row['component_id'],
                                $row['lang'],
                                PageUiComponentDataModel::KEY_SKU,
                                ''
                            );
                        }
                        $update = true;
                    }
                }
                if($update === true){
                    SoaObsGoodsModel::deleteAll(['activity_id'=>$activity_id]);
                    if(empty($theme_id)){
                        return true;
                    }
                    $soaObsGoodsModel = new SoaObsGoodsModel();
                    $soaObsGoodsModel->activity_id = $activity_id;
                    $soaObsGoodsModel->theme_id = $theme_id;
                    $soaObsGoodsModel->theme_name = $theme_name;
                    if (!$soaObsGoodsModel->save(true)) {
                        throw $this->newJsonException($soaObsGoodsModel->flattenErrors(', '));
                    }     
                }    
            }else{
                if(!empty($theme_id)){
                    $soaObsGoodsModel = new SoaObsGoodsModel();
                    $soaObsGoodsModel->activity_id = $activity_id;
                    $soaObsGoodsModel->theme_id = $theme_id;
                    $soaObsGoodsModel->theme_name = $theme_name;
                    if (!$soaObsGoodsModel->save(true)) {
                        throw $this->newJsonException($soaObsGoodsModel->flattenErrors(', '));
                    }
                }
                
            }

            $transaction->commit(); 
        } catch (\Exception $e) {

            $transaction->rollBack();
            throw $this->newJsonException($e->getMessage());
        }
        return true;
    } 

    /**
     * 删除活动关联obs关系
     * @param   int     $activity_id     活动ID
     * @return bool 
     */
    public static function deleteActivity($activity_id)
    { 
        return SoaObsGoodsModel::deleteAll(['activity_id'=>$activity_id]);
    }

    /**
     * 删除页面关联obs关系
     * @param   int     $pid    页面ID
     * @return void
     */
    public static function deletePage($pid)
    {
        SoaObsGoodsModel::updateAll([
            'page_id' => '',
            'page_name' => '',
            'pid' => '',
            'section_id' => '',
            'section_name' => '',
            'component_id' => '',
            'component_key' => '',
        ],['pid'=>$pid]);
    }

    /**
     * 获取活动选中主题
     * @param  int      $activity_id  活动ID
     * @return array 
     */
    public function getThemeByActivity($activity_id)
    {
        return  SoaObsGoodsModel::find()->where(['activity_id'=>$activity_id])
        ->select('theme_id,theme_name')->asArray()->one();
    }
    /**
     * 获取页面选中obs页面
     * @param  int      $pid            页面ID
     * @param  string   $lang           页面语言
     * @param  int      $activity_id    活动ID
     * @return array 
     */
    public function getObsPageId($pid,$lang,$activity_id)
    {
        $theme = SoaObsGoodsModel::find()->where(['activity_id'=>$activity_id])
        ->select('theme_id,theme_name')->asArray()->one();
        if(empty($theme)){
            return ['theme_id'=>'','theme_name'=>'','page_id'=>'','page_name'=>''];
        }
        $page =  SoaObsGoodsModel::find()->where(['pid'=>$pid,'lang'=>$lang])
        ->select('page_id,page_name')->asArray()->one();
        return [ 
            'theme_id'=>$theme['theme_id'],
            'theme_name'=>$theme['theme_name'],
            'page_id'=>$page['page_id'],
            'page_name'=>$page['page_name'],
        ];
    }

    /**
     * 获取组件选中板块ID
     * @param    int        $component_id  组件ID
     * @param    string     $lang          语言
     * @param    int        $pid           页面ID                  
     * @return   array
     */
    public static function getSectionId($component_id,$lang,$pid)
    {
        $page = SoaObsGoodsModel::find()->where(['pid'=>$pid,'lang'=>$lang])
        ->select('page_id,page_name,theme_id,theme_name')->asArray()->one();
        if(empty($page)){
            return [
                'theme_id'=>'',
                'theme_name'=>'',
                'page_id'=>'',
                'page_name'=>'',
                'section_id'=>'',
				'section_name'=>'',
            ];
        }
        $section = SoaObsGoodsModel::find()->where(['component_id'=>$component_id,'lang'=>$lang])
        ->select('section_id')->asArray()->one();
        return [ 
            'theme_id'=>$page['theme_id'],
            'theme_name'=>$page['theme_name'],
            'page_id'=>$page['page_id'],
            'page_name'=>$page['page_name'],
            'section_id'=> !empty($section['section_id']) ? $section['section_id'] : '',
            'section_name'=>!empty($section['section_name']) ? $section['section_name'] : ''
        ];
    }

    /**
     * 更新组件对应关系
     * @param    int    $section_id      板块ID
     * @param    int    $pageId          页面ID
     * @param    string $lang            语言
     * @param    int    $componentId     组件ID
     * @param    string $componentKey    组件key
     * @param    string $section_name    板块名称
     * @return bool
     * @throws \ego\base\JsonResponseException
     */

    private function updateComponentData($section_id,$pageId,$lang,$componentId,$componentKey,$section_name,&$fieldData)
    {
        if(empty($section_id)){
            return true;
        }
        $transaction = app()->db->beginTransaction();
        try {
            $detail = SoaObsGoodsModel::find()->where(['pid'=>$pageId,'lang'=>$lang])->asArray()->one();
            if(!empty($detail)){
                $theme_id = $detail['theme_id'];
                $theme_name = $detail['theme_name'];
                $page_id = $detail['page_id'];
                $page_name = $detail['page_name'];
                $activity_id = $detail['activity_id'];
                $platform = $detail['platform'];
                SoaObsGoodsModel::deleteAll(['theme_id'=>$detail['theme_id'],'page_id'=>$detail['page_id'],'section_id'=>'']);
            }else{
                throw $this->newJsonException('请先选择页面');
            }
            list($goodsSkuList,$lastUpdateTime) = $this->getGoodsList($section_id);
            if(empty($goodsSkuList)){
                 throw $this->newJsonException('板块下没有商品');
            }
            //检查商品是否可用

            try{
                $goodsList = (new GearbestComponent())->getBaseGoodsList([
                        'sku' => $goodsSkuList,
                        'type' => 0,
                        'pipeline'=> $detail['pipeline'],
                        'platforms' => SitePlatform::getPlatformCodeByPlatformType($platform),
                        'siteCode' => $detail['site_code'],
                ]);
                $goodsSkuList = !empty($goodsList) ? str_replace('#','_',join(',',array_column($goodsList, 'goodsId'))) : '';
            }catch (\Exception $e) {
               throw $this->newJsonException($e->getMessage());
            }
            if(empty($goodsSkuList)){
                 throw $this->newJsonException('板块下没有符合条件的商品');
            }

            $fieldData['goodsSKU'] = $goodsSkuList;
            $soaObsGoodsModel = SoaObsGoodsModel::find()
                ->where([
                    'pid'           => $pageId,
                    'lang'          => $lang,
                    'component_id'  => $componentId,
                    'section_id'    => $section_id,
                ])->one();
            if(!empty($soaObsGoodsModel)){
                if($lastUpdateTime > $soaObsGoodsModel->last_update_time){
                    $soaObsGoodsModel->last_update_time = $lastUpdateTime;
                    $soaObsGoodsModel->goods_sku = $goodsSkuList;
                    if (!$soaObsGoodsModel->update(true)) {

                        throw $this->newJsonException($soaObsGoodsModel->flattenErrors(', '));
                    } 
                }
            }else{
                $soaObsGoodsModel = new SoaObsGoodsModel();
                $soaObsGoodsModel->section_id = $section_id;
                $soaObsGoodsModel->section_name = $section_name;
                $soaObsGoodsModel->theme_id = $theme_id;
                $soaObsGoodsModel->theme_name = $theme_name;
                $soaObsGoodsModel->page_id = $page_id;
                $soaObsGoodsModel->page_name = $page_name;
                $soaObsGoodsModel->activity_id = $activity_id;
                $soaObsGoodsModel->pid = $pageId;
                $soaObsGoodsModel->lang = $lang;
                $soaObsGoodsModel->platform = $platform;
                $soaObsGoodsModel->component_id = $componentId;
                $soaObsGoodsModel->component_key = $componentKey;
                $soaObsGoodsModel->goods_sku = $goodsSkuList;
                $soaObsGoodsModel->last_update_time = $lastUpdateTime;

                if (!$soaObsGoodsModel->save(true)) {

                    throw $this->newJsonException($soaObsGoodsModel->flattenErrors(', '));
                } 
            }   
            
            $transaction->commit();

        } catch (\Exception $e) {

            $transaction->rollBack();
            throw $this->newJsonException($e->getMessage());
        }
        return true;
    }

    /**
     * 复制组件 
     * @param   int         $pageId             页面ID
     * @param   string      $lang               语言
     * @param   int         $fromComponentId    复制组件ID
     * @param   int         $toComponentId      新组件ID 
     * @return  void
     */
    public static function copyUiData($pageId, $lang, $fromComponentId, $toComponentId)
    {

        $list = SoaObsGoodsModel::find()
            ->where([
                'pid'           => $pageId,
                'lang'              => $lang,
                'component_id'      => $fromComponentId
            ])->asArray()->all();

        //复制关联数据
        if (!empty($list)) {
            $columns = [];
            $copyData = [];
            foreach ($list as $row) {
                unset($row['id']);
                $row['component_id'] = $toComponentId;
                if (empty($columns)) {
                    $columns = array_keys((array)$row);
                }
                $copyData[] = array_values((array)$row);
            }
            SoaObsGoodsModel::insertAll($columns, $copyData);
        }
    }
}
