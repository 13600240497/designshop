<template>
    <div class="component-body geshop-U000178-rg_template_v1-body"
         :class="{ 'geshop-hidden-box': noPreview,'is-whole': is_whole }"
         ref="componentWrap">
        <div class="list-wrap" ref="swipers">
            <!-- 商品列表数据 -->
            <ul class="goods-list-wrap">
                <!-- 商品轮播列表 -->
                <li class="list-item"
                    v-for="(item, index) in (list && list.length > 0 ? list : 3)"
                    :key="index">
                    <div class="item-img">
                        <!-- 商品图片 -->
                        <geshop-analytics-href
                            :href="item.url_title"
                            :sku="item.goods_sn"
                            :cate="item.cateid"
                            :warehouse="item.warehousecode"
                            :goods_id="item.goods_id">
                            <geshop-image-goods
                                :src="item.goods_img"
                                :sku="item.goods_sn"
                                :lazyload="lazyLoad"
                                :swiperLazy="lazyLoad"
                                :index="index">
                            </geshop-image-goods>
                        </geshop-analytics-href>
                    </div>
                    <div class="item-info-box">
                        <div class="rate-box">
                            <!-- 商品标题 -->
                            <!--<geshop-analytics-href
                                v-if="(data.title_is_show || 1) == 1"
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id" class="item-title rg-ellipsis-1">
                                <geshop-goods-title>{{ list && list.length >0 ? item.goods_title :
                                    defaultValue.goods_title }}
                                </geshop-goods-title>
                            </geshop-analytics-href>-->
                            <!--销售价-->
                            <div class="item_shop">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                            </div>
                            <!--市场价-->
                            <!--<div class="item_market">
                                <geshop-market-price :value="item.market_price"
                                                     :class="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) ? '':'visible-hidden'"></geshop-market-price>
                            </div>-->
                            <p class="item_sell_point">
                                {{ rankSold(item.sale_number,item.discount) }}
                            </p>
                        </div>

                    </div>
                    <span class="ranking-icon" :style="rankIcon(index)">{{index>2 ? index+1 : ''}}</span>
                </li>

            </ul>
        </div>
    </div>
</template>

<script>
import './index.less';

export default {
    props: ['data', 'pid'],
    data () {
        return {
            lang: '', // 当前语言
            sku: '', // 当前sku
            list: [],
            defaultValue: {
                view: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/view.png',
                goods_title: 'Plus Size Color Block Flare Tankini Set …',
                iconFirstImg: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingFirst.png',
                iconSecondImg: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingSecond.png',
                iconThirdImg: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingThird.png',
                iconOtherImg: 'https://geshopcss.logsss.com/imagecache/geshop-test/resources/images/default/rankingOther.png'
            },
            lazyLoad: true,
            noPreview: false, // 是否隐藏组件
            dataParam: {} // 商品信息
        };
    },
    computed: {
        is_whole () {
            return this.$root.data.box_is_whole == null ? true : this.$root.data.box_is_whole == 1;
        }
    },
    mounted () {
        this.dataParam = this.$root.data;
        this.getGoodsList();
    },
    methods: {
        /**
         * 商品信息加载完成
         */
        goodsMounted () {
            // 去除loading骨架图
            this.$store.dispatch('global/loaded', this);
            // 商品信息格式化
            this.$store.dispatch('global/async_goods_init', this);
        },
        /**
         * 获取商品列表信息
         * @returns {Promise<void>}
         */
        async getGoodsList () {
            const $dataParam = this.dataParam;
            const rankdetailObj = window.GESHOP_INTERFACE ? window.GESHOP_INTERFACE.getrankdetail : {};
            const url = rankdetailObj.url;
            const type = $dataParam.goodsDataSource;
            const lang = typeof window.GESHOP_LANG !== 'undefined' ? window.GESHOP_LANG : 'en';
            const cateId = $dataParam.cateId || 0;
            const data = {
                type: type,
                pageno: 1,
                pagesize: $dataParam.goods_quantity || 3,
                cateid: cateId,
                lang: lang,
                pipeline: (typeof window.GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '')
            };
            try {
                const res = await this.$jsonp(url, data, { cache: true });
                if (res.code === 0 && res.data && res.data.goodsInfo && res.data.goodsInfo.length > 0) {
                    this.list = res.data.goodsInfo;
                }
                this.goodsMounted();
                this.initSwiper();
            } catch (err) {
                this.goodsMounted();
            }
        },
        /**
         * 获取商品榜单icon
         * @param index
         * @returns {{backgroundImage: *}}
         */
        rankIcon (index) {
            let rankName = 'iconOtherImg';
            switch (index) {
            case 0:
                rankName = 'iconFirstImg';
                break;
            case 1 :
                rankName = 'iconSecondImg';
                break;
            case 2:
                rankName = 'iconThirdImg';
                break;
            default:
                rankName = 'iconOtherImg';
                break;
            }
            return {
                backgroundImage: `url(${this.dataParam[rankName] || this.defaultValue[rankName]})`
            };
        },
        /**
         * 获取榜单销售折扣信息
         * @param sale_number
         * @param discount
         * @returns {string}
         */
        rankSold (sale_number = 0, discount = 0) {
            const lang = GESHOP_LANG || 'en';
            const type = Number(this.dataParam.goodsDataSource || 1);
            const rank_sold = this.$lang('rank_sold');
            const rank_off = this.$lang('rank_off');
            let result;
            if (type !== 3) {
                result = `${sale_number} ${rank_sold}`;
            } else {
                result = lang !== 'fr' ? `${discount}${rank_off}` : `-${discount}${rank_off}`;
            }
            return result;
        }
    }
};
</script>

<style lang="less" scoped>
</style>
