<template>
    <div class="geshop-u000109-custom-body" :class="`is-${site_code}`" :style="style_body">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list-item">
                    <!--榜单 first-->
                    <div v-if="index == 0" class="item-rank" :style="style_first"></div>
                    <div v-else-if="index == 1" class="item-rank" :style="style_second"></div>
                    <div v-else-if="index == 2" class="item-rank" :style="style_three"></div>
                    <div v-else class="item-rank" :style="style_other">
                        <span :style="style_color">{{ index + 1 }}</span>
                    </div>

                    <!--折扣标-->
                    <div class="item-discount" :class="site_code === 'rg-pc' ? 'geshop-discount-rg' : ''"
                         v-if="discount_show == 1">
                        <geshop-discount :value="item.discount"></geshop-discount>
                    </div>

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

                        <geshop-button-quick-view class="item-view site-bold-strict"
                                                  :item="item"
                                                  :index="index"
                                                  :url_quick="item.url_quick">
                            <span>+{{ quick_view }}</span>
                        </geshop-button-quick-view>
                    </div>

                    <!--sold out -->
                    <div class="item-soldOut site-bold-strict" v-if="item.goods_number <= 0">
                        <span>{{ sold_out }}</span>
                    </div>

                    <!--sku标题-->
                    <div class="item-title">
                        <geshop-analytics-href
                            v-if="item.goods_number > 0"
                            target="_blank"
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
                        <span v-else>{{ item.goods_title }}</span>
                    </div>

                    <!--销售价-->
                    <div class="item-shop site-bold-strict">
                        <div class="shop-title">{{ price_content }}</div>
                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                    </div>

                    <!--市场价-->
                    <div class="item-market">
                        <geshop-market-price v-if="item.shop_price <= item.market_price" :value="item.market_price"></geshop-market-price>
                    </div>

                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['data'],
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
            quick_view: ''
        };
    },
    mounted () {
        this.price_content = this.$root.languages.price_content;
        this.sold_out = this.$root.languages.sold_out;
        this.quick_view = this.$root.languages.quick_view;

        if (this.data.goodsInfo && this.data.goodsInfo.length > 0) {
            this.discount_show = this.data.discount_show;
            this.discount_type = this.data.discount_type;
            this.price_content = this.data.price_content;
            this.list = this.data.goodsInfo;
        }
        // 页面初始化
        this.$store.dispatch('global/async_goods_init', this);
    },
    methods: {
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
            const left = this.data.list_img_left || 12;
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
            let url = 'https://geshopimg.logsss.com/uploads/dYjSD7mRkobON2nWZagAf9rGLJsTwi3h.png';
            if (_self.data.list_first_icon && _self.data.list_first_icon !== '') {
                url = _self.data.list_first_icon;
            }
            return _self.styleCss(url);
        },
        style_second () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/LH5k8UDZxdBqE67Yo9XtGRgCQWIfzyhJ.png';
            if (_self.data.list_second_icon && _self.data.list_second_icon !== '') {
                url = _self.data.list_second_icon;
            }

            return _self.styleCss(url);
        },
        style_three () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/yparqCgv2YnZ1xe3uRP7iowMl8SAQBIN.png';
            if (_self.data.list_three_icon && _self.data.list_three_icon !== '') {
                url = _self.data.list_three_icon;
            }
            return _self.styleCss(url);
        },
        style_other () {
            let _self = this;
            let url = 'https://geshopimg.logsss.com/uploads/rkgPzLI91s3mXYneJuhCDi6OUy275Bwx.png';
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
                'line-height': img_height + 'px',
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

    li{
        display: inline-block;
        font-size: 0;
        width: 264px;
        margin-right: 32px;
        margin-bottom: 32px;
        vertical-align: middle;

        &:nth-child(4n){
            margin-right: 0px;
        }
    }
    .list-item{
        position: relative;

        .item-rank{
            position: absolute;
            top: 0px;
            left: 12px;
            background-repeat: no-repeat;
            z-index: 1;
            & span{
                font-size:24px;
                font-family:OpenSans-Bold;
                text-align: center;
                display: inline-block;
            }
        }

        .item-title{
            width: 258px;
            font-size:14px;
            height: 20px;
            line-height: 20px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            margin-top: 12px;
            margin-bottom: 4px;
            color: #333333;
        }

        .item-shop{
            font-size:18px;
            font-family:OpenSans-Bold;
            color: #333333;
            line-height: 30px;
            & .shop-title{
                display: inline-block;
            }
        }
        .item-market{
            line-height: 19px;
            height: 19px;
            color: #999999;
            font-size: 14px;
        }

        .item-soldOut{
            position: absolute;
            top: 96px;
            left: 52px;
            width: 160px;
            height: 160px;
            border-radius: 80px;
            background-color: #000000;
            opacity: 0.4;
            z-index: 1;
            font-weight:600;
            & span{
                display: inline-block;
                text-align: center;
                width:auto;
                height:auto;
                line-height:26px;
                font-size:24px;
                color: #ffffff;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%,-50%);
                z-index: 2;
            }
        }
        .item-view{
            display: none;
            position: absolute;
            left: 50%;
            top: 159px;
            width: auto;
            height: 34px;
            text-align: center;
            background-color: #FFFFFF;
            font-weight:600;
            cursor: pointer;
            opacity:0.7;
            z-index: 1;
            transform: translate(-50%,-50%);
            &:hover{
                opacity: 1;
            }
        }
        .item-view span {
            display: inline-block;
            max-width:224px;
            height:34px;
            line-height: 34px;
            color: #333333;
            font-size:14px;
            padding: 0 8px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow: hidden;
        }
    }
    .list-item .item-image{
        width: 100%;
        height: 352px;
        overflow: hidden;
    }
    &.is-rg-pc{
        padding: 16px 16px 0px;
        *{
            font-family: inherit;
        }
        li{
            width: 280px;
            margin-right: 16px;
            margin-bottom: 16px;
            &:nth-child(4n){
                margin-right: 0px;
            }
        }
        .shop-title{
            font-size: 14px;
        }
    }
}
</style>
