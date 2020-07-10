<template>
    <div class="geshop-u000163-rg_template1_v1-body" v-if="list.length > 0">
        <div class="geshop-component-wrapper">
            <ul>
                <template v-for="(item, index) in list">
                    <li
                        v-if="index < skuLimit"
                        :key="item.goods_sn"
                        :class="get_item_status_className(item)">
                        <div class="item-image">
                            <!-- 折扣标 -->
                            <geshop-discount
                                :value="item.discount"
                                :visible="parseInt(item.discount) > 0">
                            </geshop-discount>

                            <!-- 链接 -->
                            <geshop-analytics-href
                                :disabled="item.goods_number <= 0"
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id">
                                <geshop-image-goods :src="item.goods_img" :sku="item.goods_sn" :index="index"></geshop-image-goods>
                            </geshop-analytics-href>

                            <!-- 售罄弹层 -->
                            <geshop-soldout :visible="item.goods_number <= 0"></geshop-soldout>
                        </div>

                        <!-- promotion 信息 -->
                        <div class="item-promotion">
                            {{ get_item_promotion_label(item) }}
                            <span class="my_shop_price my-shop-price site-bold-strict" :data-orgp="item.preview_price">${{ item.preview_price || '0.00' }}</span>
                        </div>

                        <!-- 其他信息 -->
                        <div class="item-info">
                            <div class="item-title">
                                <geshop-goods-title>{{ item.goods_title || 'Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}</geshop-goods-title>
                            </div>
                            <div class="item-price site-font-bold">
                                {{ get_item_promotion_label2(item) }}
                                <geshop-market-price :value="item.shop_price" />
                                <!-- <span class="my_shop_price my-shop-price" :data-orgp="item.shop_price">${{ item.shop_price || '0.00' }}</span> -->
                            </div>
                        </div>

                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            list: [
                { discount: 99 },
                { discount: 99 },
                { discount: 99 },
                { discount: 99 }
            ]
        };
    },
    computed: {
        // 设置固定展示多少个SKU
        skuLimit () {
            return this.$root.data.skuLimit || 65535;
        }
    },
    methods: {
        /**
         * 根据商品的预促销时间判断，获取商品的样式 class name, 可选 is-ready=未开始, is-now=已开始或结束
         * @param {timestamp} item.promotion_start 预促销的开始时间
         * @param {timestamp} item.promotion_end 预促销的结束时间
         * @return {string} 标记商品状态的样式的calss name
         *  */
        get_item_status_className (item) {
            const promotion_start = parseInt(item.promotion_start) * 1000;
            // const promotion_end = parseInt(item.promotion_end) * 1000;
            const now = new Date().getTime();
            return now < promotion_start ? 'is-ready' : 'is-now';
        },

        /**
         * 根据商品的预促销状态, 获取预促销栏目的文案
         * @param {object} item 商品对象，约等于data.goodsInfo[X]
         * @return {string} 返回文案
         */
        get_item_promotion_label (item) {
            const status = this.get_item_status_className(item);
            return status === 'is-ready' ? this.$root.data.pre_text || 'Upcoming' : this.$root.data.start_text || 'Now';
        },

        /**
         * 根据商品的预促销状态, 获取预促销栏目2的文案
         * @param {object} item 商品对象，约等于data.goodsInfo[X]
         * @return {string} 返回文案
         */
        get_item_promotion_label2 (item) {
            const status = this.get_item_status_className(item);
            return status === 'is-ready' ? this.$root.data.notstart_shopprice_text || 'Now' : '';
        },

        /**
         * 获取商品数据
         * @return {Array}
         */
        async get_list () {
            try {
                const goodsSn = this.$root.data.goodsSKU;
                const res = await this.$jsonp(GESHOP_INTERFACE.prepromotion.url, { goodsSn });
                return res.data.goodsInfo || [];
            } catch (err) {
                return [];
            }
        }
    },
    async created () {
        const arr = await this.get_list();
        // 有数据，或者非装修，才取AJAX的值，否则还是读取默认的值
        if (this.$root.is_edit_env == '0' || arr.length > 0) {
            this.list = arr;
        }
        // 去除骨架图
        this.$store.dispatch('global/loaded', this);
        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000163-rg_template1_v1-body {
        width: 100%;
    }
    .geshop-component-wrapper {
        width: 100%;
        overflow: hidden;
        margin: 0 auto;
        box-sizing: border-box;
        padding-left: 24 / 75rem;
        padding-top: 30 / 75rem;
        padding-bottom: 12 / 75rem;

        ul {
            display: block;
            position: relative;
            font-size: 0px;
            line-height: 0px;
        }

        li {
            position: relative;
            display: inline-block;
            width: 342 / 75rem;
            min-height: 468 / 75rem;
            overflow: hidden;
            background:rgba(255,255,255,1);
            margin-right: 18 / 75rem;
            margin-bottom: 16 / 75rem;
            box-sizing: border-box;
            padding-bottom: 24 / 75rem;
        }

        .item-info {
            padding: 0 24 / 75rem;
        }
        .item-image {
            position: relative;
            width: 342 / 75rem;
            min-height: 410 / 75rem;
            height: 6.08rem;
            overflow: hidden;
            z-index: 1;
            .geshop-components-default-image-goods{
                min-height: 410 / 75rem;
            }
        }
        .item-promotion {
            display: block;
            height: 50 / 75rem;
            line-height: 50 / 75rem;
            font-size: 24 / 75rem;
            padding-left: 24 / 75rem;
            margin-bottom: 18 / 75rem;
            color: #fff;
            background: #333;
        }
        .item-title {
            font-size: 24 / 75rem;
            color: #333;
            line-height: 32 / 75rem;
            height: 32 / 75rem;
            overflow: hidden;
            margin-bottom: 8 / 75rem;
        }
    }

    // 各商品状态的交互
    .geshop-u000163-rg_template1_v1-body li {
        // 促销时间前
        &.is-ready {
            .item-price {
                color: #333;
                font-size: 26 / 75rem;
                line-height: 1.4em;
                overflow: hidden;
                span {
                    font-size: 32 / 75rem;
                }
            }
        }
        // 正在促销中
        &.is-now {
            .item-price {
                color: #999999;
                font-size: 24 / 75rem;
                height: 33 / 75rem;
                line-height: 33 / 75rem;
                margin-top: 8 / 75rem;
                text-decoration: line-through;
            }
        }
    }
</style>
<style>
    .geshop-u000163-rg_template1_v1-body .item-image img{
        width: auto;
        height: auto;
        max-height: 100%;
        max-width: 100%;
        margin: auto;
    }
</style>
