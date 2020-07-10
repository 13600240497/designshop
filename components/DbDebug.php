<?php
namespace app\components;

/**
 * php日志接口组件
 */
class DbDebug
{
	static $sqlList = [];

	public static function info($sql)
	{
		self::$sqlList[] = $sql;
	}

	public static function getSqlList()
	{
		return self::$sqlList;
	}
}