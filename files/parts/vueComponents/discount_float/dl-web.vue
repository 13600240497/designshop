<template>
    <div class="geshop-components-discount" :class="{'dl-discount': type == 3}" :style="[sizeW, style_body]"
         v-if="show && value_parse > 0">
        <span>
          <template v-if="type == 1">
              <label v-if="nowrap == 1">{{ value_parse }}%<i>OFF</i></label>
              <label v-else>{{ value_parse }}%<br><i>OFF</i></label>
          </template>
          <template v-else-if="type == 2">
              <label>-{{ value_parse }}%</label>
          </template>
          <template v-else-if="type == 3">
            <label>{{ value_parse }}%</label>
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
            default: 0
        },
        value: {
            default: 0
        }
    },
    data () {
        return {
            sizeW: {
                width: 0,
                height: 0
            }
        };
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
            };
        },
        right () {
            return this.$root.data.discount_right || 0;
        },
        top () {
            return this.$root.data.discount_top || 0;
        },
        type () {
            return this.$root.data.discount_type || 1;
        },
        nowrap () {
            return this.$root.data.discount_type_nowrap || 0;
        },
        style_body () {
            const style = {
                right: this.right + 'px',
                top: this.top + 'px',
                color: this.$root.data.discount_font_color || '#fff'
            };
            if (this.background) {
                style['background-image'] = `url("${this.background}")`;
                style['border-radius'] = 0;
            } else {
                style['background-color'] = this.$root.data.discount_bg_color || '#333333';
            };
            return style; 
        },
        background () {
            if (this.media_platform === 'pc' || this.media_platform === 'pad') {
                return this.$root.data.discount_bg_image;
            } else {
                return this.$root.data.discount_bg_image_m;
            }
        },
        // 计算值，四舍五入
        value_parse () {
            const nval = Math.round(this.percent);
            return nval < 0 ? 0 : nval;
        },
        // pc/wap/pad
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        }
    },
    methods: {
        converPixcel (val) {
            if (window.GESHOP_PLATFORM === 'web') {
                return val + 'px';
            }
            ;
        },
        fixdW () {
            if (document.documentElement.clientWidth < 768) {
                this.$set(this.sizeW, 'width', (this.$root.data.discount_width_m || 40) + 'px');
                this.$set(this.sizeW, 'height', (this.$root.data.discount_height_m || 40) + 'px');
            } else {
                this.$set(this.sizeW, 'width', (this.$root.data.discount_width || 50) + 'px');
                this.$set(this.sizeW, 'height', (this.$root.data.discount_height || 50) + 'px');
            }
            ;
        }
    },
    mounted () {
        this.$nextTick(() => {
            this.fixdW();
        });
        window.addEventListener('resize', () => {
            this.sizeW = {};
            this.fixdW();
        });
    }
};
</script>

<style lang="less" scoped>
    // 浮动折扣标
    .geshop-components-discount {
        position: absolute;
        right: 0px;
        top: 0px;
        border-radius: 50px;
        overflow: hidden;
        z-index: 1;

        &.dl-discount {
            position: absolute;
            right: 6px;
            top: 6px;
            width: 46px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 0;

            label {
                font-size: 12px;
                font-weight: normal;
            }
        }

        > span {
            display: table;
            width: 100%;
            height: 100%;

            > label {
                display: table-cell;
                text-align: center;
                vertical-align: middle;
                font-size: 16px;
                line-height: .9em;
                font-weight: bold;
                font-family: LatoBold;
                @media (max-width: 768px) {
                    font-size: 14px;
                }

                > i {
                    font-size: 12px;
                    font-family: Lato;
                    font-style: normal;
                    font-weight: 400;
                }
            }
        }
    }
</style>
