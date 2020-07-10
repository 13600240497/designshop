<?php

namespace app\base;

use app\modules\base\components\LanguageDataComponent;
use yii;

/**
 * 翻译函数
 */
class Translate
{
	private static $transMessageCache = [];

	/**
	 * Gb站点组件公用内容翻译
	 *
	 * @param string $language 语言简码; 如：en,ru
	 * @param string $message  翻译文本下标; 如: coming_soon
	 * @param array  $params   翻译文本所需参数，默认NULL
	 *
	 * @return string
	 */
	public static function gbUiComponentTrans ($language, $message, $params = NULL)
	{
		$language = empty($language) ? 'en' : str_replace('_', '-', $language);
		if (!isset(static::$transMessageCache[$language])) {
			$langFile = yii::getAlias('@app/files/languages/gb/' . $language . '/component.php');
			$langMessage = require($langFile);
			static::$transMessageCache[$language] = $langMessage;
		}

		$message = static::$transMessageCache[$language][$message] ?? '';
		if (!empty($message) && !empty($params) && is_array($params)) {
			$message = sprintf($message, ...$params);
		}

		return $message;
	}

	public static function getGbUiComponentJsTransMessage ($language)
	{
		$language = empty($language) ? 'en' : str_replace('_', '-', $language);
		$langFile = yii::getAlias('@app/files/languages/gb/' . $language . '/component.php');
		$languages = require($langFile);
		$langData = (new LanguageDataComponent())->getLanguageDataForLang($language);
		$languages = !empty($langData) ? array_merge($languages, $langData) : $languages;

		$jsBody = sprintf('var GESHOP_LANGUAGES=%s;', json_encode($languages, JSON_UNESCAPED_UNICODE));
		return $jsBody;
	}

	/**
	 * 站点组件公用内容翻译
	 *
	 * @param string $language 语言简码; 如：en,ru
	 * @param string $message  翻译文本下标; 如: coming_soon
	 * @param array  $params   翻译文本所需参数，默认NULL
	 * @param string $siteCode 站点简码
	 *
	 * @return string
	 */
	public static function getUiComponentTrans ($language, $message, $params = NULL, $siteCode = '')
	{
		$siteCode = $siteCode ? $siteCode : SitePlatform::getCurrentSiteGroupCode();
		$language = empty($language) ? 'en' : str_replace('_', '-', $language);
		if (!isset(static::$transMessageCache[$language])) {
			$commonLang = require(yii::getAlias('@app/files/languages/common/' . $language . '.php'));
			$langFile = yii::getAlias('@app/files/languages/' . $siteCode . '/' . $language . '.php');
			$langMessage = is_file($langFile) ? array_merge($commonLang['tpl'], require ($langFile)['tpl']) : $commonLang['tpl'];
			static::$transMessageCache[$language] = $langMessage;
		}

		$message = static::$transMessageCache[$language][$message] ?? '';
		if (!empty($message) && !empty($params) && is_array($params)) {
			$message = sprintf($message, ...$params);
		}

		return $message;
	}

	/*
	 * 获取js翻译
	 * @param  string   $language
	 * @param  string   $siteCode
	 * @return string
	 */
	public static function getUiComponentJsTransMessage ($language, $siteCode = '')
	{
		$siteCode = $siteCode ? $siteCode : SitePlatform::getCurrentSiteGroupCode();
		$language = empty($language) ? 'en' : str_replace('_', '-', $language);
		$commonLangFile = yii::getAlias('@app/files/languages/common/' . $language . '.php');
		$commonLang = require($commonLangFile);
		$langFile = yii::getAlias('@app/files/languages/' . $siteCode . '/' . $language . '.php');
		$languages = is_file($langFile) ? array_merge($commonLang['js'], require ($langFile)['js']) : $commonLang['js'];
		$jsBody = sprintf('var GESHOP_LANGUAGES=%s;', json_encode($languages, JSON_UNESCAPED_UNICODE));
		$langData = (new LanguageDataComponent())->getLanguageDataForLang($language);
		if (!empty($langData)) {
			$langData = array_map(function ($item) {
				return stripslashes($item);
			}, $langData);
		}
		$jsBody .= sprintf('var GESHOP_LANGUAGES_V2=%s;', json_encode($langData, JSON_UNESCAPED_UNICODE));

		return $jsBody;
	}
}
