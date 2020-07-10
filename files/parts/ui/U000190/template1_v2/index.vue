<template>
    <div class="geshop-U000190-template1-v2-body">
        <ul class="goods-list clearfix">
            <li v-for="(item, index) in list" :key="index">
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

                    <!-- 销售价 -->
                    <div class="price-style1">
                        <span>
                            {{$root.data.shopPriceCopywrite || 'New User Price' }}:
                        </span>
                        <geshop-shop-price
                            :value="item.shop_price">
                        </geshop-shop-price>
                    </div>

                    <!-- 市场价 -->
                    <div class="price-style2" v-show="showMarketPrice">
                        <span class="geshop-marketprice-title">
                            {{$root.data.originalPriceCopywrite || 'Market Price' }}:
                        </span>
                        <geshop-market-price
                            :value="item.market_price"
                            :is_show_del="1">
                        </geshop-market-price>
                    </div>

                    <!-- 促销信息 -->
                    <p class="geshop-goods-promotions has-more"
                        @click="handleShowPromotionByClick(index)"
                        @mouseenter="handleShowPromotion(index, true)"
                        @mouseleave="handleShowPromotion(index, false)">
                        <template v-if="item.promotions.length > 0">
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
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['pid'],
    data () {
        return {
            list: [],
            // 展示促销信息的 SKU INDEX 索引
            showPromotionsGoodsSKU: null,
            // 默认数据格式
            defaultGoodsItem: {
                shop_price: 9.99,
                market_price: 19.99,
                goods_title: 'Plus Size Mesh Panel Snowfla…',
                discount: 99,
                promotions: {
                    0: 'Buy 1 Get 10% OFF'
                }
            },
            // 是否展示市场价
            showMarketPrice: this.$root.data.isOriginalPriceVis != 0,
            // 价格类型
            shop_price_type: this.$root.data.newUserPrice != null ? this.$root.data.newUserPrice : 1,
            // 立即购买按钮
            label_btn_buy_now: window.GESHOP_LANGUAGES.btn_shop_now
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
        }
    },
    watch: {
        isDateRes () {
            this.update_async_data();
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
            this.list = list.map(row => {
                // 根据 [newUserPrice] 判断，1=新人价取 new_user_price, 0=普通价取 shop_price 2 app专享价
                if (this.shop_price_type == 1) {
                    row.shop_price = row.new_user_price || row.shop_price;
                } else if (this.shop_price_type == 2) {
                    row.shop_price = row.app_price || row.shop_price;
                }

                // 计算 soldout 状态
                row['is_soldout'] = (parseInt(row.goods_number) <= 0 || parseInt(row.is_on_sale) <= 0);

                // 营销数据，obejct 转为 array
                if (typeof row.promotions == 'object' && Array.isArray(row.promotions) === false) {
                    row.promotions = Object.keys(row.promotions).map(key => {
                        let label = row.promotions[key];
                        label = label.replace(/&lt;/g, '<');
                        label = label.replace(/&gt;/g, '>');
                        label = label.replace(/&quot;/g, `"`);
                        return label;
                    });
                } else {
                    row.promotions = [];
                }
                return row;
            });
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
        }
    },
    mounted () {
        this.isDateRes && this.update_async_data();
    }
};
</script>

<style lang="less" scoped>
    @import 'index.less';
</style>
