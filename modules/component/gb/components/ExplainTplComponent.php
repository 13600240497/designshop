<?php

namespace app\modules\component\gb\components;

use app\base\SitePlatform;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Yii;
use app\modules\common\models\PageLanguageModel;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;
use app\modules\common\gb\models\PageModel;
use app\modules\common\gb\models\PagePublishLogModel;
use yii\helpers\Url;

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
    
    protected $headFooterList = [];
    
    /**
     * 站点头尾部模板解析
     *
     * @param  \app\modules\common\gb\components\CommonPageConfig $pageConfig 页面配置对象
     * @param string                                              $place
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
        
        if (empty($pageConfig->pipeline)) {
            $this->errors = '模板渠道未设置';
            
            return false;
        }
        
        empty($place) && $place = !empty($pageConfig->activityId) ? 'activity' : 'home';
        $html = $this->getHeadOrFooter($pageConfig->siteCode, $pageConfig->lang, $place, $pageConfig->pipeline);
        
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
        
        //GB临时方案-尾部banner隐藏 - 2018-11-22
        $findCssClass = 'class="siteFooter_banner"';
        $html = str_replace($findCssClass, $findCssClass . ' style="display:none;"', $html);
        
        $stylesheet = '/<!--\s*geshop\s*stylesheet\s*start\s*-->/';
        preg_match($stylesheet, $html, $matches);
        if (!empty($matches[0])) {
            $customCss = $this->encodeCustomCss($pageConfig) . $pageConfig->componentCss;
            $html = str_replace($matches[0], $matches[0] . $customCss, $html);
        }
        
        $this->matchSeoAlternate($pageConfig, $html);
        
        $javascript = '/<!--\s*geshop\s*javascript\s*start\s*-->/';
        preg_match($javascript, $html, $matches);
        if (!empty($matches[0])) {
            $customJs = $pageConfig->componentJs . $this->encodeStatisticsCode($pageConfig->statisticsCode);
            $html = str_replace($matches[0], $matches[0] . $customJs, $html);
            //加入自定义JS和统计代码
            $commonJavascript = '/<!--\s*Common\s*JavaScript\s*-->/';
            preg_match($commonJavascript, $html, $matches);
            if (!empty($matches[0])) {
                $bigDataJs = (new BigDataComponent())->bulit(
                    $pageConfig->pageId,
                    $pageConfig->lang,
                    $pageConfig->pipeline,
                    $pageConfig->siteCode
                );
                $bigDataJs = '<script>' . $bigDataJs . '</script>';
                $html = str_replace($matches[0], $matches[0] . $bigDataJs, $html);
            }
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
        $metaStr .= '<meta name="robots" content="index, follow">';
        $parseArr = parse_url($pageConfig->share_link);
        if (!empty($parseArr)) {
            if (substr_count($parseArr['path'], '/') > 1) {
                $path = pathinfo($parseArr['path'])['dirname'];
            } else {
                $path = $parseArr['path'];
            }
            $domain = "{$parseArr['scheme']}://{$parseArr['host']}{$path}";
            $metaStr .= '<link rel="canonical" href="' . "{$domain}/{$pageConfig->url_name}-special-{$pageConfig->special_id}.html" . '" />';
        }
        $metaStr .= '<!-- Open Graph data -->';
        $metaStr .= '<meta property="og:title" content="' . $pageConfig->share_title . '" />';
        $metaStr .= '<meta property="og:type" content="' . $share['type'] . '" />';
        $metaStr .= '<meta property="og:url" content="' . $pageConfig->share_link . '" />';
        $metaStr .= '<meta property="og:image" content="' . $pageConfig->share_image . '" />';
        $metaStr .= '<meta property="og:description" content="' . $pageConfig->share_desc . '" />';
        $metaStr .= '<meta property="og:site_name" content="' . $share['site_name'] . '" />';
        $metaStr .= '<meta property="fb:app_id" content="' . $share['app_id'] . '" />';
        $metaStr .= '<!-- Twitter Card data -->';
        $metaStr .= '<meta name="twitter:card" content="' . $share['type'] . '">';
        $metaStr .= '<meta name="twitter:site" content="' . $share['site_name'] . '">';
        $metaStr .= '<meta name="twitter:title" content="' . $pageConfig->share_title . '">';
        $metaStr .= '<meta name="twitter:description" content="' . $pageConfig->share_desc . '">';
        $metaStr .= '<meta property="twitter:account_id" content="' . $share['creator'] . '"/>';
        $metaStr .= '<!-- Twitter Summary card images must be at least 120x120px -->';
        $metaStr .= '<meta name="twitter:image" content="' . $pageConfig->share_image . '">';
        $metaStr .= '<h1 class="header-tag-title" style="display:none">' . $pageConfig->title . '</h1>';
        
        $html = str_replace('</head>', $metaStr . '</head>', $html);
    }
    
    /**
     * 替换页面统计代码相关内容
     *
     * @param $pageConfig
     * @param $html
     * @param $place
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
        
        if (false === stristr($pageConfig->siteCode, 'pc')) {
            $shareStr = '<meta name="share_title" content="' . $pageConfig->share_title . '" />
                    <meta name="share_desc" content="' . $pageConfig->share_desc . '" />
                    <meta name="share_logo" content="' . $pageConfig->share_image . '" />
                     <meta name="share_url" content="' . $pageConfig->share_link . '" />
                     <meta name="attach_cart" content="true" /> <!--表示是否展示APP右上角购物车按钮-->';
            $html = str_replace('</head>', $shareStr . '</head>', $html);
        }
    }
    
    /**
     * 替换alternate多语言标签
     *
     * @param       $pageConfig
     * @param       $html
     */
    private function matchSeoAlternate($pageConfig, &$html)
    {
        $alternate = '/<!--\s*geshop\s*alternate\s*start\s*-->(.*)<!--\s*geshop\s*alternate\s*end\s*-->/s';
        preg_match($alternate, $html, $matches);
        if (!empty($matches[1])) {
            $element = explode('/>', $matches[1]);
            $pushPipeline = $this->getPagePushPipelineList($pageConfig->group_id);
            $selectedPipeline = [];
            if (app()->arrayCache->exists('release-pipeline-list')) {
                $selectedPipeline = app()->arrayCache->get('release-pipeline-list');
            }
            $pipelineArray = array_merge($pushPipeline, $selectedPipeline);
            array_unshift($pipelineArray, $pageConfig->pipeline);
            $pipelineArray = array_unique($pipelineArray);
           
            if (!empty($pipelineArray) && is_array($pipelineArray)) {
                $parseArr = parse_url($pageConfig->share_link);
                if (substr_count($parseArr['path'], '/') > 1) {
                    $path = pathinfo($parseArr['path'])['dirname'];
                } else {
                    $path = $parseArr['path'];
                }
                $data = array_map(function ($item) {
                    return trim($item);
                }, $element);
                $data = array_filter($data);
                foreach ($data as $l_key => $label) {
                    $label = array_filter(explode(' ', $label));
                    $length = (strrpos($label[4], '"') - strpos($label[4], '"')) - 1;
                    $pipeline = substr($label[4], strpos($label[4], '"') + 1, $length);
                    $length = (strrpos($label[5], '"') - strpos($label[5], '"')) - 1;
                    $domain = substr($label[5], strpos($label[5], '"') + 1, $length);
                    if (!in_array($pipeline, $pipelineArray)) {
                        unset($data[ $l_key ]);
                    } else {
                        $href = "{$domain}{$path}/{$pageConfig->url_name}-special-{$pageConfig->special_id}.html";
                        $data[$l_key] = "{$label[0]} {$label[1]} {$label[2]} href=\"{$href}\" />";
                    }
                }
                $html = preg_replace($alternate, implode('', $data), $html);
            } else {
                $html = preg_replace($alternate, '', $html);
            }
        }
    }
    
    /**
     * 获取页面所有发布的渠道
     *
     * @param string $groupId
     *
     * @return array
     */
    public function getPagePushPipelineList(string $groupId)
    {
        $data = [];
        $files = PagePublishLogModel::getPageNewestPublishLogList($groupId, PageModel::PAGE_STATUS_HAS_ONLINE);
        if (!empty($files)) {
            $versions = array_column($files, 'version');
            $pushs = PagePublishLogModel::getPageNewestPushLogList($versions);
            $pushs = $pushs ? array_column($pushs, null, 'file_hash') : [];
            $errorLang = $successLang = [];
            foreach ($files as $file) {
                $pipeline = $file['pipeline'];
                $pipeLang = "{$pipeline}-{$file['lang']}";
                if (\array_key_exists($file['file_hash'], $pushs) && !\in_array($pipeLang, $successLang)
                    && !\in_array($pipeLang, $errorLang)
                ) {
                    array_push($successLang, $pipeLang);
                    array_push($data, $pipeline);
                } elseif (!\in_array($pipeLang, $errorLang) && !\in_array($pipeLang, $successLang)) {
                    array_push($errorLang, $pipeLang);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 替换页面seo相关内容
     *
     * @param object $pageConfig
     * @param string $html
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
     * @param object $pageConfig
     * @param string $html
     */
    private function appendSeoMeta($pageConfig, &$html)
    {
        $seoMeta = '<head>';
        $seoMeta .= "<title>{$pageConfig->title}</title>";
        $seoMeta .= '<meta name="keywords" content="' . $pageConfig->keywords . '"/>';
        $seoMeta .= '<meta name="description" content="' . $pageConfig->description . '"/>';
        $html = str_replace('<head>', $seoMeta, $html);
    }
    
    
    /**
     * 获取头尾部数据
     *
     * @param string $siteCode
     * @param string $lang
     * @param int    $place    1：活动页 2：首页
     * @param string $pipeline 渠道简码
     *
     * @return string
     */
    public function getHeadOrFooter($siteCode, $lang, $place, $pipeline)
    {
        $api = app()->params['sites'][ $siteCode ]['headFooterMonitorDomain'][ $place ][ $pipeline ][ $lang ] ?? '';
        if (!empty($api) && filter_var($api, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $data = '';
            try {
                $params = ['verify' => false];
                if (app()->env->isDev() || app()->env->isTest()) {
                    $params['headers'] = ['User-Agent' => !empty($_GET['ua']) ? $_GET['ua'] : 'member005'];
                }
                if (app()->env->isPreRelease()) {
                    $cookieJar = CookieJar::fromArray(
                        ['staging' => 'true'],
                        mb_substr(
                            app()->params['sites'][ $siteCode ]['domain'][ $pipeline ],
                            stripos(app()->params['sites'][ $siteCode ]['domain'][ $pipeline ], '.'),
                            strlen(app()->params['sites'][ $siteCode ]['domain'][ $pipeline ])
                        )
                    );
                    $params['cookies'] = $cookieJar;
                }
                $response = (new Client())->get($api, $params);
                $data = $response->getBody()->getContents();
                Yii::info("{$siteCode} {$place} 请求站点头尾：{$api}");
            } catch (ServerException $exception) {
                Yii::error("{$siteCode} {$place} 请求页面地址异常：{$exception->getMessage()}");
                app()->rms->reportHeadFooterError("{$siteCode} 请求页面地址异常：{$exception->getMessage()}");
            } catch (\RuntimeException $exception) {
                Yii::error("{$siteCode} {$place} 请求页面地址异常：{$exception->getMessage()}");
                app()->rms->reportHeadFooterError("{$siteCode} 请求页面地址异常：{$exception->getMessage()}");
            }
            
            // 2018-09-13 by tengjiashun 站点更换新模板地址，改为/index.php这种地址
            // 将模板中包含在<!-- geshop delet start -->和<!-- geshop delet end -->中间的内容给剔除掉
            $stylesheet = '/<!--\s*geshop\s*delet\s*start\s*-->(.*?)<!--\s*geshop\s*delet\s*end\s*-->/is';
            $data = preg_replace($stylesheet, '<!-- geshop delet start --><!-- geshop delet end -->', $data);
        }
        
        return $data ?? '';
    }
    
    /**
     * 并发请求头尾部页面
     *
     * @param array $urls
     */
    public function promiseSetHeadOrFooter(array $urls)
    {
        if (!empty($urls) && is_array($urls)) {
            $client = new Client();
            $pool = new Pool(
                $client,
                $this->yieldRequests($client, $urls),
                [
                    'concurrency' => 10,
                    'fulfilled'   => function ($response, $index) use ($urls) {
                        $info = $response->getBody()->getContents();
                        /** @var Response $response */
                        app()->arrayCache->set($urls[ $index ]['api'], $info);
                    },
                    'rejected'    => function ($e, $index) {
                        //TODO
                    },
                ]
            );
            $pool->promise()->wait();
        }
    }
    
    /**
     * 协程异步请求接口
     *
     * @param       $client Client
     * @param array $urls 请求接口
     *
     * @return \Generator
     */
    protected function yieldRequests($client, array $urls)
    {
        $params = [RequestOptions::VERIFY => false];
        foreach ($urls as $url) {
            if (app()->env->isDev() || app()->env->isTest()) {
                $params[ RequestOptions::HEADERS ] = ['User-Agent' => !empty($_GET['ua']) ? $_GET['ua'] : 'member005'];
            }
            if (app()->env->isPreRelease()) {
                $cookieJar = CookieJar::fromArray(
                    ['staging' => 'true'],
                    mb_substr(
                        app()->params['sites'][ $url['site_code'] ]['domain'][ $url['pipeline'] ],
                        stripos(app()->params['sites'][ $url['site_code'] ]['domain'][ $url['pipeline'] ], '.'),
                        strlen(app()->params['sites'][ $url['site_code'] ]['domain'][ $url['pipeline'] ])
                    )
                );
                $params[ RequestOptions::COOKIES ] = $cookieJar;
            }
            
            yield function () use ($client, $url, $params) {
                return $client->getAsync($url['api'], $params);
            };
        }
    }
    
    /**
     * 站点下线模板解析
     *
     * @param  \app\modules\common\gb\components\CommonPageConfig $pageConfig 页面配置对象
     * @param bool                                                $isAppSite  是否为APP站点
     * @param array                                               $data       模板数据
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
     * @param \app\modules\common\gb\components\CommonPageConfig $pageConfig 页面配置对象
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
        $goodsComponentStyle = !empty($pageConfig->goods_component_style)
            ? json_decode($pageConfig->goods_component_style, true)
            : [];
        if (SitePlatform::isPcPlatform($pageConfig->siteCode)) {
            $sitePath = $pageConfig->siteCode;
        } else {
            $sitePath = str_replace(strstr($pageConfig->siteCode, '-'), '-wap', $pageConfig->siteCode);
        }
        if (file_exists(APP_PATH . "/htdocs/resources/sitesPublic/{$sitePath}/twig/tpl_component_stylesheet.twig")) {
            $customCss .= app()->view->renderFile(
                APP_PATH . "/htdocs/resources/sitesPublic/{$sitePath}/twig/tpl_component_stylesheet.twig",
                $goodsComponentStyle
            );
        }
        
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
