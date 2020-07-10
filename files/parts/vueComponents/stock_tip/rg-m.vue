<template>
    <div class="geshop__mask--stocktip geshop-wap" v-if="show">
        <p>{{ stock_value }}</p>
    </div>
</template>

<script>
export default {
    name: 'geshop-stocktip',
    props: {
        item: {
            type: Object | Number,
            default: {}
        }
    },
    data () {
        return {};
    },
    computed: {
        /**
         * 库存告急显示条件
         * 存在商品库存告急> promotions 不存在营销信息 + 存在库存告急标识 is_stock_urgent = 1 + 组件开启库存告急
         * @returns {*|number|boolean}
         */
        show () {
            const promotion_length = this.item.promotions && this.item.promotions.length;
            const stock_urgent = (parseInt(this.$root.data.stock_urgent) || 1) === 1;
            return stock_urgent && parseInt(this.item.goods_number) && typeof this.item.is_stock_urgent !== 'undefined' && parseInt(this.item.is_stock_urgent) && (!promotion_length);
        },
        stock_value () {
            return this.$lang('stock_left').replace('XX', this.item.goods_number);
        }
    }
};
</script>

<style lang="less">
    .geshop__mask--stocktip.geshop-wap {
        position: absolute;
        left: 0px;
        right: 0px;
        z-index: 1;
        display: block;
        line-height: 0.64rem;
        bottom: 1.013rem;
        text-align: center;
        margin: auto;

        &:hover {
            opacity: .9;
        }

        p {
            text-transform: capitalize;
            width: auto;
            display: inline-block;
            background: rgba(0, 0, 0, 0.6);
            color: #ffffff;
            font-size: 0.32rem;
            padding: 0 0.427rem;
            border-radius: 0.45rem;
        }
    }
</style>
