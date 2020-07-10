<?php

namespace app\modules\base\controllers;

use yii\web\Response;

/**
 * php日志
 */
class PhplogController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        app()->response->format = Response::FORMAT_HTML;
    }

    /**
     * 获取日志页面
     *
     * @param string $file
     * @return string
     * @throws \yii\base\UserException
     */
    public function actionList($file = null)
    {
        $result = app()->phplog->get($file);
        if (\is_array($result)) {
            array_walk($result, function (&$item) {
                $item['file'] = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    app()->url->admin('base/phplog/list', ['file' => $item['file']]),
                    $item['file']
                );
            });
            $result = var_export($result, true);
        }
        return '<pre>' . $result . '</pre>';
    }

    /**
     * 下载日志文件
     * @param null $file
     * @return string
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionDownload($file)
    {
        if (substr_count($file, '.') !== 1 || false !== strpos($file, '/')
            || substr($file, -\strlen('.log')) !== '.log') {
            return '非法文件名';
        }

        $filepath = \Yii::getAlias('@runtime/logs/') . $file;
        if (!file_exists($filepath)) {
            return '文件不存在';
        }

        ob_clean();
        $fp = fopen($filepath, 'rb');
        $filesize = filesize($filepath);
        header('Content-type:application/octet-stream');
        header('Accept-Ranges:bytes');
        header('Accept-Length:' . $filesize);
        header('Content-Disposition: attachment; filename=' . $file);
        $buffer = 1024;
        $buffer_count = 0;
        while (!feof($fp) && $filesize - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
        }
        fclose($fp);
    }
}
