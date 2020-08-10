<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Clothing\Tools\ServiceTracing\Generator;
use Clothing\Tools\ServiceTracing\LocalService;

//----------------------------------------------------------
// 初始化创建
// 关键信息将会从指定的 $servers 信息中获取
//      LocalHost, GlobalTraceId, ParentSpanId, SpanId
//----------------------------------------------------------
$siteCode = 'ZF';
$appName  = 'zaful-pc';
$servers  = require __DIR__ . '/servers.php';
$service  = new LocalService($siteCode, $appName, $servers);

var_export([
    'siteCode'      => $service->getSiteCode(),
    'appName'       => $service->getAppName(),
    'localHost'     => $service->getLocalHost(),
    'globalTraceId' => $service->getGlobalTraceId(),
    'parentSpanId'  => $service->getParentSpanId(),
    'spanId'        => $service->getSpanId(),
]);

//----------------------------------------------------------
// 通过设置的方式进行指定
//----------------------------------------------------------
$service->setSiteCode('RG')
    ->setAppName('rosegal-pc')
    ->setLocalHost('192.168.0.123', 8080)
    ->setGlobalTraceId(Generator::generateTraceId())
    ->setParentSpanId(Generator::generateSpanId())
    ->setSpanId(Generator::generateSpanId());

var_export([
    'siteCode'      => $service->getSiteCode(),
    'appName'       => $service->getAppName(),
    'localHost'     => $service->getLocalHost(),
    'globalTraceId' => $service->getGlobalTraceId(),
    'parentSpanId'  => $service->getParentSpanId(),
    'spanId'        => $service->getSpanId(),
]);
