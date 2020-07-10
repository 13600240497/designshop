<template>
    <div
        class="geshop-components-discount"
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
         * 显示类型
         * **/
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
         * 左偏移
         *
         * **/
        left () {
            return this.$root.data.discount_left || 0;
        },

        /**
         *  上偏移
         *
         * **/
        top () {
            return this.$root.data.discount_top || 16;
        },
        /**
         * 整体宽度
         */
        width () {
            return this.$root.data.discount_width || 60;
        },
        /**
         * 整体高度
         */
        height () {
            return this.$root.data.discount_height || 30;
        },

        /**
         * 折扣标整体样式
         * **/
        style_body () {
            const _data = this.$root.data;
            const style = {
                width: this.converPixcel(this.width),
                height: this.converPixcel(this.height),
                left: this.converPixcel(this.left),
                top: this.converPixcel(this.top),
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
                'top': this.converPixcel(this.$root.data.discount_font_top || 9),
                'left': this.converPixcel(this.$root.data.discount_font_left || 10)
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
    .geshop-components-discount {
        position: absolute;
        left: 0px;
        top: 0px;
        width: 60px;
        height: 30px;
        overflow: hidden;
        z-index: 1;
        > label {
            font-family:Rubik-Medium;
            display: block;
            position: absolute;
            text-align: center;
            font-size: 16px;
            line-height: 14px;
            font-weight: 400;
            & i {
                font-size: 14px;
                font-style: normal;
                font-weight: 400;
                font-family: OpenSans-Regular, Arial;
            }
        }
    }
</style>
