<script type="text/javascript">
    var GESHOP_PAGE_TYPE = 1;
    var GESHOP_LANG = "<?php echo $lang;?>";
    var GESHOP_PIPELINE = "<?php echo $pipeline;?>";
    var GESHOP_SITECODE = "<?php echo $siteCode;?>";
    var GESHOP_PLATFORM = "<?php echo $platform;?>";
    var GESHOP_URL_SOP_ADD_RULE = '<?php echo $sopAddRuleUrl; ?>';
    var GESHOP_MULTI_TIME_STYLE = "";
    var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    var GESHOP_IS_PRERELEASE = <?php echo YII_ENV === 'prerelease' ? 'true' : 'false';?>;
    var GESHOP_STATIC = "<?php echo (new app\modules\common\components\CommonPageConfig())->staticDomain;?>";
    var GESHOP_PID = "<?php echo $pageId;?>";
    <?php echo $jsLanguage; ?>
    <?php echo $jsAsyncData; ?>
</script>
<div id="app">
    <app></app>
</div>

<link rel="stylesheet" href="<?= app()->url->assets->css('resources/ant-design-vue/custom/antd-pure.css'); ?>">

<?php

$this->registerJsFile(
    app()->url->assets->js('bundle_ant.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('designZF.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    $siteDomain. '/sitemap/currency_huilv.js?v='. date('YmdH'),
    ['position' => \yii\web\View::POS_BEGIN]
);
$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/jquery.js'),
    ['position' => \yii\web\View::POS_BEGIN]
);
$this->registerJsFile(
	app()->url->assets->js('resources/javascripts/library/LAB.js'),
	['position' => \yii\web\View::POS_BEGIN]
);
?>
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/design.activity.vue.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/common/geshop-grid-server.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/javascripts/library/swiper/swiper.min.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/layui/css/layui.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/colorpicker/css/colorpicker.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/jstree/themes/default/style.min.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/design.activity.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('frontend/geshop/stylesheets/geshop-components-default-pc.css'); ?>">

    <style>
        /* <!-- ZAFUL 字体 --> */
        @font-face {
            font-family: OpenSans-Semibold;
            src: url("<?= app()->url->assets->css('temp/skin_wap/dist/fonts/OpenSans-Semibold.woff2'); ?>");
        }
        @font-face {
            font-family: OpenSans-Regular;
            src: url("<?= app()->url->assets->css('temp/skin_wap/dist/fonts/OpenSans-Regular.woff2'); ?>");
        }

        .site-header {
            display: none;
        }

        #app {
            z-index: -999;
        }

        body {
            font-family: PingFangSC-Regular;
            font-size:14px;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        ::-webkit-scrollbar-track-piece {
            background-color: none;
            -webkit-border-radius: 0;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb:vertical {
            height: 30px;
            background-color: #6C727D;
            -webkit-border-radius: 4px;
            outline: 2px solid #fff;
        }

        ::-webkit-scrollbar-thumb:hover {
            height: 30px;
            background-color: #9f9f9f;
            -webkit-border-radius: 4px;
        }

        .design-form::-webkit-scrollbar {
            display: none;
        }

        .layui-layer-setwin .layui-layer-min cite {
            display: none;
        }

        .layui-layer-setwin .layui-layer-min {
            display:none;
        }

        .layui-layer-setwin .layui-layer-max,
        .layui-layer-setwin .layui-layer-max:hover {
            width: 24px;
            height: 24px;
            background-image: url('/resources/images/icon/enlarge.png') !important;
            background-position: 0 0;
            background-size: cover;
            margin-top: -3px;
        }

        .layui-layer-setwin .layui-layer-ico.layui-layer-max.layui-layer-maxmin,
        .layui-layer-setwin .layui-layer-ico.layui-layer-max.layui-layer-maxmin:hover {
            background-image: url('/resources/images/icon/zoom-out.png') !important;
            background-position: 0 0;
        }

        .layui-layer-dialog .layui-layer-content .layui-input-block {
            margin-left: 20px;
            margin-right: 20px;
            min-height: 26px;
        }

        .layui-layer-page .layui-layer-content .layui-input-block {
            margin-left: 40px;
            margin-right: 40px;
				}

				.layui-layer-page .layui-layer-content .layui-form-radio>i {
					margin-right: 5px;
				}

        .layui-layer-dialog .layui-form-item {
            margin-bottom: 12px;
        }

        .layui-layer-page .layui-form-item {
            margin-bottom: 0;
        }

        .layui-layer-page .layui-layer-content .layui-form-title {
            min-height: 28px;
            line-height: 28px;
        }

        .layui-layer-dialog .layui-layer-content {
            padding-top: 10px;
            overflow-y: hidden;
        }

        .layui-layer-dialog .layui-layer-content .layui-form-radio,
        .layui-layer-page .layui-layer-content .layui-form-radio {
            margin: 0px;
            padding-right: 18px;
        }

        .layui-layer-dialog .layui-layer-content .layui-form-radio>i {
            margin: 0px;
            padding-right: 5px;
        }
        .layui-layer-page .layui-layer-content .preview-img{
            margin-left: 24px;
            height: 300px;
            overflow: scroll;
        }

        .layui-layer-dialog .layui-layer-btn,
        .layui-layer-page .layui-layer-btn {
            padding-bottom: 40px;
            padding-top: 20px;
        }

        .layui-layer-dialog .layui-layer-btn a,
        .layui-layer-page .layui-layer-btn a {
            width: 98px;
            height: 38px;
            line-height: 38px;
            margin: 5px 8px 0;
            padding: 0;
            text-align: center;
        }

        .layui-layer-msg .layui-layer-content {
            padding-bottom: 10px;
                }

               .design-control .layui-unselect.layui-form-select {
            width: 200px;
        }

        .design-control .layui-unselect.layui-form-select .layui-input.layui-unselect {
            height: 60px;
            border-top: none;
            border-bottom: none;
            border-color: #E6E6E6;
        }

        .design-control .layui-unselect.layui-form-select .layui-anim.layui-anim-upbit {
            top: 60px;
        }

        .layui-nav .layui-nav-more{
            right: 24px;
        }

        .layui-nav .layui-nav-item:hover {
            background-color: #34A8FF;
        }

        .layui-layer-btn .layui-layer-btn0{
            border-color: #dedede;
            background-color: #fff;
            color: #333;
        }

        .layui-layer-btn .layui-layer-btn1{
            border-color: #1E9FFF;
            background-color: #1E9FFF;
            color: #fff;
        }

        .design-control-lang-form > * {
            display: inline-block;
            vertical-align: bottom;
        }
    </style>

    <header class="site-header-container">
        <div class="left">
            <h1>
                <a href="javascript:;" style="display:block;height:30px;" onclick="goHome()">
                    <img src="/resources/images/geshop-logo.png" style="vertical-align: top;" alt="geshop logo" />
                </a>
            </h1>
            <div class="design-control-title-box">
                <p class="design-control-title">
                    <?= $pageInfo['pageLanguages'][0]['title']; ?>
                </p>
            </div>
        </div>
        <div class="right">
            <form class="layui-form" action="">
                <span>布局视图</span>
                <input type="checkbox" name="xxx" title="布局视图" lay-skin="switch" lay-filter="switchInput">
                <button type="button" id="preview_page" class="btn" data-href="<?= $preview_url; ?>">预览</button>
                <button type="button" id="view_page" class="btn">访问</button>
                <button type="button" id="generate_site_page" class="btn active">发布</button>
                <!-- <button type="button" id="generate_sync_platform_page" class="btn active btn-deep-blue">多端绑定数据发布</button> -->
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
                    <div class="sidebar-item-title">
                        <h2 class="sidebar-title">內容組件
                            <i class="layui-icon back" id="js_toggleLeft_2" style="margin-left:185px">&#xe603;</i>
                        </h2>
                        <div class="sidebar-title-search layui-item">
                            <i class="layui-icon layui-icon-search" style="color: gray;position: absolute;left: 4px;top: 10px;width:20px;"></i>
                            <input type="text" name="componentName" class="layui-input" placeholder="组件筛选">
                        </div>
                    </div>
                    <p class="sidebar-desc">选择所需組件， 并拖动至相应布局中</p>
                    <div class="sidebar-list-container">
                        <h3 class="sidebar-list-title" onselectstart="return false;">组件列表
                            <i class="icon"></i>
                        </h3>
                        <ul class="sidebar-component-container sidebar-component-container-big" style="max-height: 75vh">
                            <?php if (!empty($data['ui'])) { ?>
                            <?php foreach ((array)$data['ui'] as $value) { ?>
                            <li class="design-model-item component-drag" draggable="true" data-key="<?= $value->component_key ?>" <?php if ($value->component_key == 'U000055'): ?>data-unique="true"
                                <?php endif; ?> data-name="<?= $value->name ?>">
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
                    <div class="sidebar-item-title">
                        <h2 class="sidebar-title">页面模板
                            <i class="layui-icon back" id="js_toggleLeft_2" style="margin-left:185px">&#xe603;</i>
                        </h2>
                        <div class="sidebar-title-search layui-item">
                            <i class="layui-icon layui-icon-search" style="color: gray;position: absolute;left: 4px;top: 10px;width:20px;"></i>
<input type="text" name="templateName" class="layui-input" placeholder="组件筛选">
                        </div>
                    </div>
                    <p class="sidebar-desc">切换所需模板后，参数会被替换。</p>
                    <div class="sidebar-list-container">
                        <h3 class="sidebar-list-title" onselectstart="return false;">我的模板
                            <i class="icon"></i>
                        </h3>
                        <ul class="sidebar-component-container sidebar-component-container-big sidebar-template-list-container" data-type="1">
                            <?php if (!empty($data['template']['private'])) { ?>
                            <?php foreach ((array)$data['template']['private'] as $value) { ?>
                            <li class="template-list-item" data-id="<?= $value['id']; ?>" data-name="<?= $value['name']; ?>">
                                <i class="layui-icon layui-icon-ok"></i>
                                <p class="title">
                                    <?= $value['name']; ?>
                                </p>
                                <img src="<?= $value['pic'] ? $value['pic'] : '/resources/images/default/picture.png'; ?>" alt="<?= $value['name']; ?>">
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
                            <li class="template-list-item" data-id="<?= $value['id']; ?>" data-name="<?= $value['name']; ?>">
                                <i class="layui-icon layui-icon-ok"></i>
                                <p class="title">
                                    <?= $value['name']; ?>
                                </p>
                                <img src="<?= $value['pic'] ? $value['pic'] : '/resources/images/default/picture.png'; ?>" alt="<?= $value['name']; ?>">
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
                            <form class="layui-form design-control-lang-form" action="##">
                                <select name="" lay-filter="selectLang">
                                    <?php foreach ($activityInfo['allLangList'] as $item) { ?>
                                        <option value="<?= \yii\helpers\Url::current(['pipeline' => $item['pipeline'], 'lang' => $item['langList'][0]['key']]); ?>" <?= $pipeline !== $item['pipeline'] ? '' : 'selected'; ?>><?= $item['pipeline_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <?php foreach ($activityInfo['allLangList'] as $outerItem): ?>
                                    <?php if ($outerItem['pipeline'] == $pipeline): ?>
                                        <?php if (count($outerItem['langList']) > 4) { ?>
                                        <select name="" lay-filter="selectLang">
                                            <?php foreach ($outerItem['langList'] as $item) { ?>
                                                <option value="<?= \yii\helpers\Url::current(['lang' => $item['key']]); ?>" <?= $lang !== $item['key'] ? '' : 'selected'; ?>><?= $item['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } else {
                                            foreach ($outerItem['langList'] as $item) {
                                        ?>
                                            <a href="<?= \yii\helpers\Url::current(['lang' => $item['key']]); ?>" class="
                                                <?= $lang !== $item['key'] ? '' : 'current'; ?>">
                                                <?= $item['name']; ?>
                                                <?php if ($item['is_default'] == 1) { ?>
                                                    <span style="color: #999;">(默认)</span>
                                                <?php } ?>
                                            </a>
                                        <?php }} ?>
                                    <?php endif; ?>
                                <?php endforeach ?>
                            </form>
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
                            <a href="javascript:;" id="sync_channel">同步其他渠道信息</a>
                            <a href="javascript:;" id="add_page_stylesheet"><i class="icon"></i>添加页面样式</a>
													<!-- <a href="javascript:;" id="multiple_plat_sync" class="icon-plus-target"><i class="icon icon-plus"></i>三端数据绑定</a> -->
													<a href="javascript:;" id="generate_page_model"><i class="icon"></i>生成模板</a>
                        </p>
                    </div>
                </div>
            </div>
            <div id="design_view" class="design-view layout-drop">
                <?= $pageHtml; ?>
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

    <div id="geshop_drop_tip_top" class="geshop-drop-tip">
        <span></span>
    </div>
    <div id="geshop_drop_tip_bottom" class="geshop-drop-tip">
        <span></span>
    </div>

    <div id="geshop_drop_tip_center" class="geshop-drop-tip">
        <p></p>
    </div>

    <input id="pageId" type="hidden" value="<?= $pageId ?>">
    <input id="activityId" type="hidden" value="<?= $pageInfo['activity_id']; ?>">
    <input id="pageLang" type="hidden" value="<?= $lang ?>">
    <input id="groupId" type="hidden" value="<?= $groupId ?>">
    <input id="pageStatus" type="hidden" value="<?= $pageInfo['status'] ?>">
    <input id="siteCode" type="hidden" value="<?= $siteCode ?>">
    <input id="customLayoutKey" type="hidden" value="<?= $customKey ?>">
    <input id="pageLanguageId" type="hidden" value="<?= $pageInfo['pageLanguages'][0]['id'] ?>">

    <script id="layout_operation_template" type="text/template">
        <div class="design-operation-panel design-layout-operation-panel">
            <span id="js_layoutName"></span>
            <a class="geshop-draggable" href="javascript:;" draggable="true" data-type="layout">
                <i class="icon"></i>
                <span>移动</span>
            </a>
            <a class="js_addLayout" href="javascript:;">
                <i class="icon"></i>
                <span>添加布局</span>
            </a>
            <a class="js_copyLayout" href="javascript:;">
                <i class="icon"></i>
                <span>复制</span>
            </a>
            <a class="js_sortUpLayout" href="javascript:;">
                <i class="icon"></i>
                <span>上移</span>
            </a>
            <a class="js_sortDownLayout" href="javascript:;">
                <i class="icon"></i>
                <span>下移</span>
            </a>
            <a class="js_openLayoutForm" href="javascript:;">
                <i class="icon"></i>
                <span>编辑</span>
            </a>
            <a class="js_pasteInEmptyLayout" href="javascript:;">
                <i class="icon"></i>
                <span>粘贴内容</span>
            </a>
            <a class="js_pasteInLayout" href="javascript:;">
                <i class="icon"></i>
                <span>粘贴布局</span>
            </a>
            <a class="js_removeLayout" href="javascript:;">
                <i class="icon"></i>
                <span>删除</span>
            </a>
        </div>
    </script>

    <script id="component_operation_template" type="text/template">
        <div class="design-operation-panel design-component-operation-panel">
            <span id="js_componentName"></span>
            <a class="geshop-draggable" href="javascript:;" draggable="true" data-type="component">
                <i class="icon"></i>
                <span>移动</span>
            </a>
            <a class="js_sortUpComponent" href="javascript:;">
                <i class="icon"></i>
                <span>上移</span>
            </a>
            <a class="js_sortDownComponent" href="javascript:;">
                <i class="icon"></i>
                <span>下移</span>
            </a>
            <a class="js_copyComponent" href="javascript:;">
                <i class="icon"></i>
                <span>复制</span>
            </a>
            <a class="js_openComponentForm" href="javascript:;">
                <i class="icon"></i>
                <span>编辑</span>
            </a>
            <a class="js_pasteInComponent" href="javascript:;">
                <i class="icon"></i>
                <span>粘贴内容</span>
            </a>
            <a class="js_removeComponent" href="javascript:;">
                <i class="icon"></i>
                <span>删除</span>
            </a>
            <a class="js_saveComponentTemp" href="javascript:;">
                <i class="icon"></i>
                <span>模板</span>
            </a>
        </div>
    </script>

    <script id="resource_view_template" type="text/template">
        <div class="geshop-resource-explorer" style="height: calc(100% - 74px);">
            <div class="layui-row" style="height: 100%;">
                <div class="layui-col-xs3 geshop-resource-tree-container">
                    <p id="empty_tip" class="geshop-resource-empty-tip">
                        <i class="el-icon-warning"></i>
                        <span>请先选择你要上传到的文件夹或新建文件夹</span>
                    </p>
                    <ul id="geshop_resource_tree"></ul>
                    <div id="resource_folder_box" class="layui-form-item" style="display: none;">
                        <div class="layui-input-block">
                            <input id="resource_folder_input" type="text" name="title" placeholder="请输入文件夹名称" class="layui-input">
                        </div>
                    </div>
                </div>
                <div class="layui-col-xs9" style="height: 100%;">
                    <div id="geshop_resoure_list" class="layui-row geshop-resource-list"></div>
                </div>
            </div>
            <div class="layui-row geshop-resource-bottom">
                <div class="layui-col-xs3 geshop-resource-folder-creation">
                    <a id="create_resource_folder" href="javascript:;" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">&#xe654;</i>新增文件夹
                    </a>
                </div>
                <div class="layui-col-xs9 geshop-resource-file-upload">
                    <a id="upload_resource_replace" href="javascript:;" class="layui-btn layui-btn-normal"><i class="layui-icon">&#xe681;</i>上传图片</a>
                    <button id="upload_resource" type="button" class="layui-btn layui-btn-normal" style="display: none;">
                        <i class="layui-icon">&#xe681;</i>上传图片
                    </button>
                </div>
            </div>
        </div>
    </script>

    <!-- 布局复制弹出框 -->
    <script id="copy_layout_template" type="text/template">
        <label class="layui-form-label">输入份数</label>
        <input type="number" id="copyNum" name="title" required lay-verify="required" placeholder="请输入份数" autocomplete="off" class="layui-input"
            min="0" max="10" value="1">
    </script>

    <script id="custom_layout_template" type="text/template">
        <form class="layui-form" action="" style="margin-top:8px">
            <div class="layui-form-item">
                <div class="layui-input-block layui-form-title">宽度(必填)</div>
                <div class="layui-input-block" style="min-height: 30px;">
                    <input type="radio" name="width" lay-filter="width" value="100%" title="100%" checked>
                    <input type="radio" name="width" lay-filter="width" value="960" title="960px">
                    <input type="radio" name="width" lay-filter="width" value="1200" title="1200px">
                    <input type="radio" name="width" lay-filter="width" value="0" title="自定义">
                </div>
            </div>
            <div data-type="width" class="layui-form-item" style="display: none;">
                <div class="layui-input-block">
                    <input type="text" name="customWidth" placeholder="请设置布局宽度值(范围960-1920px)" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block layui-form-title">列(必填)</div>
                <div class="layui-input-block">
                    <input type="radio" name="column" lay-filter="column" value="1" title="一列" checked>
                    <input type="radio" name="column" lay-filter="column" value="2" title="二列">
                    <input type="radio" name="column" lay-filter="column" value="3" title="三列">
                    <input type="radio" name="column" lay-filter="column" value="4" title="四列">
                    <input type="radio" name="column" lay-filter="column" value="0" title="自定义">
                </div>
            </div>
            <div data-type="column" class="layui-form-item" style="display: none;">
                <div class="layui-input-block">
                    <input type="text" name="customColumn" placeholder="请设置列的数量(范围1-9列)" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-input-block">
                    <input type="radio" name="type" lay-filter="type" value="1" title="均分" checked>
                    <input type="radio" name="type" lay-filter="type" value="0" title="自定义">
                </div>
            </div>
            <div data-type="type" class="layui-form-item" style="display: none;">
                <div class="layui-input-block">
                </div>
            </div>
        </form>
    </script>

    <!-- 页面模板 -->
    <script id="page_model_template" type="text/template">
        <form class="layui-form layui-field-box gs-form-valid">
            <div class="layui-form-item gs-required">
                <div class="layui-input-block layui-form-title" style="margin-left:24px">模板分类</div>
                <div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;float:left;">
                    <select name="model_type" lay-verify="required" required>
                        <option value="1">公有模板</option>
                        <option value="2">私有模板</option>
                    </select>
                </div>
                <div class="layui-form-mid layui-word-aux">注:私有模板仅自己可见</div>
            </div>
            <div class="layui-form-item gs-required">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">模板名称</div>
                <div class="layui-input-block" style="margin-left: 0px;">
                    <input type="text" name="model_name" autocomplete="off" class="layui-input" style="margin-left: 24px;width:640px" required>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">图片链接</div>
                <div class="layui-input-block" style="margin-left: 24px;">
                    <input type="text" disabled name="model_pic" autocomplete="off" class="layui-input" style="width:640px" placeholder="不可编辑，请上传图片！">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">预览图</div>
                <div class="preview-img">
                    <img src="https://geshopcss.logsss.com/imagecache/geshop/resources/images/view/zaful/loading.gif" alt="">
                </div>
            </div>
        </form>
    </script>

    <!-- 组件模板 -->
    <script id="component_model_template" type="text/template">
        <form class="layui-form layui-field-box gs-form-valid">
            <div class="layui-form-item gs-required">
                <div class="layui-input-block layui-form-title" style="margin-left:24px">模板分类</div>
                <div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;float:left;">
                    <select name="model_type" lay-verify="required" required>
                        <option value="1">公有模板</option>
                        <option value="2">私有模板</option>
                    </select>
                </div>
                <div class="layui-form-mid layui-word-aux">注:私有模板仅自己可见</div>
            </div>
            <div class="layui-form-item gs-required">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">模板名称</div>
                <div class="layui-input-block" style="margin-left: 0px;">
                    <input type="text" name="cmp_model_name" autocomplete="off" class="layui-input" style="margin-left: 24px;width:640px" required>
                </div>
            </div>
            <div class="layui-form-item layui-hide">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">图片链接</div>
                <div class="layui-input-block" style="margin-left: 24px;">
                    <input type="text" disabled name="model_pic" autocomplete="off" class="layui-input" style="width:640px" placeholder="不可编辑，请上传图片！">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">预览图</div>
                <div class="layui-input-block" style="margin-left: 24px;">
                    <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" id="upload_model_picture">
                        <i class="layui-icon">&#xe67c;</i>上传图片
                    </button>
                    <div style="font-size: 12px; color: #666;">只能上传jpeg/png文件，且不超过3M</div>
                <div id="upload_model_resource" style="font-size: 12px; color: #666;"></div>
                </div>
            </div>
        </form>
    </script>

    <script id="page_stylesheet_template" type="text/template">
        <form class="layui-form">
            <div class="layui-tab page_style_option_bar" lay-filter="page-stylesheet-settings-bar" lay-allowclose="true" lay-confirm="true" lay-confirm-msg="是否删除该页面样式？">
                <ul class="layui-tab-title" style="margin:17px 20px 12px;">
                    <li class="layui-this" lay-id="0" style="padding: 0px 16px;">通用样式</li>
                    <button class="layui-btn add-tab-btn" type="button" id="js_addTab" data-type="tabAdd"><span class="tips">按时段添加</span></button>
                </ul>
                <div class="layui-tab-content page-stylesheet-list-container">
                    <div class="layui-tab-item layui-show">
                    <div class="layui-form-item page-style-select-container">
                        <div class="layui-input-block layui-form-title" style="z-index:-2;">页面样式选择<span style="margin-left:10px;color:#9E9E9E;font-size:12px;">（注：当无任何切换的时间段时，页面样式按照通用样式设置进行展示）</span></div>
                            <div class="layui-input-block">
                                <input type="radio" name="page_style_select" lay-filter="system-custom-settings" value="1" title="系统设置" checked>
                                <input type="radio" name="page_style_select" lay-filter="system-custom-settings" value="2" title="自定义设置">
                            </div>
                        </div>

                        <div class="page-system-settings-container">
                            <div class="layui-form-item" style="margin-top: -4px;">
                                <div class="layui-input-block layui-form-title">整体页面背景颜色</div>
                                <div class="layui-input-block">
                                    <div class="color-picker-selector" data-hidden-name="page_background_color">
                                        <div></div>
                                    </div>
                                    <input type="text" class="layui-input" style="width:406px" name="page_background_color" autocomplete="off" value="">
                                </div>
                            </div>
                            <div class="layui-form-item" style="margin-top: 4px;">
                                <div class="layui-input-block layui-form-title">整体页面背景图片</div>
                                <div class="layui-input-block">
                                    <a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">
                                        <i class="layui-icon">&#xe64a;</i>
                                    </a>
                                    <input type="text" name="page_background_image" style="width:406px" autocomplete="off" class="layui-input" value="">
                                </div>
                            </div>
                            <div class="layui-form-item page-background-repeat" style="margin-top: 8px;">
                                <div class="layui-input-block layui-form-title">平铺方式</div>
                                <div class="layui-input-block">
                                    <input type="radio" name="page_background_repeat" value="no-repeat" title="不平铺">
                                    <input type="radio" name="page_background_repeat" value="repeat" title="平铺" checked>
                                    <input type="radio" name="page_background_repeat" value="repeat-x" title="横向平铺">
                                    <input type="radio" name="page_background_repeat" value="repeat-y" title="纵向平铺">
                                </div>
                            </div>
                            <div class="layui-form-item page-position-repeat" style="margin-top: -4px;">
                                <div class="layui-input-block layui-form-title">对齐方式</div>
                                <div class="layui-input-block">
                                    <!-- <input type="radio" name="page_background_position" value="top" title="向上">
                                    <input type="radio" name="page_background_position" value="right" title="向右">
                                    <input type="radio" name="page_background_position" value="bottom" title="向下">
                                    <input type="radio" name="page_background_position" value="left" title="向左">
                                    <input type="radio" name="page_background_position" value="center" title="居中" checked>
                                    <input type="radio" name="page_background_position" value="top left" title="上左">
                                    <input type="radio" name="page_background_position" value="top right" title="上右">
                                    <input type="radio" name="page_background_position" value="buttom left" title="下左">
                                    <input type="radio" name="page_background_position" value="bottom right" title="下右"> -->
                                    <a href="javascript:;" class="page-background-position" data-value="top left"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="top"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="top right"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="left"></a>
                                    <a href="javascript:;" class="page-background-position current" data-value="center"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="right"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="buttom left"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="bottom"></a>
                                    <a href="javascript:;" class="page-background-position" data-value="bottom right"></a>
                                </div>
                            </div>
                        </div>
                        <div class="page-custom-settings-container">
                            <div class="layui-form-item custom-settings-stylesheet">
                                <div class="layui-input-block layui-form-title">页面样式</div>
                                <div class="layui-input-block">
                                    <textarea name="page_custom_css" style="width:800px" placeholder="请输入样式代码" class="layui-textarea"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </script>

    <script id="page_link_template" type="text/template">
        <?php if (!empty($pageUrl)): ?>
        <a href="<?= $pageUrl ?>" target="_blank" class="layui-btn layui-btn-normal">
            <?= $langName ?>
        </a>
        <?php else: ?>
        <span>发布活动才能生成来链接</span>
        <?php endif; ?>
    </script>

    <script>
        function goHome() {
            if (window.sessionStorage) {
                sessionStorage.setItem('menuIndex', '')
                sessionStorage.setItem('routeOpeneds', '')
            }
            window.location.href = '/'
        }
    </script>

    <script id="page_pipeline_sync_template" type="text/template">
        <form class="layui-form layui-field gs-form-valid" style="margin-bottom:30px;" lay-filter="pipeline_been">
            <h4 class="gs-label-title_normal">被同步的页面信息</h4>
            <div class="layui-form-item-box">
                <div class="layui-form-item layui-form-inline gs-required">
                    <div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">被同步的渠道</div>
                    <div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;">
                        <select name="pipeline_selected" lay-verify="required" lay-filter="pipeline_selected" required></select>
                    </div>
                </div>
                <div class="layui-form-item layui-form-inline gs-required">
                    <div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">被同步的语言页面</div>
                    <div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;">
                            <select name="lang_list" lay-verify="required" lay-filter="lang_list" required>
                            </select>
                    </div>

                </div>
            </div>

            <div class="layui-form-item gs-required">
                <div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">同步内容</div>
                <div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;">
                    <input type="radio" name="pipeline_type" value="1" title="页面" checked>
                    <input type="radio" name="pipeline_type" value="3" title="商品数据">
                </div>
            </div>

            <!-- <h4 class="gs-label-title_normal">同步到的页面信息</h4> -->
            <div class="layui-form-item gs-required">
                <div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">同步到的页面信息</div>
            </div>
            <div class="layui-form gs-required">
                <!-- <div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">同步到的渠道站和语言页面</div> -->
                <div class="layui-input-block" style="width: 90%;display: inline-block;margin-left: 24px; margin-right: 10px;">
                        <div class="layui-form-item pipeline-item gs-required">
                            <div class="gs-from-label pipeline-label" style="width:60px;">
                                <p class="item1 gs-form-label">渠道</p><p class="item2 gs-form-label">语言</p>
                            </div>
                                <div class="channel-container">
                                    <div class="swiper-wrapper"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                    <div class="layui-tab-content"></div>
                                </div>
                        </div>
                </div>
            </div>
            <blockquote class="layui-elem-quote" style="margin-left: 24px; margin-right: 24px;">温馨提示：勾选后会覆盖掉所选渠道已有数据信息且不可恢复，请谨慎勾选！</blockquote>
        </form>
    </script>

    <script id="page_pipleline_generate_template" type="text/template">
        <form class="layui-form" id="page_channel_form">
            <div class="layui-form-item">
                <label class="layui-form-label">渠道: </label>
                <div class="layui-input-block">
                    <?php foreach ($activityInfo['allLangList'] as $item) { ?>
                    <input type="checkbox" name="generate_pipeline_site" data-pipeline="<?= $item['pipeline']; ?>" title="<?= $item['pipeline_name']; ?>" lay-skin="primary" value="<?= $item['pipeline']; ?>_<?= $item['langList'][0]['key'] ?>">
                    <?php } ?>
                </div>
            </div>
        </form>
    </script>

<?php

$this->registerJsFile(
    app()->url->assets->js('resources/layui/layui.all.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/swiper/swiper.4.4.1.min.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/design.activity.site.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/common/design.plus.min.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/sitesPublic/default/js/gs_common_activity.min.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/colorpicker/js/colorpicker.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/js.cookie.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/jstree/jstree.min.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/gs_laytpl.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/colorpicker/js/colorpicker.v2.js'),
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
