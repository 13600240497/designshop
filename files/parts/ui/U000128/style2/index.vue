<template>
    <div class="geshop-u000128-style2-body" :style="style_body">
        <ul class="gus2-list">
            <!-- 循环列表，区分广告图/商品图 -->
            <template v-for="(item, index) in list" >
                <!-- 商品 -->
                <li class="gus2-list-product-item" :key="index" @click="handleGotoDetail(item)">
                    <div class="gus2-rule" :style="style_rule">
                        <label v-html="get_rule_label(item.activityInfo_thresholdamount, item.currency, item.currency_original_amount)"></label>
                    </div>
                    <!-- 左侧图片区域 -->
                    <div class="gus2-image-layer">
                        <geshop-image-goods :src="item.goods_img" :lazyload="false"></geshop-image-goods>
                        <!-- 未开始, 已结束, 售罄, 赠完 -->
                        <div class="gus2-product-status" v-if="show_mask(item)"><span><label>{{ get_mask_label(item) }}</label></span></div>
                    </div>

                    <!-- 价格 -->
                    <div class="gus2-pricy-layer">
                        <geshop-shop-price :value="item.shop_price" :currency="item.currency"></geshop-shop-price>
                        <br>
                        <geshop-market-price :value="item.market_price" :currency="item.currency"></geshop-market-price>
                    </div>

                    <!-- 库存情况 -->
                    <div class="gus2-claimed-status" :style="style_claimd">
                        <span :style="style_claimd_pre">{{ item.activity_volume_number || 0 }} {{ $root.languages.claimed }}</span>
                        <span>/ {{ item.activity_number || 0 }}</span>
                    </div>

                    <!-- 进度条 -->
                    <geshop-progress-bar
                        :color1="data.progress_color1"
                        :color2="data.progress_color2"
                        :current="parseInt(item.activity_number) - parseInt(item.activity_volume_number)"
                        :total="parseInt(item.activity_number)">
                    </geshop-progress-bar>

                </li>
            </template>
            <div class="after-block"></div>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            visible: false,
            list: [],
            timestamp: new Date().getTime()
        };
    },
    computed: {
        style_body () {
            return {
                'margin-top': `${(this.data.margin_top || 0) / 75}rem`,
                'margin-bottom': `${(this.data.margin_bottom || 40) / 75}rem`,
                'background-color': this.data.background_color || '#f2f2f2'
            };
        },
        style_rule () {
            const style = {
                'color': this.data.rule_font_color || '#333333',
                'font-size': `${(this.data.rule_font_size || 26) / 75}rem`
            };
            if (this.$root.lang === 'ar') {
                style['direction'] = 'rtl';
            };
            return style;
        },
        style_claimd () {
            return {
                'color': this.data.cliamd_color || '#333'
            };
        },
        style_claimd_pre () {
            return {
                direction: this.$root.lang === 'ar' ? 'rtl' : 'ltr'
            };
        },
        style_btn () {
            return {
                'color': this.data.btn_font_color || '#333',
                'border-radius': `${this.data.btn_radius || 17}px`
            };
        }
    },
    methods: {

        // 获取商品列表
        async get_goods_list () {
            const listArr = [];
            const self = this;

            const data = {
                activityid: this.data.promotion_ids,
                each_activity_count: this.data.show_count || 1
            };
            try {
                const res = await this.$jsonp(GESHOP_INTERFACE.fullgiftlist.url, data);
                this.list = res.data.goodsInfo.map(product => {
                    product['activity_limit_number'] = product.activity_number - product.activity_volume_number;
                    return product;
                });

                // goods_number 库存为0时，排序到最后
                this.list.forEach((item, index) => {
                    if (item.goods_number && item.goods_number === 0) {
                        listArr.push(item);
                        self.list.splice(index, 1);
                    };
                });
                this.list = this.list.concat(listArr);
            } catch (err) {
                this.list = [];
            };
        },

        /**
         * 返回商品所有的状态
         * 0 = 正常
         * 1 = 未开始
         * 2 = 结束了
         * 3 = 卖完了 (真实库存<=0)
         * 4 = 赠完了（活动库存<=0)
         */
        get_product_item_status (product) {
            // 赠完
            if (parseInt(product.activity_number) <= 0) {
                return 4;
            };
            if (parseInt(product.activity_number) - parseInt(product.activity_volume_number) <= 0) {
                return 4;
            };
            // 卖完
            if (parseInt(product.goods_number) <= 0) {
                return 3;
            };
            // 结束了
            if (this.timestamp > product.activityInfo_endtime * 1000) {
                return 2;
            };
            // 还没开始
            if (this.timestamp <= product.activityInfo_starttime * 1000) {
                return 1;
            };
            // 后台关闭了这个活动
            if (product.is_closed == '1') {
                return 2;
            };
            return 0;
        },

        // 是否展示萌层
        show_mask (product) {
            const status = this.get_product_item_status(product);
            return status > 0;
        },

        // 获取语言
        get_mask_label (product) {
            const status = this.get_product_item_status(product);
            const labels = [
                '',
                this.$root.languages.ready,
                this.$root.languages.ended,
                this.$root.languages.soldout,
                this.$root.languages.empty
            ];
            return labels[status];
        },

        // 返回满赠门槛语句
        get_rule_label (dollar = '0.00', currency = 'USD', original_amount = '0.00') {
            const label = `<span class="my_shop_price" data-orgp="${dollar}" data-currency="${currency}" data-original_amount="${original_amount}">$${dollar}</span>`;
            return this.$root.languages.rule.replace(/XX/g, label);
        },

        /**
         * 跳到商详页，M端和APP端跳的地址不一样
         * @description
         * 只有正常状态才可以点击进去详情页，其他状态点击无效
         * @example
         * M端：url_gift
         * APP端：app_gift_url
         * 兜底连接：url_title
         */
        handleGotoDetail (product) {
            const status = this.get_product_item_status(product);
            if (status !== 0) { return false; }
            // 正常状态才可以跳转
            let url = GESHOP_PLATFORM === 'app' ? product.app_gift_url : product.url_gift;
            window.location.href = url || geshopUrlToApp(product.url_title, product.goods_id);
        }
    },

    async mounted () {
        // 获取远端数据
        if (this.data.promotion_ids !== undefined && this.data.promotion_ids !== '') {
            await this.get_goods_list();
        };

        // 装修页 && 没有数据
        if (this.$root.is_edit_env) {
            this.visible = true;
            if (this.list.length === 0) {
                this.list = [ {}, {}, {} ];
            };
        } else {
            // 非装修页
            if (this.list.length > 0) {
                this.visible = true;
            };
        };

        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
        this.$store.dispatch('global/loaded', this);
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000128-style2-body {
        background-color: #f8f8f8;
        position: relative;
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
        padding: 24 / 75rem;
        padding-right: 0px;
        overflow-y: hidden;
        overflow-x: auto;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;

        .gus2-list {
            position: relative;
            display: flex;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;

            li {
                position: relative;
                flex-shrink: 0;
                display: block;
                width: 568 / 75rem;
                height: 320 / 75rem;
                margin-right: 24 / 75rem;
                box-sizing: border-box;
                padding: 24 / 75rem;
                padding-left: 264 / 75rem;
                padding-top: 30 / 75rem;
                background: #fff;
                overflow: hidden;
                border-radius: 12 / 75rem;
            }
            .after-block {
                display: block;
                width: 0.1px;
                flex-shrink: 0;
            }
        }

       // 图片
        .gus2-image-layer {
            position: absolute;
            width: 240 / 75rem;
            height: 320 / 75rem;
            left: 0px;
            top: 0px;
            display: table;
            > span {
                display: table-cell;
                vertical-align: middle;
                text-align: center;
                img {
                    display: inline-block;
                    vertical-align: middle;
                    width: 100%;
                }
            }
        }

        // 门槛
        .gus2-rule {
            width: 100%;
            min-height: 28 / 75rem;
            line-height: 28 / 75rem;
            font-size: 26 / 75rem;
            color: #FFA800;
            margin-bottom: 32 / 75rem;
        }

        // 价格
        .gus2-pricy-layer {
            margin-bottom: 24 / 75rem;
        }

        // 库存状态文字描述
        .gus2-claimed-status {
            height: 30 / 75rem;
            line-height: 30 / 75rem;
            font-size: 22 / 75rem;
            color: #333;
            margin-bottom: 4 / 75rem;
            direction: ltr;
            span {
                display: inline-block;
            }
        }

        // 商品状态遮照层
        .gus2-product-status {
            position: absolute;
            width: 240 / 75rem;
            height: 320 / 75rem;
            left: 0px;
            top: 0px;
            right: 0px;
            bottom: 0px;
            display: table;
            > span {
                display: table-cell;
                vertical-align: middle;
                text-align: center;
                padding-left: 24 / 75rem;
                padding-right: 24 / 75rem;
                label {
                    display: inline-block;
                    width: 100%;
                    font-size: 24 / 75rem;
                    color: #fff;
                    line-height: 1.2em;
                    padding: 17 / 75rem 24 / 75rem;
                    background: rgba(0, 0, 0, .4);
                    border-radius: 30 / 75rem;
                    box-sizing: border-box;
                }
            }
        }

    }
</style>
