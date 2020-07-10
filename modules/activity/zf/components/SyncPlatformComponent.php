<?php

namespace app\modules\activity\zf\components;

use app\base\SitePlatform;
use app\modules\base\components\AdminSitePrivilegeComponent;
use app\modules\common\zf\models\{
    PageGroupModel,
    PageModel,
    PageOperateLogModel,
    PageSyncPlatformWaitDataModel,
    PageUiComponentDataModel,
    PageUiModel
};
use app\modules\base\models\AdminSitePrivilegeModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\component\models\UiTplRelationModel;
use function GuzzleHttp\Promise\exception_for;
use function PHPSTORM_META\type;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use app\modules\common\zf\traits\CommonPageParseTrait;
use ego\base\JsonResponseException;
use app\modules\common\zf\components\{
    CommonCrontabComponent,
    CommonGoodsComponent,
    CommonPageUiComponentDataComponent,
    CommonUi
};

class SyncPlatformComponent extends Component
{
    use CommonPageParseTrait;
    
    const AUTH_CACHE_TIME = 60 * 5;
    
    protected $admin;
    
    public function __construct(array $config = [])
    {
        $user = ArrayHelper::toArray(app()->user->identity);
        $this->admin = $user['admin'];
    }
    
    /**
     * 检查访问权限
     *
     * @param int    $pageId 页面ID
     * @param string $route  访问路由
     *
     * @return bool
     * @throws JsonResponseException
     */
    public function checkAuth($pageId)
    {
        if (empty($pageId)) {
            throw new JsonResponseException($this->codeFail, 'page_id不能为空');
        }
        
        $pageModel = PageModel::getById($pageId);
        $groupId = PageGroupModel::getPageGroupIdByPageId($pageId);
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($groupId);
        $data = json_decode(app()->redis->get($redisKey), true);
        if (empty($data['id'])) {
            
            if (!empty($this->admin)) {
                $userPipe = AdminSitePrivilegeModel::getUserSpecialPermissions(
                    $this->admin['id'], SITE_GROUP_CODE
                );
                if (empty($userPipe)) {
                    $message = ($this->admin['realname'] ?: $this->admin['username']) . ',你没有该渠道的权限！';
                    throw new JsonResponseException($this->codeFail, $message, $this->admin);
                }
                $this->admin['pipeline'] = $userPipe;
                app()->redis->setex($redisKey, static::AUTH_CACHE_TIME, json_encode($this->admin));
            }
        } elseif ($data['id'] === app()->user->id) {
            if (in_array($pageModel->pipeline, $data['pipeline'])) {
                app()->redis->expire($redisKey, static::AUTH_CACHE_TIME);
            } else {
                $message = ($data['realname'] ?: $data['username']) . ',你没有该渠道的权限！';
                throw new JsonResponseException($this->codeFail, $message, $data);
            }
        } else {
            //不是本人，需要求出此人和已经进去的人的渠道有没有交集
            $current_user = app()->user->id;
            $current_user_pipe = AdminSitePrivilegeModel::getUserSpecialPermissions(
                $current_user, SITE_GROUP_CODE
            );
            //找出当前活动的pipeline
            $page_group = PageGroupModel::find()->select("pipeline")->where(['page_group_id'=>$groupId])->asArray()->all();
            $pipeline = array_unique(array_column($page_group,"pipeline"));
            //当前用户在这个活动的渠道
            $current_user_pipe =array_intersect($current_user_pipe,$pipeline);
            //已经在操作的人的这个活动的渠道
            $old_user_pipe = array_intersect($data['pipeline'],$pipeline);
            $common_pipe =  array_intersect($current_user_pipe, $old_user_pipe);

            if (!array_diff($current_user_pipe, $common_pipe)) {
                //只返回给前端有限的字段信息，敏感信息不返回
                $returnData = [
                    'id'            => $data['id'],
                    'department_id' => $data['department_id'],
                    'username'      => $data['username'],
                    'realname'      => $data['realname'],
                    'user_no'       => $data['user_no']
                ];
                $message = '当前页面人员 ' . ($returnData['realname'] ?: $returnData['username'])
                    . ' 在配置数据，请于' . (int) (static::AUTH_CACHE_TIME / 60) . '分钟后再尝试操作';
                throw new JsonResponseException($this->codeFail, $message, $returnData);
            }
        }
        
        return true;
    }
    
    /**
     * 获取三端同步渠道列表数据
     *
     * @param int $page_id
     * @param     $site_code
     *
     * @return array
     */
    public function getSyncPlatformOptions(int $page_id, $site_code)
    {
        $data = [];
        $groupId = PageGroupModel::getPageGroupIdByPageId($page_id);
        $pageList = PageGroupModel::getPageListByPageGroupId($groupId);
        if (!empty($pageList)) {
            $pageList = ArrayHelper::toArray($pageList);
            $userPipe = AdminSitePrivilegeModel::getUserSpecialPermissions($this->admin['id'], SITE_GROUP_CODE);
            //需要结合别人的权限来设定显示的渠道
            $groupId = PageGroupModel::getPageGroupIdByPageId($page_id);
            $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($groupId);
            $user_cache_data = json_decode(app()->redis->get($redisKey), true);
            $lock_pipe = [];
            if(!empty($user_cache_data['pipeline']) && $user_cache_data['id']!=app()->user->id){
                $lock_pipe = $user_cache_data['pipeline'];
            }

            foreach ($pageList as $list) {
                if(!in_array($list['pipeline'],$lock_pipe)){
                    if (in_array($list['pipeline'], $userPipe)) {
                        $platform = strtolower(SitePlatform::getPlatformNameByType($list['platform_type']));
                        $site = SitePlatform::splitSiteCode($site_code);
                        $pipeName = app()->params['site'][ $site[0] ]['pipeline'][ $list['pipeline'] ];
                        $data[ $platform ][ $list['pipeline'] ] = $pipeName;
                    }
                }

            }


        }
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
    }
    
    /**
     * 获取三端同步组件模板列表
     *
     * @param        $page_id
     * @param        $platform
     * @param string $pipeline
     *
     * @return array
     */
    public function getSyncComponentSelect($page_id, string $platform, string $pipeline, $isJson = true)
    {
        $groupId = PageGroupModel::getPageGroupIdByPageId($page_id);
        $pipeline = explode(',', $pipeline);
        $pageList = PageGroupModel::getPageLangListByGroupId($groupId, $pipeline);
        $pageList = ArrayHelper::toArray($pageList);
        if (!empty($pageList) && is_array($pageList)) {
            $platforms = array_map(function ($item) {
                return SitePlatform::getPlatformTypeByPlatformCode($item);
            }, explode(',', $platform));
            $pageList = array_filter($pageList, function ($item) use ($platforms) {
                return in_array($item['platform_type'], $platforms);
            });
            list($platformIntersect, $sourceTpl, $uiIds) = $this->buildSyncComponentSelect($pageList, $platforms, $groupId);
        }
        unset($pageList);
        $data = [];
        if (!empty($platformIntersect) && is_array($platformIntersect)) {
            foreach ($platformIntersect as $uiId => $id) {
                $info = current(UiTplModel::batchTplFullInfo([$id]));
                $info['pc_tpl'] = !empty($sourceTpl[ $uiId ]) ? (string) current(array_keys($sourceTpl[ $uiId ])) : $id;
                $info_pc = current(UiTplModel::batchTplFullInfo([$info['pc_tpl']]));
                if(!empty($info_pc)){
                    $info['c_name'] = $info_pc['c_name'];
                    $info['t_name'] = $info_pc['t_name'];
                }
                if(!empty($uiIds[$uiId]) && count($uiIds[$uiId])==1 && in_array('pc',array_keys($uiIds[$uiId]))){
                    //需要将pc的key转换成m端
                    $m_tpl =  UiTplRelationModel::getTplRelationId([$info['pc_tpl']]);
                    if(!empty($m_tpl)){
                        $ui_tpl = UiTplModel::getById(current($m_tpl));
                        if(!empty($ui_tpl)){
                            $info['component_key'] = $ui_tpl->component_key;
                            $info['name_en'] = $ui_tpl->name_en;
                        }
                    }
                }
                $info['ui_id'] = !empty($uiIds[ $uiId ]) ? json_encode($uiIds[ $uiId ]) : '';
                $sub_tab_count = $this->getSubTabItem($info['ui_id']);
                $info['sub_tab_count'] = $sub_tab_count;
                array_push($data, $info);
            }
        }
        
        return $isJson ? app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data) : $data;
    }

    /**
     * 获取子tab数量
     *
     * @param $ui_ids
     * @return int
     * @author yuanwenguang 2019/4/12 13:44
     */
    private function getSubTabItem($ui_ids){
        $count = 1;
        $ui_ids = empty($ui_ids) ? '' : json_decode($ui_ids,1);
        if(empty($ui_ids)){
            return $count;
        }
        $count_arr = [];
        foreach ($ui_ids as $platform => $ui_id){
            foreach ($ui_id as $pipeline => $uid){
                $page_ui = PageUiModel::find()->where(['id'=>$uid])->one();
                if($page_ui){
                   $list =  PageUiComponentDataModel::getDataList($uid,$page_ui->lang,$page_ui->tpl_id);
                   foreach ($list as $k => $item){
                       if($k == "goodsSKUTab" || $k == "goodsID" || $k == "goodsIds"){
                           //多时段秒
                           $count_arr[$platform][$pipeline] = empty($item)? 1 : count($item);
                       }
                   }
                }
            }
        }
        if(empty($count_arr)){
            return $count;
        }else{
            if(isset($count_arr['pc'])){
                //存在pc取pc的最大值
                $count = min($count_arr['pc']);
            }elseif(isset($count_arr['m'])){
                //没有pc端取m端
                $count = min($count_arr['m']);
            }else{
                //没有pc端取m端
                $count = min($count_arr['app']);
            }
        }

        return $count;
    }
    
    /**
     * 组装数据并取交集
     *
     * @param array $pageList
     * @param array $platforms
     *
     * @return array
     */
    public function buildSyncComponentSelect(array $pageList, array $platforms, string $groupId)
    {
        $pageData = $platformIntersect = $pcTpl = $uiIds = [];
        foreach ($pageList as $p_key => $page) {
            list(, $uiListByLayoutPosition,) = $this->getPageLayoutAndUiByPageId($page['page_id'], $page['lang']);
            if (!empty($uiListByLayoutPosition)) {
                $uiList = array_map(function ($item) {
                    return current(current($item));
                }, $uiListByLayoutPosition);
                $uiList = $this->getOrderedComponents($uiList);
                $tpl = array_column($uiList, 'tpl_id', 'id');
                if (SitePlatform::PLATFORM_TYPE_PC == $page['platform_type'] && !(count($platforms)==1 && in_array('1',$platforms))) {
                    $sourceTpl = array_map(function ($item) {
                        return UiTplRelationModel::getTplRelationId([$item]);
                    }, $tpl);
                    if (0 == $p_key) {
                        $pcTpl = $sourceTpl;
                    }
                    foreach ($sourceTpl as $key => $value) {
                        $pageData[ $page['platform_type'] ][ $page['pipeline'] ][ $key ] = array_pop($value);
                    }
                } else {
                    $pageData[ $page['platform_type'] ][ $page['pipeline'] ] = $tpl;
                }
            }
            
            unset($uiListByLayoutPosition, $uiList, $pageList);
        }
        if (!empty($pageData) && is_array($pageData)) {
            ksort($pageData);
            //要是单端口并且单渠道，返回空
            if(count($pageData) == 1){
                foreach ($pageData as $k => $page) {
                    if (count($page) <= 1) {
                        return [$platformIntersect, $pcTpl, $uiIds];
                    }
                }
            }
            if(count($pageData)!=count($platforms)){
                return [$platformIntersect, $pcTpl, $uiIds];
            }
            //判断渠道是否一致
            $pipeline_count = [];
            foreach ($pageData as $platform_data){
                $count = count($platform_data);
                array_push($pipeline_count,$count);
            }
            if(!empty($pipeline_count)){
                $uniq_arr = array_unique($pipeline_count);
                if(count($uniq_arr)>1){
                    return [$platformIntersect, $pcTpl, $uiIds];
                }
            }

            list($platformIntersect, $uiIds) = $this->getPlatformAllUiId($pageData, $groupId);
            unset($pageData);
        }
        return [$platformIntersect, $pcTpl, $uiIds];
    }
    
    /**
     * 获取所有端的组件唯一id
     *
     * @param array $intersect
     * @param array $data
     *
     * @return array
     */
    private function getPlatformAllUiId(array $data, string $groupId)
    {
        $return = $intersect = [];
        $syncData = PageSyncPlatformWaitDataModel::getUiComponentSyncRelation($groupId);
        if (!empty($syncData) && is_array($syncData)) {
            $syncData = array_column($syncData, 'ui_id', 'single_ui_id');
            $syncData = array_map(function ($item) {
                return json_decode($item, true);
            }, $syncData);
            if (
                count($data) == count(current($syncData))
                && count(current($data)) == count(current(current($syncData)))
            ) {
                //把数组的值抽出来
                $syncArr = [];
                foreach ($syncData as $item){
                    $pipeline_data =  array_values($item);
                    foreach ($pipeline_data as $pipeline_datum){
                        $ui_id = array_values($pipeline_datum);
                        foreach ($ui_id as $u_value){
                            array_push($syncArr,$u_value);
                        }
                    }
                }

//                $syncArr = array_reduce($syncArr, 'array_merge', []);
                foreach ($data as $p_key => $platform) {
                    foreach ($platform as $pl_key => $pipeline) {
                        foreach ($pipeline as $pipe_key => $item) {
                            if (in_array($pipe_key, array_keys($syncData))) {
                                $return[ $pipe_key ] = $syncData[ $pipe_key ];
                                $intersect[ $pipe_key ] = $item;
                            }
                            if (in_array($pipe_key, $syncArr)) {
                                unset($data[ $p_key ][ $pl_key ][ $pipe_key ]);
                            }
                        }
                    }
                }
            }
        }
        $platformIntersect = componentIntersect($data);
        if (!empty($platformIntersect) && is_array($platformIntersect)) {
            foreach ($platformIntersect as $key => $id) {
                $list = [];
                foreach ($data as $p_key => $platform) {
                    $uiIds = [];
                    foreach ($platform as $pl_key => $pipeline) {
                        foreach ($pipeline as $pipe_key => $item) {
                            if ($item == $id) {
                                $uiIds[$pl_key] = (string) $pipe_key;
                                unset($data[ $p_key ][ $pl_key ][ $pipe_key ]);
                                break;
                            }
                        }
                    }
                    $keyName = strtolower(SitePlatform::getPlatformNameByType($p_key));
                    $list[ $keyName ] = $uiIds;
                }
                $return[ $key ] = $list;
                $intersect[ $key ] = $id;
            }
        }
        return [$intersect, $return];
    }
    
    /**
     * 保存三端数据统一表单接口
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     * @author yuanwenguang 2019/3/26 11:50
     */
    public function batchSaveFrom($params = [])
    {
        $pipeline = !empty($params['pipeline']) ? $params['pipeline'] : null;//渠道，数组
        $platform = !empty($params['platform']) ? $params['platform'] : null;//平台类型，数组
        $is_cover = isset($params['is_cover']) ? $params['is_cover'] : null; //是否覆盖 0.不覆盖 1.覆盖
        //参数校验
        if (empty($pipeline)) {
            return app()->helper->arrayResult(1, '渠道不能为空');
        }
        
        if (empty($platform)) {
            return app()->helper->arrayResult(1, '平台不能为空');
        }
        
        if (is_null($is_cover) || $is_cover == '') {
            return app()->helper->arrayResult(1, '是否覆盖不能为空');
        }

        $page_group_id = PageGroupModel::getPageGroupIdByPageId($params['page_id']);
        if (empty($page_group_id)) {
            throw new JsonResponseException(1, '页面分组不存在');
        }
        $wait_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id'=>$page_group_id]);
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($page_group_id);
        $user_cache_data = json_decode(app()->redis->get($redisKey), true);
        //需要解绑绑定的数据
        if(!empty($wait_data)){
            if($wait_data->update_user != app()->user->username){
                //并且最后设置不是本人需要清除
                $form_data = empty($wait_data->form_data) ? [] : json_decode($wait_data->form_data, 1);
                $ui = new CommonUi();
                foreach ($form_data as $form_datum) {
                    $ui_id = json_decode($form_datum['ui_id'], 1);
                    $pc = empty($ui_id['pc']) ? [] : array_values($ui_id['pc']);
                    $m = empty($ui_id['m']) ? [] : array_values($ui_id['m']);
                    $app = empty($ui_id['app']) ? [] : array_values($ui_id['app']);
                    $ui_ids = array_merge($pc, $m);
                    $ui_ids = array_merge($ui_ids, $app);
                    $tmp_data = PageUiModel::getUiComponentLangForUiId($ui_ids);
                    $ui->pageId = $params['page_id'];
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }
            }elseif (!empty($user_cache_data) && app()->user->id != $user_cache_data['id'] && $wait_data->update_user != app()->user->username){

                //有人占用，清除
                $form_data = empty($wait_data->form_data) ? [] : json_decode($wait_data->form_data, 1);
                $ui = new CommonUi();
                foreach ($form_data as $form_datum) {
                    $ui_id = json_decode($form_datum['ui_id'], 1);
                    $pc = empty($ui_id['pc']) ? [] : array_values($ui_id['pc']);
                    $m = empty($ui_id['m']) ? [] : array_values($ui_id['m']);
                    $app = empty($ui_id['app']) ? [] : array_values($ui_id['app']);
                    $ui_ids = array_merge($pc, $m);
                    $ui_ids = array_merge($ui_ids, $app);
                    $tmp_data = PageUiModel::getUiComponentLangForUiId($ui_ids);
                    $ui->pageId = $params['page_id'];
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }
            }
        }

        //保存
        $this->saveForm($params);

        $log_platform = [];
        $platform = explode(',',$params['platform']);

        //求并集

        $old_platform = empty($wait_data['platform']) ? [] : explode(',',$wait_data['platform']);
        //找出删除的
        $del_platform = array_diff($old_platform,$platform);
        //找出新增的
        $add_platform = array_diff($platform,$old_platform);

        //最终的集合
        $full_platforms = array_merge($old_platform,$add_platform);
        foreach ($full_platforms as $full_platform){
            $tmp['data'] = $full_platform;
            $tmp['operate'] = '';
            if(in_array($full_platform,$del_platform)){
                $tmp['operate'] = PageOperateLogModel::TYPE_DEL;
            }
            if(in_array($full_platform,$add_platform)){
                $tmp['operate'] = PageOperateLogModel::TYPE_ADD;
            }
            $log_platform[] = $tmp;
        }

        $log_pipeline = [];
        $pipeline = explode(',',$params['pipeline']);
        $old_pipeline = empty($wait_data['pipeline']) ? [] : explode(',',$wait_data['pipeline']);
        //找出删除的
        $del_pipeline = array_diff($old_pipeline,$pipeline);
        //找出新增的
        $add_pipeline = array_diff($pipeline,$old_pipeline);

        //最终的集合
        $full_pipelines = array_merge($old_pipeline,$add_pipeline);
        foreach ($full_pipelines as $full_pipeline){
            $tmp['data'] = $full_pipeline;
            $tmp['operate'] = '';
            if(in_array($full_pipeline,$del_pipeline)){
                $tmp['operate'] = PageOperateLogModel::TYPE_DEL;
            }
            if(in_array($full_pipeline,$add_pipeline)){
                $tmp['operate'] = PageOperateLogModel::TYPE_ADD;
            }
            $log_pipeline[] = $tmp;
        }


        $params['platform'] = $log_platform;
        $params['pipeline'] = $log_pipeline;

        $params['form_data'] = json_decode($params['form_data'],1);

        //记录操作日志
        $this->saveLog(PageOperateLogModel::TYPE_SAVE,$params);

        //4.返回成功
        return app()->helper->arrayResult(0, '保存成功');
    }
    
    /**
     * 保存表单
     *
     * @param $data
     *
     * @throws JsonResponseException
     * @author yuanwenguang 2019/3/26 11:52
     */
    public function saveForm($data)
    {
        $page_group = PageGroupModel::findOne(['page_id' => $data['page_id']]);
        if (empty($page_group)) {
            throw new JsonResponseException(1, '页面分组不存在');
        }
        $page_sync_platform_wait_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id' => $page_group->page_group_id]);
        if (empty($page_sync_platform_wait_data)) {
            $page_sync_platform_wait_data = new PageSyncPlatformWaitDataModel();
        }
        $page_sync_platform_wait_data->page_group_id = $page_group->page_group_id;
        $page_sync_platform_wait_data->is_cover = $data['is_cover'];
        $page_sync_platform_wait_data->pipeline = $data['pipeline'];
        $page_sync_platform_wait_data->form_data = $data['form_data'];
        $page_sync_platform_wait_data->platform = $data['platform'];
        if (!$page_sync_platform_wait_data->save()) {
            throw new JsonResponseException(1, '保存失败');
        }
    }
    
    /**
     * 获取三端配置数据
     *
     * @param array $params
     *
     * @return array
     * @author yuanwenguang 2019/3/26 11:53
     */
    public function getBatchFromData($params = [])
    {
        $page_id = empty($params['page_id']) ? null : $params['page_id'];
        $page_group_id = PageGroupModel::getPageGroupIdByPageId($page_id);
        $data = PageSyncPlatformWaitDataModel::find()->where(['page_group_id' => $page_group_id])->asArray()->one();
        if (!empty($data)) {
            $data['form_data'] = json_decode($data['form_data'], 1);
            unset($data['create_user']);
            unset($data['create_time']);
            unset($data['update_time']);
        }
        
        //和用户渠道权限求交集
//        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($page_group_id);
//        $cacheData = json_decode(app()->redis->get($redisKey), true);
//        $data['pipeline'] = explode(',', $data['pipeline']);
        //            $wait_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id'=>$groupId]);
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($page_group_id);
        $user_cache_data = json_decode(app()->redis->get($redisKey), true);
        //需要解绑绑定的数据
        $wait_data = $data;
        $return_data = $data;
        if(!empty($wait_data)){
            if($wait_data['update_user'] != app()->user->username){
                //并且最后设置不是本人需要清除
                $return_data=[];
            }elseif (!empty($user_cache_data) && app()->user->id != $user_cache_data['id'] && $wait_data['update_user'] != app()->user->username){
                //有人占用，清除
                $return_data=[];
            }
        }

        //需要结合别人的权限来设定显示的渠道
        $groupId = PageGroupModel::getPageGroupIdByPageId($page_id);
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($groupId);
        $user_cache_data = json_decode(app()->redis->get($redisKey), true);

        $lock_pipe = [];
        if(!empty($user_cache_data['pipeline']) && $user_cache_data['id']!=app()->user->id){

            $user_pipeline = AdminSitePrivilegeModel::getUserSpecialPermissions(app()->user->id,"zf");
            $lock_pipe = $user_cache_data['pipeline'];
            $data['pipeline'] = array_intersect(array_diff(explode(',',$data['pipeline']), $lock_pipe),$user_pipeline);

        }elseif (!empty($user_cache_data['pipeline']) && $user_cache_data['id'] == app()->user->id){
            //本人，也需要求渠道交集
            $data['pipeline'] = array_intersect(explode(',',$data['pipeline']),$user_cache_data['pipeline']);
        }
        if(empty($data['pipeline'])){
            $data['form_data'] = [];
        }
        if(!empty($return_data)){
            $data['pipeline'] = array_values($data['pipeline']);
            $return_data = $data;
        }

        return app()->helper->arrayResult(0, 'success', $return_data);
    }
    
    /**
     * 绑定三端组件数据
     *
     * @param array $params
     *
     */
    public function batchBind($params = [])
    {
        // 参数校验
        $rules = [
            [['page_id', 'platform', 'pipeline', 'is_cover', 'form_data'], 'required'],
            [['page_id', 'is_cover'], 'integer'],
            [['pipeline', 'platform', 'form_data'], 'string']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }
//        $platforms = explode(',', $params['platform']);
//        if (count($platforms) < 2) {
//            throw new JsonResponseException($this->codeFail, '至少需要两个端才能进行绑定');
//        }
        #{{
        $page_group_id = PageGroupModel::getPageGroupIdByPageId($params['page_id']);
        if (empty($page_group_id)) {
            throw new JsonResponseException(1, '页面分组不存在');
        }
        $wait_data = PageSyncPlatformWaitDataModel::findOne(['page_group_id'=>$page_group_id]);
        #}}
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getComponentBindLockKey($page_group_id);
        $user_cache_data = json_decode(app()->redis->get($redisKey), true);
        //需要解绑绑定的数据
        if(!empty($wait_data)){
            if($wait_data->update_user != app()->user->username){
                //并且最后设置不是本人需要清除
                $form_data = empty($wait_data->form_data) ? [] : json_decode($wait_data->form_data, 1);
                $ui = new CommonUi();
                foreach ($form_data as $form_datum) {
                    $ui_id = json_decode($form_datum['ui_id'], 1);
                    $pc = empty($ui_id['pc']) ? [] : array_values($ui_id['pc']);
                    $m = empty($ui_id['m']) ? [] : array_values($ui_id['m']);
                    $app = empty($ui_id['app']) ? [] : array_values($ui_id['app']);
                    $ui_ids = array_merge($pc, $m);
                    $ui_ids = array_merge($ui_ids, $app);
                    $tmp_data = PageUiModel::getUiComponentLangForUiId($ui_ids);
                    $ui->pageId = $params['page_id'];
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }
            }elseif (!empty($user_cache_data) && app()->user->id != $user_cache_data['id'] && $wait_data->update_user != app()->user->username){

                //有人占用，清除
                $form_data = empty($wait_data->form_data) ? [] : json_decode($wait_data->form_data, 1);
                $ui = new CommonUi();
                foreach ($form_data as $form_datum) {
                    $ui_id = json_decode($form_datum['ui_id'], 1);
                    $pc = empty($ui_id['pc']) ? [] : array_values($ui_id['pc']);
                    $m = empty($ui_id['m']) ? [] : array_values($ui_id['m']);
                    $app = empty($ui_id['app']) ? [] : array_values($ui_id['app']);
                    $ui_ids = array_merge($pc, $m);
                    $ui_ids = array_merge($ui_ids, $app);
                    $tmp_data = PageUiModel::getUiComponentLangForUiId($ui_ids);
                    $ui->pageId = $params['page_id'];
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }
            }
        }


        $formData = json_decode($params['form_data'], true);
        $exceptionUiAll = [];
        if (!empty($formData) && is_array($formData)) {
            foreach ($formData as $form) {
                $uiIdList = $exceptionUi = [];
                $uiIds = json_decode($form['ui_id'], true);
                $uiName = UiModel::getComponentName($form['component_key']);
                foreach ($uiIds as $platformKey => $platformUi) {
                    $realTpl = ($platformKey == SitePlatform::PLATFORM_CODE_PC) ? $form['pc_tpl'] : $form['tpl_id'];
                    $bindUi = PageUiModel::checkPageUiComponentExsits($platformUi, $realTpl);
                    foreach ($platformUi as $ui) {
                        if (!in_array($ui, array_keys($bindUi))) {
                            //需要对照之前的交集
                            $ui_id =  json_decode($form['ui_id'],1);
                            $pipeName = '';
                            foreach ($ui_id as $p_k => $p_item){
                                foreach ($p_item as $pipeline=>$id){
                                    if($id == $ui){
                                        $pipeName = $pipeline;
                                    }
                                }
                            }
                            if(!empty($pipeName)){
                                $pipeName = app()->params['site']['zf']['pipeline'][$pipeName];
                            }
                            array_push($exceptionUi, "{$platformKey} 端口下的 {$pipeName} 渠道的 {$uiName} 组件已删除");
                        }
                    }
                    
                    $uiIds[ $platformKey ] = $bindUi;
                    $uiIdList = $uiIdList + $uiIds[ $platformKey ];
                }
                if (!empty($exceptionUi)) {
                    $exceptionUiAll = array_merge($exceptionUiAll,$exceptionUi);
                    $exceptionUi = [];
                    continue;
                }
                $isCover = empty($params['is_cover']) ? $this->checkBindComponentField($uiIdList, $form['tpl_id']) : $params['is_cover'];
                if (!empty($isCover)) {
                    $transaction = app()->db->beginTransaction();
                    try {
                        $this->syncComponentData($uiIds, $form['tpl_id'], $form, $params['page_id']);
                        try {
                            $this->saveForm($params);
                            $transaction->commit();
                        } catch (JsonResponseException $exception) {
                            throw new JsonResponseException($this->codeFail, $exception->getMessage());
                        }
                    } catch (\yii\base\Exception $exception) {
                        $transaction->rollBack();
                        throw new JsonResponseException($this->codeFail, $exception->getMessage());
                    }
                }
            }
        }

        $log_platform = [];
        $platform = explode(',',$params['platform']);

        //求并集

        $old_platform = empty($wait_data['platform']) ? [] : explode(',',$wait_data['platform']);
        //找出删除的
        $del_platform = array_diff($old_platform,$platform);
        //找出新增的
        $add_platform = array_diff($platform,$old_platform);

        //最终的集合
        $full_platforms = array_merge($old_platform,$add_platform);
        foreach ($full_platforms as $full_platform){
            $tmp['data'] = $full_platform;
            $tmp['operate'] = '';
            if(in_array($full_platform,$del_platform)){
                $tmp['operate'] = PageOperateLogModel::TYPE_DEL;
            }
            if(in_array($full_platform,$add_platform)){
                $tmp['operate'] = PageOperateLogModel::TYPE_ADD;
            }
            $log_platform[] = $tmp;
        }

        $log_pipeline = [];
        $pipeline = explode(',',$params['pipeline']);
        $old_pipeline = empty($wait_data['pipeline']) ? [] : explode(',',$wait_data['pipeline']);
        //找出删除的
        $del_pipeline = array_diff($old_pipeline,$pipeline);
        //找出新增的
        $add_pipeline = array_diff($pipeline,$old_pipeline);

        //最终的集合
        $full_pipelines = array_merge($old_pipeline,$add_pipeline);
        foreach ($full_pipelines as $full_pipeline){
            $tmp['data'] = $full_pipeline;
            $tmp['operate'] = '';
            if(in_array($full_pipeline,$del_pipeline)){
                $tmp['operate'] = PageOperateLogModel::TYPE_DEL;
            }
            if(in_array($full_pipeline,$add_pipeline)){
                $tmp['operate'] = PageOperateLogModel::TYPE_ADD;
            }
            $log_pipeline[] = $tmp;
        }


        $params['platform'] = $log_platform;
        $params['pipeline'] = $log_pipeline;

        $params['form_data'] = json_decode($params['form_data'],1);

        //记录操作日志
        $this->saveLog(PageOperateLogModel::TYPE_BIND,$params);
        if (!empty($exceptionUiAll)) {
           return app()->helper->arrayResult(2, '绑定失败', $exceptionUiAll);
        }
        return app()->helper->arrayResult($this->codeSuccess, '绑定成功', []);
    }
    
    /**
     * 获取需要绑定组件的字段
     *
     * @param array $uiIds
     * @param int   $tplId
     *
     * @return int
     */
    private function checkBindComponentField(array $uiIds, int $tplId)
    {
        $filterConfig = app()->params['site'][ SITE_GROUP_CODE ]['componentFilterField'];
        $tplInfo = UiTplModel::getTplFullInfo($tplId);
        $tplInfo = ArrayHelper::toArray($tplInfo);
        $fields = $filterConfig[ $tplInfo['component_key'] ][ $tplInfo['id'] ];
        $data = [];
        foreach ($uiIds as $id => $lang) {
            $uiData = PageUiComponentDataModel::getDataList($id, $lang, $tplId);
            $data[] = array_filter($uiData, function ($item, $key) use ($fields) {
                return in_array($key, $fields) && !empty($item);
            }, ARRAY_FILTER_USE_BOTH);
        }
        
        return (int) empty(array_filter($data));
    }
    
    /**
     * 绑定三端数据
     *
     * @param array $uiIds
     * @param int   $tplId
     * @param array $data
     *
     * @throws \yii\base\Exception
     */
    private function syncComponentData(array $uiIds, int $tplId, array $data,$page_id)
    {
        $publicData = !empty($data['public_data']) ? $data['public_data'] : [];
        $privateData = !empty($data['private_data']) ? $data['private_data'] : [];
        $formData = array_merge($publicData, $privateData);
        $formData = array_map(function ($item) {
            if (is_string($item) && json_decode($item)) {
                $item = json_decode($item, true);
            }
            
            return $item;
        }, $formData);

        if($this->checkEmptyFormData($formData,$uiIds,$page_id,$tplId)){
            //说明有删除操作
            return;
        }

        //事物开始
        
        $transaction = app()->db->beginTransaction();
        try {
            $insert = $bindRelation = [];
            foreach ($uiIds as $platform => $list) {
                foreach ($list as $uiId => $lang) {
                    $formatData = (new CommonPageUiComponentDataComponent())->formatData(
                        [
                            $uiId,
                            ($platform == SitePlatform::PLATFORM_CODE_PC) ? $data['pc_tpl'] : $tplId,
                            $lang
                        ],
                        $formData
                    );
                    PageUiComponentDataModel::deleteAll(['component_id' => $uiId, 'lang' => $lang, 'key' => array_keys($formData)]);
                    $insert = array_merge($insert, $formatData);
                }
                $bindRelation = $bindRelation + $list;
            }
            $columns = PageUiComponentDataModel::getTableSchema()->getColumnNames();
            array_shift($columns);
            PageUiComponentDataModel::insertAll($columns, $insert);
            PageUiModel::updateAll(
                ['bind_relation' => implode(',', array_keys($bindRelation))],
                ['id' => array_keys($bindRelation)]
            );
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw new \yii\base\Exception('绑定数据失败.');
        }
    }

    /**
     * 检查表单数据是否为空,为空则删除绑定
     * @param $from_data
     * @param $uiIds
     * @param $page_id
     * @author yuanwenguang 2019/4/9 17:46
     */
    private function checkEmptyFormData($from_data,$uiIds,$page_id,$tpl_id){
        $need_check_field = ["goodsSKU","goodsID","goodsSKUTab"];
        $no_need_check_tpl = ['170','124'];
        $uiIds = array_values($uiIds);
        $return = false;
        if(in_array($tpl_id,$no_need_check_tpl)){
            return $return;
        }
        $tmp_data = [];
        foreach ($uiIds as $ui_id){
            foreach ($ui_id as $k => $v){
                $tmp_data[$k] = $v;
            }
        }
        foreach ($from_data as $k => $from_datum){
            if(in_array($k,$need_check_field) && (empty($from_datum) || $from_datum=="[]")){
                $return = true;
                //需要解除绑定
                $ui = new CommonUi();
                $ui->pageId = $page_id;
                foreach ($tmp_data as $id => $lang){
                    $ui->delUserData($id);
                    $ui->cancelUiComponentBindRelation($id, $lang);
                }
            }

            if($k == "goodsSKUTab" && !empty($from_datum) && $from_datum != "[]"){
                //多时段秒杀里面有一个lists为空则删除
                $is_need_del = true;
                foreach ($from_datum as $item){
                    if(!empty($item['lists'])){
                        $is_need_del = false;
                    }
                }
                if($is_need_del){
                    $return = true;
                    //需要解除绑定
                    $ui = new CommonUi();
                    $ui->pageId = $page_id;
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }

            }
            if($k == "goodsID" ){
                if(empty($from_datum)|| $from_datum == "[]"){
                    //空
                    $return = true;
                    //需要解除绑定
                    $ui = new CommonUi();
                    $ui->pageId = $page_id;
                    foreach ($tmp_data as $id => $lang){
                        $ui->delUserData($id);
                        $ui->cancelUiComponentBindRelation($id, $lang);
                    }
                }else{
                    $is_need_del1 = true;
                    foreach ($from_datum as $item){
                        if(!empty($item)){
                            if(is_string($item)){
                                $is_need_del1 = false;
                            }elseif(is_array($item)){
                                foreach ($item as $k => $value){
                                    if($k == "ids" && !empty($value)){
                                        $is_need_del1 = false;
                                    }
                                }
                            }
                        }
                    }
                    if($is_need_del1){
                        $return = true;
                        //需要解除绑定
                        $ui = new CommonUi();
                        $ui->pageId = $page_id;
                        foreach ($tmp_data as $id => $lang){
                            $ui->delUserData($id);
                            $ui->cancelUiComponentBindRelation($id, $lang);
                        }
                    }

                }
            }
        }
        return $return;
    }
    
    /**
     * 删除三端绑定组件
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function deleteBind(array $params)
    {
        $uiIds = PageUiModel::getPageUiComponentRelation($params['ui_id']);
        $uiIds = PageUiModel::getUiComponentLangForUiId($uiIds);
        
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            $ui = new CommonUi();
            $ui->pageId = $params['page_id'];
            foreach ($uiIds as $id => $lang) {
                $ui->delUserData($id);
                $ui->cancelUiComponentBindRelation($id, $lang);
            }
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw new JsonResponseException($this->codeFail, $exception->getMessage());
        }
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, []);
    }

    /**
     * 校验组件数据是否存在
     *
     * @param $params
     * @return array
     * @throws JsonResponseException
     * @author yuanwenguang 2019/4/2 10:10
     */
    public function checkGoodsExists($params){
        $pageModel = PageModel::getById($params['page_id']);
        $diff_skus = [];
        if(empty($params['api'])){
            //常规校验
            // 参数校验
            $rules = [
                [['skus', 'pipeline'], 'required'],
            ];
            $model = app()->validatorModel->new($rules)->load($params);
            if (false === $model->validate()) {
                throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
            }

            $pipeline = explode(',',$params['pipeline']);
            $goods = new CommonGoodsComponent();
            foreach ($pipeline as $pipeline_item){

                $lang_lists = [];
                if(!empty(app()->params['sites']['zf-pc']['secondary_domain'][$pipeline_item])){
                    $lang_lists = array_keys(app()->params['sites']['zf-pc']['secondary_domain'][$pipeline_item]);
                    if(empty($lang_lists)){
                        continue;
                    }
                }
                foreach ($lang_lists as $lang){
                    $tmp['pipeline'] = $pipeline_item;
                    $tmp['lang'] = $lang;
                    $tmp['skus'] = $params['skus'];
                    $tmp['pageId'] = $params['page_id'];
                    $diff_skus = array_merge($diff_skus,explode(',',$goods->checkSyncPlatformGoodsExists($tmp)));
                }
            }

            //求并集。
            $diff_skus = array_unique($diff_skus);

        }else{
            //api检查
            // 参数校验
            $rules = [
                [['skus', 'pipeline', 'api','content'], 'required'],
            ];
            $model = app()->validatorModel->new($rules)->load($params);
            if (false === $model->validate()) {
                throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
            }
            $content = json_decode($params['content'],1);
            $pipeline = explode(',',$params['pipeline']);
            $goods = new CommonGoodsComponent();
            foreach ($pipeline as $pipeline_item){
                $lang_lists = [];
                if(!empty(app()->params['sites']['zf-pc']['secondary_domain'][$pipeline_item])){
                    $lang_lists = array_keys(app()->params['sites']['zf-pc']['secondary_domain'][$pipeline_item]);
                    if(empty($lang_lists)){
                        continue;
                    }
                }
                foreach ($lang_lists as $lang){
                    $tmp['site_code'] = $pageModel->site_code;
                    $tmp['pipeline'] = $pipeline_item;
                    $tmp['lang'] = $lang;
                    $tmp['skus'] = $params['skus'];
                    $tmp['pageId'] = $params['page_id'];
                    $tmp['api'] = $params['api'];
                    if(!empty($content['lang'])){
                        //找到所有的语言
                        $content['lang'] = $lang;
                    }
                    if(!empty($content['pipeline'])){
                        $content['pipeline'] = $pipeline_item;
                    }
                    $tmp['content'] = json_encode($content);
                    if($params['api']=='isseckill'){
                        //秒杀组件
                        $diff_skus =array_merge($diff_skus, explode(',',$goods->checkIsseckillGoodsExitsWithAPI($tmp)));
                    }else{
                        $diff_skus = array_merge($diff_skus,explode(',',$goods->checkGoodsExitsWithAPI($tmp)));
                    }
                }

            }

            //求并集。
            $diff_skus = array_unique($diff_skus);
        }
        $diff_skus = implode(',',$diff_skus);
        if (!empty($diff_skus)) {
            if(!empty($params['api']) && ($params['api'] == 'fullgiftlist' || $params['api']=='increasebuylist' || $params['api']=="getrankdetail")){
                return app()->helper->arrayResult(1, "请输入正确的ID", $diff_skus);
            }
            return app()->helper->arrayResult(1, "SKU {$diff_skus} 不存在", $diff_skus);
        }
        return app()->helper->arrayResult($this->codeSuccess,$this->msgSuccess,[]);
    }


    /**
     * 保存日志
     * @param $type
     * @param $params
     * @throws JsonResponseException
     * @author yuanwenguang 2019/4/2 15:59
     */
    public function saveLog($type,$params){
        if(empty($type)){
            throw new JsonResponseException($this->codeFail,"type不能为空");
        }

        $page_group_id = PageGroupModel::getPageGroupIdByPageId($params['page_id']);
        $page_operate_log =new PageOperateLogModel();

        $page_operate_log->type = $type;
        $page_operate_log->page_group_id = $page_group_id;
        $content['platform'] = $params['platform'];
        $content['pipeline'] = $params['pipeline'];
        $content['form_data'] = $params['form_data'];
        $page_operate_log->content = json_encode($content);

        if(!$page_operate_log->save()){
            throw new JsonResponseException($this->codeFail,"保存操作日志是吧");
        }
    }
}