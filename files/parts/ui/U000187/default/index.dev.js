if (GESHOP_SITECODE && (GESHOP_SITECODE == 'zf-pc' || GESHOP_SITECODE == 'zf-wap')) {
    // window.geshop_share_appid = '966242223397117'
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '1396335280417835'
    window.tag = 'ra-5a38671bb83b79fe';
} else if (GESHOP_SITECODE && GESHOP_SITECODE == 'rg-wap') {
    // window.geshop_share_appid = '584881354898638'
    window.tag = 'ra-54c2151b31fb2710';
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '860992623979486'
} else if (GESHOP_SITECODE && GESHOP_SITECODE == 'rg-pc') {
    // window.geshop_share_appid = '584881354898638'
    window.tag = 'ra-54c2151b31fb2710';
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '860992623979486'
}
// 分享
window.share = {
    facebook: {
        init: function (callback) {
            var _this = this;
            /* $('[property="og:url"]').attr('content', window.location.href);
             // $('[property="og:image"]').attr('content');
             $('[property="og:title"]').attr('content', $('title').text());
             $('[property="og:description"]').attr('content', $("[name='description']").attr('content'));*/
            $("body").append('<div id="fb-root"></div>');
            window.fbAsyncInit = function () {
                FB.init({
                    appId: window.geshop_share_appid,
                    status: true,
                    cookie: true,
                    oauth: true,
                    xfbml: true,
                    version: 'v2.6'
                })
            };
            (function () {
                var e = document.createElement('script');
                e.type = 'text/javascript';
                e.src = 'https://connect.facebook.net/en_US/all.js';
                e.async = true;
                document.getElementById('fb-root').appendChild(e)
            }());
            if (typeof callback == 'function') {
                callback()
            }
        }, start: function (cfg, callback) {
            var _this = this;
            cfg = cfg || {};

            var shareParams = 'url=' + encodeURIComponent(window.location.href) + '&title=' + encodeURIComponent($('title').text()) + '&description=' +
                $("[name='description']").attr('content') + '&screenshot=' + $('[property="og:image"]').attr('content') + '&pubid=' + window.tag;

            var iWidth = 616, iHeight = 383, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url = '//api.addthis.com/oexchange/0.8/forward/facebook/offer?' + shareParams;
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);

            if (typeof callback == 'function') {
                callback()
            }

            /*FB.ui({
                method: 'feed',
                display: 'popup',
                name: cfg.name,
                picture: cfg.picture,
                link: cfg.link,
                caption: cfg.caption,
                description: cfg.description
            }, function (response) {
                if (response && !response.error_code) {
                    if (typeof callback == 'function') {
                        callback()
                    }
                }
            })*/
        }
    }, twitter: {
        init: function (callback) {
            var _this = this;
            $("body").append('<div id="twitter-root"></div>');
            window.twttr = (function (d, s, id) {
                var t, js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = "https://platform.twitter.com/widgets.js";
                fjs.parentNode.insertBefore(js, fjs);
                return window.twttr || (t = {
                    _e: [], ready: function (f) {
                        t._e.push(f)
                    }
                })
            }(document, "script", "twitter-wjs"));
            if (typeof callback == 'function') {
                callback()
            }
        }, start: function (cfg, callback) {
            var _this = this;
            cfg = cfg || {};
            var iWidth = 650, iHeight = 430, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(cfg.text) + '&url=' + encodeURIComponent(cfg.link);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback()
            }
        }
    }, google: {
        init: function (callback) {
            var _this = this;
            (function () {
                var e = document.createElement('script'), fjs = document.getElementsByTagName('script')[0];
                e.type = 'text/javascript';
                e.src = 'https://apis.google.com/js/platform.js';
                e.async = true;
                fjs.parentNode.insertBefore(e, fjs)
            }());
            if (typeof callback == 'function') {
                callback()
            }
        }, start: function (cfg, callback) {
            var _this = this;
            cfg = cfg || {};
            var iWidth = 510, iHeight = 550, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url = 'https://plus.google.com/share?url=' + encodeURIComponent(cfg.link) + '&t=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback()
            }
        }
    }, pinterest: {
        start: function (cfg, callback) {
            var _this = this;
            cfg = cfg || {};
            var iWidth = 800, iHeight = 650, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url = 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent(cfg.link) + '&media=' + encodeURIComponent(cfg.image) + '&description=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback()
            }
        }
    }, line: {
        start: function (cfg, callback) {
            var _this = this;
            cfg = cfg || {};
            var iWidth = 800, iHeight = 650, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(cfg.link) + '&description=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback()
            }
        }
    }
};
// 分享初始化
window.share.facebook.init();

// 加载插件
$(function () {
    var components = $('.geshop-U0000187-default');
    var staticDomain = GESHOP_STATIC;
    components.each(function (index, elem) {
        // 初始化组件
        $LAB.script(staticDomain + "/resources/javascripts/library/jQueryRotate.js").wait(function () {
            geshop_turntable_init(elem);
        });
    });
});

// 页面初始化
function geshop_turntable_init(elem) {
    try {
        var helper = new Geshop_turntable_helper(elem, {
            elements: {
                dialog_share: '.geshop-187d-dialog-share',
                spins: '.geshop-187d-spins',
                left_points: '.geshop-187d-points',
            }
        });
        // 初始化清空sessionStorage
        helper.delete_award_sessionstorage();
        // 绑定事件
        helper.init_events();
        // 初始化抽奖活动数据
        helper.init_lottery_info({});
        // 初始化中奖名单
        helper.init_luck_list();
        // 初始化分享
        helper.init_share();
    } catch (e) {
        console.log(e);
    }
}

var Geshop_turntable_helper = function (elem, options) {

    /**
     * 绑定转盘所需要的所有JQ对象，避免在JS里面写CSS类名
     * @param {jQueryNodeList} elements 
     * @example { dialog_share: ".dialog-share" }
     */
    this.bind_trigger = function (elements) {
        Object.keys(elements).map(key => {
            const trigger_class = elements[key];
            this.helper.jQueryNodeList[key] = $(trigger_class);
        });
    };

    this.helper = {
        site_code: (typeof GESHOP_SITECODE !== 'undefined') ? GESHOP_SITECODE.split('-')[0] : '', // 站点site code
        site_code_upper: ((typeof GESHOP_SITECODE !== 'undefined') ? GESHOP_SITECODE.split('-')[0] : '').toUpperCase(), // 站点site code
        pipeline_code: typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : GESHOP_LANG === 'en' ? (GESHOP_SITECODE.split('-')[0]).toUpperCase() : (GESHOP_SITECODE.split('-')[0] + GESHOP_LANG).toUpperCase(), // 国家站简码
        lang: ((typeof GESHOP_LANG !== 'undefined') ? GESHOP_LANG : '').toUpperCase(), // 语言
        spins: 3, // 剩余抽奖次数
        canShare: true, // 能否通过分享获得抽奖次数
        countdown_status: 0, // 倒计时状态 0-1-2
        languages: {}, // 语言包
        asyncData: {}, // 初始化接口返回数据
        left_second: 0, // 剩余秒数
        doLotteryData: {}, // 点击抽奖返回数据
        show_points_info: false, // 是否展示积分信息 [2020-05-10]
        use_point: 0, // 是否允许使用积分 [2020-05-10]
        /**
         * jQ对象节点
         */
        jQueryNodeList: {
            dialog_share: null,
            spins: null,
            left_points: null,
        },

        // 初始化语言包
        init_languages: function () {
            var langKey = GESHOP_LANG || 'en';
            Object.keys(GESHOP_TUNRNTABLE_LANGUAGES).map(function (key) {
                this.languages[key] = GESHOP_TUNRNTABLE_LANGUAGES[key][langKey] || GESHOP_TUNRNTABLE_LANGUAGES[key]['en'];
            }.bind(this));
        },

        // 获取 cookie 方法
        get_cookie: function (name) {
            var arr, reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
            if (arr = document.cookie.match(reg)) {
                return arr[2];
            } else {
                return null;
            }
        },

        // 存储是否可通过分享增加抽奖次数状态
        set_share_award_status: function (status) {
            sessionStorage.setItem('IS_SHARE_AWARD_STATUS', status);
        },

        delete_award_sessionstorage: function () {
            sessionStorage.removeItem('IS_SHARE_AWARD_STATUS');
        },

        // 将抽奖按钮置为可用状态
        set_dolottery_btn_enable: function () {
            $(elem).find('.js-start-draw').attr('data-isdrawing', 0);
        },

        // 绑定事件
        init_events: function () {
            // 点击指针抽奖
            $(elem).on('click', '.js-start-draw', function (target) {
                
                // 如果接口没初始化完成点击抽奖无效
                var is_loaded = $(target.currentTarget).attr('data-loaded');
                if (is_loaded == 0) return false

                // 正在抽奖状态不可连续点击
                if ($(target.currentTarget).attr('data-isdrawing') == 1) return false;

                // 0=未开始, 1=进行中, 2=已结束
                if (this.countdown_status == 0) return this.dialog_show_fail_not_start();
                if (this.countdown_status == 2) return this.dialog_show_fail(this.languages.ended);
               
                // 将抽奖按钮置为正在抽奖状态
                $(target.currentTarget).attr('data-isdrawing', 1);

                // 请求站点接口获取用户信息
                this.ajax_get_usertoken(function (user_token) {

                    // spins - 剩余抽奖次数
                    // status - 状态码
                    // 200 - 抽奖成功
                    // 601 - 没有抽奖机会
                    // 403 - 未登陆
                    // 602 - 抽奖失败
                    // 603 - 活动已结束
                    // 604 - 活动无效
                    // 605 - 用户信息无效用户不存在
                    // 607 - 积分不足 2020-05-08新增
                    // 608 - 网络异常 2020-05-08新增
                    this.ajax_get_spins(function (spins, status, left_points) {

                        // 未登陆，跳转到重定向
                        if (this.asyncData.is_login == 0) {
                            this.handleRedirectLogin();
                        } else {
                            // 请求成功
                            if (status == 200) {

                                // 是否中奖
                                if (this.doLotteryData.has_win == 1) {
                                    this.ajax_get_bonus();
                                    setTimeout(function () {
                                        left_points != null && this.render_points(left_points);
                                        this.init_lottery_info({
                                            render_points: false
                                        });
                                        this.set_dolottery_btn_enable();
                                    }.bind(this), 5000);
                                } else {
                                    this.ajax_get_bonus();
                                    setTimeout(function () {
                                        left_points != null && this.render_points(left_points);
                                        this.dialog_show_fail(this.languages.unlucky);
                                        this.set_dolottery_btn_enable();
                                    }.bind(this), 5000);
                                }
                            }

                            // 没有抽奖次数了
                            else if (status == 601 || status == 607) {
                                /**
                                 * 积分不够的提示
                                 */
                                if (this.use_point == 1) {
                                    this.dialog_show_points_tips();
                                    this.set_dolottery_btn_enable();
                                    this.use_point = 0; // 改为0，下次不再出现这个弹窗了
                                    return false;
                                }

                                // 是否可通过分享增加抽奖次数
                                // IS_SHARE_AWARD_STATUS 可分享 - 1 不可分享 - 0
                                if (sessionStorage.getItem('IS_SHARE_AWARD_STATUS') == 1) {
                                    this.dialog_show_share();
                                    this.set_dolottery_btn_enable();
                                } else {
                                    this.dialog_show_fail(this.languages.noChance);
                                }
                            }

                            // 没登录权限，跳转登录页面
                            else if (status == 403) {
                                GEShopSiteCommon.dialog.message('please log in first.');
                                this.handleRedirectLogin();
                            } else if (status == 602) {
                                this.dialog_show_fail(this.languages.fail);
                            } else if (status == 606) {
                                this.dialog_show_fail(this.languages.unlucky);
                            } else if (status == 603) {
                                this.dialog_show_fail(this.languages.ended);
                            } else if (status == 604) {
                                this.dialog_show_fail(this.languages.fail);
                            } else if (status == 605) {
                                GEShopSiteCommon.dialog.message('User information is invalid.');
                                this.set_dolottery_btn_enable();
                            } else if (status == 500) {
                                GEShopSiteCommon.dialog.message('Invalid request2！');
                                this.set_dolottery_btn_enable();
                            }
                        }
                    }.bind(this), user_token);
                }.bind(this));
                    
            }.bind(this));

            // 点击规则
            $(elem).on('click', '.js-show-rule-dialog', function (target) {
                $(elem).find('.geshop-187d-dialog-rule').show();
            }.bind(this));

            // 关闭弹出层
            $(elem).on('click', '.js-close-dialog', function (target) {
                // 隐藏弹窗
                var className = '.' + $(target.currentTarget).attr('data-tag');
                $(elem).find(className).hide();
                // 重置动画转盘
                this.animateReset();
            }.bind(this));

            // Check Now
            $(elem).on('click', '.js-check-now', function (target) {
                // 跳转
                var that = this;
                var site_code = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';

                if (site_code == 'zf') {
                    var url = '';
                    // prize_type 1 优惠劵
                    // prize_type 2 积分
                    // prize_type 3 商品
                    if (that.doLotteryData.prize_type == 1 || that.doLotteryData.prize_type == 2 || that.doLotteryData.prize_type == 3) {
                        url = (typeof HTTPS_USER_DOMAIN !== 'undefined' ? HTTPS_USER_DOMAIN : '') + GESHOP_PIPELINE.toLowerCase() + '/m-users-a-coupon_points.htm';
                    }
                    window.open(url);
                }
                // RG PC端的DOMAIN_USER没区分语言，M端区分了语言（what a fuck！）
                else if (site_code == 'rg') {
                    var url = '';
                    // prize_type 1 优惠劵
                    // prize_type 2 积分
                    // prize_type 3 商品
                    if (that.doLotteryData.prize_type == 1 || that.doLotteryData.prize_type == 3) {
                        url = (typeof DOMAIN_USER !== 'undefined' ? DOMAIN_USER : '') + GESHOP_LANG + '/m-ucoupon-a-couponlist.htm';
                    } else if (that.doLotteryData.prize_type == 2) {
                        url = (typeof DOMAIN_USER !== 'undefined' ? DOMAIN_USER : '') + GESHOP_LANG + '/m-users-a-points_record.htm';
                    }
                    window.open(url);
                }

            }.bind(this));

            // 关闭弹出层 - 活动未开始倒计时
            $(elem).on('click', '.js-close-dialog-not-start', function (target) {
                // 隐藏弹窗
                var className = '.' + $(target.currentTarget).attr('data-tag');
                $(elem).find(className).addClass('is-hidden').removeClass('is-visible');
                // 重置动画转盘
                this.animateReset();
            }.bind(this));
        },

        // 倒计时显示剩余 天 时 分 秒
        countdown: function (left_time) {
            var days = parseInt(left_time / 60 / 60 / 24, 10); // 剩余天数
            var hours = parseInt(left_time / 60 / 60 % 24, 10); // 剩余小时
            var minutes = parseInt(left_time / 60 % 60, 10); // 剩余分钟
            var seconds = parseInt(left_time % 60, 10);
            var checkTime = function (t) {
                if (t < 10) {
                    t = '0' + t;
                }
                return t;
            }
            days = checkTime(days);
            hours = checkTime(hours);
            minutes = checkTime(minutes);
            seconds = checkTime(seconds);
            var html = '<i>' + days + '</i>' + ': ' + '<i>' + hours + '</i>' + ': ' + '<i>' + minutes + '</i>' + ': ' + '<i>' + seconds + '</i>'
            return html;
        },

        // 初始化倒计时
        init_coutdown: function () {
            var startTime = this.asyncData.start_timestamp * 1;
            var endTime = this.asyncData.end_timestamp * 1;

            // 获取状态
            this.countdown_status = this.check_time(startTime, endTime);
            // 状态对照className
            var classMap = ['is-ready', 'is-drawing', 'is-ended'];
            // 获取对应要计算的时间戳
            var count_time = this.countdown_status == 0 ? startTime : this.countdown_status == 1 ? endTime : 0;
            // 倒计时开始
            clearInterval(intervalID);
            var intervalID = setInterval(function () {

                // 剩余秒数
                var second = this.get_second(count_time);
                this.left_second = second;

                // 右侧中奖名单显示剩余倒计时
                $(elem).find('.geshop-187d-list-warning .time').html(this.countdown(second));

                // 弹窗显示剩余倒计时
                $(elem).find('.geshop-187d-dialog-fail-not-start .geshop-187d-dialog-body p.title').html(this.languages.startIn);
                $(elem).find('.geshop-187d-dialog-fail-not-start .geshop-187d-dialog-body p.time').html(this.countdown(second));

                if (second <= 0) {
                    clearInterval(intervalID);
                    // 如果还没到已结束阶段则继续跑
                    if (this.countdown_status == 0) {
                        this.init_coutdown();
                    } else if (this.countdown_status >= 1) {
                        $(elem).removeClass('is-ready').removeClass('is-ended').addClass('is-drawing');
                        $(elem).find('.geshop-187d-dialog-fail-not-start').removeClass('is-visible').addClass('is-hidden');
                        this.countdown_status = 1;
                        return;
                    }
                } else {
                    $(elem).addClass(classMap[this.countdown_status]);
                }
            }.bind(this), 1000);
        },

        // 初始化中奖名单，每5分钟获取名单列表
        init_luck_list: function () {
            this.ajax_get_lucky();
        },

        // 检查活动状态 0 未开始  1 进行中 2 已结束
        check_time: function (startTime, endTime) {
            var now = Date.parse(new Date()) / 1000;
            if (now < startTime) {
                return 0
            } else if (now > startTime && now < endTime) {
                return 1
            } else {
                return 2
            }
        },

        // 根据时间戳得出秒数
        get_second: function (timestamp) {
            var now = Date.parse(new Date()) / 1000;
            return timestamp - now;
        },

        /**
         * 开始抽奖
         * @param {Functioin} callback(left_times, status) 回调函数(剩余次数，状态码)
         * @param {string} user_token 用户标识
         */
        ajax_get_spins: function (callback, user_token) {
            var _this = this;
            var act_code = $(elem).attr('data-actcode');
            var share_code = _this.asyncData.share_code;
            var url = GESHOP_INTERFACE.elf_webgame_do_lottery.url;
            var params = {
                act_code: act_code,
                user_token: user_token,
                share_code: share_code,
                website: _this.site_code_upper,
                pipeline_code: _this.pipeline_code,
                language: _this.lang,
            };

            $.ajax({
                type: 'GET',
                url: url,
                data: params,
                dataType: 'jsonp',
                success: function (res) {
                    _this.doLotteryData = $.extend(_this.doLotteryData, res.data);
                    const left_times = res.data.left_times || 0;
                    _this.render_spins(left_times);
                    // 更新积分
                    if (res.data.hasOwnProperty('user')) {
                        var left_points = res.data.user.points;
                    } else {
                        var left_points = null;
                    }
                    callback && callback(left_times, res.status, left_points);
                }
            });
        },

        // 获取奖品奖励
        ajax_get_bonus: function () {
            var data = this.doLotteryData;
            var res = {
                has_win: data.has_win, // 是否中奖
                type: data.prize_type || 9, // 奖品类型：1优惠券 2积分 3商品
                points: data.points, // 积分数量
                goods_sku: data.goods_sku, // 中奖商品sku
                coupon_json: data.coupon_json, // 优惠券信息
                sku_image: data.goods_img,
                bonusIndex: data.prize_id // 奖品ID
            }
            this.animation(res);
        },

        /**
         * 初始化抽奖活动数据
         * @param {Boolean} render_points 是否渲染积分 
         */
        init_lottery_info: function ({ render_points = true }) {
            var _this = this;
            var act_code = $(elem).attr('data-actcode');
            var user_token = $.cookie('user-token') ? $.cookie('user-token') : '';
            var url = GESHOP_INTERFACE.elf_webgame_info.url;
            // var url = 'http://www.elf.com.v0805.php5.egomsl.com/api/webgame-api/info';
            var params = {
                act_code: act_code,
                user_token: user_token,
                website: _this.site_code_upper,
                pipeline_code: _this.pipeline_code,
                language: _this.lang
            }
            $.ajax({
                type: 'GET',
                url: url,
                data: params,
                dataType: 'jsonp',
                success: function (res) {
                    // 请求失败
                    if (res.status == 500) {
                        typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message('error!');
                    }
                    _this.asyncData = res.data;

                    // 存储是否可通过分享增加抽奖次数状态
                    _this.set_share_award_status(_this.asyncData.is_share_award);
                    _this.ajax_get_lucky(res.data.win_list);

                    // 接口请求成功后初始化倒计时
                    _this.init_coutdown();

                    // 渲染剩余抽奖次数
                    _this.render_spins(res.data.left_times || 0);
                    
                    // 设置是否允许使用积分, only for Zaful
                    if (_this.site_code_upper == 'ZF') {
                        _this.use_point = res.data.use_point;
                        _this.show_points_info = parseInt(res.data.use_point) == 1;
                        // 渲染用户的剩余积分
                        render_points == true && _this.render_points(res.data.left_points || 0);
                    } else {
                        _this.use_point = 0;
                        _this.show_points_info = false;
                    }

                    // 渲染完成将抽奖按钮置为可点击状态
                    $(elem).find('.js-start-draw').attr('data-loaded', 1);

                    // 未登陆隐藏剩余抽奖次数
                    if (res.data.is_login == 0) {
                        $(elem).find('.geshop-187d-spins').hide();
                    }
                }
            });
        },

        // 中奖名单奖品信息初始化
        init_prize_info: function (res) {
            var html = '';
            // 1优惠券 2积分 3商品
            switch (res.prize_type) {
                case 1:
                    // nomail 1免邮 0非免邮
                    if (res.coupon_json.no_mail == 1) {
                        html = this.languages.freeCouponText;
                    } else {
                        // coupon_type 1百分比 2直减
                        if (res.coupon_json.coupon_type == 1) {
                            var youhuilv = res.coupon_json.youhuilv.split('-');
                            if (youhuilv.length == 1) {
                                html = this.languages.couponTextPercent.replace('XX', youhuilv[0]).replace('YY', '').replace('ZZ', '');
                            } else {
                                html = this.languages.couponTextPercent.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(res.coupon_json.currency));
                                // html = this.languages.couponTextPercent.replace('XX', youhuilv[1]).replace('YY', youhuilv[0]).replace('ZZ', this.change_sign(res.coupon_json.currency));
                            }
                        } else if (res.coupon_json.coupon_type == 2) {
                            var youhuilv = res.coupon_json.youhuilv.split('-');
                            if (youhuilv.length == 1) {
                                // html = this.languages.couponTextDecrease.replace(/XX/g, youhuilv[0]).replace(/YY/g, this.change_sign(res.coupon_json.currency));
                                html = this.languages.couponTextDecrease.replace(/XX/g, this.change_sign(res.coupon_json.currency) + youhuilv[0]).replace(/YY/g, '').replace('ZZ', '');
                            } else {
                                html = this.languages.couponTextDecrease.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(res.coupon_json.currency));
                            }
                        }

                    }
                    break;
                case 2:
                    if (this.site_code == 'zf') {
                        html = this.languages.pointText.replace('XX', res.points).replace('YY', 'Z');
                    } else {
                        html = this.languages.pointText.replace('XX', res.points).replace('YY', 'R');
                    }
                    break;
                case 3:
                    html = this.languages.skuText;
                    break;
                default:
                    break;
            }
            return html;
        },

        // 获取中奖名单
        ajax_get_lucky: function (win_list) {
            this.lucky_list = win_list;
            var default_list = [
                { "email": "tomer***@gmail.com", "created_time": "", "prize_type": 2, "points": "100", "win_time": "" },
                {
                    "email": "jacker***@gmail.com",
                    "created_time": "",
                    "prize_type": 2,
                    "points": "100",
                    "win_time": ""
                },
                {
                    "email": "markere***@gmail.com",
                    "created_time": "",
                    "prize_type": 2,
                    "points": "100",
                    "win_time": ""
                },
                { "email": "dfdf***@gmail.com", "created_time": "", "prize_type": 2, "points": "100", "win_time": "" },
                {
                    "email": "fvdffg***@gmail.com",
                    "created_time": "",
                    "prize_type": 2,
                    "points": "100",
                    "win_time": ""
                },
                {
                    "email": "trttyu***@gmail.com",
                    "created_time": "",
                    "prize_type": 2,
                    "points": "100",
                    "win_time": ""
                },
                { "email": "drfg***@gmail.com", "created_time": "", "prize_type": 2, "points": "100", "win_time": "" },
                {
                    "email": "hgghdf***@gmail.com",
                    "created_time": "",
                    "prize_type": 2,
                    "points": "100",
                    "win_time": ""
                },
                { "email": "fghf***@gmail.com", "created_time": "", "prize_type": 2, "points": "100", "win_time": "" },
                { "email": "rtry***@gmail.com", "created_time": "", "prize_type": 2, "points": "100", "win_time": "" }
            ]
            $(elem).find('.geshop-187d-list-uname ul').html('');
            var _this = this;
            var act_code = $(elem).attr('data-actcode');
            if (win_list && win_list.length) {
                win_list.map(function (row, index) {
                    $(elem).find('.geshop-187d-list-uname ul').append('<li>' + row.email + '<span>' + _this.init_prize_info(row) + '</span></li>');
                });
                // 中奖名单动画
                this.animateList();
            } else if (act_code == 0) {
                default_list.map(function (row, index) {
                    $(elem).find('.geshop-187d-list-uname ul').append('<li>' + row.email + '<span>' + _this.init_prize_info(row) + '</span></li>');
                });
                // 中奖名单动画
                this.animateList();
            }
        },

        // 抽奖动画
        animation: function (res) {
            if (res.has_win == 0) {
                res.type = 9;
            }
            var total = $(elem).attr('data-prizeamount') * 1; // 转盘奖品数量
            var repeat = 360 * 4;
            var eachReg = (360 / total);
            var bonusReg = eachReg * res.bonusIndex + repeat - eachReg / 2;

            // CSS3动画ie9无效
            // $(elem).addClass('geshop-animate-rotate');
            // $(elem).find('.geshop-187d-pointer img').css('transform', 'rotate(' + bonusReg + 'deg)').css('transition', 'all 5s ease-in-out');

            var _this = this;
            var angle = 0;
            var timer = setInterval(function () {
                angle += 4;
                if (angle >= bonusReg) {
                    clearInterval(timer);
                    $(elem).find('.geshop-187d-pointer img').rotate({
                        animateTo: bonusReg,
                        callback: function () {
                            // type - 1 优惠券 2 积分 3 商品 9 未中奖
                            switch (res.type) {
                                case 3:
                                    _this.dialog_show_sku(res);
                                    break;
                                case 9:
                                    break;
                                default:
                                    _this.dialog_show_other(res);
                                    break;
                            }
                        }
                    });
                    return false;
                }

                $(elem).find('.geshop-187d-pointer img').rotate(angle);
            }, 10);
        },

        animateReset: function () {
            $(elem).find('.geshop-187d-pointer img').css('transform', '').css('transition', 'none');
        },

        animateList: function () {
            var animate_wraper = $(elem).find('.geshop-187d-list-uname');
            var animate_target = animate_wraper.find('ul');
            var top = 0;

            function up() {
                animate_target.animate({
                    top: (top - 38) + 'px'
                }, 1000, function () {
                    animate_target.css({ top: '0px' }).find('li:first').appendTo(animate_target);
                    up();
                })
            }

            if (animate_wraper.height() <= animate_target.height()) {
                up();
            }
            // setInterval(function() {
            //     var d_height = dom.height();
            //     var c_height = container.height();
            //     top = top - 22 - 16;
            //     if (Math.abs(top) + d_height >= c_height) {
            //         top = 0;
            //         container.css({
            //             'transition': 'none',
            //             'transform': 'translateY('+top+'px)'
            //         });
            //     } else {
            //         container.css({
            //             'transition': 'all .5s',
            //             'transform': 'translateY('+top+'px)'
            //         });
            //     }
            // }, 1000)
        },

        // 打开弹窗 - 失败类型
        dialog_show_fail: function (msg) {
            $(elem).find('.geshop-187d-dialog-fail').show().find('p.title').html(msg);
            $(elem).find('.js-start-draw').attr('data-isdrawing', 0);
        },

        // 打开弹窗 - 失败类型
        dialog_show_fail_not_start: function () {
            $(elem).find('.geshop-187d-dialog-fail-not-start').addClass('is-visible').removeClass('is-hidden');
        },

        /**
         * 打开/关闭 - 分享提示弹窗
         * @param {Boolean} visible 
         */
        dialog_show_share: function (visible = true) {
            const target = this.jQueryNodeList.dialog_share;
            target.find('p').html(this.languages.lastChange);
            target.find('.js-share-icons').show();
            visible ? target.show() : target.hide();
        },

        /**
         * 打开弹窗，积分不够
         */
        dialog_show_points_tips: function () {
            let message = this.languages.points_use_tips;
            this.dialog_show_fail(message);
        },

        // 打开弹窗 - 分享SKU类型
        dialog_show_sku: function (params) {
            $(elem).find('.geshop-187d-dialog-sku').show().find('.geshop-187d-dialog-sku-image img').attr('src', params.sku_image);
        },

        change_sign: function (currency) {
            var my_array_sign = window.my_array_sign ? window.my_array_sign : [];
            return my_array_sign[currency || 'USD'];
        },

        // 打开弹窗 - 通用奖品类型
        dialog_show_other: function (params) {
            var p1, p2 = '';
            var site_code = GESHOP_SITECODE.split('-')[0] || '';
            // type - 1 优惠券 2 积分 3 商品
            switch (params.type) {
                case 1:
                    // nomail 1免邮 0非免邮
                    if (params.coupon_json.no_mail == 1) {
                        p1 = this.languages.dialogFreeShipping;
                    } else {
                        // 优惠券类型，1：百分比，2:直减
                        if (params.coupon_json.coupon_type == 1) {
                            var youhuilv = params.coupon_json.youhuilv.split('-');
                            if (site_code == 'zf') {
                                if (youhuilv.length == 1) {
                                    p1 = this.languages.dialogCouponPercentZF.replace('XX', youhuilv[0]).replace('YY', '').replace('ZZ', '');
                                } else {
                                    if (params.coupon_json.currency == 'EUR' || params.coupon_json.currency == 'RUB') {
                                        p1 = this.languages.dialogCouponPercentZF.replace('XX', youhuilv[1]).replace('ZZ', this.change_sign(params.coupon_json.currency)).replace('YY', youhuilv[0]);
                                    } else {
                                        p1 = this.languages.dialogCouponPercentZF.replace('XX', youhuilv[1]).replace('YY', this.change_sign(params.coupon_json.currency)).replace('ZZ', youhuilv[0]);
                                    }
                                    // p1 = this.languages.dialogCouponPercentZF.replace('XX', youhuilv[1]).replace('YYZZ', '<span class="my_shop_price" data-currency="' + params.coupon_json.currency + '" data-orgp="' + youhuilv[0] + '">' + youhuilv[0] + '</span>');
                                }
                            } else if (site_code == 'rg') {
                                if (youhuilv.length == 1) {
                                    p1 = this.languages.dialogCouponPercentRG.replace('XX', youhuilv[0]).replace('YY', '').replace('ZZ', '');
                                } else {
                                    if (params.coupon_json.currency == 'EUR' || params.coupon_json.currency == 'RUB') {
                                        p1 = this.languages.dialogCouponPercentZF.replace(/XX/g, youhuilv[1]).replace(/YY/g, youhuilv[0]).replace(/ZZ/g, this.change_sign(params.coupon_json.currency));
                                    } else {
                                        p1 = this.languages.dialogCouponPercentZF.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(params.coupon_json.currency));
                                    }
                                    // p1 = this.languages.dialogCouponPercentRG.replace('XX', youhuilv[1]).replace('YYZZ', '<span class="my_shop_price" data-currency="' + params.coupon_json.currency + '" data-orgp="' + youhuilv[0] + '">' + youhuilv[0] + '</span>');
                                }
                            }

                        } else if (params.coupon_json.coupon_type == 2) {
                            var youhuilv = params.coupon_json.youhuilv.split('-');
                            if (site_code == 'zf') {
                                if (youhuilv.length == 1) {
                                    p1 = this.languages.dialogCouponDecreaseZF.replace('XX', youhuilv[0]).replace('YY', this.change_sign(params.coupon_json.currency)).replace('YYZZ', '').replace('ZZYY', '');
                                } else {
                                    if (params.coupon_json.currency == 'EUR' || params.coupon_json.currency == 'RUB') {
                                        p1 = this.languages.dialogCouponDecreaseZF.replace(/XX/g, this.change_sign(params.coupon_json.currency)).replace(/YY/, youhuilv[1]).replace(/YY/, youhuilv[0]).replace(/ZZ/g, this.change_sign(params.coupon_json.currency));
                                    } else {
                                        p1 = this.languages.dialogCouponDecreaseZF.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(params.coupon_json.currency));
                                    }
                                    // p1 = this.languages.dialogCouponDecreaseZF.replace(/XX/g, youhuilv[1]).replace('YYZZ', '<span class="my_shop_price" data-currency="' + params.coupon_json.currency + '" data-orgp="' + youhuilv[0] + '">' + youhuilv[0] + '</span>');
                                }
                            } else if (site_code == 'rg') {
                                if (youhuilv.length == 1) {
                                    p1 = this.languages.dialogCouponDecreaseRG.replace('XX', youhuilv[0]).replace('YY', this.change_sign(params.coupon_json.currency)).replace('YYZZ', '').replace('ZZYY', '');
                                } else {
                                    if (params.coupon_json.currency == 'EUR' || params.coupon_json.currency == 'RUB') {
                                        p1 = this.languages.dialogCouponDecreaseRG.replace(/XX/g, youhuilv[1]).replace(/YY/g, youhuilv[0]).replace(/ZZ/g, this.change_sign(params.coupon_json.currency));

                                    } else {
                                        p1 = this.languages.dialogCouponDecreaseRG.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(params.coupon_json.currency));

                                    }
                                    // p1 = this.languages.dialogCouponDecreaseRG.replace(/XX/g, youhuilv[1]).replace('YYZZ', '<span class="my_shop_price" data-currency="' + params.coupon_json.currency + '" data-orgp="' + youhuilv[0] + '">' + youhuilv[0] + '</span>');
                                }
                            }

                        }
                    }
                    break;
                case 2:
                    // 初始化积分字段
                    if (site_code == 'zf') {
                        p1 = this.languages.dialogPoints.replace(/XXX/g, params.points).replace(/YYY/g, 'Z');
                    } else if (site_code == 'rg') {
                        p1 = this.languages.dialogPoints.replace(/XXX/g, params.points).replace(/YYY/g, 'R');
                    }
                    break;
                default:
                    p1 = '';
                    break;
            }
            if (site_code == 'zf') {
                p2 = this.languages.dialogSuccessContent1;
            } else if (site_code == 'rg') {
                p2 = this.languages.dialogSuccessContent2;
            } else {
                p2 = '';
            }
            $(elem).find('.geshop-187d-dialog-other').show().find('p.success-content').html(p1 + p2);
            GEShopSiteCommon.renderCurrency();
        },

        // 更新剩余次数
        render_spins: function (left_times) {
            $(elem).find('.geshop-187d-spins').html(this.languages.spins.replace('XXX', '<span class="site-bold-strict">' + left_times + '</span>'));
        },

        /**
         * 渲染用户剩余的积分
         * @param {Number} points 积分
         */
        render_points: function (points) {
            this.jQueryNodeList.left_points.find('span').html(points);
            if (this.show_points_info == true) {
                this.jQueryNodeList.left_points.show();
            } else {
                this.jQueryNodeList.left_points.hide();
            }
        },

        /**
         * 获取积分
         * @returns {Number}
         */
        get_points: function () {
            if (this.jQueryNodeList.left_points) {
                const points = this.jQueryNodeList.left_points.find('span').html();
                return parseInt(points);
            } else {
                return 0;
            }
        },

        /**
         * 初始化分享？
         */
        init_share: function () {
            var shareParam = {
                url: $('[property="og:url"]').attr('content') || '',
                image: $('[property="og:image"]').attr('content') || '',
                title: $('[property="og:title"]').attr('content') || '',
                desc: $('[property="og:description"]').attr('content') || ''
            };
            var _this = this;

            function shareFinish(target) {

                var shareTypeMap = {
                    facebook: 1,
                    twitter: 2,
                    pinterest: 3,
                    messenger: 4,
                    line: 5,
                }

                var shareType = shareTypeMap[target];
                var act_code = $(elem).attr('data-actcode');
                // var user_token = _this.get_cookie('user-token') ? _this.get_cookie('user-token') : '64d7e43fda55d93a3d03d8ff260c701e';
                var user_token = _this.get_cookie('user-token') ? _this.get_cookie('user-token') : '';
                var share_channel = target;
                var url = GESHOP_INTERFACE.elf_webgame_share_prize.url;
                // var url = "http://www.elf.com.v0805.php5.egomsl.com/api/webgame-api/share-prize";
                var params = {
                    act_code: act_code,
                    user_token: user_token,
                    share_channel: share_channel,
                    website: _this.site_code_upper,
                    pipeline_code: _this.pipeline_code,
                    language: _this.lang
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: params,
                    dataType: 'jsonp',
                    success: function (res) {
                        if (res.status == 200) {
                            // 分享成功后状态设置
                            sessionStorage.setItem('IS_SHARE_AWARD_STATUS', 0);
                            // 分享成功后将剩余抽奖次数置+1
                            var left_time = $(elem).find('.geshop-187d-spins span').text() * 1;
                            _this.render_spins(left_time + 1);

                            // facebook
                            if (share_channel == 'facebook') {
                                // 分享成功提示
                                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.shareSuccessTips1);
                                // 若分享弹窗显示则关闭弹窗
                                $(elem).find('.geshop-187d-dialog-share').hide();
                            }

                        } else if (res.status == 607) {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.noChance);
                        } else {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(res.msg);
                        }
                    }
                });
            };

            $(elem).find('.js_share_btn').on('click', function () {
                // // 判断是否有分享机会
                // if (sessionStorage.getItem('IS_SHARE_AWARD_STATUS') == 2) {
                //     GEShopSiteCommon.dialog.message(_this.languages.noChance);
                //     return false;
                // }
                var shareType = $(this).attr('data-type');
                switch (shareType) {
                    case 'facebook':
                        var cfg = {
                            name: shareParam.title,
                            picture: shareParam.image,
                            link: shareParam.url,
                            caption: shareParam.title,
                            description: shareParam.desc
                        };
                        share.facebook.start(cfg, function () {
                            shareFinish(shareType);
                        });
                        break;
                    case 'pinterest':
                        var cfg = {
                            link: shareParam.url,
                            image: shareParam.image,
                            text: shareParam.desc
                        };
                        share.pinterest.start(cfg, function () {
                            shareFinish(shareType);
                        });
                        break;
                    case 'twitter':
                        var cfg = {
                            text: shareParam.desc,
                            link: shareParam.url
                        };
                        share.twitter.start(cfg, function () {
                            shareFinish(shareType);
                        });
                        break;
                    case 'google':
                        var cfg = {
                            text: shareParam.desc,
                            link: shareParam.url
                        };
                        share.google.start(cfg, function () {
                            shareFinish(shareType);
                        });
                        break;
                    default:
                        var cfg = {
                            text: shareParam.desc,
                            link: shareParam.url
                        };
                        share.line.start(cfg, function () {
                            shareFinish(shareType);
                        });
                        break;
                }
            });
        },

        /**
         * 获取用户的 token
         * @param {function} callback 获取回调
         */
        ajax_get_usertoken: function (callback) {
            var that = this;
            var url = GESHOP_INTERFACE.user_info.url;
            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    content: JSON.stringify({
                        lang: GESHOP_LANG,
                        pipeline: GESHOP_SITECODE
                    })
                },
                dataType: 'jsonp',
                success: function (res) {
                    // 已经登录执行回调，没登录则跳转
                    if (res.code === 0 && res.data.elf_user_token != '') {
                        callback && callback(res.data.elf_user_token);
                    } else {
                        // 没登录，重定向
                        that.handleRedirectLogin();
                    }
                },
                error: function () {
                    var token = that.get_cookie('user-token') || '';
                    callback && callback(token);
                }
            });
        },

        /**
         * 重定向
         */
        handleRedirectLogin: function () {
            var redirectUrl = window.location.href;
            var loginUrl = {
                'zf': (HTTPS_LOGIN_DOMAIN ? HTTPS_LOGIN_DOMAIN : '') + (typeof GESHOP_PIPELINE != 'undefined' && GESHOP_PIPELINE.toLowerCase()) + '/sign-up.html?ref=' + redirectUrl,
                'rg': (HTTPS_LOGIN_DOMAIN ? HTTPS_LOGIN_DOMAIN : '') + (typeof GESHOP_LANG != 'undefined' && GESHOP_LANG.toLowerCase()) + '/m-users-a-sign.htm?ref=' + redirectUrl
            };
            window.location.href = loginUrl[this.site_code];
        },

        /**
         * 使用积分兑换
         */
        handle_use_points: function () {
            $.ajax({
                url: '',
                method: 'POST',
                data: {},
                success: function (res) {
                    if (res.status == 200) {

                    }
                    // 积分不够
                    else if (res.status == 201) {

                    }
                },
                fail: function (error) {

                }
            });
        }
    }
    // 初始化语言包(根据lang抽取对应的包)
    this.helper.init_languages();

    // 绑定元素
    this.bind_trigger(options.elements);

    return this.helper;
}
