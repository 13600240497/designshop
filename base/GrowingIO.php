<?php
namespace app\base;

use yii\helpers\Json;
use app\common\constants\SiteConstants;

/**
 * GrowingIO 埋点
 *
 * @package app\base
 */
class GrowingIO
{
    /**
     * 是否支持
     *
     * @param string $siteCode
     * @return bool
     */
    public static function isSupport($siteCode)
    {
        return in_array($siteCode, ['zf-wap', 'zf-app'], true);
    }

    /**
     * UI组件增加DIV层包裹
     *
     * @param int $uiId 组件ID
     * @param string $uiBody 组件渲染内容
     * @return string
     */
    public static function warpUiBody($uiId, $uiBody)
    {
        return sprintf('<div class="js-growingio" data-growingio-id="%d">%s</div>', $uiId, $uiBody);
    }

    /**
     * 生成原生页组件埋点数据
     *
     * @param int $pageId
     * @param string $lang
     * @param array $uiListByLayoutPosition
     * @return string
     */
    public static function doGenerateNativeUiList($siteCode, $pageId, $lang, $uiListByLayoutPosition)
    {
        $pageUiGrowingInfo = [];
        $floorNum = 1;
        $websiteCode = \app\base\SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $pageInfo = self::getPageLangInfo($websiteCode, $pageId, $lang);
        $pageTitle = empty($pageInfo['title']) ? '' : $pageInfo['title'];
        $uiKeys = [];
        foreach ($uiListByLayoutPosition as $layoutId => $uiListByPosition) {
            foreach ($uiListByPosition as $positionId => $uiList) {
                foreach ($uiList as $uiInfo) {
                    $uiId = $uiInfo['id'];
                    $uiKeys[] = $uiInfo['component_key'];
                    $pageUiGrowingInfo[$uiId] = [
                        'goodsPageId_var' => $pageId,        // 页面ID
                        'goodsPage_evar' => $pageTitle,   // 页面title
                        'floor_evar' => $floorNum,       // layout索引
                        'positionId_var' => $uiId,    // 组件ID
                        'position_evar' => $uiInfo['component_key'], // 组件名称
                        'bannerId_var' => '', // Geshop banner id
                        'activityName_var' => '', // Geshop banner 名称
                    ];
                }
            }

            $floorNum++;
        }

        $uiInfo = self::getUiInfo($uiKeys);
        foreach ($pageUiGrowingInfo as &$growingInfo) {
            $key = $growingInfo['position_evar'];
            $growingInfo['position_evar'] = $uiInfo[$key] ?? '';
        }

        return Json::encode($pageUiGrowingInfo, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成原生页面组件埋点数据
     *
     * @param int $pageId
     * @param string $lang
     * @param array $uiListByLayoutPosition
     * @return string
     */
    public static function generateNativeUiList($siteCode, $pageId, $lang, $uiList)
    {
        foreach ($uiList as &$uiInfo) {
            $uiInfo['id'] = $uiInfo['component_id'];
        }

        $layoutId = 1;
        $uiListByLayoutPosition = [];
        foreach ($uiList as $uiItem) {
            $uiListByLayoutPosition[$layoutId++][1] = [$uiItem];
        }
        return self::doGenerateNativeUiList($siteCode, $pageId, $lang, $uiListByLayoutPosition);
    }

	/**
	 * 生成组件埋点数据
	 *
	 * @param int $pageId
	 * @param string $lang
	 * @param array $uiListByLayoutPosition
	 * @return string
	 */
	public static function doGenerateDefaultUiList($siteCode, $pageId, $lang, $orderedLayouts, $uiListByLayoutPosition)
	{
		$pageUiGrowingInfo = [];
		$floorNum = 1;
		$websiteCode = \app\base\SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
		$pageInfo = self::getPageLangInfo($websiteCode, $pageId, $lang);
		$pageTitle = empty($pageInfo['title']) ? '' : $pageInfo['title'];
		$uiKeys = [];

		foreach ($orderedLayouts as $layoutInfo) {
			$uiListByPosition = !empty($uiListByLayoutPosition[$layoutInfo['id']]) ? $uiListByLayoutPosition[$layoutInfo['id']] : [];
			if (!empty($uiListByPosition) && is_array($uiListByPosition)) {
				foreach ($uiListByPosition as $positionId => $uiList) {
					foreach ($uiList as $uiInfo) {
						if (arrayLevel($uiInfo) > 1) {
							foreach ($uiInfo as $info) {
								$uiId = $info['id'];
								$uiKeys[] = $info['component_key'];
								$pageUiGrowingInfo[$uiId] = [
									'goodsPageId_var' => $pageId,        // 页面ID
									'goodsPage_evar' => $pageTitle,   // 页面title
									'floor_evar' => $floorNum,       // layout索引
									'positionId_var' => $uiId,    // 组件ID
									'position_evar' => $info['component_key'], // 组件名称
									'bannerId_var' => '', // Geshop banner id
									'activityName_var' => '', // Geshop banner 名称
								];
							}
						} else {
							$uiId = $uiInfo['id'];
							$uiKeys[] = $uiInfo['component_key'];
							$pageUiGrowingInfo[$uiId] = [
								'goodsPageId_var' => $pageId,        // 页面ID
								'goodsPage_evar' => $pageTitle,   // 页面title
								'floor_evar' => $floorNum,       // layout索引
								'positionId_var' => $uiId,    // 组件ID
								'position_evar' => $uiInfo['component_key'], // 组件名称
								'bannerId_var' => '', // Geshop banner id
								'activityName_var' => '', // Geshop banner 名称
							];
						}
					}
				}
			}

			$floorNum++;
		}

		$uiInfo = self::getUiInfo($uiKeys);
		foreach ($pageUiGrowingInfo as &$growingInfo) {
			$key = $growingInfo['position_evar'];
			$growingInfo['position_evar'] = $uiInfo[$key] ?? '';
		}

		return Json::encode($pageUiGrowingInfo, JSON_UNESCAPED_UNICODE);
	}

    /**
     * 获取组件模板信息
     *
     * @param array $uiKeys
     * @return array|\yii\db\ActiveRecord[]
     */
    private static function getUiInfo($uiKeys)
    {
        if (empty($uiKeys)) {
            return [];
        }

        return \app\modules\component\models\UiModel::find()
            ->select('name')
            ->where(['component_key' => $uiKeys])
            ->asArray()
            ->indexBy('component_key')
            ->column();
    }

    /**
     * 获取页面信息
     *
     * @param string $websiteCode 站点简码
     * @param int $pageId 页面ID
     * @param string $lang 语言简码
     * @return array
     */
    private static function getPageLangInfo($websiteCode, $pageId, $lang)
    {
        if (SiteConstants::WEBSITE_CODE_ZF === $websiteCode) {
            return \app\modules\common\zf\models\PageLanguageModel
                ::find()
                ->select('title', 'seo_title')
                ->where(['page_id' => $pageId, 'lang' => $lang])
                ->asArray()
                ->one();
        } elseif (SiteConstants::WEBSITE_CODE_DL === $websiteCode) {
            return \app\modules\common\dl\models\PageLanguageModel
                ::find()
                ->select('title', 'seo_title')
                ->where(['page_id' => $pageId, 'lang' => $lang])
                ->asArray()
                ->one();
        } else {
            return \app\modules\common\models\PageLanguageModel
                ::find()
                ->select('title', 'seo_title')
                ->where(['page_id' => $pageId, 'lang' => $lang])
                ->asArray()
                ->one();
        }
    }
}

