# php-diff
php仿svn，git对比两个字符串

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
        "globalegrow/php-diff": "^1.0"
    },
    "config": {
        "secure-http" : false
    }
}

## 预览效果
http://www.php-diff.com.master.test50.egomsl.com