<script id="page_link_template" type="text/template">
    <?php if (!empty($pageUrl)): ?>
    <a href="<?= $pageUrl ?>" target="_blank" class="layui-btn layui-btn-normal">
        <?= $langName ?>
    </a>
    <?php else: ?>
    <span>发布活动才能生成来链接</span>
    <?php endif; ?>
</script>