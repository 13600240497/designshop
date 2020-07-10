<?php
return array_merge(
    require(YII2_PATH . '/messages/zh-CN/yii.php'),
    [
        'I\'m going to be translated in twig' => '我是要在twig中被翻译的内容[我在module中]',
        'I\'m going to be translated in controller' => '我是要在controller中被翻译的内容[我在module中]',
    ]
);
