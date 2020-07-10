<script type="text/javascript">
    var GESHOP_PLOATFORMS = <?php echo json_encode($platforms);?>;
</script>
<div id="app">
    <app></app>
</div>
<?php
$this->registerJsFile(app()->url->assets->js('homepageDL.js'), ['position' => \yii\web\View::POS_END]);
