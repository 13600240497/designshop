<template>
    <div class="geshop-U000231-model1-body" :style="style_body">
        <div
            class="wrap geshop-U000231-model1-wrapper geshop-navigator-wrapper"
            :class="[navExpand?'geshop-statu-expand':'geshop-statu-fold',data.isEditEnv == '1'?'edit-now':'',navigation_position == 1? 'geshop-navigator-left':'geshop-navigator-right' ]">
            <div class="geshop-page-navigator text-center geshop-scrollbar">
                <div class="geshop-page-content">
                    <!--                <div class="geshop-page-header">
                                        <img src="" alt="" class="lazyload">
                                    </div>-->
                    <div class="geshop-page-main" v-if="(data.main_active|| 1) == 1">
                        <a :href="data.main_link || 'javascript:;'"
                           :target="data.main_link_target == 1? '_blank':'_self'">
                            <img :src="main_venue_img[this.platform]" @load="imageLoaded"
                                 class="main-img js_gdexp_lazy js-lazy" alt="主会场">
                        </a>
                    </div>
                    <div class="geshop-page-part">
                        <ul>
                            <li v-for="(item,index) in partsArr" :key="index" :data-index="index">
                                <a v-for="(partItem,key) in item.colArr" v-if="key<item.col"
                                   :href="partItem.link || 'javascript:;'"
                                   :key="key"
                                   :target="data.main_link_target == 1? '_blank':'_self'"
                                   :class="'part-col'+ item.col"><span>{{partItem.text}}</span></a>
                            </li>
                        </ul>
                    </div>
                    <div class="geshop-page-goTop" v-if="(data.top_active|| 1) == 1">
                        <img :src="foot_img[this.platform]" @load="imageLoaded" class="js_gdexp_lazy js-lazy">
                        <!--                        <div class="btn-top">{{ this.$root.languages['top']}}</div>-->
                    </div>
                </div>
            </div>

            <!-- fold -->
            <div class="side-btn" @click="handleTransform">
                <div class="side-icon side-right">
                    <span class="icon-img-right"></span>
                </div>
                <div class="side-icon side-left">
                    <span class="icon-img-left"></span>
                </div>

            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    name: 'geshop-U000231-model1-body',
    props: ['theme', 'pid', 'data'],
    data () {
        return {
            bodyWidth: 0,
            platform: 'pc',
            style_body: '',
            list: {
                pc: [],
                pad: [],
                m: []
            },
            thumbnail_img: {
                pc: '',
                pad: '',
                m: ''
            },
            main_venue_img: {
                pc: '',
                pad: '',
                m: ''
            },
            foot_img: {
                pc: '',
                pad: '',
                m: ''
            },
            // 导航默认是否展开
            nav_defualt: {
                pc: 1,
                pad: 1,
                m: 0
            },
            navExpand: true,
            partsArr: [
                {
                    'col': 2,
                    colArr: [
                        { text: 'Plus Size', link: '' },
                        { text: 'Plus Size', link: '' }
                    ]
                },
                {
                    'col': 2,
                    colArr: [
                        { text: 'Plus Size', link: '' },
                        { text: 'Plus Size', link: '' }
                    ]
                },
                {
                    'col': 2,
                    colArr: [
                        { text: 'Plus Size', link: '' },
                        { text: 'Plus Size', link: '' }
                    ]
                },
                {
                    'col': 2,
                    colArr: [
                        { text: 'Plus Size', link: '' },
                        { text: 'Plus Size', link: '' }
                    ]
                },
                {
                    'col': 2,
                    colArr: [
                        { text: 'Plus Size', link: '' },
                        { text: 'Plus Size', link: '' }
                    ]
                },
                {
                    'col': 1,
                    colArr: [
                        { text: 'Plus Size', link: '' }
                    ]
                }
            ]
        };
    },
    watch: {
        bodyWidth (newValue, oldValue) {
            if (newValue >= 1025) {
                // pc
                this.platform = 'pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.platform = 'pc';
                // this.platform = 'pad';
            } else if (newValue <= 767) {
                // m
                this.platform = 'm';
            }
        },
        platform (newVal) {
            this.navExpand = this.nav_defualt[newVal] == 1;
        }
    },
    computed: {
        navigation_position () {
            return this.platform === 'm' ? (this.data.m_nav_default || 0) : (this.data.pc_nav_default || 1);
        },
        wrapper_style () {
        },
        style_body_pc () {
            return {
                'margin-top': `${this.data.pc_marginTop || 0}px`,
                'margin-bottom': `${this.data.pc_marginBottom || 32}px`
            };
        },
        style_body_pad () {
            return {
                'margin-top': `${this.data.pad_marginTop || 0}px`,
                'margin-bottom': `${this.data.pad_marginBottom || 32}px`
            };
        },
        style_body_m () {
            return {
                'margin-top': `${this.data.m_marginTop || 0}px`,
                'margin-bottom': `${this.data.m_marginBottom || 20}px`
            };
        }
    },
    methods: {
        handleMove () {
            this.$nextTick(() => {
                if (this.platform && this.platform === 'pc') {
                } else if (this.platform && this.platform === 'pad') {
                } else if (this.platform && this.platform === 'm') {
                }
            });
        },
        initOffset () {
            if (this.data.isEditEnv == '1') {
                return false;
            }
            let documentH = document.documentElement.clientHeight;
            let $wrapper = $('.geshop-U000231-model1-wrapper .geshop-page-navigator');
            $wrapper.each(function () {
                let $that = $(this);
                // let $wrapperH = $that.height();
                let contentH = $('.geshop-page-content', $that).height();
                if (contentH < documentH) {
                    let offsetTop = (documentH - contentH) / 2;
                    $('.geshop-page-content', $that).css('margin-top', offsetTop + 'px');
                } else {
                    $('.geshop-page-content', $that).css('margin-top', '0');
                }
            });
        },
        imageLoaded () {
            this.initOffset();
        },
        handleTransform () {
            let navExpand = this.navExpand;
            this.navExpand = !navExpand;
        },
        debounce (fn, delay) {
            let args = arguments;
            let context = this;
            let timer = null;

            return function () {
                if (timer) {
                    clearTimeout(timer);

                    timer = setTimeout(function () {
                        fn.apply(context, args);
                    }, delay);
                } else {
                    timer = setTimeout(function () {
                        fn.apply(context, args);
                    }, delay);
                }
            };
        },
        /**
         * 滚动到特定的区域，控制展示或者隐藏当前组件
         * 1. 当屏幕滚动超过1屏，展示。
         * 2. 当网页尾部出现的时候，隐藏。
         * */
        handleHidden () {
            // 是否在首屏
            let in_first_page;
            // 是否在页尾
            let in_last_page;
            // 其他页面数据
            let documentH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight;
            let bodyH = document.getElementsByTagName('body')[0].offsetHeight;
            let osTop = document.documentElement.scrollTop || document.body.scrollTop;
            let footH = document.querySelector('.foot-area').offsetHeight;
            let nodeLists = document.querySelectorAll('.geshop-page-navigator,.geshop-navigator-wrapper .side-btn');

            // 判断是否滚动到底部
            in_last_page = !(bodyH - documentH - osTop >= footH + 10);
            // 判断是否在首屏
            in_first_page = osTop >= documentH;

            // 判断条件上看面
            if (in_first_page === true && in_last_page === false) {
                for (let i = 0; i < nodeLists.length; i++) {
                    nodeLists[i].style.visibility = 'visible';
                    nodeLists[i].style.zIndex = '91';
                }
            } else {
                for (let i = 0; i < nodeLists.length; i++) {
                    nodeLists[i].style.visibility = 'hidden';
                    nodeLists[i].style.zIndex = '-91';
                }
            }
        }
    },
    created () {
        // 默认图片
        this.thumbnail_img.pc = 'https://geshopimg.logsss.com/uploads/HsvKLCn5tj7pQEPJzXgcrSuM3NhUBkFl.png';

        this.main_venue_img = {
            'pc': this.data.pc_main_img_url || 'https://geshopimg.logsss.com/uploads/Uwad8P6p0cb9oYNJyW4TBShuzHtvIrF3.png',
            'pad': this.data.pad_main_img_url || 'https://geshopimg.logsss.com/uploads/Uwad8P6p0cb9oYNJyW4TBShuzHtvIrF3.png',
            'm': this.data.m_main_img_url || 'https://geshopimg.logsss.com/uploads/Uwad8P6p0cb9oYNJyW4TBShuzHtvIrF3.png'
        };

        this.foot_img = {
            'pc': this.data.pc_foot_img_url || 'https://geshopimg.logsss.com/uploads/7xACajVKQm8JBrEcP5614gt9FvfzRSTZ.png',
            'pad': this.data.pad_foot_img_url || 'https://geshopimg.logsss.com/uploads/7xACajVKQm8JBrEcP5614gt9FvfzRSTZ.png',
            'm': this.data.m_foot_img_url || 'https://geshopimg.logsss.com/uploads/7xACajVKQm8JBrEcP5614gt9FvfzRSTZ.png'
        };

        this.nav_defualt = {
            'pc': this.data.pc_nav_active || 1,
            'pad': this.data.pc_nav_active || 1,
            'm': this.data.m_nav_active || 0
        };
        this.navExpand = this.nav_defualt[this.platform] == 1;

        if (typeof this.data.partsArr === 'object' && this.data.partsDataAble == '1') {
            this.partsArr = this.data.partsArr;
        }

        // 初始化视图
        this.$nextTick(() => {
            this.handleMove();
            this.initOffset();
            this.handleHidden();

            window.onscroll = this.debounce(this.handleHidden, 50);
        });
    },

    mounted () {
        const that = this;

        // 获取组件宽度
        window.addEventListener('resize', () => {
            return (() => {
                this.initOffset();
                this.handleMove();
                that.bodyWidth = document.body.clientWidth;
            })();
        });

        // 初始化页面宽度
        that.$nextTick(() => {
            // 预览页 m = 1 pc =2 ipad = 3
            let cw = document.body.clientWidth;
            if (that.isEditEnv == 0) {
                try {
                    const platformt = typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                    if (platformt == 1) {
                        // m
                        that.platform = 'm';
                    } else if (platformt == 2) {
                        // pc
                        that.platform = 'pc';
                    } else if (platformt == 3) {
                        // pad
                        that.platform = 'pad';
                    }
                } catch (e) {
                }
            } else {
                // 编辑模式
                if (cw >= 1025) {
                    // pc
                    that.platform = 'pc';
                    if (this.list.pc.length > 1) that.pcNavShow = true;
                } else if (cw <= 1024 && cw >= 768) {
                    // pad
                    that.platform = 'pad';
                } else if (cw <= 767) {
                    // m
                    that.platform = 'm';
                }
            }
        });
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000231-model1-body {
        .wrap {
            width: 100%;
            height: 100%;
        }
    }

    @media screen and (min-width: 1025px) {
        .geshop-U000231-model1-body {
            .wrap {

            }
        }
    }

    @media screen and (max-width: 1024px) and (min-width: 768px) {
        .geshop-U000231-model1-body {
            .wrap {

            }
        }
    }

    @media screen and (max-width: 767px) and (min-width: 0px) {
        .geshop-U000231-model1-body {
            .wrap {

            }
        }
    }
</style>
