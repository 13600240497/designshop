<?php
namespace app\modules\activity\zf\cache;


class PageCache
{
    /**
     * 一级前缀
     */
    const AllPageCachePrefix = 'PageCache';
    /**
     * 二级前缀
     */
    const SiteAndActityPrefix = 'PageListGroupInfoAndLanguagesKey';

    /**
     * 生成缓存的KEY
     * @param $site string 站点
     * @param $actityId int 活动也ID
     * @param $pageIds string 页面id 例如:978,268
     * @return string
     */
    public function generateKey($site, $actityId, $pageIds)
    {
        return $this->getPrefix($site, $actityId).'-'. md5($pageIds);
    }

    /**
     * 生成缓存的KEY
     * @param $site string
     * @param $actityId int
     * @param $pageIds string
     * @return string
     */
    public function getPrefix($site, $actityId)
    {
        $key = substr(md5(self::SiteAndActityPrefix . $site . $actityId), 0, 16);
        return self::AllPageCachePrefix . '-' . $key;
    }

    /**
     * 删除缓存
     * @param $site string
     * @param $actityId int
     * @param $pageIds string
     * @return string
     */
    public function refreshKey($site, $actityId)
    {
        $result = app()->redis->keys($this->getPrefix($site, $actityId) . '*');
        $result && app()->redis->del($result);
        return count($result);
    }
}