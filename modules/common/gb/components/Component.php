<?php
namespace app\modules\common\gb\components;

use app\modules\common\gb\traits\{
    CommonMagicPropertyTrait,
    CommonErrorMessageTrait
};

/**
 * activity模块基础组件
 */
class Component extends \yii\base\Component
{
    use CommonMagicPropertyTrait;
    use CommonErrorMessageTrait;

    /**
     * 获取站点活动页面支持的语言列表
     * @param string $siteCode 语言简码
     * @param int $pageType 页面类型 1: 专题活动 2: 首页
     * @return array
     */
    public function getSiteLangs(string $siteCode, $pageType=1)
    {
        if (empty($siteCode)) {
            return [];
        }

        $domainIndex = ($pageType == 1) ? 'secondary_domain' : 'home_secondary_domain';
        $langList = [];
        $langConf = app()->params['lang'];
        $siteConf = app()->params['sites'][ $siteCode ][$domainIndex];
        if (!empty($siteConf) && \is_array($siteConf)) {
            foreach ($siteConf as $key => $value) {
                $langList[] = [
                    'key'  => $key,
                    'name' => $langConf[ $key ]
                ];
            }
        }
        return $langList;
    }
}
