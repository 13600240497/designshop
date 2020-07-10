<?php
/**
 * SOA服务配置
 */

$soaCommonConfig = [
    /************************************* 公共配置 ***********************************/
    'common'  => [
        //geshop站点组简码到选品系统站点简码转换
        'groupCodeToWebsiteCodeMapping' => [
            'test' => 'RG',
            'rg'   => 'RG',
            'rw'   => 'RW',
            'zf'   => 'ZF',
            'dl'   => 'DL',
            'gb'   => 'GB'
        ]
    ],
    /************************************* 搜索配置 ***********************************/
    'eSearch' => [
        'tokenId'            => '576861360e1344ba9b6de1992f60b28a',
        'version'            => '3.0',
        'gatewayKey'         => 'gb_search',
        'languageGatewayKey' => 'i18n_search'
    ],
    
    /************************************* 选品系统配置 ***********************************/
    'ips'     => [
        //geshop站点组简码到选品系统站点简码转换
        'groupCodeToWebsiteCodeMap' => [
            'test' => 'RG',
            'rg'   => 'RG',
            'rw'   => 'RW',
            'zf'   => 'ZF',
            'dl'   => 'DL',
            'gb'   => 'GB'
        ]
    ],
    
    /********************************* GB站点S应用默认设定 ***********************************/
    'gb'      => [
        'siteCode'    => 'GB',
        // 渠道名称映射表
        'pipeline'    => [
            'GB'    => '全球站',
            'GBES'  => '西班牙站',
            'GBFR'  => '法国站',
            'GBRU'  => '俄罗斯站',
            'GBPT'  => '葡萄牙站',
            'GBIT'  => '意大利站',
            'GBDE'  => '德国站',
            'GBUK'  => '英国站',
            'GBUS'  => '美国站',
            'GBAU'  => '澳大利亚站',
            'GBBR'  => '巴西站',
            'GBTR'  => '土耳其站',
            'GBPL'  => '波兰站',
            'GBIN'  => '印度站',
            'GBGR'  => '希腊站',
            'GBMX'  => '墨西哥站',
            'GBHU'  => '匈牙利站',
            'GBSK'  => '斯洛伐克站',
            'GBCZ'  => '捷克站',
            'GBNL'  => '荷兰站',
            'GBRO'  => '罗马尼亚站',
            'GBMA'  => '摩洛哥站',
            
            /*'GBIL' => '以色列站',
            'GBKZ' => '哈萨克斯坦站',
            'GBID' => '印尼站',
            'GBTH' => '泰国站',
            'GBVN' => '越南站',*/
            'GBBD'  => '孟加拉国站',
            'GBLK'  => '斯里兰卡站',
            'GBHR'  => '克罗地亚站',
            'GBBG'  => '保加利亚站',
            'GBDK'  => '丹麦站',
            'GBNO'  => '挪威站',
            'GBFI'  => '索马莱宁站',
            'GBSE'  => '瑞典站',
            //'GBUA' => '乌克兰站',
            'GBJP'  => '日本站',
            'GBSTY' => 'Stylebest站',
            'GBGAG' => 'Gagabop站',
            'GBCOZ' => 'Cozyvoices站'
        ],
        'countryCode' => [
            'GB'    => 'US',
            'GBES'  => 'ES',
            'GBFR'  => 'FR',
            'GBRU'  => 'RU',
            'GBPT'  => 'PT',
            'GBIT'  => 'IT',
            'GBDE'  => 'DE',
            'GBUK'  => 'UK',
            'GBUS'  => 'US',
            'GBAU'  => 'AU',
            'GBBR'  => 'BR',
            'GBTR'  => 'TR',
            'GBPL'  => 'PL',
            'GBIN'  => 'IN',
            'GBGR'  => 'GR',
            'GBMX'  => 'MX',
            'GBHU'  => 'HU',
            'GBSK'  => 'SK',
            'GBCZ'  => 'CZ',
            'GBNL'  => 'NL',
            'GBRO'  => 'RO',
            'GBMA'  => 'MA',
            
            /*'GBIL' => 'IL',
            'GBKZ' => 'KZ',
            'GBID' => 'ID',
            'GBTH' => 'TH',
            'GBVN' => 'VN',*/
            'GBBD'  => 'BD',
            'GBLK'  => 'LK',
            'GBHR'  => 'HR',
            'GBBG'  => 'BG',
            'GBDK'  => 'DK',
            'GBNO'  => 'NO',
            'GBFI'  => 'FI',
            'GBSE'  => 'SE',
            //'GBUA' => 'UA',
            'GBJP'  => 'JP',
            'GBSTY' => 'US',
            'GBGAG' => 'US',
            'GBCOZ' => 'US'
        ]
    ]
    /********************************* GB站点S应用默认设定END ********************************/
];

return yii\helpers\ArrayHelper::merge($soaCommonConfig, require(__DIR__ . '/soa.' . YII_ENV . '.php'));

/*---------------------------------- 选品系统站点配置 ------------------------------------------------------*/
//网站与站点对应关系(写死)
//static $website_site = [
//    'GB' => ['GB', 'GBBR', 'GBPT', 'GBUK', 'GBUS', 'GBDE', 'GBES', 'GBFR', 'GBIT', 'GBRU', 'GBTR', 'GBPL', 'GBNL', 'GBGR',
//              'GBHU', 'GBSK', 'GBJP', 'GBRO', 'GBMA', 'GBCZ','GBIL', 'GBSA', 'GBUA', 'GBBG', 'GBHR', 'GBSE', 'GBFI',
//              'GBNO', 'GBDK', 'GBTH', 'GBVN', 'GBLK', 'GBAU'],
//    'ZF' => ['en', 'fr', 'ep', 'de', 'po', 'it', 'ar'],
//    'RG' => ['en', 'fr', 'ep', 'ru', 'ar'],
//    'SD' => ['en', 'fr'],
//    'RW' => ['en', 'ep'],
//    'DL' => ['en', 'fr'],
//    'TG' => ['en'],
//    'GS' => ['en'],
//    'YS' => ['en', 'ar'],
//];

//网站与端口对应关系(写死)
//static $website_terminal = [
//    'GB' => ['PC', 'WAP', 'IOS', 'ANDROID', 'PAD'],
//    'ZF' => ['PC', 'WAP', 'IOS', 'ANDROID'],
//    'RG' => ['PC', 'WAP', 'IOS', 'ANDROID'],
//    'SD' => ['PC', 'WAP', 'IOS', 'ANDROID'],
//    'RW' => ['PC', 'WAP', 'IOS', 'ANDROID'],
//    'DL' => ['PC', 'WAP', 'IOS', 'ANDROID'],
//    'TG' => ['PC'],
//    'GS' => ['PC', 'WAP'],
//    'YS' => ['IOS', 'ANDROID'],
//];
