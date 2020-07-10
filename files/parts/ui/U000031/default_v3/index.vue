<template>
    <div class="geshop-u000031-default-v3-body" v-if="list.length > 0" :style="style_body" :class="{ 'is-whole': whole }" :data-id="pid">
        <ul :style="style_bg_radius">
            <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
                <div class="list-item">
                    <!--折扣标-->
                    <div class="item-discount">
                        <geshop-discount :value="item.discount"></geshop-discount>
                    </div>

                    <div class="item-image">
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
                    </div>

                    <!--sold out -->
                    <!--<div class="item-soldOut" v-if="item.goods_number <= 0">-->
                        <!--<span>{{ sold_out }}</span>-->
                    <!--</div>-->

                    <div class="item-info">
                        <!--sku标题-->
                        <div class="item-title">
                            <geshop-analytics-href
                                :item="item"
                                :index="index">
                                {{ item.goods_title || 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}
                            </geshop-analytics-href>
                        </div>

                        <div class="item-shop-market">
                            <!--销售价-->
                            <div class="item-shop">
<!--                                <div class="shop-title" :style="style_title">{{ shop_price_content }}</div>-->
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!--市场价-->
                            <div class="item-market" v-if="(data.market_price_active || 0) == 1">
                                <geshop-market-price :value="item.market_price"></geshop-market-price>
                            </div>
                        </div>

                        <!--促销 -->
                        <div class="item-promotions" v-if="item.promotions && item.promotions.length > 0">
                            <div class="gs-off-more" :style="style_promotions">
                                <div v-for="(val, key) in item.promotions" :key="key">
                                    <div class="gs-off-text" v-html="filterVal(val)"></div>
                                </div>
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
    props: ['data', 'pid'],
    data () {
        return {
            list: [{}, {}, {}, {}],
            shop_price_content: 'Now'
        };
    },
    mounted () {
        if (this.data.goodsInfo && this.data.goodsInfo.length > 0) {
            const len = this.data.goodsInfo.length;
            this.list = this.data.goodsInfo;

            if (this.data.goods_fixed_show_number != '') {
                if (len > Number(this.data.goods_fixed_show_number)) {
                    this.list = this.list.slice(0, this.data.goods_fixed_show_number);
                }
            }
        }

        if (this.data.shop_price_content) {
            this.shop_price_content = this.data.shop_price_content;
        }

        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
        this.$store.dispatch('global/loaded', this);
    },
    computed: {
        style_body () {
            const style = {
                marginBottom: this.$px2rem(this.data.box_margin_bottom),
                backgroundColor: this.data.box_bg_color ? this.data.box_bg_color : '#F8F8F8'
            };
            return style;
        },
        style_item () {
            let _radius = this.data.goods_radius_size ? this.data.goods_radius_size : '12';
            const style = {
                'border-radius': this.$px2rem(_radius)
            };
            return style;
        },
        style_bg_radius () {
            let style = '';
            this.box_is_whole = this.data.box_is_whole ? this.data.box_is_whole : 1;

            // 背景是否整体式， 1:是，0:否
            if (this.box_is_whole == 1) {
                let _radius = this.data.goods_bg_radius_size ? this.data.goods_bg_radius_size : '12';
                style = {
                    'border-radius': this.$px2rem(_radius),
                    'background-color': '#FFFFFF'
                };
            }
            return style;
        },
        style_title () {
            let _color = this.data.shop_price_color ? this.data.shop_price_color : '#333333';
            const style = {
                'color': _color
            };
            return style;
        },
        style_promotions () {
            let _color = this.data.promotions_text_color ? this.data.promotions_text_color : '#333333';
            const style = {
                'color': _color
            };
            return style;
        },
        whole () {
            return this.data.box_is_whole == 0;
        }
    },
    methods: {
        filterVal (val) {
            return val.replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, '\'');
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000031-default-v3-body {
        display: flex;
        justify-content: center;
        background-color: #F8F8F8;
        width: 750/75rem;
        opacity: 0.99;

        ul{
            display: flex;
            width: 702/75rem;
            margin: 24/75rem;
            padding: 24/75rem 24/75rem 0rem;
            border-radius:12/75rem;
            justify-content: space-between;
            flex-flow: row wrap;

            li{
                display: flex;
                width: 318/75rem;
                margin-bottom: 18/75rem;
                background-color: #FFFFFF;
                overflow: hidden;
            }
            li:nth-last-child(2) {
                margin-bottom: 0rem;
            }
            li:last-child {
                margin-bottom: 0rem;
            }

            .list-item{
                position: relative;

                .item-image{
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    width: 100%;
                    height: 424/75rem;
                }
                .item-info {
                    background-color: #FFFFFF;
                    margin-bottom: 24/75rem;
                }
                .item-title{
                    box-sizing: content-box;
                    width: 315/75rem;
                    font-size: 22/75rem;
                    height: 30/75rem;
                    line-height: 30/75rem;
                    overflow:hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    word-break: keep-all;
                    word-wrap: break-word;
                    padding-top: 18/75rem;
                    margin-bottom: 8/75rem;
                    color: #333333;
                    a {
                        color: #333333 !important;
                        &:hover{
                            color: #333333 !important;
                        }
                    }
                }

                .item-shop-market{
                    line-height: 48/75rem;
                    width: 100%;

                    .item-shop{
                        display: flex;
                        flex-flow: row wrap;
                        color: #333333;
                        line-height: 48/75rem;
                        align-items: baseline;

                        .shop-title{
                            font-size: 26/75rem;
                            padding-right: 8/75rem;
                            line-height: 33/75rem;
                        }
                    }
                    .item-market{
                        color: #999999;
                        line-height:33/75rem;
                    }
                }

                .item-promotions{
                    position: relative;
                    height: 24/75rem;
                    line-height: 24/75rem;
                    overflow: hidden;
                    margin-top: 6/75rem;
                    margin-bottom: 24/75rem;
                    font-size: 24/75rem;
                    .gs-off-text{
                        .special{
                            font-weight: 700;
                            font-family: OpenSans-Bold,arial,serif;
                        }
                    }
                    .sjx{
                        position: absolute;
                        right: 0;
                        top: 0;
                        width: 28/75rem;
                        height: 28/75rem;
                    }
                    .icon-downs{
                        width: 100%;
                        height: 100%;
                    }
                }

                .item-soldOut{
                    position: absolute;
                    top: 182/75rem;
                    left: 24/75rem;
                    width: 270/75rem;
                    height: 60/75rem;
                    border-radius: 80/75rem;
                    background-color: #000000;
                    opacity: 0.4;
                    z-index: 1;
                    & span{
                        display: inline-block;
                        text-align: center;
                        font-weight:600;
                        line-height:26/75rem;
                        font-size:24/75rem;
                        color: #ffffff;
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%,-50%);
                        z-index: 2;
                    }
                }

                .item-button{
                    margin-top: 18/75rem;
                    padding: 0rem 0rem 24/75rem 0rem;
                }
            }
        }
    }
    .geshop-u000031-default-v3-body.is-whole {
        ul{
            margin: 24/75rem 24/75rem 6/75rem;
            padding: 0;
            li{
                display: flex;
                width: 342/75rem;
                margin-bottom: 18/75rem;
            }
            .list-item{
                .item-image{
                    height: 456/75rem;
                }

                .item-soldOut{
                    position: absolute;
                    top: 198/75rem;
                    left: 24/75rem;
                    width: 294/75rem;
                    height: 60/75rem;
                }

                .item-promotions{
                    padding-left: 24/75rem;

                    .sjx{
                        position: absolute;
                        right: 10/75rem;
                        top: 0;
                    }
                }
                .item-title{
                    padding-left: 24/75rem;
                }
                .item-shop{
                    padding-left: 24/75rem;
                }
                .item-market{
                    padding-left: 24/75rem;
                }

                .item-button{
                    padding: 0rem 24/75rem 24/75rem;
                }
            }
        }
    }
</style>
