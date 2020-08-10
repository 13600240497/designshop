# 一、背景描述

提供接入网关系统的SDK。

# 二、技术实现

## php环境要求
- php >= 5.6

## 目录描述

    |- php-gateway
            |- src
            |   |- Client.php
            |   |- Request.php
            |   |- Response.php
            |- tests

## 安装
composer.json 增加以下代码

    {
        "repositories": [
           {
               "type": "composer",
               "url": "http://www.composer-satis.com.master.test50.egomsl.com"
           }
        ],
        "require": {
            "globalegrow/php-gateway": "^1.0"
        },
        "config": {
            "preferred-install": "dist",
            "sort-packages": true,
            "optimize-autoloader": true,
            "secure-http":false
        }
    }


## 代码示例

    include_once (dirname(__FILE__) . '/../vendor/autoload.php');

    use Globalegrow\Gateway\Client;

    $response = (new Client('http://10.4.4.203', 'gateway', '3e21ab62fb17400301d9f0156b6c3031'))
        ->send('oms.gateway_test.get', ['id' => 1]);

## 异常处理
- 请求http响应状态码不为**200**：抛出`Globalegrow\Gateway\Exceptions\RequestException`
- 返回码不为**0**：抛出`Globalegrow\Gateway\Exceptions\ResponseException`

# 三、相关链接

- [01-网关系统](http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=30476103)
- [02-接口规范](http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=31786282)
