<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Clothing\Tools\ServiceTracing\Recorder;
use Clothing\Tools\ServiceTracing\TracingFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// 让日志通过 stdout 打印到屏幕
$handler = new StreamHandler('php://stdout');

// 指定日志信息格式化的方式
$handler->setFormatter(new TracingFormatter);

// 创建 logger
$logger = new Logger('test_tracing');

// 加入日志输出方式
$logger->pushHandler($handler);

// 创建追踪信息记录器
$recorder = new Recorder($logger);

var_dump($recorder);
