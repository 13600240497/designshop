<template>
    <div
        class="geshop-components-discount geshop-rg-discount"
        :style="style_body"
        v-if="show">

        <label :style="style_label">
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
    name: 'geshop-discount',
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
        },

        /**
         * 折扣标的右边距
         */
        left () {
            return this.$root.data.discount_left || 0;
        },
        /**
         * 折扣标的上边距
         */
        top () {
            return this.$root.data.discount_top || 32;
        },
        /**
         * 整体宽度
         */
        width () {
            return this.$root.data.discount_width || 78;
        },
        /**
         * 整体高度
         */
        height () {
            return this.$root.data.discount_height || 36;
        },
        // 折扣标的自定义样式
        style_body () {
            const _data = this.$root.data;
            const style = {
                width: this.$px2rem(this.width),
                height: this.$px2rem(this.height),
                left: this.$px2rem(this.left),
                top: this.$px2rem(this.top),
                color: _data.discount_font_color || '#fff'
            };
            // 圆角
            style['border-top-left-radius'] = this.converPixcel(_data.discount_border_radius_left_top);
            style['border-top-right-radius'] = this.converPixcel(_data.discount_border_radius_right_top);
            style['border-bottom-right-radius'] = this.converPixcel(_data.discount_border_radius_right_bottom);
            style['border-bottom-left-radius'] = this.converPixcel(_data.discount_border_radius_left_bottom);

            if (_data.discount_bg_image) {
                style['background-image'] = `url("${_data.discount_bg_image}")`;
                style['background-repeat'] = 'no-repeat';
                style['background-size'] = '100% 100%';
                style['background-position'] = 'left center';
                style['border-radius'] = 0;
            } else {
                style['background-color'] = _data.discount_bg_color || '#FA386A';
            }
            return style;
        },

        // 折扣标文字区域的自定义样式
        style_label () {
            return {
                'top': this.$px2rem(this.$root.data.discount_font_top || 8),
                'left': this.$px2rem(this.$root.data.discount_font_left || 9)
            };
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
            line-height: 24 / 75rem;
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
