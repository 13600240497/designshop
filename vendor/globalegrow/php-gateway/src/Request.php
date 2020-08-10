<?php
namespace Globalegrow\Gateway;

/**
 * 请求
 */
class Request
{
    /**
     * @var resource
     */
    protected $curl;
    /**
     * @var array
     */
    protected $options = [
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
    ];

    /**
     * 构造函数
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->curl = curl_init($url);
        curl_setopt_array($this->curl, $this->options);
    }

    /**
     * 发送请求
     *
     * @param array $data
     * @return string|false
     */
    public function send(array $data)
    {
        if ($this->isPreRelease()) {
            $this->setPreRelease();
        };
        $this->setOptions([
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
        return curl_exec($this->curl);
    }

    /**
     * `curl_setopt_array`
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        curl_setopt_array($this->curl, $options);
        return $this;
    }

    /**
     * 获取curl对象
     *
     * @return resource
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * `curl_error`
     *
     * @return string
     */
    public function error()
    {
        return curl_error($this->curl);
    }

    /**
     * `curl_getinfo`
     *
     * @param int $opt
     * @return array|string
     */
    public function getinfo($opt = null)
    {
        return curl_getinfo($this->curl, $opt);
    }

    /**
     * `curl_close`
     */
    public function close()
    {
        curl_close($this->curl);
    }

    /*
     * 设置为预发布环境
     */
    public function setPreRelease()
    {
        $this->addHeader('Cookie', 'staging=true');
    }

    /**
     * 增加头信息
     *
     * @param $key string 头的名称
     * @param $val string 头的值
     */
    public function addHeader($key, $val)
    {
        $header = [$key .':'. $val];
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
    }

    /**
     * 判断是否是预发布环境
     */
    public function isPreRelease()
    {
        return isset($_COOKIE['staging']) && 'true' === $_COOKIE['staging'];
    }
}
