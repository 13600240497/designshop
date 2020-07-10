if (GESHOP_SITECODE && (GESHOP_SITECODE == 'zf-pc' || GESHOP_SITECODE == 'zf-wap')) {
    // window.geshop_share_appid = '966242223397117'
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '1396335280417835';
    window.tag = 'ra-5a38671bb83b79fe';
} else if (GESHOP_SITECODE && GESHOP_SITECODE == 'rg-wap') {
    // window.geshop_share_appid = '584881354898638'
    window.tag = 'ra-54c2151b31fb2710';
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '860992623979486';
} else if (GESHOP_SITECODE && GESHOP_SITECODE == 'rg-pc') {
    // window.geshop_share_appid = '584881354898638'
    window.tag = 'ra-54c2151b31fb2710';
    window.geshop_share_appid = $('[property="fb:app_id"]').attr('content') ? $('[property="fb:app_id"]').attr('content') : '860992623979486';
};
window.share = {
    facebook: {
        init: function (callback) {
            let _this = this;
            $('body').append('<div id="fb-root"></div>');
            window.fbAsyncInit = function () {
                FB.init({
                    appId: window.geshop_share_appid,
                    status: true,
                    cookie: true,
                    oauth: true,
                    xfbml: true,
                    version: 'v3.2'
                });
            };
            (function (d, s, id) {
                let js; let fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement(s);
                js.id = id;
                js.src = 'https://connect.facebook.net/en_US/sdk.js';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
            if (typeof callback == 'function') {
                callback();
            }
        },
        start: function (cfg, callback) {
            let _this = this;
            cfg = cfg || {};
            /* var shareParams = 'url=' + encodeURIComponent(window.location.href) + '&title=' + encodeURIComponent($('title').text()) + '&description=' +
                $("[name='description']").attr('content') + '&screenshot=' + $('[property="og:image"]').attr('content') + '&pubid=' + window.tag;

            var iWidth = 616, iHeight = 383, iTop = (window.screen.availHeight - 30 - iHeight) / 2,
                iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
                url =  '//api.addthis.com/oexchange/0.8/forward/facebook/offer?' + shareParams;
            window.open(url, '') */
            FB.ui({
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
                        callback();
                    }
                }
            });
        }
    },
    twitter: {
        init: function (callback) {
            let _this = this;
            $('body').append('<div id="twitter-root"></div>');
            window.twttr = (function (d, s, id) {
                let t; let js; let fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = 'https://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js, fjs);
                return window.twttr || (t = {
                    _e: [],
                    ready: function (f) {
                        t._e.push(f);
                    }
                });
            }(document, 'script', 'twitter-wjs'));
            if (typeof callback == 'function') {
                callback();
            }
        },
        start: function (cfg, callback) {
            let _this = this;
            cfg = cfg || {};
            let iWidth = 650; let iHeight = 430; let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
            let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
            let url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(cfg.text) + '&url=' + encodeURIComponent(cfg.link);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback();
            }
        }
    },
    google: {
        init: function (callback) {
            let _this = this;
            (function () {
                let e = document.createElement('script'); let fjs = document.getElementsByTagName('script')[0];
                e.type = 'text/javascript';
                e.src = 'https://apis.google.com/js/platform.js';
                e.async = true;
                fjs.parentNode.insertBefore(e, fjs);
            }());
            if (typeof callback == 'function') {
                callback();
            }
        },
        start: function (cfg, callback) {
            let _this = this;
            cfg = cfg || {};
            let iWidth = 510; let iHeight = 550; let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
            let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
            let url = 'https://plus.google.com/share?url=' + encodeURIComponent(cfg.link) + '&t=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback();
            }
        }
    },
    pinterest: {
        start: function (cfg, callback) {
            let _this = this;
            cfg = cfg || {};
            let iWidth = 800; let iHeight = 650; let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
            let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
            let url = 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent(cfg.link) + '&media=' + encodeURIComponent(cfg.image) + '&description=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback();
            }
        }
    },
    line: {
        start: function (cfg, callback) {
            let _this = this;
            cfg = cfg || {};
            let iWidth = 800; let iHeight = 650; let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
            let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
            let url = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(cfg.link) + '&description=' + encodeURIComponent(cfg.text);
            window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
            if (typeof callback == 'function') {
                callback();
            }
        }
    }
};
window.share.facebook.init();

/**
 * 转盘核心函数
 * @param {jQueryNode} elem 
 * @param {Object} options 
 */
let Geshop_turntable_helper = function (elem, options) {

    

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

    /**
     * 数据存储
     */
    this.helper = {
        is_debug_mode: window.location.search.includes('is_debug'), // Debug模式下，各个操作都会提示alert，方便app里面调试
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
        loginUrl: {
            'zf': (typeof HTTPS_LOGIN_DOMAIN !== 'undefined' ? HTTPS_LOGIN_DOMAIN : '') + '/' + (typeof JS_LANG_CODE !== 'undefined' ? JS_LANG_CODE : '') + 'sign-up.html?ref=',
            'rg': (typeof DOMAIN_LOGIN !== 'undefined' ? DOMAIN_LOGIN : '') + '/m-users-a-sign.html?ref='
        },
        show_points_info: false, // 是否展示积分信息
        use_point: 0, // 是否允许使用积分
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
            /* Object.keys(GESHOP_TUNRNTABLE_WAP_LANGUAGES).map(function (key) {
                this.languages[key] = GESHOP_TUNRNTABLE_WAP_LANGUAGES[key][GESHOP_LANG];
            }.bind(this)); */
            let langKey = GESHOP_LANG || 'en';
            Object.keys(GESHOP_TUNRNTABLE_WAP_LANGUAGES).map(function (key) {
                this.languages[key] = GESHOP_TUNRNTABLE_WAP_LANGUAGES[key][langKey] || GESHOP_TUNRNTABLE_WAP_LANGUAGES[key]['en'];
            }.bind(this));
        },

        // isAlert 0 不弹窗
        // isAlert 1 弹窗
        webLoginAction: function (webLoginUrl, isAlert) {
            if (GESHOP_PLATFORM != 'app') {
                window.location.href = webLoginUrl + window.location.href;
            } else {
                let site = 0;
                site = GESHOP_SITECODE == 'zf-app' ? 1 : site;
                site = GESHOP_SITECODE == 'rg-app' ? 2 : site;
                site = GESHOP_SITECODE == 'rw-app' ? 3 : site;

                switch (site) {
                case 1:
                    window.location.href = 'webAction://login?callback=appUserInfo()&isAlert=' + isAlert;
                    break;
                case 2:
                    window.location.href = 'webAction://checkUserInfo?callback=appUserInfo()&isAlert=' + isAlert;

                    break;
                case 3:
                    window.location.href = webLoginUrl;
                }
            }
        },

        // 获取 cookie 方法
        get_cookie: function (name) {
            let arr; let reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
            if (arr = document.cookie.match(reg)) {
                return arr[2];
            } else {
                return null;
            }
        },

        // app check now 跳转
        // zaful - actionType 9 coupon列表页  actionType 18 积分列表页
        // rg - actionType 10 coupon列表页  actionType 19 积分列表页
        geshopUserCenterUrlToApp: function (link, actionType) {
            let urlconfig = {
                'zf-app': {
                    '9': 'zaful://action?actiontype=9&source=deepLink',
                    '18': 'zaful://action?actiontype=18&source=deepLink',
                    '6': 'zaful://action?actiontype=9&source=deepLink'
                    // '6': 'zaful://action?actiontype=6&url=1,21&name=UserStyle&source=deeplink'
                },
                'rg-app': {
                    '19': 'rosegal://action?actiontype=19&name=R Points&source=deeplink',
                    '10': 'rosegal://action?actiontype=10&name=couponList&source=deeplink'
                }
            };
            if (GESHOP_PLATFORM || GESHOP_SITECODE) {
                if (GESHOP_PLATFORM != 'app') {
                    return link;
                } else {
                    return urlconfig[GESHOP_SITECODE][actionType];
                }
            } else {
                return link;
            }
        },

        // app分享
        appShare: function (cfg, callback) {
            let urlconfig = {
                'zf-app': 'webAction://sharing?shareUrl=' + cfg.url + '&imageUrl=' + cfg.image + '&callback=' + callback,
                'rg-app': 'webAction://sharing?shareUrl=' + cfg.url + '&shareContent=' + cfg.desc + '&imageUrl=' + cfg.image + '&callback=' + callback
            };
            if (GESHOP_SITECODE) {
                return urlconfig[GESHOP_SITECODE];
            }
        },

        // 存储是否可通过分享增加抽奖次数状态
        set_share_award_status: function (status) {
            sessionStorage.setItem('IS_SHARE_AWARD_STATUS', status);
        },

        delete_award_sessionstorage: function () {
            sessionStorage.removeItem('IS_SHARE_AWARD_STATUS');
        },

        show_loading_style: function () {
            $(elem).find('.js_gs-dialog-U000188-container').show();
        },

        // 将抽奖按钮置为可用状态
        set_dolottery_btn_enable: function () {
            $(elem).find('.js-start-draw').attr('data-isdrawing', 0);
        },

        hide_loading_style: function () {
            $(elem).find('.js_gs-dialog-U000188-container').hide();
        },

        auto_hide_loading_style: function () {
            setTimeout(function () {
                if ($(elem).find('.js_gs-dialog-U000188-container').is(':visible')) {
                    $(elem).find('.js_gs-dialog-U000188-container').hide();
                }
            }, 15000);
        },

        // 绑定事件
        init_events: function () {
            // 点击指针
            $(elem).on('click', '.js-start-draw', function (target) {
                // 如果接口没初始化完成点击抽奖无效
                let is_loaded = $(target.currentTarget).data('loaded');
                if (!is_loaded) return false;

                // 正在抽奖状态不可连续点击
                if ($(target.currentTarget).attr('data-isdrawing') == 1) return false;

                // 0 - 未开始 1 - 进行中 2 - 已结束
                switch (this.countdown_status) {
                case 0:
                    this.dialog_show_fail_not_start();
                    break;
                case 1:
                    // 将抽奖按钮置为正在抽奖状态
                    $(target.currentTarget).attr('data-isdrawing', 1);

                    // show loading
                    this.show_loading_style();

                    // 超过15s接口还没请求完也关闭遮罩层
                    this.auto_hide_loading_style();

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
                        // 607 - 积分不够了
                        this.ajax_get_spins(function (spins, status, left_points) {
                            // hide loading
                            this.hide_loading_style();

                            // 未登陆
                            if (this.asyncData.is_login == 0) {
                                this.webLoginAction(this.loginUrl[this.site_code], 1);
                                this.set_dolottery_btn_enable();
                                // GEShopSiteCommon.appLogin(1);
                            } else {
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
                                } else if (status == 601 || status == 607) {
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
                                else if (status == 403) {
                                    GEShopSiteCommon.dialog.message('please log in first.');
                                    this.webLoginAction(this.loginUrl[this.site_code], 1);
                                }
                                else if (status == 606) {
                                    this.dialog_show_fail(this.languages.unlucky);
                                }
                                else if (status == 603) {
                                    this.dialog_show_fail(this.languages.ended);
                                }
                                else if (status == 602 || status == 604 || status == 608) {
                                    this.dialog_show_fail(this.languages.fail);
                                }
                                else if (status == 605) {
                                    GEShopSiteCommon.dialog.message('User information is invalid.');
                                    this.set_dolottery_btn_enable();
                                }
                                else if (status == 500) {
                                    GEShopSiteCommon.dialog.message('Invalid request！');
                                    this.set_dolottery_btn_enable();
                                }
                            }
                        }.bind(this), user_token);
                    }.bind(this), function (error) {
                        // 将抽奖按钮置为正在抽奖状态
                        this.hide_loading_style();
                        this.set_dolottery_btn_enable();
                    }.bind(this));

                    break;
                default:
                    this.dialog_show_fail(this.languages.ended);
                    break;
                }
            }.bind(this));

            // 点击规则
            $(elem).on('click', '.js-show-rule-dialog', function (target) {
                $(elem).find('.geshop-188d-dialog-rule').show();
            });

            // 关闭弹出层
            $(elem).on('click', '.js-close-dialog', function (target) {
                // 隐藏弹窗
                let className = '.' + $(target.currentTarget).attr('data-tag');
                $(elem).find(className).hide();
                // 重置动画转盘
                this.animateReset();
            }.bind(this));

            // Check Now
            $(elem).on('click', '.js-check-now', function (target) {
                let that = this;
                let zfActionType = 0;
                let rgActionType = 0;
                // prize_type 1 优惠劵
                // prize_type 2 积分
                // prize_type 3 商品
                if (that.doLotteryData.prize_type == 2) {
                    zfActionType = 18;
                    rgActionType = 19;
                } else if (that.doLotteryData.prize_type == 1) {
                    zfActionType = 9;
                    rgActionType = 10;
                } else {
                    zfActionType = 6;
                    rgActionType = 10;
                }

                // 跳转
                let site_code = GESHOP_SITECODE ? GESHOP_SITECODE.split('-')[0] : '';
                let platform = GESHOP_PLATFORM || 'wap';

                if (site_code == 'zf') {
                    var url = '';
                    if (this.doLotteryData.prize_type == 1 || this.doLotteryData.prize_type == 3) {
                        url = (typeof HTTPS_USER_DOMAIN !== 'undefined' ? HTTPS_USER_DOMAIN : '') + '/' + GESHOP_PIPELINE.toLowerCase() + '/m-users-a-my_coupon.htm';
                    } else if (this.doLotteryData.prize_type == 2) {
                        url = (typeof HTTPS_USER_DOMAIN !== 'undefined' ? HTTPS_USER_DOMAIN : '') + '/' + GESHOP_PIPELINE.toLowerCase() + '/m-users-a-points_record.htm';
                    }
                    window.location.href = this.geshopUserCenterUrlToApp(url, zfActionType);
                }
                // RG PC端的DOMAIN_USER没区分语言，M端区分了语言
                else if (site_code == 'rg') {
                    var url = '';
                    if (this.doLotteryData.prize_type == 1 || this.doLotteryData.prize_type == 3) {
                        url = (typeof DOMAIN_USER !== 'undefined' ? DOMAIN_USER : '') + '/m-users-a-coupon.html';
                    } else if (this.doLotteryData.prize_type == 2) {
                        url = (typeof DOMAIN_USER !== 'undefined' ? DOMAIN_USER : '') + '/m-users-a-points_record.html';
                    }
                    window.location.href = this.geshopUserCenterUrlToApp(url, rgActionType);
                }
            }.bind(this));

            // 关闭弹出层 - 活动未开始倒计时
            $(elem).on('click', '.js-close-dialog-not-start', function (target) {
                // 隐藏弹窗
                let className = '.' + $(target.currentTarget).attr('data-tag');
                $(elem).find(className).addClass('is-hidden').removeClass('is-visible');
                // 重置动画转盘
                this.animateReset();
            }.bind(this));
        },

        // 倒计时显示剩余 天 时 分 秒
        countdown: function (left_time) {
            let days = parseInt(left_time / 60 / 60 / 24, 10); // 剩余天数
            let hours = parseInt(left_time / 60 / 60 % 24, 10); // 剩余小时
            let minutes = parseInt(left_time / 60 % 60, 10); // 剩余分钟
            let seconds = parseInt(left_time % 60, 10);
            let checkTime = function (t) {
                if (t < 10) {
                    t = '0' + t;
                }
                return t;
            };
            days = checkTime(days);
            hours = checkTime(hours);
            minutes = checkTime(minutes);
            seconds = checkTime(seconds);
            let html = '<i>' + days + '</i>' + ': ' + '<i>' + hours + '</i>' + ': ' + '<i>' + minutes + '</i>' + ': ' + '<i>' + seconds + '</i>';
            return html;
        },

        // 初始化倒计时
        init_coutdown: function () {
            let startTime = this.asyncData.start_timestamp * 1;
            let endTime = this.asyncData.end_timestamp * 1;

            // 获取状态
            this.countdown_status = this.check_time(startTime, endTime);
            // 状态对照className
            let classMap = ['is-ready', 'is-drawing', 'is-ended'];
            // 获取对应要计算的时间戳
            let count_time = this.countdown_status == 0 ? startTime : this.countdown_status == 1 ? endTime : 0;
            // 倒计时开始
            clearInterval(intervalID);
            var intervalID = setInterval(function () {
                // 剩余秒数
                let second = this.get_second(count_time);
                this.left_second = second;

                // 右侧中奖名单显示剩余倒计时
                $(elem).find('.geshop-188d-list-warning .time').html(this.countdown(second));

                // 弹窗显示剩余倒计时
                $(elem).find('.geshop-188d-dialog-fail-not-start .geshop-188d-dialog-body p.title').html(this.languages.startIn);
                $(elem).find('.geshop-188d-dialog-fail-not-start .geshop-188d-dialog-body p.time').html(this.countdown(second));

                if (second <= 0) {
                    clearInterval(intervalID);
                    // 如果还没到已结束阶段则继续跑
                    if (this.countdown_status == 0) {
                        this.init_coutdown();
                    } else if (this.countdown_status >= 1) {
                        $(elem).removeClass('is-ready').removeClass('is-ended').addClass('is-drawing');
                        $(elem).find('.geshop-188d-dialog-fail-not-start').removeClass('is-visible').addClass('is-hidden');
                        this.countdown_status = 1;
                    }
                } else {
                    $(elem).addClass(classMap[this.countdown_status]);
                }
            }.bind(this), 1000);
        },

        // 初始化中奖名单，每5分钟获取名单列表
        init_luck_list: function () {
            this.ajax_get_lucky();
            // setInterval(function() {
            //     this.ajax_get_lucky()
            // }.bind(this), 1000 * 60 * 5);
        },

        // 检查活动状态 0 未开始  1 进行中 2 已结束
        check_time: function (startTime, endTime) {
            let now = Date.parse(new Date()) / 1000;
            if (now < startTime) {
                return 0;
            } else if (now > startTime && now < endTime) {
                return 1;
            } else {
                return 2;
            }
        },

        // 根据时间戳得出秒数
        get_second: function (timestamp) {
            let now = Date.parse(new Date()) / 1000;
            return timestamp - now;
        },

        // 获取次数
        ajax_get_spins: function (callback, user_token) {
            let _this = this;
            let act_code = $(elem).attr('data-actcode');
            let share_code = _this.asyncData.share_code;
            let url = GESHOP_INTERFACE.elf_webgame_do_lottery.url;
            let params = {
                act_code: act_code,
                user_token: user_token,
                share_code: share_code,
                website: _this.site_code_upper,
                pipeline_code: _this.pipeline_code,
                language: _this.lang
            };

            $.ajax({
                type: 'GET',
                url: url,
                data: params,
                dataType: 'jsonp',
                success: function (res) {
                    // APP Debug调试信息
                    _this.is_debug_mode && alert('ajax_get_spins= ' + JSON.stringify(res));

                    _this.doLotteryData = res.data;
                    var left_times = res.data.left_times ? res.data.left_times : 0;
                    var left_times = res.data.left_times ? res.data.left_times : 0;
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
            let data = this.doLotteryData;
            let res = {
                has_win: data.has_win, // 是否中奖
                type: data.prize_type, // 奖品类型：1优惠券 2积分 3商品
                points: data.points, // 积分数量
                goods_sku: data.goods_sku, // 中奖商品sku
                coupon_json: data.coupon_json, // 优惠券信息
                sku_image: data.goods_img,
                bonusIndex: data.prize_id // 奖品ID
            };

            this.animation(res);
        },

        /**
         * 初始化抽奖活动数据
         * @param {Boolean} render_points 是否渲染积分 
         */
        init_lottery_info: function ({ render_points = true }) {
            let _this = this;
            let act_code = $(elem).attr('data-actcode');
            let user_token = _this.get_cookie('user-token') ? _this.get_cookie('user-token') : '';
            let url = GESHOP_INTERFACE.elf_webgame_info.url;
            let params = {
                act_code: act_code,
                user_token: user_token,
                website: _this.site_code_upper,
                pipeline_code: _this.pipeline_code,
                language: _this.lang
            };

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
                    let left_times = res.data.left_times ? res.data.left_times : 0;
                    _this.render_spins(left_times);

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
                        $(elem).find('.geshop-188d-spins').hide();
                    }
                }
            });
        },

        // 中奖名单奖品信息初始化
        init_prize_info: function (res) {
            let html = '';
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
            let default_list = [
                { 'email': 'tomer***@gmail.com', 'created_time': '', 'prize_type': 2, 'points': '100', 'win_time': '' },
                {
                    'email': 'jacker***@gmail.com',
                    'created_time': '',
                    'prize_type': 2,
                    'points': '100',
                    'win_time': ''
                },
                {
                    'email': 'markere***@gmail.com',
                    'created_time': '',
                    'prize_type': 2,
                    'points': '100',
                    'win_time': ''
                },
                { 'email': 'dfdf***@gmail.com', 'created_time': '', 'prize_type': 2, 'points': '100', 'win_time': '' },
                {
                    'email': 'fvdffg***@gmail.com',
                    'created_time': '',
                    'prize_type': 2,
                    'points': '100',
                    'win_time': ''
                },
                {
                    'email': 'trttyu***@gmail.com',
                    'created_time': '',
                    'prize_type': 2,
                    'points': '100',
                    'win_time': ''
                },
                { 'email': 'drfg***@gmail.com', 'created_time': '', 'prize_type': 2, 'points': '100', 'win_time': '' },
                {
                    'email': 'hgghdf***@gmail.com',
                    'created_time': '',
                    'prize_type': 2,
                    'points': '100',
                    'win_time': ''
                },
                { 'email': 'fghf***@gmail.com', 'created_time': '', 'prize_type': 2, 'points': '100', 'win_time': '' },
                { 'email': 'rtry***@gmail.com', 'created_time': '', 'prize_type': 2, 'points': '100', 'win_time': '' }
            ];
            $(elem).find('.geshop-188d-list-uname ul').html('');
            let _this = this;
            let act_code = $(elem).attr('data-actcode');
            if (win_list && win_list.length) {
                win_list.map(function (row) {
                    $(elem).find('.geshop-188d-list-uname ul').append('<li>' + row.email + '<span>' + _this.init_prize_info(row) + '</span></li>');
                });
                // 中奖名单动画
                this.animateList();
            } else if (act_code == 0) {
                default_list.map(function (row) {
                    $(elem).find('.geshop-188d-list-uname ul').append('<li>' + row.email + '<span>' + _this.init_prize_info(row) + '</span></li>');
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
            let total = $(elem).attr('data-prizeamount') * 1; // 转盘奖品数量
            let repeat = 360 * 6;
            let eachReg = (360 / total);
            let bonusReg = eachReg * res.bonusIndex + repeat - eachReg / 2;
            $(elem).addClass('geshop-animate-rotate');
            $(elem).find('.geshop-188d-pointer img').css('transform', 'rotate(' + bonusReg + 'deg)').css('transition', 'all 5s ease-in-out');
            setTimeout(function () {
                // type - 1 优惠券 2 积分 3 商品
                switch (res.type) {
                case 3:
                    this.dialog_show_sku(res);
                    break;
                case 9:
                    break;
                default:
                    this.dialog_show_other(res);
                    break;
                }
            }.bind(this), 5000);
        },

        animateReset: function () {
            $(elem).find('.geshop-188d-pointer img').css('transform', '').css('transition', 'none');
        },

        animateList: function () {
            let animate_wraper = $(elem).find('.geshop-188d-list-uname');
            let animate_target = animate_wraper.find('ul');
            let top = 0;

            function up () {
                animate_target.animate({
                    top: (top - 30) + 'px'
                }, 1000, function () {
                    animate_target.css({ top: '0px' }).find('li:first').appendTo(animate_target);
                    up();
                });
            }

            if (animate_wraper.height() <= animate_target.height()) {
                up();
            }
            // var dom = $('.geshop-188d-list-uname');
            // var container = dom.find('ul');
            // var top = 0;
            // setInterval(function() {
            //     var d_height = dom.height();
            //     var c_height = container.height();
            //     top = top - 8 - 22;
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
            $(elem).find('.geshop-188d-dialog-fail').show().find('p.title').html(msg);
            $(elem).find('.js-start-draw').attr('data-isdrawing', 0);
        },

        // 打开弹窗 - 失败类型
        dialog_show_fail_not_start: function () {
            $(elem).find('.geshop-188d-dialog-fail-not-start').addClass('is-visible').removeClass('is-hidden');
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
            $(elem).find('.geshop-188d-dialog-sku').show().find('.geshop-188d-dialog-sku-image img').attr('src', params.sku_image);
        },

        change_sign: function (currency) {
            let my_array_sign = window.my_array_sign ? window.my_array_sign : [];
            return my_array_sign[currency || 'USD'];
        },

        // 打开弹窗 - 通用奖品类型
        dialog_show_other: function (params) {
            let p1; let p2 = '';
            let site_code = GESHOP_SITECODE.split('-')[0] || '';
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
                            }
                        } else if (site_code == 'rg') {
                            if (youhuilv.length == 1) {
                                p1 = this.languages.dialogCouponPercentRG.replace('XX', youhuilv[0]).replace('YY', '').replace('ZZ', '');
                            } else {
                                if (params.coupon_json.currency == 'EUR' || params.coupon_json.currency == 'RUB') {
                                    p1 = this.languages.dialogCouponPercentRG.replace('XX', youhuilv[1]).replace('ZZ', this.change_sign(params.coupon_json.currency)).replace('YY', youhuilv[0]);
                                } else {
                                    p1 = this.languages.dialogCouponPercentRG.replace('XX', youhuilv[1]).replace('YY', this.change_sign(params.coupon_json.currency)).replace('ZZ', youhuilv[0]);
                                }
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
                                    // p1 = this.languages.dialogCouponDecreaseZF.replace(/XX/g, youhuilv[1]).replace(/YY/g, youhuilv[0]).replace(/ZZ/g, this.change_sign(params.coupon_json.currency));
                                } else {
                                    p1 = this.languages.dialogCouponDecreaseZF.replace(/XX/g, youhuilv[1]).replace(/ZZ/g, youhuilv[0]).replace(/YY/g, this.change_sign(params.coupon_json.currency));
                                }
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
            $(elem).find('.geshop-188d-dialog-other').show().find('p.success-content').html(p1 + p2);
        },

        /**
         * 更新剩余次数
         * @param {Number} left_times 
         */
        render_spins: function (left_times) {
            const content = this.languages.spins.replace('XXX', '<span class="site-bold-strict">' + left_times + '</span>');
            this.jQueryNodeList.spins.html(content);
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

        init_share: function () {
            let shareParam = {
                url: $('[property="og:url"]').attr('content') || '',
                image: $('[property="og:image"]').attr('content') || '',
                title: $('[property="og:title"]').attr('content') || '',
                desc: $('[property="og:description"]').attr('content') || ''
            };
            let _this = this;

            function shareFinish (target) {
                let shareTypeMap = {
                    facebook: 1,
                    twitter: 2,
                    pinterest: 3,
                    messenger: 4,
                    line: 5
                };

                let shareType = shareTypeMap[target];
                let act_code = $(elem).attr('data-actcode');
                let user_token = _this.get_cookie('user-token') ? _this.get_cookie('user-token') : '';
                let share_channel = target || 'facebook';
                let url = GESHOP_INTERFACE.elf_webgame_share_prize.url;
                let params = {
                    act_code: act_code,
                    user_token: user_token,
                    share_channel: share_channel,
                    website: _this.site_code_upper,
                    pipeline_code: _this.pipeline_code,
                    language: _this.lang
                };
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
                            let left_time = $(elem).find('.geshop-188d-spins span').text() * 1;
                            _this.render_spins(left_time + 1);

                            // facebook
                            if (share_channel == 'facebook') {
                                // 分享成功提示
                                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.shareSuccessTips1);
                                // 若分享弹窗显示则关闭弹窗
                                _this.dialog_show_share(false);
                            }
                        } else if (res.status == 607) {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.noChance);
                        } else {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(res.msg);
                        }
                    }
                });
            };
            // APP分享回调
            window.appShareFinish = function (share_channel) {
                let act_code = $(elem).attr('data-actcode');
                let user_token = _this.get_cookie('user-token') ? _this.get_cookie('user-token') : '';
                var share_channel = share_channel || 'facebook';
                let url = GESHOP_INTERFACE.elf_webgame_share_prize.url;
                let params = {
                    act_code: act_code,
                    user_token: user_token,
                    share_channel: share_channel,
                    website: _this.site_code_upper,
                    pipeline_code: _this.pipeline_code,
                    language: _this.lang
                };
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
                            let left_time = $(elem).find('.geshop-188d-spins span').text() * 1;
                            _this.render_spins(left_time + 1);
                            // facebook
                            if (share_channel == 'facebook') {
                                // 分享成功提示
                                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.shareSuccessTips1);
                                // 若分享弹窗显示则关闭弹窗
                                _this.dialog_show_share(false);
                            }
                        } else if (res.status == 607) {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(_this.languages.noChance);
                        } else {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(res.msg);
                        }
                    },
                    error: function (err) {
                    }
                });
            };

            // APP分享按钮
            $(elem).find('.js_app_share_btn').on('click', function () {
                let url = $('[property="og:url"]').attr('content') || '';
                url = encodeURIComponent(url);
                let image = $('[property="og:image"]').attr('content') || '';
                let title = $('[property="og:title"]').attr('content') || '';
                let desc = $('[property="og:description"]').attr('content') || '';
                let cfg = {
                    url: url,
                    image: image,
                    title: title,
                    desc: desc
                };
                window.location.href = _this.appShare(cfg, 'appShareFinish()');
            });

            $(elem).find('.js_share_btn').on('click', function () {
                let shareType = $(this).attr('data-type');
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
        ajax_get_usertoken: function (callback, error) {
            let that = this;
            let url = GESHOP_INTERFACE.user_info.url;
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
                    console.log(that);
                    // 调试模式
                    that.is_debug_mode && alert('ajax_get_usertoken= ' + JSON.stringify(res));

                    // 已经登录执行回调
                    if (res.code === 0 && res.data.elf_user_token != '') {
                        callback && callback(res.data.elf_user_token);
                    }
                    // 没登录则跳转
                    else {
                        error && error();
                        that.webLoginAction(that.loginUrl[that.site_code], 1);
                        that.set_dolottery_btn_enable();
                    }
                },
                error: function () {
                    let token = that.get_cookie('user-token') || '';
                    callback && callback(token);
                }
            });
        }
    };

    // 初始化语言包(根据lang抽取对应的包)
    this.helper.init_languages();

    // 绑定元素
    this.bind_trigger(options.elements);
    
    return this.helper;
};
