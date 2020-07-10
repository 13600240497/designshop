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
            <div class="preview-img" >
                <img src="/frontend/geshop/images/view/zaful/loading.gif" alt="">
            </div>
        </div>
    </form>
</script>