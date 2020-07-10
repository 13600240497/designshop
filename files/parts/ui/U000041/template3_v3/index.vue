<template>
    <div class="geshop-U000041-template3_v3 geshop-col-12" ref="myTabWrap" v-show="compShow">
        <div class="gs-tab">
            <!-- 头部导航 -->
            <div class="fixed-wrap">
                <div class="tab-wrap">
                    <div class="tab-nav-box">
                        <swiper :options="swiperOption" class="gs-tab-label" ref="mySwiper">
                            <!--v-for="(item, index) in data.navList || 3"-->
                            <swiper-slide
                                    v-for="(item, index) in ( (data.navList && data.navList.length > 0 ) ? data.navList : 3 )"
                                    :class="['gs-tab-list', {on :curTabox == index}]"
                                    :key="index"
                            >
                                <span @click.prevent="handleChangeTab(index)">{{ item.navName || 'Tab' + (index + 1) }}</span>
                            </swiper-slide>
                        </swiper>
                        <div class="swiper-button-prev hide" slot="button-prev"></div>
                        <div class="swiper-button-next hide" slot="button-next"></div>
                    </div>
                </div>
            </div>

            <!--商品信息主体-->
            <div class="gs-tab-content" ref="listWrap">
                <div class="gs-tab-item" :class="{on :curTabox == indexs}" v-for="(items, indexs) in  goodsArray || ( data.navList && data.navList.length > 0 ? data.navList.length : 3 )" :key="indexs">
                <!--<div class="gs-tab-item">-->
                    <ul class="clearfix">
                        <!--列表-->
                        <!--<li class="list-item" v-for="(item, index) in showNum(dataArr) || 4" :key="item.goods_sn + index">-->
                        <li class="list-item" v-for="(item, index) in showNum(items)" :key="index">
                            <div class="list-item-img">
                                <geshop-analytics-href
                                        :href="item.url_title"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id"
                                        target="_blank"
                                        class="item-img">
                                    <!--图片-->
                                    <geshop-image-goods :src="item.goods_img"
                                                        :sku="item.goods_sn"
                                                        :lazyload="true"
                                                        :index="index">

                                    </geshop-image-goods>
                                    <!--折扣-->
                                    <geshop-discount :value="item.discount">
                                    </geshop-discount>
                                    <!--售罄-->
                                </geshop-analytics-href>
                                <span class="quick_view" @click="handleQuick(item.url_quick)">
                                    {{ data.view_text || '+ Quick View' }}
                                </span>
                                <geshop-soldout
                                        :visible="item.goods_number <= 0">
                                </geshop-soldout>
                            </div>
                            <!--商品标题-->
                            <div class="item-info-box">
                                <geshop-analytics-href
                                        :href="item.url_title"
                                        target="_blank"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id" class="item-title">{{ item.goods_title || 'Spaghetti Strap Criss Cross Bikini'}}
                                </geshop-analytics-href>
                                <!--售价信息-->
                                <p class="item-shop-price">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                    <geshop-market-price :value="item.market_price"
                                                         v-show="item.shop_price  - item.market_price > 0 ? false : true"></geshop-market-price>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
                <!--see more-->
                <a :href="data.more_href ? data.more_href : 'javascript:void(0)'" target="_blank" class="btn-more" v-show="data.more_is_show == 0 ? false : true">
                    {{ data.more_text || 'See More' }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: 'index.vue',
    props: ['data', 'pid'],
    data () {
        return {
            dataArr: [], // 存当前索引数据
            navLoad: [], // 存放导航索引，判断是否需要懒加载
            swiperOption: {
                // swiper 配置项
                slidesPerView: 'auto',
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                }
            },
            compShow: true,
            scrollFlag: true, // 点击tab时禁用滚动监听
            goodsArray: null, // 当前组件商品信息
            curTabox: 0, // 当前的选中tab
            languges: window.GESHOP_LANGUAGES // 当前语种
        };
    },
    components: {
        swiper,
        swiperSlide
    },
    computed: {
        isDateRes () {
            // ajax 请求 json文件回来存放的信息
            return this.$store.state.global.isDateRes;
        },
        swiper () {
            // 轮播组件实例
            return this.$refs.mySwiper.swiper;
        }
    },
    mounted () {
        this.$nextTick(() => {
            // 初始化数据
            this.isDateRes && this.init();
        });
    },
    methods: {
        init () {
            try {
                // 从变量里面去当前组件的商品信息
                this.goodsArray = window.GESHOP_ASYNC_DATA_INFO[this.pid] && window.GESHOP_ASYNC_DATA_INFO[this.pid].length > 0 ? [...window.GESHOP_ASYNC_DATA_INFO[this.pid]] : null;
                this.dataArr = this.goodsArray[this.curTabox].goodsInfo;
            } catch (e) {
            }

            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 当前索引写入数组，表示已进行懒加载
            this.pushIsLoad(0);
            // 箭头隐藏显示
            this.hideBtn();
            // 1 == 导航吸顶
            if (this.data.is_fixed != 0) {
                // 绑定监听
                this.bindScroll();
            }
        },
        // 两个都是disable时 隐藏 否则 显示
        hideBtn () {
            let w = $(this.$refs.myTabWrap).find('.gs-tab-label').width();
            if (w < 1100) {
                this.swiper.allowTouchMove = false;
                $(this.$refs.myTabWrap).find('.swiper-button-prev,.swiper-button-next').addClass('hide');
            } else {
                this.swiper.allowTouchMove = true;
                $(this.$refs.myTabWrap).find('.hide').removeClass('hide');
            }
        },
        /**
             * 切换导航和内容
             * @param {number} index
             */
        handleChangeTab (index) {
            const that = this;
            this.scrollFlag = false;
            if (this.data.is_fixed != 0) {
                $('html,body').animate({
                    scrollTop: $(that.$refs.myTabWrap).offset().top
                }, 300, function () {
                    that.scrollFlag = true;
                });
            }
            if (this.curTabox === index) {
                return;
            }
            this.curTabox = index;
            // this.dataArr = this.goodsArray[index].goodsInfo;
            this.pushIsLoad(index);
            // this.$store.dispatch('global/async_goods_init', this);
        },
        /**
             * 默认显示多少商品
             * @param items
             * @returns {*}
             */
        showNum (items) {
            if (typeof items != 'number' && items.goodsInfo.length > 0) {
                return items.goodsInfo.slice(0, this.data.defaultGoodsCount > 0 ? this.data.defaultGoodsCount : items.length);
            } else {
                return 4;
            }
        },
        /**
         * 触发懒加载
         * @param index 当前导航索引
         */
        pushIsLoad (index) {
            // 数组里面不存在  没有进行过懒加载
            if (this.navLoad.indexOf(index) < 0) {
                this.navLoad.push(index);
                // 商品懒加载
                this.$store.dispatch('global/async_goods_init', {
                    _that: this, // vue 实例
                    $el: $(this.$refs.listWrap).find('.gs-tab-item').eq(index), // 需要懒加载的dom
                    type: 2 // 懒加载类型 2 指display 为 none的元素不进行懒加载
                });
            }
        },
        /**
         * 快速购买弹窗
         * @param url 弹窗链接
         */
        handleQuick (url) {
            window.GEShopSiteCommon.dialog.iframe(url, 1080, 597, true);
        },
        // 导航滚动监听
        bindScroll () {
            let $box = $('#U000041_' + `${this.pid}`); // 组件容器
            let $levelNav = $('div[data-key="U000027"]').find('nav'); // 水平导航

            let $wrap = $(this.$refs.myTabWrap); // 当前组件
            let timer = null;
            let scrollTop = 0; // 滚动高度
            let Top = $wrap.offset().top; // 组件t位置op值
            let H = $wrap.height(); // 组件高度，因为tab每一个高度不一致所以滚动时计算
            if (this.scrollFlag) {
                $(window).on('scroll', function () {
                    scrollTop = $(this).scrollTop(); // 滚动上边距
                    Top = $wrap.offset().top; // 组件t位置op值
                    H = $wrap.height(); // 组件高度，因为tab每一个高度不一致所以滚动时计算
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        if (scrollTop >= Top && scrollTop <= Top + H - $wrap.find('.tab-wrap').height()) {
                            // 滚动到容器时
                            $wrap.find('.tab-wrap').addClass('gs_fixed');
                            // 站点导航栏处理
                            if ($('.js-geshop-nav').length) {
                                $('.js-geshop-nav').hide();
                            }

                            // 水平导航
                            if ($levelNav.length > 0) {
                                $levelNav.hide();
                            }
                            $box.addClass('js-geshop-nav-fixed');
                        } else {
                            // 组件外
                            $wrap.find('.tab-wrap').removeClass('gs_fixed');
                            $box.removeClass('js-geshop-nav-fixed');

                            // 站点导航栏处理
                            if ($('.js-geshop-nav').length) {
                                $('.js-geshop-nav').show();
                            }

                            // 水平导航
                            if ($levelNav.length > 0) {
                                $levelNav.show();
                            }

                            if (GEShopSiteCommon) {
                                GEShopSiteCommon.jsNavFixed();
                            }
                        }
                    }, 10);
                });
            }
        }
    },
    watch: {
        isDateRes () {
            this.init();
        }
    }
};
</script>

<style lang="less">
    .geshop-U000041-template3_v3 {
        .swiper-container {
            overflow: visible;
        }

        .swiper-wrapper {
            display: inline-block;
            white-space: nowrap;
            font-size: 0;
            position: relative;
            height: 64px;
            width: auto;
        }

        .swiper-button-prev, .swiper-button-next {
            background-image: none;
            height: 64px;
            width: 50px;
            top: 0;
            margin-top: 0;
            &.hide {
                display: none;
            }
            &.swiper-button-disabled {
                opacity: 1;

                &:after {
                    content: "";
                    opacity: .35;
                }
            }

            &:hover {
               opacity: .9;
                /*background-color: #D8D8D8;*/
            }

            &:focus {
                outline: none
            }

            &:after {
                content: "";
                border-top: 2px solid #000;
                border-right: 2px solid #000;
                width: 14px;
                height: 14px;
                line-height: 0;
                font-size: 0;
                position: absolute;
                left: 50%;
                top: 50%;
                margin-top: -7px;
            }
        }

        .swiper-button-prev {
            &:after {
                content: "";
                -webkit-transform: rotate(-135deg);
                transform: rotate(-135deg);
                margin-left: -3px;
            }

            left: 0;
        }

        .swiper-button-next {
            &:after {
                content: "";
                margin-left: -12px;
                -webkit-transform: rotate(45deg);
                transform: rotate(45deg);
            }

            right: 0;
        }
    }
</style>
<style scoped lang="less">
    @import "template3_v3.less";
</style>
