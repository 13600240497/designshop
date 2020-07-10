<template>
    <div class="geshop-components-discount" :style="style_body" v-if="show && value_parse > 0">

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
        visible: {
            type: Boolean,
            default: null
        },
        value: {
            default: 0
        }
    },
    filters: {
        int (val) {
            return parseInt(val);
        }
    },
    computed: {
        show () {
            const config_show = this.$root.data.discount_show == null ? true : this.$root.data.discount_show >= 1;
            if (this.visible != null) {
                return this.visible;
            } else {
                return config_show;
            }
        },
        type () {
            return this.$root.data.discount_type || 1;
        },
        right () {
            return this.$root.data.discount_right || 0;
        },
        top () {
            return this.$root.data.discount_top || 0;
        },
        /**
         * 整体宽度
         */
        width () {
            return this.$root.data.discount_width || 50;
        },
        /**
         * 整体高度
         */
        height () {
            return this.$root.data.discount_height || 50;
        },
        style_body () {
            const style = {
                width: this.converPixcel(this.width),
                height: this.converPixcel(this.height),
                right: this.converPixcel(this.right),
                top: this.converPixcel(this.top),
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
                'top': this.converPixcel(this.$root.data.discount_font_top || 11),
                'right': this.converPixcel(this.$root.data.discount_font_right || 9)
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
    .geshop-components-discount {
        position: absolute;
        right: 0px;
        top: 0px;
        width: 50px;
        height: 50px;
        border-radius: 50px;
        overflow: hidden;
        z-index: 1;
        background-size: 100% 100%;
        > label {
            font-family: OpenSans-Semibold;
            display: block;
            position: absolute;
            text-align: center;
            font-size: 16px;
            line-height: 14px;
            font-weight: 400;
            > i {
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                font-family: OpenSans-Regular, Arial;
            }
        }
    }
</style>
