<template>
    <div class="geshop-U000255-template1 geshop-col-12 layout-1200"
         :class="{'is-whole':whole}" :data-id="pid" ref="myTabWrap" v-if="is_tab_show">
        <div class="gs-tab">
            <!-- 头部导航 -->
            <div class="fixed-wrap" :class="{'expand_open':tab_expand}">
                <div class="tab-wrap">
                    <div class="tab-nav-box">
                        <div class="swiper-wraper" v-show="nextTickReady">
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
                        <div class="expand_arrow" @click="handleExpandNav">
                            <i class="gs-icon gs-icon-arrow-down" v-show="!tab_expand"></i>
                            <i class="gs-icon gs-icon-arrow-up" v-show="tab_expand"></i>
                        </div>
                    </div>
                    <div class="nav_tab">
                        <ul>
                            <li v-for="(item, index) in ( (data.tab_list && data.tab_list.length > 0 ) ? data.tab_list : 3 )"
                                :class="[nav_show_num,{'current':curTabox == index}]"
                                :key="index"><span @click.prevent="handleTabChange(index,'expandTab')">{{item.name || 'Tab' + (index + 1)}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--商品信息主体-->
            <div class="gs-tab-content">
                <ul :class="['gs-tab-item',{current :curTabox === tabIndex}]"
                    v-for="(tabItem,tabIndex) in (tab_data_group)"
                    :style="style_bg_radius"
                    :key="tabIndex">
                    <!--商品列表-->
                    <li v-for="(item, index) in (tabItem.dataGroup && tabItem.dataGroup.length > 0 ? tabItem.dataGroup : 4)"
                        :key="Number(item.goods_sn) + '_' + index" class="list-li">
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
                                <!--营销信息-->
                                <geshop-promotion :value="item.promotions"></geshop-promotion>
                            </div>
                        </div>
                    </li>
                </ul>
                <!--loading-->
                <div class="loading-more" v-show="view_more_loading" style="padding: 3%;text-align: center;">
                    <img src="https://css.zafcdn.com/imagecache/MZF/images/loading_zf.gif">
                </div>
            </div>
        </div>
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
    data () {
        return {
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
            isFix: false, // 是否处于吸顶
            default_text: {
                goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …'
            },
            // dom 是否加载完毕
            nextTickReady: false,
            curTabox: 0, // 当前的选中tab
            // tab下的分页及状态记录
            tab_data_group: [{
                dataGroup: [], // 商品数据列表
                totalCount: 30, // 总共商品个数
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
                visible: true,
                has_more: true, // 是否有更多分页
                can_request: true // 是否允许请求
            },
            tab_expand: false, // tab是否展开
            view_more_loading: false,
            imgFilter: true
        };
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
        // tab展开每行显示tab个数
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
                this.view_more_loading = false;
            }, 10);
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
                    // 绑定监听
                    this.bindScroll();
                }
            } catch (err) {
                // 去处loading
                this.$store.dispatch('global/loaded', this);
            }
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
        // 导航滚动监听
        bindScroll () {
            if (!this.$refs.myTabWrap) {
                return;
            }
            let $box = $('#U000255_' + `${this.pid}`); // 组件容器
            let $levelNav = $('div[data-key="U000030"]').find('nav'); // 水平导航
            let $wrap = $(this.$refs.myTabWrap); // 当前组件
            let timer = null;
            let scrollTop = 0; // 滚动高度
            let Top = $wrap.offset().top; // 组件t位置op值
            let H = $wrap.height(); // 组件高度，因为tab每一个高度不一致所以滚动时计算vagrant add
            let _this = this;
            // 兼容装修页加载数据
            // const scrollTarget = GESHOP_PAGE_TYPE == '1' ? '.design-right' : window;
            const scrollTarget = window;
            const component_ref = this.$refs.myTabWrap;
            if (this.scrollFlag) {
                $(scrollTarget).on('scroll', this.throttle(function () {
                    _scrollTopFix();
                    // 加载更多页
                    if (Number(_this.$root.data.is_pagination) === 1) {
                        _this.scrollBottomEvent(component_ref, scrollTarget, _this);
                    }
                }, 100));
            }

            function _scrollTopFix () {
                scrollTop = $(scrollTarget).scrollTop(); // 滚动上边距
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
            }
        },
        /**
         * 滚动加载更多页面
         * @param component_ref 组件ref
         * @param scrollTarget window/design-right
         * @param _this (vue instance)
         */
        scrollBottomEvent (component_ref, scrollTarget, _this) {
            const pagination = _this.pagination;
            const scrollTop = $(scrollTarget).scrollTop();
            const gsTabOffset = component_ref && $(component_ref).offset();
            if (!this.view_more_loading && pagination.has_more && document.documentElement.clientHeight + scrollTop > gsTabOffset.top + $(component_ref).height() * 2 / 3) {
                if (_this.pagination.can_request) {
                    _this.pagination.can_request = false;
                    // 避免tab切换 currentPage 数据出错
                    const new_page = this.tab_data_group[this.curTabox].currentPage + 1;
                    _this.handleList(new_page);
                }
            }
        },
        /**
         * 请求商品运营平台数据
         * @returns {Promise<void>}
         * curTabox 当前tab index
         */
        async handleList (page_no) {
            const { tab_list = [] } = this.data;
            const curTabox = this.curTabox;
            const tab_info = tab_list[curTabox].goods_source_info;
            try {
                this.view_more_loading = true;
                let res = await this.$GESHOP_DATA_FN(this, {
                    rule_id: tab_info.sop_rule_id,
                    page_no: page_no || this.pagination.currentPage
                });
                // 恢复加载前状态
                this.view_more_loading = false;
                this.pagination.can_request = true;
                // 从接口更新当前tab分页信息
                const { pagination, goods_list } = res.data;
                // 商品列表赋值
                const data_current = [...this.tab_data_group[curTabox]['dataGroup'], ...goods_list];
                this.$set(this.tab_data_group[curTabox], 'dataGroup', data_current);
                const current_pagination = {
                    currentPage: Number(pagination.page_num),
                    totalCount: Number(pagination.total_count),
                    pageSize: Number(pagination.page_size),
                    pageCount: Math.ceil(pagination.total_count / pagination.page_size)
                };
                this.pagination = Object.assign(this.pagination, current_pagination);
                this.$set(this.tab_data_group, curTabox, Object.assign(this.tab_data_group[curTabox], current_pagination));
                this.$store.dispatch('global/async_goods_init', this);

                // 判断是否存在更多分页
                const page_max = Math.ceil(pagination.total_count / pagination.page_size);
                if (Number(pagination.page_num) + 1 > page_max) {
                    this.pagination.has_more = false;
                    this.$set(this.tab_data_group[curTabox], 'has_more', false);
                }
            } catch (err) {
                this.$store.dispatch('global/async_goods_init', this);
            }
        },
        /**
         * tab 切换
         * @param index 下标
         * @param type
         * @returns {Promise<void>}
         */
        async handleTabChange (index, type) {
            if (this.curTabox === index || this.view_more_loading) {
                return;
            }
            // 记录当前tab scrollTop
            this.recordTabScroll();
            // 重新赋值 获取当前tab下数据
            this.curTabox = index;
            this.pagination = this.generatePage(index);
            // 异步数据请求前恢复到当前tab高度
            this.handleTabScroll();
            // tab不存在数据请求新数据
            if (this.tab_data_group[index]['dataGroup'].length === 0) {
                await this.handleList();
                this.loadingHide();
            }
            setTimeout(() => {
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
                visible: true,
                has_more: $tab.has_more,
                can_request: $tab.can_request
            };
        },
        /**
         * 初始化商品数据状态
         */
        initDataGroup () {
            const { tab_list = [] } = this.data;
            let result = [];
            let tab_length = tab_list.length || 4;
            for (let i = 0; i <= tab_length; i++) {
                result.push({
                    dataGroup: [], // tab商品数据列表
                    scrollTop: 0,
                    totalCount: 30,
                    pageCount: 1,
                    currentPage: 1, // 分页当前，默认1
                    pageSize: 30,
                    has_more: true, // 是否有更多分页
                    can_request: true // 是否允许请求
                });
            }
            this.tab_data_group = result;
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
    .geshop-U000255-template1 {
        .gs-off-text {
            em {
                font-style: normal;
            }
        }
    }

</style>
