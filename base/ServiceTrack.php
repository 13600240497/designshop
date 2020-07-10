<?php
/**
 *--------------------------------------------------------------------------
 * 接入服务治理，进行链路服务追踪
 *--------------------------------------------------------------------------
 * 服务治理平台的具体数据处理流程为：
 * 项目写入调用链式日志 -> 中间件日志采集 agent -> kafka -> logstash -> elasticsearch
 * @see http://gitlab.egomsl.com/clothing/packages/service-tracing
 * @see http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=155027500
 *
 */

namespace app\base;

use Mellivora\Logger\LoggerFactory;
use Monolog\Handler\StreamHandler;
use Clothing\Tools\ServiceTracing\Recorder;
use Clothing\Tools\ServiceTracing\Tracer;
use Clothing\Tools\ServiceTracing\BinaryClient;
use Clothing\Tools\ServiceTracing\TracingFormatter;
use Clothing\Tools\ServiceTracing\RpcClientRequest;
use Clothing\Tools\ServiceTracing\LocalService;

class ServiceTrack
{
    const SITE_CODE = 'geshop';
    const APP_NAME  = 'geshop-pc';

    // 服务追踪标识
    const TRACE_RPC = 'rpc.geshop';

    //SOA服务追踪标识
    const TRACE_RPC_SOA = 'rpc.soa';

    // Esearch服务追踪标识
    const TRACE_RPC_ESEARCH = 'rpc.esearch';

    // 状态值
    const STATUS_STARTED = 1;
    const STATUS_STOPED = 2;

    // 追踪器状态
    protected static $tracerStatus;

    /**
     * 装配追踪器
     * @return bool
     */
    public function setupTracer()
    {
        if (( PHP_SAPI == 'cli') && empty($_COOKIE['trace_debug'])) {
            return false;
        }

        if (self::$tracerStatus == self::STATUS_STARTED) {
            return false;
        }
        //过滤静态资源请求
        $redirect_url = !empty($_SERVER['REQUEST_URI']) ?  str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) : '';
        $redirect_url = trim($redirect_url, '?');

        if(!empty($redirect_url) && preg_match('/.*\.(gif|jpg|jpeg|png|swf|js|css|ico|map)/is', $redirect_url)){
            return  false;
        }
        try {
            // 让日志通过 stdout 打印到屏幕
            $handler = new StreamHandler('php://stdout');
            // 指定日志信息格式化的方式
            $handler->setFormatter(new TracingFormatter);
            // 创建 logger
            LoggerFactory::setRootPath(APP_PATH);
            LoggerFactory::build(
                [
                    'formatters' => [
                        // 服务治理追踪日志格式
                        'tracing' => [
                            'class' => \Clothing\Tools\ServiceTracing\TracingFormatter::class
                        ],
                    ],
                    'handlers'   => [
                        // 服务治理追踪日志
                        'tracing' => [
                            'class'  => 'Mellivora\Logger\Handler\NamedRotatingFileHandler',
                            'params' => [
                                'filename'    => 'runtime/traceLogs/tracing.%date%.log',
                                'maxBytes'    => 1024 * 1024 * 1024,  // 文件最大尺寸 1G
                                'backupCount' => 2,  // 文件保留数量
                                'bufferSize'  => 0,  // 缓冲区大小(日志数量)
                                'dateFormat'  => 'YmdH',  // 日期格式，加入小时
                                'level'       => 'INFO',    //重要模块需要记录INFO及以上信息
                            ],
                            'formatter'  => 'tracing',
                            'processors' => ['profiler', 'web', 'script', 'memory'],
                        ],
                    ],
                    'loggers'    => [
                        'tracing' => ['tracing'],
                    ],
                ]
            );
            $logger = LoggerFactory::singleton()->get('tracing');
            // 创建追踪信息记录器
            $recorder = new Recorder($logger);

            // 初始化创建
            $localService = new LocalService(self::APP_NAME,  self::SITE_CODE);

            $serviceId = !empty(trim($redirect_url, '/')) ? trim($redirect_url, '/') : 'activity/index';
            $serviceId = explode('/', $serviceId);
            $methodName = array_pop($serviceId);
            //初始化trace
            $tracer = new Tracer($localService, $recorder, join('.', $serviceId),  $methodName);
            $tracer->start();

            self::$tracerStatus = self::STATUS_STARTED;

            register_shutdown_function([$this, 'shutdownTracer']);
            return true;

        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * 关闭追踪器，同时写追踪日志
     * @return void
     */
    public function shutdownTracer()
    {
        if (self::$tracerStatus != self::STATUS_STARTED) {
            return;
        }

        try {
            Tracer::singleton()->flush();
        } catch (\Exception $e) {
        }
    }

    /**
     * 终止追踪
     */
    public static function stopTracing()
    {
        self::$tracerStatus = self::STATUS_STOPED;
    }

    /**
     * 设置服务名称（ID）和方法（Name）
     * @param $serviceId
     * @param $methodName
     * @return  bool
     */
    public static function setServiceIdName($serviceId, $methodName)
    {
        if (empty($serviceId)) {
            return false;
        }
        empty($methodName) && $methodName = 'unknown';
        try {
            $serviceId = str_replace(['/', '\\'], '.', $serviceId);
            Tracer::singleton()->setServiceId($serviceId);
            Tracer::singleton()->setMethodName($methodName);
        } catch (\Exception $e) {
        }
        return  true;
    }

    /**
     * 获取客户端调用追踪器
     * @param $client
     * @param object     $clientInstance
     * @param null|array $listenMethods
     * @param  array $noListenMethods
     * @return BinaryClient|null
     */
    public static function getClientTracer($client, $clientInstance = null, array $listenMethods = null, $noListenMethods = [])
    {
        if (self::$tracerStatus != self::STATUS_STARTED) {
            return null;
        }

        try {
            if ($clientInstance) {
                $tracer = Tracer::singleton()->newBinaryClientWithProxy($client, $clientInstance, $listenMethods, $noListenMethods);
            } else {
                $tracer = Tracer::singleton()->newBinaryClient($client);
            }
            return $tracer;
        } catch (\Exception $e) {
            
        }

        return null;
    }

    /**
     * 获取Mysql追踪器
     * @return BinaryClient|null
     */
    public static function getMysqlTracer()
    {
        return self::getClientTracer('mysql');
    }

    /**
     * 获取Redis追踪器
     * @return BinaryClient|null
     */
    public static function getRedisTracer()
    {
        return self::getClientTracer('redis');
    }

    /**
     * 获取Mysql代理客户端
     * @param $mysqlClient
     * @param array|null $listenMethods
     * @return BinaryClient|null
     */
    public static function getMysqlProxyTracerClient($mysqlClient, array $listenMethods = ['prepare'])
    {
        return self::getClientTracer('mysql', $mysqlClient, $listenMethods);
    }

    /**
     * 获取Redis代理客户端
     * @param $redisClient
     * @param array|null $listenMethods
     * @param array $noListenMethods
     * @return BinaryClient|null
     */
    public static function getRedisProxyTracerClient($redisClient, array $listenMethods = null, array $noListenMethods = ['pipeline'])
    {
        return self::getClientTracer('redis', $redisClient, $listenMethods, $noListenMethods);
    }

    /**
     * 获取RPC追踪器
     * @param $serviceId
     * @param $methodName
     * @return RpcClientRequest|null
     */
    public static function getRpcTracer($serviceId, $methodName)
    {
        if (self::$tracerStatus != self::STATUS_STARTED) {
            return null;
        }

        try {
            return Tracer::singleton()->newRpcClientRequest($serviceId, $methodName);
        } catch (\Exception $e) {
            Yii::info($e->getLine().$e->getMessage(), __METHOD__);
        }

        return null;
    }

    /**
     * 获取HTTP RPC追踪请求头
     * @param RpcClientRequest $rpcRequest
     * @param string $isSample
     * @return array
     */
    public static function getRpcTraceHeader(RpcClientRequest $rpcRequest, $isSample = 'true')
    {
        $headers = $rpcRequest->getHeaders();
        $headers = array_merge($headers, ['isSample: ' . $isSample]);
        return $headers;
    }

    /**
     * 获取HTTP RPC追踪请求头 数组key => value形式
     * @param RpcClientRequest $rpcRequest
     * @param string $isSample
     * @return array
     */
    public static function getRpcTraceHeaderWithKey(RpcClientRequest $rpcRequest, $isSample = 'true')
    {
        $headers = $rpcRequest->getHeadersWithKey();
        $headers['isSample'] = $isSample;
        return $headers;
    }
}
