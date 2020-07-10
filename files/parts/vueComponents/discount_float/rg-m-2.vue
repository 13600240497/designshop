<template>
    <div class="geshop-rg-discount" v-if="show">
        <label>
            <!-- 类型1 -->
            <template v-if="type == 1">
                <span v-if="dStyle == 0">
                    {{ value | int }}%<i> OFF</i>
                </span>
                <span v-else>
                    {{ value | int }}%<br/><i>OFF</i>
                </span>
            </template>
            <!-- 类型2 -->
            <template v-else>-{{ value | int }}%</template>
        </label>
    </div>
</template>

<script>
export default {
    name: 'discount-rg-m-2',
    props: {
        value: {
            default: 0
        }
    },
    filters: {
        int (val) {
            return Math.round(val);
        }
    },
    computed: {
        // 是否展示折扣标
        show () {
            // 获取配置项的 是否展示
            const config_show = this.$root.data.discount_show == null ? true : this.$root.data.discount_show >= 1;
            // value 展示区间 1-99
            if (this.value <= 0 || this.value >= 100) return false;
            return config_show;
        },
        /**
         * 折扣标的类型
         * @default 2
         * @example
         * 1 =  **%OFF
         * 2 =  -***%
         */
        type () {
            return this.$root.data.discount_type || 0;
        },

        /**
         * 显示样式 0: 方形, 1: 圆形
         * **/
        dStyle () {
            return this.$root.data.discount_style || 0;
        }
    }
};
</script>

<style lang="less" scoped>
    // 浮动折扣标
    .geshop-rg-discount {
        position: absolute;
        right: 0px;
        top: 0px;
        width: 78 / 75rem;
        height: 36 / 75rem;
        overflow: hidden;
        z-index: 1;
        background-size: 100% 100%;

        > label {
            position: absolute;
            display: block;
            text-align: center;
            font-size: 24 / 75rem;
            line-height: 30 / 75rem;
            font-family: Rubik-Medium;

            & i {
                font-size: 22 / 75rem;
                font-style: normal;
                font-weight: 400;
                font-family: Rubik-Medium;
            }
        }
    }
</style>
