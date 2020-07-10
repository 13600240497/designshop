<template>
    <div :class="['geshop_U000001_template6_v3_wrapper', userGroupClass]" v-if="list.length > 0">
        <ul class="wrap">
            <li v-for="(item, index) in list" :key="item.goods_sn" class="goods">
                <div class="list_item">

                    <div class="item_image">
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

                        <geshop-button-quick-view class="item_view"
                                                  :item="item"
                                                  :index="index"
                                                  v-if="item.goods_number > 0"
                                                  :url_quick="item.url_quick">
                            <span>{{ quick_view }}</span>
                        </geshop-button-quick-view>

                        <!--sold out-->
                        <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>

                        <!--折扣标-->
                        <geshop-discount :value="item.discount"></geshop-discount>

                    </div>

                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
                    </div>

                    <div class="item_content">
                        <!--销售价-->
                        <div class="item_shop">
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>

                        <!--市场价-->
                        <div class="item_market" v-if="market_price_active == 1">
                            <geshop-market-price v-if="item.show_market_price" :value="item.market_price"></geshop-market-price>
                        </div>
                    </div>

                    <!--营销信息-->
                    <div class="item_promotion">
                        <div class="title"
                             v-if="item.promotions && item.promotions.length == 1"
                             v-html="promotions(item.promotions[0])"></div>
                        <div class="title"
                             v-if="item.promotions && item.promotions.length > 1" v-html="promotions(item.promotions[0], '1')"></div>

                        <ul v-if="item.promotions && item.promotions.length > 1" class="promotion">
                            <li v-for="(val, idx) in item.promotions" :key="idx" v-html="promotions(val)"></li>
                        </ul>
                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    name: 'template6_v3',
    props: ['data', 'pid'],
    data () {
        return {
            list: [], // 商品列表
            editList: [
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00',
                    'promotions': ['Buy 1 Get 10% off']
                }
            ], // 默认商品列表
            quick_view: '', // 购买文案
            market_price_active: 0 // 市场价是否显示
        };
    },
    mounted () {
        const $data = this.data;
        this.quick_view = $data.quick_view_label;
        this.market_price_active = $data.market_price_active;

        this.isDateRes && this.init();
    },
    methods: {
        init () {
            // 获取 store 的数据
            this.list = [...this.goodsInfo].map(item => {
                // 如果市场价大于销售价，则不展示市场价
                item['show_market_price'] = Number(item.shop_price) < Number(item.market_price);
                return item;
            });

            // 装修页
            if (window.GESHOP_PAGE_TYPE == 1 && this.list.length == 0) {
                this.list = [...this.editList];
            }
            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 页面初始化
            this.$store.dispatch('global/async_goods_init', this);
            // 组件展示人群
            this.$store.dispatch('global/userGroupHandle', this);
        },

        /**
         *  @Description 营销信息转化
         *
         */
        promotions (val, type) {
            let $val = val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
            return type && type == 1 ? $val + '...' : $val;
        }
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
        },
        userGroupClass: function () {
            return 'geshop_user_' + this.data.userGroupSelect || 0;
        }
    },
    watch: {
        /**
         *  @Description 监听goodsInfo
         *
         */
        isDateRes () {
            this.init();
        }
    }
};
</script>

<style scoped lang="less">
.geshop_U000001_template6_v3_wrapper {
    width: 1200px;
    margin: 0 auto;

    ul.wrap {
        display: block;
        width: 1184px;
        margin: 12px auto 4px;
        font-size: 0;
        li.goods {
            display: inline-block;
            position: relative;
            width: 288px;
            margin: 0 4px 8px;
            background: #fff;
            padding: 12px;
            box-sizing: border-box;
            vertical-align: top;
        }
    }

    .list_item{

        .item_title{
            width: 258px;
            font-size:14px;
            height: 20px;
            line-height: 20px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            margin-top: 12px;
            margin-bottom: 4px;
            color: #333333;
        }

        .item_shop{
            font-size:20px;
            font-weight:600;
            color: #333333;
            line-height: 30px;
        }

        .item_market{
            line-height: 19px;
            height: 19px;
            color: #999999;
            font-size: 14px;
        }

        .item_soldout{
            z-index: 99;
        }
    }

    .item_promotion {
        position: relative;
        font-size: 12px;
        height: 20px;
        .title {
            line-height: 20px;
            height: 20px;
            cursor: pointer;
        }
        &:hover {
            .promotion {
                display: block;
            }
        }

        .promotion {
            position: absolute;
            top: 22px;
            left: 0;
            display: none;
            padding: 12px;
            background-color: #FFFFFF;
            border:1px solid rgba(238,238,238,1);
            z-index: 99;
        }
        li {
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow: hidden;
            color: #333333;
        }
    }

    .item_image{
        position: relative;
        display: block;
        width: 264px;
        height: 352px;
        overflow: hidden;
        &:hover {
            .item_view {
                display: block;
            }
        }

        .item_view{
            display: none;
            position: absolute;
            top: 159px;
            left: 63px;
            width:138px;
            height: 34px;
            text-align: center;
            background-color: #FFFFFF;
            cursor: pointer;
            opacity:0.7;
            z-index: 1;
            &:hover{
                opacity: 1;
            }
        }
        .item_view span {
            display: inline-block;
            width:138px;
            height:34px;
            line-height: 34px;
            font-weight:600;
            font-size:14px;
        }
    }
}
</style>
