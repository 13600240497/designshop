<?php

namespace app\components;

use ego\log\FileTarget;
use yii\base\UserException;

/**
 * php日志接口组件
 */
class Phplog extends \ego\log\Phplog
{
    /**
     * 获取日志
     *
     * @param string $file
     * @return string|array
     * @throws UserException
     */
    public function get($file)
    {
        $fileStr = $file;
        $file = urldecode($file);
        if ($file && false !== strpos($file, '..')) {
            return '日志文件"' . $file . '"不能使用相对路径';
        } elseif ($file && preg_match($this->fileSuffix, $file)) { // 查看文件
            if (is_file($file = $this->root . '/' . $file)) {
                if (filesize($file) > $this->maxSize * 1024) {
                    $content = $this->tail(fopen($file, "r+"), 2000);
                    return join("\n", array_map('trim', $content));
                }
                $content = htmlspecialchars(file_get_contents($file), ENT_QUOTES | ENT_IGNORE);
                $content = explode(FileTarget::SEPARATOR, $content);
                $content = array_reverse($content);
                return join("\n", array_map('trim', $content));
            }
        }

        return parent::get($fileStr);
    }

    /**
     * 获取文件最后的数据
     *
     * @param resource $fileHandle 文件的句柄
     * @param int $lastNum 最后多少行
     * @param int $base
     * @return string
     */
    private function tail($fileHandle, $lastNum, $base = 5)
    {
        assert($lastNum > 0);
        $pos = $lastNum + 1;
        $lines = [];
        while (count($lines) <= $lastNum) {
            try {
                fseek($fileHandle, -$pos, SEEK_END);
            } catch (\Exception $e) {
                fseek($fileHandle, 0);
                break;
            }
            $pos *= $base;
            while (!feof($fileHandle)) {
                array_unshift($lines, htmlspecialchars(fgets($fileHandle), ENT_QUOTES | ENT_IGNORE));
            }
        }
        $content = join('', array_slice($lines, 0, $lastNum));
        return explode(FileTarget::SEPARATOR, $content);
    }
}
