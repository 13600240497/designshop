<template>
    <div class="geshop-market-price">
        <del v-if="is_show_del === 1">
            <span
                class="my_shop_price"
                :data-orgp="value"
                :data-currency="currency"
                :data-original_amount="value">
                ${{ value }}
            </span>
        </del>
        <span v-else
              class="my_shop_price"
              :data-orgp="value"
              :data-currency="currency"
              :data-original_amount="value">
            ${{ value }}
        </span>

    </div>
</template>

<script>
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
    .geshop-market-price {
        display: inline-block;
        font-size: 14px;
        color: #999;
    }
</style>
