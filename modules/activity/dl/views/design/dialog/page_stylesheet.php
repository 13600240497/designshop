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