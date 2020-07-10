<template>
    <div :class="`geshop-progress-text is-${site_code}`" :style="style_body">
        <!-- 库存显示方式 1 only xx % left 百分比 2 only xx left 1-->
        {{ get_text }}
    </div>
</template>

<script>
export default {
    name: 'geshop-progress-text',
    props: {
        current: {
            default: 0
        },
        total: {
            default: 0
        },
        goods_number: {
            default: 0
        },
        type: {
            type: Number,
            default: 1
        },
        // 商品详情
        item: {
            type: Object | Number,
            default: {}
        }
    },
    computed: {
        goodsInfo () {
            return {
                goods_number: this.item.goods_number || this.goods_number,
                activity_number: this.item.activity_number || this.total,
                activity_left_number: this.item.activity_left_number || this.current
            };
        },
        get_text () {
            let result = null;
            if (this.type === 1) {
                // result = this.$lang('only_left').replace('xx%', this.left_percent());
                const lang_text = this.$lang('left').charAt(0).toUpperCase() + this.$lang('left').slice(1);
                result = `${this.left_percent()} ${lang_text}`;
            } else {
                result = this.$lang('left_piece').replace('XX', this.left_value);
            }
            return result;
        },
        style_body () {
            return {
                color: this.$root.data.limits_text_color || this.$root.data.stock_tip_font_color || '#333333'
            };
        },
        site_code () {
            return window.GESHOP_SITECODE;
        }
    },
    methods: {
        left_percent () {
            const current = parseInt(this.goodsInfo.activity_left_number);
            const total = parseInt(this.goodsInfo.activity_number);
            const goods_number = parseInt(this.goodsInfo.goods_number);
            if (total <= 0 || goods_number <= 0) {
                return '0%';
            } else if (current >= total || isNaN(current) || isNaN(total)) {
                return '100%';
            } else {
                return Math.ceil(Number((current / total) * 100)) + '%';
            }
        },
        left_value () {
            const current = parseInt(this.goodsInfo.activity_left_number);
            const goods_number = parseInt(this.goodsInfo.goods_number);
            return current > 0 && goods_number > 0 ? current : 0;
        }
    }
};
</script>

<style lang="less">
    .geshop-progress-text {
        &.is-rg-wap, &.is-rg-app {
            height: 28/75rem;
            line-height: 28/75rem;
            font-size: 24/75rem;
            color: #666666;
            display: block;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
    }
</style>
