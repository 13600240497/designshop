<template>
    <button class="geshop-button-buynow" v-if="show" :style="style_body">
        {{ label }}
    </button>
</template>

<script>
export default {
    name: 'geshop-buynow',
    props: ['visible'],
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
                style['background-color'] = this.$root.data.buynow_bg_color || '#333333';
                style['border-radius'] = this.radius + 'px';
            };
            return style;
        },
        label () {
            return this.$lang('btn_buy_now');
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
        background: #333;
        color: white;
        border-radius: 35px;
        border: none;
        line-height: 36px;
        padding: 0 16px;
        text-overflow: ellipsis;
        white-space: nowrap;
        word-wrap: break-word;
        overflow: hidden;

        &:hover {
            opacity: .9;
        }
    }
</style>
