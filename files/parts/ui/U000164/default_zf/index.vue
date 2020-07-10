<template>
    <div class="geshop_u000164_default_zf_body" v-if="list.length > 0" :style="styleBody">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list_item">
                    <!--商品图片-->
                    <div class="item_image">
                        <geshop-analytics-href
                            v-if="goodsHref(item)"
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="item.goods_id">
                            <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                        </geshop-analytics-href>

                        <geshop-image-goods v-else :src="item.goods_img"></geshop-image-goods>

                        <!--活动剩于库存-->
                        <div class="item_stock" :style="styleStock"><span>{{ integral(item.activity_left_number) }}</span></div>
                    </div>

                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                            v-if="item.url_title !== ''"
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
<!--                        <a :href="item.url_title" v-if="item.url_title !== ''">{{ item.goods_title }}</a>-->
                        <span v-else>{{ item.goods_title }}</span>
                    </div>

                    <!--积分价格-->
                    <div class="item_integral">
                        <div class="left">
                            <geshop-shop-price
                                :value="item.integral_price"
                                class="price"></geshop-shop-price>
                            <label class="add">+</label>
                        </div>

                        <div class="right">
                            <span class="icon"><img :src="integralIconImg" alt="" /></span>
                            <label class="integral_value">{{ item.integral }}</label>
                        </div>
                    </div>

                    <!--本店价-->
                    <div class="item_shop">
                        <geshop-market-price
                            class="shop_price"
                            :value="item.shop_price"></geshop-market-price>
                    </div>

                    <!--兑换按钮-->
                    <div class="item_btn_integral">
                        <geshop-analytics-href
                            v-if="item.url_title !== ''"
                            :item="item"
                            :index="index">
                            {{ redeemNow }}
                        </geshop-analytics-href>
                    </div>

                    <!--dialog -->
                    <div class="item_dialog" v-if="!isShow">
                        <span>{{ dialogShowTitle }}</span>
                    </div>

                </div>
            </li>
        </ul>

        <!--view more-->
        <div class="view_more">
            <a :href="viewMoreUrl" target="_blank" v-if="viewMoreUrl != ''">{{ viewMoreTitle }}</a>
        </div>

    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            list: [
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    integral_price: '22.99',
                    integral: '99',
                    market_price: '44.99'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    integral_price: '22.99',
                    integral: '99',
                    market_price: '44.99'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    integral_price: '22.99',
                    integral: '99',
                    market_price: '44.99'
                },
                {
                    goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                    integral_price: '22.99',
                    integral: '99',
                    market_price: '44.99'
                }
            ], // 商品详情
            activityLeftNumber: '', // 活动剩于库存
            redeemNow: '', // 兑换按钮
            integralIconImg: '', // 积分图片
            iconDefault: 'https://geshopimg.logsss.com/uploads/OQagVAmSFJ4DKs3rLGTlp58H6kE02ZMv.png', // 默认图
            viewMoreUrl: '',
            viewMoreTitle: 'VIEW MORE' // view more文案
        };
    },
    async mounted () {
        const self = this;
        const _url = GESHOP_INTERFACE.redeemlist.url;
        const _data = self.data;
        const _lang = typeof GESHOP_LANG !== 'undefined' ? GESHOP_LANG : 'en';
        const _pipeline = typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '';
        const _platform = typeof GESHOP_PLATFORM !== 'undefined' ? GESHOP_PLATFORM : '';

        this.activityLeftNumber = this.$root.languages.left;
        this.redeemNow = this.$root.languages.redeem_now;
        this.viewMoreUrl = _data.view_more_url;
        this.viewMoreTitle = _data.view_more_title;
        this.integralIconImg = _data.integral_icon_img;

        const jsonData = {
            lang: _lang,
            pipeline: _pipeline,
            goodsSn: _data.goodsSKU,
            platform: _platform
        };

        // sku不能为空
        if (_data.goodsSKU && _data.goodsSKU !== '') {
            await this.$jsonp(_url, jsonData).then(res => {
                // 成功
                if (res.code === 0) {
                    self.list = res.data.goodsInfo;
                    // self.$nextTick(() => {
                    //     // 渲染图片
                    //     console.log($(self.$el), 1111);
                    //     const images = $(self.$el).find('img.js_gdexp_lazy');
                    //     window.GS_GOODS_LAZY_FN && window.GS_GOODS_LAZY_FN(images);
                    //     // 渲染货币
                    //     window.GEShopSiteCommon && window.GEShopSiteCommon.renderCurrency_v2();
                    // });
                    self.$store.dispatch('global/async_goods_init', self);
                }
            });
        }

        // 非编辑页,sku为空，组件不显示
        if (_data.isEditEnv !== 1) {
            if (_data.goodsSKU === '') {
                this.list = [];
            }
        } else {
            // 默认赋值
            if (!_data.goodsSKU) {
                this.integralIconImg = self.iconDefault;
            } else if (_data.goodsSKU && _data.goodsSKU === '') {
                this.integralIconImg = _data.integral_icon_img != '' ? _data.integral_icon_img : self.iconDefault;
            }
        }
    },
    computed: {
        // 剩于库存替换
        integral () {
            let self = this;
            return val => {
                return val != undefined ? self.activityLeftNumber.replace(/XX/g, val) : self.activityLeftNumber;
            };
        },

        /**
         *  @Description 组件样式
         *
         */
        styleBody () {
            const _data = this.data;
            const style = {
                marginBottom: _data.margin_bottom + 'px',
                backgroundColor: _data.box_bj_color
            };
            return style;
        },

        /**
         *  @Description 剩于库存样式
         *
         */
        styleStock () {
            const _data = this.data;
            const style = {
                backgroundColor: _data.stock_tip_bg_color,
                color: _data.stock_tip_font_color
            };
            return style;
        },

        /**
         *  @Description 剩于库存或销售库存为0, 兑换活动未开始，已结束状态，商品区域无交互
         *  @params item 商品sku单个详情
         *
         */
        goodsHref () {
            let self = this;
            return item => {
                self.isShow = true;
                let nowTime = new Date().getTime() / 1000;
                let exchangeStart = item.exchange_start; // 兑换开始时间
                let exchangeEnd = item.exchange_end; // 兑换结束时间

                // sold out
                if (item.activity_left_number <= 0 || item.goods_sale_number <= 0) {
                    self.isShow = false;
                    self.dialogShowTitle = this.$root.languages.sold_out;
                }
                // 未开始
                if (exchangeStart > nowTime) {
                    self.isShow = false;
                    self.dialogShowTitle = this.$root.languages.coming_soon;
                } else if (exchangeEnd < nowTime) { // 已结束
                    self.isShow = false;
                    self.dialogShowTitle = this.$root.languages.ended;
                }
                return self.isShow;
            };
        }
    }
};
</script>

<style lang="less" scoped>

.geshop_u000164_default_zf_body {
    background-color: #F8F8F8;
    margin-left: auto;
    margin-right: auto;
    max-width: 1200px;
    box-sizing: border-box;
    li{
        display: inline-block;
        width: 276px;
        padding: 12px;
        margin-right: 16px;
        margin-bottom: 16px;
        background-color: #ffffff;
        box-sizing: border-box;
        border:1px solid #ffffff;

        &:nth-child(4n){
            margin-right: 0px;
        }
    }
    .list_item{
        position: relative;

        .item_image{
            width: 100%;
            height: 336px;
            overflow: hidden;
            position: relative;
        }

        .item_stock{
            position: absolute;
            bottom: 0;
            left: 0;
            z-index: 1;
            width: 100%;
            height: 24px;
            line-height: 24px;
            text-align: center;
            font-size:14px;
            background-color: #FFFFFF;
            opacity: 0.8;
            color: #666666;
        }

        .item_stock span{
            display: block;
            padding: 0px 24px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow:hidden;
        }

        .item_title{
            width: 252px;
            font-size:14px;
            height: 20px;
            line-height: 20px;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            margin-top: 13px;
            margin-bottom: 4px;
            color: #333333;
        }

        .item_shop{
            height:19px;
            font-size:14px;
            color:#999999;
            line-height:19px;
        }
    }

    .list_item .item_dialog{
        position: absolute;
        top: 96px;
        left: 52px;
        width: 160px;
        height: 160px;
        border-radius: 80px;
        background-color: #000000;
        opacity: 0.4;
        z-index: 1;
        & span{
            display: inline-block;
            text-align: center;
            width:128px;
            height:22px;
            font-weight:600;
            line-height:22px;
            font-size:16px;
            color: #ffffff;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            z-index: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            font-family: OpenSans-Semibold;
        }
    }

    .list_item .item_integral{
        font-family:OpenSans-Semibold;
        font-size:22px;
        line-height: 30px;
        .left {
            display: inline-block;
        }
        .right {
            display: inline-block;
            margin-left: 8px;
        }
    }

    .list_item .item_integral .right {
        .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            overflow: hidden;
            vertical-align: -2px;
        }
        img{
            vertical-align: top;
        }
    }

    .view_more{
        margin-top: 8px;
        text-align: center;
        padding-bottom: 8px;

        & a{
            display: inline-block;
            width: 250px;
            height: 34px;
            line-height: 36px;
            border-radius: 20px;
            border: 1px solid #333333;
            font-size:16px;
            color: #333333;
            margin-bottom: 16px;
        }
    }
}
</style>
