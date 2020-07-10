<link rel="stylesheet" href="<?= app()->url->assets->css('resources/stylesheets/geshop-grid-server.css'); ?>">

<main class="site-main-design">
    <div id="design_drag" class="design-left-box design-left-box-visible">
        <div class="design-left">
            <h6 class="design-left-title">布局</h6>
            <div class="el-row design-layout-box">
                <div class="el-col el-col-12 design-layout-item layout-drag" draggable="true">
          <span class="design-layout-body">
            <span class="design-layout-full"></span>
          </span>
                </div>
            </div>
            <h6 class="design-left-title">模块</h6>
            <div class="el-row design-model-box">
                <div class="el-col el-col-12 design-model-item component-drag" draggable="true">
                    <p class="design-model-icon"><i class="el-icon-picture"></i></p>
                    <p class="design-model-desc">Banner 图</p>
                </div>
            </div>
        </div>
    </div>
    <div class="design-right">
        <div class="el-row design-control">
            <div class="el-col el-col-12">
                <a href="javascript:;" id="js_toggleLeft"><i class="el-icon-menu"></i></a>
                <strong class="design-control-title">38 妇女节</strong>
            </div>
            <div class="el-col el-col-12 text-right">
                <button type="button" class="el-button el-button--primary el-button--small">预览</button>
                <button type="button" class="el-button el-button--danger el-button--small">发布</button>
            </div>
        </div>
        <div class="design-view layout-drop">

        </div>
    </div>
</main>

<?php
$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/frameworks/jquery.js'),
    ['position' => \yii\web\View::POS_END]
);
$this->registerJsFile(
    app()->url->assets->js('resources/javascripts/design.js'),
    ['position' => \yii\web\View::POS_END]
);
