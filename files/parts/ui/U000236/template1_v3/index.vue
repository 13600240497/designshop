<template>
    <div class="geshop-u000236-model1-body" :class="[{ 'geshop-hidden-box': noPreview }]">
        <ul class="goods_list_wrap">
            <li v-for="(item,index) in list" :key="index" class="clearfix">
                <!--折扣标-->
                <div class="item-discount"
                     v-if="view.discount_show == '1' && (mockData === true || (item.discount > 0 && item.discount< 100 && item.shop_price > 0 && Number(item.market_price) > Number(item.shop_price)))">
                    <geshop-discount :value="item.discount"></geshop-discount>

                </div>
                <div class="list_item">
                    <div class="item_image"
                         @mousemove="showQuickView($event, item.goods_number)"
                         @mouseout="hideQuickView($event, item.goods_number)">
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
                        <!-- 快速购买 -->
                        <geshop-button-quick-view class="item_view"
                                                  :item="item"
                                                  :index="index"
                                                  :url_quick="item.url_quick">
                            <span>{{ langText.quick_view }}</span>
                        </geshop-button-quick-view>
                        <!-- sold out  -->
                        <geshop-soldout :visible="item.goods_number <= 0" :type="soldoutType"></geshop-soldout>
                    </div>

                    <!-- 商品描述区 -->
                    <div class="item_content">
                        <div class="item_title font_regular">
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
                        <!-- 销售价 -->
                        <div class="item_shop_price font_bold">
                            <div class="shop_title inline_block">{{ text.first_price_content }}
                            </div>
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>
                        <!-- 市场价 -->
                        <div class="item_market_price font_regular">
                            <template v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) || mockData === true">
                                <div class="market_title inline_block">{{ text.second_price_content }}
                                </div>
                                <geshop-market-price
                                    :is_show_del="view.is_show_del"
                                    :value="item.market_price"></geshop-market-price>
                            </template>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
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
            view: {
                is_show_del: 2, // 市场价是否划掉 1 是 2 否
                discount_show: 1,
                discount_type: 1,
                discount_style: 1
            },
            langText: {
                quick_view: window.GESHOP_LANGUAGES['quick_view'] || 'QUICK VIEW'
            },
            text: {
                first_price_content: this.$root.data.first_price_content || 'New User Price:',
                second_price_content: this.$root.data.second_price_content || 'Market Price:'
            }
        };
    },
    computed: {},
    methods: {
        /**
         * mounted 初始函数
         */
        initMounted () {
            // 页面挂载完毕，去除 loadingTemplate
            this.$store.dispatch('global/loaded', this);
            let data = this.$root.data;
            this.data = data;
            this.view = {
                is_show_del: data.is_show_del || 2,
                discount_show: data.discount_show || 1,
                discount_type: data.discount_type || 1,
                discount_style: data.discount_style || 1
            };
            let goodsInfo = this.list;
            // 获取商品列表数据
            if (goodsInfo && goodsInfo.length > 0) {
                this.list = goodsInfo;
                this.mockData = false;
            }

            // 非发布页面不执行去除loading,json数据请求完毕再执行
            if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
                this.$store.dispatch('global/loaded', this);
            }
            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 显示快速购买按钮
         * @param $event
         * @param goods_number 商品数量
         */
        showQuickView ($event, goods_number) {
            const $target = $($event.target).parents('.list_item').find('.item_view');
            if (goods_number > 0) {
                $target.show();
            }
        },
        /**
         * 关闭快速购买
         * @param $event
         * @param goods_number 商品数量
         */
        hideQuickView ($event, goods_number) {
            const $target = $($event.target).parents('.list_item').find('.item_view');
            if (goods_number > 0) {
                $target.hide();
            }
        }
    },
    mounted () {
        this.initMounted();
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000236-model1-body {
        width: 1200px;
        margin: auto;
        font-family: TrebuchetMS, Arial;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        font-size: 14px;
        overflow: hidden;

        .goods_list_wrap {
            overflow: hidden;

            li {
                width: 288px;
                float: left;
                margin-right: 16px;
                margin-bottom: 16px;
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
            height: 384px;
            overflow: hidden;

            img {
                width: 100%;
            }
        }

        .item_content {
            margin: 12px 16px 20px 12px;
            overflow: hidden;

            .item_title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 8px;

                * {
                    color: #222222;
                }
            }
        }

        .item_view {
            display: none;
            position: absolute;
            bottom: 100px;
            left: 0px;
            right: 0px;
            margin: auto;
            width: 200px;
            height: 40px;
            text-align: center;
            background-color: #FFFFFF;
            cursor: pointer;
            opacity: 0.8;
            border: 1px solid rgba(221, 221, 221, 1);
            border-radius: 2px;
            box-sizing: border-box;
            overflow: hidden;
            z-index: 1;

            &:hover {
                opacity: 1;
            }

            span {
                display: inline-block;
                height: 40px;
                line-height: 40px;
                font-weight: 600;
                color: #333333;
                font-size: 16px;
            }
        }

        .item_shop_price {
            margin-bottom: 4px;

            .my_shop_price {
                font-size: 22px !important;
            }
        }

        .item_market_price {
            line-height: 17px;
            height: 17px;
        }

        .item_shop_price .shop_title, .item_market_price .market_title {
            max-width: 60%;
        }

        .item_shop_price .geshop-shop-price, .item_market_price .geshop-market-price {
            max-width: 38%;
        }

        .item_shop_price .geshop-shop-price {
            font-size: 22px;
            line-height: 22px;
        }

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
    }

    .component-hide {
        display: none !important;
    }
</style>
