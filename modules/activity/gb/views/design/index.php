<script type="text/javascript">
    var GESHOP_LANG = "<?php echo $lang;?>";
    var GESHOP_SITECODE = "<?php echo $siteCode;?>";
    var GESHOP_PLATFORM = "<?php echo $platform;?>";
    var GESHOP_ISEDITENV = <?php echo $isEditEnv;?>;
    var GESHOP_INTERFACE = <?php echo json_encode($interfaceConfig);?>;
    <?php echo $uiComponentJsMessage;?>
</script>
<div id="app">
    <app></app>
</div>

<?php
$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/jquery.js'),
    ['position' => \yii\web\View::POS_BEGIN]
);
$this->registerJsFile(
	app()->url->assets->js('resources/javascripts/library/LAB.js'),
	['position' => \yii\web\View::POS_BEGIN]
);

/* GB */
if (0 === substr_compare($siteCode, 'gb-', 0, strlen('gb-'))) {
    $this->registerJsFile(
        app()->url->assets->js('resources/sitesPublic/gb-pc/js/gs_common_activity.js'),
        ['position' => \yii\web\View::POS_BEGIN]
		);
		echo '<link rel="stylesheet" href="'. app()->url->assets->css('resources/sites/gb/css/icon.css') .'">';
		echo '<link rel="stylesheet" href="'. app()->url->assets->css('resources/fonts/font-gb/iconfont.css') .'">';
}

?>

    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/common/geshop-grid-server.css'); ?>">
		<link rel="stylesheet" href="<?= app()->url->assets->css('resources/javascripts/library/swiper/swiper.min.css'); ?>">
		<link rel="stylesheet" href="<?= app()->url->assets->css('resources/layui/css/layui.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/colorpicker/css/colorpicker.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/jstree/themes/default/style.min.css'); ?>">
    <link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/design.activity.css'); ?>">

    <style>
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

        .layui-layer-page .layui-layer-content .preview-img{
            margin-left: 24px;
            height: 300px;
            overflow: scroll;
        }

        .layui-layer-dialog .layui-layer-content .layui-form-radio>i {
            margin: 0px;
            padding-right: 5px;
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
        <?php if($customCss){
           echo $customCss;
       }  ?>
    </style>

    <header class="site-header-container site-gb-header-container">
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
        <div class="right" style="min-width:880px;">
            <form class="layui-form" action="">
								<div class="gb-sync-group gb-btn-group">
									<a href="javascript:void" id="gb_sync_channel">同步其他渠道所有数据</a>
									<a href="javascript:void" id="gb_sync_channel_goods">同步其他渠道商品数据信息</a>
								</div>
                <span>布局视图</span>
                <input type="checkbox" name="xxx" title="布局视图" lay-skin="switch" lay-filter="switchInput">
                <button type="button" id="preview_page" class="btn" data-href="<?= $preview_url; ?>">预览</button>
                <button type="button" id="view_page" class="btn">访问</button>
                <button type="button" id="generate_page" class="btn active">发布</button>
            </form>
        </div>
    </header>
    <main class="site-main-design gb-main-design">
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
                    <p>模板</p>
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
                            <li class="design-model-item component-drag" draggable="true" data-key="<?= $value->component_key ?>" <?php if ($value->component_key == 'U000055'): ?>data-unique="true"
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
                            <li class="template-list-item" data-id="<?= $value['id']; ?>">
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
                    <div class="el-col el-col-8">
                        <div class="design-control-lang">
												<form class="layui-form gb-design-lang" action="##">
														<select name="" class="gb_selectLang" lay-filter="gb_selectLang">
																<?php foreach ($activityInfo['langList'] as $item) { ?>
																<option value="<?= $item['key'] ?>" data-channel="<?= $item['key']?>" <?= $pipeline !== $item['key'] ? '' : 'selected'; ?>><?= $item['name']; ?></option>
																<?php } ?>
														</select>
														<ul class="langList-content gb-line-inline">
																<?php
															foreach ($activityInfo['langList'] as $key => $item) { ?>
																<li <?php echo ($pipeline == $item['key']) ? 'class="current"' : ''; ?>>

																	<?php if(count($item['langList'])>2){?>
																			<select name="" lay-filter="gb_selectChildLang">
																				<?php foreach ($item['langList'] as $childItem) { ?>
																				<option value="<?= \yii\helpers\Url::current(['pipeline' => $item['key'],'lang' => $childItem['key']]); ?>"  data-channel="<?= $childItem['key']?>" <?= $lang !== $childItem['key'] ? '' : 'selected'; ?>><?= $childItem['name']; ?></option>
																				<?php } ?>
																			</select>
																	<?php } else { ?>
																	<?php foreach ($item['langList'] as $childItem) { ?>
																		<a href="<?= \yii\helpers\Url::current(['pipeline' => $item['key'],'lang' => $childItem['key']]); ?>" class="
																		<?= $lang !== $childItem['key'] ? '' : 'current'; ?>">
																				<?= $childItem['name']; ?>
																		</a>

																	<?php }?>

																</li>
															<?php }}?>
																</ul>
                          </form>
                        </div>
                    </div>
                    <div class="el-col el-col-16">
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
                            <a href="javascript:;" id="edit-page-stylesheet">
                                <i class="icon"></i>商品类组件样式设置</a>
                            <a href="javascript:;" id="add_goods_serviceStandard">
                                <i class="icon"></i>添加商品服务标</a>
                            <a href="javascript:;" id="add_page_stylesheet">
                                <i class="icon"></i>添加页面样式</a>
                            <a href="javascript:;" id="generate_page_model">
                                <i class="icon"></i>生成模板</a>
                            <?php if ($lang !== $defaultLang): ?>
                            <a href="javascript:;" id="gb_sync_config" data-lang="<?=$defaultLangName ?>">
                                <i class="icon"></i>同步<?=$defaultLangName ?>版配置</a>
														<a href="javascript:;" id="gb_sync_goodsList" data-lang="<?=$defaultLangName ?>">
																<i class="icon"></i>同步sku</a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div id="geshop-page-content">
							<div id="design_view" class="design-view layout-drop">
											<?= $pageHtml; ?>
							</div>
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
    <input id="siteCode" type="hidden" value="<?= $siteCode ?>">
    <input id="customLayoutKey" type="hidden" value="<?= $customKey ?>">
    <input id="group_id" type="hidden" value="<?= $group_id ?>">
    <input id="defaultLang" type="hidden" value="<?=$defaultLang ?>" >
    <input id="shareChannel" type="hidden" value="<?=$shareChannel ?>" >
    <input id="pageLanguageId" type="hidden" value="<?= $pageInfo['pageLanguages'][0]['id'] ?>">
    <!-- 子页面类型,gb,gbad（活动页，落地页） -->
    <input id="gb_page_type" type="hidden" value="gb">

    <!-- 添加商品服务标数据渲染 -->
    <script id="goods-service-tag-lay-template" type="text/html">
        <div class="goods-service-tag-container">
            <h3 class="title">服务标设置（建议上传高为20px的图片）</h3>
            {{#  layui.each(d, function(index, item){ }}
            <div class="layui-card goods-service-tag-card">
            <div class="layui-card-header"><span style="font-weight:bold;">服务标组合<span class="service-index">{{index+1}}</span>设置</span>（注：一个商品最多可以展示三个服务标，填写不足三个时，按实际服务标数量展示，显示的顺序按照填写的顺序）</div>
            <div class="layui-card-body ">
                <ul>
                    {{#  layui.each(item.data_config, function(i, it){ }}
                    <li>
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">文案</label>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input type="text" name="" autocomplete="off" class="layui-input text" value="{{ it.text }}">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">背景颜色</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <div class="color-picker-selector" data-hidden-name="service_standard_bgC">
                                    <div style="background-color:{{ it.bg_color }}"></div>
                                </div>
                                <input type="text" class="layui-input bg_color" name="service_standard_bgC" autocomplete="off" value="{{ it.bg_color }}">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">文案颜色</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <div class="color-picker-selector" data-hidden-name="service_standard_color">
                                    <div style="background-color:{{ it.text_color }}"></div>
                                </div>
                                <input type="text" class="layui-input text_color" name="service_standard_color" autocomplete="off" value="{{ it.text_color }}">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">图片</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">
                                    <i class="layui-icon">&#xe64a;</i>
                                </a>
                                <input type="text" name="service_standard_bgImg_{{ index + 1 }}_{{ i + 1 }}" autocomplete="off" class="layui-input image-input pic_url" value="{{ it.pic_url }}">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">链接</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                    <input type="text" name="" autocomplete="off" class="layui-input link_url" value="{{ it.link_url }}">
                                </div>
                            </div>
                        </div>
                    </li>
                    {{#  }); }}
                </ul>
                <div class="layui-form-item layui-form-text textarea-form-item">
                    <label class="layui-form-label">商品SKU</label>
                    <div class="layui-input-block">
                    <textarea name="goodsSKU" placeholder="请输入服务标商品SKU" required lay-verify="required" class="layui-textarea goodsSKU" value="">{{item.sku_list}}</textarea>
                    </div>
                </div>
            </div>
        </div>
        {{#  }); }}
        <button class="layui-btn layui-btn-normal add_service_standard_btn" style="float:right;">增加商品服务标组合</button>
        </div>
    </script>

    <!-- 添加商品服务标 -->
    <script id="goods-service-tag-template" type="text/template">
        <div class="goods-service-tag-container">
            <h3 class="title">服务标设置（建议上传高为20px的图片）</h3>
            <div class="layui-card goods-service-tag-card">
                <div class="layui-card-header"><span style="font-weight:bold;">服务标组合<span class="service-index">1</span>设置</span>（注：一个商品最多可以展示三个服务标，填写不足三个时，按实际服务标数量展示，显示的顺序按照填写的顺序）</div>
                <div class="layui-card-body ">
                    <ul>
                        <li>
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">文案</label>
                                    <div class="layui-input-inline" style="width: 100px;">
                                        <input type="text" name="" autocomplete="off" class="layui-input text">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">背景颜色</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <div class="color-picker-selector" data-hidden-name="service_standard_bgC">
                                        <div style="background-color:#451ca9"></div>
                                    </div>
                                    <input type="text" class="layui-input bg_color" name="service_standard_bgC" autocomplete="off" value="#451ca9">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">文案颜色</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <div class="color-picker-selector" data-hidden-name="service_standard_color">
                                        <div style="background-color:#FFFFFF"></div>
                                    </div>
                                    <input type="text" class="layui-input text_color" name="service_standard_color" autocomplete="off" value="#FFFFFF">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">图片</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">
                                        <i class="layui-icon">&#xe64a;</i>
                                    </a>
                                    <input type="text" name="service_standard_bgImg_1_1" autocomplete="off" class="layui-input image-input pic_url" value="">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">链接</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                        <input type="text" name="" autocomplete="off" class="layui-input link_url">
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                        <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">文案</label>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input type="text" name="" autocomplete="off" class="layui-input text">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">背景颜色</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <div class="color-picker-selector" data-hidden-name="service_standard_bg">
                                    <div style="background-color:#451ca9"></div>
                                </div>
                                <input type="text" class="layui-input bg_color" name="service_standard_bg" autocomplete="off" value="#451ca9">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">文案颜色</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <div class="color-picker-selector" data-hidden-name="service_standard_color">
                                    <div style="background-color:#FFFFFF"></div>
                                </div>
                                <input type="text" class="layui-input text_color" name="service_standard_color" autocomplete="off" value="#FFFFFF">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">图片</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                <a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">
                                    <i class="layui-icon">&#xe64a;</i>
                                </a>
                                <input type="text" name="service_standard_bgImg_1_2" autocomplete="off" class="layui-input image-input pic_url" value="">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">链接</label>
                                <div class="layui-input-inline" style="width: 125px;">
                                    <input type="text" name="" autocomplete="off" class="layui-input link_url">
                                </div>
                            </div>
                        </li>
                        <li>
                        <div class="layui-form-item">
                        <div class="layui-inline">
                                <label class="layui-form-label">文案</label>
                                    <div class="layui-input-inline" style="width: 100px;">
                                        <input type="text" name="" autocomplete="off" class="layui-input text">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">背景颜色</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <div class="color-picker-selector" data-hidden-name="service_standard_bg">
                                        <div style="background-color:#451ca9"></div>
                                    </div>
                                    <input type="text" class="layui-input bg_color" name="service_standard_bg" autocomplete="off" value="#451ca9">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">文案颜色</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <div class="color-picker-selector" data-hidden-name="service_standard_color">
                                        <div style="background-color:#FFFFFF"></div>
                                    </div>
                                    <input type="text" class="layui-input text_color" name="service_standard_color" autocomplete="off" value="#FFFFFF">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">图片</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                    <a href="javascript:;" class="js_openResource design-open-resource" data-type="tabBgImg">
                                        <i class="layui-icon">&#xe64a;</i>
                                    </a>
                                    <input type="text" name="service_standard_bgImg_1_3" autocomplete="off" class="layui-input image-input pic_url" value="">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">链接</label>
                                    <div class="layui-input-inline" style="width: 125px;">
                                        <input type="text" name="" autocomplete="off" class="layui-input link_url">
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="layui-form-item layui-form-text textarea-form-item">
                        <label class="layui-form-label">商品SKU</label>
                        <div class="layui-input-block">
                        <textarea name="goodsSKU" placeholder="请输入服务标商品SKU" required lay-verify="required" class="layui-textarea goodsSKU"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <button class="layui-btn layui-btn-normal add_service_standard_btn" style="float:right;">增加商品服务标组合</button>
        </div>
    </script>

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
                <!-- <label class="layui-form-label">宽度(必填)</label> -->
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
                <!-- <label class="layui-form-label">列(必填)</label> -->
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

    <script id="page_model_template" type="text/template">
        <form class="layui-form layui-field-box gs-form-valid">
            <div class="layui-form-item gs-required">
                <!-- <label class="layui-form-label">模板分类</label> -->
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
                <!-- <label class="layui-form-label">模板名称</label> -->
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">模板名称</div>
                <div class="layui-input-block" style="margin-left: 0px;">
                    <input type="text" name="model_name" autocomplete="off" class="layui-input" style="margin-left: 24px;width:640px" required>
                </div>
            </div>
            <div class="layui-form-item">
                <!-- <label class="layui-form-label">图片链接</label> -->
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">图片链接</div>
                <div class="layui-input-block" style="margin-left: 24px;">
                    <input type="text" disabled name="model_pic" autocomplete="off" class="layui-input" style="width:640px" placeholder="不可编辑，请上传图片！">
                </div>
            </div>
            <div class="layui-form-item">
                <!-- <label class="layui-form-label">预览图</label> -->
                <div class="layui-input-block layui-form-title" style="margin-left: 24px;">预览图</div>
                <div class="layui-input-block" style="display: inline-block;margin-left: 24px; margin-right: 10px;float:left;">
                    <div class="layui-upload-drag" id="upload_model_picture" style="background-size:160px 135px;background-repeat:no-repeat;">
                        <i class="layui-icon"></i>
                        <p>点击上传或拖拽</p>
                    </div>
                </div>
                <div class="layui-form-mid layui-word-aux" style="line-height: 112px;">*预览图作为模板的图片标识</div>
            </div>
            <div class="layui-form-mid layui-word-aux" style="margin-left: 24px;">只能上传jpg/png/gif/bmp/wepb文件，且不超过3M</div>
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

                            <div class="layui-form-item page-background-attachment" style="margin-top: 8px;">
                                <div class="layui-input-block layui-form-title">背景是否固定</div>
                                <div class="layui-input-block">
                                    <input type="radio" name="page_background_attachment" value="fixed" title="是">
                                    <input type="radio" name="page_background_attachment" value="" title="否" checked>
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
		<script id="page_pipeline_sync_template" type="text/template">
			<form  class="layui-form layui-field gs-form-valid" style="margin-bottom:30px;">
				<h4 class="gs-label-title_normal">同步其他渠道所有数据</h4>
				<div class="layui-form gs-required">
					<div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">被同步的渠道</div>
					<div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;">
							<select name="channel_list" lay-verify="required" lay-filter="gb_channel" required>
							</select>
					</div>

				</div>
				<div class="layui-form gs-required">
					<div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">被同步的语言页面</div>
					<div class="layui-input-block" style="width: 200px;display: inline-block;margin-left: 24px; margin-right: 10px;">
							<select name="lang_list" lay-verify="required" required>
							</select>
					</div>

				</div>

				<h4 class="gs-label-title_normal">同步信息</h4>
				<div class="layui-form gs-required">
					<div class="layui-input-block gs-form-label layui-form-title" style="margin-left:24px">同步到的渠道站和语言页面</div>
					<div class="layui-input-block" style="width: 90%;display: inline-block;margin-left: 24px; margin-right: 10px;">
							<!-- <div class="layui-tab layui-tab-brief" lay-filter="tab_channel_lang">
								<ul class="layui-tab-title"></ul>
								<div class="layui-tab-content"></div>
							</div> -->
							<div class="channel-container">
								<div class="swiper-wrapper"></div>
								<div class="swiper-button-next"></div>
								<div class="swiper-button-prev"></div>
								<div class="layui-tab-content"></div>
							</div>
					</div>

				</div>
			</form>
        </script>

    <script id="page_release_template" type="text/template">
        <form class="layui-form">
            <div class="layui-form-item">
                <div class="layui-input-block" style="margin-top: 20px;">
                    <div class="layui-form-item layui-all-check" style="margin-bottom: 10px;"></div>
                    <div class="layui-form-item layui-lang-item"></div>
                    <p class="layui-form-tips" style="margin-top: 20px; color: red;">注：发布成功后，线上的页面将会更新为此次编辑的效果，请谨慎操作！！</p>
                </div>
            </div>
        </form>
    </script>

    <script id="page_release_tips_template" type="text/template">
        <form class="layui-form">
            <div class="layui-form-item" style="margin-bottom: 0;">
                <div class="layui-input-block">
                    <div class="layui-upload-drag" style="width: 100%; border: 0; cursor: default; padding: 40px 0 0 0;">
                        <i class="layui-icon" style="color: #32A0ff;"></i>
                    </div>
                </div>
                <div class="layui-form-mid layui-word-aux" style="width: 100%; text-align: center; font-size: 16px; color: #333 !important; float: initial; padding: 0 !important;">发布进行中，稍后在访问里查看线上页面效果！！！</div>
            </div>
        </form>
    </script>

    <script id="page_release_succ_template" type="text/template">
        <form class="layui-form">
            <div class="layui-form-item" style="margin-bottom: 0;">
                <p style="color: #333; font-size: 16px; text-align: center; padding: 40px 40px;">发布成功，请在访问中查看已发布的页面效果</p>
            </div>
        </form>
    </script>

<?php
include_once(app()->basePath.'/htdocs/resources/tpl/commontpl/tpl_component_stylesheet.html');
?>

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


    <?php
$this->registerJsFile(
    app()->url->assets->js('design.js'),
    ['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/library/swiper/swiper.4.4.1.min.js'),
    ['position' => \yii\web\View::POS_END]
);
$this->registerJsFile(
	app()->url->assets->js('resources/layui/layui.all.js'),
	['position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/design.activity.gb.js'),
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
    app()->url->assets->js('resources/javascripts/config.item.gb.js'),
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

