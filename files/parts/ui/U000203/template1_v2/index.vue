<template>
    <div class="geshop-U000203-template1_v2 nav-container">
        <ul class="nav-list">
            <li
                v-for="(item, index) in navList"
                :class="{'active':activityTabIndex==index}"
                :key="index"
                @click="handleTabChange(index)">
                <span>{{ item.navName }}</span>
            </li>
        </ul>

        <!-- Tab contents -->
        <div
            v-for="tabContent in remoteGoodsInfoArray"
            :key="tabContent.tab_index"
            v-show="activityTabIndex==tabContent.tab_index"
            class="goods-list">
            
            <ul class="goods-list-ul">
                <template v-for="(item, index) in tabContent.goodsInfo">
                    <li :key="item.goods_sn" v-show="index < page.current * page.size">
                        <div class="goods-items">
                            <div class="goods-item-head">
                                <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    :index="index">
                                    <!-- 图片 -->
                                    <geshop-image-goods
                                        :src="item.goods_img"
                                        :sku="item.goods_sn"
                                        :index="index"
                                        :type="1">
                                    </geshop-image-goods>

                                    <!-- 购买弹出层，只有PC才有 -->
                                    <div
                                        class="shop-now-container"
                                        v-if="is_soldout(item.goods_number, item.is_on_sale) == false && media_platform == 'pc'">
                                        <div class="inner-wrapper">
                                            <i class="bag-img"></i>
                                            <span class="shop-now-text"> {{ $lang('shop_now') }} </span>
                                        </div>
                                    </div>
                                </geshop-analytics-href>

                                <!-- 售罄 -->
                                <geshop-soldout :visible="is_soldout(item.goods_number, item.is_on_sale)"></geshop-soldout>
                            </div>

                            <!-- 标题 -->
                            <geshop-analytics-href
                                class="geshop-goods-title"
                                target="_blank"
                                :item="item"
                                :index="index">
                                {{ item.goods_title }}
                            </geshop-analytics-href>

                            <p class="geshop-item-price-layer">
                                <geshop-shop-price
                                    v-if="is_app"
                                    :value="item.app_price || item.shop_price">
                                </geshop-shop-price>

                                <geshop-shop-price
                                    v-if="!is_app"
                                    :value="item.shop_price">
                                </geshop-shop-price>

                                <span class="discount" v-if="item.discount > 0">
                                    <template v-if="$root.lang=='fr'">
                                        (-{{item.discount}}%)
                                    </template>
                                    <template v-else>
                                        ({{item.discount}}%Off)
                                    </template>
                                </span>
                            </p>
                            
                            <!-- 促销信息 -->
                            <geshop-promotion-dl
                                :media_platform="media_platform"
                                :list="promotions_formate(item.promotions)" />
                        </div>
                    </li>
                </template>
            </ul>

            <a
                v-if="navList[tabContent.tab_index].more_href"
                class="btn-seemore"
                target="_blank"
                :href="navList[tabContent.tab_index].more_href">
                {{ data.seemore_btnText || 'See More>>' }}
            </a>

            <!-- viewmore -->
            <div class="item-more-wrap" v-if="page.count > 1">
                <div class="item-more-less">
                    <div class="view_more view_more_btn" @click="handleTapViewMore(tabContent.tab_index)">
                        <span v-if="page.current >= page.count">{{ $lang('view_less') }}</span>
                        <span v-else>{{ $lang('view_more') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

/** 默认的导航数据 */
const defaultNavList = [
    { catIds: '', key: '0-0', lists: '', navName: 'Tops', skuFrom: '1' },
    { catIds: '', key: '0-0', lists: '', navName: 'Dress', skuFrom: '1' },
    { catIds: '', key: '0-0', lists: '', navName: 'Bottoms', skuFrom: '1' },
    { catIds: '', key: '0-0', lists: '', navName: 'Swimwear', skuFrom: '1' }
];

/** 默认的商品数据 */
const defaultGoodsTb = [
    {
        tab_index: 0,
        goodsInfo: [
            { shop_price: 9.99, market_price: 19.99, goods_title: 'Plus Size Mesh Panel Snowfla…', discount: 20, promotions: { 0: 'Buy 1 Get 10% OFF', 1: 'Buy 1 Get 10% OFF' } },
            { shop_price: 9.99, market_price: 19.99, goods_title: 'Plus Size Mesh Panel Snowfla…', discount: 20, promotions: { 0: 'Buy 1 Get 10% OFF', 1: 'Buy 1 Get 10% OFF' } },
            { shop_price: 9.99, market_price: 19.99, goods_title: 'Plus Size Mesh Panel Snowfla…', discount: 20, promotions: { 0: 'Buy 1 Get 10% OFF', 1: 'Buy 1 Get 10% OFF' } },
            { shop_price: 9.99, market_price: 19.99, goods_title: 'Plus Size Mesh Panel Snowfla…', discount: 20, promotions: { 0: 'Buy 1 Get 10% OFF', 1: 'Buy 1 Get 10% OFF' } }
        ]
    }
];

export default {
    props: ['pid', 'data'],

    data () {
        return {
            activityTabIndex: 0, // 当前展示的TAB索引
            // 页码
            page: {
                current: 1,
                size: 0,
                total: 0,
                count: 0
            },
            is_app: false
        };
    },

    computed: {
        // pc/wap/pad
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        /**
         * 导航数据
         */
        navList () {
            if (this.data.navList && this.data.navList.length > 0) {
                return this.data.navList;
            } else {
                return defaultNavList;
            }
        },
        /**
         * 远端数据是否已经加载完毕
         */
        isDateRes () {
            return this.$store.state.global.isDateRes;
        },
        /**
         * 远端数据
         */
        remoteGoodsInfoArray () {
            return this.$store.state.global.goodsInfo[this.pid] || defaultGoodsTb;
        }
    },

    watch: {
        /**
         * 当远端数据准备好之后
         */
        isDateRes (val) {
            val == true && this.init();
        },
        /**
         * 响应式变更
         */
        media_platform (val) {
            val == 'wap' && this.reset_pager();
        }
    },

    methods: {
        /**
         * 组件初始化初始化
         */
        init () {
            // 重置分页
            this.reset_pager();
            // 页面元素初始化
            this.$store.dispatch('global/loaded', this);
            this.$store.dispatch('global/async_goods_init', this);
        },

        /**
         * 初始化页码
         * @param {Number} index tab索引
         */
        reset_pager (index = this.activityTabIndex) {
            const goodsLen = this.remoteGoodsInfoArray[index].goodsInfo.length;
            this.page.size = this.media_platform == 'wap' ? (this.data.page_show_goods_number || 20) : goodsLen;
            this.page.count = Math.ceil(goodsLen / this.page.size);
            this.page.total = goodsLen;
            this.page.current = 1;
        },

        /**
         * Tab切换
         * @param {Number} index tab索引
         */
        handleTabChange (index = 0) {
            this.activityTabIndex = index;
            this.reset_pager();
        },

        /**
         * 检查是否已经售罄
         * @param {Number} goods_number
         * @param {Nmumber} is_on_sale
         */
        is_soldout (goods_number, is_on_sale) {
            return (parseInt(goods_number) <= 0 || parseInt(is_on_sale) <= 0);
        },

        /**
         * 点击viewmore
         * @param {Number} index 当前tab的索引
         */
        handleTapViewMore (index) {
            if (this.page.current >= this.page.count) {
                this.page.current = 1;
            } else {
                this.page.current += 1;
            }
        },

        /**
         * 营销信息处理
         * Convert obejct to array
         * @param {Object|Array} data 营销信息数据
         */
        promotions_formate (data) {
            let arr = [];
            if (typeof data == 'object' && Array.isArray(data) === false) {
                arr = Object.keys(data).map(key => {
                    let label = data[key];
                    label = label.replace(/&lt;/g, '<');
                    label = label.replace(/&gt;/g, '>');
                    label = label.replace(/&quot;/g, `"`);
                    return label;
                });
            }
            return arr;
        }
    },

    created () {
        // 判断是否APP，如果是APP，则取价格 app_price 字段
        this.is_app = window.GESHOP_PLATFORM == 'app';
    },

    mounted () {
        this.isDateRes == true && this.init();
    }
};
</script>

<style lang="less">

.geshop-U000203-template1_v2 {
    .nav-container {
        overflow: hidden;
    }

    .nav-list {
        max-width: 1200px;
        text-align: center;
        width: 100%;
        margin: 30px auto 0px;

        li {
            text-align: center;
            margin-right: 16px;
            margin-bottom: 16px;
            display: inline-block;
            cursor: pointer;
            overflow: hidden;
            background-size: 100% 100%;
            box-sizing: border-box;
            border-style: solid;
            border-width: 2px;
            vertical-align: middle;

            span {
                display: inline-block;
                overflow: hidden;
                width: 88%;
                * {
                    color:inherit;
                    pointer-events: none;
                }
            }

            &:last-child {
                margin-right: 0;
            }
        }
    }

    .goods-list {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px 24px;
        overflow: hidden;
        box-sizing: border-box;

        ul.goods-list-ul {
            overflow: hidden;
            box-sizing: border-box;

            > li {
                float: left;
                width: 25%;
                box-sizing: border-box;
                position: relative;
            }

            > li:hover {
                .shop-now-container {
                    display: block;
                }
            }

            .goods-items {
                position: relative;
                background-color: #fff;
            }

            .goods-item-head {
                position: relative;
                width: 100%;
            }

            .geshop-goods-title {
                display: block;
                margin-top: 13px;
                font-size: 14px;
                color: #000;
                text-overflow: ellipsis;
                overflow: hidden;
                line-height: 20px;
                height: 20px;
                width: 90%;
                padding-left: 12px;
                white-space: nowrap;
            }

            .geshop-item-price-layer {
                margin-top: 4px;
                padding-left: 12px;
                overflow: hidden;
                .discount {
                    font-size: 14px;
                }
            }
        }
    }

    .btn-seemore {
        display: block;
        margin: 4px auto 0;
        border-style: solid;
        border-width: 1px;
        border-radius: 22px;
        text-align: center;
        box-sizing: border-box;
    }
    .view_more {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        width: 165px;
        height: 36px;
        margin: auto;
        border-radius: 0.05333333rem;
        text-align: center;
        span{
            font-size: 14px;
            font-weight: 600;
            line-height: 17px;
            color: inherit;
        }
    }

    // 购买遮罩
    .shop-now-container {
        display: none;
        position: absolute;
        top: 0px;
        left: 0px;
        right: 0px;
        width: 100%;
        padding-top: 100%;
        opacity: 0.75;
        border-style: solid;
        border-width: 2px;
        box-sizing: border-box;

        .inner-wrapper {
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            margin-top: -10px;
            text-align: center
        }

        .bag-img {
            background-image: url('https://geshoptest.s3.amazonaws.com/uploads/OmoKtEqZh8027cfkU56VTJaYi1WSIXRQ.png');
            font-size: 25px;
            width: 22px;
            height: 22px;
            background-size: 100% 100%;
            display: inline-block;
        }

        .shop-now-text {
            display: block;
        }
    }
}

@media screen and (min-width: 1025px) {
    .geshop-U000203-template1_v2 {
        .geshop-goods-item-price {
            height: 27px;
            line-height: 27px;
        }

        ul.goods-list-ul {
            margin-top: 4px;
            box-sizing: border-box;

            > li {
                padding: 0 8px 16px;
            }
        }

        .geshop-goods-promotions-more {
            left: 12px !important;
        }
    }
}

@media screen and (max-width: 1024px) and (min-width: 768px) {
    .geshop-U000203-template1_v2 {

        ul.goods-list-ul {

            box-sizing: border-box;

            > li {
                float: left;
                width: 25%;
                box-sizing: border-box;
                padding: 0 6px 16px;
            }

            .goods-items {
                min-height: 100%;
                position: relative;
                width: 100%;
                background: #fff;
                overflow: hidden;
            }

            .geshop-goods-img {
                position: relative;
                width: 100%;
                overflow: hidden;
            }
            .discount {
                font-size: 14px;
            }
        }
    }
}

@media screen and (max-width: 767px) and (min-width: 374px) {
    .geshop-U000203-template1_v2 {

        .nav-container {
            padding: 0 5px;
        }

        .goods-list {
            padding: 0;
        }

        .nav-list {
            li {
                margin-bottom: 10px !important;
                margin-left: 4px;
                margin-right: 4px;
                &:last-child{
                    margin-right: 4px;
                }
            }
        }

        ul.goods-list-ul {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            box-sizing: border-box;

            > li {
                flex-shrink: 0;
                width: 50% !important;
                padding-left: 5px;
                padding-right: 5px;
                padding-bottom: 10px;
                box-sizing: border-box;
            }

            .goods-items {
                min-height: 100%;
                position: relative;
                width: 100%;
                background: #fff;
                overflow: hidden;
            }

            .geshop-goods-img {
                position: relative;
                width: 100%;
                overflow: hidden;
            }

            .geshop-goods-item-price {
                line-height: 22px;
                height: 22px;
            }

            .discount {
                font-size: 13px;
            }
        }
    }
}

@media screen and (max-width: 374px) {
    .geshop-U000203-template1_v2 {

        .nav-container {
            padding: 0 5px;
        }

        .goods-list {
            padding: 0;
        }

        .nav-list {
            li {
                margin-right: 0;
                margin-bottom: 10px !important;
            }
        }

        ul.goods-list-ul {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            box-sizing: border-box;

            > li {
                flex-shrink: 0;
                width: 50% !important;
                padding-left: 5px;
                padding-right: 5px;
                padding-bottom: 10px;
                box-sizing: border-box;
            }

            .goods-items {
                min-height: 100%;
                position: relative;
                width: 100%;
                background: #fff;
                overflow: hidden;
            }

            .geshop-goods-img {
                position: relative;
                width: 100%;
                overflow: hidden;
            }

            .geshop-goods-promotions {
                padding-bottom: 10px !important;
                font-size: 13px;

                text-overflow: ellipsis;
                overflow: hidden;
                width: 90%;
                white-space: nowrap;

            }

            .discount {
                font-size: 13px;
            }
        }
    }
}

// hack for android 360 with
@media screen and (min-width: 360px) and (max-width: 374px) {
    .geshop-U000203-template1_v2 {
        .nav-list{
            li {
                width: 165px !important;
                margin-right: 11px !important;
                margin-bottom: 10px !important;

                &:nth-child(2n) {
                    margin-right: 0 !important;
                }
            }
        }
    }
}

// PAD
@media screen and (min-width: 768px){
    .geshop-U000203-template1_v2 {
        .item-more-wrap{
            display:none;
        }
    }
}

// M
@media screen and (max-width: 767px) and (min-width: 0px){
    .geshop-U000203-template1_v2 {
        .btn-seemore {
            display:none;
        }
        .item-more-less {
            padding-bottom:10px ;
        }
    }
}

</style>
