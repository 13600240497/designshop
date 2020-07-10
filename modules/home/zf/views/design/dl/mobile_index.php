<script type="text/javascript">
    var GESHOP_LANG = "<?php echo $lang;?>";
    var GESHOP_SITECODE = "<?php echo $siteCode;?>";
    var GESHOP_HOME_PAGE_STATUS = "<?php echo $groupHomePageStatus;?>";
    var GESHOP_PLATFORM = "<?php echo $platform;?>";
    var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    var GESHOP_IS_PRERELEASE = <?php echo YII_ENV === 'prerelease' ? 'true' : 'false';?>;
    var GESHOP_STATIC = "<?php echo (new app\modules\common\components\CommonPageConfig())->staticDomain;?>";
    <?php echo $jsLanguage; ?>
</script>
<div id="app">
    <app></app>
</div>

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

<script>
    document.getElementsByTagName("html")[0].style.fontSize = '37.5px'
</script>

<header class="site-header-container">
    <div class="left">
        <h1>
            <a href="javascript:;" style="display:block;height:30px;" onclick="goHome()">
                <img src="/frontend/geshop/images/geshop-logo.png" style="vertical-align: top;" alt="geshop logo" />
            </a>
        </h1>
        <div class="design-control-title-box">
            <p class="design-control-title"><?= $pageInfo['pageLanguages'][0]['title']; ?></p>
        </div>
    </div>
    <div class="right">
        <form class="layui-form" action="">
            <span>布局视图</span>
            <input type="checkbox" name="xxx" title="布局视图" lay-skin="switch" lay-filter="switchInput">
            <button type="button" id="preview_page" class="btn" data-href="<?= $preview_url; ?>">预览</button>
            <button type="button" id="view_page" class="btn">访问</button>
            <button type="button" id="generate_page" class="btn active">发布</button>
        </form>
    </div>
</header>

<main class="site-main-design">
    <div id="design_drag" class="design-left-box design-left-box-visible">
        <div class="design-left">
            <div class="sidebar-btn current">
                <i class="icon"></i>
                <p>布局</p>
            </div>
            <div class="sidebar-btn">
                <i class="icon"></i>
                <p>组件</p>
            </div>
            <div class="sidebar-btn select-template-btn">
                <i class="icon"></i>
                <p>页面模板</p>
            </div>
            <div class="sidebar-btn select-template-btn">
                <i class="icon"></i>
                <p>组件模板</p>
            </div>
        </div>
        <div class="sidebar-container">
            <div class="sidebar-content">
                <h2 class="sidebar-title">基础布局
                    <i class="layui-icon back" id="js_toggleLeft">&#xe603;</i>
                </h2>
                <p class="sidebar-desc">选择所需布局， 并拖动至相应位置</p>
                <ul class="sidebar-component-container">
                    <?php if (!empty($data['layout'])) { ?>
                    <?php foreach ((array)$data['layout'] as $value) { ?>
                    <li class="design-layout-item layout-drag" draggable="true" data-key="<?= $value->component_key ?>" data-name="<?= $value->name ?>"
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
                        <li class="design-model-item component-drag" draggable="true" data-key="<?= $value->component_key ?>"
                        <?php if ($value->component_key == 'U000056' || $value->component_key == 'U000180'): ?>data-unique="true"<?php endif; ?>
                            data-name="<?= $value->name ?>">
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
            <!-- 页面模板 start -->
            <div class="sidebar-content hide" style="width:320px;">
                <h2 class="sidebar-title">页面模板
                    <i class="layui-icon back" id="js_toggleLeft_2" style="margin-left:185px">&#xe603;</i>
                </h2>
                <p class="sidebar-desc">切换所需模板后，参数会被替换。</p>
                <div class="sidebar-list-container">
                    <h3 class="sidebar-list-title" onselectstart="return false;">我的模板
                        <i class="icon"></i>
                    </h3>
                    <ul class="sidebar-component-container sidebar-component-container-big sidebar-template-list-container" data-type="1">
                        <?php if (!empty($data['template']['private'])) { ?>
                        <?php foreach ((array)$data['template']['private'] as $value) { ?>
                        <li class="template-list-item" data-id="<?= $value['id']; ?>">
                            <i class="layui-icon layui-icon-ok"></i>
                            <p class="title">
                                <?= $value['name']; ?>
                            </p>
                            <img src="<?= $value['pic'] ? $value['pic'] : '/frontend/geshop/images/default/picture.png'; ?>" alt="<?= $value['name']; ?>">
                        </li>
                        <?php } ?>
                        <?php } else { ?>
                        <p style="padding:20px;color:#fff;">暂无内容。</p>
                        <?php } ?>
                    </ul>
                </div>
                <div class="sidebar-list-container">
                    <h3 class="sidebar-list-title open" onselectstart="return false;">共用模板
                        <i class="icon"></i>
                    </h3>
                    <ul class="sidebar-component-container sidebar-component-container-big sidebar-template-list-container" style="display:none"
                        data-type="2">
                        <?php if (!empty($data['template']['public'])) { ?>
                        <?php foreach ((array)$data['template']['public'] as $value) { ?>
                        <li class="template-list-item" data-id="<?= $value['id']; ?>">
                            <i class="layui-icon layui-icon-ok"></i>
                            <p class="title">
                                <?= $value['name']; ?>
                            </p>
                            <img src="<?= $value['pic'] ? $value['pic'] : '/frontend/geshop/images/default/picture.png'; ?>" alt="<?= $value['name']; ?>">
                        </li>
                        <?php } ?>
                        <?php } else { ?>
                        <p style="padding:20px;color:#fff;">暂无内容。</p>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <!-- 页面模板 end -->
            <!-- 组件模板 start -->
            <div class="sidebar-content hide" style="width:320px;overflow-y: auto;">
                <h2 class="sidebar-title">组件模板
                    <i class="layui-icon back" id="js_toggleLeft_2" style="margin-left:185px">&#xe603;</i>
                </h2>
                <div class="sidebar-list-container">
                    <h3 class="sidebar-list-title" onselectstart="return false;">我的模板
                        <i class="icon"></i>
                    </h3>
                    <div class="sidebar-child-list-container" style="display:block">
                    <?php if (!empty($uiTplList['private'])) { ?>
                    <?php foreach ((array)$uiTplList['private'] as $item) { ?>
                    <h3 class="sidebar-child-list-title open" onselectstart="return false;"><?= $item['name'] ?>
                        <i class="icon"></i>
                    </h3>
                    <ul class="sidebar-component-container sidebar-component-container-big sidebar-template-list-container" data-type="2" style="display:none">
                        <?php if (!empty($item['tpl_list'])) { ?>
                        <?php foreach ((array)$item['tpl_list'] as $value) { ?>
                        <li class="component-list-item component-drag" data-key="<?= $value['ui_key']; ?>" data-tplid="<?= $value['id']; ?>" data-ctype="cTmp" draggable="true">
                            <i class="layui-icon layui-icon-ok"></i>
                            <p class="title">
                                <?= $value['name']; ?>
                            </p>
                            <p class="image" style="background-image:url(<?= $value['pic_url'] ? $value['pic_url'] : '/resources/images/default/picture.png'; ?>)"></p>
                        </li>
                        <?php } ?>
                        <?php } else { ?>
                        <p style="padding:20px;color:#fff;">无模板数据。</p>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                    <?php } else { ?>
                    <p style="padding:20px;color:#fff;">无模板数据。</p>
                    <?php } ?>
                    </div>
                </div>

                <div class="sidebar-list-container">
                    <h3 class="sidebar-list-title open" onselectstart="return false;">共用模板
                        <i class="icon"></i>
                    </h3>
                    <div class="sidebar-child-list-container">
                        <?php if (!empty($uiTplList['public'])) { ?>
                        <?php foreach ((array)$uiTplList['public'] as $item) { ?>
                        <h3 class="sidebar-child-list-title open" onselectstart="return false;"><?= $item['name'] ?>
                            <i class="icon"></i>
                        </h3>
                        <ul class="sidebar-component-container sidebar-component-container-big sidebar-template-list-container" data-type="2" style="display:none">
                            <?php if (!empty($item['tpl_list'])) { ?>
                            <?php foreach ((array)$item['tpl_list'] as $value) { ?>
                            <li class="component-list-item component-drag" data-key="<?= $value['ui_key']; ?>" data-tplid="<?= $value['id']; ?>" data-ctype="cTmp" draggable="true">
                                <i class="layui-icon layui-icon-ok"></i>
                                <p class="title">
                                    <?= $value['name']; ?>
                                </p>
                                <p class="image" style="background-image:url(<?= $value['pic_url'] ? $value['pic_url'] : '/resources/images/default/picture.png'; ?>)"></p>
                            </li>
                            <?php } ?>
                            <?php } else { ?>
                            <p style="padding:20px;color:#fff;">无模板数据。</p>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                        <?php } else { ?>
                        <p style="padding:20px;color:#fff;">无模板数据。</p>
                        <?php } ?>
                    </div>

                </div>
            </div>
            <!-- 组件模板 end -->
        </div>
        <div class="template-item-select-container">
            <button type="button" class="layui-btn layui-btn-primary js_tmpCancelSelect">取消</button>
            <button type="button" class="layui-btn layui-btn-normal js_tmpConfirmSelect">确定</button>
        </div>
    </div>
    <div class="design-right">
        <div class="el-row design-control">
            <div class="el-col el-col-24">
                <button id="view_to_design" style="display:none" type="button" class="layui-btn layui-btn-sm layui-btn-normal">页面装修</button>
                <button id="design_to_view" style="display:none" type="button" class="layui-btn layui-btn-sm layui-btn-primary">页面布局</button>
            </div>
            <div>
                <div class="el-col el-col-12">
                    <div class="design-control-lang">
                        <?php if (count($pageInfo['pageLanguages']) > 4) { ?>
                            <form class="layui-form" action="##">
                                <select name="" lay-filter="selectLang">
                                    <?php foreach ($pageInfo['pageLanguages'] as $item) { ?>
                                        <option value="<?= \yii\helpers\Url::current(['lang' => $item['lang']]); ?>" <?= $lang !== $item['lang'] ? '' : 'selected'; ?>><?= $item['lang_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        <?php } else {
                            foreach ($pageInfo['pageLanguages'] as $item) {
                                ?>
                                <a href="<?= \yii\helpers\Url::current(['lang' => $item['lang']]); ?>" class="
                        <?= $lang !== $item['lang'] ? '' : 'current'; ?>">
                                    <?= $item['lang_name']; ?>
                                </a>
                            <?php }} ?>
                    </div>
                </div>
                <div class="el-col el-col-12">
                    <?php /** @var array[] $relations */
                        if (count($relations['list']) > 1): ?>
                        <div class="layui-input-inline" style="float:right;margin-left:40px">
                            <select id="view_convert_page" lay-filter="viewConvertPage" class="layui-btn-sm" style="height:30px;" lay-ignore>
                                <?php foreach ($relations['list'] as $key => $relation) :?>
                                    <option <?php if ($key === $relations['current']) {echo 'selected';}?>
                                        value="<?php echo $relation['url'];?>"
                                        data-site="<?php echo $relation['siteCode'];?>"
                                    ><?php echo $relation['name'];?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    <?php endif;?>
                    <p class="design-control-option">
                        <a href="javascript:;" id="add_page_stylesheet"><i class="icon"></i>添加页面样式</a>
                        <a href="javascript:;" id="generate_page_model"><i class="icon"></i>生成模板</a>
                        <?php if ($hasEn && $lang !== $defaultLang): ?>
                        <a href="javascript:;" id="sync_config"><i class="icon"></i>同步英文版配置</a>
                        <a href="javascript:;" id="sync_goodsList"><i class="icon"></i>同步sku</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div id="design_view" class="design-view" style="margin: 72px auto 0; border: 0; width: 387px; padding-bottom: 10px;">
            <iframe id="design_view_iframe" src="<?= \yii\helpers\Url::current(['iframe' => 1]); ?>" width="100%" height="100%" frameborder="0"></iframe>
        </div>
        <div class="design-form design-form-layout">
            <form id="layout_form" class="layui-form"></form>
            <a href="javascript:;" class="design-form-close js_closeDesignForm">
                <i class="el-icon-close"></i>
            </a>
        </div>
        <div class="design-form design-form-component">
            <form id="component_form" class="layui-form"></form>
            <a href="javascript:;" class="design-form-close js_closeDesignForm">
                <i class="el-icon-close"></i>
            </a>
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
    app()->url->assets->js('frontend/geshop/library/colorpicker/js/colorpicker.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('frontend/geshop/javascripts/design.home.js'),
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
