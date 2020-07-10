<?php

namespace app\commands\models;

use app\base\RedisKey;

/**
 * Class PageModel 页面管理
 *
 * @package app\commands\models
 * @property int $status;
 *
 */
class PageModel extends BaseModel
{
    //页面状态|1待上线
    const PAGE_STATUS_TO_BE_ONLINE = 1;
    
    //页面状态|2已上线
    const PAGE_STATUS_HAS_ONLINE = 2;
    
    //页面状态|已发布
    const PAGE_STATUS_HAS_RELEASE = 3;

    //页面状态|已下线
    const PAGE_STATUS_HAS_OFFLINE = 4;

    //首页A
    const HOME_A = 0;
    
    //首页B
    const HOME_B = 1;
    
    /**
     * 变更首页发布状态
     *
     * @param $pageId
     * @return bool
     */
    public static function changeHomePageState($pageId)
    {
        $pageModel = static::getById($pageId);
        $pageModel->status = (self::PAGE_STATUS_TO_BE_ONLINE == $pageModel->status) ? self::PAGE_STATUS_HAS_RELEASE : $pageModel->status;
        return $pageModel->save(true);
    }
    
    /**
     * 下线所有正在启用的首页
     *
     * @param $pageId
     */
    public static function offlineHomeOnlinePage($siteCode, $pageId)
    {
        $pageModel = static::getById($pageId);
        $onlineHomePage = static::getHomeOnlineIds($siteCode, $pageModel->group_id, $pageModel->pipeline);
        
        if (!empty($onlineHomePage) && is_array($onlineHomePage)) {
            $onlineIds = array_column($onlineHomePage, 'id');
            static::updateAll(['status' => self::PAGE_STATUS_HAS_RELEASE], ['id' => $onlineIds]);
        }
    }
    
    /**
     * 下线所有的AB测试页的B页
     *
     * @param $pageId
     */
    public static function offlineHomeOnlinePageB($siteCode, $pageId)
    {
        $pageModel = static::getById($pageId);
        $onlineHomePage = static::getHomeBOnlineIds($siteCode, $pageModel->group_id, $pageModel->pipeline);
        
        if (!empty($onlineHomePage) && is_array($onlineHomePage)) {
            $onlineIds = array_column($onlineHomePage, 'id');
            static::updateAll(['status' => self::PAGE_STATUS_HAS_RELEASE], ['id' => $onlineIds]);
        }
    }
    
    /**
     * 获取正在上线的首页id
     *
     * @param string $siteCode
     * @param string $groupId
     * @param string $pipeline
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getHomeOnlineIds(string $siteCode, string $groupId, string $pipeline)
    {
        return static::find()->select('id')
            ->where([
                'activity_id' => 0,
                'status'      => self::PAGE_STATUS_HAS_ONLINE,
                'pipeline'    => $pipeline,
                'site_code'   => $siteCode,
                'home_type'   => self::HOME_A
            ])
            ->andWhere(['<>', 'group_id', $groupId])
            ->asArray()->all();
    }
    
    /**
     * 获取正在上线的B首页id
     *
     * @param string $siteCode
     * @param string $groupId
     * @param string $pipeline
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getHomeBOnlineIds(string $siteCode, string $groupId, string $pipeline)
    {
        return static::find()->select('id')
            ->where([
                'activity_id' => 0,
                'status'      => self::PAGE_STATUS_HAS_ONLINE,
                'pipeline'    => $pipeline,
                'site_code'   => $siteCode,
                'home_type'   => self::HOME_B
            ])
            ->andWhere(['<>', 'group_id', $groupId])
            ->asArray()->all();
    }
}
