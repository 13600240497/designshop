<template>
    <div class="geshop-u000031-default-v1-body" :style="style_body" :class="{ 'is-whole': whole }" :data-id="pid">
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
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!--市场价-->
                            <div class="item-market">
                                <geshop-market-price :value="item.market_price"></geshop-market-price>
                            </div>
                        </div>

                        <div class="item-button">
                            <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id">
                                <geshop-buynow></geshop-buynow>
                            </geshop-analytics-href>
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
            list: [{}, {}, {}, {}]
        };
    },
    mounted () {
        if (this.data.goodsInfo && this.data.goodsInfo.length > 0) {
            this.list = this.data.goodsInfo;
        }
        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
        // 去处loading
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
        whole () {
            return this.data.box_is_whole == 0;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000031-default-v1-body {
        display: flex;
        justify-content: center;
        background-color: #F8F8F8;
        width: 750/75rem;
        opacity:0.99;

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
                background-color: #FFFFFF;
                overflow: hidden;
                margin-bottom: 18/75rem;
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
                    height: 424/75rem;
                }
                .item-info {
                    background-color: #FFFFFF;
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
                    display: flex;
                    flex-flow: row wrap;
                    line-height: 48/75rem;
                    align-items: center;

                    .item-shop{
                        color: #333333;
                    }
                    .item-market{
                        color: #999999;
                        padding-left: 8/75rem;
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
                    font-size: 28/75rem;
                }
            }
        }
    }
    .geshop-u000031-default-v1-body.is-whole {
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

                .item-title{
                    padding-left: 24/75rem;
                }
                .item-shop{
                    padding-left: 24/75rem;
                }
                .item-market{
                    padding-left: 8/75rem;
                }

                .item-button{
                    padding: 0rem 24/75rem 24/75rem;
                }
            }
        }
    }

</style>
