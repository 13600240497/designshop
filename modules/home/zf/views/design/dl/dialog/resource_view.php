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