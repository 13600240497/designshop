<div id="app">
  <app></app>
</div>

<?php
$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/common/utils.js'),
    ['position' => \yii\web\View::POS_END]
);
$this->registerJsFile(app()->url->assets->js('role.js'), ['position' => \yii\web\View::POS_END]);
