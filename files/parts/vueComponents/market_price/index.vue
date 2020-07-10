<template>
    <div :class="`geshop-market-price is-${$root.platform} is-${site_code}`">
        <del
            v-if="is_show_del === 1"
            class="my_shop_price my-shop-price js_market_wrap dl-has-rrp-tag"
            :data-orgp="value"
            :data-currency="currency"
            :data-original_amount="value">
            ${{ value }}
        </del>
        <span v-else
            class="my_shop_price my-shop-price"
            :data-orgp="value"
            :data-currency="currency"
            :data-original_amount="value">
            ${{ value }}
        </span>
    </div>
</template>

<script>
/**
 * 货币切换：
 * 1. D网 - my-shop-price
 * 2. 其他 - my_shop_price
 *
 * RRP的区别：
 * 1. ZF - ???
 * 2. RG - js_market_wrap 类
 * 3. DL - dl-has-rrp-tag 类
 *
 */
export default {
    name: 'geshop-market-price',
    props: {
        value: {
            default: '0.00'
        },
        currency: {
            default: 'USD'
        },
        is_show_del: {
            type: Number,
            default: 1
        }
    },
    computed: {
        site_code () {
            return GESHOP_SITECODE;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-market-price {
        display: inline-block;
        color: #999;
        &.is-pc {
            font-size: 14px;
        }
        &.is-wap, &.is-app {
            font-size: 24 / 75rem;
        }

        // D网的
        &.is-dl-web, &.is-dl-app {
            font-size: 14px;
            .dl-has-rrp-tag {
                text-decoration: line-through;
            }
        }

        // RG网的
        &.is-rg-wap {
            font-size: 26 / 75rem;
        }

    }

</style>
