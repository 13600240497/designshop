<div id="app">
  <app></app>
</div>


<link rel="stylesheet" href="<?= app()->url->assets->css('resources/layui/css/layui.css'); ?>">
<link rel="stylesheet" href="<?= app()->url->assets->css('resources/template/goods/goodsUnit.css'); ?>">
<?php
$this->registerJsFile(app()->url->assets->js('goodManage.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(app()->url->assets->js('resources/javascripts/library/jquery.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(app()->url->assets->js('resources/layui/layui.all.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(app()->url->assets->js('resources/template/goods/index_server.js'), ['position' => \yii\web\View::POS_END]);
?>


