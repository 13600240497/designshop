<?php

namespace app\modules\test\controllers;

use app\modules\common\traits\CommonPublishTrait;
use yii\web\Response;

/**
 * 配置文件控制器
 */
class ConfigController extends Controller
{
    use CommonPublishTrait;

    /**
     * 文件差异对比内容为空时的html结尾
     */
    const EMPTY_DIFF_HTML_END = /** @lang html */
        '<body></body></html>';

    /**
     * 文件差异对比内容为空时的默认替换html结尾
     */
    const EMPTY_DIFF_HTML_END_DEFAULT = /** @lang html */
        '<body><h2>暂无文件差异</h2></body></html>';

    /**
     * 配置文件比较
     */
    public function actionCompare()
    {
        $oldSiteConfig = require app()->basePath . '/config/sites/sites.' . YII_ENV . '.php';
        $newSiteConfig = app()->params['sites'];

        $html = $this->getDiffContent(
            $this->arrayToString($this->multiKsort($oldSiteConfig)),
            $this->arrayToString($this->multiKsort($newSiteConfig))
        );

        if (false !== stripos($html, static::EMPTY_DIFF_HTML_END)) {
            $html = str_replace(static::EMPTY_DIFF_HTML_END, static::EMPTY_DIFF_HTML_END_DEFAULT, $html);
        }

        app()->response->format = Response::FORMAT_HTML;
        echo $html;
        exit;
    }

    /**
     * 多维数组转string
     * @param array $arr
     * @return string
     */
    private function arrayToString(array $arr)
    {
        $str = 'Array' . PHP_EOL;
        $str .= '(' . PHP_EOL;

        if (!empty($arr)) {
            foreach ($arr as $key => $val) {
                $str .= '[' . $key . '] => ';
                if (!\is_array($val)) {
                    $str .= $val . PHP_EOL;
                } else {
                    $str .= $this->arrayToString($val) . PHP_EOL;
                }
            }
        }

        $str .= ')' . PHP_EOL;

        return $str;
    }

    /**
     * 多维排序
     * @param array $arr
     * @return array
     */
    private function multiKsort(array $arr)
    {
        ksort($arr);
        if (!empty($arr)) {
            foreach ($arr as $key => $val) {
                ksort($val);
                $arr[$key] = $val;
            }
        }
        return $arr;
    }
}
