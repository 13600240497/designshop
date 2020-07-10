<?php

namespace app\modules\test\zf\components;

use Yii;


/**
 * 工具组件
 */
class ToolsComponent extends Component
{
    /**
     * 博客代理
     * @param array $params
     */
    public function blogProxy($params)
    {
        $isDownload = $params['download'] ?? 0;
        $pageType = $params['type'] ?? 'special';
        $sites = ['zf-pc', 'zf-wap'];

        $domain = ('special' == $pageType) ? 'secondary_domain' : 'home_secondary_domain';
        $publish =  ('special' == $pageType) ? 's3PublishPath' : 's3HomePublishPath';

        $proxyRules = [];
        foreach ($sites as $siteCode) {
            $pipelineList = app()->params['sites'][$siteCode][$domain];
            foreach ($pipelineList as $pipelineCode => $langList) {
                foreach ($langList as $langCode => $pageUrl) {
                    $format = '%s/xxxxx.html => geshoptest/%s/xxxxx.html';
                    $publishPath = app()->params['sites'][$siteCode][$publish][$pipelineCode][$langCode] ?? '';
                    $pageUrl = str_replace('promotion', 'blog', $pageUrl);
                    $publishPath .= '/blog';

                    $proxyRules[] = sprintf($format, $pageUrl, $publishPath);
                }
            }
        }

        if (1 === (int)$isDownload) {
            $outfile='blog.'.'txt';
            header('Content-type: application/octet-stream; charset=utf8');
            Header("Accept-Ranges: bytes");
            header('Content-Disposition: attachment; filename='.$outfile);
            echo join("\r\n", $proxyRules);
        } else {
            app()->response->isSent = true;
            print_r($proxyRules);
        }

        exit();
    }
}
