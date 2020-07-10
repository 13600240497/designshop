<script>
    var source_data = <?php echo json_encode($pageData['pageData']); ?>;
    var GESHOP_LANGUAGES = <?php echo json_encode($languages); ?>;
    var GESHOP_PID = <?php echo $nativeId; ?>; // APP的页面ID
    var GESHOP_PAGE_ID = <?php echo $pageId; ?>;
    var GESHOP_SITE_CODE = '<?php echo $siteCode; ?>';
    var GESHOP_PIPELINE = '<?php echo $pipeline; ?>';
    var GESHOP_LANG = '<?php echo $lang; ?>';
    var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    var GESHOP_GROWINGIO = <?php echo $growingIoUiInfo; ?>; // Growing IO 组件埋点数据
</script>
<div id="release-app">
    <app></app>
</div>
<script src="https://geshopcss.logsss.com/vue/vue.min.js"></script>
<script src="<?php echo app()->url->assets->clientJs('sites', true); ?>"></script>