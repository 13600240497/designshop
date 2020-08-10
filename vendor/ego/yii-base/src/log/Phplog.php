<?php
namespace ego\log;

use yii\base\Component;
use yii\base\UserException;

/**
 * php日志接口组件
 */
class Phplog extends Component
{
    /**
     * @var string 日志路径
     */
    public $root = null;
    /**
     * @var int 可通过浏览器访问日志路径结束时间
     */
    public $endTime = null;
    /**
     * @var int 最大可查看日志大小
     */
    public $maxSize = 5632; // kb, 5.5M
    /**
     * @var string 文件结尾
     */
    public $fileSuffix = '/\.(txt|log)(\.\d+)?/';

    /**
     * 获取日志
     *
     * @param string $file
     * @return string|array
     * @throws UserException
     */
    public function get($file)
    {
        $file = urldecode($file);
        if ($file && false !== strpos($file, '..')) {
            return '日志文件"' . $file . '"不能使用相对路径';
        } elseif ($file && preg_match($this->fileSuffix, $file)) { // 查看文件
            if (is_file($file = $this->root . '/' . $file)) {
                if (filesize($file) > $this->maxSize * 1024) {
                    return '文件大小超出限制';
                } else {
                    $content = htmlspecialchars(file_get_contents($file), ENT_QUOTES|ENT_IGNORE);
                    $content = explode(FileTarget::SEPARATOR, $content);
                    $content = array_reverse($content);
                    return join("\n", array_map('trim', $content));
                }
            } else {
                return '日志文件"' . $file . '"不存在';
            }
        }

        $result = [];
        $path = $this->root;
        if ($file) {
            $path = $this->root . '/' . $file;
        }

        if (is_dir($path)) {
            /** @var \SplFileInfo[] $iterator */
            $iterator   = new \FilesystemIterator($path);
            $len        = strlen($this->root) + 1;
            foreach($iterator as $item) {
                $file = substr($item, $len);
                if ($item->isFile()) {
                    $result[$file] = [
                        'file'  => substr($item, $len),
                        'size'  => app()->helper->formatSize($item->getSize()),
                        'time'  => app()->datetime->cn->setTimestamp($item->getMTime())
                            ->format('Y-m-d H:i:s'),
                    ];
                } else {
                    $result[$file] = [
                        'file' => substr($item, $len),
                        'size' => '--',
                        'time' => '--',
                    ];
                }
            }
        }

        // 按时间倒序
        uasort($result, [$this, 'sortFile']);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->root = $this->root ?: \Yii::getAlias('@app/runtime/logs');
        if ($this->endTime && time() > $this->endTime) {
            throw new UserException('非法访问');
        }
    }

    /**
     * 按时间倒序文件
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    private function sortFile($a, $b)
    {
        return $a['time'] > $b['time'] ? -1 : 1;
    }
}