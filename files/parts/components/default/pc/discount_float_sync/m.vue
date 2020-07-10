<template>
    <div class="geshop-zaful-discount" :style="style_body" v-if="show && value_parse != 0">
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
        value: {
            default: 0,
        },
        visible: {
            type: Boolean,
            default: null,
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
        type() {
            return this.$root.data.discount_type || 1
        },
        right() {
            return this.$root.data.discount_right || 12
        },
        top() {
            return this.$root.data.discount_top || 12
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
            const nval = Math.round(this.value)
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
    .geshop-zaful-discount {
        position: absolute;
        right: 0px;
        top: 0px;
        width: 80 / 75rem;
        height: 80 / 75rem;
        border-radius: 80 / 75rem;
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
                font-size: 24 / 75rem;
                line-height: 22 / 75rem;
                font-weight: bold;
                > i {
                    font-size: 22 / 75rem;
                    font-style: normal;
                    font-weight: 400;
                }
            }
        }
    }
</style>
