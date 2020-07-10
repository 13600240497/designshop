<template>
    <div class="geshop_u000209_default_rg_body"  ref="wrapper">
        <!--导航-->
        <div class="nav_tab">
            <!-- <div class="swiper-container" v-show="showNav">
                <ul class="swiper-wrapper">
                    <li :class="[ 'swiper-slide', { is_check: idx == navCur }]"
                        v-for="(item, idx) in navList"
                        v-if="item.navName != ''"
                        :key="idx"
                        @click="handlerClickWrap(item, idx)">
                        <span>{{ item.navName != '' ? item.navName: 'tab' + (idx + 1) }}</span>
                    </li>
                </ul>
            </div> -->
            <swiper :options="swiperOption" ref="mySwiper" v-show="showNav">
                <!-- slides -->
                <swiper-slide :class="[ 'swiper-slide', { is_check: idx == navCur }]"
                    v-for="(item, idx) in navList"
                    v-if="item.navName != ''"
                    :key="idx">
                    <span @click="handlerClickWrap(item, idx)">
                        {{ item.navName != '' ? item.navName: 'tab' + (idx + 1) }}
                    </span>
                </swiper-slide>
            </swiper>
        </div>

        <!--goods_list_wrap -->

        <div class="goods_list_wrap" v-if="list.length == 0 && data.isEditEnv == 1" >
            <ul class="list_wrap">
                <li v-for="(item, index) in arrList" :key="index">
                    <div :class="['list_item', { hide: idx != 0 } ]"
                         :data-idx="idx"
                         v-if="idx == 0"
                         v-for="(goods, idx) in item"
                         :key="goods.goods_sn">

                        <!--商品图片-->
                        <div class="item_image">
                            <geshop-analytics-href>
                                <geshop-image-goods></geshop-image-goods>
                            </geshop-analytics-href>

                            <!--商品营销信息-->
                            <div class="item_marketing_info"
                                 v-if="showMarketing == 1 && goods.promotions && goods.promotions.length > 0">
                                <span v-html="goods.promotions[goods.promotions.length - 1]"></span>
                            </div>
                        </div>

                        <!--折扣标-->
                        <div class="item_discount" v-if="discount_show == 1">
                            <geshop-discount :value="goods.discount"></geshop-discount>
                        </div>

                        <div class="item_content">
                            <!--sku标题-->
                            <div class="item_title rg-ellipsis-1" v-if="goodsTitleShow == 1">
                                <a :href="goods.url_title">{{ goods.goods_title }}</a>
                            </div>

                            <!--销售价-->
                            <div class="item_shop">
                                <div><geshop-shop-price :value="goods.shop_price"></geshop-shop-price></div>
                                <!--市场价-->
                                <div><geshop-market-price
                                    v-if="goods.shop_price <= goods.market_price"
                                    :value="goods.market_price">
                                </geshop-market-price></div>
                            </div>

                            <!--加购-->
                            <geshop-analytics-href
                                v-if="client == 'app'"
                                class="shop-fast">
                            </geshop-analytics-href>

                            <a href="javascript:void (0)"
                               v-else
                               class="shop-fast js_fast_buy"
                               :data-href="'/m-goods_fast-a-ajax_goods-id-' + item.goods_id">
                            </a>
                        </div>

                    </div>
                </li>
            </ul>
        </div>

        <div class="goods_list_wrap" v-if="list.length > 0">
            <!-- loading -->
            <div class="loading-more" v-show="!addFlag">
                <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt="">
            </div>
            <!-- 列表 -->
            <ul class="list_wrap" :style="{ 'opacity': !addFlag ? 0 : 1 }">
                <li v-for="(item, index) in list" :key="item.goods_sn">
                    <div :class="['list_item', { hide: idx != 0 } ]"
                         :data-idx="idx"
                         v-if="idx == 0"
                         v-for="(goods, idx) in item" 
                         :key="goods.goods_sn">

                        <!--商品图片-->
                        <div class="item_image">
                            <geshop-analytics-href
                                :href="goods.url_title"
                                :sku="goods.goods_sn"
                                :cate="goods.cateid"
                                :warehouse="goods.warehousecode"
                                :goods_id="goods.goods_id">
                                <geshop-image-goods :src="goods.goods_img"></geshop-image-goods>
                            </geshop-analytics-href>

                            <!-- 库存告急 -->
                            <geshop-stocktip class="item_stocktip" :item="goods"></geshop-stocktip>

                            <!--商品营销信息-->
                            <div class="item_marketing_info"
                                 v-if="showMarketing == 1 && goods.promotions && goods.promotions.length > 0">
                                <span v-html="goods.promotions[goods.promotions.length - 1]"></span>
                            </div>

                        </div>

                        <!--折扣标-->
                        <div class="item_discount" v-if="discount_show == 1">
                            <geshop-discount :value="goods.discount"></geshop-discount>
                        </div>

                        <div class="item_content">
                            <!--sku标题-->
                            <div class="item_title rg-ellipsis-1" v-if="goodsTitleShow == 1">
                                <a :href="goods.url_title">{{ goods.goods_title }}</a>
                            </div>

                            <div class="item_shop">
                                <!--销售价-->
                                <div><geshop-shop-price :value="goods.shop_price"></geshop-shop-price></div>
                                <!--市场价-->
                                <div><geshop-market-price
                                    v-if="Number(goods.shop_price) < Number(goods.market_price)"
                                    :value="goods.market_price">
                                </geshop-market-price></div>
                            </div>

                            <!--加购-->
                            <geshop-analytics-href
                                v-if="client == 'app'"
                                :href="goods.url_title"
                                :sku="goods.goods_sn"
                                :cate="goods.cateid"
                                :warehouse="goods.warehousecode"
                                :goods_id="goods.goods_id" class="shop-fast">
                            </geshop-analytics-href>

                            <a href="javascript:void (0)"
                               v-else
                               class="shop-fast js_fast_buy"
                               :data-href="'/m-goods_fast-a-ajax_goods-id-' + goods.goods_id ">
                            </a>
                        </div>

                    </div>
                </li>
            </ul>
        </div>

        <!--more or less-->
        <div class="item_more_less" v-if="isMore">
            <!-- 点击 viewmore loading 的效果 -->
            <template v-if="view_more_loading">
                <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt="" style="height: 0.96rem;">
            </template>
            <template v-else>
                <div class="view_more" @click="showMoreOrLess(1)" >
                    <span>{{ view_more }}</span>
                </div>
            </template>
        </div>

        <div class="item_more_less" v-if="isLess">
            <div class="view_more" @click="showMoreOrLess(0)">
                <span>{{ view_less }}</span>
            </div>
        </div>

    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    data () {
        return {
            // swiper配置项
            swiperOption: {
                slidesPerView: 'auto',
                slideToClickedSlide: true
            },
            $boxWrap: null,
            navCur: 0, // 当前导航
            navList: [
                {
                    navName: 'tab1'
                },
                {
                    navName: 'tab2'
                },
                {
                    navName: 'tab3'
                }
            ], // 导航列表
            isFixed: '1', // 是否吸顶,默认吸顶
            list: [], // 商品列表
            arrList: [
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['Buy 1 Get <strong class="red">15%</strong> OFF']
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['Buy 1 Get <strong class="red">15%</strong> OFF']
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33'
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33'
                    }
                ]
            ],
            showGoodsNumber: 20, // 默认显示商品个数
            tabGoodsNumber: '', // tab显示商品个数
            showMarketing: 1, // 商品营销信息是否显示
            discount_show: 1, // 折扣标是否显示
            goodsTitleShow: 1, // 商品标题是否显示
            pageNo: 1, // 当前页数
            pageSize: 20, // 每页显示个数
            total: '', // 总条数
            pageCount: '', // 总页数
            showNav: true,
            isMore: false,
            isLess: false,
            view_more: '',
            view_less: '',
            view_more_scroll_top: 0, // 点击 view more 记录当前高度
            oTabData: {}, // 当前tab
            addFlag: false,
            client: '', // 端, 'pc', 'wap', 'app'
            view_more_loading: false // 点击 viewmore 之后的 loading 效果
        };
    },
    computed: {
        isDateRes () {
            // ajax 请求 json文件回来存放的信息
            return this.$store.state.global.isDateRes;
        }
    },
    mounted () {
        const self = this;
        // loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        // $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {
        self.lang = GESHOP_LANG || 'en';
        self.$boxWrap = $(this.$refs.mySwiper);
        self.client = GESHOP_PLATFORM || 'wap';
        this.isDateRes && self.init();
        // });
    },
    watch: {
        isDateRes () {
            this.init();
        }
    },
    methods: {
        /**
         *  @Description 初始化
         *
         */
        init () {
            const self = this;
            const $data = this.data;
            this.isFixed = $data.is_fixed;
            this.showMarketing = $data.market_info_or_show ? $data.market_info_or_show : 1; // 营销信息
            this.discount_show = $data.discount_show ? $data.discount_show : 1; // 折扣标
            this.goodsTitleShow = $data.goods_title_show ? $data.goods_title_show : 1; // 商品名称

            this.view_more = this.$root.languages.view_more;
            this.view_less = this.$root.languages.view_less;

            // goodsSKU
            if ($data.goodsSKU) {
                self.navList = $data.goodsSKU; // 导航tab

                self.oTabData = $data.goodsSKU[0];
                self.renderList($data.goodsSKU[0]);
                self.scrollFn();
                self.showNav = true;
            }
        },

        /**
         *  @Description 切换tab
         *  @params item object
         *
         */
        handlerClickWrap (item, index) {
            const self = this;
            if (self.navCur == index) {
                return false;
            }
            self.addFlag = false;
            self.navCur = index;
            self.oTabData = item;
            self.pageNo = 1;
            // self.list = [];
            self.showNav = true;
            this.isMore = false;
            this.isLess = false;
            // 滚动到顶部
            self.scrollTop();
            self.renderList(item);
        },

        scrollTop () {
            const self = this;
            if (self.isFixed == 1) {
                self.$nextTick(() => {
                    const targetScrollTop = $(this.$refs.wrapper).offset().top + 2;
                    $('html,body').scrollTop(targetScrollTop);
                });
            }
        },

        /**
         *  @Description more or less
         *  @params type 0: less 1: more
         *  @params
         *  @params
         *  @return
         *
         */
        showMoreOrLess (type) {
            const self = this;
            // less
            if (type == 0) {
                this.pageNo = 1;
                this.isMore = true;
                this.isLess = false;
                this.list = this.list.slice(0, self.showGoodsNumber);

                this.$nextTick(() => {
                    $(window).scrollTop(self.view_more_scroll_top);
                });
            }

            // more
            if (type == 1) {
                // 页数累加
                self.pageNo++;

                // 当前页小于总页数
                if (self.pageNo <= self.pageCount) {
                    // 开启 loading
                    this.view_more_loading = true;
                    self.renderList(self.oTabData);
                }
                this.$nextTick(() => {
                    if (self.pageNo >= self.pageCount) {
                        this.isMore = false;
                        this.isLess = true;
                    } else {
                        this.isMore = true;
                        this.isLess = false;
                    }
                    if (self.pageNo == 2) {
                        this.view_more_scroll_top = $(window).scrollTop();
                    }
                });
            }
        },

        /**
         *  @Description 渲染列表
         *
         */
        async renderList (item) {
            const self = this;
            const $data = this.data;
            self.oTabData = item;

            if (item.catIds !== '' || item.goods !== '' || item.ipsGoodsSKU !== '') {
                // 默认显示商品个数
                if ($data.default_show_goods_number && $data.default_show_goods_number != '') {
                    self.pageSize = $data.default_show_goods_number; // 每页显示商品数
                    self.showGoodsNumber = $data.default_show_goods_number;
                }
                const jsonData = {
                    pageNo: self.pageNo,
                    pageSize: self.pageSize
                };

                jsonData['catId'] = item.catIds;
                jsonData['goodsSn'] = item.goods;
                let sku;
                if (window.GESHOP_ASYNC_DATA_INFO[this.pid] && window.GESHOP_ASYNC_DATA_INFO[this.pid].length && window.GESHOP_ASYNC_DATA_INFO[this.pid]) {
                    let obj = window.GESHOP_ASYNC_DATA_INFO[this.pid].find((item) => {
                        return item.tab_index == this.navCur;
                    });
                    sku = obj ? obj.goodsSku : '';
                }
                // 分类id
                if (item.skuFrom == 3) {
                    jsonData['catId'] = item.catIds;
                    jsonData['goodsSn'] = '';
                } else if (item.skuFrom == 2) { // 选品
                    jsonData['catId'] = '';
                    jsonData['goodsSn'] = sku || item.ipsGoodsSKU;
                } else { // 商品sku
                    jsonData['catId'] = '';
                    jsonData['goodsSn'] = sku || item.goods;
                }

                const res = await self.getGoodsList(jsonData);
                this.view_more_loading = false;
                try {
                    if (res.code === 0) {
                        self.pagination = res.data.pagination;
                        let tabNumber = $data.tab_total_number;
                        let goodsInfo = res.data.goodsInfo;

                        // 切换tab, 清空list
                        if (this.pageNo > 1) {
                            self.list = self.list.concat([...res.data.goodsInfo]);
                        } else {
                            self.list = [...res.data.goodsInfo];
                        }

                        // 总商品数
                        if (tabNumber != '') {
                            self.total = self.pagination.totalCount > $data.tab_total_number ? $data.tab_total_number : self.pagination.totalCount;
                            if (goodsInfo.length > tabNumber) {
                                self.list = JSON.parse(JSON.stringify(goodsInfo.slice(0, tabNumber)));
                            } else {
                                if (self.isLess) {
                                    self.list = JSON.parse(JSON.stringify(self.list.slice(0, tabNumber)));
                                }
                            }
                        } else {
                            self.total = self.pagination.totalCount;
                        }

                        self.pageCount = Math.ceil(self.total / self.pageSize); // 总页数

                        // 页数大于1, 出现view more
                        if (self.pageCount > 1 && self.pageNo < self.pageCount) {
                            self.isMore = true;
                        }

                        // 页面元素初始化
                        self.$store.dispatch('global/async_goods_init', this);

                        setTimeout(() => {
                            self.addFlag = true;
                        }, 300);
                    }
                } catch (e) {
                    self.addFlag = true;
                    if (self.data.isEditEnv == 0) {
                        self.list = [];
                    }
                }
            }
        },

        /**
         *  @Description 获取商品列表
         *  @params jsonData object
         *
         */
        async getGoodsList (jsonData) {
            const _url = GESHOP_INTERFACE.goods_samelistCatId.url;
            const data = {
                lang: GESHOP_LANG || 'en',
                client: GESHOP_PLATFORM || 'wap',
                pageNo: jsonData.pageNo || '1',
                pageSize: jsonData.pageSize || '20',
                catId: jsonData.catId || '',
                goodsSn: jsonData.goodsSn || ''
            };
            try {
                const res = await this.$jsonp(_url, data);
                return res;
            } catch (err) {}
        },
        /**
         *  @Description 滚动时吸顶
         *
         */
        scrollFn () {
            const self = this;
            $(window).on('scroll', self.throttle(function () {
                const scrollTop = $(window).scrollTop();

                // 是否吸顶
                if (self.isFixed == 1) {
                    let $levelNav = $('div[data-key="U000030"]'); // 水平导航
                    // 页面存在水平导航
                    if ($levelNav.length) {
                        self.handlerNav(scrollTop, 1);
                    } else {
                        self.handlerNav(scrollTop, 0);
                    }
                }
            }, 100));
        },

        handlerNav (scrollTop, type) {
            const self = this;
            let $wrap = $('#U000209_' + `${self.pid}`);
            let gsTabOffset = $(self.$el).offset();
            let $selfWrap = $(self.$el);
            let $levelNav = $('div[data-key="U000030"]').find('nav');

            let is_fixed = type == 1 ? gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + $selfWrap.height() - $levelNav.height() : gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + $selfWrap.height();

            if (is_fixed) {
                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').hide();
                }

                // 水平导航
                if (type == 1) {
                    $levelNav.hide();
                }

                $wrap.addClass('js-geshop-nav-fixed');
                // 获取高度
                const height = $(self.$refs.mySwiper.$el).height();
                $selfWrap.find('.nav_tab').addClass('is_fixed').height(height);
            } else {
                $selfWrap.find('.nav_tab').removeClass('is_fixed');
                $wrap.removeClass('js-geshop-nav-fixed');

                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').show();
                }

                // 水平导航
                if (type == 1) {
                    $levelNav.show();
                }

                if (GEShopSiteCommon) {
                    GEShopSiteCommon.jsNavFixed();
                }
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

<style lang="less" scoped>
    .geshop_u000209_default_rg_body {
        position: relative;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);

        .is_fixed > .swiper-container {
            position: fixed;
            z-index: 99999;
            right: 0;
            left: 0;
            top: 0;

            & ul {
                box-shadow: none;
                margin: 0 auto;
            }
        }
        .swiper-container {
            display: block;
            height: 88 / 75rem;
            line-height: 88 / 75rem;

            .swiper-slide {
                display: inline-block;
                vertical-align: middle;
                width: auto;
                padding: 0 36/75rem;
            }
        }

        .item_more_less{
            margin-top: 0.10666667rem;
            padding-bottom: 0.42666667rem;
            display: flex;
            justify-content: center;
            color: #fff;
        }

        .view_more{
            display: flex;
            justify-content: center;
            width:165/37.5rem;
            height:36/37.5rem;
            background-color: #333333;
            text-align: center;
            & span{
                font-size:14/37.5rem;
                font-weight:600;
                line-height:36/37.5rem;
            }
        }

        .goods_list_wrap {
            overflow: hidden;
            display: flex;
            box-sizing: border-box;

            ul {
                display: flex;
                justify-content: space-between;
                flex-flow: row wrap;
                padding: 12/37.5rem 12/37.5rem 0rem;
                width: 375/37.5rem;
            }
            ul li{
                width: 170/37.5rem;
                box-sizing: border-box;
                margin-bottom: 12/37.5rem;
                overflow: hidden;
                position: relative;
                background: #fff;
            }
        }
        .item_image {
            position: relative;
            height: 224/37.5rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;

            img {
                width: 100%;
            }
        }

        .item_marketing_info{
            position: absolute;
            width: 100%;
            bottom: 0;
            left: 0;
            opacity: 0.8;
            height: 24/37.5rem;
            background-color: #ffffff;
            font-size: 13/37.5rem;
            z-index: 1;
            overflow: hidden;
            text-align: center;
            line-height: 24/37.5rem;
        }

        .item_content{
            position: relative;
            padding: 0px 12px;
            margin-bottom: 12/37.5rem;
        }

        .item_title {

            margin-top: 10/37.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size:13/37.5rem;
            color:#222222;
        }
        .item_shop{
            margin-top: 6/37.5rem;
            margin-right: 24/37.5rem;
        }
        .item_shop /deep/ .geshop-shop-price {
            font-size: 16/37.5rem;
            font-weight:500;
        }

        .shop-fast {
            cursor: pointer;
            background: url("https://geshoptest.s3.amazonaws.com/uploads/XR6nmvfcejDVSgNbwHpLaqCyI08T1h9l.png") 50% 50% no-repeat;
            width: 24/37.5rem;
            background-size: 24/37.5rem auto;
            height: 24/37.5rem;
            position: absolute;
            right: 12/37.5rem;
            bottom: 4/37.5rem;
        }

        // 数据加载 loading
        .loading-more {
            position: absolute;
            left: 0px;
            right: 0px;
            bottom: 0px;
            top: 0px;
            display: flex;
            justify-content: center;

            img {
                width: 32px;
                height: 32px;
                display: inline-block;
                margin-top: 256 / 75rem;
            }
        }
        .block--inline{
            display: inline-block;
        }
    }
</style>
