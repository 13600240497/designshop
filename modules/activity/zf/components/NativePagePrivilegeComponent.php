<?php
namespace app\modules\activity\zf\components;

use app\base\SitePlatform;
use app\modules\common\zf\components\CommonPageDesignComponent;
use app\modules\common\zf\models\PageGroupModel;


/**
 * 原生页面权限组件
 */
class NativePagePrivilegeComponent extends CommonPageDesignComponent
{
    /** @var int 原生页面装修页面锁定时间 */
    const LOCK_EXPIRE_TIME = 60 * 1;

    /**
     * 对装修页面加锁，加锁成功后其他用户不能进入装修页面
     *
     * @param string $websiteCode 网站简码,如： zf/rg/dl
     * @param int $pageIds 当前装修的移动(包含WAP和APP)页面ID
     * @param int $userId 当前登录的用户ID
     * @return bool
     */
    public function lockNativePageDesign($websiteCode, $pageIds, $userId)
    {
        // 过期时间加上 30 秒的请求接口延迟时间
        $expireTime = self::LOCK_EXPIRE_TIME + 5;
        $lockKey = $this->getLockKey($websiteCode, $pageIds);
        $lockUserId = app()->redis->get($lockKey);
        if (empty($lockUserId)) {
            app()->redis->setex($lockKey, $expireTime, $userId);
        } else {
            if ($lockUserId == $userId) {
                app()->redis->expire($lockKey, $expireTime);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * 对装修页面解锁
     *
     * @param string $websiteCode 网站简码,如： zf/rg/dl
     * @param int  $pageId 当前装修的移动(包含WAP和APP)页面ID
     */
    public function unlockNativePageDesign($websiteCode, $pageId)
    {
        $lockKey = $this->getLockKey($websiteCode, $pageId);
        app()->redis->del($lockKey);
    }

    /**
     * 获取当前持有锁的用户ID
     *
     * @param string $websiteCode 网站简码,如： zf/rg/dl
     * @param int  $pageIds 当前装修的移动(包含WAP和APP)页面ID
     * @return int
     */
    public function getCurrentDesignUserId($websiteCode, $pageIds)
    {
        $lockKey = $this->getLockKey($websiteCode, $pageIds);
        return app()->redis->get($lockKey);
    }

    /**
     * 获取移动端(包含WAP和APP)要锁定页面ID
     *
     * @param \app\modules\common\zf\models\PageModel $pageModel
     * @return string
     */
    public function getLockPageIds($pageModel)
    {
        $pageGroupId = PageGroupModel::getPageGroupIdByPageId($pageModel->id);
        $groupPipelinePageIds = PageGroupModel::find()->select('platform_type, page_id')
            ->where(['page_group_id' => $pageGroupId, 'pipeline' => $pageModel->pipeline])
            ->indexBy('platform_type')
            ->asArray()
            ->all();

        $wapPageId = $groupPipelinePageIds[SitePlatform::PLATFORM_TYPE_WAP]['page_id'] ?? '';
        $appPageId = $groupPipelinePageIds[SitePlatform::PLATFORM_TYPE_APP]['page_id'] ?? '';
        return $wapPageId .'-'. $appPageId;
    }

    /**
     * 获取装修页面锁redis缓存键名称
     *
     * @param string $websiteCode 网站简码,如： zf/rg/dl
     * @param int  $pageIds 当前装修的页面ID
     * @return string
     */
    private function getLockKey($websiteCode, $pageIds)
    {
        return sprintf('geshop:%s:%s:native:design:%s', YII_ENV, $websiteCode, $pageIds);
    }
}