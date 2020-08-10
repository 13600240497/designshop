# php性能分析

## 运行环境要求
* php >= 5.6

## 安装
- php7需要安装[xhprof](https://github.com/longxinH/xhprof)扩展
- php5.6需要安装[uprofiler](https://github.com/FriendsOfPHP/uprofiler)
- 性能文件保存在`dirname(ini_get('error_log')) . '/profile.dat'`
- 调试环境下，将通过curl数据实时提交到**http://www.rum.com.master.test50.egomsl.com/test/index/save-php-profile**
- 其它环境下，需要运维安装[filebeat](https://www.elastic.co/downloads/beats/filebeat)，收集到性能分析redis中
- 线上通过猫头鹰登录RUM系统，其它环境则是直接访问: http://www.rum.com.master.test50.egomsl.com

在**composer.json**中增加以下代码：
```js
{
    "repositories": [
       {
           "type": "composer",
           "url": "http://www.composer-satis.com.master.test50.egomsl.com"
       }
    ],
    "require": {
        "globalegrow/php-profile": "^1.0"
    },
    "config": {
        "secure-http" : false
    }
}
```

入口文件

```php
    require __DIR__ . '/../vendor/composer/autoload.php';
    new (Globalegrow\PhpProfile($site, $isDebug))->start();
    
    ...
```

* `$site`为站点编码，具体编码联系**马善灵**
* `$isDebug`是否为调试环境
* 默认情况下，页面执行时间大于0.01秒才会进行性能数据收集，同时只会收集执行大于0.01秒的函数，
  数据采样率：
  - 开发环境：100%
  - 线上环境，1/1000
  - 测试环境，1/10
