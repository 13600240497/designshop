<template>
    <div
        class="geshop-U000031-rg_quick_buy_v3-body"
        :class="{ 'geshop-hidden-box': noPreview }"
        :style="boxStyle">
        <div class="list-wrap">

            <!-- 商品列表数据 -->
            <div class="goods-list-wrap">
                <ul>
                    <li
                        class="list-item"
                        v-for="(item, index) in list"
                        :key="`${index}-${item.goods_id}`">

                        <div class="item-img">
                            <!-- 商品图片 -->
                            <geshop-analytics-href
                                class="item-title rg-ellipsis-1"
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

                            <!--折扣标-->
                            <geshop-discount
                                :value="typeof item.discount != 'undefined' ? item.discount : 50">
                            </geshop-discount>

                            <div class="promotion-info"
                                 v-if="item.promotions.length > 0 && (data.marketing_is_show || 1) == 1"
                                 v-html="htmldecode(item.promotions[item.promotions.length - 1])">
                            </div>
                            <!-- 库存告急 -->
                            <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>
                            <!-- sold out 售空-->
                            <geshop-soldout :visible="Number(item.goods_number) <= 0"/>
                        </div>

                        <div class="item-info-box">
                            <!-- 商品标题 -->
                            <geshop-analytics-href
                                v-if="(data.title_is_show || 1) == 1"
                                class="item-title rg-ellipsis-1"
                                :item="item"
                                :index="index">
                                {{ item.goods_title }}
                            </geshop-analytics-href>
                            <div class="rate-box">
                                <!--销售价-->
                                <p class="item-shop-price">
                                    <shop-price-rg-m-2 :value="item.shop_price"></shop-price-rg-m-2>
                                </p>
                                <!--市场价-->
                                <p class="item-shop-prce2">
                                    <geshop-market-price
                                        :value="item.market_price"
                                        :class="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) ? '':'visible-hidden'">
                                    </geshop-market-price>
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
            </div>
        </div>
    </div>
</template>

<script>
import refresh_sku from '../../../dataCommon/refresh_sku_update_v2.js';

export default {
    props: ['data', 'pid'],
    extends: refresh_sku,
    data () {
        return {
            list: [],
            client: GESHOP_PLATFORM || 'wap'
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
    methods: {
        htmldecode (s) {
            return rg_promotion_htmldecode(s);
        }
    }
};
</script>

<style lang="less" scoped>
    @import './component.less';
</style>
