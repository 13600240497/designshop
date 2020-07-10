$(function() {
    $('.geshop-U000210-default .gs-blog-content').each(function (index, item) {
        $(item).hover(function () {
            $(item).addClass('hover-blog-content');
        }, function () {
            $(item).removeClass('hover-blog-content');
        })
    });

});
