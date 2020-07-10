<?php
namespace app\base;

class RequestUtils
{
    public static function getModuleName()
    {
        if (!empty(app()->controller->module->module)) {
            $moduleName = app()->controller->module->module->id;
            if ('geshop' != $moduleName) {
                return $moduleName;
            }
        }

        return app()->controller->module->id;
    }

    public static function getPageTypeByModuleName()
    {
        $moduleName = self::getModuleName();
        if (SiteConstants::MODULE_NAME_HOME == $moduleName) {
            return SiteConstants::ACTIVITY_PAGE_TYPE_HOME;
        } elseif (SiteConstants::MODULE_NAME_ACTIVITY == $moduleName) {
            return SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL;
        } elseif (SiteConstants::MODULE_NAME_ADVERTISEMENT == $moduleName) {
            return SiteConstants::ACTIVITY_PAGE_TYPE_ADVERTISEMENT;
        }
        return SiteConstants::ACTIVITY_PAGE_TYPE_UNKNOW;
    }

    /**
     * 先返回信息给前端，然后后端继续处理
     *
     * @param string $message 返回信息
     */
    public static function closeConnectionAndFlush($message)
    {
        $content = json_encode(app()->helper->arrayResult(0, $message));
        ob_end_clean();
        header('Connection: close');
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json;charset=utf-8');// 如果前端要的是json则添加，默认是返回的html/text
        ob_start();
        echo $content;// 输出结果到前端
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
        flush();
        if (function_exists('fastcgi_finish_request')) { // yii或yaf默认不会立即输出，加上此句即可（前提是用的fpm）
            fastcgi_finish_request(); // 响应完成, 立即返回到前端,关闭连接
        }
    }
}