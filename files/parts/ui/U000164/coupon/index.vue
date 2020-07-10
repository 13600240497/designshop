<template>
    <div class="geshop-u000164-coupon-body" :style="style_body">
        <input type="hidden" name="active_id" :value="active_id" />
        <div class="exchange-body" :style="style_coupon">
            <a href="javascript:void(0);" :data-id="active_id" :id="$root.compKey+'_'+$root.pageInstanceId+'_exchangebtn'"  class="exchange-btn" :style="style_btn">{{stateText}}</a>
        </div>
        <div class="exchange-dialog">
            <div class="modal-container" :ref="$root.compKey+'_'+$root.pageInstanceId+'_exchang_dialog'" :style="style_exchange">
                <i class="ico-close"></i>
                <div class="exchange-text" :style="style_exchange_text" v-html="use_score"></div>
                <a href="javascript:void(0);"  :id="$root.compKey+'_'+$root.pageInstanceId+'_confirmbtn'"  class="confirm-btn"> {{ $root.languages.yes}}</a>
            </div>
        </div>
        <div class="not-enough-dialog">
            <div class="modal-container" :ref="$root.compKey+'_'+$root.pageInstanceId+'_msg_dialog'" :style="style_not_enough">
                <i class="ico-close"></i>
                <p class="ne-title" :style="style_oops">{{ $root.languages.oops }}</p>
                <div class="ne-img" :style="style_oops_ico" ></div>
                <p class="ne-msg" :style="style_oops_text">{{ tips_type }}</p>
                <p class="ne-herf" v-show="href_show">{{ $root.languages.get_more_score}} <a :href="getMoreLink" :style="style_link">{{ $root.languages.get_more_score_href}}</a></p>
            </div>
        </div>
    </div>
</template>

<script>
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
        use_score () {
            return this.$root.languages.use_score.replace('{score}', '<strong>' + this.score + '</strong>');
        },
        active_id () {
            return this.data.active_id;
        },
        style_body () {
            return {
                'margin-top': `${this.data.box_margin_top || 0}px`,
                'margin-bottom': `${this.data.box_margin_bottom || 40}px`,
                'text-align': `${this.data.components_align || 'left'}`
            };
        },
        style_coupon () {
            return {
                'width': `${this.data.bg_width || 389}px`,
                'height': `${this.data.bg_height || 282}px`,
                'background-color': `${this.data.coupon_bg_color || '#ededed'}`,
                'background-image': `url(${this.data.bg_img})`
            };
        },
        style_btn_upcomming () {
            return {
                'background-color': `${this.data.notstart_bg_color || '#666666'}`,
                'color': `${this.data.notstart_text_color || '#FFFFFF'}`,
                'bottom': `${this.data.btn_mb || 40}px`,
                'transform': `translateX(${this.data.btn_mr}px)`,
                'cursor': 'default'
            };
        },
        style_btn_underway () {
            return {
                'background-color': `${this.data.underway_bg_color || '#333333'}`,
                'color': `${this.data.underway_text_color || '#FFFFFF'}`,
                'bottom': `${this.data.btn_mb || 40}px`,
                'transform': `translateX(${this.data.btn_mr}px)`
            };
        },
        style_btn_ended () {
            return {
                'background-color': `${this.data.ended_bg_color || '#666666'}`,
                'color': `${this.data.ended_text_color || '#FFFFFF'}`,
                'bottom': `${this.data.btn_mb || 40}px`,
                'transform': `translateX(${this.data.btn_mr}px)`,
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
                platform: GESHOP_PLATFORM
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
                platform: GESHOP_PLATFORM,
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
                    window.location.href = err.login_url + '?ref=' + encodeURIComponent(window.location.href);
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
                            modalWidth: '415px',
                            modalHeight: '260px',
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

        $(document).on('click', '#' + confirmBtnId, function (event) {
            that.confirmExchange();
        });

        $(document).on('click', '#' + exchangeBtnId, function (event) {
            // that.$refs[that.$root.compKey + '_' + that.$root.pageInstanceId + '_exchang_dialog'];
            window.current_active_id = $(this).data('id');
            const options = {
                content: that.$refs[that.$root.compKey + '_' + that.$root.pageInstanceId + '_exchang_dialog'],
                modalWidth: '415px',
                modalHeight: '260px',
                className: 'exchange-modal'
            };

            if (that.canClick && that.use_score) {
                typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.custom(options);
            }
        });
    }
};
</script>

<style lang="less" scoped>
.geshop-u000164-coupon-body {
    margin: 0 auto;
    max-width: 1200px;
    .exchange-body{
        background-size: 100% 100%;
        /*background-color: #ededed;*/
        display: inline-block;
        text-align: center;
        position: relative;
        .exchange-btn{
            position: absolute;
            display: inline-block;
            white-space: pre;
            padding: 0 32px;
            height: 36px;
            font-size: 16px;
            line-height: 36px;
            text-align: center;
            border-radius:20px;
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

    .exchange-modal{
        margin-left: -207px;
        margin-top: -130px;
        .modal-container{
            .ico-close{
                position:absolute;
                right: -9px;
                top: -9px;
                background-image: url('https://geshopimg.logsss.com/uploads/e54ByT6fP0MnNjCoQzlpDRvZbFKJEgH8.png');
                background-size: 100% 100%;
                width: 28px;
                height: 28px;
                display:inline-block;
            }
            .exchange-text{
                padding: 80px 40px 0 40px;
                font-size: 18px;
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
                width: 160px;
                height: 40px;
                line-height: 40px;
                background:rgba(51,51,51,1);
                border-radius:24px;
                font-size:20px;
                color:rgba(255,255,255,1);
                text-align: center;
                margin: 80px auto 40px;

            }
        }
    }
    .not-enough-modal{
        margin-left: -207px;
        margin-top: -130px;

        .modal-container{
            height: 100%;
            .ico-close{
                position:absolute;
                right: -9px;
                top: -9px;
                background-image: url('https://geshopimg.logsss.com/uploads/e54ByT6fP0MnNjCoQzlpDRvZbFKJEgH8.png');
                background-size: 100% 100%;
                width: 28px;
                height: 28px;
                display:inline-block;
            }
            .ne-title{
                padding-top: 32px;
                padding-bottom: 10px;
                font-size:24px;
                color: #333;
                font-weight:600;
            }
            .ne-img{
                width: 70px;
                height: 64px;
                margin: 0 auto 25px;
                background-size: 100% 100%;
            }
            .ne-msg{
                font-size: 18px;
                color: #333;
                margin-bottom: 17px;
                padding: 0 15px;
            }
            .ne-herf{
                font-size: 16px;
                color: #333;
            }
        }
    }
</style>
