<?php
/**
 * 站点测试环境主域名配置
 */
defined('TEST_DOMAIN') || define('TEST_DOMAIN', '-master-php5.fpm.egomsl.com');//test站点主域名，常驻分支 master
defined('RW_DOMAIN') || define('RW_DOMAIN', '-master-php5.fpm.egomsl.com');//rw站点主域名，常驻分支 master
defined('RG_DOMAIN') || define('RG_DOMAIN', '-v200618-php5.fpm.egomsl.com');//rg站点主域名，常驻分支 master
defined('ZF_DOMAIN') || define('ZF_DOMAIN', '-master-php5.fpm.egomsl.com');//zaful站点主域名，常驻分支 master
defined('GB_DOMAIN') || define('GB_DOMAIN', '.gearbest.net');//gearbest站点主域名
defined('DL_DOMAIN') || define('DL_DOMAIN', '-release-php5.fpm.egomsl.com');//dresslily站点主域名，常驻分支 release
defined('SUK_DOMAIN') || define('SUK_DOMAIN', '.hqygou.com');// suaoki站点主域名

defined('TEST_DOMAIN_DEVELOP') || define('TEST_DOMAIN_DEVELOP', '-master-php5.fpm.egomsl.com');//test站点开发分支域名
defined('RW_DOMAIN_DEVELOP') || define('RW_DOMAIN_DEVELOP', '-master-php5.fpm.egomsl.com');//rw站点开发分支域名
defined('RG_DOMAIN_DEVELOP') || define('RG_DOMAIN_DEVELOP', '-v200618-php5.fpm.egomsl.com');//rg站点开发分支域名
defined('ZF_DOMAIN_DEVELOP') || define('ZF_DOMAIN_DEVELOP', '-master-php5.fpm.egomsl.com');//zaful站点开发分支域名
defined('GB_DOMAIN_DEVELOP') || define('GB_DOMAIN_DEVELOP', '.net');//gearbest站点开发分支域名
defined('DL_DOMAIN_DEVELOP') || define('DL_DOMAIN_DEVELOP', '-release-php5.fpm.egomsl.com');//dresslily站点开发分支域名
defined('SUK_DOMAIN_DEVELOP') || define('SUK_DOMAIN_DEVELOP', '.hqygou.com');// suaoki站点开发分支域名

return [
    /****************************************测试站点配置START*****************************************/
    'test-pc'    => [
        'name'          => 'test-pc',
        'domain'        => 'http://www.pc-rosewholesale' . TEST_DOMAIN,
        'developdomain' => 'http://www.pc-rosewholesale' . TEST_DOMAIN_DEVELOP
    ],
    'test-wap'   => [
        'name'          => 'test-wap',
        'domain'        => 'http://m.wap-rosewholesale' . TEST_DOMAIN,
        'developdomain' => 'http://m.wap-rosewholesale' . TEST_DOMAIN_DEVELOP
    ],
    'test-app'   => [
        'name'          => 'test-app',
        'domain'        => 'http://m.wap-rosewholesale' . TEST_DOMAIN,
        'developdomain' => 'http://m.wap-rosewholesale' . TEST_DOMAIN_DEVELOP
    ],
    /****************************************测试站点配置END*******************************************/
    'rw-pc'      => [
        'name'          => 'rosewholesale-pc',
        'domain'        => 'http://www.pc-rosewholesale' . RW_DOMAIN,
        'developdomain' => 'http://www.pc-rosewholesale' . RW_DOMAIN_DEVELOP
    ],
    'rw-wap'     => [
        'name'          => 'rosewholesale-wap',
        'domain'        => 'http://m.wap-rosewholesale' . RW_DOMAIN,
        'developdomain' => 'http://m.wap-rosewholesale' . RW_DOMAIN_DEVELOP
    ],
    'rw-app'     => [
        'name'          => 'rosewholesale-app',
        'domain'        => 'http://m.wap-rosewholesale' . RW_DOMAIN,
        'developdomain' => 'http://m.wap-rosewholesale' . RW_DOMAIN_DEVELOP
    ],
    'rg-pc'      => [
        'name'          => 'rosegal-pc',
        'domain'        => 'http://www.pc-rosegal' . RG_DOMAIN,
        'developdomain' => 'http://www.pc-rosegal' . RG_DOMAIN_DEVELOP
    ],
    'rg-wap'     => [
        'name'          => 'rosegal-wap',
        'domain'        => 'http://m.wap-rosegal' . RG_DOMAIN,
        'developdomain' => 'http://m.wap-rosegal' . RG_DOMAIN_DEVELOP
    ],
    'rg-app'     => [
        'name'          => 'rosegal-app',
        'domain'        => 'http://m.wap-rosegal' . RG_DOMAIN,
        'developdomain' => 'http://m.wap-rosegal' . RG_DOMAIN_DEVELOP
    ],
    'suk-pc'      => [
        'name'          => 'suaoki-pc',
        'domain'        => '//suaoki' . SUK_DOMAIN,
        'developdomain' => 'http://suaoki' . SUK_DOMAIN_DEVELOP
    ],
    'suk-wap'     => [
        'name'          => 'suaoki-wap',
        'domain'        => '//msuaoki' . SUK_DOMAIN,
        'developdomain' => 'http://msuaoki' . SUK_DOMAIN_DEVELOP
    ],
    'zf-pc'      => [
        'name'          => 'zaful-pc',
        'domain'        => 'http://www.pc-zaful' . ZF_DOMAIN,
        'developdomain' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP
    ],
    'zf-wap'     => [
        'name'          => 'zaful-wap',
        'domain'        => 'http://m.wap-zaful' . ZF_DOMAIN,
        'developdomain' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP
    ],
    'zf-app'     => [
        'name'          => 'zaful-app',
        'domain'        => 'http://m.wap-zaful' . ZF_DOMAIN,
        'developdomain' => 'http://www.pc-zaful' . ZF_DOMAIN_DEVELOP
    ],
    'gb-pc'      => [
        'name'          => 'gearbest-pc',
        'domain'        => [
            'GB'    => 'https://www' . GB_DOMAIN,
            'GBES'  => 'https://es' . GB_DOMAIN,
            'GBFR'  => 'https://fr' . GB_DOMAIN,
            'GBRU'  => 'https://ru' . GB_DOMAIN,
            'GBPT'  => 'https://pt' . GB_DOMAIN,
            'GBIT'  => 'https://it' . GB_DOMAIN,
            'GBDE'  => 'https://de' . GB_DOMAIN,
            'GBUK'  => 'https://uk' . GB_DOMAIN,
            'GBUS'  => 'https://us' . GB_DOMAIN,
            'GBBR'  => 'https://br' . GB_DOMAIN,
            'GBTR'  => 'https://tr' . GB_DOMAIN,
            'GBMX'  => 'https://mx' . GB_DOMAIN,
            'GBMA'  => 'https://ma' . GB_DOMAIN,
            'GBGR'  => 'https://gr' . GB_DOMAIN,
            'GBHU'  => 'https://hu' . GB_DOMAIN,
            'GBNL'  => 'https://nl' . GB_DOMAIN,
            'GBSK'  => 'https://sk' . GB_DOMAIN,
            'GBRO'  => 'https://ro' . GB_DOMAIN,
            'GBCZ'  => 'https://cz' . GB_DOMAIN,
            'GBAU'  => 'https://au' . GB_DOMAIN,
            'GBIN'  => 'https://in' . GB_DOMAIN,
            'GBJP'  => 'https://jp' . GB_DOMAIN,
            /*'GBUA' => 'https://ua' . GB_DOMAIN,
            'GBIL' => 'https://il' . GB_DOMAIN,
            'GBKZ' => 'https://kz' . GB_DOMAIN,
            'GBTH' => 'https://th' . GB_DOMAIN,
            'GBVN' => 'https://vn' . GB_DOMAIN,
            'GBID' => 'https://id' . GB_DOMAIN,*/
            'GBPL'  => 'https://pl' . GB_DOMAIN,
            'GBSTY' => 'https://stylebest' . GB_DOMAIN,
            'GBGAG' => 'https://gagabop' . GB_DOMAIN,
            'GBCOZ' => 'https://cozyvoices' . GB_DOMAIN,
        ],
        'developdomain' => 'https://www' . GB_DOMAIN_DEVELOP,
        'goodsImageUrl' => 'http://gloimg.gbtcdn.com/'
    ],
    'gb-wap'     => [
        'name'          => 'gearbest-wap',
        'domain'        => [
            'GB'    => 'https://m' . GB_DOMAIN,
            'GBES'  => 'https://m-es' . GB_DOMAIN,
            'GBFR'  => 'https://m-fr' . GB_DOMAIN,
            'GBRU'  => 'https://m-ru' . GB_DOMAIN,
            'GBPT'  => 'https://m-pt' . GB_DOMAIN,
            'GBIT'  => 'https://m-it' . GB_DOMAIN,
            'GBDE'  => 'https://m-de' . GB_DOMAIN,
            'GBUK'  => 'https://m-uk' . GB_DOMAIN,
            'GBUS'  => 'https://m-us' . GB_DOMAIN,
            'GBBR'  => 'https://m-br' . GB_DOMAIN,
            'GBTR'  => 'https://m-tr' . GB_DOMAIN,
            'GBMX'  => 'https://m-mx' . GB_DOMAIN,
            'GBMA'  => 'https://m-ma' . GB_DOMAIN,
            'GBGR'  => 'https://m-gr' . GB_DOMAIN,
            'GBHU'  => 'https://m-hu' . GB_DOMAIN,
            'GBNL'  => 'https://m-nl' . GB_DOMAIN,
            'GBSK'  => 'https://m-sk' . GB_DOMAIN,
            'GBRO'  => 'https://m-ro' . GB_DOMAIN,
            'GBCZ'  => 'https://m-cz' . GB_DOMAIN,
            'GBAU'  => 'https://m-au' . GB_DOMAIN,
            'GBIN'  => 'https://m-in' . GB_DOMAIN,
            'GBJP'  => 'https://m-jp' . GB_DOMAIN,
            /*'GBUA' => 'https://m-ua' . GB_DOMAIN,
            'GBIL' => 'https://m-il' . GB_DOMAIN,
            'GBKZ' => 'https://m-kz' . GB_DOMAIN,
            'GBTH' => 'https://m-th' . GB_DOMAIN,
            'GBVN' => 'https://m-vn' . GB_DOMAIN,
            'GBID' => 'https://m-id' . GB_DOMAIN,*/
            'GBPL'  => 'https://m-pl' . GB_DOMAIN,
            'GBSTY' => 'https://m-stylebest' . GB_DOMAIN,
            'GBGAG' => 'https://m-gagabop' . GB_DOMAIN,
            'GBCOZ' => 'https://m-cozyvoices' . GB_DOMAIN,
        ],
        'developdomain' => 'https://m' . GB_DOMAIN_DEVELOP,
        'goodsImageUrl' => 'http://gloimg.gbtcdn.com/'
    ],
    'gb-ios'     => [
        'name'          => 'gearbest-ios',
        'domain'        => [
            'GB'   => 'https://m' . GB_DOMAIN,
            'GBRU' => 'https://m-ru' . GB_DOMAIN,
            'GBFR' => 'https://m-fr' . GB_DOMAIN,
            'GBPT' => 'https://m-pt' . GB_DOMAIN,
            'GBIT' => 'https://m-it' . GB_DOMAIN,
            'GBUK' => 'https://m-uk' . GB_DOMAIN,
            'GBUS' => 'https://m-us' . GB_DOMAIN,
            'GBBR' => 'https://m-br' . GB_DOMAIN,
            'GBTR' => 'https://m-tr' . GB_DOMAIN,
            'GBMX' => 'https://m-mx' . GB_DOMAIN,
            'GBMA' => 'https://m-ma' . GB_DOMAIN,
            'GBGR' => 'https://m-gr' . GB_DOMAIN,
            'GBHU' => 'https://m-hu' . GB_DOMAIN,
            'GBNL' => 'https://m-nl' . GB_DOMAIN,
            'GBSK' => 'https://m-sk' . GB_DOMAIN,
            'GBRO' => 'https://m-ro' . GB_DOMAIN,
            'GBCZ' => 'https://m-cz' . GB_DOMAIN,
            'GBAU' => 'https://m-au' . GB_DOMAIN,
            'GBIN' => 'https://m-in' . GB_DOMAIN,
            'GBJP' => 'https://m-jp' . GB_DOMAIN,
            /*'GBUA' => 'https://m-ua' . GB_DOMAIN,
            'GBIL' => 'https://m-il' . GB_DOMAIN,
            'GBKZ' => 'https://m-kz' . GB_DOMAIN,
            'GBTH' => 'https://m-th' . GB_DOMAIN,
            'GBVN' => 'https://m-vn' . GB_DOMAIN,
            'GBID' => 'https://m-id' . GB_DOMAIN,*/
            'GBPL' => 'https://m-pl' . GB_DOMAIN,
        ],
        'developdomain' => 'https://m' . GB_DOMAIN_DEVELOP,
        'goodsImageUrl' => 'http://gloimg.gbtcdn.com/'
    ],
    'gb-android' => [
        'name'          => 'gearbest-android',
        'domain'        => [
            'GB'   => 'https://m' . GB_DOMAIN,
            'GBRU' => 'https://m-ru' . GB_DOMAIN,
            'GBFR' => 'https://m-fr' . GB_DOMAIN,
            'GBPT' => 'https://m-pt' . GB_DOMAIN,
            'GBIT' => 'https://m-it' . GB_DOMAIN,
            'GBUK' => 'https://m-uk' . GB_DOMAIN,
            'GBUS' => 'https://m-us' . GB_DOMAIN,
            'GBBR' => 'https://m-br' . GB_DOMAIN,
            'GBTR' => 'https://m-tr' . GB_DOMAIN,
            'GBMX' => 'https://m-mx' . GB_DOMAIN,
            'GBMA' => 'https://m-ma' . GB_DOMAIN,
            'GBGR' => 'https://m-gr' . GB_DOMAIN,
            'GBHU' => 'https://m-hu' . GB_DOMAIN,
            'GBNL' => 'https://m-nl' . GB_DOMAIN,
            'GBSK' => 'https://m-sk' . GB_DOMAIN,
            'GBRO' => 'https://m-ro' . GB_DOMAIN,
            'GBCZ' => 'https://m-cz' . GB_DOMAIN,
            'GBAU' => 'https://m-au' . GB_DOMAIN,
            'GBIN' => 'https://m-in' . GB_DOMAIN,
            'GBJP' => 'https://m-jp' . GB_DOMAIN,
            /*'GBUA' => 'https://m-ua' . GB_DOMAIN,
            'GBIL' => 'https://m-il' . GB_DOMAIN,
            'GBKZ' => 'https://m-kz' . GB_DOMAIN,
            'GBTH' => 'https://m-th' . GB_DOMAIN,
            'GBVN' => 'https://m-vn' . GB_DOMAIN,
            'GBID' => 'https://m-id' . GB_DOMAIN,*/
            'GBPL' => 'https://m-pl' . GB_DOMAIN,
        ],
        'developdomain' => 'https://m' . GB_DOMAIN_DEVELOP,
        'goodsImageUrl' => 'http://gloimg.gbtcdn.com/'
    ],

    /**************************************** DL *******************************************/
    'dl-web'      => [
        'name'          => 'dresslily-pc',
        'domain'        => 'http://www.pc-dresslily' . DL_DOMAIN,
        'developdomain' => 'http://www.pc-dresslily' . DL_DOMAIN_DEVELOP
    ],
    'dl-app'     => [
        'name'          => 'dresslily-app',
        'domain'        => 'http://www.pc-dresslily' . DL_DOMAIN,
        'developdomain' => 'http://www.pc-dresslily' . DL_DOMAIN_DEVELOP
    ],
];
