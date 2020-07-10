<template>
    <div :class="`geshop-shop-price is-${$root.platform} is-${site_code}`">
        <span class="my_shop_price my-shop-price"
                :data-orgp="value"
                :style="{ 'color': color }">
            ${{ value }}
        </span>
    </div>
</template>

<script>
export default {
    name: 'geshop-shop-price',
    props: {
        value: {
            default: '0.00'
        },
        currency: {
            default: 'USD'
        }
    },
    computed: {
        color () {
            return this.$root.data.shop_price_color || '#FA386A';
        },
        site_code () {
            return GESHOP_SITECODE;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-shop-price {
        display: inline-block;
        font-family:Rubik-Medium;
        vertical-align: middle;

        .my_shop_price {
            display: block;
            color: #FA386A;
            font-size: 18px;
            line-height: 22px;
        }
    }
</style>
