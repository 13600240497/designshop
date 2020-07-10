<template>
    <a class="geshop-button-buynow goods-buy-buttom js_logsss_click_delegate_ps"
       :href="href || 'javascript:;'" :target="target" :data-skus="sku">{{ buy_now }}</a>
</template>

<script>
export default {
    name: 'geshop-buynow',
    props: {
        href: {
            type: String
        },
        sku: {
            type: String
        },
        value: {
            type: String
        },
        // 链接跳转类型
        target: {
            type: String,
            default: '_self'
        }
    },
    data () {
        return {};
    },
    computed: {
        buy_now () {
            return this.value || this.$root.data.buy_text || this.$lang('buy_now');
        }

    }
};
</script>

<style lang="less" scoped>
    .geshop-button-buynow {
    }

    .goods-buy-buttom {
        display: inline-block;
        text-align: center;
        border-radius: 20px;
        font-size: 16px;
        max-width: 100%;
        width: 100%;
        height: 36px;
        line-height: 36px;
        background-color: #000000;
        color: #ffffff;
        overflow: hidden;
        font-family: LatoBold, Lato;
    }

    @media (max-width: 767px) {
        .goods-buy-buttom {
            height: 30px;
            line-height: 30px;
            font-size: 13px;
        }
    }
</style>
