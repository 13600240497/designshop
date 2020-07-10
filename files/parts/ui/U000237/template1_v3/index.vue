<template>
    <div class="geshop-U000237-model1-body" :class="[{ 'geshop-hidden-box': noPreview }]">
        <ul class="goods_list_wrap">
            <li v-for="(item,index) in list" :key="index" class="clearfix">
                <div class="list_item">
                    <div class="item_image">
                        <!-- 链接埋点 -->
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
                        <!-- 商品图片 -->
                        <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>
                        <!-- sold out  -->
                        <geshop-soldout :visible="item.goods_number <= 0" :type="soldoutType"></geshop-soldout>
                    </div>

                    <!-- 商品描述区 -->
                    <div class="item_content">
                        <div class="item_title font_regular" v-if="view.is_show_goods_title == 1">
                            <geshop-analytics-href
                                v-if="item.goods_number > 0"
                                :href="item.url_title"
                                target="_blank"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id">
                                {{ item.goods_title }}
                            </geshop-analytics-href>
                            <span v-else>{{item.goods_title}}</span>
                        </div>
                        <div class="geshop-price-content">
                            <!-- 销售价 -->
                            <div class="item_shop_price font_bold">
                                <div class="shop_title inline_block">{{ text.first_price_content }}
                                </div>
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!-- 市场价 -->
                            <div class="item_market_price font_regular">
                                <template
                                    v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) || mockData === true">
                                    <div class="market_title inline_block">{{ text.second_price_content }}
                                    </div>
                                    <geshop-market-price
                                        :is_show_del="view.is_show_del"
                                        :value="item.market_price"></geshop-market-price>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <!--折扣标-->
                <div class="item-discount"
                     v-if="view.discount_show == '1' && (mockData === true || (item.discount > 0 && item.discount< 100 && item.shop_price > 0 && Number(item.market_price) > Number(item.shop_price)))">
                    <geshop-discount :value="item.discount"></geshop-discount>
                </div>
            </li>
        </ul>

        <!--more or less-->
        <div class="item-more-wrap">
            <div class="item-more-less" v-if="isMore">
                <div class="view_more" @click="showMoreOrLess(1)" :style="style_view_more">
                    <span>{{ langText.view_more }}</span>
                </div>
            </div>

            <div class="item-more-less" v-if="isLess">
                <div class="view_more" @click="showMoreOrLess(0)" :style="style_view_more">
                    <span>{{ langText.view_less }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import refresh_sku from '../../../dataCommon/refresh_sku_update.js';

export default {
    extends: refresh_sku,
    data () {
        return {
            lang: '', // 当前语言
            data: {}, // 组件表单数据
            lazyLoad: true,
            mockData: true, // 是否是模拟数据
            noPreview: false, // 预览是否存在数据
            soldoutType: 'bottom', // sold类型
            // 商品列表数据
            list: [
                {
                    'goods_title': 'Plus Size Color Block Flare Tankini Set …',
                    'discount': '48'
                },
                {
                    'goods_title': 'Plus Size Color Block Flare Tankini Set …',
                    'discount': '48'
                },
                {
                    'goods_title': 'Plus Size Color Block Flare Tankini Set …',
                    'discount': '48'
                },
                {
                    'goods_title': 'Plus Size Color Block Flare Tankini Set …',
                    'discount': '48'
                }
            ],
            // 商品列表数据备份
            arrData: [],
            dataListGroup: [],
            view: {
                is_show_del: 2, // 市场价是否划掉 1 是 2 否
                discount_show: 1,
                discount_type: 1,
                discount_style: 1,
                is_show_goods_title: 1 // 商品title是否显示
            },
            text: {
                first_price_content: this.$root.data.first_price_content || 'New User Price:',
                second_price_content: this.$root.data.second_price_content || 'Market Price:'
            },
            langText: {
                quick_view: window.GESHOP_LANGUAGES['quick_view'] || 'QUICK VIEW',
                view_more: window.GESHOP_LANGUAGES['view_more'] || 'View More',
                view_less: window.GESHOP_LANGUAGES['view_less'] || 'View Less'
            },
            isMore: false, // 是否显示more
            isLess: false,
            view_more_scroll_top: 0, // 点击 view more 记录当前高度
            view_more_page: 0,
            view_max_page: 0
        };
    },
    computed: {
        style_view_more () {
            const style = {
                background: this.$root.data.view_more_bg_color || '#222222',
                color: this.$root.data.view_more_font_color || '#FFFFFF'
            };
            return style;
        }
    },
    methods: {
        /**
         * mounted 初始函数
         */
        initMounted () {
            let data = this.$root.data;
            this.data = data;
            this.view = {
                is_show_del: data.is_show_del || 2,
                discount_show: data.discount_show || 1,
                discount_type: data.discount_type || 1,
                discount_style: data.discount_style || 1,
                is_show_goods_title: data.is_show_goods_title || 1
            };
            let goodsInfo = this.list;
            // 获取商品列表数据
            if (goodsInfo && goodsInfo.length > 0) {
                this.arrData = goodsInfo;
                this.mockData = false;
                let goodsArr = goodsInfo || [];
                // 默认商品个数是否为空
                if (data.show_goods_number && data.show_goods_number != '') {
                    /*            // 小于所有商品总数时，出现view more
                                if (data.show_goods_number < data.goodsInfo.length) {
                                    this.isMore = true;
                                    this.list = this.list.slice(0, data.show_goods_number);
                                } */
                    if (data.show_goods_number < goodsInfo.length) {
                        this.isMore = true;
                        this.dataListGroup = this.dataArrayGroup(goodsInfo, data.show_goods_number);
                        goodsArr = this.dataListGroup[0];
                        this.view_more_page = 0;
                        this.view_max_page = this.dataListGroup.length > 0 ? this.dataListGroup.length - 1 : 0;
                    } else {
                        goodsArr = goodsInfo;
                    }
                }
                this.list = goodsArr;
            } else {
                this.arrData = this.list;
            }

            // 非发布页面不执行去除loading,json数据请求完毕再执行
            if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
                this.$store.dispatch('global/loaded', this);
            }
            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * view more/less switch
         * @param type 0 less 1 More
         */
        showMoreOrLess (type) {
            // less
            if (type == 0) {
                this.isMore = true;
                this.isLess = false;
                this.list = this.list.slice(0, this.data.show_goods_number);
                this.view_more_page = 0;
                this.$nextTick(() => {
                    $(window).scrollTop(this.view_more_scroll_top);
                });
            }

            // more
            if (type == 1) {
                let view_more_page = this.view_more_page;
                let view_max_page = this.view_max_page;
                if (view_more_page < view_max_page) {
                    let current_page = view_more_page + 1;
                    let page_data = this.dataListGroup[current_page];
                    if (page_data) {
                        this.list = this.list.concat(page_data);
                        this.view_more_page = current_page;
                        if (view_max_page == current_page) {
                            this.isMore = false;
                            this.isLess = true;
                        }
                    }
                } else {
                    this.isMore = false;
                    this.isLess = true;
                }

                if (view_more_page == 0) {
                    this.$nextTick(() => {
                        this.view_more_scroll_top = $(window).scrollTop();
                    });
                }
            }

            this.$nextTick(() => {
                if (window.GS_GOODS_LAZY_FN) {
                    window.GS_GOODS_LAZY_FN($('img.js_gdexp_lazy'));
                }
                if (window.GEShopSiteCommon) {
                    window.GEShopSiteCommon.renderCurrency();
                }
            });
        },
        /**
         * 商品数据分组
         * @param arr target Array
         * @param length Array group length
         * @returns {Array}
         */
        dataArrayGroup (arr, length) {
            let result = [];
            for (let i = 0, len = arr.length; i < len; i += length) {
                result.push(arr.slice(i, i + length));
            }
            return result;
        }
    },
    mounted () {
        this.initMounted();
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000237-model1-body {
        width: 100%;
        margin: auto;
        font-family: TrebuchetMS, Arial;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        font-size: 13/37.5rem;
        overflow: hidden;

        .goods_list_wrap {
            width: 9.68rem;
            margin: 0 auto;
            overflow: hidden;

            li {
                float: left;
                width: 4.493333rem;
                margin: 0 0.16rem 0.32rem;
                box-sizing: border-box;
                background-color: #FFFFFF;
                position: relative;

                &:nth-of-type(4n) {
                    margin-right: 0;
                }
            }
        }

        .list_item {
            position: relative;
        }

        .item_image {
            position: relative;
            overflow: hidden;

            img {
                width: 100%;
            }

            .geshop-zaful-image-goods {
                height: 100%;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;

            }
        }

        .item_content {
            margin: 12/37.5rem;
            overflow: hidden;

            .item_title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 8/37.5rem;

                * {
                    color: #222222;
                }
            }
        }

        .item_view {
            display: none;
            position: absolute;
            bottom: 100/37.5rem;
            left: 0/37.5rem;
            right: 0/37.5rem;
            margin: auto;
            width: 200/37.5rem;
            height: 40/37.5rem;
            text-align: center;
            background-color: #FFFFFF;
            cursor: pointer;
            opacity: 0.8;
            border: 1/37.5rem solid rgba(221, 221, 221, 1);
            border-radius: 2/37.5rem;
            box-sizing: border-box;
            overflow: hidden;
            z-index: 1;

            &:hover {
                opacity: 1;
            }

            span {
                display: inline-block;
                height: 40/37.5rem;
                line-height: 40/37.5rem;
                font-weight: 600;
                color: #333333;
                font-size: 16/37.5rem;
            }
        }

        .item_shop_price {
            /*margin-bottom: 4/37.5rem;*/

            .shop_title {
                max-width: 100%;
            }

            .my_shop_price {
                font-size: 16/37.5rem !important;
            }
        }

        .item_market_price {
            line-height: 17/37.5rem;

            .market_title {
                max-width: 60%;
            }
        }

        .geshop-shop-price {
            max-width: 100%;
            color: inherit;
            font-size: 0.48rem;
            line-height: 0.48rem;
        }

        /*        .item_shop_price .shop_title, .item_market_price .market_title {
                    max-width: 60%;
                }

                .item_shop_price .geshop-shop-price, .item_market_price .geshop-market-price {
                    max-width: 38%;
                }*/
        /*        .geshop-shop-price{
                    vertical-align: bottom;
                }*/

        .item_shop_price, .item_market_price {
            > div {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                vertical-align: middle;
            }
        }

        .geshop-components-default-image-goods {
            background: none;
        }

        .inline_block {
            display: inline-block;
        }

        .font_bold {
            font-family: 'Rubik-Medium';
        }

        .font_regular {
            font-family: 'Rubik-Regular';
        }

        .item-more-wrap {
            margin: 4/37.5rem 0 16/37.5rem;
        }

        .view_more {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            justify-content: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            width: 165/37.5rem;
            height: 36/37.5rem;
            margin: auto;
            background-color: #333333;
            border-radius: 2/37.5rem;
            text-align: center;

            & span {
                font-size: 28/75rem;
                font-weight: 600;
                line-height: 34/75rem;
            }
        }
    }

    .component-hide {
        display: none !important;
    }
</style>
