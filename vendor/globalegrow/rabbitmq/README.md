# rabbitmq

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
        "globalegrow/rabbitmq": "^1.0"
    },
    "config": {
        "secure-http" : false
    }
}
```

## 使用示例

```php
    // config/mq/mq.dev.php
    return Globalegrow\RabbitMQ\Config::build([
        'test' => [
            'base' => [
                'host' => '10.40.6.89',
                'port' => 5672,
                'login' => 'gb_test',
                'password' => 'gb_test',
                'vhost' => 'WEB_GB'
            ],
            'queue' => [
                'routingkey' => 'routingkey',
            ],
            'connect_timeout' => 3,
            // 更多配置
        ],
        // 不需要routingkey，默认与队列名相同
        'queue_name' => [
            'base' => [
                'host' => 'host',
                'port' => 'port',
                'login' => 'username',
                'password' => 'password',
                'vhost' => 'vhost'
            ],
        ],
        // 更多队列
    ]);
```

```php
    require __DIR__ . '/../vendor/composer/autoload.php';
    $client = Globalegrow\RabbiitMQ();

    // 发送数据
    $client->send('test', $data);

    // 获取数据
    $client->receive('test', function($data, $client) {
        /** @var \xcmq\module\Xcmq_Abstract $client */
        ...
        $client->ack();
    });
```
