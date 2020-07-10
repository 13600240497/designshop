function geshop_U000025_rule_init(id) {
    var $root = $(`.geshop-component-box[data-id="${id}"]`)

    $root.on('click', '.dialog-btn', function () {
        $root.find('.geshop-U000025-dialog-rule').show();
    });

    // 关闭
    $root.on('click', '.js-close-dialog', function () {
        $root.find('.geshop-U000025-dialog-rule').hide()
    });

    function scrollFn() {
        const $width = $root.find('.gs-bg-img').width();
        if (document.body.scrollHeight > (window.innerHeight || document.documentElement.clientHeight)) {
            if ($width >= 1900 && $width <= 1920 && window.innerWidth >= 1920) {
                $root.find('.geshop-U000025-rule').addClass('overflow');
            } else {
                $root.find('.geshop-U000025-rule').removeClass('overflow');
            }
        }
    };
    scrollFn();

    $(window).on('resize', scrollFn);
}


