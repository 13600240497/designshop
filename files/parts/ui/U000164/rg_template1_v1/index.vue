<template>
    <div class="geshop_u000164_rg_template1_v1_body" v-if="list.length > 0">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn" class="list_item">
                <!--商品图片-->
                <div class="item_image">
                    <geshop-analytics-href
                        :href="item.url_title"
                        :sku="item.goods_sn"
                        :cate="item.cateid"
                        :warehouse="item.warehousecode"
                        :goods_id="item.goods_id">
                        <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                    </geshop-analytics-href>
                    <!-- 兑换按钮 -->
                    <a class="exchange_view site-bold-strict" :href="item.url_title"
                       v-if="item.url_title !== '' && !(Number(item.activity_left_number)<=0 || Number(item.goods_number) <= 0)">
                        {{exchange_text}}
                    </a>
                    <!--sold out-->
                    <geshop-soldout class="item_soldout"
                                    :visible="Number(item.activity_left_number)<=0 || Number(item.goods_number) <= 0"
                                    :type="soldoutType"></geshop-soldout>
                </div>
                <div class="item_content">
                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-goods-title>
                            <geshop-analytics-href
                                v-if="item.url_title !== ''"
                                :item="item"
                                :index="index">
                                {{ item.goods_title }}
                            </geshop-analytics-href>
<!--                            <a :href="item.url_title" v-if="item.url_title !== ''">{{ item.goods_title }}</a>-->
                            <span v-else>{{ item.goods_title }}</span>
                        </geshop-goods-title>
                    </div>

                    <!--积分价格-->
                    <div class="item_integral inline-block">
                        <div class="left">
                            <geshop-shop-price
                                :value="item.integral_price"
                                class="price"></geshop-shop-price>
                        </div>

                        <div class="right">
                            <!--<span class="icon"><img :src="integralIconImg" alt=""/></span>-->
                            <label class="add">+</label>
                            <label class="integral_value">{{ item.integral }}points</label>
                        </div>
                    </div>

                    <!--市场价 -->
                    <div class="item_shop inline-block">
                        <geshop-market-price
                            class="shop_price"
                            :value="item.market_price"></geshop-market-price>
                    </div>
                    <!-- 库存进度条 -->
                    <geshop-progress-bar
                        :color1="data.stock_tip_has_color"
                        :color2="data.stock_tip_bg_color"
                        :current="item.activity_left_number"
                        :total="item.activity_number"
                    >
                    </geshop-progress-bar>
                    <!-- 库存描述百分比 -->
                    <geshop-progress-text
                        class="item_stock"
                        :item="item"
                    ></geshop-progress-text>
<!--                    &lt;!&ndash;活动剩于库存&ndash;&gt;-->
<!--                    <div class="item_stock">-->
<!--                        <span>{{ integral(item.activity_left_number,item.activity_number,item.goods_number) }}</span>-->
<!--                    </div>-->
                </div>
            </li>
        </ul>

        <!--view more-->
        <div class="view_more" v-if="view_more_target === 1">
            <a class="ellipsis view_more_btn btn__site-default" :href="view_more_url" target="_blank"
               v-if="view_more_url != ''">{{
                viewMoreTitle }}</a>
            <span class="ellipsis view_more_btn btn__site-default" v-else>{{ viewMoreTitle }}</span>
            <!--<a :href="view_more_url" :target="view_more_target === 1 ? '_blank':'_self'" v-if="view_more_url != ''">{{ viewMoreTitle }}</a>-->
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
            activityLeftPercent: '', // 活动剩于库存多语言文案
            integralIconImg: '', // 积分图片
            iconDefault: 'https://geshopimg.logsss.com/uploads/OQagVAmSFJ4DKs3rLGTlp58H6kE02ZMv.png', // 默认图
            exchange_text: '', // 兑换按钮文案
            view_more_target: '', // 1,2是否显示view more
            view_more_url: '', // view more链接
            soldoutType: 'bottom' // sold类型
        };
    },
    async mounted () {
        const self = this;
        const _data = self.data;
        this.activityLeftPercent = this.$lang('only_left');
        this.view_more_url = _data.view_more_url;
        this.viewMoreTitle = _data.view_more_title || 'VIEW MORE';
        this.view_more_target = Number(_data.view_more_target) || 1;
        this.exchange_text = _data.exchange_text || 'EXCHANGE';
        this.integralIconImg = _data.integral_icon_img;
        this.handleList();

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
            return (left = 0, total = 0, goods_number = 0) => {
                const percent = total >= left && total !== 0 && goods_number > 0 ? Math.ceil(parseFloat(left / total) * 100) : 0;
                return self.activityLeftPercent.replace(/xx/g, percent);
            };
        },

        /**
         * RG 不在兑换活动范围内，接口直接不返回,不调用goodsHref
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
                    // self.dialogShowTitle = this.$root.languages.sold_out;
                }
                // 未开始
                if (exchangeStart > nowTime) {
                    self.isShow = false;
                    // self.dialogShowTitle = this.$lang('coming_soon');
                } else if (exchangeEnd < nowTime) { // 已结束
                    self.isShow = false;
                    // self.dialogShowTitle = this.$lang('ended');
                }
                return self.isShow;
            };
        }
    },
    methods: {
        /**
         * 列表数据获取
         * @returns {Promise<void>}
         */
        async handleList () {
            const self = this;
            const _data = self.data;
            const _url = GESHOP_INTERFACE.redeemlist.url;
            const _lang = typeof GESHOP_LANG !== 'undefined' ? GESHOP_LANG : 'en';
            const _pipeline = typeof GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '';
            const _platform = typeof GESHOP_PLATFORM !== 'undefined' ? GESHOP_PLATFORM : '';

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
                    if (res.code === 0 && res.data && res.data.goodsInfo) {
                        // 装修页
                        if (_data.isEditEnv === 1 && res.data.goodsInfo.length === 0) {
                            return false;
                        }
                        const limit_num = parseInt(_data.goods_limit_num);
                        self.list = limit_num ? res.data.goodsInfo.slice(0, limit_num) : res.data.goodsInfo;
                        // 去除loading骨架图
                        self.$store.dispatch('global/loaded', self);
                        self.$store.dispatch('global/async_goods_init', self);
                    }
                });
            }
        }
    }
};
</script>

<style lang="less" scoped>
    @import "./component.less";
</style>
