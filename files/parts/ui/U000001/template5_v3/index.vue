<template>
    <div :class="['geshop_U000001_template5_v3_wrapper', userGroupClass]" v-if="list.length > 0">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
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
                                                  :style="viewStyle"
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
                            <div class="shop_title">{{ shop_title }}</div>
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>

                        <!--市场价-->
                        <div class="item_market">
                            <geshop-market-price v-if="item.show_market_price" :value="item.market_price"></geshop-market-price>
                        </div>
                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    name: 'template5_v3',
    props: ['data', 'pid'],
    data () {
        return {
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
            ], // 默认商品列表
            quick_view: '', // 购买文案
            shop_title: '' // 销售价文案
        };
    },
    mounted () {
        const $data = this.data;
        this.shop_title = $data.shop_price_text;
        this.quick_view = $data.quick_text;

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
        },

        /**
         *  @Description quick_view 样式
         *
         */
        viewStyle () {
            let $data = this.data;
            return {
                'color': `${$data.quick_font_color}`,
                'background-color': `${$data.quick_bg_color}`
            };
        },

        userGroupClass: function () {
            return 'geshop_user_' + this.data.userGroupSelect || 0;
        }
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
.geshop_U000001_template5_v3_wrapper {
    width: 1200px;
    margin: 0 auto;
    overflow: hidden;

    ul {
        display: block;
        width: 1184px;
        margin: 12px auto 4px;
        font-size: 0;
        li {
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
            font-family: OpenSans-Semibold;

            & .shop_title{
                display: inline-block;
            }
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
