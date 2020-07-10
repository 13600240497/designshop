<template>
    <div class="geshop-u000225-default-body"  :style="style_body">
        <p :style="style_content" v-html="textTransfer"></p>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            style_body: '',
            style_content: ''
        };
    },
    computed: {
        textTransfer () {
            if (this.$root.data.content) {
                return this.$root.data.content.replace(/\n|\r\n/g, '<br/>');
            } else {
                return 'DressLily.com Casual Style: Welcome to DressLily.com! We not only strive to offer the latest casual style wear for women and men, but also supply the best home essentials, including dresses, outerwear, sweaters, bags, shoes, home decors, bedding, accessories and more. Enjoy our entire range at affordable prices and special discounts. Shop at DressLily and dress to express .';
            };
        },
        isEditEnv () {
            return this.data.isEditEnv;
        },
        style_body_pc () {
            let is_show = 'block';
            if (this.data.pc_is_show == '0') {
                is_show = 'none';
            };
            return {
                'background-color': `${this.data.component_bg_color || '#EDEDED'}`,
                'margin-top': `${this.data.pc_box_margin_top || '0'}px`,
                'margin-bottom': `${this.data.pc_box_margin_bottom || '32'}px`,
                'width': `${this.data.pc_box_width || '100'}%`,
                'padding-top': `${this.data.pc_box_paddingTB || '32'}px`,
                'padding-bottom': `${this.data.pc_box_paddingTB || '32'}px`,
                'background-image': `url(${this.data.pc_box_bgImg})`,
                'font-size': `${this.data.pc_font_size || 13}px`,
                'display': is_show
            };
        },
        style_body_pad () {
            let is_show = 'block';
            if (this.data.pad_is_show == '0') {
                is_show = 'none';
            };
            return {
                'background-color': `${this.data.component_bg_color || '#EDEDED'}`,
                'margin-top': `${this.data.pad_box_margin_top || '0'}px`,
                'margin-bottom': `${this.data.pad_box_margin_bottom || '32'}px`,
                'width': `${this.data.pad_box_width || '100'}%`,
                'padding-top': `${this.data.pad_box_paddingTB || '32'}px`,
                'padding-bottom': `${this.data.pad_box_paddingTB || '32'}px`,
                'background-image': `url(${this.data.pad_box_bgImg})`,
                'font-size': `${this.data.pad_font_size || 13}px`,
                'display': is_show
            };
        },
        style_body_m () {
            let is_show = 'block';
            if (this.data.m_is_show == '0') {
                is_show = 'none';
            };
            return {
                'background-color': `${this.data.component_bg_color || '#EDEDED'}`,
                'margin-top': `${this.data.m_box_margin_top || '0'}px`,
                'margin-bottom': `${this.data.m_box_margin_bottom || '20'}px`,
                'width': `${this.data.m_box_width || '100'}%`,
                'padding-top': `${this.data.m_box_paddingTB || '32'}px`,
                'padding-bottom': `${this.data.m_box_paddingTB || '32'}px`,
                'background-image': `url(${this.data.m_box_bgImg})`,
                'font-size': `${this.data.m_font_size || 13}px`,
                'display': is_show
            };
        },
        style_content_pc () {
            return {
                'color': `${this.data.text_font_color || '#8C8C8C'}`,
                'font-weight': `${this.data.font_weight}`,
                'margin-left': `${this.data.pc_box_paddingRL || '300'}px`,
                'margin-right': `${this.data.pc_box_paddingRL || '300'}px`
            };
        },
        style_content_pad () {
            return {
                'color': `${this.data.text_font_color || '#8C8C8C'}`,
                'font-weight': `${this.data.font_weight}`,
                'margin-left': `${this.data.pad_box_paddingRL || '100'}px`,
                'margin-right': `${this.data.pad_box_paddingRL || '100'}px`
            };
        },
        style_content_m () {
            return {
                'color': `${this.data.text_font_color || '#8C8C8C'}`,
                'font-weight': `${this.data.font_weight}`,
                'margin-left': `${this.data.m_box_paddingRL || '32'}px`,
                'margin-right': `${this.data.m_box_paddingRL || '32'}px`
            };
        },
        style_btn_upcomming () {
            return {
                'background-color': `${this.data.notstart_bg_color || '#FBF8F7'}`
            };
        }
    },
    methods: {
        reInitComponent () {
            if (sessionStorage.getItem('gs_platform') === 'pc') {
                // pc
                this.style_body = this.style_body_pc;
                this.style_content = this.style_content_pc;
            } else if (sessionStorage.getItem('gs_platform') === 'pad') {
                // pad139:18
                this.style_body = this.style_body_pad;
                this.style_content = this.style_content_pad;
            }
            if (sessionStorage.getItem('gs_platform') === 'wap') {
                // m
                this.style_body = this.style_body_m;
                this.style_content = this.style_content_m;
            }
        },
        setPlatform () {
            const platform = typeof window.GLOBAL.util.getPlatform() !== 'undefined' ? window.GLOBAL.util.getPlatform() : 2;
            if (platform === 1) {
                sessionStorage.setItem('gs_platform', 'wap');
                this.currentPlatform = 'wap';
            } else if (platform === 2) {
                sessionStorage.setItem('gs_platform', 'pc');
                this.currentPlatform = 'pc';
            } else if (platform === 3) {
                sessionStorage.setItem('gs_platform', 'pad');
                this.currentPlatform = 'pad';
            };
        }
    },
    async mounted () {
        const _this = this;
        if (_this.isEditEnv == 1) {
            _this.currentPlatform = sessionStorage.getItem('gs_platform') || 'pc';
            sessionStorage.setItem('gs_platform', 'pc');
            // 监听当前选择的平台
            window.addEventListener('storage', () => {
                _this.reInitComponent();
            }, false);
        } else {
            // 监听窗口拖放
            window.addEventListener('resize', () => {
                setTimeout(() => {
                    _this.setPlatform();
                    _this.reInitComponent();
                }, 300);
            }, false);
        }
        _this.reInitComponent();
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000225-default-body {
        background-size:  100% 100%;
        margin: 0 auto;
    }
</style>
