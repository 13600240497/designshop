<template>
    <div class="geshop-U000113-default-v2-body" :class="{ 'is-whole': is_whole }" :style="style_body">
        <ul :style="style_ul">

            <li
                v-for="(item, index) in list"
                :key="item.goods_sn"
                :style="style_item">

                <geshop-analytics-href
                    :href="item.url_title"
                    :sku="item.goods_sn"
                    :goods_id="item.goods_id"
                    :cate="item.cateid"
                    :warehouse="item.warehousecode"
                    :index="index"
                    :disabled="item.activity_number_left <= 0">

                    <div class="item-image">
                        <div class="item-tag"
                            v-if="icons[index]">
                            <img :src="icons[index]">
                        </div>
                        <geshop-image-goods
                            :src="item.goods_img">
                        </geshop-image-goods>
                    </div>

                    <div class="item-price">
                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                    </div>

                    <!-- 3个榜单类型返回的文案都不同 -->
                    <div class="item-info" :style="style_info" v-html="label(item)"></div>

                </geshop-analytics-href>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    data () {
        return {
            icons: [
                'https://geshopimg.logsss.com/uploads/z5ZqQuGj3voW89UsYMalpkE0f7LemcRb.png',
                'https://geshopimg.logsss.com/uploads/xl41J7BzMFRVHvWwgqcdsjhyuoIUiP6Z.png',
                'https://geshopimg.logsss.com/uploads/UcvodEBAXalT3knJKPW9SmZuh1IHLypN.png'
            ],
            list: [{}, {}, {}],
            type: this.$root.data.goodsDataSource || 1,
            cateid: this.$root.data.cateId || ''
        };
    },
    computed: {
        is_whole () {
            return this.$root.data.box_is_whole == null ? true : this.$root.data.box_is_whole == 1;
        },
        style_body () {
            return {
                'background-color': this.$root.data.box_bg_color || '#f2f2f2',
                'margin-bottom': this.$px2rem(this.$root.data.box_margin_bottom || 40)
            };
        },
        style_ul () {
            return {
                'border-radius': this.$px2rem(this.$root.data.item_radius || 12)
            };
        },
        style_item () {
            return {
                'border-radius': this.$px2rem(this.$root.data.item_radius || 12)
            };
        },
        style_info () {
            return {
                'background-color': this.$root.data.btnBgColor || '#FFEECC',
                'color': this.$root.data.leaderBoardTextColor || '#FFA800'
            };
        }
    },
    methods: {
        async get_data () {
            const params = {
                type: this.type,
                cateid: this.cateid,
                pageno: 1,
                pagesize: this.$root.data.goodsQuantity || 3
            };
            try {
                const res = await this.$jsonp(GESHOP_INTERFACE.getrankdetail.url, params);
                if (res.data.goodsInfo.length > 0) {
                    this.list = res.data.goodsInfo || [];
                }
                // 页面元素初始化
                this.$store.dispatch('global/async_goods_init', this);
            } catch (err) {}
        },
        label (product) {
            // 根据轮播排行榜单类型，获取对应的语言key键值对
            let label = '';
            switch (this.type) {
            case 1:
                label = this.$root.languages['new_rank_label'];
                return label.replace('XX', product.sale_number || 0).replace('XX', product.sale_days || 0);
            case 2:
                label = this.$root.languages['hotsale_rank_label'];
                return label.replace('XX', product.sale_number || 0);
            case 3:
                label = this.$root.languages['discount_rank_label'];
                return label.replace('XX', product.discount || 0);
            }
        }
    },
    created () {
        this.icons[0] = this.$root.data.iconFirstImg || this.icons[0];
        this.icons[1] = this.$root.data.iconSecondImg || this.icons[1];
        this.icons[2] = this.$root.data.iconThirdImg || this.icons[2];
    },
    mounted () {
        this.get_data();
    }
};
</script>

<style lang="less" sopced>
    /*
        Warning:
            组件区分多种样式
            is-whole [单个 ／ 整体]
            is-rg [是否rg站点]
    */

    // 单个 START
    .geshop-U000113-default-v2-body {
        background-color: #f2f2f2;
        ul {
            padding-left: 24 / 75rem;
            padding-top: 24 / 75rem;
            padding-bottom: (24 - 6) / 75rem;
            display: flex;
            flex-wrap: wrap;
            overflow: hidden;
        }
        li {
            position: relative;
            flex-shrink: 0;
            width: 230 / 75rem;
            margin-right: 6 / 75rem;
            margin-bottom: 6 / 75rem;
            text-align: center;
            padding-bottom: 24 / 75rem;
            background-color: #fff;
            overflow: hidden;
        }
        .item-image {
            position: relative;
            margin-bottom: 18 / 75rem;
            height: 307 / 75rem;
        }
        .item-price {
            font-size: 36 / 75rem;
            margin-bottom: 8 / 75rem;
            font-weight: bold;
        }
        .item-info {
            display: inline-block;
            position: relative;
            width: 182 / 75rem;
            padding: 8 / 75rem 19 / 75rem;
            background: #FFEECC;
            color: #FFA800;
            line-height: 28 / 75rem;
            text-align: center;
            border-radius: 99999px;
            box-sizing: border-box;
            font-size: 22 / 75rem;
        }
        .item-tag {
            position: absolute;
            left: 12 / 75rem;
            top: 0px;
            width:  52 / 75rem;
            img {
                display: block;
                width: 100%;
            }
        }
    } // 单个 END

    // 整个 START
    .geshop-U000113-default-v2-body.is-whole  {
        padding: 24 / 75rem;
        box-sizing: border-box;
        ul {
            background: #fff;
            padding-bottom: 0px;
        }
        li {
            width: 210 / 75rem;
            margin-right: 8 / 75rem;
        }
        .item-image {
            height: 280 / 75rem;
        }
        .item-info {
            width: 178 / 75rem;
        }
    }
</style>
