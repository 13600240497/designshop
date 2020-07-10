<template>
    <div class="geshop-components-discount" :style="style_body" v-if="show && value_parse > 0">
        <span>
            <template v-if="type == 1">
                <label>{{ value_parse }}%<br><i>OFF</i></label>
            </template>
            <template v-else>
                <label>-{{ value_parse }}%</label>
            </template>
        </span>
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
        percent: {
            default: 0,
        }
    },
    filters: {
        int(val) {
            return parseInt(val)
        }
    },
    computed: {
        show() {
            const config_show = this.$root.data.discount_show == null ? true : this.$root.data.discount_show >= 1
            if (this.visible != null) {
                return this.visible
            } else {
                return config_show
            }
        },
        right() {
            return this.$root.data.discount_right || 0
        },
        top() {
            return this.$root.data.discount_top || 0
        },
        type() {
            return this.$root.data.discount_type || 1
        },
        style_body() {
            const style = {
                right: this.converPixcel(this.right),
                top: this.converPixcel(this.top),
                color: this.$root.data.discount_font_color || '#fff'
            }
            if (this.$root.data.discount_bg_image) {
                style['background-image'] = `url("${this.$root.data.discount_bg_image}")`;
                style['border-radius'] = 0;
            } else {
                style['background-color'] = this.$root.data.discount_bg_color || '#333333';
            }
            return style;
        },
        // 计算值，四舍五入
        value_parse() {
            const nval = Math.round(this.percent)
            return nval < 0 ? nval * -1 : nval
        }
    },
    methods: {
        converPixcel(val) {
            if (window.GESHOP_PLATFORM == 'pc' || window.GESHOP_PLATFORM == 'web') {
                return val + 'px';
            } else {
                return (val / 75) + 'rem';
            }
        }
    }
}
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
        > span {
            display: table;
            width: 100%;
            height: 100%;
            > label {
                display: table-cell;
                text-align: center;
                vertical-align: middle;
                font-size: 16px;
                line-height: 1em;
                font-weight: bold;
                > i {
                    font-size: 12px;
                    font-style: normal;
                    font-weight: 400;
                }
            }
        }
    }
</style>
