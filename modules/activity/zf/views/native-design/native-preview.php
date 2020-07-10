<?php echo $data['head']; ?>

<script>
    var source_data = <?php echo json_encode($data['pageData']['pageData']); ?>;
    var GESHOP_LANGUAGES = <?php echo json_encode($data['languages']); ?>;
    var GESHOP_PAGE_ID = <?php echo $data['pageId']; ?>;
    var GESHOP_SITE_CODE = '<?php echo $data['siteCode']; ?>';
    var GESHOP_PIPELINE = '<?php echo $data['pipeline']; ?>';
    var GESHOP_LANG = '<?php echo $data['lang']; ?>';
    var GESHOP_INTERFACE = <?php echo json_encode($data['interfaceConfig']);?>;
</script>

<div id="preview-app">
    <app></app>
</div>

<?php
    $this->registerJsFile(
        app()->url->assets->js('design-preview.js'),
        ['position' => \yii\web\View::POS_END]
    );
?>

<?php echo $data['footer']; ?>