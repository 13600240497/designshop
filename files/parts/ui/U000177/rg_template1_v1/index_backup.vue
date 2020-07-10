<template>
    <div class="component-body geshop-U000177-rg_template1_v1-body" :class="{ 'geshop-hidden-box': noPreview }">
        <div class="list-wrap" ref="swipers">
            <!-- 商品列表数据 -->
            <div class="goods-list-wrap">
                <div class="leader-board-aside-link">
                    <a
                        :href="dataParam.jumpLink || 'javascript:void (0)'"
                        :target="dataParam.jumpLink ? '_blank' : '_self'"
                        :class="dataParam.jumpLink ? '' : 'no-link'">
                        <geshop-image-goods
                            :src="dataParam.leftSideImage"
                            v-if="dataParam.leftSideImage">
                        </geshop-image-goods>
                        <div :style="defaultView" class="defaultView" v-else></div>
                    </a>
                </div>
                <!-- 商品轮播列表 -->
                <div class="swiper-wraper">
                    <swiper
                        :options="dataParam.isEditEnv == 1 || !list || list.length === 0 ? swiperEdit : swiperOption"
                        ref="mySwiper"
                        v-if="swiperMounted">
                        <swiper-slide :class="['swiper-slide']"
                                      v-for="(item, index) in (list && list.length > 0 ? list : 3)"
                                      :key="item.goods_id">
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
                                <!--折扣标-->
                                <!--<geshop-discount
                                    :value="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>
                                &lt;!&ndash; sold out 售空&ndash;&gt;
                                <geshop-soldout :visible="Number(item.goods_number) <= 0"></geshop-soldout>-->
                            </div>
                            <div class="item-info-box">
                                <div class="rate-box">
                                    <!-- 商品标题 -->
                                    <geshop-analytics-href
                                        v-if="(data.title_is_show || 1) == 1"
                                        :href="item.url_title"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id" class="item-title rg-ellipsis-1">
                                        <geshop-goods-title>{{ list && list.length >0 ? item.goods_title :
                                            defaultValue.goods_title }}
                                        </geshop-goods-title>
                                    </geshop-analytics-href>
                                    <!--销售价-->
                                    <div class="item_shop">
                                        <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                    </div>
                                    <!--市场价-->
                                    <div class="item_market">
                                        <geshop-market-price :value="item.market_price"
                                                             :class="Number(item.shop_price) < Number(item.market_price) || (!item.shop_price && !item.market_price) ? '':'visible-hidden'"></geshop-market-price>
                                    </div>
                                    <p class="item_sell_point">
                                        {{ rankSold(item.sale_number,item.discount) }}
                                    </p>
                                    <!-- 购买按钮 -->
                                    <p class="buy-now text-center none">
                                        <geshop-analytics-href
                                            v-if="(data.title_is_show || 1) == 1"
                                            :href="item.url_title"
                                            :sku="item.goods_sn"
                                            :cate="item.cateid"
                                            :warehouse="item.warehousecode"
                                            :goods_id="item.goods_id" class="rg-ellipsis-1">
                                            <geshop-buynow :value="dataParam.buyText || 'SNAP UP'"></geshop-buynow>
                                        </geshop-analytics-href>
                                    </p>
                                </div>

                            </div>
                            <span class="ranking-icon" :style="rankIcon(index)">{{index>2 ? index+1 : ''}}</span>
                        </swiper-slide>
                        <div class="swiper-button-prev swiper-button-black hide" slot="button-prev"
                             v-show="list && list.length > 0"></div>
                        <div class="swiper-button-next swiper-button-black hide" slot="button-next"
                             v-show="list && list.length > 0"></div>
                    </swiper>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import './index.less';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    props: ['data', 'pid'],
    components: {
        swiper,
        swiperSlide
    },
    data () {
        return {
            $boxWrap: null, // 当前容器
            lang: '', // 当前语言
            sku: '', // 当前sku
            list: [],
            // swiper配置项
            swiperEdit: {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 16,
                simulateTouch: false
            },
            swiperOption: {
                observer: true,
                observeParents: true,
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 16,
                loop: true,
                autoplay: {
                    delay: 3000,
                    stopOnLastSlide: false,
                    disableOnInteraction: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                }, lazy: {
                    elementClass: 'js_gdexp_lazy',
                    lazy: true,
                    loadOnTransitionStart: true,
                    loadPrevNext: true
                },
                simulateTouch: false
            },
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
            swiperMounted: false,
            dataParam: {} // 商品信息
        };
    },
    computed: {
        defaultView () {
            return {
                backgroundImage: `url(${this.defaultValue.view})`
            };
        },
        swiper () {
            return this.$refs.mySwiper.swiper;
        }
    },
    mounted () {
        this.dataParam = this.$root.data;
        this.initMounted();
    },
    methods: {
        /**
         * 初始化Mounted
         */
        initMounted () {
            this.getGoodsList();
        },
        /**
         * 商品信息加载完成
         */
        goodsMounted () {
            // 去除loading骨架图
            this.$store.dispatch('global/loaded', this);
            this.$store.dispatch('global/async_goods_init', this);
            this.swiperMounted = true;
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
                lang: lang,
                pageno: 1,
                pagesize: 12,
                cateid: cateId,
                pipeline: (typeof window.GESHOP_PIPELINE !== 'undefined' ? GESHOP_PIPELINE : '')
            };
            try {
                const res = await this.$jsonp(url, data, { cache: true });
                if (res.code === 0 && res.data && res.data.goodsInfo && res.data.goodsInfo.length > 0) {
                    this.list = res.data.goodsInfo;
                }
                this.goodsMounted();
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
