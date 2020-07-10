<div id="app">
  <app></app>
</div>

<?php
$this->registerJsFile(app()->url->assets->js('homepagestatistics.js'), ['position' => \yii\web\View::POS_END]);
