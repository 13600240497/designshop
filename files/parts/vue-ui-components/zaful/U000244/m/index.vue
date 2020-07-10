<template>
    <div class="u000244-component-wrapper" ref="navigator" :style="style_body">
        <nav ref="nav">
            <swiper :options="swiperOption" ref="mySwiper" :style="swiper_body">
                <swiper-slide
                    :data-ids="item.component_id"
                    v-for="(item, idx) in list"
                    :key="idx"
                    :class="[ { is_check: idx == navCur }]">
                    <span
                        class="slide"
                        @click="handlerClick(item, idx)"
                        :style="style_slide">
                            {{ item.component_title }}
                    </span>
                </swiper-slide>
            </swiper>
        </nav>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    props: ['id', 'datas', 'styles'],
    data () {
        return {
            swiperOption: {
                slidesPerView: 'auto',
                slideToClickedSlide: true
            },
            key: 'U000242', // 标题栏KEY
            navigatorArr: [], // 选中标题栏ID数组
            navComponent: [], // 导航list
            navCur: 0, // 当前导航
            scrollLocked: false, // 阻止滚动事件
            timer: null,
            scrollFlag: 0 // 是否定位对应标题栏
        };
    },
    components: {
        swiper,
        swiperSlide
    },
    watch: {
        'datas.list': {
            handler (val) {
                this.getStyle();
            }
        },
        styles () {
            this.getStyle();
        }
    },

    mounted () {
        this.$emit('loaded');
        this.getStyle();
        this.bindEvent();
    },

    computed: {
        // 初始化
        swiper () {
            return this.$refs.mySwiper.swiper;
        },

        env () {
            return this.$store.state.page.env;
        },

        // 导航
        list () {
            const list = this.datas.list;
            if (Array.isArray(list) && list.length > 0) {
                return list;
            } else {
                return [{
                    component_id: -1,
                    component_title: '请设置数据'
                }];
            }
        },

        // 占位
        style_body () {
            return {
                'height': this.px2rem(88)
            };
        },

        // 导航样式
        swiper_body () {
            const { bg_color, text_color, text_size } = this.styles;
            return {
                'background-color': bg_color,
                'color': text_color,
                'font-size': this.px2rem(text_size),
                'height': this.px2rem(88),
                'line-height': this.px2rem(88)
            };
        },

        style_slide () {
            // const { padding_left, padding_right } = this.styles;
            return {
                'padding-left': this.px2rem(24),
                'padding-right': this.px2rem(24)
            };
        }
    },

    methods: {
        // 导航点击
        handlerClick (item, idx) {
            const self = this;
            // 不在装修页
            if (self.env != 1) {
                self.navCur = idx; // 选中当前
                self.scrollLocked = true;

                const id = item.component_id; // 定位标题栏ID
                const navigateTarget = $(this.$refs.navigator); // 当前元素

                // 更改样式
                this.getStyle();

                // 定位到对应标题栏
                const top = $('div[data-id="' + id + '"]').offset().top - navigateTarget.height();
                $(window).scrollTop(top);

                // 清除onSroll 的锁
                setTimeout(() => {
                    self.scrollLocked = false;
                }, 100);

                // 是否导航固定
                let is_fixed = top > navigateTarget.offset().top;
                const navTarget = $(self.$refs.nav);
                is_fixed ? navTarget.addClass('is-fixed') : navTarget.removeClass('is-fixed');
            }
        },

        // rem转换
        px2rem (val = 0) {
            return (val / 75) + 'rem';
        },

        // 选中样式
        getStyle () {
            const { active_bg_color, active_text_color } = this.styles;
            this.$nextTick(() => {
                this.$el.querySelectorAll('.swiper-slide').forEach((item) => {
                    item.removeAttribute('style');
                });
                let target = this.$el.querySelector('.is_check');
                if (target) {
                    target.style.backgroundColor = active_bg_color;
                    target.style.color = active_text_color;
                }
            });
        },

        // 事件
        bindEvent () {
            const self = this;
            if (this.env != 1) {
                self.scrollFlag = 0;

                // 判断是否定位, 根据pageTo
                const hash = window.location.hash.split('#');
                if (hash.length > 1) {
                    const target = $(this.$refs.nav); // nav
                    const pageTo = hash[1].split('=')[1]; // 定位目标对象
                    const pageToTarget = $('div[data-id="' + pageTo + '"]'); // 目标对象

                    if (pageToTarget.length > 0) {
                        self.scrollFlag = 1; // 定位

                        const pageToTargetTop = pageToTarget.offset().top;

                        $('html, body').animate({
                            scrollTop: pageToTargetTop
                        }, 400, function () {
                            self.scrollFlag = 0;
                            target.addClass('is-fixed'); // 固定导航

                            // 添加选中样式
                            self.list.map((item, idx) => {
                                if (item.component_id == pageTo) {
                                    self.navCur = idx;
                                }
                            });
                            // 更改样式
                            self.getStyle();
                        });
                    }
                }
                window.addEventListener('scroll', this.scrollCallBack);
            }
        },

        // scroll
        scrollCallBack () {
            const self = this;
            const scrollTop = $(window).scrollTop();
            const target = $(self.$refs.nav); // nav
            const navigateTarget = $(self.$refs.navigator); // 当前组件

            // 是否固定导航
            let is_fixed = scrollTop > navigateTarget.offset().top;

            // 判断阻止, 点击事件时触发scroll
            if (this.scrollLocked === true) {
                return false;
            }

            // 是否导航固定
            if (this.scrollFlag == 0) {
                is_fixed ? target.addClass('is-fixed') : target.removeClass('is-fixed');
            }

            clearTimeout(this.timer);
            this.timer = setTimeout(function () {
                let scrollTopAfter = $(window).scrollTop();
                if (scrollTop == scrollTopAfter) {
                    // 添加选中样式
                    self.list.map((item, idx) => {
                        let id = item.component_id;
                        let titleTarget = $('div[data-id="' + id + '"]');

                        if (scrollTopAfter > titleTarget.offset().top - titleTarget.height() - navigateTarget.height()) {
                            self.navCur = idx;
                        }
                    });
                    // 更改样式
                    self.getStyle();

                    self.$nextTick(() => {
                        let target = $(self.$refs.mySwiper.$el);
                        let slide = target.find('.is_check');
                        let swiperWidth = target.width();

                        let maxTranslate = self.swiper.maxTranslate();
                        let maxWidth = -maxTranslate + swiperWidth / 2;
                        let slideLeft = slide.position().left;
                        let slideWidth = slide.outerWidth(true);
                        let slideCenter = slideLeft + slideWidth / 2;

                        if (slideCenter < swiperWidth / 2) {
                            self.swiper.setTranslate(0);
                        } else if (slideCenter > maxWidth) {
                            self.swiper.setTranslate(maxTranslate);
                        } else {
                            let translate = slideCenter - (swiperWidth / 2);
                            self.swiper.setTranslate(-translate);
                        }
                    });
                }
            }, 50);
        }
    },
    // 销毁scroll事件
    beforeDestroy () {
        if (this.env != 1) {
            window.removeEventListener('scroll', this.scrollCallBack);
        }
    }
};
</script>

<style lang="less">
    .u000244-component-wrapper .is-fixed {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        height: auto;
    }
</style>

<style lang="less" scoped>
    .u000244-component-wrapper {
        display: block;

        .swiper-container {
            display: block;
            height: 88 / 75rem;
            line-height: 88 / 75rem;

            .swiper-slide {
                display: inline-block;
                vertical-align: middle;
                width: auto;
            }
            .swiper-slide .slide {
                display: inline-block;
            }
        }
    }
</style>
