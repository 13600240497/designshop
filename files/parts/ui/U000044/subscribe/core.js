/**
 * 订阅按钮核心
 * @description
 * 目前用于：
 * 首页：
 * U000079/subscribe (PC端)
 * 活动页：
 * U000044/subscribe (PC端)
 * U000045/subscribe (M端)
 * @author Cullen
 * @version v1.0
 * @document https://docs.qq.com/doc/DTUJNaUVpR1ZZcmRM
 */

/**
 * 获取设备端
 * @returns {String} [pc/wap/ios/android]
 */
const get_platform = () => {
    if (GESHOP_PLATFORM == 'app') {
        const u = window.navigator.userAgent;
        const isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // 根据输出结果true或者false来判断ios终端
        const isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; // 如果输出结果是true就判定是android终端或者uc浏览器
        if (isIOS == true) {
            return 'ios';
        }
        if (isAndroid == true) {
            return 'android';
        }
    } else {
        return GESHOP_PLATFORM;
    }
};

/**
 * 获取用户ID
 * @returns {String}
 */
const get_user_id = () => {
    try {
        const user_id = GEShopSiteCommon.getCookie('WEBF-user_id');
        return user_id || '';
    } catch (err) {
        return '';
    }
};

/**
 * 检查用户ID在cookie里面是否记录了的已经订阅
 * @param {String} component_id
 * @param {String} user_id
 */
const get_user_was_subscribe = (component_id, user_id) => {
    if (window.localStorage) {
        const key = `geshop_u000044_user_id_${component_id}`;
        const value = window.localStorage[key] || '';
        return (value == user_id && user_id != '');
    } else {
        return false;
    }
};

/**
 * 记录已经订阅的用户ID
 * @param {String} component_id
 * @param {String} user_id
 */
const set_user_was_subscribe = (component_id, user_id) => {
    if (window.localStorage) {
        const key = `geshop_u000044_user_id_${component_id}`;
        window.localStorage[key] = user_id;
    }
};

/**
 * 检查时间区间
 * @param {timestamp} start 
 * @param {timestamp} end 
 * @param {timestamp} current 
 * @returns {Number} 0/1/2
 */
const check_time_range = (start, end, current) => {
    if (current < start) {
        return 0;
    }
    if (current > end) {
        return 2;
    }
    return 1;
};

/**
 * 检查是否已经登录
 */
const check_login_status = () => {
    const user_id = get_user_id();
    return new Promise((resolve, reject) => {
        if (!user_id) {
            redirection();
            return reject(new Error('login'));
        } else {
            resolve();
        }
    });
};

/**
 * 重定向
 * @param {String} platform 设备类型
 */
const redirection = (platform = get_platform()) => {
    // 判断是否手机
    if (platform == 'ios' || platform == 'android') {
        window.location.href = 'webAction://login?callback=appUserInfo()&isAlert=1';
    } else {
        const site_code = (typeof GESHOP_SITECODE !== 'undefined') ? GESHOP_SITECODE.split('-')[0] : '';
        const redirectUrl = window.location.href;
        const loginUrl = {
            'zf': (window.HTTPS_LOGIN_DOMAIN ? window.HTTPS_LOGIN_DOMAIN : '') + '/' + (typeof GESHOP_PIPELINE != 'undefined' && GESHOP_PIPELINE.toLowerCase()) + '/sign-up.html?ref=' + redirectUrl,
            'rg': (window.HTTPS_LOGIN_DOMAIN ? window.HTTPS_LOGIN_DOMAIN : '') + (typeof GESHOP_LANG != 'undefined' && GESHOP_LANG.toLowerCase()) + '/m-users-a-sign.htm?ref=' + redirectUrl
        };
        window.location.href = loginUrl[site_code].replace('com//', 'com/');
    }
};

/**
 * IOS获取app是否开启了推送权限
 * @returns {Promise}
 */
const ios_check_push = () => {
    return new Promise((resolve, reject) => {
        if (window.ios_bridge) {
            window.ios_bridge.callHandler('nativeMethod', {
                'action': '5',
                'params': ''
            }, function (response) {
                resolve(response);
            });
        }
    });
};

/**
 * 安卓检查权限
 */
const android_check_auth = (component_id, callback) => {
    // app 6.1.0以上版本用到
    if (window.gwAndroid) {
        const json = JSON.stringify({
            'action': '5', // 方法类型
            'params': '', // 没有参数，可不传或传空
            'callback': `android_callback_${component_id}` // 没有回调方法，可以不传或传空
        });
        eval(`window.android_callback_${component_id} = callback;`);
        window.gwAndroid.nativeMethod(json);
    }
};

// ios 初始化webview通信
const setupWebViewJavascriptBridge = (callback) => {
    if (window.WebViewJavascriptBridge) { return callback(WebViewJavascriptBridge); }
    if (window.WVJBCallbacks) { return window.WVJBCallbacks.push(callback); }
    window.WVJBCallbacks = [callback];
    let WVJBIframe = document.createElement('iframe');
    WVJBIframe.style.display = 'none';
    WVJBIframe.src = 'https://__bridge_loaded__';
    document.documentElement.appendChild(WVJBIframe);
    setTimeout(function () {
        document.documentElement.removeChild(WVJBIframe);
    }, 0);
};

/**
 * 暴露
 */
export default {
    props: ['data'],
    data () {
        return {
            /**
             * 0=未开始
             * 1=正在开始
             * 2=已经结束
             */
            time_status: 0,
            is_subscribe: false, // 是否已经订阅
            timer: null,
            dialog: {
                // 引导下载弹窗
                download: {
                    visible: false
                },
                // 没有推送权限的弹窗
                auth: {
                    visible: false
                }
            }
        };
    },

    computed: {
        lang () {
            return this.$lang;
        },
        // 返回按钮的文案
        text () {
            switch (this.time_status) {
            case 0:
                return this.data.ready_text || '活动订阅';
            case 2:
                return this.data.disabled_text || '超出活动时间';
            default:
                if (this.is_subscribe == true) {
                    return this.data.clicked_text || '已订阅';
                } else {
                    return this.data.unclick_text || '活动订阅';
                }
            }
        },
        // 返回按钮的class Name
        activityClass () {
            const className = ['subscribe'];
            switch (this.time_status) {
            case 0:
                className.push('ready');
                break;
            case 2:
                className.push('disabled');
                break;
            default:
                if (this.is_subscribe == true) {
                    className.push('clicked');
                } else {
                    className.push('unclick');
                }
            }
            // 是否开启了动画
            if (Number(this.data.is_animate) == 1) {
                className.push('animate');
            }
            return className.join(' ');
        },
        // 埋点信息，只有在unclick的状态下才能展示
        logsss () {
            let params = {};
            if (this.time_status == 1 && this.is_subscribe == false) {
                const currentPageId = this.$root.pageId;
                const pageInstanceId = this.$root.pageInstanceId;
                const bindPageId = this.data.page_id;
                const bindPageName = this.data.page_name;
                params = {
                    'pm': 'mbu',
                    'p': `gs-${currentPageId}-${pageInstanceId}`,
                    'ubcta': {
                        'activity_id': bindPageId,
                        'activity_name': bindPageName,
                        'endtime': this.data.end_time || ''
                    },
                    'x': 'STE'
                };
            }
            return JSON.stringify(params).replace(/"/g, '\'');
        },
        // 从cookie中获取安卓回调信息
        android_response () {
            const component_id = this.$root.pageInstanceId;
            const value = GEShopSiteCommon.getCookie(`geshop-subscribe-${component_id}`);
            if (value == 1) {
                this.open_dialog('auth', true);
            }
        },
        // 去下载XX APP
        label_download_app_site () {
            return this.$lang('download_app_site').replace('XX', 'Zaful');
        },
        // 订阅成功的提示，wap和app的语言包取值有区别
        label_subscribe_success () {
            if (get_platform() == 'ios' || get_platform() == 'android') {
                return this.$lang('sub_success_app');
            } else {
                return this.$lang('subscribe_success');
            }
        }
    },

    watch: {
        // 当时间过期了，销毁timer
        time_status (val) {
            if (val == 2) {
                clearInterval(this.timer);
            }
        }
    },

    methods: {
        /**
         * 点击订阅
         */
        async handle_click () {
            // 检查可点击状态
            if (this.time_status != 1 || this.is_subscribe == true) return false;

            // 检查登录状态
            await check_login_status();

            // 设置成已经订阅
            setTimeout(() => {
                this.is_subscribe = true;
            }, 100);

            // 记录用户ID的cookie
            const component_id = this.$root.pageInstanceId;
            const user_id = get_user_id();
            set_user_was_subscribe(component_id, user_id);

            // PC/M的情况下打开引导APP下载的弹窗
            if (get_platform() == 'pc' || get_platform() == 'wap') {
                this.open_dialog('download', true);
            }

            // app下的检查推送权限
            const platform = get_platform();
            // IOS 流程
            if (platform == 'ios') {
                const ios_response = await ios_check_push();
                if (ios_response == false) {
                    this.open_dialog('auth', true);
                }
            }
            // 安卓流程
            if (platform == 'android') {
                const component_id = this.$root.pageInstanceId;
                android_check_auth(component_id, (response) => {
                    if (Number(response) == 0) {
                        this.open_dialog('auth', true);
                    }
                });
            }
        },

        /**
         * 打开弹窗
         * @param {String} type 弹窗类型
         * @param {Boolean} val true/false
         */
        open_dialog (type, val) {
            this.dialog[type].visible = val;
        },

        /**
         * 下载APP
         */
        handle_download_app () {
            window.open('https://zaful.app.link/en');
        }
    },

    created () {
        // APP进入页面跳转登录
        if (get_platform() == 'ios' || get_platform() == 'android') {
            window.GEShopSiteCommon && window.GEShopSiteCommon.appLogin();
        }

        // ios 初始化bridge
        get_platform() == 'ios' && setupWebViewJavascriptBridge((bridge) => {
            window.ios_bridge = bridge;
        });

        // 检查cookie是否已经订阅了
        const component_id = this.$root.pageInstanceId;
        const user_id = get_user_id();
        this.is_subscribe = get_user_was_subscribe(component_id, user_id);

        // 开始倒计时
        this.timer = setInterval(() => {
            const current_timestamp = new Date().getTime();
            this.time_status = check_time_range(this.data.start_time, this.data.end_time, current_timestamp);
        }, 1000);
    }
};
