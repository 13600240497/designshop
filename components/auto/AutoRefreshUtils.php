<?php
namespace app\components\auto;

abstract class AutoRefreshUtils
{
    /**
     * 给日志信息添加固定前缀
     *
     * @param string $message 日志信息
     * @return string
     */
    public static function getLogMessagePrefix($message)
    {
        return '[组件自动刷新] '. $message;
    }

    /**
     * 获取获取类实例
     *
     * @param string $class
     * @param array $params
     * @return mixed
     */
    public static function getInstance($class, $params = [])
    {
        if (!app()->has($class)) {
            $params['class'] = $class;
            app()->set($class, $params);
        }
        return app()->get($class);
    }

    /**
     * 获取活动页面异步数据json数据文件名称
     *
     * @param int $pageId
     * @return string
     */
    public static function getAsyncDataJsFileName($pageId)
    {
        return 'async-data-'. $pageId .'.json';
    }

	/**
	 * 获取活动原生页面异步数据json数据文件名称
	 *
	 * @param int    $pageId
	 *
	 * @return string
	 */
	public static function getAsyncDataNativeFileName(int $pageId)
	{
		return "api-async-data-{$pageId}.json";
	}
}
