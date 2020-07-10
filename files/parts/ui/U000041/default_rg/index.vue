<template>
    <div class="geshop_u000041_default_body" :data-pid="pid">

        <!--导航-->
        <div class="nav_tab" v-show="showNav">
            <ul>
                <li :class="{ is_check: idx == navCur }"
                    v-if="item.navName != ''"
                    v-for="(item, idx) in navList"
                    :key="idx" :style="liStyle" @click="handlerClickWrap(item, idx)">
                    <span>{{ item.navName != '' ? item.navName: 'tab' + (idx + 1) }}</span>
                </li>
            </ul>
        </div>

        <!--loading-->
        <div class="loading-more" v-show="!addFlag" style="padding: 3%;text-align: center;">
            <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt="">
        </div>

        <!--goods_list_wrap-->
        <div class="goods_list_wrap">
            <ul class="list_wrap">
                <li v-for="(item, index) in list" :key="index">
                    <div :class="['list_item', { hide: idx != 0 } ]" :data-idx="idx" v-for="(goods, idx) in item" :key="goods.goods_sn">

                        <!--商品图片-->
                        <div class="item_image"
                             @mousemove="showQuickView($event, goods)"
                             @mouseout="hideQuickView($event, goods)">
                            <geshop-analytics-href
                                v-if="goods.goods_number > 0"
                                :href="goods.url_title"
                                :sku="goods.goods_sn"
                                :cate="goods.cateid"
                                :warehouse="goods.warehousecode"
                                :goods_id="goods.goods_id">
                                <geshop-image-goods :src="goods.goods_img"></geshop-image-goods>
                            </geshop-analytics-href>

                            <geshop-image-goods v-else :src="goods.goods_img"></geshop-image-goods>

                            <geshop-button-quick-view class="item_view"
                                                      :item="item"
                                                      :index="index"
                                                      :url_quick="goods.url_quick">
                                <span>{{ $root.languages.quick_view }}</span>
                            </geshop-button-quick-view>

                            <!-- 库存告急 -->
                            <geshop-stocktip class="item_stocktip" :item="goods"></geshop-stocktip>

                            <!--商品营销信息-->
                            <div class="item_marketing_info" v-if="showMarketing == 1 && goods.promotions && goods.promotions.length > 0">
                                <span v-html="goods.promotions[goods.promotions.length -1 ]"></span>
                            </div>

                        </div>

                        <!--折扣标-->
                        <div class="item_discount" v-if="discount_show == 1">
                            <geshop-discount :value="goods.discount"></geshop-discount>
                        </div>

                        <div class="item_content">
                            <!--sku标题-->
                            <div class="item_title">
                                <geshop-analytics-href
                                    v-if="goods.goods_number > 0"
                                    :item="goods"
                                    :index="idx">
                                    {{ goods.goods_title }}
                                </geshop-analytics-href>
                                <span v-else>{{ goods.goods_title }}</span>
                            </div>

                            <div class="item_price" :style="priceStyle">
                                <!--销售价-->
                                <geshop-shop-price :value="goods.shop_price"></geshop-shop-price>

                                <!--市场价-->
                                <geshop-market-price
                                    v-if="Number(goods.market_price) > Number(goods.shop_price)" :value="goods.market_price"></geshop-market-price>
                            </div>

                            <!--商品同款-->
                            <div class="item_same" v-if="showSame == 1">
                                <ul v-if="item.length > 1">
                                    <li :class="{ is_same: indexs == 0 }" v-for="(val, indexs) in item.slice(0, 5)" v-if="indexs <= 4"
                                        :key="val.goods_sn" @mouseenter="handlerMouseEnterSame($event, indexs)">
                                        <geshop-image-goods class="is_same_list" v-if="val.goods_img_sm" :src="val.goods_img_sm"></geshop-image-goods>
                                    </li>
                                    <li v-if="item.length > 5" class="ellipsis">
                                        <a :href="goods.url_title">...</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </li>
            </ul>
        </div>

        <!--分页-->
        <pagination :pageSize="pageSize"
                    :showPage="showPage"
                    :total="total"
                    :pagerCount="pagerCount"
                    :pageCount="pageCount"
                    @change="handlePageChange"
                    :currentPage="pageNo"></pagination>

    </div>
</template>

<script>
import pagination from './pagination';

export default {
    props: ['data', 'pid'],
    components: {
        pagination
    },
    data () {
        return {
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
            edit_list: [
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>'],
                        goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                        discount: 30
                    },
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>'],
                        goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png'
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                    }
                ],
                [
                    {
                        goods_title: 'Plus Size Color Block Flare Tankini Set …',
                        shop_price: '15.99',
                        market_price: '26.33',
                        discount: 30,
                        promotions: ['<span class="bngo-list">Buy 1 Get <strong class="red_font">15%</strong> Off</span>']
                    }
                ]
            ], // 商品列表
            list: [], // 商品列表
            showSame: 1, // 商品同款是否显示
            showMarketing: 1, // 商品营销信息
            discount_show: '', // 折扣标是否显示
            pageNo: 1, // 当前页数
            pageSize: 20, // 每页显示个数
            total: 0, // 总条数
            pageCount: 0, // 总页数
            pagerCount: 0, // 默认显示页数
            showNav: false,
            navArr: [],
            navCur: 0,
            addFlag: false,
            showPage: false,
            oTabData: {} // 当前tab
        };
    },
    computed: {
        /**
         *  @Description 导航li样式
         *
         */
        liStyle () {
            const $data = this.data;
            const self = this;
            let style = {};

            // goodsSKU
            let _width = 1200;
            if ($data.goodsSKU && $data.goodsSKU.length) {
                self.navArr = $data.goodsSKU.filter((item) => {
                    if (item.navName != '') {
                        return item;
                    }
                });
                const navLength = self.navArr.length;
                const rowTabNumber = $data.row_show_tab_number != '' ? $data.row_show_tab_number : 4;

                let marginLeft = '';

                // 导航个数小于每行默认显示个数时
                if (navLength < rowTabNumber) {
                    marginLeft = navLength > 1 ? (navLength - 1) * 16 : 0;
                    style = {
                        width: (_width - marginLeft) / navLength + 'px'
                    };
                } else {
                    marginLeft = navLength > 1 ? (rowTabNumber - 1) * 16 : 16;

                    style = {
                        width: (_width - marginLeft) / rowTabNumber + 'px'
                    };
                }
            } else {
                const navLength = this.navList.length;
                style = {
                    width: (_width - navLength * 16) / navLength + 'px'
                };
            }
            return style;
        },

        /**
         *  @Description 价格样式
         *  @params
         *
         */
        priceStyle () {
            // 不显示同款
            if (this.showSame == 0) {
                return {
                    marginBottom: '20px'
                };
            }
        },
        isDateRes () {
            // ajax 请求 json文件回来存放的信息
            return this.$store.state.global.isDateRes;
        }
    },
    created () {
        if (this.data.isEditEnv === 1) {
            this.list = [...this.edit_list];
        }
    },
    async mounted () {
        this.isDateRes && this.init();
    },
    watch: {
        isDateRes () {
            this.init();
        }
    },
    methods: {
        init () {
            const self = this;
            const $data = this.data;
            this.isFixed = $data.is_fixed;
            this.showSame = $data.goods_color_img_or_show ? $data.goods_color_img_or_show : '1';
            this.showMarketing = $data.market_info_or_show ? $data.market_info_or_show : '1';
            this.discount_show = $data.discount_show ? $data.discount_show : '1';

            // goodsSKU
            if ($data.goodsSKU) {
                self.navList = $data.goodsSKU; // 导航tab
                self.showPageCount = $data.show_default_page;
                self.oTabData = $data.goodsSKU[0];
                self.renderList($data.goodsSKU[0]);
            } else {
                self.addFlag = true;
            }

            // nav 样式
            this.$nextTick(() => {
                const self = this;
                if ($data.goodsSKU) {
                    self.showNav = true;
                    self.scrollFn();
                }
                if ($data.isEditEnv === 1) {
                    self.showNav = true;
                }
            });
        },

        /**
         *  @Description 渲染列表
         *
         */
        async renderList (item) {
            const self = this;
            const $data = this.data;
            if (item.catIds !== '' || item.goods !== '' || item.ipsGoodsSKU !== '') {
                // 分页
                if ($data.page_show_goods_number && $data.page_show_goods_number != '') {
                    self.pageSize = this.data.page_show_goods_number; // 每页显示商品数
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
                try {
                    if (res.code === 0) {
                        self.addFlag = true;
                        self.showPage = true;
                        self.list = res.data.goodsInfo;
                        self.pagination = res.data.pagination;
                        let tabNumber = $data.tab_total_number;
                        let goodsInfo = res.data.goodsInfo;

                        // 总商品数
                        if (tabNumber != '') {
                            self.list = goodsInfo.length > tabNumber ? goodsInfo.slice(0, tabNumber) : goodsInfo;
                            self.total = self.pagination.totalCount > $data.tab_total_number ? $data.tab_total_number : self.pagination.totalCount;
                        } else {
                            self.total = self.pagination.totalCount;
                        }
                        self.pageCount = Math.ceil(self.total / self.pageSize); // 总页数
                        self.pagerCount = $data.show_default_page; // 默认显示页数

                        // 页面元素初始化
                        self.$store.dispatch('global/async_goods_init', this);

                        $(self.$el).find('.goods_list_wrap').css({ height: 'auto' });
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
                lang: GESHOP_LANG,
                client: GESHOP_PLATFORM || 'pc',
                pageNo: jsonData.pageNo || 1,
                pageSize: jsonData.pageSize || 20,
                catId: jsonData.catId || '',
                goodsSn: jsonData.goodsSn || ''
            };
            try {
                const res = await this.$jsonp(_url, data);
                return res;
            } catch (err) {}
        },

        /**
         *  @Description 显示快速购买按钮
         *  @param $event
         *  @param item
         */
        showQuickView ($event, item) {
            const $target = $($event.target).parents('.list_item').find('.item_view');
            if (item.goods_number > 0) {
                $target.show();
            }
        },
        /**
         *  @Description 关闭快速购买
         *  @param $event
         *  @param item
         */
        hideQuickView ($event, item) {
            const $target = $($event.target).parents('.list_item').find('.item_view');
            if (item.goods_number > 0) {
                $target.hide();
            }
        },

        /**
         *  @Description 商品同款切换
         *  @params
         *
         */
        handlerMouseEnterSame (event, idx) {
            const $target = $(event.target);
            const $li = $target.parents('li');

            $target.addClass('is_same').siblings('li').removeClass('is_same');
            $li.find('.list_item').each((index, item) => {
                if ($(item).attr('data-idx') == idx) {
                    $(item).removeClass('hide');
                } else {
                    $(item).addClass('hide');
                }
            });
        },

        /**
         *  @Description 页码改变
         *
         */
        handlePageChange (pageNo) {
            const self = this;
            let $goodsListWrap = $(self.$el).find('.goods_list_wrap');

            this.list = [];
            this.pageNo = pageNo;
            this.addFlag = false;
            $goodsListWrap.height($goodsListWrap.height());
            this.renderList(this.oTabData);

            const targetScrollTop = $(self.$el).offset().top + 1;
            $('html,body').scrollTop(targetScrollTop);
        },

        /**
         *  @Description 切换tab
         *  @params item object
         *
         */
        handlerClickWrap (item, index) {
            const self = this;
            self.navCur = index;
            self.oTabData = item;
            self.pageNo = 1;
            this.handlePageChange(self.pageNo);
        },

        handlerNav (scrollTop, type) {
            const self = this;
            let gsTabOffset = $(self.$el).offset();
            let $selfWrap = $(self.$el);
            let $nav_tab_box = $selfWrap.find('.nav_tab');
            let $levelNav = $('div[data-key="U000027"]').find('nav');

            let is_fixed = type == 1 ? gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + $selfWrap.height() - $levelNav.height() : gsTabOffset.top <= scrollTop && scrollTop < gsTabOffset.top + $selfWrap.height();

            if (is_fixed) {
                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').hide();
                }

                // 水平导航
                if (type == 1) {
                    $levelNav.find('nav').hide();
                }

                $selfWrap.addClass('js-geshop-nav-fixed');
                $nav_tab_box.addClass('is_fixed');
            } else {
                $nav_tab_box.removeClass('is_fixed');
                $selfWrap.removeClass('js-geshop-nav-fixed');

                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').show();
                }

                // 水平导航
                if (type == 1) {
                    $levelNav.find('nav').show();
                }

                if (GEShopSiteCommon) {
                    GEShopSiteCommon.jsNavFixed();
                }
            }
        },

        /**
         *  @Description 滚动时吸顶
         *
         */
        scrollFn () {
            const self = this;
            $(window).on('scroll', self.throttle(function () {
                const scrollTop = $(window).scrollTop();
                let $self_el = $(self.$el);
                let $nav_tab_box = $self_el.find('.nav_tab');
                let nav_height = $nav_tab_box.outerHeight(true);

                if ($nav_tab_box.data('is_warp') != 1) {
                    $nav_tab_box.wrap('<div style="height: ' + nav_height + 'px;"></div>');
                    $nav_tab_box.data('is_warp', 1);
                }

                // 是否吸顶
                if (self.isFixed == 1) {
                    let $levelNav = $('div[data-key="U000027"]'); // 水平导航
                    // 页面存在水平导航
                    if ($levelNav.length) {
                        self.handlerNav(scrollTop, 1);
                    } else {
                        self.handlerNav(scrollTop, 0);
                    }
                }
            }, 100));
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
    .geshop_u000041_default_body {
        .is_same_list {
            img {
                height: 22px;
                width: auto;
                position: relative;
                left: 50%;
                -webkit-transform: translateX(-50%);
                -moz-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                -o-transform: translateX(-50%);
                transform: translateX(-50%);
            }
        }
    }
</style>
<style lang="less" scoped>
    .geshop_u000041_default_body {
        max-width: 1200px;
        margin-right: auto;
        margin-left: auto;

        .is_fixed {
            position: fixed;
            z-index: 9999;
            right: 0;
            left: 0;
            top: 0;

            & ul{
                box-shadow: none;
                margin: 0 auto;
            }
        }

        .nav_tab {
            padding-top: 19px;
            padding-bottom: 6px;
            width: 1200px;
            margin: 0 auto;
            overflow: hidden;
        }

        .nav_tab ul{
            margin-left: -8px;
            margin-right: -8px;
            text-align: center;
            box-sizing: border-box;
        }
        .nav_tab ul li{
            display: inline-block;
            cursor: pointer;
            padding: 0px 24px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            box-sizing: border-box;
            margin-bottom: 12px;
        }

        .goods_list_wrap {
            margin-top: 16px;
            overflow: hidden;

            .hide {
                display: none;
            }
            .list_item {
                position: relative;
            }

            .list_wrap{
                margin: 0 8px;
                display: flex;
                align-items: stretch;
                flex-wrap: wrap;
            }
            li {
                width: 280px;
                float: left;
                margin: 0 8px 16px;
                box-sizing: border-box;
                background-color: #FFFFFF;
                overflow: hidden;

                &:nth-of-type(4n) {
                    margin-right: 0;
                }
            }
        }

        .item_image {
            position: relative;
            height: 368px;
            overflow: hidden;

            img {
                width: 100%;
            }
        }

        .item_marketing_info{
            position: absolute;
            width: 100%;
            bottom: 0;
            left: 0;
            height: 30px;
            background-color: rgba(255,255,255,.8);
            z-index: 1;
            overflow: hidden;
            text-align: center;
            line-height: 30px;
            font-size: 14px;
        }

        .item_content{
            padding: 0px 16px 0px 12px;
            font-family:Rubik-Regular;
        }

        .item_title {
            margin-top: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size:14px;
            & a{
                color:#222222;
            }
        }

        .item_price {
            margin-top: 4px;
            min-height: 22px;
            line-height: 22px;
            vertical-align: middle;
            margin-bottom: 12px;
        }
        .item_price /deep/ .geshop-shop-price {
            font-size: 18px;
            vertical-align: middle;
        }
        .item_price /deep/ .geshop-shop-price .my_shop_price{
            font-size: 18px;
            font-family:Rubik-Medium;
            margin-right: 8px;
        }
        .item_price /deep/ .geshop-market-price{
            vertical-align: middle;
        }
        .item_same{
            height: 24px;
            margin-bottom: 20px;

            ul li{
                width: 24px;
                height: 24px;
                overflow: hidden;
                border:1px solid #DDDDDD !important;
                border-radius: 0px !important;
                margin: 0 8px 0 0;
                cursor: pointer;
                text-align: center;

                &:nth-of-type(4n) {
                    margin-right: 10px;
                }
                &.is_same{
                    border:1px solid #EA5455 !important;
                }
            }
            ul li img{
                height: 100%;
            }

            .ellipsis{
                text-align: center;
                color: #666666;
                font-size: 18px;
                line-height: 12px;
                font-family: Airal;
            }
        }

        .item_view {
            display: none;
            position: absolute;
            top: 244px;
            left: 0px;
            right: 0px;
            margin: auto;
            width: 200px;
            height: 40px;
            text-align: center;
            background-color: #FFFFFF;
            cursor: pointer;
            opacity: 0.8;
            border: 1px solid rgba(221, 221, 221, 1);
            border-radius: 2px;
            box-sizing: border-box;
            overflow: hidden;
            z-index: 1;

            &:hover {
                opacity: 1;
            }

            span {
                display: inline-block;
                height: 40px;
                line-height: 40px;
                font-weight: 600;
                color: #333333;
                font-size: 16px;
            }
        }
    }
</style>
