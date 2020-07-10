<template>
    <div
        class="geshop-u000081-default-v2-body"
        :style="style_body"
        :class="{ 'is-whole': whole }"
        v-if="list.length > 0">
        <ul :style="style_item">
            <li v-for="(item, index) in list" :key="item.goods_sn" :style="style_item">
                <div class="item-image">
                    <geshop-discount :value="item.discount" :visible="parseInt(item.discount) > 0"></geshop-discount>
                    <geshop-analytics-href
                        :disabled="item.goods_number <= 0"
                        :href="item.url_title"
                        :sku="item.goods_sn"
                        :cate="item.cateid"
                        :warehouse="item.warehousecode"
                        :goods_id="item.goods_id">
                        <geshop-image-goods :src="item.goods_img" :sku="item.goods_sn" :index="index"></geshop-image-goods>
                    </geshop-analytics-href>
                    <geshop-soldout :visible="item.goods_number <= 0"></geshop-soldout>
                </div>
                <div class="item-info">
                    <div class="item-title">
                        {{ item.goods_title || 'Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}
                    </div>
                    <div class="item-price">
                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        <geshop-market-price :value="item.market_price"></geshop-market-price>
                    </div>
                    <div class="item-button">
                        <geshop-analytics-href
                            :disabled="item.goods_number <= 0"
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="item.goods_id">
                            <geshop-buynow :href="item.url_title"></geshop-buynow>
                        </geshop-analytics-href>
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
            list: [{}, {}, {}, {}]
        };
    },
    computed: {
        whole () {
            return this.$root.data.box_is_whole == 1;
        },
        style_body () {
            return {
                'margin-bottom': this.px2rem(this.$root.data.box_margin_bottom || 40),
                'background-color': this.$root.data.box_bg_color
            };
        },
        style_item () {
            return {
                'border-radius': this.px2rem(this.$root.data.item_radius || 12)
            };
        }
    },
    methods: {
        px2rem (val) {
            return (val / 75) + 'rem';
        },
        async get_list () {
            const params = {
                goodsSn: this.$root.data.goodsSKU
            };
            try {
                const res = await this.$jsonp(GESHOP_INTERFACE.goods_async_detail.url, params);
                this.list = res.data.goodsInfo || [];
            } catch (err) {}
        }
    },
    async created () {
        await this.get_list();
        if (this.$root.is_edit_env && this.list.length == 0) {
            this.list = [{}, {}, {}, {}];
        }
        // 页面元素初始化
        this.$store.dispatch('global/async_goods_init', this);
        this.$store.dispatch('global/loaded', this);
    }
};
</script>

<style lang="less" scoped>

.geshop-u000081-default-v2-body {
    background: #f2f2f2;
    ul {
        display: flex;
        flex-wrap: wrap;
        width: 750 / 75rem;
        padding-top: 24 / 75rem;
        padding-left: 15 / 75rem;
    }

    li {
        width: 342 / 75rem;
        background:rgba(255,255,255,1);
        border-radius: 12 / 75rem;
        flex-shrink: 0;
        margin: 0 9 / 75rem;
        margin-bottom: 18 / 75rem;
        padding-bottom: 24 / 75rem;
        overflow: hidden;
    }

    .item-info {
        padding: 0 24 / 75rem;
    }
    .item-image {
        position: relative;
        width: 100%;
        padding-top: 133%;
    }
    .item-title {
        font-size: 22 / 75rem;
        color: #333;
        line-height: 30 / 75rem;
        height: 60 / 75rem;
        overflow: hidden;
        margin-top: 18 / 75rem;
        margin-bottom: 8 / 75rem;
    }
    .item-price {
        margin-top: 12 / 75rem;
        line-height: 1.2em;
    }
    .geshop-zaful-image-goods {
        position: absolute;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
    }
    .item-button {
        margin-top: 24 / 75rem;
        width: 294 / 75rem;
    }
}

.geshop-u000081-default-v2-body.is-whole {
    padding: 24 / 75rem;
    ul {
        width: 100%;
        background: #fff;
        padding: 15 / 75rem;
        padding-right: 0px;
        padding-bottom: 0px;
        box-sizing: border-box;
    }
    li {
        width: 318 / 75rem;
        margin: 0 9 / 75rem;
        margin-top: 9 / 75rem;
    }
    .item-info {
        padding: 0px;
    }
}
</style>
