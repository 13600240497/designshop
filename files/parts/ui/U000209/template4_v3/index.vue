<template>
    <div class="geshop-U000209-template4_v3 geshop-col-12 layout-1200"
         :class="{'is-whole':whole}" :data-id="pid" ref="myTabWrap">
        <div class="gs-tab">
            <!-- 头部导航 -->
            <div class="fixed-wrap" :class="{'expand_open':tab_expand}">
                <div class="tab-wrap">
                    <div class="tab-nav-box">
                        <div class="swiper-wraper" v-show="nextTickReady">
                            <swiper :options="swiperOption" class="gs-tab-label" ref="mySwiper">
                                <swiper-slide
                                    v-for="(item, index) in ( (data.navList && data.navList.length > 0 ) ? data.navList : 3 )"
                                    :class="['gs-tab-list', {current :curTabox == index}]"
                                    :key="index"
                                >
                                    <span @click.prevent="handleTabChange(index)">{{ item.navName || 'Tab' + (index + 1) }}</span>
                                </swiper-slide>
                            </swiper>
                        </div>
                        <div class="expand_arrow" @click="handleExpandNav">
                            <i class="gs-icon gs-icon-arrow-down icon_down" v-show="!tab_expand"></i>
                            <i class="gs-icon gs-icon-arrow-up icon_down" v-show="tab_expand"></i>
                        </div>
                    </div>
                    <div class="nav_tab">
                        <ul>
                            <li v-for="(item, index) in ( (data.navList && data.navList.length > 0 ) ? data.navList : 3 )"
                                :class="[nav_show_num,{'current':curTabox == index}]"
                                :key="index"><span @click.prevent="handleTabChange(index,'expandTab')">{{item.navName || 'Tab' + (index + 1)}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--商品信息主体-->
            <div class="gs-tab-content">
                <!--loading-->
                <div class="loading-more" v-show="loading" style="padding: 3%;text-align: center;">
                    <img src="https://css.zafcdn.com/imagecache/MZF/images/loading_zf.gif">
                </div>
                <ul class="gs-tab-item" :style="style_bg_radius">
                    <!--商品列表-->
                    <li v-for="(item, index) in (dataArr && dataArr.length > 0 ? dataArr : 4)"
                        :key="Number(item.goods_sn) + '_' + index">
                        <div class="list-item">
                            <!--折扣标-->
                            <div class="item-discount">
                                <geshop-discount :value="item.discount"
                                                 :visible="parseInt(item.discount) > 0"></geshop-discount>
                            </div>

                            <div class="item-image">
                                <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id">
                                    <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :index="index">
                                    </geshop-image-goods>
                                </geshop-analytics-href>
                                <!--sold out-->
                                <geshop-soldout
                                    :visible="Number(item.goods_number) <= 0">
                                </geshop-soldout>
                            </div>


                            <!--sold out -->
                            <!--<div class="item-soldOut" v-if="item.goods_number <= 0">-->
                            <!--<span>{{ sold_out }}</span>-->
                            <!--</div>-->

                            <div class="item-info">
                                <!--sku标题-->
                                <div class="item-title">
                                    <geshop-analytics-href
                                        :item="item"
                                        :index="index">
                                        {{ item.goods_title || default_text.goods_title }}
                                    </geshop-analytics-href>
                                </div>

                                <div class="item-shop-market">
                                    <!--销售价-->
                                    <div class="item-shop">
                                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                    </div>
                                    <!--市场价-->
                                    <div class="item-market">
                                        <template
                                            v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)">
                                            <geshop-market-price :value="item.market_price"></geshop-market-price>
                                        </template>
                                    </div>
                                </div>

                                <!--促销 -->
                                <!-- <div class="item-promotions" v-if="item.promotions && item.promotions.length > 0">
                                    <div class="gs-off-more" :style="style_promotions">
                                        <div v-for="(val, key) in item.promotions" :key="key">
                                            <div class="gs-off-text" v-html="filterVal(val)"></div>
                                        </div>
                                    </div>
                                </div> -->

                            </div>

                        </div>
                    </li>
                </ul>
                <!--more or less-->
                <div class="item-more-wrap" v-if="tab_data_group && tab_data_group.length > 0">
                    <div class="item-more-less"
                         v-if="tab_data_group[this.curTabox].pageCount >1 && tab_data_group[this.curTabox].pageCount > tab_data_group[this.curTabox].page">
                        <div class="view_more" @click="showMoreOrLess(1)">
                            <span>{{ langText.view_more }}</span>
                        </div>
                    </div>

                    <div class="item-more-less"
                         v-if="tab_data_group[this.curTabox].pageCount >1 && tab_data_group[this.curTabox].pageCount === tab_data_group[this.curTabox].page">
                        <div class="view_more" @click="showMoreOrLess(0)">
                            <span>{{ langText.view_less }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import './component.less';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import tab_refresh_sku from '../../../dataCommon/tab_refresh_sku_update.js';

export default {
    name: 'index',
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    extends: tab_refresh_sku,
    data () {
        return {
            dataArr: [], // 商品数据信息,
            swiperOption: {
                // swiper 配置项
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                slideToClickedSlide: true
                // freeMode: true,
                // slideToClickedSlide: true
            },
            scrollFlag: true, // 点击tab时禁用滚动监听
            // goodsArray: null, // 当前组件商品信息
            isFix: false, // 是否处于吸顶
            languges: window.GESHOP_LANGUAGES, // 当前语种
            default_text: {
                goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …'
            },
            langText: {
                quick_view: window.GESHOP_LANGUAGES['quick_view'] || 'QUICK VIEW',
                view_more: window.GESHOP_LANGUAGES['view_more'] || 'View More',
                view_less: window.GESHOP_LANGUAGES['view_less'] || 'View Less'
            },
            // dom 是否加载完毕
            nextTickReady: false,
            curTabox: 0, // 当前的选中tab
            // tab下的分页及状态记录
            tab_data_group: [{
                totalCount: 0, // 总共商品个数
                currentNum: 0, // 当前商品个数
                status: 0, // 0 | 1  > view more | view less,
                page: 1, // 分页当前，默认1
                pageCount: 1, // 分页页数总数
                pageSize: 20, // 分页加载数量
                dataGroup: [], // 分组数据
                dataRecord: [], // 已记录的sku
                scrollTop: '' // tab 滚动值记录
            }],
            tab_expand: false, // tab是否展开
            view_more: {
                isMore: false,
                isLess: false
            },
            loading: true,
            imgFilter: true
        };
    },
    computed: {
        swiper () {
            // 轮播组件实例
            return this.$refs.mySwiper.swiper;
        },
        // 是否需要吸顶
        needFix () {
            return 1;
        },
        style_bg_radius () {
            let style = '';
            this.box_is_whole = this.data.box_is_whole ? this.data.box_is_whole : 1;

            // 背景是否整体式， 1:是，0:否
            if (this.box_is_whole == 1) {
                let _radius = this.data.goods_bg_radius_size ? this.data.goods_bg_radius_size : '24';
                style = {
                    'border-radius': this.$px2rem(_radius),
                    'background-color': '#FFFFFF'
                };
            }
            return style;
        },
        style_promotions () {
            let _color = this.data.promotions_text_color ? this.data.promotions_text_color : '#333333';
            const style = {
                'color': _color
            };
            return style;
        },
        whole () {
            return this.data.box_is_whole == 0;
        },
        nav_show_num () {
            let result = '';
            switch (this.data.tab_slide_preview) {
                case 2:
                    result = 'geshop-col-6';
                    break;
                case 3:
                    result = 'geshop-col-4';
                    break;
                case 4:
                    result = 'geshop-col-3';
                    break;
                default:
                    result = 'geshop-col-6';
            }
            return result;
        }
    },
    mounted () {
        this.$nextTick(() => {
            this.nextTickReady = true;
            this.initMounted();
        });
    },
    methods: {
        /**
         * 隐藏loading
         */
        loadingHide () {
            setTimeout(() => {
                this.loading = false;
            }, 10);
        },
        /**
         * 初始化函数
         */
        initMounted () {
            this.initDataGroup();
            try {
                // 默认为第一个tab 第一组分页
                this.handleTabPageEvent();
            } catch (e) {

            }
            // 非发布页面不执行去除loading,json数据请求完毕再执行
            if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
                this.$store.dispatch('global/loaded', this);
            }

            this.loadingHide();

            // 页面元素初始化
            setTimeout(() => {
                this.$store.dispatch('global/async_goods_init', this);
                // 1 == 导航吸顶
                if (this.needFix !== 0) {
                    // 绑定监听
                    this.bindScroll();
                }
            }, 50);

        },
        /**
         * 记录组件位置
         */
        recordComponentOffsetTop () {
            // return Math.ceil($(this.$refs.myTabWrap).offset().top) - this.swiper.height - 5;
            if (this.isFix) {
                return Math.ceil($(this.$refs.myTabWrap).offset().top);
            } else {
                return $(window).scrollTop();
            }
        },
        /**
         * 回到组件位置
         */
        backComponentOffsetTop () {
            $('html,body').scrollTop(this.recordComponentOffsetTop());
        },
        /**
         * view more/less switch
         * @param type 0 less 1 More
         */
        showMoreOrLess (type) {
            let currentTab = this.tab_data_group[this.curTabox];
            // less
            if (type === 0) {
                this.view_more = {
                    isMore: true,
                    isLess: false
                };
                // 取tab下第一页
                this.dataArr = currentTab.dataGroup[0];
                currentTab.page = 1;
                this.backComponentOffsetTop();
                // TODO: 更新scroll位置
            }
            if (type === 1) {
                let oldData = this.dataArr;
                if (oldData.length < currentTab.totalCount) {
                    this.dataArr = oldData.concat(currentTab.dataGroup[currentTab.page]);
                    currentTab.page += 1;
                }
            }
            this.$nextTick(() => {
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            });

        },
        // 导航滚动监听
        bindScroll () {
            if (!this.$refs.myTabWrap) {
                return;
            }
            let $box = $('#U000209_' + `${this.pid}`); // 组件容器
            let $levelNav = $('div[data-key="U000030"]').find('nav'); // 水平导航
            let $wrap = $(this.$refs.myTabWrap); // 当前组件
            let timer = null;
            let scrollTop = 0; // 滚动高度
            let Top = $wrap.offset().top; // 组件t位置op值
            let H = $wrap.height(); // 组件高度，因为tab每一个高度不一致所以滚动时计算
            let _this = this;
            if (this.scrollFlag) {
                $(window).on('scroll', this.throttle(function () {
                    scrollTop = $(window).scrollTop(); // 滚动上边距
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
                            // 记录tab吸顶信息
                            _this.isFix = true;
                            // _this.swiper.slideTo(_this.curTabox, 500, false);
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
                            _this.isFix = false;
                        }
                        if (scrollTop === 0) {
                            $('.js-geshop-nav').show();
                            $wrap.find('.tab-wrap').removeClass('gs_fixed');
                            $box.removeClass('js-geshop-nav-fixed');
                            $('#page').css('padding-top', $('#pageHeader').height());
                        }
                    }, 10);
                }, 100));
            }
        },
        filterVal (val) {
            return val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
        },
        handleTabChange (index, type) {
            if (this.curTabox === index) {
                return;
            }
            // 记录当前tab scrollTop
            this.recordTabScroll();

            this.loading = true;

            // 记录当前sku
            this.recordTabGoodsInfo();

            this.curTabox = index;

            // 重新赋值 获取当前tab下数据
            this.handleTabPageEvent();

            this.loadingHide();

            setTimeout(() => {
                this.$store.dispatch('global/async_goods_init', this);
                // 触发当前tab scrollTop
                this.handleTabScroll();

                if (type && type === 'expandTab') {
                    this.handleExpandNav();
                    this.swiper.slideTo(index, 500, false);
                }
            }, 50);
        },
        /**
         * 记录当前tab scrollTop
         */
        recordTabScroll () {
            if (this.needFix !== 0) {
                let scrollTop = $(window).scrollTop();
                let top = this.recordComponentOffsetTop();
                if (this.tab_data_group.length > 0) {
                    this.tab_data_group[this.curTabox].scrollTop = scrollTop > top ? scrollTop : top;
                }

            }
        },
        /**
         * 触发当前tab scrollTop
         */
        handleTabScroll () {
            // 吸顶处理
            this.scrollFlag = false;
            if (this.needFix !== 0) {
                this.$nextTick(() => {
                    let componentTop = this.recordComponentOffsetTop();
                    let recordTop = this.tab_data_group[this.curTabox].scrollTop;
                    // 是否是吸顶状态
                    let scrollTop = this.isFix ? (recordTop > componentTop ? recordTop : componentTop) : recordTop;
                    if (scrollTop) {
                        $('html,body').scrollTop(scrollTop);
                    }
                    this.scrollFlag = true;
                });
            }
        },
        /**
         * 记录当前tab sku
         */
        recordTabGoodsInfo () {
            if (this.tab_data_group.length > 0) {
                this.tab_data_group[this.curTabox].dataRecord = this.dataArr;
            }

        },
        handleTabGoodsInfo () {

        },
        /**
         * tab切换选中 page更新，并刷新商品数据
         */
        handleTabPageEvent () {
            this.dataArr = this.getCurrentTabData();
        },
        /**
         * 获取当前tab下当前记录数据
         * @returns {*}
         */
        getCurrentTabData () {
            let currentTab = this.tab_data_group[this.curTabox];
            let currentPage = currentTab.page;
            return this.tab_data_group[this.curTabox].dataRecord;
            // return this.tab_data_group[this.curTabox].dataGroup[currentPage - 1];
        },
        /**
         * 初始化商品数据状态
         */
        initDataGroup () {
            let page_total_number = Number(this.data.page_total_number);

            let result = [];
            // 判断是否存在数据
            if (this.goodsArray.length > 0) {
                this.goodsArray.forEach((item) => {
                    // 每页总数限制
                    item.goodsInfo = page_total_number ? item.goodsInfo.slice(0, page_total_number) : item.goodsInfo;
                    let page_show_goods_number = parseInt(this.data.page_show_goods_number) || 20; // 默认展示前20；
                    let dataGroup = getDataArrayGroup(item.goodsInfo, page_show_goods_number);
                    // 每个tab状态记录
                    result.push({
                        currentNum: '', // 当前商品个数
                        scrollTop: 0,
                        dataGroup: dataGroup,
                        dataRecord: dataGroup[0],
                        status: 0, // 0 | 1  > view more | view less,
                        totalCount: item.goodsInfo.length,
                        pageCount: Math.ceil(item.goodsInfo.length / page_show_goods_number),
                        page: 1, // 分页当前，默认1
                        pageSize: page_show_goods_number
                    });
                });
            } else {
                for (let i = 0; i <= 4; i++) {
                    result.push({
                        currentNum: '', // 当前商品个数
                        scrollTop: 0,
                        dataGroup: [],
                        dataRecord: [],
                        status: 0, // 0 | 1  > view more | view less,
                        totalCount: 0,
                        pageCount: 1,
                        page: 1, // 分页当前，默认1
                        pageSize: 4
                    });
                }
            }

            this.tab_data_group = result;

            /**
             * 商品数据分组
             * @param arr target Array
             * @param length Array group length
             * @returns {Array}
             */
            function getDataArrayGroup (arr, length) {
                let result = [];
                for (let i = 0, len = arr.length; i < len; i += length) {
                    result.push(arr.slice(i, i + length));
                }
                return result;
            }
        },
        /**
         * 展开tab导航
         */
        handleExpandNav () {
            this.tab_expand = !this.tab_expand;

        },
        throttle (fn, delay, atleast) {
            let timer = null;
            let previous = null;

            return function () {
                let now = +new Date();
                if (!previous) {
                    previous = now;
                }
                if (atleast && now - previous > atleast) {
                    fn();
                    previous = now;
                    clearTimeout(timer);
                } else {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        fn();
                        previous = null;
                    }, delay);
                }
            };
        }
    }
};
</script>

<style lang="less">
    .geshop-U000209-template4_v3 {
        .gs-off-text {
            em {
                font-style: normal;
            }
        }
    }

</style>
