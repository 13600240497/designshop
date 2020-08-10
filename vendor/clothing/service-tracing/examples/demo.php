<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Clothing\Tools\ServiceTracing\LocalService;
use Clothing\Tools\ServiceTracing\Recorder;
use Clothing\Tools\ServiceTracing\Tracer;
use Clothing\Tools\ServiceTracing\TracingFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// 初始化 local service
$siteCode   = 'ZF';
$appName    = 'zaful-pc';
$servers    = [
    'HTTP_TRACEID'       => '9b559735d3db127adc629d3e',
    'HTTP_SPANID'        => '659c51f60072b86b65596d25',
    'HTTP_PARENTID'      => '16995a3c528c0eda19e72f6b',
];
$localService  = new LocalService($siteCode, $appName, $servers);

// 初始化 logger 输出方式
$handler = new StreamHandler('php://stdout');
$handler->setFormatter(new TracingFormatter);

// 创建 logger
$logger = new Logger('test_tracing');
$logger->pushHandler($handler);

// 创建追踪信息记录器
$recorder = new Recorder($logger);

// 初始化 tracer 跟踪器
$serviceId  = 'Controller.GoodsController'; // 可根据 url 或 controller 类名指定
$methodName = 'detail'; // 具体的 action 方法
$tracer     = new Tracer($localService, $recorder, $serviceId, $methodName);

$tracer->start(); // 开始追踪

require __DIR__ . '/lib/mysql.class.php';

$mysql = $tracer->newBinaryClientWithProxy('mysql', new Mysql);

$redis = $tracer->newBinaryClient('redis');

$r = $redis->newRequest('connect', ['127.0.0.1', 6379])->start();
usleep(mt_rand(1000, 99999));
$r->finish();

try {
    $mysql->query('select * from users limit 1');
} catch (\Throwable $th) {
    echo  exception_as_string($th) . PHP_EOL;
}

$r = $redis->newRequest('get', ['my_redis_key'])->start();
usleep(mt_rand(1000, 99999));
$r->finish();

$r = $redis->newRequest('set', ['my_redis_key', 'my_redis_value'])->start();
usleep(mt_rand(1000, 99999));
$r->finish();

try {
    $mysql->truncate('users');
} catch (\Throwable $th) {
    echo  exception_as_string($th) . PHP_EOL;
}

try {
    $mysql->insert('users', ['name'=>'baz']);
} catch (\Throwable $th) {
    echo  exception_as_string($th) . PHP_EOL;
}

$r = $redis->newRequest('incr', ['my_redis_key'])->start();
usleep(mt_rand(1000, 99999));
$r->finish();

// 发起 api 请求
$request = $tracer->newRpcClientRequest('pdm.goods.info', 'get')->start();
usleep(mt_rand(100000, 999999));
$request->finish();

// 发起 api 请求
$request = $tracer->newRpcClientRequest('pdm.goods.stock', 'get')->start();
usleep(mt_rand(100000, 999999));
$request->finish();

var_export($request->getHeaders());

$tracer->finish(); // 追踪结束

// var_export($tracer->getRpcClientRequests());
