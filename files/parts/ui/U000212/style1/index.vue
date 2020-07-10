<template>
    <div class="geshop-U000212-style1" :style="main_style">
        <div class="geshop-U000212-style1-wrapper">
            <template v-if="data.image">
                <a :href="link" class="default-image-box">
                    <img :src="data.image" :style="wrapper_style" />
                </a>
            </template>
            <template v-else>
                <div class="geshop-U000212-style1-wrapper-preview">
                    <img :src="defaultImg" />
                </div>
            </template>
            <button type="button" :style="button_style" @click.prevent="handleTapBook" v-if="showButton">{{ button_label }}</button>
        </div>
    </div>
</template>

<script type="text/javascript">

/* 设置cookie */
const setCookie = (name, value, expiredays) => {
    const exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = name + '=' + escape(value) + (expiredays == null ? ';path=/;' : ';path=/;expires=' + exdate.toGMTString());
};

/**
 * 获取用户ID
 * @returns {String}
 */
const get_app_user_id = () => {
    try {
        let user_id = GEShopSiteCommon.getCookie('WEBF-APP_user_id') || '';
        user_id = user_id.replace('=', '');
        return decodeURIComponent(user_id);
    } catch (err) {
        return '';
    }
};

/**
 * 获取站点的用户ID
 * @returns {String}
 */
const get_website_user_id = () => {
    try {
        const user_id = GEShopSiteCommon.getCookie('WEBF-user_id') || '';
        return user_id.replace('=', '');
    } catch (err) {
        return '';
    }
};

/**
 * APP检查是否已经登录
 * 通过检查 app_user_id 和 website_user_id 来判断
 */
const app_check_login = () => {
    const website_user_id = get_website_user_id();
    const app_user_id = get_app_user_id();
    return (website_user_id != '' && app_user_id != '');
};

export default {
    name: 'geshop-U000212-style1',
    props: ['pid', 'data'],
    data () {
        return {
            userID: 'xxxx',
            link: 'javascript:;',
            showButton: true,
            is_booked: false,
            booked_key: null,
            timestamp_local: null,
            timestamp_start: null,
            timestamp_end: null,
            language: {
                ready: { en: 'COMING SOON', fr: 'ARRIVERA', es: 'PRÓXIMAMENTE' },
                able: { en: 'REMIND ME', fr: 'ME RAPPELER', es: 'RECUERDAME' },
                booked: { en: 'REMINDED', fr: 'RAPPELÉ', es: 'RECORDADO' },
                ing: { en: 'SHOP NOW', fr: 'SHOPPER', es: 'COMPRA AHORA' },
                ended: { en: 'ENDED', fr: 'TERMINÉ', es: 'TERMINADO' },
                success: { en: 'Subscribed successful! The remind will be sent when the sale start soon. ', fr: 'Abonné avec succès! Le rappel sera envoyé au début de la vente.', es: 'Suscrito exitoso! El recordatorio será enviado cuando la venta comience pronto.' }
            },
            timer: null,
            defaultImg: 'https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png'
        };
    },
    computed: {
        // 主区域的定制样式
        main_style () {
            return {
                marginTop: (this.data.margin_top || 0) / 75 + 'rem',
                marginBottom: (this.data.margin_bottom || 32) / 75 + 'rem',
                'text-align': this.data.position || 'center'
            };
        },
        wrapper_style () {
            return {
                width: (this.data.width) ? (this.data.width) / 75 + 'rem' : 100 + '%',
                height: (this.data.height) ? (this.data.height) / 75 + 'rem' : 'auto',
                'max-width': 100 + '%',
                'max-height': 100 + '%'
            };
        },
        // 预览的定制样式
        preview_style () {
            return {
                width: 10 + 'rem',
                height: 5.33 + 'rem',
                'background-image': 'url(https://geshopimg.logsss.com/uploads/G6yp2z1vAX8SCFVEIbQx93Tkeon7f0jK.png)'
            };
        },
        // 按钮的样式
        button_style () {
            // 根据状态值来取按钮文本颜色
            const button_font_color = [
                this.data.button_color_able,
                this.data.button_color_ready,
                this.data.button_color_ing,
                this.data.button_color_ended
            ];
            const button_bg_color = [
                this.data.button_bgColor_able,
                this.data.button_bgColor_ready,
                this.data.button_bgColor_ing,
                this.data.button_bgColor_ended || '#cccccc'
            ];
            // 返回按钮样式
            const style = {
                width: (this.data.button_width || 200) / 75 + 'rem',
                height: (this.data.button_height || 60) / 75 + 'rem',
                right: (this.data.button_right || 20) / 75 + 'rem',
                bottom: (this.data.button_bottom || 20) / 75 + 'rem',
                color: button_font_color[this.activity_status] || '#ffffff',
                'background-color': button_bg_color[this.activity_status] || '#333',
                'background-image': `url('${this.data.button_image || ''}')`
            };
            // 如果已经预约了
            if (this.is_booked) {
                style.color = this.data.button_color_booked || '#ffffff';
                style['background-color'] = this.data.button_bgColor_booked || '#cccccc';
            }
            if (this.data.button_image) {
                style['border-radius'] = '0px';
                style['background-color'] = 'transparent';
            }
            return style;
        },
        // 返回按钮的文案
        button_label () {
            switch (this.activity_status) {
            case 0:
                return this.is_booked ? this.language.booked[GESHOP_LANG] : this.language.able[GESHOP_LANG];
            case 1:
                return this.is_booked ? this.language.booked[GESHOP_LANG] : this.language.ready[GESHOP_LANG];
            case 2:
                this.link = this.data.book_link ? this.data.book_link : 'javascript:;';
                return this.language.ing[GESHOP_LANG];
            case 3:
                return this.language.ended[GESHOP_LANG];
            case 4:
                return this.is_booked ? this.language.booked[GESHOP_LANG] : this.language.able[GESHOP_LANG];
            }
        },
        /**
         * 计算当前的时间状态
         * 0 = commin soon
         * 1 = 没开始
         * 2 = 进行中
         * 3 = 结束
         * */
        activity_status () {
            const threeHour = 10800000;
            if (this.timestamp_local == null) return 4;
            if (this.timestamp_local <= this.timestamp_start) {
                if (this.timestamp_local + threeHour <= this.timestamp_start) {
                    return 0;
                } else {
                    return 1;
                }
            } else if (this.timestamp_local >= this.timestamp_end) {
                return 3;
            } else {
                return 2;
            }
        },
        appIsLogin () {
            return GESHOP_STORE.state.global.isAppLogin;
        }
    },
    filters: {
        trans (field) {
            return field[GESHOP_LANG];
        }
    },
    methods: {
        /**
         * APP预约状态
         * @param {String} user_id APP登录后回传的ID
         */
        app_check_was_booked (user_id) {
            const url = GESHOP_INTERFACE.user_activityBookList.url;
            const params = { user_id };
            const content = { content: JSON.stringify(params) };
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'jsonp',
                data: content
            }).done((res) => {
                if (res.code == 0) {
                    this.booked_key = res.data || '';
                    const booked_code = this.booked_key.join(',') || [];
                    this.is_booked = booked_code.includes(this.pid);
                } else {
                    GEShopSiteCommon.appLogin(0);
                }
            });
        },

        /**
         * 点击预约
         */
        handleTapBook () {
            switch (this.activity_status) {
            case 0:
                this.handleBook();
                break;
            case 2:
                window.open(this.data.book_link);
                this.link = this.data.book_link ? this.data.book_link : 'javascript:;';
                break;
            }
        },

        // 预约接口
        handleBook () {
            const self = this;
            const url = GESHOP_INTERFACE.user_activityBook.url;
            let platform = null;

            if (GESHOP_PLATFORM == 'wap') {
                platform = 'wap';
            } else if (GESHOP_PLATFORM == 'app') {
                const u = window.navigator.userAgent;
                const isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // 根据输出结果true或者false来判断ios终端
                const isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; // 如果输出结果是true就判定是android终端或者uc浏览器
                if (isAndroid) {
                    platform = 'android';
                } else if (isIOS) {
                    platform = 'ios';
                }
            }

            // 获取站点的用户ID
            const user_id = get_app_user_id();

            const params = {
                pid: this.pid,
                client: platform,
                lang: GESHOP_LANG || 'en',
                user_id: user_id,
                book_desc: this.data.book_desc || '',
                book_link: this.data.book_link || '',
                book_start_time: new Date(this.data.book_time.split(' - ')[0].replace(/-/g, '/')).getTime() / 1000 || '',
                book_end_time: new Date(this.data.book_time.split(' - ')[1].replace(/-/g, '/')).getTime() / 1000 || ''
            };

            const content = {
                content: JSON.stringify(params)
            };

            if (GESHOP_PLATFORM == 'wap') {
                // 检测WAP站点登录信息
                if (typeof info_check(1) != 'undefined') {
                    info_check(1).done((res) => {
                        if (res.user_id !== 0) {
                            if (self.is_booked != true) {
                                $.ajax({
                                    url: url,
                                    type: 'get',
                                    dataType: 'jsonp',
                                    data: content
                                }).done(function (res) {
                                    if (res.code == 0) {
                                        // 已预约
                                        self.is_booked = true;
                                        GEShopSiteCommon.dialog.message(self.language.success[GESHOP_LANG]);
                                    } else {
                                        GEShopSiteCommon.dialog.message(res.message);
                                    }
                                });
                            }
                        } else {
                            window.location.href = DOMAIN_LOGIN + '/m-users-a-sign.htm?ref=' + window.location.href;
                        }
                    });
                }
            } else if (GESHOP_PLATFORM == 'app') {
                const appUserId = get_app_user_id();
                if (appUserId) {
                    if (self.is_booked != true) {
                        $.ajax({
                            url: url,
                            type: 'get',
                            dataType: 'jsonp',
                            data: content
                        }).done(function (res) {
                            if (res.code == 0) {
                                // 已预约
                                self.is_booked = true;
                                setCookie('booked_list', res.data, 30);
                                GEShopSiteCommon.dialog.message(self.language.success[GESHOP_LANG]);
                            } else {
                                GEShopSiteCommon.dialog.message(res.message);
                            }
                        });
                    }
                } else {
                    // 调起登陆
                    GEShopSiteCommon.appLogin(1);
                }
            }
        }
    },
    created () {
        // APP进入页面跳转登录, APP登录会刷页面
        if (GESHOP_PLATFORM == 'app') {
            // window.alert(get_app_user_id());
            // window.alert(get_website_user_id());
            if (app_check_login() == true) {
                const userId = get_app_user_id();
                this.app_check_was_booked(userId);
            } else {
                window.GEShopSiteCommon && window.GEShopSiteCommon.appLogin();
            }
        }

        // 设置timer
        if (!window.geshop_timer) {
            window.geshop_timer = {};
        }

        // M端读取cookie，是有已经预约了这个活动
        if (GESHOP_PLATFORM == 'wap') {
            const booked_list = GEShopSiteCommon.getCookie('booked_list') || [];
            this.is_booked = booked_list.includes(this.pid);
        }
    },

    // 初始化
    mounted () {
        if (this.data.isEditEnv == 0) {
            setTimeout(() => {
                this.showButton = true;
            }, 1000);
        }

        if (this.data.book_time) {
            this.timestamp_start = new Date(this.data.book_time.split(' - ')[0].replace(/-/g, '/')).getTime();
            this.timestamp_end = new Date(this.data.book_time.split(' - ')[1].replace(/-/g, '/')).getTime();
            // 清除老定时器
            window.geshop_timer && clearInterval(window.geshop_timer[this.pid]);
            // 启动定时器
            window.geshop_timer[this.pid] = setInterval(() => {
                this.timestamp_local = new Date().getTime();
            }, 1000);
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000212-style1 {
        position: relative;
        display: block;
        font-size: 0px;
        display: flex;
        justify-content: center;
        align-items: center;

        .geshop-U000212-style1-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            // 默认的图片
            div.geshop-U000212-style1-wrapper-preview {
                width: 100%;
                height: 100%;
                background-size: auto;
                background-position: center;
                background-color: #EDEDED;
                background-repeat: no-repeat;
            }
            // 用户配置的图片
            img {
                display: block;
                height: 100%;
                width: 100%;
            }
            // 预约按钮
            button {
                position: absolute;
                box-sizing: border-box;
                border-radius: 2px;
                background-size: 100% 100%;
                border: none;
                outline: none;
            }
        }
    }
</style>
