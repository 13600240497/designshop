<script id="page_language_generate_template" type="text/template">
    <form class="layui-form" id="page_lang_wraper">
        <div class="layui-form-item">
            <label class="layui-form-label">语言: </label>
            <div class="layui-input-block">
                <?php foreach ($pageInfo['pageLanguages'] as $item) { ?>
                <input type="checkbox" name="<?= $item['lang'] ?>" title="<?= $item['lang_name'] ?>" lay-skin="primary" value="<?= $item['lang'] ?>">
                <?php } ?>
            </div>
        </div>
    </form>
    <div id="page_lang_temp">
        <div>
            <label><input type="radio" name="indexType" value="1" checked="true" />A首页</label>
            <label><input type="radio" name="indexType" value="2" />B首页</label>
        </div>
        <div>
            A首页为正常首页，B首页为测试首页，设为A首页后不可转为B首页
        </div>
    </div>

</script>
