<template>
    <div class="geshop-u000204-template2">
        <div class="u000204-template2-couponsBox">
            <picture>
                <source media="(max-width: 1024px) and (min-width: 768px)" :srcset="data.box_bg_pad">
                <source media="(min-width: 1024px)" :srcset="data.box_bg">
                <source media="(max-width: 768px)" :srcset="data.box_bg_m">
                <img :src="data.box_bg" class="counp-img">
            </picture>
            <div class="btn-group">
                <!--库存条-->
                <div class="stock-box">
                    <!--剩余-->
                    <p v-if="data.count_range_type == 1">{{ couponInfo.couponRemainder }} {{ $lang('left') }}</p>
                    <p v-else>{{ percent }}% {{ $lang('left') }}</p>
                    <div class="rang-box">
                        <div class="remainder-bar" :style="{width: `${percent}%`}"></div>
                    </div>
                </div>
                <!--按钮状态-->
                <div class="btn-claim-box">
                    <!--未开始-->
                    <ul v-if="couponInfo.couponState == 0">
                        <li>{{ $lang('upcoming') }}</li>
                    </ul>
                    <!--进行中-->
                    <ul v-if="couponInfo.couponState == 1">
                        <!--可以领取-->
                        <li v-if="couponInfo.couponGetState == 1" class="light" @click="getCoupon">{{ $lang('claim') }}</li>
                        <!--已领取-->
                        <li v-else-if="couponInfo.couponGetState == 2" class="light ed">{{ $lang('claimed') }}</li>
                        <!--已领完-->
                        <li v-else>{{ $lang('all_claimed') }}</li>
                    </ul>
                    <!--已结束-->
                    <ul v-if="couponInfo.couponState == 2">
                        <li>{{ $lang('ended') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data', 'pid'],
    data () {
        return {
            platform: GESHOP_PLATFORM || 'web',
            lang: GESHOP_LANG || 'en', // 语言
            couponInfo: {
                couponTotal: 100, // 优惠券总数
                couponRemainder: 100, // 优惠券剩余
                couponState: 0, // （0：未开始， 1：进行中， 2：已结束）
                couponGetState: 1 // 0 进行中状态  为1时 返回 1：可领，2：已领，3：已领完
            }
        };
    },
    computed: {
        percent () {
            return Math.floor(this.couponInfo.couponRemainder / this.couponInfo.couponTotal * 100);
        }
    },
    created () {
    },
    mounted () {
        // 追加函数到队列
        // this.$store.commit('dresslily/update_onresize_marque', this.onChange);
        this.$nextTick(() => {
            // 获取页面cookie， 没有代表未登录
            if (typeof GEShopSiteCommon !== 'undefined' && !GEShopSiteCommon.getCookie('WEBF-user_id')) {
                GEShopSiteCommon.appLogin();
            }
            this.getCouponInfo();
        });
    },
    updated () {
        // 去除loading骨架图
        this.$store.dispatch('global/loaded', this);
    },
    methods: {
        // 获取优惠券信息
        getCouponInfo () {
            const url = GESHOP_INTERFACE.goods_getCouponInfo.url;
            try {
                this.$jsonp(url, {}).then(res => {
                    this.couponInfo = Object.assign({}, res.data.couponInfo);
                });
            } catch (e) {
                console.log(e);
            }
        },
        // 领取优惠券
        getCoupon () {
            const url = GESHOP_INTERFACE.goods_receiveCoupon.url;
            try {
                this.$jsonp(url, {}).then(res => {
                    // 领取成功
                    GEShopSiteCommon.dialog.message(this.$lang('claimed_success'));
                    this.couponInfo.couponGetState = 2; // 按钮变为已领
                    this.couponInfo.couponRemainder = this.couponInfo.couponRemainder - 1; // 剩余数 -1
                    // 其他异常情况
                }, data => {
                    if (data.code === 1) { // app端未登录
                        if (GESHOP_PLATFORM == 'app') {
                            // 获取页面cookie， 没有代表未登录
                            if (!GEShopSiteCommon.getCookie('WEBF-user_id')) {
                                GEShopSiteCommon.appLogin(1);
                            }
                        } else {
                            // web端未登录
                            window.location.href = data.data.loginurl + '?ref=' + window.location.href;
                        }
                    } else {
                        GEShopSiteCommon.dialog.message(data.message);
                    }
                });
            } catch (e) {
                console.log(e);
            }
        }
    },
    watch: {

    }
};
</script>
<style lang="less">
    @import "coupon";
</style>
