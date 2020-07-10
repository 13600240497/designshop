<template>
    <div :class="['geshop_u000031_rg_default_v3_body', userGroupClass]" :data-id="pid" v-if="list.length > 0">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list_item">
                    <div class="item_image">
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

                        <!--折扣标-->
                        <geshop-discount :value="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>

                        <!-- 库存告急 -->
                        <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>

                        <!--sold out-->
                        <geshop-soldout :visible="item.goods_number <= 0"></geshop-soldout>

                    </div>

                    <div class="item_info">
                        <!--sku标题-->
                        <div class="item_title rg-ellipsis-1">
                            <geshop-analytics-href
                                :item="item"
                                :index="index">
                                <geshop-goods-title>{{ item.goods_title }}</geshop-goods-title>
                            </geshop-analytics-href>
                        </div>

                        <div class="item_shop_market">
                            <!--销售价-->
                            <div class="item_shop">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!--市场价-->
                            <div class="item_market">
                                <template v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)">
                                    <geshop-market-price :value="item.market_price"></geshop-market-price>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import refresh_sku from '../../../dataCommon/refresh_sku_update_v2.js';

export default {
    props: ['data', 'pid'],
    extends: refresh_sku,
    data () {
        return {
            list: []
        };
    },
    computed: {
        userGroupClass: function () {
            return 'geshop_user_' + this.data.userGroupSelect || 0;
        }
    },
    mounted () {
        // 组件展示人群
        this.$store.dispatch('global/userGroupHandle', this);
    }
};
</script>

<style lang="less" scoped>
    .geshop_u000031_rg_default_v3_body {
        display: flex;
        width: 375/37.5rem;

        ul {
            display: flex;
            justify-content: space-between;
            flex-flow: row wrap;
            padding: 12/37.5rem 12/37.5rem 4/37.5rem ;

            li {
                display: flex;
                width: 172/37.5rem;
                background-color: #FFFFFF;
                overflow: hidden;
                margin-bottom: 8/37.5rem;
                box-sizing: border-box;
            }

            .list_item {
                /*font-family:Rubik-Regular;*/
                .item_image {
                    position: relative;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 230/37.5rem;
                }

                .item_info {
                    background-color: #FFFFFF;
                    padding: 12/37.5rem;
                }

                .item_title {
                    box-sizing: content-box;
                    width: 148/37.5rem;
                    font-size: 13/37.5rem;
                    height: 16/37.5rem;
                    line-height: 16/37.5rem;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    word-break: keep-all;
                    word-wrap: break-word;
                    color: #222222;

                    a {
                        color: #222222 !important;

                        &:hover {
                            color: #222222 !important;
                        }
                    }
                }

                .item_shop_market {
                    display: flex;
                    flex-flow: row wrap;
                    margin-top: 8/37.5rem;
                    line-height: 20/37.5rem;

                    .item_shop {
                        color: #333333;
                        line-height: 20/37.5rem;
                        height: 22/37.5rem;
                        overflow: hidden;
                        margin-right: 10/75rem;

                        & /deep/ .geshop-shop-price {
                            font-size: 16/37.5rem;
                        }
                    }

                    .item_market {
                        /deep/ .my_shop_price {
                            font-size: 13/37.5rem;
                        }
                        color: #999999;
                    }
                }
            }
        }
    }
</style>
