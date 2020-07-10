<template>
    <div class="geshop-u000227-default">
        <div class="geshop-u000227-default-wrapper">
            <div class="item-list item-for-pc" v-if="platform == 'pc'">
                <ul>
                    <template v-for="(item, key) in list.pc">
                        <li :key="key" :style="style_width_li">
                            <a class="item-link" :target="openType" :href="item.base_lampLink">{{ item.base_lampText }}
                                <span class="item-line" :style="style_itemline" :class="key === list.pc.length -1 ? 'lastItem': ''">|</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="item-list item-for-pad" v-if="platform == 'pad' || platform == 'wap'">
                <ul class="slide-item-container">
                    <div class="slide-item-box" :class="{ marquee_top:animate }">
                        <template v-for="(item, key) in list.pc">
                            <li :key="key" :style="style_width_pad_li">
                                <a class="item-link" :target="openType" :href="item.base_lampLink" >{{ item.base_lampText }}</a>
                            </li>
                        </template>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">

export default {
    name: 'geshop-u000227-default',
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            timer: null,
            animate: false,
            number: 0,
            platform: '',
            platWidth: 0,
            platformCode: '',
            totalDuration: 3000,
            list: {
                pc: [],
                pad: [],
                m: []
            },
            default_list: [
                { base_lampText: '$55+10% OFF FOR NEW MEMBERS', base_lampLink: '' },
                { base_lampText: 'SALE! SALE! SALE!', base_lampLink: '' }
            ]
        };
    },
    watch: {
        platWidth (val) {
            this.platWidth = val;
            if (val >= 1025) {
                this.platform = 'pc';
            } else if (val <= 1024 && val >= 768) {
                this.platform = 'pad';
            } else if (val <= 767) {
                this.platform = 'wap';
            }
        }
    },
    computed: {
        isEditEnv () {
            return this.data.isEditEnv;
        },
        // 获取当前端 pc/pad/wap
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        },
        style_itemlink () {
            return {
                color: this.data.lamp_fontColor || '#ffffff'
            };
        },
        style_itemline () {
            return {
                width: 1 + 'px',
                color: this.data.lamp_pc_itemColor || '#ffffff'
            };
        },

        style_width_li () {
            const itemLen = this.list.pc.length;
            return {
                width: (100 / itemLen) + '%'
            };
        },

        style_width_pad_li () {
            return {
                width: 100 + '%'
            };
        },

        // 主区域的定制样式
        wrapper_style () {},

        // 预览的定制样式
        preview_style () {}
    },
    methods: {
        // 跑马灯切换
        handleMarquee () {
            this.animate = true;
            setTimeout(() => {
                this.list.pc.push(this.list.pc[0]);
                this.list.pc.shift();
                this.animate = false;
            }, 500);
        },

        handleMove () {
            const waitTimer = this.data.lamp_switchTime ? this.data.lamp_switchTime * 1000 : 3000;
            if (this.platform && this.platform != 'pc') {
                this.timer = setInterval(this.handleMarquee, waitTimer);
            }
        },

        reInit () {
            if (sessionStorage.getItem('gs_platform') == 'pc') {
                this.platform = 'pc';
                this.platWidth = 'pc';
                clearInterval(this.timer);
            } else if (sessionStorage.getItem('gs_platform') == 'pad') {
                this.platform = 'pad';
                this.platWidth = 'pad';
                clearInterval(this.timer);
                this.handleMove();
            } else if (sessionStorage.getItem('gs_platform') == 'wap') {
                this.platform = 'wap';
                this.platWidth = 'wap';
                clearInterval(this.timer);
                this.handleMove();
            }
        },

        setPlatform () {
            const platform = typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;

            if (platform === 1) {
                this.platform = 'wap';
                sessionStorage.setItem('gs_platform', 'wap');
            } else if (platform === 2) {
                this.platform = 'pc';
                sessionStorage.setItem('gs_platform', 'pc');
            } else if (platform === 3) {
                this.platform = 'pad';
                sessionStorage.setItem('gs_platform', 'pad');
            }
        },

        getPlatform () {
            if (this.$root.data.isEditEnv == 0) {
                // wap = 1 pc =2 ipad = 3
                try {
                    const getPlatform = (typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2);
                    this.platformCode = getPlatform;
                } catch (e) {}
            }
        }
    },
    created () {
        // 获取回填数据
        this.list.pc = this.$root.data.dateTemplateArr || [];

        // 初始化动画
        this.handleMove();
    },
    // 初始化
    mounted () {
        const that = this;
        // 装修页处理默认数据
        // if (this.$root.is_edit_env) {}
        if (this.list.pc.length <= 0 || this.list.pc == '[]') {
            this.list.pc = this.default_list;
        }

        if (that.isEditEnv == 1) {
            let platform = sessionStorage.getItem('gs_platform') || 'pc';
            sessionStorage.setItem('gs_platform', platform);

            // 监听当前选择的平台
            window.addEventListener('storage', () => {
                that.reInit();
            }, false);
        } else {
            that.setPlatform();

            // 监听窗口拖放
            window.addEventListener('resize', () => {
                setTimeout(() => {
                    that.setPlatform();
                    that.reInit();
                    this.platWidth = document.body.clientWidth;
                }, 300);
            }, false);
        }

        let cw = document.body.clientWidth;
        if (cw >= 1025) {
            // pc
            this.platform = 'pc';
            clearInterval(this.timer);
        } else if (cw <= 1024 && cw >= 768) {
            // pad
            this.platform = 'pad';
            this.handleMove();
        } else if (cw <= 767) {
            // wap
            this.platform = 'wap';
            this.handleMove();
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000227-default {
        .geshop-u000227-default-wrapper {
            .item-for-pc ul li {
                padding: 0 6px;
                position: relative;
                display: inline-block;
                text-align: center;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                box-sizing: border-box;
                .item-line {
                    position: absolute;
                    right: 2px;
                }
                .lastItem {
                    display: none;
                }
            }
            .slide-item-container {
                position: relative;
                .slide-item-box {
                    width: 100%;
                    position:absolute;
                    top: 0;
                }
            }
            .item-for-pad ul li {
                width: 100%;
                padding: 0 10px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                a {
                    width: 100%;
                    display: block;
                    text-align: center;
                }
            }
        }
    }
</style>
