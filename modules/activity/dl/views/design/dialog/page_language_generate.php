<script id="page_language_generate_template" type="text/template">
    <form class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">语言: </label>
            <div class="layui-input-block">
                <?php foreach ($activityInfo['langList'] as $item) { ?>
                <input type="checkbox" name="generate_release_language" title="<?= $item['name'] ?>" lay-skin="primary" value="<?= $item['key'] ?>">
                <?php } ?>
            </div>
        </div>
    </form>
</script>