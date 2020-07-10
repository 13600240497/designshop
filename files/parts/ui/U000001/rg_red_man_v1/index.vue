<template>
    <div :class="['geshop_U000001_rg_red_man_v1_wrapper','geshop-component-body']"
         v-if="hasSyncData && list.length > 0 || $root.is_edit_env == true"
         ref="component">
        <ul class="goods_list_wrap">
            <li v-for="(item,index) in (list)" :key="itemKeyCompatible(item.goods_sn,index)" class="list_item"
                @mouseleave="handleLeaveGoods($event,index)">
                <div class="item_image" v-show="!!!item.redman_replacement">
                    <geshop-analytics-href
                        v-if="item.goods_number > 0"
                        :href="$root.data.navList[index].catLink"
                        :item="item"
                        :target="'_blank'">
                        <geshop-image-goods :src="data.navList && data.navList[index].image_url"></geshop-image-goods>
                    </geshop-analytics-href>
                    <geshop-image-goods v-else
                                        :src="data.navList && data.navList[index].image_url"></geshop-image-goods>
                </div>
                <div class="item_image" v-show="item.redman_replacement">
                    <!-- 折扣标 第一张自定义图不显示discount -->
                    <geshop-discount
                        :value="typeof item.discount != 'undefined' ? item.discount: 0"></geshop-discount>
                    <!-- 默认显示红人自定义链接 -->
                    <geshop-analytics-href
                        v-if="item.goods_number > 0"
                        :item="item"
                        :target="'_blank'">
                        <geshop-image-goods :src="item.goods_img" :lazyload="false"></geshop-image-goods>
                    </geshop-analytics-href>

                    <geshop-image-goods v-else :src="item.goods_img" :lazyload="false"></geshop-image-goods>

                    <!--sold out-->
                    <geshop-soldout class="item_soldout"
                                    :visible="Number(item.goods_number) <= 0"></geshop-soldout>
                    <!-- 快速购买 第一张自定义图不显示quickview -->
                    <geshop-button-quick-view class="item_quick_view site-bold-strict"
                                              :item="item"
                                              :index="index"
                                              v-if="Number(item.goods_number) > 0"
                                              :url_quick="item.url_quick">
                        <span>{{ $lang('quick_view') }}</span>
                    </geshop-button-quick-view>
                    <!--商品营销信息-->
                    <!--<div class="item_marketing_info"
                         v-if="showMarketing == 1 && item.promotions && item.promotions.length > 0">
                        <span v-html="item.promotions[item.promotions.length -1 ]"></span>
                    </div>-->
                </div>
                <!-- 红人相关 -->
                <template>
                    <ul class="redman_list">
                        <li v-for="(aItem,aIndex) in (goodsArray[index] && goodsArray[index].goodsInfo) || [{},{},{},{},{}]"
                            v-if="aIndex <= 4" @mouseenter="handleEnterGoods($event,index,aIndex)">
                            <geshop-analytics-href
                                v-if="aItem.goods_number > 0"
                                :item="aItem"
                                :target="'_blank'">
                                <geshop-image-goods :src="aItem.goods_img"></geshop-image-goods>
                            </geshop-analytics-href>
                        </li>
                    </ul>
                </template>
                <div class="item_content">
                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                                :item="item"
                            :target="'_blank'">
                            <geshop-goods-title>{{item.goods_title}}</geshop-goods-title>
                        </geshop-analytics-href>
                    </div>

                    <div class="item_price">
                        <!--销售价-->
                        <div class="item_shop inline-block">
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>
                        <!--市场价-->
                        <div class="item_market inline-block">
                            <geshop-market-price
                                v-if="Number(item.shop_price) < Number(item.market_price) || editStatus"
                                :value="item.market_price"></geshop-market-price>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import tab_refresh_sku from '../../../dataCommon/tab_refresh_sku_update_v2.js';

export default {
    name: 'rg_red_man_v1',
    props: ['data', 'pid'],
    extends: tab_refresh_sku,
    data () {
        return {
            editStatus: 1,
            showMarketing: 0, // 商品营销信息
            redmanStatus: 1, // 商品默认显示红人自定义图
            list: [], // 商品列表
            editList: [
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                },
                {
                    'goods_title': 'ZAFUL Fleece Vest And Corduroy JackJackJackJack',
                    'shop_price': '0.00',
                    'market_price': '0.00'
                }
            ], // 默认商品列表
            imgFilter: true // 懒加载过滤已加载的
        };
    },
    mounted () {
        this.$nextTick(() => {
            this.nextTickReady = true;
            this.initMounted();
        });
    },
    computed: {
        /**
         * 判断是否IE/Edge
         * @returns {*|boolean}
         */
        isIE () {
            return /(MSIE|Trident)/g.test(window.navigator.userAgent);
        }
    },
    methods: {
        // tab_refresh_sku 将重载该初始化
        initMounted () {
            this.list = [];
            // 判断同步商品数据是否存在,取每个tab第一个商品为默认商品
            if (this.goodsArray.length > 0) {
                this.goodsArray.forEach((item, index) => {
                    if (item.goodsInfo.length === 0) {
                        return false;
                    }
                    const obj = JSON.parse(JSON.stringify(item.goodsInfo[0]));
                    // if (this.data.navList[index]['image_url']) {
                    //     obj.goods_img = this.data.navList[index]['image_url'];
                    // }
                    this.list.push(obj);
                });
            } else {
                this.list = this.editList;
            }
            // // 非发布页面不执行去除loading,json数据请求完毕再执行
            // if (window.GESHOP_PAGE_TYPE && window.GESHOP_PAGE_TYPE !== '3') {
            //     this.$store.dispatch('global/loaded', this);
            // }
            // this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 获取小图商品信息
         * @param index 商品tab序号
         * @param aIndex 子商品序号
         */
        handleEnterGoods (event, index, aIndex) {
            event.preventDefault();
            event.stopPropagation();
            $(event.currentTarget).siblings().removeClass('active').end().addClass('active');
            if (!!!this.list[index] || !!!this.goodsArray[index]) {
                return false;
            }
            const obj = JSON.parse(JSON.stringify(this.goodsArray[index]['goodsInfo'][aIndex]));
            // 增加默认图片替换标识redman_replacement
            obj.redman_replacement = 1;
            this.$set(this.list, index, obj);
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 返回默认图
         */
        handleLeaveGoods (event, index) {
            event.preventDefault();
            event.stopPropagation();
            if (!(this.list[index] && this.list[index].redman_replacement)) {
                return false;
            }
            $(event.currentTarget).find('.active').removeClass('active');
            const obj = JSON.parse(JSON.stringify(this.goodsArray[index]['goodsInfo'][0]));
            // 替换原始默认图
            obj.goods_img = this.data.navList[index]['image_url'];
            this.$set(this.list, index, obj);
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 适配IE
         * @param goods_sn
         * @param index
         * @returns {string}
         */
        itemKeyCompatible (goods_sn, index) {
            return this.isIE ? goods_sn + '_' + index : index;
        }
    }
};
</script>

<style scoped lang="less">
    @import "./component.less";
</style>
<style lang="less">
    .geshop_U000001_rg_red_man_v1_wrapper {
        .redman_list img {
            width: auto;
            height: 38px;
            margin: auto;
        }

        // 红人图片适配
        .item_image .geshop-components-default-image-goods {
            display: block;

            span {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                max-height: 100%;
                height: 100%;
            }

            img {
                width: auto;
                height: auto;
                max-height: 100%;
                max-width: 100%;
                flex-shrink: 0;
            }
        }
    }
</style>
