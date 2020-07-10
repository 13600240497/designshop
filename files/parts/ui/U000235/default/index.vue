<template>
    <div class="geshop-u000233-model1">
        <div class="u000233-model1-goods-wrap">
            <div class="u000233-model1-list">
                <ul class="comb-lists" ref="goodsList">
                    <li v-for="(item, index) in combData" :key="item[0].goods_id" class="comb-goods-list">
                        <div class="u000233-model1-goods-img">
                            <a :href="item[0].url_title" target="_blank">
                                <geshop-image-goods
                                        class="lt-img"
                                        :src="item[0].goods_img"
                                        :sku="item[0].goods_sn"
                                        :index="index"
                                        :lazyload="true">
                                </geshop-image-goods>
                            </a>
                            <geshop-discount :value="item[0].discount" :percent="item[0].discount"></geshop-discount>
                        </div>
                        <div class="goods-price">
                            <geshop-shop-price :value="item[0].shop_price"></geshop-shop-price>
                            <span class="join-comb" :data-id="item[0].goods_id">
                               <i class="icon-ck"></i><span class="icon-desc">{{ languages.join_comb[lang]}}</span>
                            </span>
                        </div>
                        <div class="con-wrap">
                            <div class="select-wrap cr-l">
                                <span class="js-size-text target-text gs-icon-down">{{ item[0].size }}</span>
                                <ul class="js-size-wrap">
                                    <li class="attr-li">3XL</li>
                                </ul>
                            </div>
                            <div class="select-wrap cr-r">
                                <span class="js-color-text target-text gs-icon-down">{{ item[0].color }}</span>
                                <ul class="js-color-wrap">
                                    <li class="attr-li">3XL</li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
                <div>
                    <button @click="submitCat">QUEREN</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            lang: GESHOP_LANG,
            checkedArr: [],
            $goodsList: null,
            combData: [
                [
                    {
                        active_num: "3319",
                        color: "WHITE",
                        discount: "50",
                        goods_id: "1",
                        goods_img: "",
                        goods_sn: "",
                        goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                        is_on_sale: "1",
                        market_price: "0.00",
                        shop_price: "0.00",
                        url_title: "",
                        size: "S"
                    },
                    {
                        active_num: "3319",
                        color: "WHITE2",
                        discount: "50",
                        goods_id: "1-2",
                        goods_img: "",
                        goods_sn: "",
                        goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                        is_on_sale: "1",
                        market_price: "0.00",
                        shop_price: "0.00",
                        url_title: "",
                        size: "S1"
                    },
                    {
                        active_num: "3319",
                        color: "WHITE2",
                        discount: "50",
                        goods_id: "1-3",
                        goods_img: "",
                        goods_sn: "",
                        goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                        is_on_sale: "1",
                        market_price: "0.00",
                        shop_price: "0.00",
                        url_title: "",
                        size: "S3"
                    }
                ],
                [{
                    active_num: "33192",
                    color: "WHITE",
                    discount: "50",
                    goods_id: "2",
                    goods_img: "",
                    goods_sn: "",
                    goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                    is_on_sale: "1",
                    market_price: "0.00",
                    shop_price: "0.00",
                    url_title: "",
                    size: "S"
                }],
                [{
                    active_num: "13319",
                    color: "WHITE",
                    discount: "50",
                    goods_id: "3",
                    goods_img: "",
                    goods_sn: "",
                    goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                    is_on_sale: "1",
                    market_price: "0.00",
                    shop_price: "0.00",
                    url_title: "",
                    size: "S"
                }],
                [{
                    active_num: "33319",
                    color: "WHITE",
                    discount: "50",
                    goods_id: "4",
                    goods_img: "",
                    goods_sn: "",
                    goods_title: "Stylish Round Neck Long Sleeve Hollow Out Spliced Women's Blouse",
                    is_on_sale: "1",
                    market_price: "0.00",
                    shop_price: "0.00",
                    url_title: "",
                    size: "S"
                }],
            ],
            languages: {
                join_comb: {
                    "en": "Join the combo",
                    "fr": "Faites votre combo",
                },
                get_car: {
                    "en": "GET THE SET",
                    "fr": "OBTENIR L'ENSEMBLE",
                }
            }
        }
    },
    computed: {},
    created () {
        this.$nextTick(() => {
            this.$goodsList = $(this.$refs.goodsList);
            this.bindEvent();
        })
    },
    mounted () {
        this.getData()
    },
    methods: {
        bindEvent() {
            const that = this;
            this.$goodsList.on('click', '.join-comb', function () {
                $(this).toggleClass('on').attr('data-id', 'xxxxx');
            })
        },
        submitCat() {
            this.$goodsList.find('.join-comb.on').each(function (index, item) {
                
            })
        },
        getdata () {
            const url = 'http://www.pc-dresslily.com.v0426.php5.egomsl.com/geshop/goods/comblist';
            const data = {
                lang: GESHOP_LANG,
                id: "130",
                client: GESHOP_PLATFORM,
                goodsSn: "109036711,143642503,228391701,228391601"

            }
            this.$jsonp(url, data).then(res => {
                this.combData = res.data.goodsInfo;
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            })
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000233-model1 {

        .u000233-model1-goods-wrap {
            padding: 1px;
            box-sizing: border-box;
            /*width: 1200px;*/
            width: 100%;
            width: auto;
            margin: 0 auto;
            position: relative;

            .u000233-model1-list {
                .comb-lists {
                    background: red;
                    display: inline-block;
                    font-size: 0;
                    /*width: 1158/1200*100%;*/
                    padding: 30px 22px 0 22px;

                    .comb-goods-list {
                        font-size: 14px;
                        display: inline-block;
                     /*   width: 273/1158 *100%;*/
                        width: 273px;
                        vertical-align: top;
                        margin: 0 8/1158*100% 30px;
                        margin: 0 8px 30px;
                        position: relative;

                        .u000233-model1-goods-img {


                        }

                        .goods-price {
                            .geshop-shop-price {
                                vertical-align: middle;
                            }

                            .join-comb {
                                user-select: none;
                                cursor: pointer;

                                .icon-ck, .icon-desc {
                                    display: inline-block;
                                    vertical-align: middle;
                                }


                            }
                        }

                        .con-wrap {
                            font-size: 0;
                            .select-wrap{
                                display: inline-block;
                                font-size: 14px;
                                position: relative;
                                .target-text  {
                                    display: block;
                                    width:130px;
                                    height:30px;
                                    line-height: 28px;
                                    background:rgba(255,255,255,1);
                                    border:1px solid rgba(221,221,221,1);
                                    box-sizing: border-box;
                                    padding: 0 8px;
                                    position: relative;
                                    cursor: pointer;
                                    &:after{
                                        content: '';
                                        position: absolute;
                                        width: 6px;
                                        height: 6px;
                                        border-left: 1px solid currentColor;
                                        border-bottom: 1px solid currentColor;
                                        transform: rotate(-45deg);
                                        right: 10px;
                                        top: 8px;
                                    }
                                }
                                .js-size-wrap,.js-color-wrap{
                                    display: ;
                                }
                            }

                        }


                    }
                }
            }

        }

        @media (min-width: 768px) and (max-width: 1025px) {
            background-color: blueviolet;
            .u000233-model1-goods-wrap {

                .u000233-model1-list {
                    .comb-lists {
                        display: block;
                        font-size: 0;
                        width: 980/1024*100%;
                        margin: 30px auto 0;

                        .comb-goods-list {
                            display: inline-block;
                            width: 229/980 *100%;
                            vertical-align: top;
                            margin: 0 8/980*100% 30px;
                            position: relative;

                            .u000233-model1-goods-img {


                            }

                        }
                    }
                }
            }
        }

        @media (max-width: 767px) {
            .u000233-model1-goods-wrap {
                box-sizing: border-box;
                padding: 10px;


                .u000233-model1-list {
                    background-color: #ffffff;

                    .comb-lists {
                        display: block;
                        font-size: 0;
                        width: 342/355*100%;
                        margin: 0 auto;
                        padding-bottom: 12px;

                        .comb-goods-list {
                            display: inline-block;
                            width: 160/342 *100%;
                            vertical-align: top;
                            margin: 12px 5.5/342*100% 0;
                            position: relative;

                            .u000233-model1-goods-img {


                            }

                        }
                    }
                }
            }
        }
    }
</style>
