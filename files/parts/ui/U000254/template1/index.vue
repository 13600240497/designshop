<template>
    <div class="geshop-U000254-template1 geshop-component-body geshop-col-12 layout-1200"
         v-if="is_tab_show"
         ref="myTabWrap">
        <div class="gs-tab">
            <!-- 头部导航 -->
            <div class="fixed-wrap">
                <div class="tab-wrap" ref="myTab">
                    <!-- 普通tab -->
                    <div class="nav_tab" v-show="!isFix">
                        <ul>
                            <li v-for="(item, index) in ( (data.tab_list && data.tab_list.length > 0 ) ? data.tab_list : 3 )"
                                :class="['gs-tab-list', {current :curTabox == index}]"
                                :key="index"
                                @click="handleTabChange(index)">
                                <span>{{ item.name || 'Tab' + (index + 1) }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- 吸顶tab -->
                    <div class="tab-nav-box" v-show="isFix">
                        <div class="swiper-wraper">
                            <swiper :options="swiperOption" class="gs-tab-label" ref="mySwiper">
                                <swiper-slide
                                    v-for="(item, index) in ( (data.tab_list && data.tab_list.length > 0 ) ? data.tab_list : 3 )"
                                    :class="['gs-tab-list', {current :curTabox == index}]"
                                    :key="index"
                                >
                                    <span
                                        @click.prevent="handleTabChange(index)">{{ item.name || 'Tab' + (index + 1) }}</span>
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
                <!--view_more_loading-->
                <div class="view_more_loading-more" v-show="view_more_loading" style="padding: 3%;text-align: center;">
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
                                <!--营销信息-->
                                <geshop-promotion :value="item.promotions"></geshop-promotion>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <geshop-pagination :visible="pagination.visible && data.is_pagination == 1" :totalCount="pagination.totalCount"
                           :currentPage="pagination.currentPage"
                           :pageCount="pagination.pageCount"
                           :pageSize="pagination.pageSize"
                           @change="handlePageChange"></geshop-pagination>
    </div>
</template>

<script>
// import '@lib/less/iconfont.less';
import './component.less';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: 'index',
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    computed: {
        /**
         * 是否显示组件
         * @returns {boolean|*}
         */
        is_tab_show () {
            return GESHOP_PAGE_TYPE == 1 || (this.data.tab_list && this.data.tab_list.length > 0);
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
                    nextEl: `#U000254_${this.pid} .geshop-U000254-template1 .swiper-button-next`,
                    prevEl: `#U000254_${this.pid} .geshop-U000254-template1 .swiper-button-prev`
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
            default_text: {
                goods_title: 'Spaghetti Strap Criss Cross Bikini'
            },
            // tab下的分页及状态记录
            tab_data_group: [{
                totalCount: 30, // 总共商品个数
                status: 0, // 0 | 1  > view more | view less,
                currentPage: 1, // 分页当前，默认1
                pageCount: 1, // 分页页数总数
                pageSize: 30, // 分页加载数量
                scrollTop: '' // tab 滚动值记录
            }],
            // 当前tab分页组件信息
            pagination: {
                totalCount: 30,
                pageCount: 1,
                currentPage: 1,
                pageSize: 30,
                visible: true
            },
            // 商品加载loading
            view_more_loading: false,
            imgFilter: true
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
                this.view_more_loading = false;
            }, 10);
        },
        // 两个都是disable时 隐藏 否则 显示
        hideBtn () {
            if (!this.data.tab_list) {
                return false;
            }
            let tabIndex = parseInt(this.data.tab_list.length || 4);
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
        async initMounted () {
            const { tab_list = [] } = this.data;
            if (tab_list.length === 0) {
                // 去处loading
                this.$store.dispatch('global/loaded', this);
                return false;
            }
            this.initDataGroup();
            try {
                await this.handleList();

                this.loadingHide();
                // 去处loading
                this.$store.dispatch('global/loaded', this);
                // 1 == 导航吸顶
                if (this.needFix !== 0) {
                    this.hideBtn();
                    // 绑定监听
                    this.bindScroll();
                }
            } catch (err) {
                // 去处loading
                this.$store.dispatch('global/loaded', this);
            }
        },
        /**
         * 请求商品运营平台数据
         * @returns {Promise<void>}
         * curTabox 当前tab index
         */
        async handleList () {
            const { tab_list = [] } = this.data;
            const curTabox = this.curTabox;
            const tab_info = tab_list[curTabox].goods_source_info;
            try {
                this.view_more_loading = true;
                let res = await this.$GESHOP_DATA_FN(this, {
                    rule_id: tab_info.sop_rule_id,
                    page_no: this.pagination.currentPage
                });
                this.view_more_loading = false;

                this.dataArr = res.data.goods_list;
                // 从接口更新当前tab分页信息
                const { pagination } = res.data;
                const current_pagination = {
                    currentPage: Number(pagination.page_num),
                    totalCount: Number(pagination.total_count),
                    pageSize: Number(pagination.page_size),
                    pageCount: Math.ceil(pagination.total_count / pagination.page_size)
                };
                this.pagination = Object.assign(this.pagination, current_pagination);
                this.$set(this.tab_data_group, curTabox, Object.assign(this.tab_data_group[curTabox], current_pagination));
                this.$store.dispatch('global/async_goods_init', this);
            } catch (err) {
                this.dataArr = [];
                this.$store.dispatch('global/async_goods_init', this);
            }
        },
        async handleTabChange (index) {
            if (this.curTabox === index || this.view_more_loading) {
                return;
            }
            // 记录当前tab scrollTop
            this.recordTabScroll();
            // 重新赋值 获取当前tab下数据
            this.curTabox = index;
            this.pagination = this.generatePage(index);
            await this.handleList();
            this.loadingHide();
            setTimeout(() => {
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
            // 非吸顶tab切换改为不触发滚动
            if (!this.isFix) {
                return false;
            }
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
        // 导航滚动监听
        bindScroll () {
            if (!this.$refs.myTabWrap) {
                return;
            }
            let $box = $('#U000254_' + `${this.pid}`); // 组件容器
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
         * 构建pagination 数据
         * @param index
         */
        generatePage (index) {
            let $tab = this.tab_data_group[index];
            return {
                totalCount: $tab.totalCount,
                pageCount: $tab.pageCount,
                currentPage: $tab.currentPage,
                pageSize: $tab.pageSize,
                visible: true
            };
        },
        /**
         * 当前tab下页码变跟
         * @param value
         */
        async handlePageChange (value) {
            if (value) {
                let currentTab = this.tab_data_group[this.curTabox];
                currentTab.currentPage = value;
                // 更新pagination
                this.pagination = this.generatePage(this.curTabox);
                // 更新当前tab当前页数据
                await this.handleList();
                this.loadingHide();
                // 分页切换回顶
                $('html,body').scrollTop(this.getComponentOffsetTop());
            }
        },
        /**
         * 初始化tab组商品数据状态
         */
        initDataGroup () {
            const { tab_list = [] } = this.data;
            let result = [];
            let tab_length = tab_list.length || 4;
            for (let i = 0; i <= tab_length; i++) {
                result.push({
                    scrollTop: 0,
                    status: 0, // 0 | 1  > view more | view less,
                    totalCount: 0,
                    pageCount: 1,
                    currentPage: 1, // 分页当前，默认1
                    pageSize: 30
                });
            }
            this.tab_data_group = result;
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
