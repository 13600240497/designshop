<?php
namespace Globalegrow\PhpProfile;

/**
 * xhprof性能分析
 */
class PhpProfile
{
    /**
     * @var string 可通过`$_GET`或者`$_COOKIE`触发性能分析的变量名称
     */
    const TRIGGER_NAME = 'globalegrow-profile';
    /**
     * @var int 最大性能日志文件大小，单位：字节
     */
    const MAX_FILESIZE = 104857600; // 100M
    /**
     * @var bool 已经开始收集?
     */
    protected static $started;
    /**
     * 采样率
     *
     * 值越小，采样率越高，1000表示1/1000采样，平均1000次请求采样一次
     *
     * - 开发环境：1
     * - 测试环境：10
     * - 生产环境：1000
     *
     * @var int
     */
    public $sampling = 1;
    /**
     * @var float 收集的最小页面执行时间，减少收集系统数据量，单位秒
     */
    public $minPageTime = 0.01;
    /**
     * @var float 收集函数/方法执行的最小时间，减少收集系统数据量，单位秒
     */
    public $minFunctionTime = 0.01;
    /**
     * @var string 非生产环境下的性能实时收集url
     */
    public $collectUrl = 'http://www.rum.com.master.test50.egomsl.com/test/index/save-php-profile';
    /**
     * @var bool 调试?
     */
    protected $isDebug;
    /**
     * @var string 站点
     */
    protected $site;
    /**
     * @var string 性能日志文件保存路径
     */
    protected $profileFile;
    /**
     * @var AbstractXhprof
     */
    protected $xhprof;

    /**
     * 构造函数
     *
     * @param string $site
     * @param bool $isDebug
     * @param AbstractXhprof $xhprof
     */
    public function __construct($site, $isDebug, AbstractXhprof $xhprof = null)
    {
        $this->site = $site;
        $this->isDebug = $isDebug;
        $this->profileFile = dirname(ini_get('error_log')) . '/profile.dat';
        $this->initSampling();
        $this->xhprof = $xhprof;
        if (!$xhprof) {
            $this->xhprof = PHP_VERSION_ID > 70000 ? new Xhprof() : new Uprofiler();
        }
    }

    /**
     * 开始分析
     *
     * @return bool
     */
    public function start()
    {
        if (static::$started
            || !$this->xhprof->isEnable()
            || (1 !== mt_rand(1, $this->sampling) && !isset($_COOKIE[static::TRIGGER_NAME]) && !isset($_GET[static::TRIGGER_NAME]))
        ) {
            return false;
        }

        if (isset($_GET[static::TRIGGER_NAME]) && !headers_sent()) {
            setcookie(static::TRIGGER_NAME, 1);
        }
        static::$started = true;
        $this->enable();
        register_shutdown_function([$this, 'stop']);
        return true;
    }

    /**
     * 结束分析
     *
     * @return bool|int
     */
    public function stop()
    {
        if (!static::$started) {
            return false;
        }

        $pageTime = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 6);
        if ($pageTime < $this->minPageTime) {
            return false;
        }

        return $this->save([
            '_SERVER' => $_SERVER,
            'exec_time' => $pageTime,
            'site' => $this->site,
            'ip' => $this->getIp(),
            'profile' => $this->disable()
        ]);
    }

    /**
     * 初始化采样率
     *
     * @return int
     */
    protected function initSampling()
    {
        $this->sampling = 1000;
        if ($this->isDebug) {
            $this->sampling = 1;
        } elseif (0 === strpos((isset($_SERVER['ENV']) ? $_SERVER['ENV'] : null), 'test')) {
            $this->sampling = 10;
        }
        return $this->sampling;
    }

    /**
     * 保存性能数据
     *
     * @param array $data
     * @return bool|int
     */
    protected function save($data)
    {
        if ($this->isDebug) {
            $this->saveByDebug($data);
        }

        // 大于100M时，清空
        if (is_file($this->profileFile)
            && filesize($this->profileFile) > static::MAX_FILESIZE
        ) {
            file_put_contents($this->profileFile, '');
        }
        return file_put_contents($this->profileFile, serialize($data) . "\n", FILE_APPEND);
    }

    /**
     * 调试环境下保存性能数据
     *
     * @param array $data
     * @return int
     */
    protected function saveByDebug($data)
    {
        $ch = curl_init($this->collectUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => ['data' => gzcompress(serialize($data))],
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        return $errno;
    }

    /**
     * 获取ip
     *
     * @return string
     */
    protected function getIp()
    {
        if (isset($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
            return $_SERVER['HTTP_TRUE_CLIENT_IP'];
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '0.0.0.0';
    }

    /**
     * 启用收集
     */
    protected function enable()
    {
        $this->xhprof->enable();
    }

    /**
     * 停止收集
     *
     * @return array
     */
    protected function disable()
    {
        $time = $this->minFunctionTime * 1000000;
        return array_filter($this->xhprof->disable(), function ($item) use ($time) {
            return $item['wt'] > $time;
        });
    }
}
