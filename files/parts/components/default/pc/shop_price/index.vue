<template>
    <div class="geshop-shop-price">
        <span
            class="my_shop_price"
            :data-orgp="value"
            :data-currency="currency"
            :data-original_amount="value"
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
        color() {
            if(GESHOP_SITECODE.split('-')[0] == 'rg') {
                return this.$root.data.shop_price_color || '#EA5455';
            } else {
                return this.$root.data.shop_price_color || '#333333';
            }
        }
    },
    mounted() {
        if (this.$root.compKey !== 'U000109') {
            this.$nextTick(() => {
                if (window.GEShopSiteCommon) {
                    window.GEShopSiteCommon.renderCurrency()
                }
            })
        }
    }
}
</script>

<style lang="less" scoped>
    .geshop-shop-price {
        display: inline-block;
        font-size: 22px;
        color: #333;
    }
</style>
