<?php

if (!function_exists('config')) {
	/**
	 * 获取配置文件内容
	 *
	 * @param array|string $key example：soa.gb.env.version
	 */
	function config (string $key = '')
	{
		if (empty($key)) {
			return app()->params ?? '';
		}

		if (!empty(app()->params) && is_array(app()->params)) {
			$config = app()->params;
			$keyArr = explode('.', $key);
			foreach ($keyArr as $keyStr) {
				if (!isset($config[$keyStr])) {
					return '';
					break;
				}
				$config = $config[$keyStr];
			}
		}

		return $config ?? '';
	}
}

if (!function_exists('apolloConfig')) {
	/**
	 * 获取 apollo 配置文件内容
	 */
	function apolloConfig (string $key = '')
	{
		$config = [];
		$handler = opendir(APOLLO_CONFIG_PATH);
		while (($fileName = readdir($handler)) !== false) {
			if ('.' === $fileName || '..' === $fileName) {
				continue;
			}
			$filePath = APOLLO_CONFIG_PATH . '/' . $fileName;
			if (is_readable($filePath)) {
				$fileData = parse_ini_file($filePath, true, INI_SCANNER_RAW);
				if (!empty($fileData) && is_array($fileData)) {
					$config = array_map(function ($item) {
						return (is_string($item) && json_decode($item)) ? json_decode($item, true) : $item;
					}, $config);
					$config = array_merge($config, $fileData);
				}
			}
		}
		closedir($handler);

		if (!empty($config) && is_array($config)) {
			if (empty($key)) {
				return $config;
			}

			$config = !empty($config[$key]) ? $config[$key] : '';
		}

		return $config;
	}
}

if (!function_exists('isGearbestSite')) {
	/**
	 * 是否是GB站点
	 *
	 * @param string $siteCode
	 *
	 * @return boolean
	 */
	function isGearbestSite ($siteCode = null)
	{
		$groupCode = (null == $siteCode) ? SITE_GROUP_CODE : \app\base\SitePlatform::getSiteGroupCodeBySiteCode($siteCode);

		return \app\base\SiteConstants::SITE_GROUP_CODE_GB == $groupCode;
	}
}

/**
 * 是否是Zuful站点
 *
 * @param string $code 站点简码(siteCode)或站点组简码(siteGroupCode)
 *
 * @return boolean
 */
function isZufulSite ($code = null)
{
	if (null === $code) {
		$groupCode = SITE_GROUP_CODE;
	} else {
		$groupCode = (strpos($code, \app\base\SitePlatform::SITE_CODE_SEPARATOR) !== false) ?
			\app\base\SitePlatform::getSiteGroupCodeBySiteCode($code) : $code;
	}

	return \app\base\SiteConstants::SITE_GROUP_CODE_ZF == $groupCode;
}

/**
 * 是否是Dresslily站点
 *
 * @param string $code 站点简码(siteCode)或站点组简码(siteGroupCode)
 *
 * @return boolean
 */
function isDresslilySite ($code = null)
{
	if (null === $code) {
		$groupCode = SITE_GROUP_CODE;
	} else {
		$groupCode = (strpos($code, \app\base\SitePlatform::SITE_CODE_SEPARATOR) !== false) ?
			\app\base\SitePlatform::getSiteGroupCodeBySiteCode($code) : $code;
	}

	return \app\base\SiteConstants::SITE_GROUP_CODE_DL == $groupCode;
}

/**
 * 专题活动三端页面组件取交集
 */
if (!function_exists('componentIntersect')) {
	function componentIntersect (array $data)
	{
		$list = [];
		$aL = arrayLevel($data);
		if (2 == $aL) {
			$firstItem = array_shift($data);
			if (!empty($data) && is_array($data)) {
				foreach ($firstItem as $key => $item) {
					if (false !== array_search([], $data)) {
						break;
					}
					$find = true;
					foreach ($data as $dataKey => $pipeData) {
						if (false === array_search($item, $pipeData)) {
							$find = false;
						}

						foreach ($pipeData as $pipeKey => $pipe) {
							if ($pipe == $item) {
								unset($data[$dataKey][$pipeKey]);
								break;
							}
						}
					}
					if (true === $find) {
						$list[$key] = $item;
					}
					//print_r($list);
					//echo "--------------------------------------\r\n";
				}
				//die;
				//echo "***********************************************\r\n";
				return $list;
			}

			//die;
			return $firstItem;
		} elseif ($aL > 2) {
			foreach ($data as $p_key => $platform) {
				if (is_array($platform) && count($platform) > 1) {
					$data[$p_key] = componentIntersect($platform);
				} else {
					$data[$p_key] = !empty($platform) ? current($platform) : [];
				}
			}

			return componentIntersect($data);
		}
	}
}

/**
 * 返回数组的维度
 *
 * @param  [type] $arr [description]
 *
 * @return [type]      [description]
 */
if (!function_exists('arrayLevel')) {
	function arrayLevel ($arr)
	{
		if (!is_array($arr)) {
			return 0;
		} else {
			$max1 = 0;
			foreach ($arr as $item1) {
				$t1 = arrayLevel($item1);
				if ($t1 > $max1) $max1 = $t1;
			}

			return $max1 + 1;
		}
	}

	/**
	 * 数字转字母 （类似于Excel列标）
	 *
	 * @param Int $index 索引值
	 * @param Int $start 字母起始值
	 *
	 * @return String 返回字母
	 */
	if (!function_exists('intToChr')) {
		function intToChr ($index, $start = 65)
		{
			$str = '';
			if (floor($index / 26) > 0) {
				$str .= intToChr(floor($index / 26) - 1);
			}

			return $str . chr($index % 26 + $start);
		}
	}

	/**
	 * 处理中文输出问题
	 *
	 * @param $str
	 *
	 * @return string
	 */
	if (!function_exists('convertUTF8')) {
		function convertUTF8 ($str)
		{
			if (empty($str)) return '';

			return iconv('GB18030', 'UTF-8', $str);
		}
	}

	if (!function_exists('printFormat')) {
		function printFormat ($data)
		{
			echo '<pre>';
			print_r($data);
			die;
		}
	}
}

/**
 * 在对外接口和定时任务无法获取到 SITE_GROUP_CODE 手动设置 SITE_GROUP_CODE_FIXED 新常量
 *
 * @param string $siteCode 站点简码，如: zf-pc
 */
function ges_set_site_group_code($siteCode)
{
    list($websiteCode, ) = explode('-', $siteCode, 2);
    if (!defined('SITE_GROUP_CODE_FIXED')) {
        define('SITE_GROUP_CODE_FIXED', strtolower($websiteCode));
    }
}

/**
 * 对字符串进行一次替换
 *
 * @param string $needle 原字符串
 * @param string $replace 替换符串
 * @param string $haystack 文本
 * @return string
 */
function ges_str_replace_once($needle, $replace, $haystack)
{
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

