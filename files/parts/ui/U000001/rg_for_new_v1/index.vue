<template>
    <div :class="['geshop_U000001_rg_for_new_v1_wrapper']" v-if="list.length > 0">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list_item">

                    <div class="item_image">
                        <!--折扣标-->
                        <geshop-discount
                            :value="typeof item.discount != 'undefined' ? item.discount: 50"></geshop-discount>

                        <geshop-analytics-href
                            v-if="item.goods_number > 0"
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="item.goods_id">
                            <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                        </geshop-analytics-href>

                        <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>

                        <!-- 快速购买 -->
                        <geshop-button-quick-view class="item_view"
                                                  :item="item"
                                                  :index="index"
                                                  :url_quick="item.url_quick">
                            <span>{{ $lang('quick_view') }}</span>
                        </geshop-button-quick-view>
                        <!-- sold out  -->
                        <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0" :type="soldoutType"></geshop-soldout>

                    </div>

                    <div class="item_content">
                        <!--sku标题-->
                        <div class="item_title">
                            <geshop-analytics-href
                                :item="item"
                                :index="index">
                                <geshop-goods-title>{{item.goods_title}}</geshop-goods-title>
                            </geshop-analytics-href>
                        </div>

                        <div class="item_price">
                            <!--销售价-->
                            <div class="item_shop">
                                <div class="shop_title inline_block">{{ text.first_price_content }}
                                </div>
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>

                            <!--市场价-->
                            <div class="item_market">
                                <div class="market_title inline_block">{{ text.second_price_content }}
                                </div>
                                <geshop-market-price v-if="Number(item.shop_price) < Number(item.market_price)"
                                                     :value="item.market_price"></geshop-market-price>
                            </div>
                        </div>

                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    name: 'rg_for_new_v1',
    props: ['data', 'pid'],
    data () {
        return {
            soldoutType: 'bottom', // sold类型
            text: {
                first_price_content: this.$root.data.first_price_content || 'New User Price:',
                second_price_content: this.$root.data.second_price_content || 'Market Price:'
            },
            list: [], // 商品列表
            editList: [
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                }
            ] // 默认商品列表
        };
    },
    mounted () {
        this.isDateRes && this.init();
    },
    computed: {
        goodsInfo () {
            try {
                return this.$store.state.global.goodsInfo[this.pid][0].goodsInfo;
            } catch (e) {
                return [];
            }
        },
        isDateRes () {
            return this.$store.state.global.isDateRes;
        }
    },
    methods: {
        init () {
            // 获取 store 的数据
            this.list = [...this.goodsInfo];

            // 装修页数据的处理
            if (window.GESHOP_PAGE_TYPE == 1 && this.list.length == 0) {
                this.list = [...this.editList];
            }
            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 商品懒加载
            this.$store.dispatch('global/async_goods_init', this);
            // 组件展示人群
            this.$store.dispatch('global/userGroupHandle', this);
        }
    },
    watch: {
        /**
         *  @Description isDateRes
         *
         */
        isDateRes () {
            this.init();
        }
    }
};
</script>

<style scoped lang="less">
    @import "./component.less";
/*    .geshop_U000001_rg_for_new_v1_wrapper {
        width: 1200px;
        margin: 0 auto;

        ul {
            display: block;
            font-size: 0;
            margin: 0 -8px;

            li {
                display: inline-block;
                position: relative;
                width: 288px;
                background: #fff;
                box-sizing: border-box;
                vertical-align: top;
                margin-bottom: 16px;
                margin-right: 8px;
                margin-left: 8px;
                transition: transform .5s;
                border: 1px solid transparent;
                overflow: hidden;

                &:hover {
                    transform: translateY(-10px);
                    cursor: pointer;
                }
            }
        }

        .list_item {

            .item_image {
                position: relative;
                display: block;
                width: 100%;
                height: 384px;
                overflow: hidden;
            }

            .item_content {
                padding: 0px 12px;
            }

            .item_title {
                width: 264px;
                font-size: 14px;
                height: 20px;
                line-height: 20px;
                text-overflow: ellipsis;
                white-space: nowrap;
                word-wrap: break-word;
                overflow: hidden;
                margin-top: 12px;
                color: #333333;
            }

            .item_price {
                margin-top: 8px;
                height: 22px;
                line-height: 22px;
            }

            .item_shop {
                display: inline-block;
                font-size: 20px;
                font-weight: 600;
                margin-right: 6px;
            }

            .item_market {
                display: inline-block;
                color: #999999;
                font-size: 14px;
            }

            .item_soldout {
                z-index: 99;
            }

            .item_button_buy {
                font-size: 16px;
                margin-top: 8px;
                margin-bottom: 16px;

                &:hover {
                    opacity: 0.9;
                }

                /deep/ .geshop-button-buynow {
                    padding: 0px 16px;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    word-wrap: break-word;
                    overflow: hidden;
                }
            }
        }
    }*/
</style>
