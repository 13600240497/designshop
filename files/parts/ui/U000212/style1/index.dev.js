
/* 保存前的方法 */
function beforeSubmit(progress) {
    var image = $('input[name="image"]').val();
    var book_time = $('input[name="book_time"]').val();
    var book_link = $('input[name="book_link"]').val();
    var book_desc = $('input[name="book_desc"]').val().replace(/\'/g,"&apos;").replace(/\"/g,"&quot;");
    $('input[name="book_desc"]').val(book_desc);
    
    if (!image) {
        layui.layer.msg('图片地址不能为空!');
        return;
    } else if (!book_time) {
        layui.layer.msg('活动时间不能为空!');
        return;
    } else if (!book_link) {
        layui.layer.msg('链接不能为空!');
        return;
    } else if (!book_desc) {
        layui.layer.msg('预约活动关键描述!');
        return;
    }
    progress.next();
}
