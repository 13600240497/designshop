<link rel="stylesheet" href="//at.alicdn.com/t/font_1246787_bd98d28ay7s.css">
<script>
    <?php if (isset($interfaceConfig)) { ?>
        var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    <?php } ?>
    <?php if (isset($sopAddRuleUrl)) { ?>
        var GESHOP_URL_SOP_ADD_RULE = '<?php echo $sopAddRuleUrl; ?>';
    <?php } ?>
</script>
<div id="app">
    <app></app>
</div>

<script src="//cdn.staticfile.org/jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo app()->url->assets->js('bundle_ant.js'); ?>"></script>
<script src="<?php echo app()->url->assets->js('main-entry.js'); ?>"></script>
