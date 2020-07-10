<template>
    <div class="geshop_u000203_template3_body" :class="[activeClass]" :data-pid="pid" :style="boxStyle" ref="wrapper">

        <!--导航 PC-->
        <div class="nav_tab" v-if="showNav && platform != 'm'">
            <ul class="pc_nav" ref="navTab">
                <li :class="{ is_check: idx == navCur }"
                    v-if="item.navName != ''"
                    v-for="(item, idx) in navList"
                    :key="idx" :style="liStyle" @click="handlerClickWrap(item, idx)">
                    <span>{{ item.navName != '' ? item.navName: 'tab' + (idx + 1) }}</span>
                </li>
            </ul>
        </div>

        <!-- 导航 M-->
        <div class="m_nav_tab" v-if="showNav && platform == 'm'">
            <div :class="['m_swiper', {'expand_open':tab_expand}]">
                <div class="tab_nav_box">
                    <div class="swiper-wraper">
                        <swiper :options="swiperOption" class="gs-tab-label" ref="mySwiper">
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
                    <!-- 下拉箭头 -->
                    <div class="expand_arrow" @click="handleExpandNav">
                        <i class="gs-icon gs-icon-arrow-down"></i>
                    </div>
                </div>
                <div class="expand_nav_tab">
                    <ul class="expend_ul">
                        <li :class="{ is_check: idx == navCur }"
                            v-if="item.navName != ''"
                            v-for="(item, idx) in navList"
                            :key="idx" @click="handlerClickWrap(item, idx)">
                            <span>{{ item.navName != '' ? item.navName: 'tab' + (idx + 1) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="goods_list_wrap">
            <!-- loading -->
            <template v-if="gs_loading">
                <img src="https://geshopimg.logsss.com/uploads/LKQ8kxuNCwGIinTfdhPqDaXRVOUeF3r4.gif" alt="" class="gs_loading">
            </template>

            <ul class="list_wrap">
                <li v-for="(item, index) in list" :key="index" class="category-good">
                    <div :class="['list_item', { hide: idx != 0 } ]" :data-idx="idx" v-if="idx == 0" v-for="(goods, idx) in item" :key="goods.goods_sn">

                        <!--商品-->
                        <div class="item_image">
                            <geshop-analytics-href
                                v-if="goods.goods_number > 0"
                                :href="goods.url_title"
                                :sku="goods.goods_sn"
                                :cate="goods.cateid"
                                :warehouse="goods.warehousecode"
                                :goods_id="goods.goods_id">

                                <geshop-image-goods
                                    :src="goods.goods_img"
                                    :sku="goods.goods_sn"
                                    :index="idx"
                                    :type="1">
                                </geshop-image-goods>
                            </geshop-analytics-href>

                            <geshop-image-goods
                                v-else
                                :src="goods.goods_img"
                                :sku="goods.goods_sn"
                                :index="idx"
                                :type="1">
                            </geshop-image-goods>

                            <!--商品营销信息-->
                            <div class="item_marketing_info" v-if="showMarketing == 1 && goods.promotions && goods.promotions.length > 0">
                                <span v-html="promotions(goods.promotions[0])"></span>
                            </div>

                            <!--折扣标-->
                            <geshop-discount :percent="goods.discount" :value="goods.discount"></geshop-discount>

                            <!--quick view-->
                            <div class="quick_view item_view">
                                <span class=" js-quick-view"
                                      :data-good="goods.goods_id"
                                      :data-fast-good=" DOMAIN + '/m-goods-fast'+ goods.goods_id +'.html?from=category'">
                                    {{ $lang('quick_view') }}
                                </span>
                            </div>

                        </div>

                        <div class="item_content">
                            <!--sku标题-->
                            <div class="item_title">
                                <a :href="goods.url_title" v-if="goods.goods_number > 0">{{ goods.goods_title }}</a>
                                <span v-else>{{ goods.goods_title }}</span>
                            </div>

                            <div class="item_price">
                                <!--销售价-->
                                <geshop-shop-price :value="goods.shop_price"></geshop-shop-price>

                                <!--市场价-->
                                <geshop-market-price
                                    v-if="Number(goods.market_price) > Number(goods.shop_price)"
                                    :value="goods.market_price"></geshop-market-price>
                            </div>

                            <!--商品同款-->
                            <div class="item_same" v-if="showSame == 1">
                                <ul v-if="item.length > 1">
                                    <li :class="{ is_same: indexs == 0 }"
                                        v-for="(val, indexs) in item.slice(0, 5)" v-if="indexs <= 4 && platform == 'pc'"
                                        :key="val.goods_sn" @mouseenter="handlerMouseEnterSame($event, indexs, val)">
                                        <geshop-image-goods v-if="val.goods_img_sm" :type="1" :src="val.goods_img_sm"></geshop-image-goods>
                                    </li>
                                    <li :class="{ is_same: indexs == 0 }"
                                        v-for="(val, indexs) in item.slice(0, 3)" v-if="indexs <= 3 && platform == 'pad'"
                                        :key="val.goods_sn" @mouseenter="handlerMouseEnterSame($event, indexs, val)">
                                        <geshop-image-goods v-if="val.goods_img_sm" :type="1" :src="val.goods_img_sm"></geshop-image-goods>
                                    </li>
                                    <li v-if="item.length > 5 && platform == 'pc'" class="ellipsis">
                                        <a :href="goods.url_title">...</a>
                                    </li>
                                    <li v-if="item.length > 3 && platform == 'pad'" class="ellipsis">
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
                    :pageStyle="pageStyle"
                    :pagerCount="pagerCount"
                    :pageCount="pageCount"
                    @change="handlePageChange"
                    :currentPage="pageNo"></pagination>

        <!--m端 分页-->
        <div class="m_page" v-show="mPage">
            <div class="item_more_less" v-if="isMore">
                <!-- loading 的效果 -->
                <template v-if="view_more_loading">
                    <img src="https://geshopimg.logsss.com/uploads/LKQ8kxuNCwGIinTfdhPqDaXRVOUeF3r4.gif" alt="" style="height: 64px;">
                </template>
                <template v-else>
                    <div class="view_more" @click="showMoreOrLess(1)" >
                        <span>{{ $lang('view_more') }}</span>
                    </div>
                </template>
            </div>

            <div class="item_more_less" v-if="isLess">
                <div class="view_more" @click="showMoreOrLess(0)">
                    <span>{{ $lang('view_less') }}</span>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import './quickView.less';
import 'swiper/dist/css/swiper.css';
import pagination from './pagination';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import './quickView.js';

export default {
    props: ['data', 'pid'],
    components: {
        pagination,
        swiper,
        swiperSlide
    },
    data () {
        let pid = this.pid;
        return {
            activeClass: 'geshop_u000203_template3_' + pid,
            platform: 'pc',
            // swiper配置项
            swiperOption: {
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                slideToClickedSlide: true
            },
            DOMAIN: '//' + window.GESHOP_SITE_DOMAIN || '//www.dresslily.com',
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
            defaultData: { // 装修页默认显示数据
                goods_title: 'Plus Size Color Block Flare Tankini Set …',
                shop_price: '15.99',
                market_price: '26.33',
                promotions: ['GET 10% OFF $20 +'],
                goods_img_sm: 'https://geshopimg.logsss.com/uploads/O8Tt6Sk5M7d4wesmfhWACKzj3cJyZLI0.png',
                discount: 30
            },
            itemGoods: '', // goodSku
            mItemGoods: [],
            list: [], // 商品列表
            showSame: 0, // 商品同款是否显示, 默认不显示
            showMarketing: 1, // 商品营销信息
            pageNo: 1, // 当前页数
            pageSize: 20, // 每页显示个数
            total: 0, // 总条数
            pageCount: 0, // 总页数
            pagerCount: 10, // 默认显示页数
            pageStyle: {}, // 页码样式
            showNav: false, // 显示导航
            navArr: [],
            navCur: 0, // 导航序号
            showPage: false, // 显示分页
            mPage: false, // M端显示分页
            mGoodsNum: 4, // M端每页显示商品数
            view_more_loading: false, // 点击 viewmore 之后的 loading 效果
            isMore: false,
            isLess: false,
            tab_expand: false, // tab是否展开
            gs_loading: false,
            initGoods: 0,
            oTabData: {} // 当前tab
        };
    },
    computed: {
        swiper () {
            // 轮播组件实例
            return this.$refs.mySwiper.swiper;
        },
        // 组件
        boxStyle () {
            let $data = this.data;
            if (this.platform != 'm') {
                return {
                    marginTop: ($data.box_margin_top || '0') + 'px',
                    marginBottom: ($data.box_margin_bottom || '32') + 'px'
                };
            } else {
                return {
                    marginTop: ($data.m_box_margin_top || '0') + 'px',
                    marginBottom: ($data.m_box_margin_bottom || '32') + 'px'
                };
            }
        },

        /**
         *  @Description 导航li样式
         *
         */
        liStyle () {
            const $data = this.data;
            const self = this;
            let wd = document.body.clientWidth;
            let style = {};
            let _width;
            if (self.platform == 'pc') {
                _width = 1140;
            } else if (self.platform == 'pad') {
                _width = wd - 60;
                // 特殊处理装修页, 导航样式不齐
                if ($data.isEditEnv == 1) {
                    _width = 934 - 60;
                }
            }
            let goodsSku = $data.goodsSKU;
            if (goodsSku && goodsSku.length) {
                self.navArr = goodsSku.filter((item) => {
                    if (item.navName != '') {
                        return item;
                    }
                });
                let navLength = self.navArr.length || 3;
                let rowTabNumber = $data.row_tab_num != '' ? $data.row_tab_num : 4;

                let marginLeft = '';

                // 导航个数小于每行默认显示个数时
                if (navLength < rowTabNumber) {
                    marginLeft = navLength > 1 ? (navLength - 1) * 20 : 0;
                    style = {
                        width: (_width - marginLeft) / navLength + 'px'
                    };
                } else {
                    marginLeft = navLength > 1 ? (rowTabNumber - 1) * 20 : 20;
                    style = {
                        width: (_width - marginLeft) / rowTabNumber + 'px'
                    };
                }
            } else {
                const navLength = this.navList.length;
                style = {
                    width: (_width - navLength * 20) / navLength + 'px'
                };
            }
            return style;
        },
        platforms () {
            return this.$store.state.dresslily.media_platform;
        }
    },
    watch: {
        platforms () {
            this.platformtFn();
        }
    },
    mounted () {
        const self = this;
        const $data = this.data;

        // tab存在
        if ($data.goodsSKU) {
            this.isFixed = $data.is_fixed; // 是否吸顶
            this.showMarketing = $data.market_info_show ? $data.market_info_show : 1; // 营销信息是否显示
            // 页码样式
            this.pageStyle = {
                color: $data.page_color
            };
            self.navName = 0;
            try {
                for (let item of $data.goodsSKU) {
                    if (item.navName != '') {
                        self.navName = 1;
                    }
                }
            } catch (e) {}

            if (self.navName == 1) {
                self.navList = $data.goodsSKU; // 导航tab
            }
            self.oTabData = $data.goodsSKU[0]; // 当前tab数据

            self.init(); // 初始化
        } else {
            self.defaultList();
        }
    },
    methods: {
        // 初始化
        init () {
            let self = this;
            self.initGoods = 1;
            self.platformtFn();

            self.$nextTick(() => {
                // PC端
                if (self.platform == 'pc') {
                    if (window.GLOBAL) {
                        let $target = `geshop_u000203_template3_${this.pid}`;
                        self.quickView = new window.GLOBAL.QuickView('.list_wrap', '.category-good', '.js-quick-view', $target);
                        self.quickView.init();
                    }
                }
                // 绑定事件
                if (self.isFixed == 1) {
                    self.scrollFn();
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

            // 显示导航
            self.showNav = true;

            // 去处loading
            self.$store.dispatch('global/loaded', this);

            if ((item.goods && item.goods !== '') || (item.catIds && item.catIds !== '') || (item.ipsGoodsSKU && item.ipsGoodsSKU !== '') || (item.ips.ipsGoodsSKU && item.ips.ipsGoodsSKU !== '')) {
                switch (self.platform) {
                case 'pc':
                case 'pad':
                    if ($data.page_goods_num && $data.page_goods_num != '') {
                        self.pageSize = $data.page_goods_num; // 每页显示商品数
                    }
                    break;
                case 'm':
                    // 分页
                    if ($data.m_goods_num && $data.m_goods_num != '') {
                        self.mGoodsNum = $data.m_goods_num;
                        self.pageSize = $data.m_goods_num; // 每页显示商品数
                    }
                    break;
                }

                const jsonData = {
                    pageNo: self.pageNo,
                    pageSize: self.pageSize
                };
                let pageCount = 0;
                let skuFrom = Number(item.skuFrom);
                self.skuForm = skuFrom;
                switch (skuFrom) {
                // 分类id
                case 3:
                    jsonData['catId'] = item.catIds;
                    jsonData['goodsSn'] = '';
                    break;
                // 选品
                case 2:
                    jsonData['catId'] = '';
                    jsonData.pageNo = 1;

                    // 筛选器, 当前tab sku总数
                    if (item.ipsMethods && item.ipsMethods == 4) {
                        self.totalItemSku = item.ips.ipsGoodsSKU.split(',').filter((item) => {
                            return item != '';
                        });
                    } else {
                        self.totalItemSku = item.ipsGoodsSKU.split(',').filter((item) => {
                            return item != '';
                        });
                    }
                    break;
                // 商品sku
                case 1:
                    jsonData['catId'] = '';
                    jsonData.pageNo = 1;
                    // 当前tab sku总数
                    self.totalItemSku = item.goods.split(',').filter((item) => {
                        return item != '';
                    });
                    break;
                }

                if (skuFrom == 1 || skuFrom == 2) {
                    pageCount = Math.ceil(self.totalItemSku.length / self.pageSize); // 总页数
                    for (let i = 0; i < pageCount; i++) {
                        let j = i + 1;
                        // 当前页请求sku
                        if (self.pageNo == j) {
                            let items = '';
                            if (i == 0) {
                                items = self.totalItemSku.slice(i, self.pageSize).join(',');
                            } else {
                                let start = self.pageSize * i;
                                let end = self.pageSize * j;
                                items = self.totalItemSku.slice(start, end).join(',');
                            }
                            jsonData['goodsSn'] = items;
                            self.itemGoods = items;
                        }
                    }
                }

                const res = await self.getGoodsList(jsonData);
                self.gs_loading = false;

                if (self.platform == 'm') {
                    self.view_more_loading = false;
                }
                try {
                    if (res.code === 0) {
                        let goodsInfo = res.data.goodsInfo;
                        let tabNumber = $data.tab_total_number; // 每个tab显示商品总数

                        // 分页
                        if (skuFrom == 3) {
                            self.pagination = res.data.pagination;
                        } else {
                            self.pagination = {
                                pageNo: self.pageNo,
                                pageSize: self.pageSize,
                                totalCount: self.totalItemSku.length
                            };
                        }
                        if (self.platform != 'm') {
                            self.list = [...goodsInfo];
                            // 是否显示PC分页
                            if (self.list.length > 0 && self.platform != 'm') {
                                self.showPage = true;
                            }
                            // 总商品数
                            if (tabNumber != '') {
                                self.list = goodsInfo.length > tabNumber ? goodsInfo.slice(0, tabNumber) : goodsInfo;
                                self.total = self.pagination.totalCount > $data.tab_total_number ? $data.tab_total_number : self.pagination.totalCount;
                            } else {
                                self.total = self.pagination.totalCount;
                            }
                            self.pageCount = Math.ceil(self.total / self.pageSize); // 总页数

                            // 商品sku, 排序
                            if (skuFrom == 1 || skuFrom == 2) {
                                let goodsArr = self.itemGoods.split(',');
                                let twoGoodsArr = [];
                                goodsArr.map(x => {
                                    let _goods = x.substring(0, 7);
                                    self.list.forEach((item) => {
                                        let items = item;
                                        let goods_sn = item[0]['goods_sn'];
                                        if (x == goods_sn || goods_sn.indexOf(_goods) != -1) {
                                            twoGoodsArr.push(items);
                                        };
                                    });
                                });
                                self.list = [...twoGoodsArr];
                            }
                        } else {
                            // 商品sku, 排序
                            if (skuFrom == 1 || skuFrom == 2) {
                                // 记录第一页数据
                                if (self.initGoods == 1) {
                                    self.mItemGoods = [...goodsInfo];
                                }

                                let goodsArr = self.itemGoods.split(',');
                                let twoGoodsArr = [];

                                goodsArr.map(x => {
                                    let _goods = x.substring(0, 7);
                                    goodsInfo.forEach((item) => {
                                        let items = item;
                                        let goods_sn = item[0]['goods_sn'];
                                        if (x == goods_sn || goods_sn.indexOf(_goods) != -1) {
                                            twoGoodsArr.push(items);
                                        };
                                    });
                                });
                                goodsInfo = [...twoGoodsArr];
                            }

                            // 切换tab, 清空list
                            if (this.pageNo > 1) {
                                self.list = self.list.concat([...goodsInfo]);
                            } else {
                                self.list = [...goodsInfo];
                            }
                            if (self.list.length > 0) {
                                self.mPage = true; // m端 view more
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
                        }

                        // 页面元素初始化
                        self.$store.dispatch('global/async_goods_init', self);

                        $(self.$el).find('.goods_list_wrap').css({ height: 'auto' });
                    }
                } catch (e) {
                    if (self.data.isEditEnv == 0) {
                        self.list = [];
                    }
                }
            } else {
                self.gs_loading = false;
            }

            // 数据为空,装修页
            if (self.list.length == 0 && self.data.isEditEnv == 1) {
                for (let i = 0; i < 4; i++) {
                    let arr = [
                        self.defaultData,
                        self.defaultData
                    ];
                    self.list.push(arr);
                }
            }
        },

        /**
         *  @Description 获取商品列表
         *  @params jsonData object
         *
         */
        async getGoodsList (jsonData) {
            const _url = GESHOP_INTERFACE.goods_goodsTabList.url;
            let client = '';
            if (this.platform == 'm') {
                if (GESHOP_PLATFORM == 'app') {
                    client = 'app';
                } else {
                    client = 'wap';
                }
            } else if (this.platform == 'pad') {
                client = 'pad';
            } else {
                client = 'pc';
            }

            const data = {
                lang: GESHOP_LANG,
                client: client,
                pageNo: jsonData.pageNo || 1,
                pageSize: jsonData.pageSize || 20,
                catId: jsonData.catId || '',
                goodsSn: jsonData.goodsSn || ''
            };
            try {
                const res = await this.$jsonp(_url, data, { cache: true });
                return res;
            } catch (err) {}
        },

        /**
         *  @Description 营销信息转化
         *
         */
        promotions (val, type) {
            let $val = val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
            return type && type == 1 ? $val + '...' : $val;
        },

        /**
         *  @Description 装修页默认赋值
         *
         */
        defaultList () {
            let self = this;
            // 数据为空,装修页
            if (self.list.length == 0 && self.data.isEditEnv == 1) {
                for (let i = 0; i < 4; i++) {
                    let arr = [];
                    if (self.platform == 'm') {
                        arr = [
                            this.defaultData
                        ];
                    } else {
                        arr = [
                            this.defaultData,
                            this.defaultData
                        ];
                    }
                    this.list.push(arr);
                }
            }
        },

        /**
         *  @Description 商品同款切换
         *  @params
         *
         */
        handlerMouseEnterSame (event, idx, val) {
            const $target = $(event.target);
            const $li = $target.parents('.list_item');
            $target.addClass('is_same').siblings('li').removeClass('is_same');

            // 替换src
            let $image = $li.find('.item_image').find('img');
            let goods_img = val.goods_img;
            let src = $image.attr('src');

            if (src != goods_img) {
                $image.attr('src', goods_img);
            }
        },

        /**
         *  @Description 页码改变
         *
         */
        handlePageChange (pageNo) {
            const self = this;
            let $target = $(self.$el);
            let $wrap = $('html,body');
            let $goodsListWrap = $target.find('.goods_list_wrap');

            // quick view 隐藏
            if (self.platform == 'pc') {
                window.GLOBAL && self.quickView.hide(true);
            }

            self.gs_loading = true;
            self.list = []; // 清空数据
            self.pageNo = pageNo; // 当前页码数
            $goodsListWrap.height($goodsListWrap.height()); // 添加高度占位
            self.renderList(this.oTabData);

            if (self.isFixed == 1) {
                self.$nextTick(() => {
                    let targetScrollTop = $(this.$refs.wrapper).offset().top + 2;
                    $wrap.scrollTop(targetScrollTop);
                });
            }
        },

        /**
         *  @Description 切换tab
         *  @params item object
         *
         */
        handlerClickWrap (item, index) {
            const self = this;
            self.initGoods = 1;
            if (index == self.navCur) {
                return false;
            }
            self.navCur = index; // 当前序号，添加样式
            // quick view 隐藏
            if (self.platform == 'pc') {
                window.GLOBAL && self.quickView.hide(true);
            }
            self.oTabData = item; // 当前tab
            self.showPage = false;
            self.mPage = false;
            this.isMore = false; //  more or less
            this.isLess = false;
            self.pageNo = 1;
            self.handlePageChange(self.pageNo);
        },

        // 展开tab
        handleExpandNav () {
            this.tab_expand = !this.tab_expand;
        },

        handlerNav (scrollTop, type) {
            const self = this;
            let $nav_tab_box;

            let $wrap = $('#U000203_' + `${self.pid}`);
            let $selfWrap = $(self.$el);
            let gsTabOffset = $selfWrap.offset();
            let $levelNav = $('div[data-key="U000186"]').find('nav'); // D网水平导航

            if (self.platform != 'm') {
                $nav_tab_box = $selfWrap.find('.nav_tab');
            } else {
                $nav_tab_box = $selfWrap.find('.m_nav_tab');
            }

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

                // 导航高度占位
                let $height = $nav_tab_box.height();
                if (self.platform == 'm') {
                    $height = $nav_tab_box.find('.tab_nav_box').height();
                }
                $nav_tab_box.height($height);

                $wrap.addClass('js-geshop-nav-fixed');
                $nav_tab_box.addClass('is_fixed');
            } else {
                $nav_tab_box.removeClass('is_fixed');
                $wrap.removeClass('js-geshop-nav-fixed');

                // 站点导航栏处理
                if ($('.js-geshop-nav').length) {
                    $('.js-geshop-nav').show();
                }

                // 水平导航
                if (type == 1) {
                    $levelNav.show();
                }
            }
            if (GEShopSiteCommon) {
                GEShopSiteCommon.jsNavFixed();
            }
        },

        /**
         *  @Description 端口判断
         *
         */
        platformtFn () {
            let self = this;
            self.showNav = false;
            self.mPage = false;
            self.gs_loading = true;

            let cw = document.body.clientWidth;
            // 预览页
            if (self.data.isEditEnv == 0) {
                try {
                    let platformt = GLOBAL && typeof GLOBAL.util.getPlatform() != 'undefined' ? GLOBAL.util.getPlatform() : 2;
                    if (platformt == 1) {
                        // m
                        self.platform = 'm';
                        self.showSame = 0;
                        self.mPage = true;
                        self.showPage = false;
                    } else if (platformt == 2) {
                        // pc
                        self.platform = 'pc';
                        self.showSame = 1;
                    } else if (platformt == 3) {
                        // pad
                        self.platform = 'pad';
                        self.showSame = 1;
                    }
                } catch (e) {}
            } else {
                // 装修页
                if (cw >= 1025) {
                    // pc
                    self.platform = 'pc';
                    self.showSame = 1;
                } else if (cw <= 1024 && cw >= 768) {
                    // pad
                    self.platform = 'pad';
                    self.showSame = 1;
                } else if (cw <= 767) {
                    // m
                    self.platform = 'm';
                    self.showSame = 0;
                    self.showPage = false;
                    self.mPage = true;
                }
            }
            self.renderList(self.oTabData);

            self.$nextTick(() => {
                let $selfWrap = $(self.$el);
                let $nav_tab_box;
                if (self.platform != 'm') {
                    $nav_tab_box = $selfWrap.find('.nav_tab');
                } else {
                    $nav_tab_box = $selfWrap.find('.m_nav_tab');
                }
                $nav_tab_box.removeAttr('style');
            });
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
                if (self.skuForm == 3) {
                    this.list = this.list.slice(0, self.data.m_goods_num);
                } else {
                    this.list = this.list.slice(0, self.mItemGoods.length);
                }

                this.$nextTick(() => {
                    $(window).scrollTop(self.view_more_scroll_top);
                });
            }
            // more
            if (type == 1) {
                // 页数累加
                self.pageNo++;

                // 第二页开始不记录
                self.initGoods = 0;

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
         *  @Description 滚动时吸顶
         *
         */
        scrollFn () {
            const self = this;
            $(window).on('scroll', function () {
                const scrollTop = $(window).scrollTop();

                // 是否吸顶
                if (self.isFixed == 1) {
                    let $levelNav = $('div[data-key="U000186"]'); // D网水平导航
                    // 页面存在水平导航
                    if ($levelNav.length) {
                        self.handlerNav(scrollTop, 1);
                    } else {
                        self.handlerNav(scrollTop, 0);
                    }
                }
            });
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
    @import "component.less";
</style>
