<template>
    <div class="geshop-u000120-style2-body" :style="style_body" v-if="visible">
        <ul class="gus2-list">
            <!-- 循环列表，区分广告图/商品图 -->
            <template v-for="(item, index) in list">
                <!-- 广告 -->
                <li v-if="item.is_ad" class="gus2-list-ad-item" :key="index">
                    <template v-if="item.ad_image == ''">
                        <div class="default-view-bg"
                             :style="{ 'background-image': `url('${data.default.view}')` }"></div>
                    </template>
                    <template v-else>
                        <a :href="item.ad_url" target="_BLANK">
                            <img :src="item.ad_image">
                        </a>
                    </template>
                </li>
                <!-- 商品 -->
                <li v-else :key="index" class="gus2-list-product-item">
                    <div class="gus2-rule" :style="style_rule">
                        <div><span><label
                            v-html="get_rule_label(item.activityInfo_thresholdamount, item.currency, item.currency_original_amount)"></label></span>
                        </div>
                    </div>
                    <!-- 图片区域 -->
                    <div class="gus2-image-layer">
                        <geshop-image-goods :src="item.goods_img" :sku="item.goods_sn"
                                            :index="index"></geshop-image-goods>
                        <!-- 未开始, 已结束, 售罄, 赠完 -->
                        <div class="gus2-product-status" v-if="show_mask(item)">
                            <span><label>{{ get_mask_label(item) }}</label></span></div>
                        <!--  -->
                        <div
                            v-if="get_product_item_status(item) <= 0"
                            class="gus2-add-bag"
                            @click="handleAddBag(item,index)">
                            <span>
                                <label :style="style_btn">
                                    {{ $root.languages.add_bag }}
                                </label>
                            </span>
                        </div>
                    </div>
                    <!-- 价格 -->
                    <div class="gus2-pricy-layer">
                        <geshop-shop-price :value="item.shop_price" :currency="item.currency"></geshop-shop-price>
                        <geshop-market-price :value="item.market_price" :currency="item.currency"></geshop-market-price>
                    </div>
                    <!-- 进度条 -->
                    <geshop-progress-bar
                        :color1="data.progress_color1"
                        :color2="data.progress_color2"
                        :current="parseInt(item.activity_number) - parseInt(item.activity_volume_number)"
                        :total="item.activity_number || 0">
                    </geshop-progress-bar>
                    <!-- 库存情况 -->
                    <div class="gus2-claimed-status" :style="style_claimd">
                        <span :style="{ 'direction': $root.lang == 'ar' ? 'rtl' : 'ltr' }">{{ item.activity_volume_number || 0 }} {{ $root.languages.claimed }}</span>
                        <span>/</span>
                        <span>{{ item.activity_number || 0 }}</span>
                    </div>
                </li>
            </template>
        </ul>
        <!-- 加购弹层 -->
        <geshop-dialog-add-bag ref="dialog"></geshop-dialog-add-bag>
    </div>
</template>

<script>
export default {
    props: ['pid', 'data'],
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
                'margin-top': `${this.data.margin_top || 0}px`,
                'margin-bottom': `${this.data.margin_bottom || 32}px`,
                'background-color': this.data.background_color || '#f8f8f8'
            };
        },
        style_rule () {
            const style = {
                'color': this.data.rule_font_color || '#333333',
                'font-size': `${this.data.rule_font_size || 20}px`
            };
            if (this.$root.lang === 'ar') {
                style['direction'] = 'rtl';
            }
            ;
            return style;
        },
        style_claimd () {
            return {
                'color': this.data.cliamd_color || '#333'
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
            const self = this;
            const listArr = [];
            const data = {
                activityid: this.data.promotion_ids,
                each_activity_count: this.data.show_count || 1
            };
            try {
                const res = await this.$jsonp(GESHOP_INTERFACE.fullgiftlist.url, data);
                this.list = res.data.goodsInfo.map((product, index) => {
                    product['activity_limit_number'] = product.activity_number - product.activity_volume_number;
                    return product;
                });

                // goods_number 库存为0时，排序到最后
                this.list.forEach((item, index) => {
                    if (item.goods_number && item.goods_number == 0) {
                        listArr.push(item);
                        self.list.splice(index, 1);
                    }
                    ;
                });
                this.list = this.list.concat(listArr);
            } catch (err) {
                this.list = [];
            }
            ;
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
            }
            if (parseInt(product.activity_number) - parseInt(product.activity_volume_number) <= 0) {
                return 4;
            }
            // 卖完
            if (parseInt(product.goods_number) <= 0) {
                return 3;
            }
            // 结束了
            if (this.timestamp > product.activityInfo_endtime * 1000) {
                return 2;
            }
            // 还没开始
            if (this.timestamp <= product.activityInfo_starttime * 1000) {
                return 1;
            }
            // 后台关闭了这个活动
            if (product.is_closed == '1') {
                return 2;
            }
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

        // 展示选择SKU的弹窗
        handleAddBag (product, index) {
            this.$refs.dialog.show({
                goods_sn: product.goods_sn,
                activity_id: product.activityInfo_id,
                rank: index
            });
        }
    },

    async mounted () {
        // 获取远端数据
        if (this.data.promotion_ids != undefined && this.data.promotion_ids != '') {
            await this.get_goods_list();
        }

        // 装修页 && 没有数据
        if (this.$root.is_edit_env) {
            this.visible = true;
            if (this.list.length === 0) {
                this.list = [{}, {}, {}];
            }
        } else {
            // 非装修页
            if (this.list.length > 0) {
                this.visible = true;
            }
        }

        // 判断是否开启广告图, 是的话在［0] 索引上追加
        if (this.data.show_ad === undefined || this.data.show_ad == '1') {
            this.list.unshift({
                is_ad: true,
                ad_image: this.data.ad_image || '',
                ad_url: this.data.ad_url || 'javascript:;'
            });
        }

        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000120-style2-body {
        background-color: #f8f8f8;
        padding: 24px;
        position: relative;
        width: 1200px;
        margin: 0 auto;
        box-sizing: border-box;
        padding-bottom: 24 - 16px;

        .gus2-list {
            position: relative;
            overflow: hidden;
            margin-right: -24px;

            li {
                position: relative;
                float: left;
                width: 276px;
                height: 508px;
                margin-right: 16px;
                margin-bottom: 16px;
                box-sizing: border-box;
            }

            li.gus2-list-ad-item {
                background: #EDEDED;
                padding: 0px;

                .default-view-bg {
                    display: block;
                    width: 100%;
                    height: 100%;
                    background-repeat: no-repeat;
                    background-position: center;
                }

                a {
                    img {
                        display: block;
                        width: 100%;
                        height: 100%;
                    }
                }
            }

            li.gus2-list-product-item {
                background: #fff;
                padding: 12px;
                text-align: center;
            }
        }

        // 门槛
        .gus2-rule {
            width: 100%;
            height: 44px;
            line-height: 1.2em;
            font-size: 22px;
            color: #333;
            margin-bottom: 12px;
            overflow: hidden;

            > div {
                display: table;
                width: 100%;
                height: 100%;
                text-align: center;

                > span {
                    display: table-cell;
                    vertical-align: middle;
                    text-align: center;
                }
            }
        }

        // 图片
        .gus2-image-layer {
            position: relative;
            display: block;
            width: 252px;
            height: 336px;
            display: table;
            margin-bottom: 12px;
        }

        // 价格
        .gus2-pricy-layer {
            height: 30px;
            line-height: 30px;
            overflow: hidden;
        }

        .gus2-claimed-status {
            height: 16px;
            line-height: 16px;
            font-size: 14px;
            color: #333;
            direction: ltr;

            span {
                display: inline-block;
            }
        }

        // 商品状态遮照层
        .gus2-product-status {
            position: absolute;
            top: 88px;
            left: 46px;
            width: 160px;
            height: 160px;
            background: rgba(0, 0, 0, .4);
            border-radius: 80px;
            display: table;

            > span {
                display: table-cell;
                vertical-align: middle;
                text-align: center;

                label {
                    font-size: 24px;
                    color: #fff;
                    line-height: 1.2em;
                }
            }
        }

        // 加购按钮
        .gus2-add-bag {
            position: absolute;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            bottom: 0px;
            font-size: 14px;
            color: #333;
            border: none;
            display: none;

            span {
                display: table-cell;
                text-align: center;
                vertical-align: middle;

                label {
                    display: inline-block;
                    padding: 8px 24px;
                    background: #fff;
                    text-align: center;
                    border-radius: 25%;
                    max-width: 132px;
                    border-radius: 999999px;
                    cursor: pointer;
                    opacity: 0.9;
                }
            }
        }

        li:hover {
            .gus2-add-bag {
                display: table;

                label:hover {
                    opacity: 1;
                }
            }
        }
    }
</style>
