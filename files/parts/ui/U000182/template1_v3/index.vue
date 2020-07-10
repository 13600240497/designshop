<template>
    <div class="geshop-U000182-template1-v3-body" v-show="show" ref="component">
        <ul class="goods-list clearfix">
            <li v-for="(item, index) in list" :key="index">
                <template v-if="item.goods_id">
                    <div class="goods-item">
                        <div class="goods-item-head">
                            <!-- 折扣标 -->
                            <geshop-discount
                                    :percent="item.discount"
                                    :value="item.discount">
                            </geshop-discount>

                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    :index="index">
                                <!-- 图片 -->
                                <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :index="index"
                                        :type="1">
                                </geshop-image-goods>

                                <!-- 购买弹出层，只有PC才有 -->
                                <div
                                        class="shop-now-container"
                                        v-if="item.is_soldout == false && media_platform == 'pc'">
                                    <div class="inner-wrapper">
                                        <i class="bag-img"></i>
                                        <span class="shop-now-text"> {{ label_btn_buy_now }} </span>
                                    </div>
                                </div>
                            </geshop-analytics-href>

                            <!-- 售罄 -->
                            <geshop-soldout :visible="item.is_soldout"></geshop-soldout>
                        </div>

                        <!-- 标题 -->
                        <geshop-analytics-href
                            class="geshop-goods-title" target="_blank"
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>

                        <p class="geshop-item-price-layer">
                            <geshop-shop-price
                                    :value="item.shop_price">
                            </geshop-shop-price>

                            <geshop-market-price
                                    :value="item.market_price"
                                    v-show="showMarketPrice && item.shop_price < item.market_price">
                            </geshop-market-price>
                        </p>

                        <!-- 促销信息 -->
                        <p class="geshop-goods-promotions has-more"
                           @click="handleShowPromotionByClick(index)"
                           @mouseenter="handleShowPromotion(index, true)"
                           @mouseleave="handleShowPromotion(index, false)">
                            <template v-if="item.promotions && item.promotions.length > 0">
                            <span
                                    v-show="media_platform == 'pc'"
                                    class="promotions-text"
                                    :data-promotions-length="item.promotions.length"
                                    v-html="item.promotions[0] + (item.promotions.length > 1 ? ' ···' : '')">
                            </span>

                                <span
                                        v-show="media_platform != 'pc'"
                                        class="promotions-text"
                                        :data-promotions-length="item.promotions.length">
                                <span v-html="item.promotions[0]"></span>
                                <img
                                        v-if="item.promotions.length > 1"
                                        :class="{ 'up': showPromotionsGoodsSKU === index }"
                                        src="https://geshoptest.s3.amazonaws.com/uploads/WaiETHlZ8ShDeFRXMB97Yjc3uQ4InAJr.png">
                            </span>
                            </template>
                        </p>
                        <div
                                v-if="item.promotions.length > 1"
                                class="geshop-goods-promotions-more"
                                :class="{ none: showPromotionsGoodsSKU !== index }">
                            <template v-for="(label, index) in item.promotions">
                                <p v-html="label" :key="index"></p>
                            </template>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <a :href="item.pic_href" class="goods-item goods-item-placehoder" :target=" media_platform == 'pc' ? '_blank' : '_self'">
                        <div class="goods-item-head " :data-class="media_platform">
                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    :index="index">
                                <!-- 图片 -->
                                <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :index="index"
                                        :type="1">
                                </geshop-image-goods>

                            </geshop-analytics-href>
                        </div>

                        <!-- 标题 -->
                        <geshop-analytics-href
                            class="geshop-goods-title" target="_blank"
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>

                        <p class="geshop-item-price-layer">
                            <geshop-shop-price
                                    :value="item.shop_price">
                            </geshop-shop-price>

                            <geshop-market-price
                                    :value="item.market_price"
                                    v-show="showMarketPrice && item.shop_price < item.market_price">
                            </geshop-market-price>
                        </p>

                        <!-- 促销信息 -->
                        <p class="geshop-goods-promotions has-more">

                        </p>
                        <!--s上面的都是占位-->
                        <template v-if="pageType == 1">
                            <img :src="item.pic_url" v-if="media_platform == 'pc'"  class="img-placehode" alt="">
                            <img :src="item.pic_url_pad" v-else-if="media_platform =='pad'"   class="img-placehode" alt="">
                            <img :src="item.pic_url_m" v-else  class="img-placehode " alt="">
                        </template>
                        <template v-else>
                            <img src="#" v-if="media_platform == 'pc'" :data-original="item.pic_url"  class="img-placehode js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy" alt="">
                            <img src="#" v-else-if="media_platform =='pad'" :data-original="item.pic_url_pad"  class="img-placehode js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy" alt="">
                            <img src="#" v-else :data-original="item.pic_url_m"  class="img-placehode js_gdexp_lazy js-geshopImg-lazyload js_gbexp_lazy" alt="">
                        </template>

                    </a>
                </template>
            </li>
        </ul>
        <!--more or less-->
        <div class="item-more-wrap">
            <div class="item-more-less" v-if="view_more.isMore">
                <div class="view_more" @click="showMoreOrLess(1)" :style="style_view_more">
                    <span>{{ $lang('view_more') }}</span>
                </div>
            </div>

            <div class="item-more-less" v-if="view_more.isLess">
                <div class="view_more" @click="showMoreOrLess(0)" :style="style_view_more">
                    <span>{{ $lang('view_less') }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['pid', 'data'],
    data () {
        return {
            show: true,
            list: [],
            // 展示促销信息的 SKU INDEX 索引
            showPromotionsGoodsSKU: null,
            // 默认数据格式
            defaultGoodsItem: {
                goods_id: 1,
                shop_price: 9.99,
                market_price: 19.99,
                goods_title: 'Plus Size Mesh Panel Snowfla…',
                discount: 20,
                promotions: {
                    0: 'Buy 1 Get 10% OFF'
                }
            },
            // 是否展示市场价
            showMarketPrice: this.$root.data.isOriginalPriceVis != 0,
            // 立即购买按钮
            label_btn_buy_now: window.GESHOP_LANGUAGES.btn_shop_now,
            // 商品分页数据
            view_more: {
                dataGroup: [],
                isMore: false,
                isLess: false,
                view_more_page: 0,
                view_max_page: 0,
                view_more_scroll_top: 0
            },
            pageType: window.GESHOP_PAGE_TYPE || 1,
            // pa端商品数据
            goodsArr: [],
            imgFilter: true
        };
    },
    computed: {
        site_code () {
            return window.GESHOP_SITECODE;
        },
        // pc/wap/pad
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        // 是否已经请求了数据
        isDateRes () {
            return this.$store.state.global.isDateRes;
        },
        // 获取store的数据
        goodsInfo () {
            try {
                return this.$store.state.global.goodsInfo[this.pid][0].goodsInfo;
            } catch (err) {
                return [];
            }
        },
        style_view_more () {
            const style = {
                background: this.$root.data.view_more_bg_color || '#222222',
                color: this.$root.data.view_more_font_color || '#FFFFFF'
            };
            return style;
        },
        pageSize () {
            return parseInt(this.$root.data.page_show_goods_number);
        }
    },
    watch: {
        isDateRes () {
            this.update_async_data();
        },
        media_platform (val, oldVal) {
            if (val === 'pc' && oldVal === 'pad' || val === 'pad' && oldVal === 'pc') {
                this.isDateRes && this.update_async_data();
                return false;
            }
            if (this.pageSize >= 1 && val === 'wap' || val === 'app') {
                let dataGroup = this.view_more.dataGroup;
                this.list = dataGroup[0];
                this.view_more = Object.assign(this.view_more, {
                    isMore: dataGroup.length > 1,
                    isLess: false,
                    view_more_scroll_top: 0
                });
            } else {
                this.list = this.goodsArr;
                this.view_more = Object.assign(this.view_more, {
                    isMore: false,
                    isLess: false,
                    view_more_scroll_top: 0
                });
            }
            this.isDateRes && this.update_async_data();
        }
    },
    methods: {
        // 获取异步数据
        update_async_data () {
            let list = [];
            if (this.goodsInfo.length <= 0 && this.$root.is_edit_env == '1') {
                list = [
                    this.defaultGoodsItem,
                    this.defaultGoodsItem,
                    this.defaultGoodsItem,
                    this.defaultGoodsItem
                ];
            } else {
                list = [...this.goodsInfo];
            }

            // 商品数据处理
            let goodsArr = list.map(row => {
                // 如果是APP端的话，取 app_price 的值
                if (this.site_code === 'dl-app') {
                    row.shop_price = row.app_price || row.shop_price;
                }

                // 修改价格类型
                row.shop_price = Number(row.shop_price);
                row.market_price = Number(row.market_price);

                // 计算 soldout 状态
                row['is_soldout'] = (parseInt(row.goods_number) <= 0 || parseInt(row.is_on_sale) <= 0);

                // 营销数据，obejct 转为 array
                /* if (typeof row.promotions == 'object' && Array.isArray(row.promotions) === false) {
                    row.promotions = Object.keys(row.promotions).map(key => {
                        let label = row.promotions[key];
                        label = label.replace(/&lt;/g, '<');
                        label = label.replace(/&gt;/g, '>');
                        label = label.replace(/&quot;/g, `"`);
                        return label;
                    });
                } else {
                    // console.log(row.promotions);
                    // row.promotions = [];
                } */
                row.promotions = Object.keys(row.promotions).map(key => {
                    let label = row.promotions[key];
                    label = label.replace(/&lt;/g, '<');
                    label = label.replace(/&gt;/g, '>');
                    label = label.replace(/&quot;/g, `"`);
                    return label;
                });
                return row;
            });
            // 备份pc端数据
            this.goodsArr = JSON.parse(JSON.stringify(goodsArr));

            if (this.data.navList && this.data.navList.length) {
                let navData = [];
                // 排序
                if (this.media_platform == 'wap') {
                    navData = this.data.navList.sort(function (x, y) {
                        return x.pic_positon.m - y.pic_positon.m;
                    });
                } else {
                    navData = this.data.navList.sort(function (x, y) {
                        return x.pic_positon.pc - y.pic_positon.pc;
                    });
                }

                navData.forEach((item, index) => {
                    if (this.media_platform == 'wap') {
                        if (window.isNaN(item.pic_positon.m) || item.pic_positon.m == 0) {
                            return false;
                        } else {
                            this.goodsArr.length && this.goodsArr.splice(item.pic_positon.m - 1, 0, item);
                        }
                    } else {
                        if (window.isNaN(item.pic_positon.pc) || item.pic_positon.pc == 0) {
                            return false;
                        } else {
                            this.goodsArr.length && this.goodsArr.splice(item.pic_positon.pc - 1, 0, item);
                        }
                    }
                });
            }
            this.initDataGroup(this.goodsArr);
            // 存在每页商品数量情况生成商品数据组dataGroup;
            if (this.pageSize >= 1 && this.media_platform === 'wap' || this.media_platform === 'app') {
                this.list = this.view_more.dataGroup[0];
            } else {
                this.list = this.goodsArr;
            }

            // 没有数据则隐藏
            this.show = this.list.length > 0;

            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
            this.$store.dispatch('global/loaded', this);
        },

        /**
         * 点击促销信息
         */
        handleShowPromotionByClick (sku) {
            if (this.showPromotionsGoodsSKU === sku) {
                this.showPromotionsGoodsSKU = '';
            } else {
                this.showPromotionsGoodsSKU = sku;
            }
        },
        // 展示促销信息
        handleShowPromotion (sku, show) {
            // 非PC不执行，避免重复执行
            if (this.media_platform != 'pc') return false;

            if (show === true) {
                this.showPromotionsGoodsSKU = sku;
            }
            if (show === false) {
                this.showPromotionsGoodsSKU = null;
            }
        },
        /**
         * 分页数据初始化
         * @param goodsArr
         */
        initDataGroup (goodsArr) {
            let data = this.$root.data;
            let result = [];
            let page_show_goods_number = data.page_show_goods_number;
            if (data.page_show_goods_number && parseInt(page_show_goods_number) !== NaN) {
                if (page_show_goods_number < goodsArr.length) {
                    this.view_more.isMore = true;
                    this.view_more.dataGroup = this.dataArrayGroup(goodsArr, page_show_goods_number);
                    this.view_more.view_more_page = 1;
                    this.view_more.view_max_page = this.view_more.dataGroup.length > 0 ? this.view_more.dataGroup.length : 0;
                } else {
                    this.view_more.isMore = false;
                    this.view_more.dataGroup = this.dataArrayGroup(goodsArr, page_show_goods_number);
                    this.view_more.view_more_page = 1;
                    this.view_more.view_max_page = 1;
                }
            }
        },
        /**
         * 商品数据分组
         * @param arr target Array
         * @param length Array group length
         * @returns {Array}
         */
        dataArrayGroup (arr, length) {
            let result = [];
            for (let i = 0, len = arr.length; i < len; i += length) {
                result.push(arr.slice(i, i + length));
            }
            return result;
        },
        /**
         * 回到组件位置
         */
        backComponentOffsetTop () {
            let $element = document.documentElement || document.body;
            $element.scrollTop = this.recordComponentOffsetTop();
        },
        /**
         * view more/less switch
         * @param type 0 less 1 More
         */
        showMoreOrLess (type) {
            let $viewMore = this.view_more;
            // less
            if (type === 0) {
                this.view_more = Object.assign($viewMore, {
                    isMore: true,
                    isLess: false,
                    view_more_page: 1
                });
                // 取tab下第一页
                this.list = $viewMore.dataGroup[0];
                // 回滚数据
                $('html,body').scrollTop($(this.$refs.component).offset().top);
            }
            if (type === 1) {
                let oldData = this.list;
                // key+1
                let current_page = $viewMore.view_more_page + 1;
                let page_data = $viewMore.dataGroup[$viewMore.view_more_page];
                if (oldData.length < this.goodsArr.length) {
                    this.list = oldData.concat(page_data);
                    $viewMore.view_more_page = current_page;
                    if ($viewMore.view_max_page === current_page) {
                        $viewMore.isMore = false;
                        $viewMore.isLess = true;
                    }
                } else {
                    $viewMore.isMore = false;
                    $viewMore.isLess = true;
                }
                if ($viewMore.view_more_page === 1) {
                    this.$nextTick(() => {
                        $viewMore.view_more_scroll_top = $(window).scrollTop();
                    });
                }
            }
            this.$nextTick(() => {
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            });
        },
        /**
         * 端变更
         */
        onChange () {

        }
    },
    mounted () {
        this.isDateRes && this.update_async_data();
        // 追加函数到队列
        this.$store.commit('dresslily/update_onresize_marque', this.onChange);
    }
};
</script>

<style lang="less" scoped>
    @import 'index.less';
</style>
