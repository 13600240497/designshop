<template>
    <div class="geshop_u000166_rg_template1_v1_body" v-if="list.length > 0">
        <ul>
            <li v-for="(item, index) in list" :key="item.goods_sn">
                <div class="list_item">
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
                        <!--sold out-->
                        <geshop-soldout class="item_soldout"
                                        :visible="Number(item.activity_left_number)<=0 || Number(item.goods_number) <= 0"
                                        :type="soldoutType"></geshop-soldout>
                    </div>

                    <div class="item_content">
                        <!--sku标题-->
                        <!--<div class="item_title">
                            <a :href="item.url_title" v-if="item.url_title !== ''">{{ item.goods_title }}</a>
                            <span v-else>{{ item.goods_title }}</span>
                        </div>-->

                        <!--积分价格-->
                        <div class="item_integral">
                            <div class="left">
                                <geshop-shop-price
                                    :value="item.integral_price"
                                    class="price"></geshop-shop-price>
                            </div>

                            <div class="right">
                                <!--<div class="icons"><img :src="integralIconImg" alt="" /></div>-->
                                <label class="add">+</label>
                                <span class="integral_value">{{ item.integral }}points</span>
                            </div>
                        </div>

                        <!--市场价-->
                        <div class="item_shop">
                            <geshop-market-price
                                class="shop_price"
                                :value="item.market_price"></geshop-market-price>
                        </div>
                        <!-- 库存描述百分比 -->
                        <geshop-progress-text
                            class="item_stock"
                            :item="item"
                        ></geshop-progress-text>
<!--                        &lt;!&ndash;活动剩于库存&ndash;&gt;-->
<!--                        <div class="item_stock">-->
<!--                            {{ integral(item.activity_left_number,item.activity_number,item.goods_number) }}-->
<!--                        </div>-->
                    </div>
                </div>
            </li>
        </ul>

        <!--view more-->
        <div class="view_more" v-if="view_more_target === 1">
            <a class="ellipsis view_more_btn" :href="view_more_url" target="_blank"
               v-if="view_more_url != ''">{{ viewMoreTitle }}</a>
            <span class="ellipsis view_more_btn" v-else>{{ viewMoreTitle }}</span>
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
            integralIconImg: '', // 积分图片
            iconDefault: 'https://geshopimg.logsss.com/uploads/l2JPgd4QrChzyMqEX91WfuUNnVp0AbRK.png', // 默认图
            goods_limit_num: '', // 默认商品个数
            exchange_text: '', // view more文案
            activityLeftPercent: '', // 活动剩于库存多语言文案
            view_more_url: '', // view more链接
            view_more_target: '', // 1,2是否显示view more
            soldoutType: 'bottom' // sold类型
        };
    },
    async mounted () {
        const self = this;
        const _data = self.data;
        this.view_more_url = _data.view_more_url;
        this.viewMoreTitle = _data.view_more_title || 'VIEW MORE';
        this.view_more_target = Number(_data.view_more_target) || 1;
        this.integralIconImg = _data.integral_icon_img;
        this.goods_limit_num = _data.goods_limit_num;
        this.activityLeftPercent = this.$lang('left').charAt(0).toUpperCase() + this.$lang('left').slice(1);

        // sku不能为空
        if (_data.goodsSKU && _data.goodsSKU !== '') {
            this.handleList();
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
            return (left = 0, total = 0, goods_number = 0) => {
                const percent = total >= left && total !== 0 && goods_number > 0 ? Math.ceil(parseFloat(left / total) * 100) : 0;
                return `${percent}% ${self.activityLeftPercent}`;
                // return self.activityLeftPercent.replace(/xx/g, percent);
            };
        }
    },
    methods: {
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
            await this.$jsonp(_url, jsonData).then(res => {
                // 成功
                if (res.code === 0 && res.data && res.data.goodsInfo) {
                    // 装修页
                    if (_data.isEditEnv === 1 && res.data.goodsInfo.length === 0) {
                        return false;
                    }
                    const limit_num = parseInt(self.goods_limit_num);
                    self.list = limit_num ? res.data.goodsInfo.slice(0, limit_num) : res.data.goodsInfo;
                    // 去除loading骨架图
                    self.$store.dispatch('global/loaded', self);
                    self.$store.dispatch('global/async_goods_init', self);
                }
            });
        }
    }
};
</script>

<style lang="less" scoped>
    @import "./component.less";

</style>
