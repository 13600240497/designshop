<template>
    <div :class="['geshop_U000031_rg_red_man_v1_wrapper','geshop-component-body']"
         v-if="hasSyncData && list.length > 0 || $root.is_edit_env == true"
         ref="component">
        <ul class="goods_list_wrap">
            <li v-for="(item,index) in (list)" :key="itemKeyCompatible(item.goods_sn,index)" class="list_item">
                <div class="item_image item-img" v-show="!!!item.redman_replacement">
                    <geshop-analytics-href
                        v-if="item.goods_number > 0"
                        :href="data.navList[index].catLink"
                        :item="item"
                        :close_deeplink="redman_close_deeplink">
                        <geshop-image-goods :src="data.navList && data.navList[index].image_url"></geshop-image-goods>
                    </geshop-analytics-href>
                    <geshop-image-goods v-else
                                        :src="data.navList && data.navList[index].image_url"></geshop-image-goods>
                </div>
                <div class="item_image item-img" v-show="item.redman_replacement">
                    <!-- 折扣标 第一张自定义图不显示discount -->
                    <geshop-discount
                        :value="typeof item.discount != 'undefined' ? item.discount: 0"></geshop-discount>
                    <!-- 默认显示红人自定义链接 -->
                    <geshop-analytics-href
                        v-if="item.goods_number > 0"
                        :item="item">
                        <geshop-image-goods :src="item.goods_img" :lazyload="false"></geshop-image-goods>
                    </geshop-analytics-href>

                    <geshop-image-goods v-else :src="item.goods_img" :lazyload="false"></geshop-image-goods>

                    <!--sold out-->
                    <geshop-soldout class="item_soldout"
                                    :visible="Number(item.goods_number) <= 0"></geshop-soldout>
                    <!--商品营销信息-->
                    <!--<div class="item_marketing_info"
                         v-if="showMarketing == 1 && item.promotions && item.promotions.length > 0">
                        <span v-html="item.promotions[item.promotions.length -1 ]"></span>
                    </div>-->
                </div>
                <!-- 红人相关 m端显示4个商品 第一个商品使用自定义图图，并使用第二个商品信息-->
                <template>
                    <ul class="redman_list">
                        <li class="active" @click="handleEnterGoods($event,index,0,'red_special')">
                            <geshop-image-goods
                                :src="data.navList && data.navList[index].image_url"></geshop-image-goods>
                        </li>
                        <li v-for="(aItem,aIndex) in (goodsArray[index] && goodsArray[index].goodsInfo) || [{},{},{},{}]"
                            v-if="aIndex <= 2" @click="handleEnterGoods($event,index,aIndex)">
                            <geshop-image-goods :src="aItem.goods_img"></geshop-image-goods>
                        </li>
                    </ul>
                </template>
                <div class="item_content">
                    <!--sku标题-->
                    <div class="item_title">
                        <geshop-analytics-href
                                :item="item">
                            <geshop-goods-title>{{item.goods_title}}</geshop-goods-title>
                        </geshop-analytics-href>
                    </div>
                    <div class="item_price rate-box">
                        <!--销售价-->
                        <div class="item_shop">
                            <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                        </div>
                        <!--市场价-->
                        <div class="item_market">
                            <geshop-market-price
                                v-if="Number(item.shop_price) < Number(item.market_price) || editStatus"
                                :value="item.market_price"></geshop-market-price>
                        </div>
                        <!-- 购物车 -->
                        <geshop-analytics-href
                            v-if="client == 'app'"
                            :item="item"
                             class="shop-fast">
                        </geshop-analytics-href>
                        <a href="javascript:void (0)"
                           class="shop-fast js_fast_buy"
                           v-else
                           :data-href="'/m-goods_fast-a-ajax_goods-id-' + item.goods_id">
                        </a>
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
            client: GESHOP_PLATFORM || 'wap',
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
            redman_close_deeplink: true, // 红人图关闭deeplink转换
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

                    this.list.push(obj);
                });
            } else {
                this.list = this.editList;
            }
        },
        /**
         * 获取小图商品信息
         * @param index 商品tab序号
         * @param aIndex 子商品序号
         */
        handleEnterGoods (event, index, aIndex, type) {
            event.preventDefault();
            event.stopPropagation();
            $(event.currentTarget).siblings().removeClass('active').end().addClass('active');
            if (!!!this.list[index] || !!!this.goodsArray[index]) {
                return false;
            }
            const obj = JSON.parse(JSON.stringify(this.goodsArray[index]['goodsInfo'][aIndex]));
            // 增加默认图片替换标识redman_replacement 0 使用自定义图 1 使用商品图
            obj.redman_replacement = type && type === 'red_special' ? 0 : 1;
            this.$set(this.list, index, obj);
            this.$store.dispatch('global/async_goods_init', this);
            if (this.isIE) {
                this.$nextTick(() => {
                    const realyIndex = type && type === 'red_special' ? aIndex : parseInt(aIndex) + 1;
                    const target = $(`#U000031_${this.pid}`).find('.list_item:eq(' + index + ') .redman_list li:eq(' + realyIndex + ')');
                    $(target).siblings().removeClass('active').end().addClass('active');
                });
            }
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
    .geshop_U000031_rg_red_man_v1_wrapper {
        .geshop-components-default-image-goods {
            max-width: 100%;

            span {
                max-width: 100%;
            }
        }

        // 红人小图移动端
        .redman_list {
            .geshop-components-default-image-goods {
                pointer-events: none;
            }

            img {
                width: auto;
                max-height: 100%;
                max-width: 100%;
                flex-shrink: 0;
            }
        }

        .redman_list .geshop-components-default-image-goods span {
            max-height: 100%;
            margin: auto;
            height: 62/75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        // 红人主图片适配
        .item_image .geshop-components-default-image-goods {
            /*display: block;*/

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
