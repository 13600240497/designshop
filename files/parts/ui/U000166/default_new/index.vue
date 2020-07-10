<template>
    <div class="geshop_u000166_default_new_body" v-if="list.length > 0" :style="style_body">
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
                        <div class="item_stock" :style="style_stock"><span>{{ integral(item.activity_left_number) }}</span></div>
                    </div>

                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                            v-if="item.url_title !== ''"
                            :item="item"
                            :index="index">
                            {{ item.goods_title }}
                        </geshop-analytics-href>
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
                            <div class="icons"><img :src="integralIconImg" alt="" /></div>
                            <span class="integral_value">{{ item.integral }}</span>
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

        <!--more or less-->
        <div class="item_more_less" v-if="isMore">
            <div class="view_more" @click="showMoreOrLess(1)">
                <span>{{ viewMore }}</span>
            </div>
        </div>

        <div class="item_more_less" v-if="isLess">
            <div class="view_more" @click="showMoreOrLess(0)">
                <span>{{ viewLess }}</span>
            </div>
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
            viewMore: '', // 查看更多
            viewLess: '', // 查看部分
            cloneGoodsNumber: '', // 记录所有商品数量
            isMore: false, // 是否显示more
            isLess: false, // 是否显示less
            iconDefault: 'https://geshopimg.logsss.com/uploads/l2JPgd4QrChzyMqEX91WfuUNnVp0AbRK.png', // 默认图
            showGoodsNumber: '', // 默认商品个数
            viewMoreScrollTop: 0 // 点击 view more 记录当前高度
        };
    },
    async mounted () {
        const self = this;
        const _url = GESHOP_INTERFACE.redeemlist.url;
        const _data = self.data;
        const _lang = typeof GESHOP_LANG !== 'undefined' ? GESHOP_LANG : 'en';
        const _pipeline = typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '';
        const _platform = typeof GESHOP_PLATFORM !== 'undefined' ? GESHOP_PLATFORM : '';

        this.integralIconImg = _data.integral_icon_img;
        this.showGoodsNumber = _data.default_show_goods_number;

        this.activityLeftNumber = this.$root.languages.left;
        this.redeemNow = this.$root.languages.redeem_now;
        this.viewMore = this.$root.languages.view_more;
        this.viewLess = this.$root.languages.view_less;

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
                    self.cloneGoodsNumber = res.data.goodsInfo;

                    // 默认商品个数是否为空
                    if (self.showGoodsNumber !== '' && self.list.length > 0) {
                        // 小于商品总数时，出现view more
                        if (self.showGoodsNumber < this.data.goodsInfo.length) {
                            self.isMore = true;
                            self.list = res.data.goodsInfo.slice(0, self.showGoodsNumber);
                        }
                    }

                    this.$store.dispatch('global/async_goods_init', this);
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
        style_body () {
            const _data = this.data;
            const style = {
                marginBottom: this.$px2rem(_data.margin_bottom),
                backgroundColor: _data.box_bj_color
            };
            return style;
        },

        /**
         *  @Description 剩于库存样式
         *
         */
        style_stock () {
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
    },
    methods: {
        /**
         *  @Description 加载更多
         *  @params type 类型 0:less, 1: more
         *
         */
        showMoreOrLess (type) {
            const self = this;

            // less
            if (type == 0) {
                self.isMore = true;
                self.isLess = false;
                self.list = self.list.slice(0, self.showGoodsNumber);
                this.$nextTick(() => {
                    $(window).scrollTop(this.viewMoreScrollTop);
                });
            }

            // more
            if (type == 1) {
                self.list = self.cloneGoodsNumber;
                self.isMore = false;
                self.isLess = true;
                this.$store.dispatch('global/async_goods_init', this);
                this.$nextTick(() => {
                    this.viewMoreScrollTop = $(window).scrollTop();
                });
            }
        }
    }
};
</script>

<style lang="less" scoped>

.geshop_u000166_default_new_body {
    display: flex;
    justify-content: center;
    flex-flow: row wrap;
    background-color: #F8F8F8;
    width: 750/75rem;
    padding: 24/75rem 24/75rem 0px;
    box-sizing: border-box;

    ul{
        display: flex;
        justify-content: space-between;
        flex-flow: row wrap;

        li{
            display: flex;
            width: 342/75rem;
            margin-bottom: 18/75rem;
            overflow: hidden;
            background-color: #ffffff;
        }
    }

    .item_more_less{
        margin-bottom: 48/75rem;
    }

    .view_more{
        display: flex;
        justify-content: center;
        width:290/75rem;
        height:56/75rem;
        border-radius:40/75rem;
        text-align: center;
        background-color: #ffffff;
        border:1px solid #333333;
        & span{
            font-family:OpenSans-Semibold;
            font-size:28/75rem;
            line-height:60/75rem;
        }
    }

    .list_item{
        position: relative;

        .item_image{
            width: 100%;
            height: 456/75rem;
            overflow: hidden;
            position: relative;
        }

        .item_stock{
            width: 100%;
            position: absolute;
            bottom: 0;
            left: 0;
            z-index: 1;
            height: 50/75rem;
            line-height: 50/75rem;
            text-align: center;
            font-size:24/75rem;
            background-color: #FFFFFF;
            color: #666666;
            opacity: 0.8;
        }

        .item_stock span{
            display: block;
            padding: 0rem 24/75rem;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow:hidden;

        }

        .item_title{
            padding: 0rem 24/75rem;
            width: 294/75rem;
            font-size:22/75rem;
            height: 30/75rem;
            line-height: 30/75rem;
            text-overflow: ellipsis;
            white-space: nowrap;
            word-wrap: break-word;
            overflow:hidden;
            margin-top: 18/75rem;
            margin-bottom: 8/75rem;
            color: #333333;
        }
        .item_title a{
            color: #333333;
        }

        .item_shop{
            height: 33/75rem;
            font-size: 24/75rem;
            color:#999999;
            line-height: 33/75rem;
            padding-left: 24/75rem;
        }
    }

    .list_item .item_dialog{
        position: absolute;
        top: 198/75rem;
        left: 24/75rem;
        width: 294/75rem;
        height: 60/75rem;
        border-radius: 80/75rem;
        background: rgba(0,0,0,0.4);
        z-index: 1;
        & span{
            display: inline-block;
            text-align: center;
            width: 262/75rem;
            height:26/75rem;
            line-height:26/75rem;
            font-size:22/75rem;
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
        display: flex;
        flex-flow: row nowrap;
        padding-left: 24/75rem;
        font-family:OpenSans-Semibold;
        font-size: 28/75rem;
        line-height: 38/75rem;
        .left /deep/ .geshop-shop-price{
            font-size: 28/75rem;
        }
        .right {
            display: flex;
            margin-left: 8/75rem;
        }
        .right .icons{
            width: 36/75rem;
            height: 36/75rem;
            overflow: hidden;
            margin-right: 8/75rem;
            line-height: 36/75rem;
        }
        .right img{
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
            vertical-align: middle;
        }
    }

    .list_item .item_btn_integral{
        margin-top: 24/75rem;
        margin-bottom: 24/75rem;
        text-align: center;

        & a{
            display: inline-block;
            width:294/75rem;
            height:60/75rem;
            line-height: 60/75rem;
            font-size:28/75rem;
        }
    }
}
</style>
