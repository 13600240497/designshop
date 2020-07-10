<template>
    <div class="geshop-u000229-default-body" :style="style_body">
        <div class="wrap geshop-u000229-default-wrapper">
            <div class="item-list-box">

                <div class="layout-u000229-for-pc default-wrapper-for" >

                     <!-- for pc+pad -->
                    <template v-if="ad_img[this.platform]">
                        <a :href="data.img_link_url || 'javascript:;'" :target="openType" class="default-image-box" :style="img_style">
                            <img  :src="ad_img[this.platform]"  />
                        </a>
                    </template>

                    <template v-else>
                        <div class="geshop-u000229-default-wrapper-preview default-wrapper" :style="img_style">
                            <img :src="defaultImg" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    name: 'geshop-u000229-default-body',
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            bodyWidth: 0,
            platform: 'pc',
            style_body: '',
            thumbnail_img: {
                pc: '',
                pad: '',
                m: ''
            },
            ad_img: {
                pc: '',
                pad: '',
                m: ''
            },
            classIco: this.data.box_banner_ico == '0',
            defaultImg: 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png'
        };
    },
    watch: {
        bodyWidth (newValue) {
            if (newValue >= 1025) {
                // pc
                this.platform = 'pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.platform = 'pad';
            } else if (newValue <= 767) {
                // m
                this.platform = 'm';
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
        img_style () {
            if (this.platform == 'pc' || this.platform == 'pad') {
                return {
                    'margin-left': `${this.data.pc_pad_margin_left || 10}px`,
                    'margin-right': `${this.data.pc_pad_margin_right || 10}px`
                };
            }
            if (this.platform == 'wap') {
                return {
                    'margin-left': `${this.data.for_m_margin_left || 10}px`,
                    'margin-right': `${this.data.for_m_margin_right || 10}px`
                };
            }
        },

        getImgUrl () {
            let imgURL = '';

            if (this.platform == 'wap') {
                imgURL = this.$root.data.for_m_img || '';
            }
            if (this.platform == 'pc' || this.platform == 'pad') {
                imgURL = this.$root.data.pc_pad_img || '';
            }

            return imgURL;
        }

    },

    methods: {
        // reInit() {
        //     if (sessionStorage.getItem('gs_platform') == 'pc') {
        //         this.platform = 'pc'
        //     } else if (sessionStorage.getItem('gs_platform') == 'pad') {
        //         this.platform = 'pad'
        //     } else if (sessionStorage.getItem('gs_platform') == 'wap') {
        //         this.platform = 'wap'
        //     }
        // },

        setPlatform () {
            if (typeof GLOBAL != 'undefined') {
                const platform = typeof GLOBAL != 'undefined' && typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                if (platform === 1) {
                    this.platform = 'wap';
                    // sessionStorage.setItem('gs_platform', 'wap')
                } else if (platform === 2) {
                    this.platform = 'pc';
                    // sessionStorage.setItem('gs_platform', 'pc')
                } else if (platform === 3) {
                    this.platform = 'pad';
                    // sessionStorage.setItem('gs_platform', 'pad')
                }
            }
        },
        resizeChange () {
            // this.setPlatform();
            this.bodyWidth = document.body.clientWidth;
        }
    },
    created () {
        // 默认图片
        this.thumbnail_img.pc = 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png';
        this.ad_img = {
            pc: this.data.pc_pad_img,
            pad: this.data.pc_pad_img,
            m: this.data.for_m_img
        };
    },

    mounted () {
        this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
        this.bodyWidth = document.body.clientWidth;
        // that.setPlatform() ;
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000229-default-body {
        .wrap {
            width: 100%;
            height: 100%;
            position: relative;
            .item-list-box {
                width: 100%;
                position: relative;
                a {

                    display: block;
                    text-align: center;
                    img {
                        border: 0;
                        width: initial;
                        max-width: 100%;
                        margin: auto;
                        display: block;
                    }
                }
                .default-wrapper {
                    overflow: hidden;
                    display: block;
                    img {
                        width: 100%;
                    }
                }
            }
        }
    }

    /* M 默认图片 */
    @media screen and (max-width: 767px) and (min-width: 0px) {
        .geshop-u000229-default-body {
            .wrap {
                .default-wrapper {

                    img {
                        width: 100%;
                        vertical-align: middle;
                    }
                }
            }
        }
    }
</style>
