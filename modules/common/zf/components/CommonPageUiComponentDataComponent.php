<?php

namespace app\modules\common\zf\components;

use app\modules\common\zf\models\{
    ActivityModel,
    PageModel,
    PageLanguageModel,
    PageLayoutModel,
    PageLayoutDataModel,
    PageUiModel,
    PageUiDataModel,
    PageUiComponentDataModel
};

use app\modules\component\models\UiTplModel;
use yii\helpers\ArrayHelper;
use yii\db\Exception;
use Yii;

/**
 * 布局组件数据
 */
class CommonPageUiComponentDataComponent extends Component
{
    //redis缓存时间【默认1天】
    const EXPIRE_TIME = 86400;
    
    //前端组件配置文件名
    const FILE_NAME_JSON = 'config.json';
    
    //迁移数据执行次数判断，redis缓存前缀
    const JUST_ONE_TIME_PRE = 'geshop::move::data::once::';
    
    //需要迁移旧数据的2 张数据表模型
    private $type = 'ui';
    
    /**
     * 存储前端组件公私有字段配置信息
     *
     * @var array
     */
    public static $setting;
    
    /**
     * 获取前端配置文件信息
     *
     * @param  int   $id     模板id （ ui_component_tpl.id ）
     * @param string $field  要查询的字段名
     * @param  bool  $update 是否强制刷新缓存：true-是， 默认false-否
     *
     * @return mixed
     */
    public static function getConfig($id, $field = 'fields', $update = true)
    {
        //redis缓存key
        $key = app()->redisKey->getUiConfigRedisKey($id, $field);
        $selfKey = $id . '_' . $field;
        
        if ($update === false) {
            //一级静态变量缓存
            if (isset(self::$setting[ $selfKey ])) {
                return self::$setting[ $selfKey ];
            }
            
            //二级redis缓存
            $res = app()->redis->get($key);
            if (!empty($res)) {
                $res = json_decode($res, true);
                self::$setting[ $selfKey ] = $res;
                
                return $res;
            }
        }
        
        $config = self::getConfigById($id, $field);
        if (!empty($config)) {
            self::$setting[ $selfKey ] = $config;
            app()->redis->setex($key, self::EXPIRE_TIME, json_encode($config));
        }
        
        return $config;
    }
    
    /**
     * 根据模板id获取前端配置文件信息
     *
     * @param int    $id    模板id （ ui_component_tpl.id ）
     * @param string $field 要查询的字段名
     *
     * @return mixed
     */
    private static function getConfigById($id, $field)
    {
        static $data = [];
        if (empty($id) || !is_numeric($id)) {
            return false;
        }
        
        if (!isset($data[ $id ])) {
            $item = UiTplModel::findOne($id);
            $data[ $id ] = $item ? ArrayHelper::toArray($item) : [];
        }
    
        if (empty($data[ $id ])) {
            return false;
        }
        
        $path = app()->basePath . '/files/parts/ui/' . $data[ $id ]['component_key'] . '/' . $data[ $id ]['name_en'];
        $filename = $path . '/' . self::FILE_NAME_JSON;
        if (!file_exists($filename)) {
            return false;
        }
        
        $result = file_get_contents($filename);
        $result = !empty($result) ? json_decode($result, true) : [];
        
        return $result[ $field ] ?? [];
    }
    
    /**
     * ui组件数据入表
     *
     * @param int    $id    组件id（page_ui_component.id）
     * @param array  $data  data数据
     * @param int    $tplId 模板id （ui_component_tpl.id）
     * @param string $lang  语种
     *
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function insertPageUiComponentData($id, $data, $tplId = 0, $lang = '')
    {
        if (!isset($id, $data)) {
            return false;
        }
        
        //开启事物
        $tr = app()->db->beginTransaction();
        
        //部分组件数据更新
        if (!$this->updateUiData($id, $tplId, $lang, $data)) {
            $tr->rollBack();
            
            return false;
        }
        
        //格式化数据
        $insert = !empty($data) ? $this->formatData([$id, $tplId, $lang], $data) : [];
        
        //新增数据
        if (!empty($insert)) {
            $model = new PageUiComponentDataModel();
            $column = ['component_id', 'lang', 'key', 'value', 'is_public', 'is_m', 'is_app', 'tpl_id'];
            if (!$model->insertAll($column, $insert)) {
                $tr->rollBack();
                
                return false;
            }
        }
        
        $tr->commit();
        
        return true;
    }
    
    /**
     * 更新ui组件数据
     *
     * @param int   $id    组件id（page_ui_data.component_id）
     * @param int   $tplId 模板id （ui_component_tpl.id）
     * @param array $data  data数据
     *
     * @return boolean
     */
    private function updateUiData($id, $tplId, $lang, &$data)
    {
        if (empty($lang)) {
            return false;
        }
        
        $where = '`component_id`=' . $id . ' AND `lang`="' . $lang . '"';
        $config = [];
        if (!empty($tplId)) {
            $where .= ' AND (`tpl_id`=' . $tplId . ' OR `is_public`=1)';
            $config = self::getConfig($tplId, 'fields');
        }
        $list = PageUiComponentDataModel::find()
            ->where($where)
            ->orderBy('is_public DESC') // 私有数据优先
            ->asArray()
            ->all();
        if (empty($list)) {
            return true;
        }
        
        foreach ($list as $v) {
            $updateData = [];
            if (!empty($config)) {
                $updateData['is_public'] = isset($config[ $v['key'] ]) ? (int) $config[ $v['key'] ]['is_public'] : 0;
                $updateData['is_m'] = isset($config[ $v['key'] ]) ? (int) $config[ $v['key'] ]['is_m'] : 0;
                $updateData['is_app'] = isset($config[ $v['key'] ]) ? (int) $config[ $v['key'] ]['is_app'] : 0;
            }
            
            if (isset($data[ $v['key'] ])) {
                $json = is_array($data[ $v['key'] ]) ? json_encode($data[ $v['key'] ]) : $data[ $v['key'] ];
                $isArray = json_decode($json, true);
                $value = is_array($isArray) ? $json : json_encode($data[ $v['key'] ]);
                if ($value != $v['value']) {
                    $updateData['value'] = $value;
                }
            }
            
            if (!empty($updateData)) {
                PageUiComponentDataModel::updateAll($updateData, ['id' => $v['id']]);
            }
            unset($data[ $v['key'] ]);
        }
        
        return true;
    }
    
    /**
     * 格式化数据
     *
     * @param array $default 统一字段数据【component_id, type, tpl_id】等
     * @param array $data    key和value字段数据
     *
     * @return array
     */
    public function formatData($default, $data)
    {
        list($id, $tplId, $lang) = $default;
        $config = self::getConfig($tplId, 'fields');
        
        $insert = [];
        foreach ($data as $key => $value) {
            if (!isset($key) || $key === '') {
                continue;
            }
            
            if (isset($config[ $key ])) {
                $isPublic = (int) $config[ $key ]['is_public'];
                $isM = (int) $config[ $key ]['is_m'];
                $isApp = (int) $config[ $key ]['is_app'];
            } else {
                $isPublic = $isM = 0;
                $isApp = 1;
            }
            
            $json = is_array($value) ? json_encode($value) : $value;
            $isArray = json_decode($json, true);
            $insert[] = [
                $id,
                $lang,
                $key,
                is_array($isArray) ? $json : json_encode($value),
                $isPublic,
                $isM,
                $isApp,
                $tplId
            ];
        }
        
        return $insert;
    }
    
    /**
     * 确认切换模板
     * 1、删除原有页面模板布局和组件数据
     * 2、更新page表tpl_id字段
     * 3、重新生成页面模板布局组件数据
     *
     * @param $params
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function confirmTpl($params)
    {
        list($pageId, $tplId, $lang) = $params;
        if (!isset($pageId, $tplId, $lang)) {
            return app()->helper->arrayResult($this->codeFail, '参数不全');
        }
        
        //开启事物
        $tr = app()->db->beginTransaction();
        try {
            //4.删除三端绑定关系
            $ui_ids = PageLayoutModel::find()
                ->select("p_u.*")
                ->from(PageLayoutModel::tableName()." as p_l")
                ->InnerJoin(PageUiModel::tableName()." as p_u","p_l.id=p_u.layout_id")
                ->where(['page_id'=>$pageId])
                ->asArray()->all();
            $ui_ids = !empty($ui_ids) ? array_column($ui_ids, 'lang', 'id') : [];
            $ui = new CommonUi();
            $ui->pageId = $pageId;
            $datas = [];
            foreach ($ui_ids as $ui_id => $_lang ){
                $ui->delUserData($ui_id);
                //取消三端数据绑定
                $ui->cancelUiComponentBindRelation($ui_id, $_lang);
                $sync_data['del_info'][] = [
                    'geshop_component_ui_id' => $ui_id
                ];
            }

            //1、删除原页面模板布局组件数据
            if (!$this->deleteLayoutUi($pageId, $lang)) {
                throw new Exception('模板数据切换失败-E001');
            }
            
            //2、更新page表tpl_id字段
            $where = 'page_id=' . $pageId . ' AND lang="' . $lang . '"';
            $page = PageLanguageModel::find()->where($where)->one();
            if (!empty($page) && false === PageLanguageModel::updateAll(['tpl_id' => $tplId], $where)) {
                throw new Exception('模板数据切换失败-E002');
            }
            
            //3、重新生成页面模板布局组件数据
            $commonPageTplComponent = new CommonPageTplComponent();
            if (!$commonPageTplComponent->initPageTpl($pageId, $tplId, $lang)) {
                throw new Exception('模板数据切换失败-E003');
            }

        } catch (\Exception $e) {
            $tr->rollBack();
            
            return app()->helper->arrayResult($this->codeFail, $e->getMessage(), [], ['errors' => $e->getTraceAsString()]);
        }

        $tr->commit();

        //同步删除IPS子活动
        $activity = ActivityModel::getActivityByPageId($pageId);
        $sync_data['geshop_activity_id'] = $activity->id;

        \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);


        $ui_ids = PageLayoutModel::find()
            ->select("p_u.*")
            ->from(PageLayoutModel::tableName()." as p_l")
            ->InnerJoin(PageUiModel::tableName()." as p_u","p_l.id=p_u.layout_id")
            ->where(['page_id'=>$pageId])
            ->asArray()->all();
        $ui_ids = empty($ui_ids) ? [] : $ui_ids;
        $datas = [];

        foreach ($ui_ids as $ui_id){
            $page_ui = PageUiModel::find()->where(['id' => $ui_id])->one();
            if(empty($page_ui)){
                continue;
            }

            $ui_datas = PageUiComponentDataModel::find()->where(['component_id'=>$ui_id["id"],"tpl_id"=>$page_ui->tpl_id])->asArray()->all();
            $is_need_sync = false;
            $is_ips = false;
            $is_rule = false;
            $ipsFilterInfo = [];
            foreach ($ui_datas as $ui_data){
                if($ui_data['key']=="goodsDataFrom" &&
                    $ui_data['value'] == 2){
                    $is_ips= true;
                }
                if($ui_data['key']=="ipsMethods" &&
                    $ui_data['value'] == 3){
                    $is_rule = true;
                }
                if($ui_data['key'] == "ipsFilterInfo"){
                    if(!empty(json_decode($ui_data['value'],1))){
                        $ipsFilterInfo = json_decode($ui_data['value'],1);
                    }
                }
            }
            $is_need_sync = $is_ips && $is_rule;
            if($is_need_sync && $ipsFilterInfo){
                //规则选品需同步
                $tmp['page_id'] = $pageId;
                $tmp['lang'] = $lang;
                $tmp['id'] = $ui_id['id'];
                $tmp['tpl_id'] = $ui_id['tpl_id'];
                $tmp['is_auto_activity'] = 2;
                $tmp['ips_activity_child_id'] = $ipsFilterInfo["ips_activity_child_id"];
                $datas[] = $tmp;
            }
        }

        \app\modules\common\components\CommonActivityComponent::batchCreateActivityToIps($datas);

        return app()->helper->arrayResult($this->codeSuccess, '页面模板切换成功');
    }
    
    /**
     * 删除原有页面模板布局和组件数据
     *
     * @param $pageId
     * @param $lang
     *
     * @return bool
     */
    private function deleteLayoutUi($pageId, $lang)
    {
        $list = PageLayoutModel::find()->where(['page_id' => $pageId, 'lang' => $lang])->asArray()->all();
        //无布局组件
        if (empty($list)) {
            return true;
        }
        
        //有布局组件
        $layoutComponentId = array_column($list, 'id');
        $condition1 = 'IN (' . implode(',', $layoutComponentId) . ')';
        $result = PageLayoutModel::deleteAll('id ' . $condition1)
            && PageLayoutDataModel::deleteAll('component_id ' . $condition1 . ' AND lang="' . $lang . '"');
        
        $list = PageUiModel::find()->where('layout_id ' . $condition1 . ' AND lang="' . $lang . '"')->asArray()->all();
        if (empty($list)) {
            return $result;
        }
        
        $uiComponentId = array_column($list, 'id');
        $condition2 = 'IN (' . implode(',', $uiComponentId) . ')';
        $result = $result && PageUiModel::deleteAll('`id` ' . $condition2);
        
        $list = PageUiComponentDataModel::find()
            ->where('component_id ' . $condition2 . ' AND lang="' . $lang . '"')
            ->asArray()
            ->all();
        if (empty($list)) {
            return $result;
        }
        
        $ids = array_unique(array_column($list, 'id'));
        
        return $result && PageUiComponentDataModel::deleteAll('id IN (' . implode(',', $ids) . ')');
    }
    
    /**
     * 【计划任务】旧数据迁移到新表
     * 1、page_ui_data数据迁移到layout_ui_data
     *
     * @param  string $type layout 表示迁移page_layout_data数据，   ui 表示迁移page_ui_data数据
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function moveLayoutUiData($type)
    {
        $timestamp = time();
        Yii::info('数据迁移Start： type:' . $type . ', time:' . $timestamp, __METHOD__);
        if ($type !== $this->type) {
            return app()->helper->arrayResult($this->codeFail, '参数错误');
        }
        
        $key = DOMAIN . self::JUST_ONE_TIME_PRE . $type;
        if (!app()->redis->setnx($key, 1)) {
            return app()->helper->arrayResult($this->codeFail, '数据迁移只允许执行一次');
        }
        
        ignore_user_abort(true);
        set_time_limit(300);
        
        $id = 0;
        $limit = 20;
        
        $field = 'id, component_id, lang, data, share_data, tpl_id, select_tpl_id';
        while (true) {
            //每一批次取数20条
            $list = PageUiDataModel::find()
                ->select($field)
                ->where('id>' . $id)
                ->orderBy('id asc')
                ->limit($limit)
                ->asArray()
                ->all();
            if (empty($list)) {
                break;
            }
            
            //更新page_ui_component数据表tpl_id字段， 剔除已录入的数据
            $this->checkDataInNewTable($id, $list);
            
            //遍历数据入表
            if (!empty($list)) {
                $this->eachDataToDb($list, $id);
            }
            
            usleep(2000);
        }
        
        Yii::info('数据迁移End： time:' . time(), __METHOD__);
        
        return app()->helper->arrayResult($this->codeSuccess, '执行完毕');
    }
    
    /**
     * 校验数据是否已经录入数据表
     *
     * @param $id
     * @param $list
     *
     * @return bool
     */
    private function checkDataInNewTable(&$id, &$list)
    {
        $componentId = array_unique(array_column($list, 'component_id'));
        $rs = PageUiComponentDataModel::find()
            ->select('component_id')
            ->where('component_id IN (' . implode(',', $componentId) . ')')
            ->all();
        if (empty($rs)) {
            return true;
        }
        
        $selectComponentId = array_unique(array_column($rs, 'component_id'));
        
        foreach ($list as $k => $v) {
            if (false === PageUiModel::updateAll(['tpl_id' => $v['select_tpl_id']], 'id=' . $v['component_id'])) {
                Yii::info('PageUiModel数据更新失败： id:' . $v['component_id'] . ', tpl_id' . $v['select_tpl_id'], __METHOD__);
            }
            
            $id = $v['id'];
            if (in_array($v['component_id'], $selectComponentId)) {
                unset($list[ $k ]);
            }
        }
        
        return true;
    }
    
    /**
     * 遍历数据入表
     *
     * @param $list
     * @param $id
     *
     * @return boolean
     * @throws \yii\db\Exception
     */
    private function eachDataToDb($list, &$id)
    {
        foreach ($list as $v) {
            $id = $v['id'];
            $data = json_decode($v['data'], true);
            if (!is_array($data)) {
                Yii::info(
                    '数据格式错误： component_id:' . $v['component_id'] . ', data:' . $v['data'],
                    __METHOD__
                );
                continue;
            }
            
            if (!empty($v['share_data'])) {
                $shareData = json_decode($v['share_data'], true);
                $data = is_array($shareData) ? array_merge($data, $shareData) : $data;
            }
            
            $tplId = isset($v['tpl_id']) ? (int) $v['tpl_id'] : 0;
            $lang = isset($v['lang']) ? $v['lang'] : '';
            if (!$this->insertPageUiComponentData($v['component_id'], $data, $tplId, $lang)) {
                Yii::info(
                    '数据迁移失败： component_id:' . $v['component_id'] . ', data:' . json_encode($data),
                    __METHOD__
                );
            }
        }
        
        return true;
    }
}
