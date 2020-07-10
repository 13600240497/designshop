<?php

namespace app\modules\component\dl\components;

use Yii;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\modules\common\dl\models\PageLanguageModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Cookie\CookieJar;

/**
 * 模板解析
 */
class ExplainTplComponent extends Component
{
    /**
     * 接口返回正常状态码
     */
    const RESPONSE_SUCCESS_CODE = 0;
    
    /**
     * CURL请求方法，POST形式
     */
    const REQUEST_METHOD_POST = 'POST';
    
    /**
     * 头尾数据缓存路径前缀
     */
    const HEAD_OR_FOOTER_CACHE_PATH_PRE = 'head_footer';
    
    /**
     * 接口地址配置key，对应到/config/sites/sites.test.php文件下的各站点
     */
    const POSITION_HEAD = 'head';
    
    /**
     * 接口地址配置key，对应到/config/sites/sites.test.php文件下的各站点
     */
    const POSITION_FOOTER = 'footer';
    
    /**
     * 站点头尾redis缓存key前缀
     */
    const REDIS_KEY_PREFIX_HEAD_FOOTER = 'head::footer::';
    
    /**
     * 站点头尾redis缓存时间，单位秒
     */
    const REDIS_CACHE_TIME = 3600;
    
    /**
     * 站点头尾部模板解析
     *
     * @param  \app\modules\common\components\CommonPageConfig $pageConfig 页面配置对象
     * @param string                                           $place
     *
     * @return bool 组件html
     * @throws \yii\base\ViewNotFoundException
     */
    public function getTplContent($pageConfig, $place = '')
    {
        if (empty($pageConfig->siteCode)) {
            $this->errors = '模板站点未设置';
            
            return false;
        }

        if (isset(app()->params['sites'][ $pageConfig->siteCode ]['headFooterSiteCode'])) {
            // 测试站点采用其他站点的头尾
            $pageConfig->siteCode = app()->params['sites'][ $pageConfig->siteCode ]['headFooterSiteCode'];
        }

        if (empty($place)) {
            $place = 'activity';
            if (empty($pageConfig->activityId)) {
                $place = 'home';
            } elseif (isset($pageConfig->mold) && SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT == $pageConfig->mold) {
                // 活动落地页
                $place = 'promotion';
            }
        }
        
        $html = $this->getHeadOrFooter($pageConfig->siteCode, $pageConfig->lang, $place);
        
        if (preg_match('/<title>.*?<\/title>/', $html)) {
            //替换页面seo相关内容
            $this->matchSeoMeta($pageConfig, $html);
        } else {
            //追加页面seo相关内容
            $this->appendSeoMeta($pageConfig, $html);
        }
    
        if (in_array($place, ['activity'])) {
            $this->appendActivitySeoMeta($pageConfig, $html);
        }
        
        //替换页面统计代码相关内容
        $this->matchLogsss($pageConfig, $html, $place);

        $stylesheet = '/<!--\s*geshop\s*stylesheet\s*start\s*-->/';
        preg_match($stylesheet, $html, $matches);
        if (!empty($matches[0])) {
            $customCss = $this->encodeCustomCss($pageConfig) . $pageConfig->componentCss;
            $html = str_replace($matches[0], $matches[0] . $customCss, $html);
        }
        
        $javascript = '/<!--\s*geshop\s*javascript\s*start\s*-->/';
        preg_match($javascript, $html, $matches);
        if (!empty($matches[0])) {
            //加入自定义JS和统计代码
            $customJs = $pageConfig->componentJs . $this->encodeStatisticsCode($pageConfig->statisticsCode);
            $html = str_replace($matches[0], $matches[0] . $customJs, $html);

        }
        
        return $html;
    }
    
    /**
     * 专题活动SEO数据
     *
     * @param $pageConfig
     * @param $html
     */
    private function appendActivitySeoMeta($pageConfig, &$html)
    {
        $metaStr = '';

        $share = SitePlatform::getSiteShareDetail($pageConfig->siteCode);
        if(!empty($pageConfig->share_image) || !empty($pageConfig->share_title)
        || !empty($pageConfig->share_title || !empty($pageConfig->share_link))){
            $metaStr .= '<meta property="og:image" content="'. $pageConfig->share_image. '">';
            $metaStr .= '<meta property="og:title" content="'.$pageConfig->share_title.'">';
            $metaStr .= '<meta property="og:description" content="'.$pageConfig->share_desc. '">';
            $metaStr .= '<meta property="og:url" content="'.$pageConfig->share_link.'">';
            $metaStr .= '<meta property="og:type" content="'.$share['type'].'"/> ';
            $metaStr .= '<meta property="og:site_name" content="'.$share['site_name'].'"/>';
            $metaStr .= '<meta property="fb:app_id" content="'.$share['app_id'].'"/>';
            $metaStr .= '<meta property="fb:admins" content="'.$share['admins'].'"/>';

            $metaStr .= '<meta itemprop="image" content="'.$pageConfig->share_image.'">';
            $metaStr .= '<meta itemprop="name" content="'.$pageConfig->share_title.'">';
            $metaStr .= '<meta itemprop="description" content="'.$pageConfig->share_title.'">';
            $metaStr .= '<meta itemprop="url" content="'.$pageConfig->share_link.'">';

            $metaStr .= '<meta name="twitter:image" content="'.$pageConfig->share_image.'">';
            $metaStr .= '<meta name="twitter:title" content="'.$pageConfig->share_title .'">';
            $metaStr .= '<meta name="twitter:description" content="'.$pageConfig->share_desc.'">';
            $metaStr .= '<meta name="twitter:url" content="'.$pageConfig->share_link.'">';
            $metaStr .= '<meta name="twitter:card" content="'.$share['type'].'">';
            $metaStr .= '<meta name="twitter:site" content="'.$share['site_name'].'">';
            $metaStr .= '<meta name="twitter:creator" content="'.$share['creator'].'">';
        }

        
        $html = str_replace('</head>', $metaStr . '</head>', $html);
    }
    
    /**
     * 替换页面统计代码相关内容
     *
     * @param $pageConfig
     * @param $html
     */
    private function matchLogsss($pageConfig, &$html, $place)
    {
        $pattern = '/<(\/?meta\s+name="GLOBEL:pageid".*?)>/';
        $logsssStr = '<meta name="GLOBEL:pageid" content="p-' . $pageConfig->pageId . '"/>';
        if ('activity' == $place) {
            $logsssStr .= '<meta name="GLOBEL:spcb" content="b"><meta name="GLOBEL:spcs" content="b03">';
        }
        preg_match($pattern, $html, $matches);
        if (!empty($matches[0])) {
            $html = str_replace($matches[0], $logsssStr, $html);
        } else {
            $html = str_replace('</head>', $logsssStr . '</head>', $html);
        }
    }
    
    /**
     * 替换页面seo相关内容
     *
     * @param int    $pageId
     * @param string $lang
     * @param string $headerAndFooter
     */
    private function matchSeoMeta($pageConfig, &$html)
    {
        $titleStr = '<title></title>';
        if (strpos($html, $titleStr) && !empty($pageConfig->title)) {
            $html = str_replace($titleStr, "<title>{$pageConfig->title}</title>", $html);
        }
        $keywordsStr = '<meta name="keywords" content=""/>';
        if (strpos($html, $keywordsStr) && !empty($pageConfig->keywords)) {
            $html = str_replace($keywordsStr, '<meta name="keywords" content="' . $pageConfig->keywords . '"/>', $html);
        }
        $descriptionStr = '<meta name="description" content=""/>';
        if (strpos($html, $descriptionStr) && !empty($pageConfig->description)) {
            $html = str_replace($descriptionStr, '<meta name="description" content="' . $pageConfig->description . '"/>', $html);
        }
    }
    
    /**
     * 追加页面seo相关内容
     *
     * @param int    $pageId
     * @param string $lang
     * @param string $headerAndFooter
     */
    private function appendSeoMeta($pageConfig, &$html)
    {
        $seoMeta = '<head>';
        $seoMeta .= "<title>{$pageConfig->title}</title>";
        $seoMeta .= '<meta name="keywords" content="' . $pageConfig->keywords . '"/>';
        $seoMeta .= '<meta name="description" content="' . $pageConfig->description .'"/>';
        $html = str_replace('<head>', $seoMeta, $html);
    }
    
    
    /**
     * 获取头尾部数据
     *
     * @param string $siteCode
     * @param string $lang
     * @param int $place 1：活动页 2：首页
     *
     * @return string
     */
    public function getHeadOrFooter($siteCode, $lang, $place)
    {
        $api = app()->params['sites'][ $siteCode ]['headFooterMonitorDomain'][ $place ][ $lang ] ?? '';
        if (!empty($api)) {
            try {
                $params = ['verify' => false];
                if (app()->env->isPreRelease()) {
                    $_domain = app()->params['sites'][ $siteCode ]['domain'];
                    $cookieJar = CookieJar::fromArray(
                        ['staging' => 'true'],
                        mb_substr($_domain, stripos($_domain, '.'))
                    );
                    $params['cookies'] = $cookieJar;
                }

                $response = (new Client())->get($api, $params);
                $data = $response->getBody()->getContents();
                Yii::info("{$siteCode} {$place} 请求站点头尾：{$api}");
                
                // 2018-09-13 by tengjiashun 站点更换新模板地址，改为/index.php这种地址
                // 将模板中包含在<!-- geshop delet start -->和<!-- geshop delet end -->中间的内容给剔除掉
                $stylesheet = '/<!--\s*geshop\s*delet\s*start\s*-->(.*?)<!--\s*geshop\s*delet\s*end\s*-->/is';
                $data = preg_replace($stylesheet, '<!-- geshop delet start --><!-- geshop delet end -->', $data);
                
            } catch (ServerException $exception) {
                Yii::error("{$siteCode} {$place} 请求页面地址异常：{$exception->getMessage()}");
                app()->rms->reportHeadFooterError("{$siteCode} 请求页面地址异常：{$exception->getMessage()}");
            } catch (\RuntimeException $exception) {
                Yii::error("{$siteCode} {$place} 请求页面地址异常：{$exception->getMessage()}");
                app()->rms->reportHeadFooterError("{$siteCode} 请求页面地址异常：{$exception->getMessage()}");
            }
        }
        
        return $data ?? '';
    }
    
    
    /**
     * 站点下线模板解析
     *
     * @param  \app\modules\common\components\CommonPageConfig $pageConfig 页面配置对象
     * @param bool                                             $isAppSite  是否为APP站点
     * @param array                                            $data       模板数据
     *
     * @return string|bool 组件html
     * @throws \yii\base\ViewNotFoundException
     */
    public function getOfflineContent($pageConfig, $isAppSite = false, $data = [])
    {
        if (empty($pageConfig->siteCode)) {
            $this->errors = '模板站点未设置';
            
            return false;
        }
        
        $html = '';
        $file = '../files/offline/';
        if ($isAppSite) {
            $file .= 'tpl/' . $pageConfig->lang . '.twig';
        } else {
            $file .= 'tips/' . $pageConfig->lang . '.twig';
        }
        
        if (is_file($file)) {
            $data['staticDomain'] = $pageConfig->staticDomain;
            $html = app()->view->renderFile($file, $data);
            $html = $this->formatStr($html);
        }
        
        return $html;
    }
    
    /**
     * 对统计代码做下处理
     *
     * @param string $statisticsCode 统计代码
     *
     * @return string
     */
    private function encodeStatisticsCode($statisticsCode)
    {
        if (empty($statisticsCode)) {
            return '';
        }
        
        $statisticsCode = (substr_compare($statisticsCode, '<script', 0, \strlen('<script')) === 0 ? '' : '<script>')
            . $statisticsCode;
        
        return $statisticsCode
            . (substr_compare($statisticsCode, '</script>', -\strlen('</script>')) === 0 ? '' : '</script>');
    }
    
    /**
     * 对自定义CSS做下处理
     *
     * @param \app\modules\common\components\CommonPageConfig $pageConfig 页面配置对象
     *
     * @return string
     */
    private function encodeCustomCss($pageConfig)
    {
        $background = '';
        if (!empty($pageConfig->background_color)) {
            $background .= 'background-color:' . $pageConfig->background_color . ';';
        }
        if (!empty($pageConfig->background_image)) {
            $background .= 'background-image:url("' . $pageConfig->background_image . '");';
        }
        if (!empty($pageConfig->background_position)) {
            $background .= 'background-position:' . $pageConfig->background_position . ';';
        }
        if (!empty($pageConfig->background_repeat)) {
            $background .= 'background-repeat:' . $pageConfig->background_repeat . ';';
        }
        if (!empty($pageConfig->background_attachment)) {
            $background .= 'background-attachment:' . $pageConfig->background_attachment . ';';
        }
        if (!empty($background)) {
            $background = ' #' . $pageConfig->contentDivId . ' {' . $background . '} ';
        }
        
        $customCss = ($pageConfig->style_type === PageLanguageModel::STYLE_TYPE_SYSTEM) ? $background : $pageConfig->customCss;
        if (empty($customCss)) {
            return '';
        }
        
        $customCss = (substr_compare($customCss, '<style', 0, \strlen('<style')) === 0 ? '' : '<style>')
            . $customCss;
        
        return $customCss
            . (substr_compare($customCss, '</style>', -\strlen('</style>')) === 0 ? '' : '</style>');
    }
    
    /**
     * 格式化字符串
     *
     * @param  string $str 解析模板的字符串
     *
     * @return string
     */
    private function formatStr($str)
    {
        //格式化字符串 转化实体
        $str = htmlspecialchars_decode($str, ENT_QUOTES);
        
        //去除反斜杠
        return stripslashes($str);
    }
}
