<template>
    <button class="geshop-button-buynow site-font-bold" v-if="show" :style="style_body">
        {{ label }}
    </button>
</template>

<script>
export default {
    name: 'geshop-buynow',
    props: {
        visible: {
            type: Boolean,
            default: true
        },
        value: {
            type: String,
            default: ''
        }
    },
    data () {
        return {};
    },
    computed: {
        show () {
            const config_show = this.$root.data.buynow_show == null ? true : this.$root.data.buynow_show >= 1;
            return this.visible != null ? this.visible >= 1 : config_show;
        },
        radius () {
            return this.$root.data.buynow_radius || 35;
        },
        image () {
            return this.$root.data.buynow_bg_image || '';
        },
        style_body () {
            const style = {
                'color': this.$root.data.buynow_font_color || '#ffffff'
            };
            if (this.image) {
                style['background-image'] = `url('${this.image}')`;
            } else {
                style['background-color'] = this.$root.data.buynow_bg_color || '#222222';
                style['border-radius'] = this.radius + 'px';
            };
            return style;
        },
        label () {
            return this.value || this.$root.data.buyText || this.$lang('btn_buy_now');
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-button-buynow {
        display: inline-block;
        width: 100%;
        font-size: 16px;
        height: 36px;
        background: #222222;
        color: white;
        border: none;
        overflow: hidden;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
        &:hover {
            opacity: .9;
        }
    }
</style>
