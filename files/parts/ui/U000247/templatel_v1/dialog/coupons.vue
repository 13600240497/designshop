<template>
    <div class="inner dia-coupons">
        <h2>{{ $lang('coupon_dialog_title') }}</h2>
        <p class="msg-body" v-html="userData.message"></p>
        <p class="msg-body body-tips" v-html="'(' + $lang('coupon_delay') + ')'"></p>
        <div class="goods" v-if="userData.prize_type === 3">
            <img :src="userData.goods_img" :alt="userData.goods_sku">
        </div>
        <div class="btn_group">
            <span class="btn btn_check_now" @click="userCenter">{{ $lang('lott_check_now') }}</span>
            <span class="btn btn_go_shop" @click="closeCop">{{ $lang('go_shop') }}</span>
        </div>
    </div>
</template>

<script>

export default {
    name: 'coupons',
    props: ['userData'],
    data () {
        return {
        };
    },
    mounted () {
    },
    components: {

    },
    methods: {
        closeCop () {
            this.$emit('hideDia', false);
        },
        userCenter () {
            this.closeCop();
            if (window.GESHOP_PLATFORM == 'app') {
                // window.location.href = 'dresslily://action?actiontype=16&url=xx&name=xxx&source=deeplink';
                if (this.userData.prize_type == 2) {
                    // 积分跳转
                    window.location.href = 'dresslily://action?actiontype=15&url=xx&name=xxx&source=deeplink';
                } else {
                    //    优惠券跳转coupon
                    window.location.href = 'dresslily://action?actiontype=10&name=couponList&source=deeplink';
                }
            } else {
                // window.location.href = window.DOMAIN_USER + '/' + window.JS_LANG + 'm-users.htm';
                // window.location.href = 'dresslily://action?actiontype=16&url=xx&name=xxx&source=deeplink';
                if (this.userData.prize_type == 2) {
                    // 积分跳转
                    window.location.href = window.DOMAIN_USER + '/' + window.JS_LANG + 'm-users-a-points_record.htm';
                } else {
                    //    优惠券跳转coupon
                    window.location.href = window.DOMAIN_USER + '/' + window.JS_LANG + 'm-users-a-coupon.htm';
                }
            }
        }
    }
};
</script>
<style lang="less">
.dia-coupons {
    .msg-body {
        > span {
            color: #DF4D34;
        }
    }
    .goods {
        width: 100px;
        height: 100px;
        position: relative;
        margin: 20px auto 0;
        img {
            height: 100%;
            width: auto;
        }
        @media (max-width: 767px) {
            margin: 10px auto 0;
        }
    }
}
</style>
<style scoped lang="less">
    .dia-coupons {
        h2 {
            margin-bottom: 23px;
        }

        .btn_group {
            margin-top: 25px;
            text-align: center;
        }
        .msg-body {
            max-width: 390px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 25px;
            &.body-tips {
                color: #999999;
            }
        }
    }

    .box-title {
        color: #222222;
        font-size: 28px;
        height: 33px;
        line-height: 33px;
        margin: 50px auto 29px;
    }
    @media (max-width: 767px) {
        .dia-coupons {
            h2 {
                margin-bottom: 11px;
            }
            .msg-body {
                max-width: 200px;
                font-size: 12px;
                line-height: 17px;
                &.body-tips {
                    max-width: 84%;
                    /*font-size: 12px;*/
                }
            }
            .btn_group {
                margin-top: 10px;
                text-align: center;
            }
        }
    }

</style>
