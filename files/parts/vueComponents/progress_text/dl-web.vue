<template>
    <div class="geshop-progress-text" :style="style_body">
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
        type: {
            type: Number,
            default: 1
        }
    },
    computed: {
        get_text () {
            let result = null;
            if (this.type === 1) {
                result = this.$lang('only_left').replace('xx%', this.left_percent());
            } else {
                result = this.$lang('left_piece').replace('XX', this.current);
            }
            return result;
        },
        style_body () {
            return {
                color: this.$root.data.limits_text_color || '#333333'
            };
        }
    },
    methods: {
        left_percent () {
            const current = parseInt(this.current);
            const total = parseInt(this.total);
            if (total <= 0) {
                return '0%';
            } else if (current >= total || isNaN(current) || isNaN(total)) {
                return '100%';
            } else {
                return Math.ceil(Number((current / total) * 100)) + '%';
            }
        }
    }
};
</script>

<style lang="less" scoped>

</style>
