<template>
    <div class="geshop-U000041-template4_v3 geshop-col-12 layout-1200" ref="myTabWrap">
        <div class="gs-tab">
            <!-- 头部导航 -->
            <div class="fixed-wrap">
                <div class="tab-wrap" ref="myTab">
                    <!-- 普通tab -->
                    <div class="nav_tab" v-show="!isFix">
                        <ul>
                            <li v-for="(item, index) in ( (data.navList && data.navList.length > 0 ) ? data.navList : 3 )"
                                :class="['gs-tab-list', {current :curTabox == index}]"
                                :key="index"
                                @click="handleTabChange(index)">
                                <span>{{ item.navName || 'Tab' + (index + 1) }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- 吸顶tab -->
                    <div class="tab-nav-box" v-show="isFix">
                        <div class="swiper-wraper">
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
                        <div class="swiper-button-prev hide" slot="button-prev">
                            <i class="gs-icon gs-icon-arrow-left"></i>
                        </div>
                        <div class="swiper-button-next hide" slot="button-next">
                            <i class="gs-icon gs-icon-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!--商品信息主体-->
            <div class="gs-tab-content">
                <!--loading-->
                <div class="loading-more" v-show="loading" style="padding: 3%;text-align: center;">
                    <img src="https://css.zafcdn.com/imagecache/MZF/images/loading_zf.gif">
                </div>
                <div class="gs-tab-item">
                    <ul class="clearfix">
                        <!--列表-->
                        <li class="list-item" v-for="(item, index) in (dataArr && dataArr.length > 0 ? dataArr : 4)"
                            :key="Number(item.goods_sn) + '_' + index">
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
                                    <geshop-discount :value="item.discount"
                                                     :visible="parseInt(item.discount) > 0"></geshop-discount>
                                </geshop-analytics-href>
                                <!-- quick view -->
                                <geshop-button-quick-view class="item_view"
                                                          :url_quick="item.url_quick"
                                                          :item="item"
                                                          :index="index"
                                                          v-if="Number(item.goods_number) > 0">
                                    <span>{{ data.view_text || '+ Quick View' }}</span>
                                </geshop-button-quick-view>
                                <!--售罄-->
                                <geshop-soldout
                                    :visible="Number(item.goods_number) <= 0">
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
                                    :goods_id="item.goods_id" class="item-title">{{ item.goods_title ||
                                    default_text.goods_title}}
                                </geshop-analytics-href>
                                <!--售价信息-->
                                <p class="item-shop-price">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                    <geshop-market-price :value="item.market_price"
                                                         v-show="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)"></geshop-market-price>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <geshop-pagination
            :visible="pagination.visible"
            :totalCount="pagination.totalCount"
            :currentPage="pagination.currentPage"
            :pageCount="pagination.pageCount"
            :pageSize="pagination.pageSize"
            @change="handlePageChange">
        </geshop-pagination>
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
    computed: {
        isDateRes () {
            // ajax 请求 json文件回来存放的信息
            return this.$store.state.global.isDateRes;
        },
        swiper () {
            // 轮播组件实例
            return this.$refs.mySwiper.swiper;
        },
        // 是否需要吸顶
        needFix () {
            return Number(this.data.is_fixed);
        }
    },
    data () {
        return {
            dataArr: [], // 商品数据信息,
            swiperOption: {
                // swiper 配置项
                spaceBetween: 23,
                slidesPerGroup: 4,
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                }
            },
            scrollFlag: true, // 点击tab时禁用滚动监听
            // goodsArray: null, // 当前组件商品信息
            curTabox: 0, // 当前的选中tab
            isFix: false, // 是否处于吸顶
            // fixInfo firstFix 是否初次吸顶，top 当前scrollTop
            fixInfo: {
                firstFix: false,
                top: ''
            },
            languges: window.GESHOP_LANGUAGES, // 当前语种
            default_text: {
                goods_title: 'Spaghetti Strap Criss Cross Bikini'
            },
            // tab下的分页及状态记录
            tab_data_group: [{
                totalCount: 0, // 总共商品个数
                currentNum: 0, // 当前商品个数
                status: 0, // 0 | 1  > view more | view less,
                page: 1, // 分页当前，默认1
                pageCount: 1, // 分页页数总数
                pageSize: 20, // 分页加载数量
                dataGroup: [], // 分组数据
                scrollTop: '' // tab 滚动值记录
            }],
            pagination: {
                totalCount: 0,
                pageCount: 0,
                currentPage: 1,
                pageSize: 10,
                visible: true
            },
            loading: true
        };
    },
    mounted () {
        this.$nextTick(() => {
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
        // 两个都是disable时 隐藏 否则 显示
        hideBtn () {
            let tabIndex = parseInt(this.data.navList.length || 4);
            let label_width = parseInt(this.data.tab_label_width || 212);
            let label_padding = parseInt(this.data.tab_label_padding || 23);
            let w = label_width * tabIndex + label_padding * (tabIndex - 1);
            // let w = $(this.$refs.myTabWrap).find('.nav_tab ul').width();
            if (w < 1080) {
                this.swiper.allowTouchMove = false;
                $(this.$refs.myTabWrap).find('.swiper-button-prev,.swiper-button-next').addClass('hide');
            } else {
                this.swiper.allowTouchMove = true;
                $(this.$refs.myTabWrap).find('.hide').removeClass('hide');
            }
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
            }, 50);
            // 1 == 导航吸顶
            if (this.needFix !== 0) {
                this.hideBtn();
                // 绑定监听
                this.bindScroll();
            }
        },
        handleTabChange (index) {
            if (this.curTabox === index) {
                return;
            }
            // 记录当前tab scrollTop
            this.recordTabScroll();

            this.loading = true;

            this.curTabox = index;

            // 重新赋值 获取当前tab下数据
            this.handleTabPageEvent();

            /* this.scrollFlag = false;
            if (this.data.is_fixed != 0) {
                $('html,body').animate({
                    scrollTop: $(this.$refs.myTabWrap).offset().top
                }, 300, function () {
                    this.scrollFlag = true;
                });
            } */
            this.loadingHide();
            setTimeout(() => {
                this.$store.dispatch('global/async_goods_init', this);
                // 触发当前tab scrollTop
                this.handleTabScroll();
            }, 50);
        },
        /**
         * 记录当前tab scrollTop
         */
        recordTabScroll () {
            if (this.needFix !== 0) {
                let $element = document.documentElement || document.body;
                let top = this.recordComponentOffsetTop();
                this.tab_data_group[this.curTabox].scrollTop = $element.scrollTop > top ? $element.scrollTop : top;
            }
        },
        /**
         * 触发当前tab scrollTop
         */
        handleTabScroll () {
            // 吸顶处理 pc吸顶取消状态记录
            this.scrollFlag = false;
            if (this.needFix !== 0) {
                let $element = document.documentElement || document.body;
                this.$nextTick(() => {
                    let scrollTop = this.isFix ? 0 : this.tab_data_group[this.curTabox].scrollTop;
                    let top = scrollTop || this.recordComponentOffsetTop();
                    if (top) {
                        $element.scrollTop = top;
                    }
                    this.scrollFlag = true;
                });
            }
        },
        /**
         * 记录组件位置
         */
        recordComponentOffsetTop () {
            // return Math.ceil($(this.$refs.myTabWrap).offset().top) - this.$refs.myTab.offsetHeight - 5;
            if (this.isFix) {
                return Math.ceil($(this.$refs.myTabWrap).offset().top);
            } else {
                return $(window).scrollTop();
            }
        },
        /**
         * 返回组件距离顶部位置
         */
        getComponentOffsetTop () {
            return Math.ceil($(this.$refs.myTabWrap).offset().top);
        },
        /**
         * 构建pagination 数据
         * @param index
         */
        generatePage (index) {
            let $tab = this.tab_data_group[index];
            return {
                totalCount: $tab.totalCount,
                pageCount: $tab.pageCount,
                currentPage: $tab.page,
                pageSize: $tab.pageSize,
                visible: true
            };
        },
        /**
         * 当前tab下页码变跟
         * @param value
         */
        handlePageChange (value) {
            if (value) {
                this.loading = true;
                let currentTab = this.tab_data_group[this.curTabox];
                currentTab.page = value;
                // 更新pagination
                this.pagination = this.generatePage(this.curTabox);
                // 更新当前tab当前页数据
                this.dataArr = currentTab.dataGroup[value - 1];
                this.$store.dispatch('global/async_goods_init', this);
                this.loadingHide();
                // 分页切换回顶
                $('html,body').scrollTop(this.getComponentOffsetTop());
            }
        },
        /**
         * tab切换选中 page更新，并刷新商品数据
         */
        handleTabPageEvent () {
            this.dataArr = this.getCurrentTabData();
            this.pagination = this.generatePage(this.curTabox);
        },
        // 导航滚动监听
        bindScroll () {
            if (!this.$refs.myTabWrap) {
                return;
            }
            let $box = $('#U000041_' + `${this.pid}`); // 组件容器
            let $levelNav = $('div[data-key="U000027"]').find('ul'); // 水平导航
            let $wrap = $(this.$refs.myTabWrap); // 当前组件
            let timer = null;
            let scrollTop = 0; // 滚动高度
            let Top = $wrap.offset().top; // 组件t位置op值
            let H = $wrap.height(); // 组件高度，因为tab每一个高度不一致所以滚动时计算
            let _this = this;
            if (this.scrollFlag) {
                $(window).on('scroll', this.throttle(() => {
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
                            if (!_this.fixInfo.firstFix) {
                                _this.fixInfo.firstFix = true;
                                _this.fixInfo.top = $(window).scrollTop();
                            }
                            _this.swiper.slideTo(_this.curTabox, 500, false);
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
                    }, 10);
                }, 100));
            }
        },
        /**
         * 获取当前tab下当前页数据
         * @returns {*}
         */
        getCurrentTabData () {
            let currentTab = this.tab_data_group[this.curTabox];
            if (currentTab) {
                let currentPage = currentTab.page;
                return this.tab_data_group[this.curTabox].dataGroup[currentPage - 1];
            }
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
