function geshop_U000027_style2_init(pid, isEditEnv) {
    // 装修页环境，不执行以下代码
    if (isEditEnv == '1') return false;
    // 激活的 className
    const active_class = 'active';
    // 站点头部的高度
    const site_header_height = GESHOP_UTIL.getSiteFiedHeader;
    // 导航组件
    const navbar = $(`.geshop-U000027-style2-${pid}`);
    // 子项的绑定的PID
    const navbar_items_id = navbar.find('li').map((index, item) => {
        return $(item).data('pid')
    });
    // 子项 target的上偏移量
    let navbar_content_top = [];
    navbar_items_id.map((index, pid) => {
        const target = $(`.geshop-component-box[data-id="${pid}"]`);
        const target_top = target.offset().top || 0;
        navbar_content_top.push({ pid, top: target_top });
    });

    // 绑定滚动事件
    var timer = null;
    $(window).scroll(() => {
        clearTimeout(timer);
        timer = setTimeout(function() {

            navbar_content_top = [];
            navbar_items_id.map((index, pid) => {
                const target = $(`.geshop-component-box[data-id="${pid}"]`);
                const target_top = target.offset().top || 0;
                navbar_content_top.push({ pid, top: target_top });
            });

            const window_scroll_top = $(window).scrollTop();
            const activity_items = navbar_content_top.filter(item => {
                return Number(window_scroll_top) >= Number(item.top) - navbar.height();
            });

            // 判断结果
            if (activity_items.length > 0) {
                navbar.addClass('component-nav-fixed');
                const current_item = activity_items.pop()
                const li = navbar.find(`li[data-pid="${current_item.pid}"]`);
                li.addClass(active_class).siblings().removeClass(active_class);
                // console.log(current_item);

                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').hide();
                }

            } else {
                navbar.removeClass('component-nav-fixed');

                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').show();
                }
            }
        }, 50);
    });
};
