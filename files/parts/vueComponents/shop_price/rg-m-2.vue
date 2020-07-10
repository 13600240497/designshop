<template>
    <span
        class="geshop-shop-price my_shop_price my-shop-price"
        :data-orgp="value">
        ${{ value }}
    </span>
</template>

<script>
export default {
    name: 'shop-price-rg-m-2',
    props: {
        value: {
            default: '0.00'
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-shop-price {
        display: inline-block;
        font-size: 22px;
        font-family:Rubik-Medium;
    }
</style>
