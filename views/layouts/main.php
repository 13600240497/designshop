<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string 字符串 */
?>
<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= Html::encode($this->context->title) ?></title>
    <link rel="stylesheet" href="<?= app()->url->assets->js('resources/stylesheets/componentStyle.css'); ?>">
    <script src="<?= app()->url->assets->js('common.js'); ?>"></script>
    <link rel="shortcut icon" href="/favicon.ico">
    <style type="text/css">
        html { height: 100%; }
        body { margin: 0; height: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .el-row { margin-bottom: 15px; }
        #app { min-height: 100%; }
        .site-container { height: 100% }
    </style>
</head>
<body>
    <?php $this->beginPage() ?>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    <?php $this->endPage() ?>
</body>
</html>
