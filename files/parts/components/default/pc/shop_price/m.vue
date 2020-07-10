<template>
    <div class="geshop-shop-price">
        <span
            class="my_shop_price"
            :data-orgp="value"
            :data-currency="currency"
            :data-original_amount="value"
            :style="color">
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
                return {
                    color: this.$root.data.shop_price_color || '#EA5455',
                };
            } else {
                return {
                    color: this.$root.data.shop_price_color || '#EA5455'
                };
            }
        }
    },
    mounted() {
        if (this.$root.compKey !== 'U000110' && this.$root.compKey !== 'U000209' && this.$root.theme !== 'rg_quick_buy') {
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
        font-size: 36 / 75rem;
        color: #333;
    }
</style>
