<?php

namespace app\base;

/**
 * 国家(渠道)站点公共函数，重新提取出来也区别以前服装站点
 *
 */
class PipelineUtils
{

    public static function getConfigHomeSupportPipelineList($siteCode)
    {
        return self::getConfigSupportPipelineList($siteCode, 'home_secondary_domain');
    }

    public static function getConfigSpecialSupportPipelineList($siteCode)
    {
        return self::getConfigSupportPipelineList($siteCode, 'secondary_domain');
    }

    /**
     * 获取活动页面渠道配置
     * @param string $siteCode
     * @param string $configIndex
     * @return array
     */
    private static function getConfigSupportPipelineList($siteCode, $configIndex)
    {
        return app()->params['sites'][$siteCode][$configIndex] ?? [];
    }

    /**
     * 获取站点所有配置渠道列表
     * @param string $siteCode
     * @return array
     */
    public static function getConfigAllPipelineList($siteCode)
    {
        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        return self::getConfigAllPipelineListByGroupCode($siteGroupCode);
    }

    public static function getConfigAllPipelineListByGroupCode($siteGroupCode)
    {
        return app()->params['site'][$siteGroupCode]['pipeline'] ?? [];
    }


    /**
     * 获取渠道和对应语言的名称信息
     * @param string $siteCode
     * @param array $pipelineLangList
     * 格式：
     * ['ZF' => ['en','es']]
     * @return array
     */
    public static function getPipelineAndLangInfoList($siteCode, $pipelineLangList)
    {
        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $configAllLanguages = app()->params['lang'] ?? [];
        $configAllPipelines = app()->params['site'][$siteGroupCode]['pipeline'] ?? [];

        $pipelineInfoList = [];
        foreach ($pipelineLangList as $pipelineCode => $langList) {
            $pipelineInfoList[$pipelineCode] = [
                'code' => $pipelineCode,
                'name' => $configAllPipelines[$pipelineCode] ?? '',
            ];

            $langInfoList = [];
            foreach ($langList as $langCode) {
                $langInfoList[$langCode] = [
                    'code' => $langCode,
                    'name' => $configAllLanguages[$langCode]['name'] ?? '',
                ];
            }
            $pipelineInfoList[$pipelineCode]['lang_list'] = $langInfoList;
        }

        return $pipelineInfoList;
    }


    public static function getSiteSpecialPageValidPipelineList($siteCode, $pipeline)
    {
        return self::getSitePageValidPipelineList($siteCode, $pipeline, 'secondary_domain');
    }

    /**
     * 获取站点端口支持的渠道和语言
     * @param string $siteCode
     * @param array $pipelineLangList
     * 格式：
     * ['ZF' => ['en','es']]
     * @param string $configIndex
     * @return array
     */
    private static function getSitePageValidPipelineList($siteCode, $pipelineLangList, $configIndex)
    {
        $pipelineList = [];
        if (!$siteCode || !is_array($pipelineLangList) || empty($pipelineLangList) || !SitePlatform::siteExists($siteCode)) {
            return $pipelineList;
        }

        $configSitePipeline = app()->params['sites'][$siteCode][$configIndex] ?? [];
        foreach ($pipelineLangList as $pipelineCode => $langList) {
            if (isset($configSitePipeline[$pipelineCode])) {
                foreach ($langList as $langCode) {
                    if (isset($configSitePipeline[$pipelineCode][$langCode])) {
                        $pipelineList[$pipelineCode][] = $langCode;
                    }
                }
            }
        }

        return $pipelineList;
    }

    /**
     * 获取服装首页活动渠道及语言配置列表
     * @param string $siteCode
     * @return array
     */
    public static function getSiteHomePipelineLangList($siteCode)
    {
        return self::getSitePipelineLangList($siteCode, 'home_secondary_domain');
    }

    /**
     * 获取服装专题活动渠道及语言配置列表
     * @param string $siteCode
     * @return array
     */
    public static function getSiteSpecialPipelineLangList($siteCode)
    {
        return self::getSitePipelineLangList($siteCode, 'secondary_domain');
    }

    /**
     * @param string $siteCode
     * @param string $configIndex
     * @param bool $isFilterClosed 是否过滤停止运营的渠道语言
     * @return array
     */
    private static function getSitePipelineLangList($siteCode, $configIndex, $isFilterClosed = false)
    {
        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        $configAllLanguages = app()->params['lang'] ?? [];
        $configAllPipelines = app()->params['site'][$siteGroupCode]['pipeline'] ?? [];
        $configSitePipelines = app()->params['sites'][$siteCode][$configIndex] ?? [];
        $configClosedPipelines = app()->params['sites'][$siteCode]['closed_pipeline_lang'] ?? [];

        $sitePipelines = [];
        if (!empty($configSitePipelines)) {
            foreach ($configSitePipelines as $pipelineCode => $configLanguages) {
                if (empty($configAllPipelines[$pipelineCode])) {
                    continue;
                }
                $sitePipelines[$pipelineCode] = [
                    'code' => $pipelineCode,
                    'name' => $configAllPipelines[$pipelineCode] ?? '',
                ];

                $langList = [];
                foreach ($configLanguages as $langCode => $urlPrefix) {
                    if ($isFilterClosed
                        && isset($configClosedPipelines[$pipelineCode])
                        && in_array($langCode, $configClosedPipelines[$pipelineCode], true)) {
                        continue;
                    }

                    $langList[$langCode] = [
                        'code' => $langCode,
                        'name' => $configAllLanguages[$langCode]['name'] ?? '',
                        'url_prefix' => $urlPrefix
                    ];
                }
                $sitePipelines[$pipelineCode]['lang_list'] = $langList;

                // 剔除没有语言的渠道
                if (empty($langList)) {
                    unset($sitePipelines[$pipelineCode]);
                }
            }
        }

        return $sitePipelines;
    }

    /**
     * 获取服装首页活动国家站语言/端 配置列表
     * @param string $siteGroupCode
     * @param bool $isFilterClosed 是否过滤停止运营的渠道语言
     * @return array
     */
    public static function getSiteHomeAllPlatformPipelineLangList($siteGroupCode, $isFilterClosed = false)
    {
        return self::getSiteAllPlatformPipelineLangList($siteGroupCode, 'home_secondary_domain', $isFilterClosed);
    }

    /**
     * 获取服装专题活动国家站语言/端 配置列表
     * @param string $siteGroupCode
     * @param bool $isFilterClosed 是否过滤停止运营的渠道语言
     * @return array
     */
    public static function getSiteSpecialAllPlatformPipelineLangList($siteGroupCode, $isFilterClosed = false)
    {
        return self::getSiteAllPlatformPipelineLangList($siteGroupCode, 'secondary_domain', $isFilterClosed);
    }

    /**
     * @param string $siteGroupCode
     * @param string $configIndex
     * @param bool $isFilterClosed 是否过滤停止运营的渠道语言
     * @return array
     */
    private static function getSiteAllPlatformPipelineLangList($siteGroupCode, $configIndex, $isFilterClosed = false)
    {
        $platforms = app()->params['site'][$siteGroupCode]['platforms'] ?? [];
        $configAllPipelines = app()->params['site'][$siteGroupCode]['pipeline'] ?? [];

        $allPipelines = [];
        foreach ($configAllPipelines as $_key => $_val) {
            $allPipelines[] = ['code' => $_key, 'name' => $_val];
        }

        $sitePlatforms = $sitePipelines = [];
        foreach ($platforms as $platformCode) {
            $sitePlatforms[] = [
                'code' => $platformCode,
                'name' => SitePlatform::getPlatformNameByCode($platformCode),
            ];
            $siteCode = SitePlatform::getPlatformSiteCode($platformCode, $siteGroupCode);

            $sitePipelines[$platformCode] = self::getSitePipelineLangList($siteCode, $configIndex, $isFilterClosed);
        }

        return [
            'all_platforms' => $sitePlatforms,
            'all_pipelines' => $allPipelines,
            'support_pipelines' => $sitePipelines
        ];
    }
}
