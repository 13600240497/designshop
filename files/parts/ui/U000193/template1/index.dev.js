
/**
 * 判断是否应该弹窗
 * 规则：当天0-24小时出现过的不再出现
 * @returns {Boolean}
 */
function judge_show () {
    // 判断是否有缓存
    if (localStorage['U000193-template1']) {
        // 上次的时间戳
        const last_timestamp = new Date(parseInt(localStorage['U000193-template1']));
        const last_y = last_timestamp.getFullYear();
        const last_m = last_timestamp.getMonth() + 1;
        const last_d = last_timestamp.getDay();
        const last_date_str = `${last_y}-${last_m}-${last_d}`;
        // 当天日期
        const current_time = new Date();
        const current_y = current_time.getFullYear();
        const current_m = current_time.getMonth() + 1;
        const current_d = current_time.getDay();
        const current_date_str = `${current_y}-${current_m}-${current_d}`;
        // 2者不一致则返回true
        return last_date_str != current_date_str;
    } else {
        return true;
    }
}

/**
 * 记录打开的时间
 */
function update_show_time () {
    localStorage['U000193-template1'] = new Date().getTime();
}

/**
 * 打开弹窗
 */
function show_dialog_193_tempalte1 () {
    var container = $('.U000193-template1');
        container.addClass('is-fixed');
    update_show_time();
}

function close_dialog_193_template1 () {
    var container = $('.U000193-template1');
    container.removeClass('is-fixed');
}

/** 初始化 */
!(function() {

    /**
     * 获取登陆状态
     * PC: window.GLOBAL.login.info_check()
     * WAP: window.info_check()
     */
    window.GLOBAL && GLOBAL.login.info_check().done(function (res) {
        // 已经登录 + 非当天注册
        if (res.is_check_in == 0 && res.is_today_reg == false) {
            // 判断缓存是否出现过
            judge_show() && show_dialog_193_tempalte1();
        }
    });
    
    /**
     * 遍历所有组件
     */
    $('.U000193-template1').each(function() {
        var $component = $(this);

        /**
         * 绑定关闭事件
         */
        $component.on('click', '.geshop-popup-close', function() {
            close_dialog_193_template1();
        });
    });
})();

