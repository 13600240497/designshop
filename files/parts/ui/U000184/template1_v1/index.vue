<template>
    <div class="geshop-component-body geshop-u000184-template1_v1-body" :class="boxWrapMedia">
        <div class="flex__body">
            <a :href="img_jump_url ? img_jump_url : null" class="jump_url" target="_blank">
                <div class="banner__img--box">
                    <img :src="img_url" class="img-url"/>
<!--                    <img :src="img_url" :data-original="img_url" class="img-url" :class="imageClassName"/>-->
                </div>
            </a>
            <template v-if="showMask">
                <!-- banner蒙层 -->
                <div class="mask"></div>
                <!-- banner 时间文案bg -->
                <div class="time-bg">
                    <!-- banner 时间文案 -->
                    <div class="time-text">
                        {{ langText }}
                    </div>
                </div>
            </template>
        </div>

    </div>

</template>

<script>
export default {
    name: 'u000184-template1_v1',
    props: ['data', 'pid'],
    data () {
        return {
            boxWrapMedia: '', // 当前端class
            view_platform: 'pc', // viewport窗口类型 pc,pad m
            img_jump_url: '',
            langText: '',
            isEditEnv: 0,
            dialogRuleShow: false,
            showMask: false,
            image_url: 'https://geshopimg.logsss.com/uploads/GbQJA0SDdhjUvMsEu9KL1ktNoYn3qXIP.png',
            image_url_m: 'https://geshopimg.logsss.com/uploads/J47pELy5jsPiux3OzfnNXoFhZKtH8wMg.png',
            default_image_url: 'https://geshopimg.logsss.com/uploads/GbQJA0SDdhjUvMsEu9KL1ktNoYn3qXIP.png',
            default_image_url_m: 'https://geshopimg.logsss.com/uploads/J47pELy5jsPiux3OzfnNXoFhZKtH8wMg.png',
            img_url: '',
            lazy_img: '' // 当前默认图
        };
    },
    created () {
        if (this.data) {
            this.isEditEnv = this.data.isEditEnv;
            this.img_jump_url = this.data.img_jump_url;

            if (this.data.image_url !== '') {
                this.image_url = this.data.image_url;
            }

            if (this.data.image_url_m !== '') {
                this.image_url_m = this.data.image_url_m;
            }
        }
    },
    computed: {
        // <img> 标签样式名
        imageClassName () {
            return this.is_edit_env ? '' : 'js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy';
        }
    },
    mounted () {
        this.mastCheck();
        this.resizeChange();
        this.$store.commit('dresslily/update_onresize_marque', this.debounce(() => {
            this.resizeChange();
        }, 300, { context: this }));
    },
    methods: {
        resizeChange () {
            // this.setPlatform();
            const newValue = document.body.clientWidth || document.documentElement.clientWidth;
            let boxWrapMedia = '';
            if (newValue >= 1025) {
                // pc
                boxWrapMedia = 'geshop_dl_pc';
                this.view_platform = 'pc';
                this.img_url = this.image_url;
                this.lazy_img = this.default_image_url;
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                boxWrapMedia = 'geshop_dl_pad';
                this.view_platform = 'pad';
                this.img_url = this.image_url;
                this.lazy_img = this.default_image_url;
            } else if (newValue <= 767) {
                // m
                boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
                this.view_platform = 'm';
                this.img_url = this.image_url_m;
                this.lazy_img = this.default_image_url_m;
            }
            this.boxWrapMedia = boxWrapMedia;
            // this.$store.dispatch('global/async_goods_init', this);
        },
        mastCheck () {
            this.$nextTick(() => {
                // 去loading
                this.$store.dispatch('global/loaded', this);
                if (this.data.active_time != '' && this.data.active_time != undefined) {
                    let times = this.data.active_time.split(' - ');
                    let start = new Date(times[0].replace(/-/g, '/')).getTime();
                    let end = new Date(times[1].replace(/-/g, '/')).getTime();
                    let now = Date.now();
                    if (!!!start || !!!end) {
                        return false;
                    }
                    // ing
                    if (start <= now && now <= end) {
                        this.showMask = false;
                        //    未开始
                    } else if (start > now) {
                        this.langText = this.$lang('coming_soon');
                        this.showMask = true;
                    } else { //    end
                        this.langText = this.$lang('ended');
                        this.showMask = true;
                    }
                } else {
                    this.showMask = false;
                }
            });
        },
        debounce (fn, wait, options) {
            wait = wait || 0;
            let timerId;

            function debounced () {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                }
                let args = Array.prototype.slice.call(arguments);
                timerId = setTimeout(() => {
                    fn.apply(options.context, args.concat(options));
                }, wait);
            }

            return debounced;
        }
    }
};
</script>

<style lang="less" scoped>
    @import './component.less';
</style>
