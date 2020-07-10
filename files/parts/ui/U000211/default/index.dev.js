/**
 * 预约提醒组件（模版一）
 * @param {string} pid 当前组件的ID
 * @param {string} lang 组件语言
 * @param {string} book_desc 预约的描述
 * @param {string} book_link 预约的跳转链接
 * @param {string} book_time 日期时间的区间
 */
function geshop_U000211_default_init({ pid, lang, book_desc, book_link, book_time }) {
    const $component = $(`.geshop-U000211-default-${ pid }`);
    const $button = $component.find('button');
    const $img = $component.find('.default-image-box')
   
    // 读取 cookie
    const getCookie = (name) => {
        let arr;
        const reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
        if (arr = document.cookie.match(reg)) {
            return arr[2]
        } else {
            return ''
        }
    }

  
    // 登录状态
    const is_login = getCookie('WEBF-user_id');
    // 当前用户预约的列表
    const booked_list = getCookie('booked_list') || [];
    // 判断是否已经预约了当前节点
    let is_booked = booked_list.indexOf(pid) >= 0;
    // 语言包
    const languages = {
        ready: { en: 'COMING SOON', fr: 'ARRIVERA', ru: 'СКОРО' },
        able: { en: 'REMIND ME', fr: 'ME RAPPELER', ru: 'НАПОМНИ МНЕ' },
        booked: { en: 'REMINDED', fr: 'RAPPELÉ', ru: 'НАПОМНЕНО' },
        ing: { en: 'SHOP NOW', fr: 'SHOPPER', ru: 'ПОКУПАЙ СЕЙЧАС' },
        ended: { en: 'ENDED', fr: 'TERMINÉ', ru: 'ЗАВЕРШЕНО' },
        success: { en: 'Subscribed successful! The remind will be sent when the sale start soon. ', fr: 'Abonné avec succès! Le rappel sera envoyé au début de la vente.', ru: 'Подписка прошла успешно! Напоминание будет отправлено в ближайшее время, когда начнется распродажа.' },
    }
    // 纪录当前状态
    let timer_id = null;
    let global_status = 0;
    // 如果没预约的话，判断时间
    /** 
     * 0 = commin soon
     * 1 = 没开始
     * 2 = 进行中
     * 3 = 结束
     * */ 
    geshop_U000211_default_datetime_start (book_time, timer_id, function(status) {
        global_status = status;
        
        switch (status) {
            case 0:
                if (is_booked) {
                    // 已预约
                    $img.removeClass('ing').attr('href', 'javascript:;');
                    $button.addClass('booked').html(languages.booked[GESHOP_LANG]);
                } else {
                    // 未开始
                    $img.removeClass('ing').attr('href', 'javascript:;');
                    $button.addClass('canbook').html(languages.able[GESHOP_LANG]);
                }
                break;   
            case 1:
                if (is_booked) {
                    // 已预约
                    $img.removeClass('ing').attr('href', 'javascript:;');
                    $button.addClass('booked').html(languages.booked[GESHOP_LANG]);
                } else {
                    // 即将开始
                    $img.removeClass('ing').attr('href', 'javascript:;');
                    $button.addClass('ready').html(languages.ready[GESHOP_LANG]);
                }
                break;
            case 2:
                // 预约中
                $img.addClass('ing').attr({
                    'href': book_link ? book_link : 'javascript:;',
                    'target': '_blank'
                });
                $button.addClass('booking').html(languages.ing[GESHOP_LANG]);
                break;
            case 3:
                // 活动结束
                $img.removeClass('ing').attr('href', 'javascript:;');
                $button.addClass('ended').html(languages.ended[GESHOP_LANG]);
                break;
            case 4:
                // 组件默认状态
                $img.removeClass('ing').attr('href', 'javascript:;');
                $button.addClass('default').html(languages.able[GESHOP_LANG]);
                break;
        }
    });

    setTimeout(() => { $button.show(); }, 100);
        
    
    // 按钮事件绑定
    $button.on('click', function(event) {
        event.stopPropagation();
        const className = $(this).attr('class');
        const isEditEnv = $(this).attr('data-edit');
        if (className.includes('canbook')) {
            handleBook(isEditEnv);
        }
        if (className.includes('booking')) {
            window.open(book_link);
        }
    });

    // 更新预约状态
    const geshop_U000211_default_datetime_isBooked = (pid) => {
        const booked_cookie = getCookie('booked_list') || [];
    
        if (booked_cookie && booked_cookie.length > 0) {
            const booked_lists = booked_cookie.split(',');

            $(`.geshop-U000211-default-${ pid }`).each((index, item) => {
                const taht = $(item);
                booked_lists.forEach((bookedItem, bookedIndex) => {
                    if (taht.data('id') == bookedItem) {
                        is_booked = true;
                    }
                });
            });
        }
    };


    // 预约函数
    const handleBook = (isEditEnv) => {
        // 兼容MAC时间格式
        const startTimeLabel = book_time.split(' - ')[0].replace(/-/g, '/');
        const endTimeLabel = book_time.split(' - ')[1].replace(/-/g, '/');
        // 传参
        const url = GESHOP_INTERFACE.user_activityBook.url;
        const params = {
            pid: pid,
            client: GESHOP_PLATFORM || 'pc',
            lang: lang || 'en',
            book_desc: book_desc || '',
            book_link: book_link || '',
            book_start_time: new Date(startTimeLabel).getTime() / 1000 || '',
            book_end_time: new Date(endTimeLabel).getTime() / 1000 || ''
        }
 
        if (isEditEnv == 0) {
            // 检测站点登录信息
            if (typeof GLOBAL.login.info_check() != 'undefined') {
                GLOBAL.login.info_check().done((res) => {
                    if (res.status == 1 && res.user_id !== 0) {
                        if ($button.data('booke') != 1) {

                            window.GEShopCommonFn_Vue.$jsonp(url, params, { pid: pid }).done(function (res) {
                                if (res.code == 0) {
                                    // 已预约
                                    is_booked = true;
                                    $button.attr('data-booke', 1);
                                    GEShopSiteCommon.dialog.message(languages.success[GESHOP_LANG]);
                                } else {
                                    GEShopSiteCommon.dialog.message(res.message);
                                }
                            })
                        }
                    } else {
                        window.location.href = HTTPS_LOGIN_DOMAIN + GESHOP_LANG + '/m-users-a-sign.htm?ref=' + window.location.href;
                    }
                })
            }
        }
    }
}



/**
 * 倒计时
 * @param {string} book_time 日期时间的区间
 * @param {function} ready_callback 未开始的回调
 * @param {function} ing_callback 活动中的回调
 * @param {function} ended_callback 活动结束的回调
 * @return {int} status 返回当前状态, 0=commin, 1=canbook, 2=ing, 3=ended
 */
function geshop_U000211_default_datetime_start (book_time, timer_id, callback) {
    // 兼容MAC时间格式
    const startTimeLabel = book_time.split(' - ')[0].replace(/-/g, '/');
    const endTimeLabel = book_time.split(' - ')[1].replace(/-/g, '/');
    // 转换时间戳
    const start = new Date(startTimeLabel).getTime();
    const end = new Date(endTimeLabel).getTime();
    const local = new Date().getTime();

    if (local > end) return callback(3)
    if (isNaN(start)) return callback(4)

    timer_id = setInterval(() => {
        const local = new Date().getTime()
        const threeHour = 10800000;
        let status = 0;

        if (local <= start) {
            if (local + threeHour <= start) {
                status = 0;
            } else {
                status = 1;
            }
        } else if (local >= end) {
            status = 3;
            clearInterval(timer_id);
        } else {
            status = 2;
        }
        callback && callback(status)
    }, 1000)
}

function geshop_U000211_default_datetime_stop(timer_id) {
    clearInterval(timer_id);
}

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

