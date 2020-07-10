<?php
namespace app\base;

/**
 * redis缓存key管理组件
 */
class RedisKey
{
    //定时任务：refresh任务锁key
    const REFRESH_TASK_LOCK_KEY = FULL_DOMAIN . '::geshop::refresh::lock';

    //定时任务：offline任务锁key
    const OFFLINE_TASK_LOCK_KEY = FULL_DOMAIN . '::geshop::offline::lock';

    //推送页面redis任务队列key
    const PUSH_TO_S3_LIST_KEY = FULL_DOMAIN . '::geshop::push::list';

    //自动刷新redis任务队列key
    const REFRESH_LIST_KEY = FULL_DOMAIN . '::geshop::refresh::list';

    //刷新站点页面任务锁key
    const REFRESH_SITE_TASK_LOCK_KEY = FULL_DOMAIN . '::geshop::refresh::site::lock';

    //页面装修互斥锁key
    const DESIGN_PAGE_LOCK_KEY = FULL_DOMAIN . '::geshop::page::auth::lock';
    
    //组件三端绑定互斥锁key
    const COMPONENT_BIND_LOCK_KEY = FULL_DOMAIN . '::geshop::component::bind::auth::lock';

    //页面发布内容缓存key
    const PUBLISH_CONTENT_KEY = FULL_DOMAIN . '::geshop::publish::content';

    //同步resources目录任务key
    const SYNC_RESOURCES_KEY = FULL_DOMAIN . '::geshop::sync::resources';

    //同步用户信息任务key
    const SYNC_USERS_KEY = FULL_DOMAIN . '::geshop::sync::users';

    //站点头尾部加密内容缓存key
    const SITE_HEAD_FOOTER_MD5_KEY = FULL_DOMAIN . '::geshop::headfooter::list';

    //Ui组件配置key
    const UI_CONFIG_KEY = FULL_DOMAIN . '::geshop::ui::config';

    //页面模板缓存key
    const PAGE_TPL_KEY = FULL_DOMAIN . '::geshop::page::tpl::cache20180831';

    //默认图片配置
    const DEFAULT_IMAGE_CONFIG_KEY = FULL_DOMAIN . '::geshop::default::image';

    //更新上线产品锁key
    const ONLINE_GOODS_LOCK_KEY = FULL_DOMAIN .'::geshop::online::goods::lock';

    //上线页面
    const ONLINE_PAGES_LISL_KEY = FULL_DOMAIN . '::geshop::pages::list';

    //上线页面锁key
    const ONLINE_PAGES_LOCK_KEY = FULL_DOMAIN . '::geshop::pages::lock';

    //推送页面到网站key
    const PUSH_ADVERTISEMENT_LIST_KEY = FULL_DOMAIN . '::geshop::advertisement::list';

    //用户缓存key
    const ADMIN_USER_KEY = FULL_DOMAIN . '::geshop::admin:user';
    
    //页面发布缓存
    const PAGE_PUBLISH_CACHE_KEY = FULL_DOMAIN . '::geshop::page::publish';
    
    //首页回滚历史版本锁定key
    const HOME_PAGE_ROLLBACK_LOCK_KEY = FULL_DOMAIN . '::geshop::home::page::rollback::lock';

    // WAP原生页Json文件数据缓存
    const NATIVE_WAP_JSON_CACHE_KEY = '::geshop::native::wap::json';

    // APP原生页Json文件数据缓存
    const NATIVE_APP_JSON_CACHE_KEY = '::geshop::native::app::json';

    //环境开发者，用来隔离开类似“预发布”和“正式”这种公用数据库和域名的环境
    public $developer;

    public function __construct()
    {
        $this->developer = \ego\base\Application::getDeveloper();
    }

    /**
     * 获取refresh任务锁的key
     *
     * @return string
     */
    public function getRefreshTaskLockKey()
    {
        return $this->developer . '::' . static::REFRESH_TASK_LOCK_KEY;
    }

    /**
     * 获取offline任务锁的key
     *
     * @return string
     */
    public function getOfflineTaskLockKey()
    {
        return $this->developer . '::' . static::OFFLINE_TASK_LOCK_KEY;
    }

    /**
     * 获取push页面任务在redis中的key
     *
     * @return string
     */
    public function getPushTaskRedisKey()
    {
        return $this->developer . '::' . static::PUSH_TO_S3_LIST_KEY;
    }

    /**
     * 获取自动刷新任务在redis中的key
     *
     * @return string
     */
    public function getRefreshTaskRedisKey()
    {
        return $this->developer . '::' . static::REFRESH_LIST_KEY;
    }

    /**
     * 获取刷新站点页面任务锁的key
     *
     * @param string $siteCode  站点siteCode
     * @param string $module    模块名称activity/home
     * @return string
     */
    public function getRefreshSiteTaskLockKey(string $siteCode, string  $module, string $pipeline = '')
    {
        return $this->developer . '::' . static::REFRESH_SITE_TASK_LOCK_KEY . '::' . $siteCode . '::' . $pipeline . '::' . $module;
    }

    /**
     * 获取页面装修互斥锁key
     *
     * @param int $pageId 页面ID
     * @param string $lang 装修语言
     * @return string
     */
    public function getDesignPageLockKey($pageId, $lang='')
    {
        return $this->developer . '::' . static::DESIGN_PAGE_LOCK_KEY . '::' . $pageId .'::'.$lang;
    }
    
    /**
     * 获取组件三端绑定互斥锁key
     *
     * @param int $pageId 页面ID
     * @param string $lang 装修语言
     * @return string
     */
    public function getComponentBindLockKey($pageId, $lang='')
    {
        return $this->developer . '::' . static::COMPONENT_BIND_LOCK_KEY . '::' . $pageId .'::'.$lang;
    }
    
    /**
     * 获取页面发布内容缓存key
     *
     * @param array $params = [
     *      int $pageId 页面ID
     *      string $lang 语言代码简称
     *      string $fileType 文件类型，html/js/css
     *      string $version 版本号
     * ]
     *
     * @return string
     */
    public function getPublishContentKey(array $params)
    {
        list($pageId, $lang, $fileType, $version) = $params;
        return $this->developer . '::' . static::PUBLISH_CONTENT_KEY
            . '::' . $pageId . '::' . $lang . '::' . $fileType . '::' . $version;
    }

    /**
     * 获取同步resources目录任务在redis中的key
     *
     * @return string
     */
    public function getSyncResourcesRedisKey()
    {
        return $this->developer . '::' . static::SYNC_RESOURCES_KEY;
    }

    /**
     * 获取同步用户信息在redis中的key
     *
     * @return string
     */
    public function getSyncUsersRedisKey()
    {
        return $this->developer . '::' . static::SYNC_USERS_KEY;
    }

    /** 获取点头尾部加密内容缓存在redis的key
     *
     * @return string
     */
    public function getSiteHeadFooterMd5Key()
    {
        return $this->developer . '::' . static::SITE_HEAD_FOOTER_MD5_KEY;
    }

    /**
     * 获取Ui组件配置key
     *
     * @param int $id
     * @param string $field
     * @return string
     */
    public function getUiConfigRedisKey($id, $field)
    {
        return $this->developer . '::' . static::UI_CONFIG_KEY . '::' . $id . '::' . $field;
    }

    /**
     * 获取页面模板缓存key
     *
     * @param int $id
     * @return string
     */
    public function getPageTplRedisKey($id)
    {
        return $this->developer . '::' . static::PAGE_TPL_KEY . '::' . $id;
    }

    /**
     * 获取默认图片缓存key
     *
     * @return string
     */
    public function getDefaultImageRedisKey()
    {
        return $this->developer . '::' . static::DEFAULT_IMAGE_CONFIG_KEY;
    }

    /**
     * 获取更新上线产品key
     *
     * @return string
     */
    public function getOnlineGoodsRedisKey()
    {
        return $this->developer . '::' . static::ONLINE_GOODS_LOCK_KEY;
    }

    /**
     * 获取更新上线页面key
     *
     * @return string
     */
    public function getOnlinePagesRedisKey()
    {
        return $this->developer . '::' . static::ONLINE_PAGES_LISL_KEY;
    }

    /**
     * 获取更新上线页面key
     *
     * @return string
     */
    public function getOnlinePagesLockRedisKey()
    {
        return $this->developer . '::' . static::ONLINE_PAGES_LOCK_KEY;
    }

    /**
     * 获取推送html css js到网站key
     *
     * @return string
     */
    public function getPushAdvertisementRedisKey()
    {
       return $this->developer . '::' . static::PUSH_ADVERTISEMENT_LIST_KEY;
    }

    /**
     * 获取用户缓存key
     * @return string
     */
    public function getAdminUserKey()
    {
        return $this->developer . '::' . static::ADMIN_USER_KEY;
    }


    /**
     * 获取 dresslily 站点缓存key,区分其他站点
     * @param string $key
     * @return string
     */
    public function getDresslilySiteKey($key) {
        return 'dl::'. $key;
    }
    
    /**
     * 获取页面内容缓存记录缓存key
     *
     * @param string $siteCode
     *
     * @return string
     */
    public function getPagePublishCacheKey(string $siteCode)
    {
        return $this->developer . '::' . static::PAGE_PUBLISH_CACHE_KEY . '::' . $siteCode;
    }
    
    /**
     * 首页回滚历史版本锁定
     *
     * @param string $siteCode
     *
     * @return string
     */
    public function getHomePageRollbackLockKey(string $siteCode)
    {
        return $this->developer . '::' . static::HOME_PAGE_ROLLBACK_LOCK_KEY . '::' . $siteCode;
    }

    /**
     * WAP原生页Json文件数据缓存
     *
     * @param string $siteCode
     *
     * @return string
     */
    public function getNativeWapJsonCacheKey(string $siteCode)
    {
        return config('appDeveloper') . '::'  . config('appFullDomain') . static::NATIVE_WAP_JSON_CACHE_KEY . '::' . $siteCode;
    }

	/**
	 * APP原生页Json文件数据缓存
	 *
	 * @param string $siteCode
	 *
	 * @return string
	 */
    public function getNativeAppJsonCacheKey(string $siteCode)
    {
	    return config('appDeveloper') . '::'  . config('appFullDomain') . static::NATIVE_APP_JSON_CACHE_KEY . '::' . $siteCode;
    }

  /**
   * 获取组件异步接口兜底队列key
   *
   * @param string $websiteCode 网站简码，如: zf/rg
   * @return string
   */
    public function getUiAsyncApiFallbackListKey(string $websiteCode)
    {
      return 'geshop::'. YII_ENV .'::ui::fallback::'. $websiteCode;
    }

  /**
   * 获取系统配置key
   *
   * @return string
   */
    public function getSysConfigKey()
    {
      return 'geshop::'. YII_ENV .'::sys::configs';
    }
}
