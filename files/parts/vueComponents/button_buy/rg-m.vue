<template>
    <button class="geshop-button-buynow" v-if="show" :style="style_body">
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
            return config_show;
        },
        radius () {
            return this.$root.data.buynow_radius || 4;
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
                style['background-color'] = this.$root.data.buynow_bg_color || '#333333';
                style['border-radius'] = this.$px2rem(this.radius);
            }
            ;
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
        height: 60 / 75rem;
        line-height: 60 / 75rem;
        background: #333;
        color: white;
        border: none;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
    }
</style>
