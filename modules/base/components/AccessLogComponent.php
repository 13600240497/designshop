<?php

namespace app\modules\base\components;

use app\base\SitePlatform;
use app\modules\base\models\SysRequestLogModel;

/**
 * 访问日志组件
 * @package app\modules\activity\components
 */
class AccessLogComponent extends Component
{
    private static $requestId = null;
    private static $pageIds = [];

    /**
     * 将当前操作的活动页面ID记录的日志中
     * @param mixed $pageId 页面ID,可以使用数组
     */
    public static function addPageId($pageId) {
        $pageIds = (array) $pageId;
        foreach ($pageIds as $_pageId) {
            if (is_numeric($_pageId)) {
                self::$pageIds[] = (int)$_pageId;
            }
        }
    }

    /**
     * 获取作当前请求ID
     */
    public static function getRequestId()
    {
        return self::$requestId;
    }

    /**
     * 初始化日志变量
     */
    public static function initLog()
    {
        if (null === static::$requestId) {
            self::$requestId = md5(uniqid(mt_rand(), true) . random_int(0, 10000));
        }
    }

    /**
     * 记录请求日志
     */
    public static function requestLogging()
    {
        if (app()->user->getIsGuest())
            return;

        if ('geshop' == app()->controller->module->module->id) {
            $module = app()->controller->module;
        } else {
            $module = app()->controller->module->module;
        }

        $data = [
            'request_id'    => self::$requestId,
            'website_code'  => SitePlatform::getCurrentSiteGroupCode(),
            'request_date'  => date('Y-m-d'),
            'username'      => app()->user->username,
            'module'        => $module->id,
            'page_ids'       => empty(self::$pageIds) ? '0' : join(',', array_unique(self::$pageIds)),
            'request_route' => app()->controller->getRoute(),
            'request_url'   => app()->request->url,
            'method'        => app()->request->method,
            'request_time'  => time(),
            'user_ip'       => app()->request->getUserIP(),
            'post_params'   => empty($_POST) ? '' : json_encode($_POST)
        ];

        SysRequestLogModel::insertAll(array_keys($data), [array_values($data)]);
    }

    /**
     * 清除6个月以前的日志
     */
    public static function cleanExpiredLog()
    {
        $time = strtotime('-6 months');
        SysRequestLogModel::deleteAll(['<', 'request_date', date('Y-m-d', $time)]);
    }
}