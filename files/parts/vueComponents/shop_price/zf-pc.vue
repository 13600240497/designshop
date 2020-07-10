<template>
    <div :class="`geshop-shop-price is-${$root.platform} is-${site_code}`">
        <span
                class="my_shop_price my-shop-price"
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
            return this.$root.data.shop_price_color || '#333333';
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
        color: #333;
        font-size: 22px;
        font-family:OpenSans-Semibold;
        &.is-wap, &.is-app {
            font-size: 36 / 75rem;
        }

        // D网的
        &.is-dl-web {
            font-size: 18px;
            font-weight: bold;
        }
    }
</style>
