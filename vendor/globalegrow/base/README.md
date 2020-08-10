# 基础组件，常用助手库
提供统一的基础操作，如运行环境组件，ip组件，字符串操作等

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
        "globalegrow/base": "^1.0"
    },
    "config": {
        "secure-http" : false
    }
}
```
