# Global Tracer - 服务治理平台接入组件 <链路追踪>

## 一、简介

该项目用于帮助我们快速接入服务治理平台，并对相关后端的 api 调用，`mysql`/`redis`/`rabbitmq`/`elasticsearch` 等组件的调用监控，做快速接入处理。

该组件提供了多种埋点接入方式，并且遵循 `psr-log` 规范进行日志记录。

服务清理平台接入方案请参考 wiki 内容：http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=155027500

服务治理平台的具体数据处理流程为：`项目写入调用链式日志` -> `中间件日志采集 agent` -> `kafka` -> `logstash` -> `elasticsearch`

## 二、组件安装

编辑 `composer.json`，新增以下代码：

```json
{
    "repositories": [
        {
            "url": "git@gitlab.egomsl.com:clothing/packages/service-tracing.git",
            "type": "git"
        },
        {
            "url": "git@gitlab.egomsl.com:clothing/packages/utils.git",
            "type": "git"
         }
    ],
    "require": {
        "clothing/service-tracing": "^1.0",
        "clothing/utils": "^1.0"
    }
}
```

然后运行 `composer update`，加载相关代码

## 三、使用说明

### 第一步：初始化日志记录器

日志记录器采用 `psr-log` 规范，具体初始化方法可参考[示例代码](examples/create_recorder.php)。

```php
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
```

**类功能说明：**
- `Clothing\Tools\ServiceTracing\Recorder` - 日志记录器，所有的调用链日志将通过这个实例进行记录
- `Clothing\Tools\ServiceTracing\TracingFormatter` - 遵循 [monolog](https://seldaek.github.io/monolog/) 标准规范，对日志数据进行格式化。这里的主要作用是转换为正确的 `json` 格式，以便服务治理平台采集数据。

**项目不支持 `psr-log` 组件怎么办？**

某些较旧的项目，或许可能不支持 `psr-log` 组件，则我们可以通过重写 `Clothing\Tools\ServiceTracing\Recorder` 类来实现独特的日志处理方法。

```php
use Clothing\Tools\ServiceTracing\Recorder;

class MyRecorder extends Recorder
{
    public function __construct()
    {
        // 重写构造方法，移除 logger 组件注册
    }
    protected function writeLog(array $data)
    {
        // 重写 writeLog 方法，自定义日志写入方式
    }
}
```

### 第二步：初始化 `local service`

`local service` 的作用用于获取及处理本地服务信息，主要包含以下内容：

|     属性      |  类型   |                                     说明                                     |           示例           |
| ------------- | ------- | ---------------------------------------------------------------------------- | ------------------------ |
| appName       | string  | 当前应用名称                                                                  | "zaful-pc", "zaful-app"  |
| siteCode      | string  | 当前站点名称简称 ，如果未设置将尝试从 HTTP 头信息中获取                           | "GB", "ZF"               |
| localIpv4     | string  | 服务器本机 ipv4 地址，如果未指定将从 $servers 信息中获取                         |                          |
| localPort     | integer | 本机端口，如果未指定将从 $servers 信息中获取                                    |                          |
| globalTraceId | string  | 全局 traceId，如果未设置将尝试从 HTTP 头信息中获取，为 24 位长度全局唯一字符串     | 8c0e7c5af647f34235100540 |
| spanId        | string  | 当前链路的 spanId，如果未设置将尝试从 HTTP 头信息中获取，为 24 位长度全局唯一字符串 | 1c63f36acf0b93b10cff44c9 |
| parentSpanId  | string  | 上级链路的 spanId，如果未设置将尝试从 HTTP 头信息中获取。当没有值时，默认值为 "0"   | 0                        |

初始化方法可参考[示例代码](examples/create_local_service.php)。

```php
use Clothing\Tools\ServiceTracing\Generator;
use Clothing\Tools\ServiceTracing\LocalService;

// 初始化创建
$appName  = 'zaful-pc';
$siteCode = 'ZF'; // 如果不指定，将从 http 头信息中获取
$servers  = $_SERVER; // 如果不指定，也将会是使用 PHP 全局变量 $_SERVER
$localService  = new LocalService($appName, $siteCode, $servers);

// 当未指定 LocalHost, GlobalTraceId, ParentSpanId, SpanId 信息时
// 将会从 $servers 信息中获取这些信息
var_export([
    'appName'       => $localService->getAppName(),
    'siteCode'      => $localService->getSiteCode(),
    'localHost'     => $localService->getLocalHost(),
    'globalTraceId' => $localService->getGlobalTraceId(),
    'parentSpanId'  => $localService->getParentSpanId(),
    'spanId'        => $localService->getSpanId(),
]);

// 通过设置的方式进行指定相关信息
// 这些信息将会对后续追踪过程中的基本信息，产生影响
$localService
    ->setAppName('rosegal-pc')
    ->setSiteCode('RG')
    ->setLocalHost('192.168.0.123', 8080)
    ->setGlobalTraceId(Generator::generateTraceId())
    ->setParentSpanId(Generator::generateSpanId())
    ->setSpanId(Generator::generateSpanId());
```

### 第三步：Service-Tracing 组件初始化

初始化方法可参考[示例代码](examples/demo.php)。

```php
use Clothing\Tools\ServiceTracing\Tracer;

// 可根据 url 或 controller 类名指定
// 例如在 mvc 框架中，可以获取当前 controller 的完整类名
// 例如 App.Http.Controllers.GoodsController (请替换掉反斜线 "\")
$serviceId  = 'Controller.GoodsController';

// 可指定具体的 action 方法
$methodName = 'detailAction';

// 执行 Tracer 初始化
// 这个步骤可以放在项目的入口文件中，例如 public/index.php
// 或者放在公共基础控制器中，例如 BaseController 的 init 方法中
$tracer = new Tracer($localService, $recorder, $serviceId, $methodName);

// 开始执行追踪
$tracer->start();

// 页面逻辑执行
// ...

// 页面逻辑执行完毕，结束调用追踪
// 如果不手动调用 finish 方法
// 则 php-cgi 进程将在会话结束后，调用 $tracer->flush() 方法，自动结束
$tracer->finish();
```

### 第四步：API 请求埋点

![](https://user-gold-cdn.xitu.io/2018/6/4/163c9bee3a9d029a?imageslim)

注解概念参考：https://juejin.im/post/5a7a9e0af265da4e914b46f1（3.3 Annotation）
- cs：Client Start，表示客户端发起请求，对应常量：`Clothing\Tools\ServiceTracing\Tracer::CLIENT_START`
- sr：Server Received，表示服务端收到请求，对应常量：`Clothing\Tools\ServiceTracing\Tracer::SERVER_RECV`
- ss：Server Send，表示服务端完成处理，并将结果发送给客户端，对应常量：`Clothing\Tools\ServiceTracing\Tracer::SERVER_SEND`
- cr：Client Received，表示客户端获取到服务端返回信息，对应常量：`Clothing\Tools\ServiceTracing\Tracer::CLIENT_RECV`

需要在客户端发起请求的位置，插入以下代码：

```php
$serviceId = 'pdm.goods.info'; // API 服务名
$methodName = 'get'; // API 服务方法
$request = $tracer->newRpcClientRequest($serviceId, $methodName)->start(); // 创建追踪请求

try {
    $headers = $request->getHeaders();

    // curl send http request
    // 把 headers 信息，加入到 curl http 请求头信息中
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

} catch (\Exception $e) {
    $request->setException($e); // 记录异常
    throw $e; // 重新抛出异常
}

$request->finish(); // 结束追踪
```

### 第四步：基于组件客户端的埋点

针对 `mysql` 的埋点，需要记录其具体的 `sql` 语句。其它像 `redis`/`memcached`/`elasticsearch` 等组件，只需要记录具体的方法调用及参数即可。

以一个简单的 mysql 类举例：

```php
class Mysql
{
    public function query($sql)
    {
        return $this->connection->execute($sql);
    }

    public function insert($table, array $data)
    {
        $sql = "insert into $table ....";

        return $this->query($sql);
    }

    public function truncate($table)
    {
        throw new \RuntimeException('disable truncate');
    }
}
```

**埋点的方式有两种，第一种，通过继承的方式实现：**

```php
use Clothing\Tools\ServiceTracing\Tracer;

class MysqlWithTracing extends Mysql
{
    protected static $tracerClient;

    public function getTracerClient()
    {
        return self::$tracerClient ?: Tracer::singleton()->newBinaryClient('mysql');
    }

    public function query($sql)
    {
        // 创建客户端埋点
        $request = $this->getTracerClient()->newRequest('query', [$sql])->start();
        try {
            $result = parent::query($sql); // 执行应该处理的工作
        } catch (\Exception $e) {
            $request->setException($e); // 记录异常
            throw $e; // 重新抛出异常
        }

        $request->finish(); // 结束追踪

        return $result;
    }
}
```

**第二种方式，通过创建代理类，进行包裹的方式实现：**

```php
$mysql = new Mysql;
$clientType = 'mysql';
$listenMethods = null; // 当值为 null 时，将监听所有的方法
$listenMethods = ['query', '...']; // 将监听指定数据中指定的方法
$mysql = $tracer->newBinaryClientWithProxy($clientType, $mysql, $listenMethods);
$mysql->query('xxxx');
```

通过这种方式进行追踪，将会对调用的方法，以及相关的参数，自动做追踪日志记录。无法实现像第一种方式一样，可以通过重载的方式，进行追踪的个性化处理。

### 第五步：不依赖组件客户端的埋点

这种方式，不依赖于任务客户端组件。

```php
$client = $tracer->newBinaryClient('rabbitmq');
$request = $client->newRequest('connect', [$ip, $port, ...])->start();
// rabbitmq connect ...
$request->finish();
```

整个埋点过程，不与 rabbitmq 的组件产生任何关联与交集，纯粹自主确定开始、结束时间。

### 其它信息：

当前我们创建下面这些请求时，对应的类将会被注册到一个 hash 数组对象中，参考 [src/ServiceTracing/ObjectHashTrait.php](src/ServiceTracing/ObjectHashTrait.php)。

以下是使用了 `ObjectHashTrait` 特征的类，及相关方法：
- `Clothing\Tools\ServiceTracing\BinaryClient`
    - `$tracer->newBinaryClient($clientType)`
- `Clothing\Tools\ServiceTracing\BinaryClientProxy`
    - `$tracer->newBinaryClientFromProxy(BinaryClientProxy $proxy)`
    - `$tracer->newBinaryClientWithProxy($clientType, $clientInstance, array $listenMethods=null)`
- `Clothing\Tools\ServiceTracing\BinaryClientRequest`
    - `$binaryClient->newRequest($method, array $params=[])`
    - `$binaryClientProxy->newRequest($method, array $params=[])`
- `Clothing\Tools\ServiceTracing\RpcClientRequest`
    - `$tracer->newRpcClientRequest($serviceId, $methodName)`

这些类在创建实例后，可通过 `HASH ID` 从全局任意位置，通过 `withHashId` 方法静态获取已创建的实例，例如：

```php

$client = $tracer->newBinaryClientWithProxy('redis', new Redis);
$hashId = $client->getHashId();

// 可在任意位置获取到这个 $client 对象
$client = Clothing\Tools\ServiceTracing\BinaryClientProxy::withHashId($hashId);
```

具体 demo 可查看 phpunit 的[单元测试代码](tests/ObjectHashTraitTest.php)
