<template>
    <div class="geshop-U000031-rg_quick_buy_v3-body" :class="{ 'geshop-hidden-box': noPreview }"
         :style="boxStyle">
        <div class="list-wrap" ref="swipers">
            <!-- 默认商品列表数据 -->
            <div class="goods-list-wrap" v-if=" list.length == 0 ">
                <ul>
                    <li class="list-item" v-for="(item, index) in 4" :key="index">
                        <div class="item-img">
                            <geshop-analytics-href>
                                <geshop-image-goods>
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <!--折扣标-->
                            <geshop-discount
                                :value="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>
                            <div class="promotion-info">Buy 1 Get <strong class="red">15%</strong> off</div>
                            <geshop-soldout :visible="false"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <a href="javascript:void (0)" class="item-title rg-ellipsis-1">Tartan Panel Long Sleeve
                                Asymmetrical
                                T-shirt OFF</a>
                            <div class="rate-box">
                                <p class="item-shop-price block--inline">
                                    <geshop-shop-price></geshop-shop-price>

                                </p>
                                <p class="item-shop-prce2 block--inline">
                                    <geshop-market-price></geshop-market-price>
                                </p>
                                <a href="#" class="shop-fast"></a>
                                <!--<a href="#" class="shop-fast js_fast_buy"
                                   data-href="/m-goods_fast-a-ajax_goods-id-"></a>-->
                            </div>

                        </div>
                    </li>
                </ul>
            </div>
            <!-- 商品列表数据 -->
            <div class="goods-list-wrap" v-else>
                <ul>
                    <li class="list-item" v-for="(item, index) in list" :key="item.goods_id">
                        <div class="item-img">
                            <!-- 商品图片 -->
                            <geshop-analytics-href
                                class="item-title"
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id">
                                <geshop-image-goods
                                    :src="item.goods_img"
                                    :sku="item.goods_sn"
                                    :lazyload="lazyLoad"
                                    :index="index">
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <!--折扣标-->
                            <!--折扣标-->
                            <geshop-discount
                                :value="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>
                            <!-- 库存告急 -->
                            <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>
                            <div class="promotion-info"
                                 v-if="item.promotions.length > 0 && (data.marketing_is_show || 1) == 1"
                                 v-html="htmldecode(item.promotions[item.promotions.length - 1])"></div>
                            <!-- sold out 售空-->
                            <geshop-soldout :visible="Number(item.goods_number) <= 0"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <geshop-analytics-href
                                v-if="(data.title_is_show || 1) == 1"
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id" class="item-title rg-ellipsis-1">{{ item.goods_title }}
                            </geshop-analytics-href>
                            <div class="rate-box">
                                <!--销售价-->
                                <p class="item-shop-price block--inline">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                </p>
                                <!--市场价-->
                                <p class="item-shop-prce2 block--inline">
                                    <geshop-market-price :value="item.market_price"
                                                         :class="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) ? '':'visible-hidden'"></geshop-market-price>
                                </p>
                                <!-- 购物车 -->
                                <geshop-analytics-href
                                    v-if="client == 'app'"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id" class="shop-fast">
                                </geshop-analytics-href>
                                <a href="javascript:void (0)"
                                   class="shop-fast js_fast_buy"
                                   v-else
                                   :data-href="'/m-goods_fast-a-ajax_goods-id-' + item.goods_id">
                                </a>
                            </div>

                        </div>
                    </li>
                </ul>
                <!-- 加载更多 -->
                <!--                <div class="item_more_less" v-if="!pagination.seeMoreHide">-->
                <!--                    &lt;!&ndash; 点击 viewmore loading 的效果 &ndash;&gt;-->
                <!--                    <template v-if="view_more_loading">-->
                <!--                        <img src="https://uidesign.rglcdn.com/RG/image/z_promo/20190311_8431/loading_tm.gif" alt=""-->
                <!--                             style="height: 0.96rem;">-->
                <!--                    </template>-->
                <!--                    <template v-else>-->
                <!--                        <div class="view_more" @click="getGoodsList">-->
                <!--                            <span>{{ $lang('see_more') }}</span>-->
                <!--                        </div>-->
                <!--                    </template>-->
                <!--                </div>-->
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data', 'pid'],
    data () {
        return {
            $boxWrap: null, // 当前容器
            lang: '', // 当前语言
            sku: '', // 当前sku
            list: [],
            client: GESHOP_PLATFORM || 'wap',
            lazyLoad: true,
            noPreview: false,
            goods_tab_from: null, // 数据来源 [0sku+选品，1分类id],[同步，jsonp]
            // 异步分类id
            pagination: {
                pageSize: 24,
                pageNo: 1,
                totalCount: null,
                pageMax: 1,
                seeMoreHide: false
            },
            view_more_loading: false,
            ajx: false, // 是否可以请求,
            imgFilter: true // 懒加载过滤已加载的
        };
    },
    computed: {
        boxStyle () {
            return {
                marginTop: `${this.data.box_margin_top / 75}rem`,
                marginBottom: `${this.data.box_margin_bottom / 75}rem`
            };
        }
    },
    mounted () {
        this.init();
    },
    methods: {
        htmldecode (s) {
            return rg_promotion_htmldecode(s);
        },
        /**
         * 初始化默认数据
         */
        initEditData () {
            this.$set(this.pagination, 'pageSize', 100);
            // if (this.$root.is_edit_env) {
            //     this.$set(this.pagination, 'pageSize', 100);
            // }
        },
        /**
         * 异步获取商品初始化
         */
        init () {
            this.initEditData();
            this.getGoodsList();
        },
        /**
         * 加载商品并初始化商品
         */
        getGoodsList () {
            const pagination = this.pagination;
            const $data = this.$root.data;
            const catType = $data.cat_goods_type ? $data.cat_goods_type : 1;
            const jsonParam = {
                catGroup: $data.navList,
                catType: catType,
                pageNo: pagination.pageNo,
                pageSize: pagination.pageSize
            };
            this.handleList(jsonParam).then(resData => {
                if (resData) {
                    this.ajx = true; // 允许再请求
                    const dataPagination = resData.pagination;
                    this.pagination.pageMax = Math.ceil(dataPagination.totalCount / dataPagination.pageSize);
                    if (dataPagination.pageNo + 1 <= this.pagination.pageMax) {
                        this.pagination.pageNo += 1;
                    } else {
                        this.pagination.seeMoreHide = true;
                    }
                }
                // this.handleEditList();
            }).catch(() => {
                // this.handleEditList();
            });
        },
        /**
         * 获取商品数据
         * @returns {Promise<void>}
         */
        async handleList (jsonData = {}) {
            // 请求loading
            this.view_more_loading = true;
            const url = GESHOP_INTERFACE.goods_samelistCatIdMultiple.url;
            const params = {
                lang: GESHOP_LANG,
                platform: GESHOP_PLATFORM || 'pc',
                pageNo: jsonData.pageNo || 1,
                pageSize: jsonData.pageSize || 24,
                catGroup: jsonData.catGroup || [],
                catType: jsonData.catType
                // goodsSn: jsonData.goodsSn || ''
            };
            try {
                return await this.$jsonp(url, params).then(res => {
                    if (res.code === 0 && res.data && res.data.goodsInfo && res.data.goodsInfo.length > 0) {
                        this.editStatus = false;
                        // this.list = [...this.list, ...res.data.goodsInfo];
                        this.handleCombSameFirst(res.data.goodsInfo);
                    }
                    this.handleDispatch();
                    this.view_more_loading = false;
                    return res.data;
                });
            } catch (err) {
                this.view_more_loading = false;
                this.handleDispatch();
            }
        },
        handleEditList () {
            // 装修页数据的处理
            if (window.GESHOP_PAGE_TYPE == 1 && this.list.length == 0) {
                this.list = [...this.editList];
            }
        },
        handleDispatch () {
            // 去除loading骨架图
            this.$store.dispatch('global/loaded', this);
            // 商品初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 商品数据列表取同款第一条数据;
         * @param list
         */
        handleCombSameFirst (list) {
            let result = [];
            if (list && list.length > 0) {
                list.map(item => {
                    result.push(item[0]);
                });
            }
            this.list = [...this.list, ...result];
        }
    }
};
</script>

<style lang="less" scoped>
    @import './component.less';
</style>
