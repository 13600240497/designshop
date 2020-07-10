var beforeSubmit = function (progress) {
    /**
     * 组装导航链接，保存为nav_link
     */
    var nav_link = {};
    $('.U000027-form-dataset .layui-form-item').each(function(index, item) {
        var checkbox = $(item).find('input[name="nav_menu"]');
        var pid = checkbox.val();
        var link = $(item).find('input[type="text"]').val();
        /**
         * 如果是选中的状态下
         */
        if (checkbox.prop('checked') === true) {
            nav_link[pid] = link;
        }
    });
    $('.U000027-form-dataset').find('input[name="nav_link"]').val(JSON.stringify(nav_link));
    progress.next();
}