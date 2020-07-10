<template>
    <div class="geshop-u000110-custom-body" :class="[{ 'geshop-hidden-box': noPreview }]" :style="style_body"
         :data-id="pid">
        <div class="geshop-u000110-wrap" :style="style_radius">
            <ul>
                <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
                    <div class="list-item">
                        <!--榜单 first-->
                        <div v-if="index === 0" class="item-rank" :class="'icon-'+(index + 1)" :style="style_first"></div>
                        <div v-else-if="index === 1" class="item-rank" :class="'icon-'+(index + 1)" :style="style_second"></div>
                        <div v-else-if="index === 2" class="item-rank" :class="'icon-'+(index + 1)" :style="style_three"></div>
                        <div v-else class="item-rank icon-other " :style="style_other">
                            <span :style="style_color">{{ index + 1 }}</span>
                        </div>

                        <!--折扣标-->
                        <div class="item-discount" :class="site_code.indexOf('rg') !== -1 ? 'geshop-discount-rg' : ''"
                             v-if="discount_show == 1">
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
                            <!-- 库存告急 -->
                            <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>
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
                        <div class="item-shop">
                            <div class="shop-title">{{ price_content }}</div>
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>

                        <!--市场价-->
                        <div class="item-market">
                            <geshop-market-price v-if="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price)"
                                                 :value="item.market_price"></geshop-market-price>
                        </div>
                    </div>
                </li>
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
            discount_show: 0, // 是否显示折扣标, 默认不显示
            discount_type: 1, // 折扣标展示方式
            price_content: '',
            sold_out: '',
            view_more: '',
            view_less: '',
            arrData: [], // 所有商品列表
            isMore: false, // 是否显示more
            isLess: false,
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
            this.price_content = this.$root.languages.price_content;
            this.sold_out = this.$root.languages.sold_out;
            this.view_more = this.$root.languages.view_more;
            this.view_less = this.$root.languages.view_less;
            // 自动刷新后的sku
            let oldLists = this.list;
            if (oldLists && oldLists.length > 0) {
                this.discount_show = this.data.discount_show;
                this.discount_type = this.data.discount_type;
                this.price_content = this.data.price_content;

                this.arrData = oldLists; // 拷贝备用, 用于more加载全部
                this.list = oldLists;
            } else {
                this.arrData = this.list;
            }
            // 默认商品个数是否为空
            if (this.data.show_goods_number && this.data.show_goods_number != '') {
                // 小于所有商品总数时，出现view more
                if (this.data.show_goods_number < this.list.length) {
                    this.isMore = true;
                    this.list = this.list.slice(0, this.data.show_goods_number);
                }
            }
            // 非发布页面不执行去除loading,json数据请求完毕再执行
            if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
                this.$store.dispatch('global/loaded', this);
            }
            // 页面元素初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
        // more or less
        showMoreOrLess (type) {
            // less
            if (type == 0) {
                this.isMore = true;
                this.isLess = false;
                this.list = this.list.slice(0, this.data.show_goods_number);
                this.$nextTick(() => {
                    $(window).scrollTop(this.view_more_scroll_top);
                });
            }

            // more
            if (type == 1) {
                this.list = this.arrData;
                this.isMore = false;
                this.isLess = true;
                this.$nextTick(() => {
                    this.view_more_scroll_top = $(window).scrollTop();
                });
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
        // 排行榜 icon 的样式
        styleCss (url) {
            const _self = this;
            const list_img_width = _self.data.list_img_width ? _self.data.list_img_width : '52';
            const list_img_height = _self.data.list_img_height ? _self.data.list_img_height : '58';
            const left = this.data.list_img_left || 0;
            const top = this.data.list_img_top || 0;
            return {
                left: this.$px2rem(left),
                top: this.$px2rem(top),
                width: this.$px2rem(list_img_width),
                height: this.$px2rem(list_img_height),
                background: 'url(' + url + ') no-repeat top',
                backgroundSize: '100%'
            };
        }
    },
    computed: {
        style_body () {
            const _self = this;
            const bg_color = _self.data.box_bg_color ? _self.data.box_bg_color : '#f8f8f8';
            const style = {
                marginTop: _self.$px2rem(_self.data.margin_top),
                marginBottom: _self.$px2rem(_self.data.margin_bottom),
                'background-color': bg_color
            };
            return style;
        },

        style_item () {
            const _size = this.data.goods_radius_size ? this.data.goods_radius_size : '12';
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
        style_first () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/z5ZqQuGj3voW89UsYMalpkE0f7LemcRb.png';
            if (_self.data.list_first_icon && _self.data.list_first_icon !== '') {
                url = _self.data.list_first_icon;
            }
            return _self.styleCss(url);
        },
        style_second () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/xl41J7BzMFRVHvWwgqcdsjhyuoIUiP6Z.png';
            if (_self.data.list_second_icon && _self.data.list_second_icon !== '') {
                url = _self.data.list_second_icon;
            }
            return _self.styleCss(url);
        },
        style_three () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/UcvodEBAXalT3knJKPW9SmZuh1IHLypN.png';
            if (_self.data.list_three_icon && _self.data.list_three_icon !== '') {
                url = _self.data.list_three_icon;
            }
            return _self.styleCss(url);
        },
        style_other () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/IqU84Fgic7zl0bk56HBLDNCmRuvKfeXh.png';
            if (_self.data.list_other_icon && _self.data.list_other_icon !== '') {
                url = _self.data.list_other_icon;
            }
            return _self.styleCss(url);
        },
        style_color () {
            const _self = this;
            const list_img_width = _self.data.list_img_width ? _self.data.list_img_width : '52';
            const list_img_height = _self.data.list_img_height ? _self.data.list_img_height : '58';

            const style = {
                width: _self.$px2rem(list_img_width),
                height: _self.$px2rem(list_img_height),
                'line-height': _self.$px2rem(list_img_height),
                color: _self.data.list_other_font_color ? _self.data.list_other_font_color : '#FFFFFF'
            };
            return style;
        },
        style_view_more () {
            const style = {
                background: this.$root.data.view_more_bg_color || '#333333',
                color: this.$root.data.view_more_font_color || '#ffffff'
            };
            return style;
        },
        site_code () {
            return window.GESHOP_SITECODE;
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000110-custom-body {
        display: flex;
        justify-content: center;
        width: 750/75rem;
        opacity: 0.99;

        .item-title,.item-shop,.item-market {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding:0 8/37.5rem;
        }
    }

    .geshop-u000110-wrap {
        display: flex;
        justify-content: center;
        flex-flow: row wrap;
        width: 702/75rem;
        margin: 24/75rem;
        padding: 24/75rem 24/75rem 0rem;
        background-color: #f8f8f8;

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
                width: 317.5/75rem;
                margin-bottom: 42/75rem;
                border-radius: 12/75rem;
                overflow: hidden;
                background-color: #ffffff;
            }

            .list-item {
                width: 318/75rem;
                position: relative;

                .item-rank {
                    position: absolute;
                    top: 0px;
                    left: 12px;
                    background-repeat: no-repeat;
                    z-index: 1;

                    & span {
                        font-size: 32/75rem;
                        font-weight: bold;
                        text-align: center;
                        display: inline-block;
                    }
                }

                .item-image {
                    width: 100%;
                    height: 424/75rem;
                    position: relative;
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
                    height: auto;
                    font-size: 26/75rem;
                    font-family: OpenSans-Bold;
                    line-height: 48/75rem;
                    color: #333333;

                    & .shop-title {
                        line-height: 30/75rem;
                        display: inline-block;
                    }
                }

                .item-market {
                    line-height: 33/75rem;
                    color: #999999;
                    font-size: 24/75rem;
                    margin-bottom: 8/37.5rem;
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
