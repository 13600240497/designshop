<div id="app">
  <app></app>
</div>

<?php
$this->registerJsFile(app()->url->assets->js('pageTemplateNative.js'), ['position' => \yii\web\View::POS_END]);
