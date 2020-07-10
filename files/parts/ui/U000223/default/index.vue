<template>
    <div class="geshop-u000223-default-body">
        <div class="title-wrap">
            <img class="left-img" :src="img_left" :style="style_img">
            <template v-if="data.link_href">
                <a class="title" :target="openType" :href="data.link_href || 'javascript:;'">{{$root.data.title_text ||
                    'Title Name'}}</a>
            </template>
            <template v-else>
                <span class="title">{{$root.data.title_text || 'Title Name'}}</span>
            </template>
            <img class="right-img" :src="img_right" :style="style_img">
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            screenWidth: 0,
            style_body: '',
            img_left: '',
            img_right: '',
            style_title: '',
            style_title_wrap: '',
            style_img: ''
        };
    },
    computed: {
        platform () {
            return this.$root.is_edit_env;
        },
        // 获取当前端 pc/pad/wap
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        },
        isEditEnv () {
            return this.data.isEditEnv;
        }
    },
    methods: {
        initAroundImg () {
            if (sessionStorage.getItem('gs_platform') === 'pc') {
                this.img_left = this.data.pc_left_img;
                this.img_right = this.data.pc_right_img;
            } else if (sessionStorage.getItem('gs_platform') === 'pad') {
                this.img_left = this.data.pad_left_img;
                this.img_right = this.data.pad_right_img;
            } else if (sessionStorage.getItem('gs_platform') === 'wap') {
                this.img_left = this.data.m_left_img;
                this.img_right = this.data.m_right_img;
            }
        },
        setPlatform () {
            const platform = typeof GLOBAL.util.getPlatform() !== 'undefined' ? GLOBAL.util.getPlatform() : 2;
            if (platform === 1) {
                sessionStorage.setItem('gs_platform', 'wap');
            } else if (platform === 2) {
                sessionStorage.setItem('gs_platform', 'pc');
            } else if (platform === 3) {
                sessionStorage.setItem('gs_platform', 'pad');
            }
        }
    },

    async mounted () {
        const _this = this;

        if (_this.isEditEnv == 1) {
            // _this.currentPlatform = sessionStorage.getItem('gs_platform') || 'pc'
            sessionStorage.setItem('gs_platform', 'pc');
            // 监听当前选择的平台
            window.addEventListener('storage', () => {
                _this.initAroundImg();
            }, false);
        } else {
            _this.setPlatform();
            // 监听窗口拖放
            window.addEventListener('resize', () => {
                setTimeout(() => {
                    _this.setPlatform();
                    _this.initAroundImg();
                }, 300);
            }, false);
        }
        ;
        _this.initAroundImg();
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000223-default-body {
        margin-left: 100px;
        margin-right: 100px;
        background-size: 100% 100%;

        .title-wrap {
            display: inline-block;
            transform: translateY(-50%);

            .left-img {
                display: inline-block;
            }

            .right-img {
                display: inline-block;
            }

            img {
                vertical-align: middle;
            }
        }

        .title {
            border-bottom-style: solid;
        }
    }

    // pad
    @media screen and (max-width: 1024px) and (min-width: 768px) {
        .geshop-u000223-default-body {
            margin-left: 30px;
            margin-right: 30px;
        }
    }

    @media screen and (max-width: 767px) {
        .geshop-u000223-default-body {
            margin-left: 20px;
            margin-right: 20px;
        }
    }
</style>
