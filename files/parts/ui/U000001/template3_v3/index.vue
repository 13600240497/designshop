<template>
    <div class="geshop_U000001_template3_v3_wrapper" :class="[{ 'no_buy_now': buynow_show == 0 }, userGroupClass]" v-if="list.length > 0">

        <div class="wrap_left">
            <a :href="ad_link">
                <geshop-image-goods :src="ad_image" v-if="ad_image && ad_image != ''" ></geshop-image-goods>
                <div v-else class="default_img"></div>
            </a>
        </div>

        <div class="wrap_swiper">

            <swiper :options="swiperOption" ref="mySwiper">
                <!-- slides -->
                <swiper-slide v-for="(item, idx) in list"
                              :key="item.goods_sn">
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
                            <div class="item_market">
                                <geshop-market-price v-if="item.show_market_price" :value="item.market_price"></geshop-market-price>
                            </div>
                        </div>

                        <!--bottom_buy-->
                        <div class="item_button_buy" v-show="buynow_show == 1">
                            <geshop-analytics-href
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :href="item.url_title">
                                <geshop-buynow></geshop-buynow>
                            </geshop-analytics-href>
                        </div>

                    </div>
                </swiper-slide>
                <div class="swiper-button-prev" slot="button-prev"><i></i></div>
                <div class="swiper-button-next" slot="button-next"><i></i></div>
            </swiper>
        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: 'template3_v3',
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    data () {
        // let _self = this;
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
                }
            ], // 默认商品列表
            swiperOption: {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 16,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                loop: true,
                loopFillGroupWithBlank: true,
                autoplay: {
                    enabled: true,
                    disableOnInteraction: false,
                    delay: 5000
                },
                on: {
                    init: function () {
                        $('.js_gdexp_lazy').each((index, item) => {
                            let original = $(item).attr('data-original');
                            let src = $(item).attr('src');
                            if (original != src) {
                                $(item).attr('src', original);
                            }
                        });
                    },
                    slideChangeTransitionEnd: function () {
                        // 页面初始化
                        // _self.$store.dispatch('global/async_goods_init', _self.$refs.mySwiper);
                    }
                }
            },
            ad_image: '', // 广告图
            ad_link: '', // 图片跳转链接
            buynow_show: 1 // 是否显示buynow
        };
    },
    mounted () {
        const $data = this.data;
        this.ad_link = $data.ad_link != '' ? $data.ad_link : 'javascript:;';
        this.ad_image = $data.ad_image;
        this.buynow_show = $data.buynow_show;
        this.isDateRes && this.init();
    },
    computed: {
        swiper () {
            return this.$refs.mySwiper.swiper;
        },
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

<style lang="less">
.geshop_U000001_template3_v3_wrapper {
    .swiper-button-prev, .swiper-button-next {
        height: 65px;
        width: 40px;
        background: #FFFFFF none;
        top: 169px;
        margin-top: 0;

        &:focus {
            outline: none
        }
        &:after {
            content: "";
            border-top: 2px solid #000;
            border-right: 2px solid #000;
            width: 10px;
            height: 10px;
            line-height: 0;
            font-size: 0;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-top: -7px;
        }
    }
    .swiper-button-prev{
        &:after{
            content: "";
            -webkit-transform: rotate(-135deg);
            transform: rotate(-135deg);
            margin-left: -3px;
        }
        left: 0;
    }
    .swiper-button-next {
        &:after{
            content: "";
            margin-left: -12px;
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
        }
        right: 0;
    }
}
</style>

<style scoped lang="less">
.geshop_U000001_template3_v3_wrapper {
    position: relative;
    width: 1200px;
    height: 517px;
    margin: 0 auto;
    overflow: hidden;
    background-color: #f8f8f8;

    .wrap_left {
        position: absolute;
        left: 0px;
        top: 0px;
        display: block;
        width: 285px;
        height: 100%;
        background: #EDEDED;

        .default_img{
            width:100%;
            height:100%;
            background: url("https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/view.png") no-repeat center center;
            background-size:120px 94px;
        }
    }

    .wrap_swiper {
        position: relative;
        margin-left: 284px;
        padding-top: 16px;
        padding-left: 16px;
        padding-right: 16px;
        display: block;
        width: 914 - 32px;
        overflow: hidden;

        .list_item {
            float: left;
            position: relative;
            width: 260px;
            height: auto;
            margin-right: 16px;
            margin-bottom: 16px;
            padding: 12px;
            background: #fff;
        }

        .item_button_buy {
            font-size:16px;
            margin-top: 12px;
            & /deep/ .geshop-button-buynow {
                height: 36px;
            }
            &:hover {
                opacity: 0.9;
            }
        }

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
            color: #333333;
        }

        .item_content{
            margin-top: 4px;
            height: 30px;
            line-height: 30px;
            overflow: hidden;
        }

        .item_shop{
            display: inline-block;
            font-size: 22px;
            color: #333333;
            margin-right: 6px;
        }

        .item_market{
            display: inline-block;
            color: #999999;
            font-size: 14px;
        }

        .item_soldout{
            z-index: 99;
        }

        .item_image{
            position: relative;
            display: block;
            width: 100%;
            height: 347px;
            overflow: hidden;
        }
    }

    &.no_buy_now{
        height: 469px;

        .list_item{
            height: auto;
        }
    }
}
</style>
