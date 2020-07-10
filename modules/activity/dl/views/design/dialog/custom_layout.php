<script id="custom_layout_template" type="text/template">
    <form class="layui-form" action="" style="margin-top:8px">
        <div class="layui-form-item">
            <div class="layui-input-block layui-form-title"><span class="text_red" style="color: red;">*</span>宽度</div>
            <div class="layui-input-block" style="min-height: 30px;">
                <input type="radio" name="width" lay-filter="width" value="100%" title="100%" checked>
                <input type="radio" name="width" lay-filter="width" value="90%" title="90%">
                <input type="radio" name="width" lay-filter="width" value="80%" title="80%">
                <input type="radio" name="width" lay-filter="width" value="0" title="自定义">
            </div>
        </div>

        <div data-type="width" class="layui-form-item" style="display: none; margin-top: 20px;">
            <div class="layui-input-block">
                <input type="text" name="customWidth" placeholder="请设置自定义布局宽度百分比(范围10-100)" onkeyup="value = value.replace(/[^\d]/g, '').replace(/^0{1,}/g,'')" maxlength="3" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-tab layui-custom-tab layui-tab-brief" lay-filter="tab-brief">
            <ul class="layui-tab-title">
				<li class="layui-this">PC+PAD</li>
				<li lay-filter="layui-tab">M</li>
            </ul>

            <div class="layui-tab-content">
                <!-- PC+PAD  -->
                <input type="hidden" class="hiddenPcItem" name="hiddenPcItem">
                <div class="layui-tab-item layui-show" data-type="pc_pad">
                    <div class="layui-form-item">
                        <div class="layui-input-block layui-input-re-block layui-form-title"><span class="text_red" style="color: red;">*</span>列(必填)</div>
                        <div class="layui-input-block layui-input-re-block">
                            <input type="radio" name="column" lay-filter="column" data-col="1" value="1" title="一列" checked>
                            <input type="radio" name="column" lay-filter="column" data-col="1,2" value="2" title="二列">
                            <input type="radio" name="column" lay-filter="column" data-col="1,3" value="3" title="三列">
                            <input type="radio" name="column" lay-filter="column" data-col="1,2,4" value="4" title="四列">
                        </div>
                    </div>

                    <div class="layui-form-item" style="margin-top: 20px;">
                        <div class="layui-input-block" style="margin-left: 0">
                            <input type="radio" name="type" lay-filter="type" value="1" title="均分" checked>
                            <input type="radio" name="type" lay-filter="type" value="0" title="自定义">
                        </div>
                    </div>

                    <div data-type="type" class="layui-form-item" style="display: none;">
                        <div class="layui-input-block" style="margin-left: 0;">
                        </div>
                    </div>
                </div>

                <!-- M -->
                <div class="layui-tab-item" data-type="m">
                    <input type="hidden" class="hiddenWapItem" name="hiddenWapItem">
                    <div class="layui-form-item layui-form-m">
                        <div class="layui-input-block layui-input-re-block layui-form-title"><span class="text_red" style="color: red;">*</span>列(必填)</div>
                        <div class="layui-column" data-val="1" >
                            <input type="radio" name="column_m" lay-filter="column_m" value="1" title="一列" checked>
                        </div>
                        <div class="layui-column" data-val="2">
                            <input type="radio" name="column_m" lay-filter="column_m" value="2" title="二列">
                        </div>
                        <div class="layui-column" data-val="3">
                            <input type="radio" name="column_m" lay-filter="column_m" value="3" title="三列">
                        </div>
                        <div class="layui-column" data-val="4">
                            <input type="radio" name="column_m" lay-filter="column_m" value="4" title="四列">
                        </div>
                    </div>

                    <div class="layui-form-item" style="margin-top: 20px;">
                        <div class="layui-input-block" style="margin-left: 0">
                            <input type="radio" name="typem" lay-filter="typem" value="1" title="均分" checked>
                            <input type="radio" name="typem" lay-filter="typem" value="0" title="自定义">
                        </div>
                    </div>

                    <!-- wap数据分割 -->
                    <div data-type="typem" class="layui-form-item" style="display: none;">
                        <div class="layui-input-block" style="margin-left: 0;">
                        </div>
                    </div>

                </div>
            </div>
        </div>



        <!-- <div class="layui-form-item">
            <div class="layui-input-block layui-form-title"><span class="text_red" style="color: red;">*</span>列(必填)</div>
            <div class="layui-input-block">
                <input type="radio" name="column" lay-filter="column" value="1" title="一列" checked>
                <input type="radio" name="column" lay-filter="column" value="2" title="二列">
                <input type="radio" name="column" lay-filter="column" value="3" title="三列">
                <input type="radio" name="column" lay-filter="column" value="4" title="四列">
                <input type="radio" name="column" lay-filter="column" value="0" title="自定义">
            </div>
        </div> -->
        <div data-type="column" class="layui-form-item" style="display: none;">
            <div class="layui-input-block">
                <input type="text" name="customColumn" placeholder="请设置列的数量(范围1-9列)" autocomplete="off" class="layui-input">
            </div>
            <!-- <div class="layui-input-block">
                <input type="radio" name="type" lay-filter="type" value="1" title="均分" checked>
                <input type="radio" name="type" lay-filter="type" value="0" title="自定义">
            </div> -->
        </div>
        <!-- <div data-type="type" class="layui-form-item" style="display: none;">
            <div class="layui-input-block">
            </div>
        </div> -->
    </form>
</script>
