<template>
    <div class="geshop-u000031-modal1-body" :style="boxStyle">
        <div class="list-wrap" ref="swipers">
            <div class="goods-list-wrap" v-if=" currentGoods.length == 0 ">
                <ul>
                    <li class="list-item" v-for="(item, index) in 4" :key="index">
                        <div class="item-img">
                            <geshop-analytics-href>
                                <geshop-image-goods>
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <p class="sale-discount"
                               v-if="(data.discount_type || 1) == 1"
                               :style="{ backgroundColor: data.discount_bg_color || '#EA5455', color: data.discount_font_color || '#FFFFFF' }">
                                <span>50%</span><span> OFF</span>
                            </p>
                            <p class="sale-discount"
                               v-else
                               :style="{ backgroundColor: data.discount_bg_color || '#EA5455', color: data.discount_font_color || '#FFFFFF' }">
                                <span>-50%</span>
                            </p>
                            <div class="promotion-info">Buy Get 1 <strong class="red">15%</strong> Off</div>
                            <geshop-soldout :visible="false"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <div class="rate-box">
                                <p class="item-shop-price block--inline" >

                                    <geshop-shop-price></geshop-shop-price>
                                </p>
                                <p class="item-shop-prce2 block--inline">
                                    <geshop-market-price></geshop-market-price>
                                </p>
                                <a href="#" class="shop-fast"></a>
                                <!--<a href="#" class="shop-fast js_fast_buy"
                                   data-href="/m-goods_fast-a-ajax_goods-id-"></a>-->
                            </div>
                            <a href="javascript:void (0)" class="item-title rg-ellipsis-1">Tartan Panel Long Sleeve Asymmetrical
                                T-shirt OFF</a>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="goods-list-wrap" v-else>
                <ul>
                    <li class="list-item" v-for="(item, index) in currentGoods" :key="item.goods_id">
                        <div class="item-img">
                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id">
                                <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :lazyload="lazyLoad"
                                        :index="index">
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <span  v-if="item.discount - 0 > 0">
                                <p class="sale-discount"
                                   v-if="(data.discount_type || 1) == 1"
                                   :style="discountStyle">
                                    <span>{{ item.discount }}% </span><span>OFF</span>
                                </p>
                                <p class="sale-discount"
                                   v-else
                                   :style="discountStyle">
                                    <span>-{{ item.discount }}%</span>
                                </p>
                            </span>
                            <div class="promotion-info" v-if="item.promotions.length > 0 && (data.marketing_is_show || 1) == 1" v-html="htmldecode(item.promotions[0])"></div>
                            <geshop-soldout :visible="item.goods_number <= 0"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <div class="rate-box">
                                <p class="item-shop-price block--inline">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                </p>
                                <p class="item-shop-prce2 block--inline">
                                    <geshop-market-price :value="item.market_price" v-if="item.market_price - item.shop_price > 0"></geshop-market-price>
                                </p>
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
                            <geshop-analytics-href
                                    v-if="(data.title_is_show || 1) == 1"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id" class="item-title rg-ellipsis-1">{{ item.goods_title }}
                            </geshop-analytics-href>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            $boxWrap: null, // 当前容器
            lang: '', // 当前语言
            sku: '', // 当前sku
            currentGoods: [],
            client: '',
            lazyLoad: true
        };
    },
    computed: {
        discountStyle () {
            let bgStyle = {};
            if (this.data.discount_bg_image) {
                bgStyle['backgroundImage'] = `url(${this.data.discount_bg_image})`;
            }
            return Object.assign(
                {
                    backgroundColor: this.data.discount_bg_color || '#EA5455',
                    color: this.data.discount_font_color || '#FFFFFF',
                    backgroundSize: 'cover'
                }, bgStyle
            );
        },
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
    },
    mounted () {
        // 页面挂载完毕，去除 loadingTemplate
        this.$store.dispatch('global/loaded', this);
        // 请求数据
        this.client = GESHOP_PLATFORM || 'wap';
        let data_sku = (this.data.goodsDataFrom || 1) == 1 ? this.data.goodsSKU : this.data.ipsGoodsSKU;
        this.sku = data_sku;
        const data = {
            lang: this.lang,
            client: GESHOP_PLATFORM || 'wap',
            // filterStock: 1,
            goodsSn: this.sku
        };
        const url = GESHOP_INTERFACE.goods_async_detail.url;
        this.$jsonp(url, data).then(res => {
            let beArr = [];
            let arr = [];
            res.data.goodsInfo.forEach((item, index) => {
                if (item.goods_number <= 0) {
                    arr.push(item);
                } else {
                    beArr.push(item);
                }
            });
            this.currentGoods = beArr.concat(arr);
            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
        });
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000031-modal1-body {
        /*font-family: TrebuchetMS,Arial;*/
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        .list-wrap {
            box-sizing: border-box;
            .goods-list-wrap {
                > ul {
                    display: block;
                    font-size: 0;
                    width: (375 - 15)/37.5rem;
                    margin: 0 auto ;
                    padding-top:12/37.5rem ;
                    padding-bottom:2/37.5rem ;

                    li.list-item {
                        display: inline-block;
                        width: 171/ 37.5rem;
                        height: auto;
                        margin: 0 4.5/37.5rem 10/37.5rem;
                        font-size: 12/37.5rem;
                        color: #333333;
                        position: relative;
                        background-color: #ffffff;
                        vertical-align: top;
                        .item-img {
                            width: 171/ 37.5rem;
                            height: 228/37.5rem;
                            position: relative;
                            display: block;
                            overflow: hidden;
                            .geshop-zaful-image-good, a {
                                display: flex;
                                height: 100%;
                                justify-content: center;
                            }

                            img {
                                display: block;
                                width: 100%;
                            }

                            .sale-discount {
                                line-height: 18/37.5rem;
                                position: absolute;
                                padding: 0 4/37.5rem;
                                top: 8/37.5rem;
                                right: 0;
                                font-size: 12/37.5rem;
                            }

                            .promotion-info {
                                width: 100%;
                                height: 20/37.5rem;
                                line-height: 20/37.5rem;
                                position: absolute;
                                bottom: 0;
                                text-align: center;
                                background: rgba(255, 255, 255, 0.6);
                            }
                        }

                        .item-info-box {
                            .item-title {
                                overflow: hidden;
                                /*text-overflow: ellipsis;  有些示例里需要定义该属性，实际可省略*/
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                line-height: 32/75rem;
                                height: 29 /37.5rem;
                                margin: 10/37.5rem 0 10/37.5rem;
                                font-size: 12/37.5rem;
                            }

                            .rate-box {
                                position: relative;

                                .item-shop-price {
                                    margin-top: 6/37.5rem;

                                    .geshop-shop-price {
                                        line-height: 22/37.5rem;
                                        font-size: 16/37.5rem;
                                        margin-right: 8/37.5rem;
                                    }
                                }

                                .item-shop-price2 {
                                    .geshop-market-price {
                                        line-height: 17/37.5rem;
                                        font-size: 12/37.5rem;
                                    }
                                }

                                .shop-fast {
                                    background: url("https://geshopimg.logsss.com/uploads/Ar4qmk8sQHGO3TjaJlbd5eYBzcXVygwu.png") 50% 50% no-repeat;
                                    width: 24/37.5rem;
                                    background-size: 24/37.5rem auto;
                                    height: 24/37.5rem;
                                    position: absolute;
                                    right: 0;
                                    top: 6/37.5rem;
                                }
                            }

                        }
                    }
                }
            }
            .block--inline{
                display: inline-block;
            }
        }
    }
</style>
