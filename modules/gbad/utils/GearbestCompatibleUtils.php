<?php
namespace app\modules\gbad\utils;

class GearbestCompatibleUtils
{

    /**
     * 根据站点语言获取渠道编码
     * @param string $lang
     * @return string
     */
    public static function getPipelineByLang($lang)
    {
        $langPipelineMapping = [
            'en'    => 'GB',
            'ep-mx' => 'GBMX',
            'pt_br' => 'GBBR',
            'tr'    => 'GBTR',
            'ep'    => 'GBES',
            'fr'    => 'GBFR',
            'ru'    => 'GBRU',
            'po'    => 'GBPT',
            'it'    => 'GBIT',
            'de'    => 'GBDE',
        ];
        return $langPipelineMapping[$lang] ?? 'GB';
    }

    /**
     * 获取GB站点配置渠道域名
     * @param string $siteCode
     * @param string $lang
     * @return string
     */
    public static function getConfigDomain($siteCode, $lang)
    {
        $pipeline = self::getPipelineByLang($lang);
        return app()->params['sites'][ $siteCode ]['domain'][$pipeline] ?? '';
    }
}