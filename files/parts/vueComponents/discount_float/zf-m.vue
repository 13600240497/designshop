<template>
    <div
        class="geshop-components-discount geshop-zaful-discount"
        :style="style_body"
        v-if="show && value_parse > 0">

        <label :style="style_label">

            <!-- 类型1 -->
            <template v-if="type == 1">
                {{ value_parse }}%<br/><i>OFF</i>
            </template>

            <!-- 类型2 -->
            <template v-else>
                -{{ value_parse }}%
            </template>

        </label>

    </div>
</template>

<script>
export default {
    name: 'geshop-discount',
    props: {
        value: {
            default: 0
        },
        visible: {
            type: Boolean,
            default: null
        }
    },
    computed: {
        show () {
            const config_show = (this.$root.data.discount_show === undefined || this.$root.data.discount_show === null) ? true : this.$root.data.discount_show >= 1;
            if (this.visible != null) {
                return this.visible;
            } else {
                return config_show;
            };
        },
        /**
         * 折扣标的类型
         * @default 2
         * @example
         * 1 =  **%OFF
         * 2 =  -***%
         */
        type () {
            return this.$root.data.discount_type || 1;
        },
        /**
         * 折扣标的右边距
         */
        right () {
            return this.$root.data.discount_right || 0;
        },
        /**
         * 折扣标的上边距
         */
        top () {
            return this.$root.data.discount_top || 0;
        },
        /**
         * 整体宽度
         */
        width () {
            return this.$root.data.discount_width || 80;
        },
        /**
         * 整体高度
         */
        height () {
            return this.$root.data.discount_height || 80;
        },
        // 折扣标的自定义样式
        style_body () {
            const style = {
                width: this.$px2rem(this.width),
                height: this.$px2rem(this.height),
                right: this.$px2rem(this.right),
                top: this.$px2rem(this.top),
                color: this.$root.data.discount_font_color || '#fff'
            };
            if (this.$root.data.discount_bg_image) {
                style['background-image'] = `url("${this.$root.data.discount_bg_image}")`;
                style['border-radius'] = 0;
            } else {
                style['background-color'] = this.$root.data.discount_bg_color || '#333333';
            }
            return style;
        },

        // 折扣标文字区域的自定义样式
        style_label () {
            return {
                'top': this.$px2rem(this.$root.data.discount_font_top || 18),
                'right': this.$px2rem(this.$root.data.discount_font_right || 15)
            };
        },

        // 计算值，四舍五入
        value_parse () {
            const nval = Math.round(this.value);
            return nval < 0 ? 0 : nval;
        }
    },
    methods: {
        converPixcel (val) {
            if (window.GESHOP_PLATFORM === 'pc' || window.GESHOP_PLATFORM === 'web') {
                return val + 'px';
            } else {
                return (val / 75) + 'rem';
            }
        }
    }
};
</script>

<style lang="less" scoped>
    // 浮动折扣标
    .geshop-zaful-discount {
        position: absolute;
        right: 0px;
        top: 0px;
        width: 80 / 75rem;
        height: 80 / 75rem;
        border-radius: 80 / 75rem;
        overflow: hidden;
        z-index: 1;
        background-size: 100% 100%;
        > label {
            position: absolute;
            display: block;
            text-align: center;
            font-size: 24 / 75rem;
            line-height: 22 / 75rem;
            font-family: OpenSans-Semibold;
            > i {
                font-size: 22 / 75rem;
                font-style: normal;
                font-weight: 400;
                font-family: OpenSans-Regular,arial,serif;
            }
        }
    }
</style>
