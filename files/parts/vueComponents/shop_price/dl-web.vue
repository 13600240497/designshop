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
            return this.$root.data.shop_price_color || '#FF4545';
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
        font-size: 22px;
        font-family: LatoBold;
    }
</style>
