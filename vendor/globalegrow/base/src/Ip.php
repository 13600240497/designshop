<?php
namespace Globalegrow\Base;

/**
 * ip组件
 */
class Ip extends Component
{
    /**
     * @var string|array $searches 搜索`$_SERVER`的键名
     */
    public $searches = 'HTTP_TRUE_CLIENT_IP,REMOTE_ADDR';
    /**
     * @var array ip信息，**['ip地址', 'ip2long']**
     */
    protected $ip;

    /**
     * 获取ip地址
     *
     * @param bool $ip2long **true**时返回非负的`ip2long`
     * @return string|int
     *
     * - 获取成功时，返回ip地址（**ip2long**为**true**时返回非负的ip2long）
     * - 获取失败，返回**0.0.0.0**（**ip2long**为**true**时返回**0**）
     */
    public function get($ip2long = false)
    {
        $index = $ip2long ? 1 : 0;
        if (null !== $this->ip) {
            return $this->ip[$index];
        }

        $ip = null;
        foreach (Arr::trim($this->searches) as $item) {
            if ($ip = (isset($_SERVER[$item]) ? $_SERVER[$item] : null)) {
                $ip = strstr($ip, ',', true) ?: $ip;
                break;
            }
        }

        $this->ip = [$ip, sprintf('%u', ip2long($ip))];
        if (!$this->ip[1]) {
            $this->ip = ['0.0.0.0', 0];
        }
        return $this->ip[$index];
    }
}
