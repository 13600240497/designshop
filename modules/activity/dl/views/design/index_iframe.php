<script type="text/javascript">
    var GESHOP_PAGE_TYPE = 1;
    var GESHOP_SITE_DOMAIN = "<?php echo $siteDomain;?>";
    var GESHOP_LANG = "<?php echo $lang;?>";
    var GESHOP_SITECODE = "<?php echo $siteCode;?>";
    var GESHOP_PLATFORM = "<?php echo $platform;?>";
    var GESHOP_MULTI_TIME_STYLE = "";
    var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    var GESHOP_IS_PRERELEASE = <?php echo YII_ENV === 'prerelease' ? 'true' : 'false';?>;
    var GESHOP_STATIC = "<?php echo (new app\modules\common\components\CommonPageConfig())->staticDomain;?>";
    <?php echo $jsLanguage; ?>
    <?php echo $jsAsyncData; ?>
</script>
 
<?php
$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/library/jquery.js'),
    ['position' => \yii\web\View::POS_BEGIN]
);
$this->registerJsFile(
	app()->url->assets->js('frontend/geshop/library/LAB.js'),
	['position' => \yii\web\View::POS_BEGIN]
);
?>

<link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/stylesheets/geshop-grid-responsive.css'); ?>">
<link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/library/layui/css/layui.css'); ?>">
<link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/library/colorpicker/css/colorpicker.css'); ?>">
<link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/library/jstree/themes/default/style.min.css'); ?>">
<link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/stylesheets/design.activity.dl.css'); ?>">

<main class="site-main-design" style="top: 0;">
    <div id="design_drag">
        <div class="sidebar-container" style="display: none;">
            <div class="sidebar-content">
                <h2 class="sidebar-title">基础布局
                    <i class="layui-icon back" id="js_toggleLeft">&#xe603;</i>
                </h2>
                <p class="sidebar-desc">选择所需布局， 并拖动至相应位置</p>
                <ul class="sidebar-component-container">
                    <?php if (!empty($data['layout'])) { ?>
                    <?php foreach ((array)$data['layout'] as $value) { ?>
                    <li class="design-layout-item layout-drag" data-key="<?= $value->component_key ?>" data-name="<?= $value->name ?>"
                        data-custom="<?= $value->is_custom ?>">
                        <p class="design-layout-desc">
                            <?= $value->name; ?>
                        </p>
                        <p class="design-layout-icon" style="background-image: url(<?= $value->logo_url; ?>)"></p>
                    </li>
                    <?php } ?>
                    <?php } ?>
                </ul>
            </div>
            <div class="sidebar-content hide" style="width:320px">
                <h2 class="sidebar-title">內容組件
                    <i class="layui-icon back" id="js_toggleLeft_2" style="margin-left:185px">&#xe603;</i>
                </h2>
                <p class="sidebar-desc">选择所需組件， 并拖动至相应布局中</p>
                <div class="sidebar-list-container">
                    <h3 class="sidebar-list-title" onselectstart="return false;">组件列表
                        <i class="icon"></i>
                    </h3>
                    <ul class="sidebar-component-container sidebar-component-container-big" style="max-height: 75vh">
                        <?php if (!empty($data['ui'])) { ?>
                        <?php foreach ((array)$data['ui'] as $value) { ?>
                            <li class="design-model-item component-drag" data-key="<?= $value->component_key ?>" <?php if ($value->component_key == 'U000055' || $value->component_key == 'U000179' ): ?>data-unique="true"
                            <?php endif; ?> data-name="
                            <?= $value->name ?>">
                                <p class="design-model-desc">
                                    <?= $value->name; ?>
                                </p>
                                <p class="design-model-icon" style="background-image: url(<?= $value->logo_url; ?>);"></p>
                        </li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="design-right">
        <button id="view_to_design" style="display:none" type="button" class="layui-btn layui-btn-sm layui-btn-normal">页面装修</button>
        <button id="design_to_view" style="display:none" type="button" class="layui-btn layui-btn-sm layui-btn-primary">页面布局</button>
        <div id="design_view" class="design-view layout-drop" style="margin: 36px 0 0; padding-bottom: 0;">
            <?= $pageHtml; ?>
        </div>
    </div>
</main>

<div id="component_layout_add_position_tips"></div>
<div id="component_form_dialog"></div>
<div id="design_view_loading" class="design-view-loading"></div>
<div id="geshop_drop_tip_top" class="geshop-drop-tip"><span></span></div>
<div id="geshop_drop_tip_bottom" class="geshop-drop-tip"><span></span></div>
<div id="geshop_drop_tip_center" class="geshop-drop-tip"><p></p></div>

<input id="pageId" type="hidden" value="<?= $pageId ?>">
<input id="activityId" type="hidden" value="<?= $pageInfo['activity_id']; ?>">
<input id="pageLang" type="hidden" value="<?= $lang ?>">
<input id="pageStatus" type="hidden" value="<?= $pageInfo['status'] ?>">
<input id="siteCode" type="hidden" value="<?= $siteCode ?>">
<input id="customLayoutKey" type="hidden" value="<?= $customKey ?>">
<input id="pageLanguageId" type="hidden" value="<?= $pageInfo['pageLanguages'][0]['id'] ?>">

<?php include 'dialog/layout_operation.php' ?>
<?php include 'dialog/component_operation.php' ?>
<?php include 'dialog/resource_view.php' ?>
<?php include 'dialog/copy_layout.php' ?>
<?php include 'dialog/custom_layout.php' ?>
<?php include 'dialog/page_model.php' ?>
<?php include 'dialog/page_stylesheet.php' ?>
<?php include 'dialog/page_language_generate.php' ?>
<?php include 'dialog/page_link.php' ?>
<?php include 'dialog/component_model_temp.php' ?>


<script>
    function goHome() {
        if (window.sessionStorage) {
            sessionStorage.setItem('menuIndex', '')
            sessionStorage.setItem('routeOpeneds', '')
        }
        window.location.href = '/'
    }
</script>



<?php
$this->registerJsFile(
    app()->url->assets->js('design.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/library/layui/layui.all.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/javascripts/design.responsive.js'),
    ['position' => \yii\web\View::POS_END]
);

// $this->registerJsFile(
//     app()->url->assets->js('resources/javascripts/design.activity.js'),
//     ['position' => \yii\web\View::POS_END]
// );

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/library/colorpicker/js/colorpicker.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/library/js.cookie.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/library/jstree/jstree.min.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    '//geshopcss.logsss.com/vue/vue.min.js',
    ['position' => \yii\web\View::POS_END]
);
// 本地环境，test环境引入   develop/client.bundle.js
// 预发布，正式环境引入  ${s3静态资源的域名} + /vueComponent/ + ${htdocs/resource/vue-ssr-client-manifest.json}里面的 initial 字段
$this->registerJsFile(
    app()->url->assets->clientJs('initial'),
    ['position' => \yii\web\View::POS_END]
);
