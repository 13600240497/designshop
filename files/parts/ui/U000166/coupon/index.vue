<template>
    <div class="geshop-u000166-coupon-body" :style="style_body">

        <input type="hidden" name="active_id" :value="active_id" />
        <div class="exchange-body" :style="style_coupon">
            <a href="javascript:void(0);" :data-id="active_id" :id="$root.compKey+'_'+$root.pageInstanceId+'_exchangebtn'"  class="exchange-btn" :style="style_btn">{{stateText}}</a>
        </div>
        <div class="exchange-dialog">
            <div class="modal-container" :ref="$root.compKey+'_'+$root.pageInstanceId+'_exchang_dialog'" :style="style_exchange">
                <a href="javascript:void(0);" class="ico-close"></a>
                <div class="exchange-text" :style="style_exchange_text"  v-html="use_score"></div>
                <a href="javascript:void(0);"  :id="$root.compKey+'_'+$root.pageInstanceId+'_confirmbtn'"  class="confirm-btn"> {{ $root.languages.yes}}</a>
            </div>
        </div>
        <div class="not-enough-dialog">
            <div class="modal-container" :ref="$root.compKey+'_'+$root.pageInstanceId+'_msg_dialog'" :style="style_not_enough">
                <a href="javascript:void(0);" class="ico-close"></a>
                <p class="ne-title" :style="style_oops">{{ $root.languages.oops }}</p>
                <div class="ne-img" :style="style_oops_ico" ></div>
                <p class="ne-msg" :style="style_oops_text">{{ tips_type }}</p>
                <p class="ne-herf" v-show="href_show">{{ $root.languages.get_more_score}} <a :href="getMoreLink" :style="style_link">{{ $root.languages.get_more_score_href}}</a></p>
            </div>
        </div>
    </div>
</template>

<script>

/* app登录方法回调，必须是全局函数 */
window.userinfo = function (userId, userToken) {
    if (userId && userToken) {
        getAppUserInfo(userId, userToken);
    }
};

/* 调用站点方法获取app登录状态 */
function getAppUserInfo (userid, token) {
    // APP内未登录暂时直接返回
    if (_GET('is_app') && (userid === '0' || token === '')) {
        return false;
    }
    $.ajax({
        url: '/fun/index.php?act=AppLogin',
        data: {
            user_id: userid,
            token: token
        },
        type: 'post',
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                $.cookie('is_app_login', 1, {
                    expires: 30,
                    path: '/',
                    domain: COOKIESDIAMON
                });
                try {
                    sessionStorage.setItem('is_app_login', 1);
                } catch (e) {}
            } else {
                window.location.href = 'https://userm.zaful.com/sign-up.html';
            }
        }
    });
};

export default {
    props: ['data'],
    data () {
        return {
            promo_code: '',
            start_time: '',
            end_time: '',
            stateText: 'Upcoming',
            style_btn: '',
            canClick: false,
            score: 0, // 每次兑换需要消耗积分
            getMoreLink: '', // 赚取积分链接
            href_show: false,
            state: '',
            tips_type: ''
        };
    },
    computed: {
        platform () {
        //    if (GESHOP_PLATFORM == 'wap') {
        //         return 'm';
        //     } else if (GESHOP_PLATFORM == 'app') {
        //         const u = window.navigator.userAgent, app = window.navigator.appVersion;
        //         const isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // 根据输出结果true或者false来判断ios终端
        //         const isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; // 如果输出结果是true就判定是android终端或者uc浏览器
        //         if (isAndroid) {
        //             return 'android';
        //         } else if (isIOS) {
        //             return 'ios';
        //         }
        //     }
        },
        use_score () {
            return this.$root.languages.use_score.replace('{score}', '<strong>' + this.score + '</strong>');
        },
        active_id () {
            return this.data.active_id;
        },
        style_body () {
            return {
                'margin-top': `${(this.data.box_margin_top || 0) / 75}rem`,
                'margin-bottom': `${(this.data.box_margin_bottom || 40) / 75}rem`,
                'text-align': `${this.data.components_align || 'left'}`
            };
        },
        style_coupon () {
            return {
                'width': `${(this.data.bg_width || 389) / 75}rem`,
                'height': `${(this.data.bg_height || 282) / 75}rem`,
                'background-color': `${this.data.coupon_bg_color || '#ededed'}`,
                'background-image': `url(${this.data.bg_img})`
            };
        },
        style_btn_upcomming () {
            return {
                'background-color': `${this.data.notstart_bg_color || '#666666'}`,
                'color': `${this.data.notstart_text_color || '#FFFFFF'}`,
                'bottom': `${(this.data.btn_mb || 40) / 75}rem`,
                'transform': `translateX(${this.data.btn_mr}rem)`,
                'cursor': 'default'
            };
        },
        style_btn_underway () {
            return {
                'background-color': `${this.data.underway_bg_color || '#333333'}`,
                'color': `${this.data.underway_text_color || '#FFFFFF'}`,
                'bottom': `${(this.data.btn_mb || 40) / 75}rem`,
                'transform': `translateX(${this.data.btn_mr}rem)`
            };
        },
        style_btn_ended () {
            return {
                'background-color': `${this.data.ended_bg_color || '#666666'}`,
                'color': `${this.data.ended_text_color || '#FFFFFF'}`,
                'bottom': `${(this.data.btn_mb || 40) / 75}rem`,
                'transform': `translateX(${this.data.btn_mr}rem)`,
                'cursor': 'default'
            };
        },
        style_exchange () {
            return {
                'border': `solid ${this.data.model_border_width || 3}px ${this.data.model_border_color || '#333333'}`,
                'background-color': `${this.data.model_bg_color || '#ffffff'}`,
                'color': `${this.data.model_text_color || '#333333'}`
            };
        },
        style_exchange_text () {
            return {
                'color': `${this.data.model_text_color || '#333333'}`
            };
        },
        style_not_enough () {
            return {
                'border': `solid ${this.data.noscore_model_border_width || 3}px ${this.data.noscore_model_border_color || '#333333'}`,
                'background-color': `${this.data.noscore_model_bg_color || '#ffffff'}`
            };
        },
        style_oops () {
            return {
                'color': `${this.data.oops_color || '#333333'}`
            };
        },
        style_oops_ico () {
            return {
                'background-image': `url(${this.data.model_icon || 'https://geshopimg.logsss.com/uploads/HgeYsmVa4r3yCPwD6WEz8xONLQvXJZ9F.png'})`
            };
        },
        style_oops_text () {
            return {
                'color': `${this.data.noscore_model_text_color || '#333333'}`
            };
        },
        style_link () {
            return {
                'color': `${this.data.getScore_link_color || '#333333'}`
            };
        }
    },
    methods: {
        /* 获取活动信息
           积分兑换的优惠券有四种类型免邮券、满减券、百分比券、保险费
           免邮券、满减券、百分比券 有优惠券id
        */
        async getExchangeData () {
            const _url = GESHOP_INTERFACE.cmsgoods_pointsProductDetail.url;
            const data = {
                id: this.data.active_id,
                platform: 'm'
            };
            try {
                const res = await this.$jsonp(_url, data);
                if (res.code === 0) {
                    if (res.exist == 1 && res.data.is_open == 1) {
                        if (res.data.promo_code != '0') {
                            // 免邮券、满减券、百分比券
                            this.promo_code = res.data.promo_code;
                            this.getCouponDetail();
                        } else {
                            // 保险费
                            let now_time = new Date().getTime() / 1000;
                            let valid_date = res.data.valid_date;
                            if (valid_date > now_time) {
                                /* 可领取 */
                                this.canClick = true;
                                this.stateText = this.$root.languages.underway;
                                this.style_btn = this.style_btn_underway;
                            } else {
                                /* 已结束 */
                                this.stateText = this.$root.languages.ended;
                                this.style_btn = this.style_btn_ended;
                            }
                        }
                        this.score = res.data.score;
                    }
                }
            } catch (err) {}
        },

        /* 获取优惠券详情 */
        async getCouponDetail () {
            const _url = GESHOP_INTERFACE.coupondetail.url;
            const content = {
                lang: GESHOP_LANG,
                couponid: this.promo_code,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };

            try {
                const res = await this.$jsonp(_url, content);
                if (res.code === 0) {
                    this.start_time = res.data.couponInfo.enableStartTime;
                    this.end_time = res.data.couponInfo.enableEndTime;
                    let now_time = new Date().getTime() / 1000;
                    if (this.end_time < now_time) {
                        /* 已结束 */
                        this.stateText = this.$root.languages.ended;
                        this.style_btn = this.style_btn_ended;
                    } else if (this.start_time > now_time) {
                        // 未开始
                        this.stateText = this.$root.languages.upcoming;
                        this.style_btn = this.style_btn_upcomming;
                    } else {
                        // 进行中
                        this.canClick = true;
                        this.stateText = this.$root.languages.underway;
                        this.style_btn = this.style_btn_underway;
                    }
                }
            } catch (err) {}
        },

        // 确认兑换
        async confirmExchange () {
            // window.current_active_id
            $('#' + this.$root.compKey + '_' + this.$root.pageInstanceId).find('input[name=active_id]').val();
            const _url = GESHOP_INTERFACE.activity_exchangePointsProduct.url;
            const params = {
                lang: GESHOP_LANG,
                id: window.current_active_id,
                platform: 'm',
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };

            try {
                const res = await this.$jsonp(_url, params);
                if (res.code === 0 && res.can_use) {
                    typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.message(this.$root.languages.exchange_succcess);
                }
            } catch (err) {
                /*
                    状态码定义
                    -4 未登录
                    -3 数量不够
                    -5 今日数量不够
                    -1 积分不够
                */
                this.state = err.code;
                if (err.code === -4) {
                    if (GESHOP_PLATFORM === 'wap') {
                        window.location.href = err.login_url + '?ref=' + encodeURIComponent(window.location.href);
                    } else if (GESHOP_PLATFORM === 'app') {
                        window.location.href = 'webAction://login?callback=userinfo()&isAlert=1&source=h5&redirect=' + encodeURIComponent(window.location.href);
                    }
                } else {
                    if (err.code === -3) {
                        this.tips_type = this.$root.languages.out_off;
                    } else if (err.code === -5) {
                        this.tips_type = this.$root.languages.today_off;
                    } else if (err.code === -1) {
                        this.href_show = true;
                        this.tips_type = this.$root.languages.oops_tips;
                        this.getMoreLink = err.getscore_url;
                    }

                    this.$nextTick(() => {
                        const options = {
                            content: this.$refs[this.$root.compKey + '_' + this.$root.pageInstanceId + '_msg_dialog'],
                            modalWidth: '8.533333333333333rem',
                            modalHeight: '5.44rem',
                            className: 'not-enough-modal'
                        };
                        if (this.tips_type) {
                            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.custom(options);
                        }
                    });
                }
            }
        }
    },
    async mounted () {
        this.style_btn = this.style_btn_upcomming;
        const that = this;
        that.getExchangeData();
        $(document).on('click', '.ico-close', function () {
            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.unblock();
        });

        let exchangeBtnId = this.$root.compKey + '_' + this.$root.pageInstanceId + '_exchangebtn';
        let confirmBtnId = this.$root.compKey + '_' + this.$root.pageInstanceId + '_confirmbtn';

        /* 点击确认兑换 */
        $(document).on('click', '#' + confirmBtnId, function (event) {
            if (GESHOP_PLATFORM === 'wap' || sessionStorage.getItem('is_app_login') === '1') {
                that.confirmExchange();
            } else {
                window.location.href = 'webAction://login?callback=userinfo()&isAlert=1&source=h5&redirect=' + encodeURIComponent(window.location.href);
            }
        });

        $(document).on('click', '#' + exchangeBtnId, function (event) {
            // that.$refs[that.$root.compKey+'_' + that.$root.pageInstanceId+'_exchang_dialog']
            window.current_active_id = $(this).data('id');
            const options = {
                content: that.$refs[that.$root.compKey + '_' + that.$root.pageInstanceId + '_exchang_dialog'],
                modalWidth: '8.533333333333333rem',
                modalHeight: '5.44rem',
                className: 'exchange-modal'
            };
            if (that.canClick && that.use_score) {
                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.custom(options);
            }
        });

        /* 如果是app.一进来需要判断是否登录 */
        if (GESHOP_PLATFORM === 'app' && !sessionStorage.getItem('is_app_login')) {
            window.location.href = 'webAction://login?callback=userinfo()&isAlert=0&source=h5';
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000166-coupon-body {
        .exchange-body{
            background-size: 100% 100%;
            background-color: #ededed;
            display: inline-block;
            text-align: center;
            position: relative;
            .exchange-btn{
                position: absolute;
                display: inline-block;
                white-space: pre;
                padding: 0 0.4266666666666667rem;
                height: 0.8rem;
                line-height: 0.8rem;
                text-align: center;
                border-radius:0.4rem;
                text-align: center;
                left: 50%;
                transform: translateX(-50%);
                cursor: pointer;
            }
        }
        .exchange-dialog{
            display:none;
        }
        .not-enough-dialog{
            display:none;
        }
    }
</style>

<style lang="less">
    .blockPage{
        border-radius: 0.32rem;
    }
    .exchange-modal{
        margin-left: -4.2265rem;
        margin-top: -2.72rem;
        .modal-container{
            height: 100%;
            border-radius: 0.32rem;
            .ico-close{
                position:absolute;
                right: -0.12rem;
                top: -0.2rem;
                background-image: url('https://geshopimg.logsss.com/uploads/e54ByT6fP0MnNjCoQzlpDRvZbFKJEgH8.png');
                background-size: 100% 100%;
                width: 0.5866666666666667rem;
                height: 0.5866666666666667rem;
                display:inline-block;
            }
            .exchange-text{
                padding: 1.0666666666666667rem 0.5333333333333333rem 0 0.5333333333333333rem;
                font-size: 0.4266666666666667rem;
                color: #333;
                text-align: center;
                word-break: break-word;
                strong{
                    font-weight:bold;
                }
            }
            .confirm-btn{
                display:inline-block;
                cursor:pointer;
                width: 2.1333333333333333rem;
                height: 0.9066666666666666rem;
                line-height: 0.9066666666666666rem;
                background:rgba(51,51,51,1);
                border-radius:0.4533333333333333rem;
                font-size:0.26666666666666666rem;
                color:rgba(255,255,255,1);
                text-align: center;
                margin: 1.0666666666666667rem auto 0.5333333333333333rem;
                text-decoration: none;

            }
        }
    }
    .not-enough-modal{
        margin-left: -4.2265rem;
        margin-top: -2.72rem;
        .modal-container{
            border-radius: 0.32rem;
            height: 100%;
            .ico-close{
                position:absolute;
                right: -0.12rem;
                top: -0.12rem;
                background-image: url('https://geshopimg.logsss.com/uploads/e54ByT6fP0MnNjCoQzlpDRvZbFKJEgH8.png');
                background-size: 100% 100%;
                width: 0.5866666666666667rem;
                height: 0.5866666666666667rem ;
                display:inline-block;
            }
            .ne-title{
                padding-top: 0.96rem;
                padding-bottom: 0.10666666666666667rem;
                font-size:0.48rem;
                color: #333;
                font-weight:600;
            }
            .ne-img{
                width: 0.9333333333333333rem;
                height: 0.8533333333333334rem;
                margin: 0 auto 0.32rem;
                background-size: 100% 100%;
            }
            .ne-msg{
                font-size: 0.4266666666666667rem;
                color: #333;
                margin-bottom: 0.96rem;
                padding: 0 0.2rem;
            }
            .ne-herf{
                font-size: 0.32rem;
                color: #333;
            }
        }
    }
</style>
