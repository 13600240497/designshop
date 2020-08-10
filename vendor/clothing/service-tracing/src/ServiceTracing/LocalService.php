<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 用于处理当前服务的相关信息
 *
 */
class LocalService
{
    /**
     * 当前应用名称，示例：zaful-pc, zaful-app
     *
     * @var string
     */
    protected $appName;

    /**
     * 当前站点名称简称，示例："GB"、"ZF"
     *
     * @var null|string
     */
    protected $siteCode;

    /**
     * 本机 ipv4 地址，如果未指定将从 $servers 信息中获取
     *
     * @var null|string
     */
    protected $localIpv4;

    /**
     * 本机端口，如果未指定将从 $servers 信息中获取
     *
     * @var null|int
     */
    protected $localPort;

    /**
     * 全局 traceId，如果未设置将尝试从 HTTP 头信息中获取
     *
     * @var null|string
     */
    protected $globalTraceId;

    /**
     * 上级链路的 spanId，如果未设置将尝试从 HTTP 头信息中获取
     *
     * @var null|string
     */
    protected $parentSpanId;

    /**
     * 当前链路的 spanId，如果未设置将尝试从 HTTP 头信息中获取
     *
     * @var null|string
     */
    protected $spanId;

    /**
     * 当前页面服务端信息数组
     *
     * @var array
     */
    protected $servers = [];

    /**
     * 构造方法
     *
     * @param string $appName
     * @param string $siteCode
     * @param array  $servers
     */
    public function __construct($appName, $siteCode=null, array $servers=null)
    {
        $this->setAppName($appName);

        if ($siteCode) {
            $this->setSiteCode($siteCode);
        }

        $this->servers = $servers ?: $_SERVER;
    }

    /**
     * 设置当前链路的站点代码，例如：GB, ZF
     *
     * @param string $siteCode
     *
     * @return static
     */
    public function setSiteCode($siteCode)
    {
        $this->siteCode = (string) $siteCode;

        return $this;
    }

    /**
     * 获取当前链路的站点代码，例如：GB, ZF
     *
     * 如果未指定，将会从 $servers 头信息中获取
     *
     * @return string
     */
    public function getSiteCode()
    {
        if ($this->siteCode === null) {
            $this->siteCode = isset($this->servers['HTTP_SITECODE'])
                ? $this->servers['HTTP_SITECODE'] : '';
        }

        return $this->siteCode;
    }

    /**
     * 设置应用名称，例如： zaful-app, zaful-pc
     *
     * @param string $appName
     *
     * @return static
     */
    public function setAppName($appName)
    {
        $this->appName = (string) $appName;

        return $this;
    }

    /**
     * 获取应用名称
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * 设置本机 ipv4 地址及端口
     *
     * @param string $ip
     * @param int    $port
     *
     * @return static
     */
    public function setLocalHost($ip, $port=80)
    {
        $this->localIpv4 = $ip;
        $this->localPort = (int) $port;

        return $this;
    }

    /**
     * 获取本机 ipv4 地址及端口
     *
     * 如果未指定 ipv4 地址及端口，将从 $servers 信息中获取
     *
     * @return array
     */
    public function getLocalHost()
    {
        if ($this->localIpv4 === null) {
            $this->localIpv4 = isset($this->servers['SERVER_ADDR'])
                ? $this->servers['SERVER_ADDR'] : '127.0.0.1';
        }

        $long = ip2long($this->localIpv4);
        if ($long === false ||
            // 2130706432 为 127.0.0.0
            // 2147483647 为 127.255.255.255
           ($long >= 2130706432 && $long <= 2147483647)
        ) {
            // 通过打开一个的 socket 方式，获取本机 ipv4 地址
            $this->localIpv4 = get_local_ipv4_addr();
        }

        if ($this->localPort === null) {
            $this->localPort = isset($this->servers['SERVER_PORT'])
                ? $this->servers['SERVER_PORT']
                : (php_sapi_name() === 'cli' ? 0 : 80);
        }

        return ['ip' => $this->localIpv4, 'port' => (string)($this->localPort)];
    }

    /**
     * 设置一个全局的 traceId
     *
     * @param string $traceId
     *
     * @return static
     */
    public function setGlobalTraceId($traceId)
    {
        $this->globalTraceId = (string) $traceId;

        return $this;
    }

    /**
     * 获取全局 traceId
     *
     * 如果未指定，将会从 $servers 头信息中获取
     *
     * @return string
     */
    public function getGlobalTraceId()
    {
        if ($this->globalTraceId === null) {
            $this->globalTraceId = isset($this->servers['HTTP_TRACEID'])
                ? $this->servers['HTTP_TRACEID'] : '';
        }

        return $this->globalTraceId;
    }

    /**
     * 设置上级链路 spanId
     *
     * @param string $spanId
     *
     * @return static
     */
    public function setParentSpanId($spanId)
    {
        $this->parentSpanId = (string) $spanId;

        return $this;
    }

    /**
     * 获取上级链路 spanId
     *
     * 如果未指定，将从 $servers 头信息中获取
     *
     * @return string
     */
    public function getParentSpanId()
    {
        if ($this->parentSpanId === null) {
            $this->parentSpanId = isset($this->servers['HTTP_PARENTID'])
                ? $this->servers['HTTP_PARENTID'] : '';
        }

        return $this->parentSpanId;
    }

    /**
     * 设置当前链路的 spanId
     *
     * @param string $spanId
     *
     * @return static
     */
    public function setSpanId($spanId)
    {
        $this->spanId = (string) $spanId;

        return $this;
    }

    /**
     * 获取当前链路的 spanId
     * 如果未设置将尝试从 HTTP 头信息中获取
     * @return string
     */
    public function getSpanId()
    {
        if ($this->spanId === null) {
            $this->spanId = isset($this->servers['HTTP_SPANID'])
                ? $this->servers['HTTP_SPANID'] : '';
        }

        return $this->spanId;
    }
}
