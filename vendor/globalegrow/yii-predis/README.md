# php-preids
yii2框架使用[predis](https://github.com/nrk/predis)包，实现sentinel，cluster等

## 运行环境要求
* php >= 5.6

## 安装

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
        "globalegrow/yii-predis": "^1.0"
    },
    "config": {
        "secure-http" : false
    }
}
```

配置文件**web.php**

```php
return [
    'components' =>
        'redis' => [
            'class' => Globalegrow\YiiPredis\Connection::class,
            'parameters' => [
                [
                    'host' => '192.168.6.176',
                    'port' => 26390,
                ],
                [
                    'host' => '192.168.6.176',
                    'port' => 26391,
                ],
                [
                    'host' => '192.168.6.176',
                    'port' => 26392,
                ],
            ],
            'options' => [
                'replication' => 'sentinel',
                'service' => 'sentinel-192.168.6.176-26388',
            ],
        ]
    ]
    ...
```
