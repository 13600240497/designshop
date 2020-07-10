<template>
    <div class="geshop-u000219-template1_v3-body" :class="[style_class,{ 'geshop-hidden-box': noPreview }]" :style="style_body" :data-id="pid">
        <div class="geshop-u000219-wrap" :style="style_radius">
            <ul class="wrap-ul">
                <template>
                    <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
                        <div class="list-item">

                            <!--折扣标-->
                            <div class="item-discount">
                                <geshop-discount :value="item.discount"></geshop-discount>
                            </div>

                            <div class="item-image">
                                <geshop-analytics-href
                                    v-if="item.goods_number > 0"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id">
                                    <geshop-image-goods
                                        class="image-radius"
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :index="index">
                                    </geshop-image-goods>
                                </geshop-analytics-href>

                                <geshop-image-goods v-else
                                                    class="image-radius"
                                                    :src="item.goods_img"
                                                    :sku="item.goods_sn"
                                                    :index="index">
                                </geshop-image-goods>
                            </div>

                            <!--sold out-->
                            <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>

                            <!--sold out -->
                            <!--<div class="item-soldOut" v-if="item.goods_number <= 0">
                                <span>{{ sold_out }}</span>
                            </div>-->

                            <!--sku标题-->
                            <div class="item-title">
                                <geshop-analytics-href
                                    v-if="item.goods_number > 0"
                                    :item="item"
                                    :index="index">
                                    {{ item.goods_title }}
                                </geshop-analytics-href>
                                <span v-else>{{ item.goods_title }}</span>
                            </div>

                            <!--销售价-->
                            <div class="item-shop" :style="style_shop_color">
                                <div class="shop-title" :style="style_shop_foot">{{ price_first_content }}</div>
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>

                            <!--市场价-->
                            <div class="item-market" :style="style_market">
                                <template v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)">
                                    <div class="market-title">{{ price_second_content }}</div>
                                    <geshop-market-price
                                        :is_show_del="is_show_del"
                                        :value="item.market_price"
                                        :style="style_market"></geshop-market-price>
                                </template>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>

            <!--more or less-->
            <div class="item-more-less" v-if="isMore">
                <div class="view_more" @click="showMoreOrLess(1)" :style="style_view_more">
                    <span>{{ view_more }}</span>
                </div>
            </div>

            <div class="item-more-less" v-if="isLess">
                <div class="view_more" @click="showMoreOrLess(0)" :style="style_view_more">
                    <span>{{ view_less }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import refresh_sku from '../../../dataCommon/refresh_sku_update.js';

export default {
    props: ['data', 'pid'],
    extends: refresh_sku,
    data () {
        return {
            list: [
                {
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress …'
                },
                {
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress …'
                },
                {
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress …'
                },
                {
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress …'
                }
            ],
            discount_show: 1, // 是否显示折扣标, 默认显示
            discount_type: 1, // 折扣标展示方式
            price_first_content: '', // 销售价名称
            price_second_content: '', // 市场价名称
            sold_out: '',
            view_more: '',
            view_less: '',
            show_goods_number: '',
            arrData: [], // 所有商品列表
            isMore: false, // 是否显示more
            isLess: false,
            is_show_del: 2, // 市场价默认不显示删除线
            view_more_scroll_top: 0 // 点击 view more 记录当前高度
        };
    },
    mounted () {
        this.initMounted();
    },
    methods: {
        /**
         * mounted 初始函数
         */
        initMounted () {
            this.price_first_content = this.$root.languages.price_first_content;
            this.price_second_content = this.$root.languages.price_second_content;
            this.sold_out = this.$root.languages.sold_out;
            this.view_more = this.$root.languages.view_more;
            this.view_less = this.$root.languages.view_less;
            // 自动刷新后的sku
            let oldLists = this.list;

            if (oldLists && oldLists.length > 0) {
                this.discount_show = this.data.discount_show;
                this.discount_type = this.data.discount_type;
                this.show_goods_number = this.data.show_goods_number;
                this.price_first_content = this.data.price_first_content;
                this.price_second_content = this.data.price_second_content;
                this.is_show_del = Number(this.data.is_show_del);

                this.list = oldLists;
                this.arrData = oldLists;

                // 默认商品个数是否为空
                if (this.show_goods_number !== '') {
                    // 小于所有商品总数时，出现view more
                    if (this.show_goods_number < oldLists.length) {
                        this.isMore = true;
                        this.list = oldLists.slice(0, this.show_goods_number);
                    }
                }
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
        // 加载更多
        showMoreOrLess (type) {
            const self = this;

            // less
            if (type == 0) {
                self.isMore = true;
                self.isLess = false;
                self.list = self.list.slice(0, self.show_goods_number);
                this.$nextTick(() => {
                    $(window).scrollTop(this.view_more_scroll_top);
                });
            }

            // more
            if (type == 1) {
                self.list = self.arrData;
                self.isMore = false;
                self.isLess = true;
                this.$store.dispatch('global/async_goods_init', this);
                this.$nextTick(() => {
                    this.view_more_scroll_top = $(window).scrollTop();
                });
            }
        },

        scrollTop () {
            if (this.data.show_goods_number !== '') {
                const targetScrollTop = $(`.geshop-u000219-default-body-${this.pid}`).find('.wrap-ul').offset().top;
                $('html,body').animate({ scrollTop: targetScrollTop }, 500);
            }
        }
    },
    computed: {
        style_class () {
            return 'geshop-u000219-default-body-' + this.pid;
        },

        style_body () {
            const style = {
                marginTop: this.$px2rem(this.data.margin_top),
                marginBottom: this.$px2rem(this.data.margin_bottom),
                backgroundColor: this.data.box_bg_color
            };
            return style;
        },
        style_item () {
            let _size = this.data.goods_radius_size ? this.data.goods_radius_size : '12';
            const style = {
                'border-top-left-radius': this.$px2rem(_size),
                'border-top-right-radius': this.$px2rem(_size)
            };
            return style;
        },
        style_radius () {
            const _size = this.data.bg_radius_size ? this.data.bg_radius_size : '12';
            const style = {
                'border-radius': this.$px2rem(_size)
            };
            return style;
        },
        style_shop_foot () {
            const _size = this.data.price_font_size ? this.data.price_font_size : '24';
            const style = {
                fontSize: this.$px2rem(_size)
            };
            return style;
        },
        style_shop_color () {
            const style = {
                color: this.data.shop_price_color
            };
            return style;
        },
        style_market () {
            const style = {
                color: this.data.price_second_font_color
            };
            return style;
        },
        style_view_more () {
            const style = {
                background: this.$root.data.view_more_bg_color || '#333333',
                color: this.$root.data.view_more_font_color || '#ffffff'
            };
            return style;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000219-template1_v3-body{
        display:flex;
    }
    .geshop-u000219-default-body {
        display: flex;
        justify-content: center;
        background-color: #F8F8F8;
        width: 750/75rem;
        opacity: 0.99;

    }

    .geshop-u000219-wrap {
        display: flex;
        justify-content: center;
        flex-flow: row wrap;
        width: 702/75rem;
        margin: 24/75rem;
        padding: 24/75rem 24/75rem 0rem;
        border-radius: 12/75rem;
        background-color: #FFFFFF;
        box-sizing: border-box;

        .item-more-less {
            margin-bottom: 48/75rem;
        }

        .view_more {
            display: flex;
            justify-content: center;
            width: 294/75rem;
            height: 60/75rem;
            background-color: #333333;
            border-radius: 30/75rem;
            text-align: center;
            margin-top: 42/75rem;

            & span {
                font-size: 28/75rem;
                font-weight: 600;
                line-height: 60/75rem;
            }
        }

        ul {
            display: flex;
            justify-content: space-between;
            flex-flow: row wrap;
            width: 654/75rem;

            li {
                display: flex;
                width: 318/75rem;
                margin-bottom: 42/75rem;
                border-radius: 12/75rem;
                overflow: hidden;
            }

            li:nth-last-child(2) {
                margin-bottom: 0rem;
            }

            li:last-child {
                margin-bottom: 0rem;
            }

            .list-item {
                width: 318/75rem;
                position: relative;

                .item-image {
                    width: 100%;
                    height: 424/75rem;
                }

                .item-title {
                    width: 315/75rem;
                    font-size: 22/75rem;
                    height: 30/75rem;
                    line-height: 30/75rem;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    word-break: keep-all;
                    word-wrap: break-word;
                    margin-top: 18/75rem;
                    margin-bottom: 8/75rem;
                    color: #333333;

                    a {
                        color: #333333 !important;

                        &:hover {
                            color: #333333 !important;
                        }
                    }
                }

                .item-shop {
                    font-size: 24/75rem;
                    color: #333333;
                    line-height: 48/75rem;
                    font-family: OpenSans-Bold;

                    & .shop-title {
                        display: inline-block;
                    }
                }

                .item-market {
                    color: #999999;
                    font-size: 24/75rem;
                    line-height: 33/75rem;

                    .market-title {
                        display: inline-block;
                    }
                }

                .item-soldOut {
                    position: absolute;
                    top: 182/75rem;
                    left: 24/75rem;
                    width: 270/75rem;
                    height: 60/75rem;
                    border-radius: 80/75rem;
                    background: rgba(0, 0, 0, 0.4);
                    z-index: 1;

                    & span {
                        display: inline-block;
                        text-align: center;
                        font-weight: 600;
                        line-height: 26/75rem;
                        font-size: 24/75rem;
                        color: #ffffff;
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 2;
                    }
                }
            }
        }
    }
</style>
