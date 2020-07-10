<?php
namespace app\modules\base\components\tools;

use app\modules\base\components\Component;

/**
 * 系统工具 - EDM链接生成器组件
 */
class EdmDeepLinkComponent extends Component
{
    const SITE_LIST = [
        'zf' => 'Zaful',
        'rg' => 'Rosegal',
        'dl' => 'Dresslily',
    ];

    const ACTION_LIST = [
        '1' => 'H5活动',
        '2' => '分类页',
        '3' => '搜索页',
        '4' => '虚拟分类',
        '5' => '原生专题'
    ];

    const SITE_ACTION_LIST = [
        'zf' => [
                'name' =>'Zaful',
                'action' => [
                    'H5活动',
                    '分类页',
                    '搜索页',
                    '虚拟分类',
                    '原生专题'
                ]
            ],
        'rg' => [
            'name' =>'Rosegal',
            'action' => [
                'H5活动',
                '分类页',
                '搜索页',
                '虚拟分类',
            ]
        ],
        'dl' => [
            'name' =>'Dresslily',
            'action' => [
                'H5活动',
                '分类页',
                '搜索页',
                '虚拟分类',
            ]
        ],
    ];
    const SITE_ACTION_RULE_LIST = [
        'zf' => [
            0 => 'zaful://action?actiontype=5&url=%s?is_app=1',
            1 => 'zaful://action?actiontype=2&url=%s',
            2 => 'zaful://action?actiontype=4&url=%s',
            3 => 'zaful://action?actiontype=14&url=%s',
            4 => 'zaful://action?actiontype=29&url=%s', //原生专题
        ],
        'rg' => [
            0 => 'rosegal://action?actiontype=5&url=%s?is_app=1',
            1 => 'rosegal://action?actiontype=2&url=%s',
            2 => 'rosegal://action?actiontype=4&url=%s',
            3 => 'rosegal://action?actiontype=13&url=%s'
        ],
        'dl' => [
            0 => 'dresslily://action?actiontype=5&url=%s?is_app=1',
            1 => 'dresslily://action?actiontype=2&url=%s',
            2 => 'dresslily://action?actiontype=4&url=%s',
            3 => 'dresslily://action?actiontype=13&url=%s'
        ]
    ];

    /**
     * 获取UI数据
     *
     * @return array
     */
    public static function getUiData()
    {
        return ['site_list' => self::SITE_ACTION_LIST];
    }

    /**
     * 生成链接
     *
     * @param string $websiteCode 站点简码 如：zf/rg
     * @param string $activityUrl pc/m活动链接
     * @param string $actionType deep link 动作类型
     * @param array $actionParams deep link 参数
     * @return string
     * @throws \Exception
     */
    public static function generateUrl($websiteCode, $activityUrl, $actionType, $actionParams)
    {
        $actionList = self::SITE_ACTION_RULE_LIST[$websiteCode] ?? [];
        if (empty($actionList)) {
            throw new \Exception('无效的站点简码 '. $websiteCode);
        }

        if (!isset($actionList[$actionType])) {
            throw new \Exception($websiteCode . '不支持的deep link 类型');
        }

        $fullUrl = $activityUrl;
        if (strpos($activityUrl, '?') !== false) {
            $fullUrl .= '&';
        } else {
            $fullUrl .= '?';
        }

        $fullUrl .= '$deep_link=true&branch_dp=';
        $ruleFormat = $actionList[$actionType];
        $deepLink = sprintf($ruleFormat, ...$actionParams);
        return $fullUrl . urlencode($deepLink);
    }
}
