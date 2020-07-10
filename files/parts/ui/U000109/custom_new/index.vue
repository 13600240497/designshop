<template>
    <div class="geshop-u000109-custom-body geshop-U0000109-custom-new" :class="[{ 'geshop-hidden-box': noPreview }]"
         :style="style_body">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list-item">
                    <!--榜单 first-->
                    <div v-if="index == 0" class="item-rank" :style="style_first"></div>
                    <div v-else-if="index == 1" class="item-rank" :style="style_second"></div>
                    <div v-else-if="index == 2" class="item-rank" :style="style_three"></div>
                    <div v-else class="item-rank" :style="style_other" >
                        <span :style="style_color" >{{ index + 1 }}</span>
                    </div>

                    <!--折扣标-->
                    <!--<div class="item-discount"
                         v-if="discount_show == 1">
                        <geshop-discount :value="item.discount"></geshop-discount>
                    </div>-->

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

                        <!--<geshop-button-quick-view class="item-view"
                                                  :url_quick="item.url_quick">
                            <span>+{{ quick_view }}</span>
                        </geshop-button-quick-view>-->
                        <!-- 快速购买 -->
                        <geshop-button-quick-view style="text-transform: uppercase" class="item_view site-bold-strict"
                                                  :item="item"
                                                  :index="index"
                                                  v-if="Number(item.goods_number) > 0"
                                                  :url_quick="item.url_quick">
                            <span>{{ $lang('quick_shop') }}</span>
                        </geshop-button-quick-view>

                        <!-- 库存告急 -->
                        <geshop-stocktip class="item_stocktip" :item="typeof item == 'object' ? item : {}"></geshop-stocktip>

                        <!--sold out-->
                        <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>
                    </div>

                    <!--sold out -->
                    <!--<div class="item-soldOut" v-if="item.goods_number <= 0">
                        <span>{{ sold_out }}</span>
                    </div>-->

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
    </div>
</template>

<script>
import refresh_sku from '../../../dataCommon/refresh_sku_update.js';

export default {
    props: ['data'],
    extends: refresh_sku,
    data () {
        return {
            list: [
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack'
                }
            ],
            discount_show: 1, // 是否显示折扣标, 默认不显示
            discount_type: 1, // 折扣标展示方式
            price_content: '',
            sold_out: '',
            quick_view: '',
            target: '_blank'
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
            this.quick_view = this.$root.languages.quick_view;
            let oldLists = this.list;
            if (oldLists && oldLists.length > 0) {
                this.discount_show = this.data.discount_show;
                this.discount_type = this.data.discount_type;
                this.price_content = this.data.price_content;
                this.list = oldLists;
            }
            // 非发布页面不执行去除loading,json数据请求完毕再执行
            if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
                this.$store.dispatch('global/loaded', this);
            }
            // 页面初始化
            this.$store.dispatch('global/async_goods_init', this);
        },
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
        },
        // 排行榜 icon 的样式
        styleCss (url) {
            const img_width = this.data.list_img_width ? this.data.list_img_width : 40;
            const img_height = this.data.list_img_height ? this.data.list_img_height : 48;
            const left = this.data.list_img_left || 0;
            const top = this.data.list_img_top || 0;
            let style = {};
            if (url) {
                style = {
                    left: left + 'px',
                    top: top + 'px',
                    width: img_width + 'px',
                    height: img_height + 'px',
                    background: 'url(' + url + ') no-repeat top'
                };
            } else {
                style = {
                    left: left + 'px',
                    top: top + 'px',
                    width: img_width + 'px',
                    height: img_height + 'px'
                };
            }
            return style;
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
        style_first () {
            let _self = this;
            let url = 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingFirst.png';
            if (_self.data.list_first_icon && _self.data.list_first_icon !== '') {
                url = _self.data.list_first_icon;
            }
            return _self.styleCss(url);
        },
        style_second () {
            let _self = this;
            let url = 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingSecond.png';
            if (_self.data.list_second_icon && _self.data.list_second_icon !== '') {
                url = _self.data.list_second_icon;
            }

            return _self.styleCss(url);
        },
        style_three () {
            let _self = this;
            let url = 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingThird.png';
            if (_self.data.list_three_icon && _self.data.list_three_icon !== '') {
                url = _self.data.list_three_icon;
            }
            return _self.styleCss(url);
        },
        style_other () {
            let _self = this;
            let url = 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingOther.png';
            if (_self.data.list_other_icon && _self.data.list_other_icon !== '') {
                url = _self.data.list_other_icon;
            }
            return _self.styleCss(url);
        },
        style_color () {
            const _self = this;
            let img_width = _self.data.list_img_width ? _self.data.list_img_width : '40';
            let img_height = _self.data.list_img_height ? _self.data.list_img_height : '48';

            const style = {
                width: img_width + 'px',
                height: img_height + 'px',
                'line-height': img_height - 8 + 'px',
                color: _self.data.list_other_font_color ? _self.data.list_other_font_color : '#FFFFFF'
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

    .geshop-u000109-custom-body {
        background-color: #FFFFFF;
        margin-left: auto;
        margin-right: auto;
        width: 1200px;
        padding: 24px 24px 0px;
        box-sizing: border-box;

        li {
            display: inline-block;
            font-size: 0;
            width: 264px;
            margin-right: 32px;
            margin-bottom: 32px;
            vertical-align: middle;

            &:nth-child(4n) {
                margin-right: 0px;
            }
        }

        .list-item {
            position: relative;

            .item-rank {
                position: absolute;
                top: 0px;
                left: 12px;
                background-repeat: no-repeat;
                z-index: 1;
                background-size: 100% 100%!important;

                & span {
                    font-size: 18px;
                    font-family: Rubik-Medium;
                    text-align: center;
                    display: inline-block;
                }
            }

            .item-image {
                background: red;
                &:hover {
                    .item_view {
                        transform: translateY(0);
                        opacity: 0.8;
                        display: block;
                    }
                }

                .item_view {
                    cursor: pointer;
                    position: absolute;
                    left: 50%;
                    margin-left: -100px;
                    bottom: 100px;
                    font-size: 16px;
                    width: 200px;
                    height: 40px;
                    color: #333333;
                    background: rgba(255, 255, 255, 0.8);
                    border-radius: 2px;
                    border: 1px solid rgba(221, 221, 221, 1);
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    text-align: center;
                    line-height: 40px;
                    transition: all .15s linear;
                    transform: translateY(10px);
                    opacity: 0;
                    box-sizing: border-box;

                    &:hover {
                        background: rgba(255, 255, 255, 1);
                        opacity: 1 !important;
                    }
                }
            }

            .item-title {
                width: 258px;
                font-size: 14px;
                height: 20px;
                line-height: 20px;
                text-overflow: ellipsis;
                white-space: nowrap;
                word-wrap: break-word;
                overflow: hidden;
                margin-top: 12px;
                margin-bottom: 4px;
                color: #333333;
            }

            .item-shop {
                font-size: 14px;
                font-family: Rubik-Medium;
                color: #333333;
                line-height: 30px;

                & .shop-title {
                    vertical-align: middle;
                    display: inline-block;
                }
            }

            .item-market {
                line-height: 19px;
                height: 19px;
                color: #999999;
                font-size: 14px;
            }

            .item-soldOut {
                position: absolute;
                top: 96px;
                left: 52px;
                width: 160px;
                height: 160px;
                border-radius: 80px;
                background-color: #000000;
                opacity: 0.4;
                z-index: 1;

                & span {
                    display: inline-block;
                    text-align: center;
                    width: 66px;
                    height: 52px;
                    font-weight: 600;
                    line-height: 26px;
                    font-size: 24px;
                    color: #ffffff;
                    position: absolute;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 2;
                }
            }

            /*.item-view span {
                display: inline-block;
                max-width:224px;
                height: 34px;
                line-height: 34px;
                font-weight: 600;
                color: #333333;
                font-size: 14px;
                padding: 0 8px;
                text-overflow: ellipsis;
                white-space: nowrap;
                word-wrap: break-word;
                overflow: hidden;
            }*/
        }

        .list-item .item-image {
            width: 100%;
            height: 352px;
            overflow: hidden;
            position: relative;
        }
    }
</style>
