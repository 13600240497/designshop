<template>
    <div class="geshop-u000218-default-body" :style="style_body">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list-item">

                    <!--折扣标-->
                    <div class="item-discount">
                        <geshop-discount :value="item.discount"></geshop-discount>
                    </div>

                    <div class="item-image"
                         @mousemove="showQuickView($event, item.goods_number)"
                         @mouseout="hideQuickView($event, item.goods_number)">
                        <geshop-analytics-href
                                v-if="item.goods_number > 0"
                                :href="item.url_title"
                                target="_blank"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id">
                            <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                        </geshop-analytics-href>

                        <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>

                        <geshop-button-quick-view class="item-view"
                                                  :item="item"
                                                  :index="index"
                                                  :url_quick="item.url_quick">
                            <span>+{{ quick_view }}</span>
                        </geshop-button-quick-view>
                    </div>

                    <!--sold out -->
                    <div class="item-soldOut" v-if="item.goods_number <= 0">
                        <span>{{ sold_out }}</span>
                    </div>

                    <!--sku标题-->
                    <div class="item-title">
                        <geshop-analytics-href
                            v-if="item.goods_number > 0"
                            :item="item"
                            :index="index"
                            :target="target">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
<!--                        <a :href="item.url_title" v-if="item.goods_number > 0" target="_blank">{{ item.goods_title }}</a>-->
                        <span v-else>{{ item.goods_title }}</span>
                    </div>

                    <!--销售价-->
                    <div class="item-shop" :style="style_shop_color">
                        <div class="shop-title" :style="style_shop_foot">{{ price_first_content }}</div>
                        <geshop-shop-price :value="item.shop_price" :style="style_shop_color"></geshop-shop-price>
                    </div>

                    <!--市场价-->
                    <div class="item-market" :style="style_market">
                        <template v-if="item.shop_price <= item.market_price">
                            <div class="market-title">{{ price_second_content }}</div>
                            <geshop-market-price
                                    :is_show_del="is_show_del"
                                    :value="item.market_price"
                                    :style="style_market"></geshop-market-price>
                        </template>
                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            list: [
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack …'
                }
            ],
            discount_show: 1, // 是否显示折扣标, 默认显示
            discount_type: 1, // 折扣标展示方式
            price_first_content: '', // 销售价名称
            price_second_content: '', // 市场价名称
            sold_out: '',
            quick_view: '',
            is_show_del: 2, // 市场价默认不显示删除线
            target: '_blank'
        };
    },
    mounted () {
        this.price_first_content = this.$root.languages.price_first_content;
        this.price_second_content = this.$root.languages.price_second_content;
        this.sold_out = this.$root.languages.sold_out;
        this.quick_view = this.$root.languages.quick_view;

        if (this.data.goodsInfo && this.data.goodsInfo.length > 0) {
            this.discount_show = this.data.discount_show;
            this.discount_type = this.data.discount_type;
            this.price_first_content = this.data.price_first_content;
            this.price_second_content = this.data.price_second_content;
            this.is_show_del = Number(this.data.is_show_del);
            this.list = this.data.goodsInfo;
        }
        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
    },
    methods: {
        showQuickView ($event, goods_number) {
            const $target = $($event.target).parents('.list-item').find('.item-view');
            if (goods_number > 0) {
                $target.show();
            }
        },
        hideQuickView ($event, goods_number) {
            const $target = $($event.target).parents('.list-item').find('.item-view');
            if (goods_number > 0) {
                $target.hide();
            }
        }
    },
    computed: {
        style_body () {
            const _self = this;
            const style = {
                marginTop: _self.data.margin_top + 'px',
                marginBottom: _self.data.margin_bottom + 'px'
            };
            return style;
        },
        style_shop_foot () {
            const style = {
                fontSize: this.data.price_font_size + 'px'
            };
            return style;
        },
        style_shop_color () {
            const style = {
                color: this.data.shop_price_color + '!important'
            };
            return style;
        },
        style_market () {
            const self = this;
            const style = {
                color: self.data.price_second_font_color + '!important'
            };
            return style;
        }
    }
};
</script>

<style lang="less" scoped>

.geshop-u000218-default-body {
    background-color: #FFFFFF;
    margin-left: auto;
    margin-right: auto;
    width: 1200px;
    padding: 24px 24px 0px;
    box-sizing: border-box;
    li{
        display: inline-block;
        font-size: 0;
        width: 264px;
        margin-right: 32px;
        margin-bottom: 32px;
        vertical-align: top;

        &:last-child{
            margin-right: 0px;
        }
    }
    li:nth-child(4n) {
        margin-right: 0px;
    }

    .list-item{
        position: relative;

        .item-title{
            width: 258px;
            font-size:14px;
            height: 20px;
            line-height: 20px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            margin-top: 12px;
            margin-bottom: 10px;
            color: #333333;
        }

        .item-shop{
            font-family:OpenSans-Bold;
            color: #333333;
            & .shop-title{
                display: inline-block;
                font-size: 20px;
            }
        }
        .item-market{
            color: #999999;
            font-size: 14px;
            height: auto;
            line-height: 19px;

            .market-title{
                display: inline-block;
            }
        }

        .item-soldOut{
            position: absolute;
            top: 96px;
            left: 52px;
            width: 160px;
            height: 160px;
            border-radius: 80px;
            background-color: #000000;
            opacity: 0.4;
            z-index: 1;
            & span{
                display: inline-block;
                text-align: center;
                width:66px;
                height:52px;
                font-weight:600;
                line-height:26px;
                font-size:24px;
                color: #ffffff;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%,-50%);
                z-index: 2;
            }
        }

        .item-view{
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
        .item-view span {
            display: inline-block;
            width:138px;
            height:34px;
            line-height: 34px;
            font-weight:600;
            color: #333333;
            font-size:14px;
        }
    }

    .list-item .item-image{
        width: 100%;
        height: 352px;
        overflow: hidden;
    }
}
</style>
