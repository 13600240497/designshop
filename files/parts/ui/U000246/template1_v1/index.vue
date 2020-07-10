<template>
    <div class="geshop-U000246-template1_v1 geshop-component-body" :class="boxWrapMedia" v-show="layOutShow"
         ref="component">
        <ul class="list_wrap">
            <li class="list_item">
                <div class="coupon-info-group">
                    <!-- 库存进度条 -->
                    <div class="item_stock font-lato-bold">
                        <span
                            v-if="formData.coupon_range_show == 0">{{ goodsPercent }}% {{$lang('left')}}</span>
                        <span v-else>{{couponInfo.leftCount || 0}} {{$lang('left')}}</span>
                    </div>
                    <geshop-progress-bar
                        :color1="data.stock_tip_has_color"
                        :color2="data.stock_tip_bg_color"
                        :current="couponInfo.leftCount "
                        :total="couponInfo.maxCount"
                        :type="'full-100'"
                        :limitType="limitType">
                        >
                    </geshop-progress-bar>
                    <!-- 未开始 -->
                    <div class="btn-group-state group-state-comming" v-if="couponInfo.coupon_active == 0">
                        <span class="btn-item state-all-claimed font-lato-bold">{{$lang('state_upcoming')}}</span>
                    </div>
                    <!-- 进行中 -->
                    <div class="btn-group-state group-state-start" v-else-if="couponInfo.coupon_active == 1">
                        <!--优惠券总数已领完-->
                        <div class="btn-item state-all-claimed font-lato-bold"
                             v-if="received_states === 0">{{$lang('state_claimed')}}
                        </div>
                        <!--优惠券当日已领完-->
                        <div class="btn-item state-all-claimed font-lato-bold"
                             v-else-if="received_states === 4">
                            {{$lang('state_today_claimed')}}
                        </div>
                        <!--优惠券可以领-->
                        <div class="btn-item state-can btn-cursor font-lato-bold"
                             v-else-if="received_states === 1"
                             @click="handleShowGetDia(true)">{{$lang('state_underway')}}
                        </div>
                        <!--用户已领去使用 received_states 2-->
                        <!--用户已领完,去使用-->
                        <div class="btn-item state-can btn-cursor font-lato-bold"
                             v-else-if="received_states === 5">
                            <a class="state-can" :href="data.active_url || couponInfo.site_url"
                               target="_blank">{{ $lang('state_use_now') }}</a>
                        </div>
                        <div class="btn-item state-can btn-cursor font-lato-bold" @click="handleShowGetDia(true)"
                             v-else>
                            {{$lang('state_underway')}}
                        </div>
                    </div>
                    <!-- 已结束 -->
                    <div class="btn-group-state group-state-end" v-else>
                        <div class="btn-item state-all-claimed font-lato-bold">{{$lang('state_ended')}}</div>
                    </div>

                </div>
            </li>
        </ul>
        <!--积分兑换弹窗-->
        <div class="dia-coupon-getting" v-show="gettingShow">
            <div class="getting-main">
                <div class="getting-info">
                    <p v-html="score"></p>
                    <span class="btn-sure" @click="getCoupon()">{{ $lang('yes') }}</span>
                </div>
                <span class="btn-close" @click="handleShowGetDia(false)">X</span>
            </div>
        </div>
        <!--积分不够提示弹窗-->
        <div class="dia-coupon-noEnough" v-show="tipsShow">
            <div class="noEnough-main">
                <div class="noEnough-info">
                    <h3 class="font-lato-bold integral_title">{{ this.$lang('oops') }}!</h3>
                    <img v-if="data.dialog_icon_image && data.dialog_icon_image.length > 1"
                         :src="data.dialog_icon_image" alt="coupon-gif"
                         class="coupon-gif">
                    <p>{{ this.$lang('oops_tips') }}</p>
                    <p><a :href="couponInfo.checkin_url || checkin_url"
                          target="_blank">{{this.$lang('get_more_score')}}</a>
                    </p>
                    <span class="btn-sure" @click="handleShowTipsDia(false)">{{ $lang('yes') }}</span>
                </div>
                <span class="btn-close" @click="handleShowTipsDia(false)">X</span>
            </div>
        </div>
    </div>
</template>

<script>
import languages from './languages';

export default {
    props: ['data'],
    data () {
        return {
            activity_paltform: GESHOP_PLATFORM,
            lang: GESHOP_LANG || 'en', // 当前语言
            view_platform: '', // viewport窗口类型 pc,pad m
            boxWrapMedia: '', // 当前端class
            formData: {
                coupon_range_show: 0 // form表单库存显示方式
            },
            couponInfo: {}, // 优惠券信息
            goodsPercent: 0, // 库存百分比
            coupon_id: null, // 积分兑换优惠券序列号
            score: '', //  您确定使用{score}积分兑换优惠券吗？ score 替换
            gettingShow: false, // 领取提示 默认false关闭
            tipsShow: false,
            layOutShow: true, // 组件数据出错预览页隐藏，装修页显示默认
            btnPrevent: false,
            limitType: 2
        };
    },
    created () {
        this.$nextTick(() => {
            this.resizeChange();
        });
        this.coupon_id = this.data.coupon_id;
        this.formData.coupon_range_show = this.data.coupon_range_show || 0;
    },
    computed: {
        couArr () {
            return this.$store.state.dresslily.coupon_redeem;
        },
        /**
         * 优惠券状态
         * @returns {number}
         */
        received_states () {
            return parseInt(this.couponInfo.received_states);
        },
        /**
         * 赚取积分链接
         */
        checkin_url () {
            const urlList = {
                'en': 'https://www.dresslily.com/promotion/DressLily-VIP-Center.html',
                'fr': 'https://fr.dresslily.com/promotion/DressLily-VIP-Center.html'
            };
            return urlList[this.lang];
        },
        /**
         * 获取当前端，web,ios,android
         * @returns {string}
         */
        geshop_platform () {
            let result = typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : '';
            if (result === 'app') { // app 区分ios 安卓
                if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
                    result = 'ios';
                } else if (/(Android)/i.test(navigator.userAgent)) {
                    result = 'android';
                }
            }
            return result;
        }
    },
    watch: {
        couArr () {
            // 监听存放所有优惠券信息的数组 变化就重新执行初始化方法 更新信息
            this.initCoupon();
        }
    },
    mounted () {
        if (typeof this.coupon_id != null && this.coupon_id) {
            this.initCoupon();
        } else {
            if (this.$root.is_edit_env == 0) {
                // 预览页
                this.layOutShow = false;
            }
        }
        // this.handleList();
        // 追加函数到队列
        this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
    },
    methods: {
        resizeChange () {
            // this.setPlatform();
            const newValue = document.body.clientWidth || document.documentElement.clientWidth;
            let boxWrapMedia = '';
            if (newValue >= 1025) {
                // pc
                this.view_platform = 'pc';
                boxWrapMedia = 'geshop_dl_pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.view_platform = 'pad';
                boxWrapMedia = 'geshop_dl_pad';
            } else if (newValue <= 767) {
                // m
                this.view_platform = 'm';
                boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
            }
            this.boxWrapMedia = boxWrapMedia;
        },
        /**
         * 获取优惠券信息
         */
        initCoupon () {
            this.layOutShow = true;
            try {
                const dataInfo = this.couArr;
                let coupData = null;
                if (dataInfo instanceof Array) {
                    coupData = dataInfo.find((item) => {
                        return item.id == this.data.coupon_id;
                    });
                } else if (dataInfo instanceof Object) {
                    coupData = dataInfo[this.data.coupon_id];
                }

                if (coupData) {
                    // 正常流程
                    this.couponInfo = Object.assign({}, coupData);
                    this.goodsPercent = Math.ceil(this.couponInfo.leftCount / this.couponInfo.maxCount * 100);
                    this.score = this.$lang('use_score').replace('{score}', '<span class="font-lato-bold integral_title">' + this.couponInfo.integral + '</span>');
                } else {
                    // 数据异常
                    // data 数据容错处理，数据发生错误没有返回时隐藏
                    if (this.$root.is_edit_env == 0) {
                        // 预览页
                        this.layOutShow = false;
                    }
                }
            } catch (e) {
            }
        },
        /**
         * 兑换弹窗
         * @param {boolean} bool : bool 为 true 显示， false 关闭
         */
        handleShowGetDia (bool) {
            if (!this.data.coupon_id || !this.couponInfo.id) { // 没有填写id 返回
                return false;
            }
            this.gettingShow = bool;
            // if (this.couponInfo.integral == 0) {
            //     // 消耗积分为0是直接领取
            //     this.getCoupon();
            // } else {
            //     // 正常流程
            //     this.gettingShow = bool;
            // }
        },
        /**
         * 积分不够提示弹窗
         * @param {boolean} bool : bool 为 true 显示， false 关闭
         *
         */
        handleShowTipsDia (bool) {
            this.tipsShow = bool;
        },
        /**
         * 兑换优惠券,兑换yes
         */
        getCoupon () {
            this.gettingShow = false; // 关闭弹窗
            if (this.btnPrevent) {
                return false;
            }
            this.btnPrevent = true;
            const url = GESHOP_INTERFACE.getcoupon.url;
            // const user_id = GEShopSiteCommon.getCookie('WEBF-user_id') ? GEShopSiteCommon.getCookie('WEBF-user_id').substr(1) : '';
            const content = {
                lang: this.lang,
                couponid: this.couponInfo.id,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                platform: this.geshop_platform
                // user_id: user_id
            };

            try {
                this.$jsonp(url, content).then(res => {
                    // res.code 为0 领取成功
                    this.couponInfo.leftCount = this.couponInfo.leftCount - 1; // 数量减1
                    this.goodsPercent = Math.ceil(this.couponInfo.leftCount / this.couponInfo.maxCount * 100);
                    GEShopSiteCommon.dialog.message(this.$lang('exchange_succcess')); // 弹出领取成功提示
                    this.btnPrevent = false;
                }, state => {
                    switch (state.code) {
                    case 1: // 请先登录
                        if (window.GESHOP_PLATFORM === 'app') {
                            GEShopSiteCommon.appLogin(1);
                        } else {
                            window.location.href = state.data.loginurl + window.location.href;
                        }
                        break;
                    case 3: // 当天限量已领取完
                        GEShopSiteCommon.dialog.message(this.$lang('tips_sorry'));
                        break;
                    case 4: // 活动未开始
                        GEShopSiteCommon.dialog.message(this.$lang('upcoming'));
                        break;
                    case 5: // 积分不够
                        this.tipsShow = true;
                        break;
                    case 6: // 已全部兑换完
                        GEShopSiteCommon.dialog.message(this.$lang('tips_all_claimed'));
                        break;
                    case 7: // 兑换已结束
                        GEShopSiteCommon.dialog.message(this.$lang('state_ended'));
                        break;
                    case 8: // 用户已兑换完
                        GEShopSiteCommon.dialog.message(this.$lang('tips_sorry'));
                        this.couponInfo.received_states = 5; // 更改状态为用户已领完
                        break;
                    default:
                        state.message && GEShopSiteCommon.dialog.message(state.message);
                    }
                    this.btnPrevent = false;
                });
            } catch (e) {
                this.btnPrevent = false;
                GEShopSiteCommon.dialog.message('Network exception, please try again');
            }
        },
        /**
         * 多语言临时调用
         * @param value
         * @returns {*}
         */
        $langOr (value) {
            return languages[value][GESHOP_LANG];
        }
    }
};
</script>

<style lang="less" scoped>
    @import "./component.less";
</style>

<style lang="less">
    .geshop-U000246-template1_v1 {
        .geshop-progress-bar {
            height: 6px;
            line-height: 6px;
            margin-top: 2px;
            margin-bottom: 0px;

            span {
                height: 100%;
                line-height: 1;
            }
        }

        &.geshop_dl_wap {
            .geshop-progress-bar {
                height: 4px;
                line-height: 4px;
            }

            .coupon-info-group {
                width: 110px;
                right: 24px;
            }

            .btn-item {
                height: 28px;
                line-height: 28px;
            }

            .getting-info {
                padding: 0 24px;

                p {
                    margin: 42px auto 49px;
                    height: auto !important;
                }
            }

            .btn-sure {
                margin-bottom: 31px;
            }

            .dia-coupon-noEnough {
                .integral_title {
                    margin: 32px auto 24px;
                }
            }
        }
    }
</style>
