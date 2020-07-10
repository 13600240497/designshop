<template>
   <div class="geshop-U000110-rg_template1_v1 rg-rank-container" ref="rankWrap" v-if="comb_show">
       <div class="gs-banner">
            <img :src="data.banner_img ? data.banner_img : 'https://geshopimg.logsss.com/uploads/XVbaglsB9icvKSeMTxdC2PFY4WQ8tyuA.png'" alt="">
       </div>

       <div class="sp-box">
           <div class="nav-top">
               <div class="top-swiper-wrap">
                   <div class="swiper-container top-swiper-container" ref="mySwiper">
                       <div class="swiper-wrapper">
                           <div class="swiper-slide nav-item" v-for="(item, index) in  (data.navList && data.navList[0].tab_name ?  data.navList : 8)" :key="index" :class="{on: index == 0}"><span>{{ item.tab_name || 'Swimwear' + (index + 1) }}</span></div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       <div class="goods-list">
           <ul>
               <li v-for="(item, index) in (list.length > 0 ? list : 10)" :key="item.goods_sn || index">
                   <div class="item_img">
                       <geshop-analytics-href
                               :href="item.url_title"
                               :sku="item.goods_sn"
                               :cate="item.cateid"
                               :warehouse="item.warehousecode"
                               :goods_id="item.goods_id">
                           <geshop-image-goods :src="item.goods_img"></geshop-image-goods>
                       </geshop-analytics-href>

                       <!-- 库存告急 -->
                       <geshop-stocktip class="item_stocktip" :item="item"></geshop-stocktip>

                       <!--sold out-->
                       <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>
                       <div class="promot-info" v-if="data.goods_type == 3"><span v-show="item.discount > 0">{{ item.discount || 'xxxx' }}{{ $lang('rank_off') }}</span></div>
                       <div class="promot-info" v-else><span>{{ item.sale_number || 'xxxx' }} {{ $lang('rank_sold') }}</span></div>
                   </div>
                   <div class="item_goods_info">
                       <geshop-analytics-href
                               :href="item.url_title"
                               :sku="item.goods_sn"
                               :cate="item.cateid"
                               :warehouse="item.warehousecode"
                               :goods_id="item.goods_id" class="item-title rg-ellipsis-1">
                           <geshop-goods-title>{{ item.goods_title || 'Rosegal HOT Slip Pockets Fast Rosegal HOT Slip Pockets Fast' }}</geshop-goods-title>
                       </geshop-analytics-href>
                       <div class="price-info">
                           <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                       </div>
                       <div class="price-info">
                           <geshop-market-price :value="item.market_price" v-show="Number(item.market_price || 0 ) - Number(item.shop_price || 0) >= 0"></geshop-market-price>
                       </div>
                       <geshop-analytics-href
                               v-if="client == 'app'"
                               :href="item.url_title"
                               :sku="item.goods_sn"
                               :cate="item.cateid"
                               :warehouse="item.warehousecode"
                               :goods_id="item.goods_id" class="btn-buy">
                       </geshop-analytics-href>
                       <a href="javascript:void (0)"
                          class="btn-buy js_fast_buy"
                          v-else
                          :data-href="'/m-goods_fast-a-ajax_goods-id-' + item.goods_id">
                       </a>
                   </div>
                   <span class="icon_rank" :class="'icon-'+(index + 1)" v-if="index < 3">{{index + 1}}</span>
                   <span class="icon_rank icon-other" v-else>{{index + 1}}</span>
               </li>
           </ul>
       </div>
   </div>
</template>

<script>

export default {
    name: 'rg_default_v1',
    props: ['data', 'pid'],
    data () {
        return {
            client: GESHOP_PLATFORM || 'wap',
            $boxWrap: '',
            swiper: '',
            comb_show: true,
            list: []
        };
    },
    mounted () {
        // 加载swiper
        const that = this;
        if (!$.fn.swiper3 && $LAB) {
            let staticDomain = GESHOP_STATIC;
            loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.min.css');
            $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function () {
                that.loadStart();
            });
        } else {
            that.loadStart();
        }
        /* loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {

        }); */
    },
    methods: {
        loadStart () {
            this.$nextTick(() => {
                this.$boxWrap = $(this.$refs.rankWrap);
                // 去处loading
                this.$store.dispatch('global/loaded', this);
                this.init();
            });
        },
        init () {
            this.initSwiper();
            const cateid = this.data.navList && this.data.navList[0] && this.data.navList[0].tab_id;
            this.getRankList(cateid);
            this.bindEvent();
        },
        initSwiper () {
            this.swiper = new Swiper3(this.$boxWrap.find('.swiper-container'), {
                slidesPerView: 'auto',
                slideToClickedSlide: true,
                observer: true,
                observeParents: true
            });
        },
        getRankList (cateid) {
            if (!cateid) {
                this.list = [];
                // window.GESHOP_PAGE_TYPE != 1 ? this.comb_show = false : this.comb_show = true;
                return;
            }
            const lang = typeof GESHOP_LANG != 'undefined' ? GESHOP_LANG : 'en';
            const params = {
                type: this.data.goods_type || 1,
                cateid: cateid,
                lang: lang,
                pageno: 1,
                pagesize: Math.min(this.data.tab_num, 100) || 100,
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
            };
            const url = GESHOP_INTERFACE.getrankdetail.url;
            this.$jsonp(url, params).then((res) => {
                this.list = [...res.data.goodsInfo];
                /* if (this.list.length > 1) {
                    this.comb_show = true;
                } else {
                    window.GESHOP_PAGE_TYPE != 1 ? this.comb_show = false : this.comb_show = true;
                } */
                // 商品懒加载
                this.$store.dispatch('global/async_goods_init', this);
            });
        },
        bindEvent () {
            const that = this;
            $(this.$refs.rankWrap).find('.nav-item').on('click', function () {
                if (that.$boxWrap.find('.nav-top').hasClass('fixed')) {
                    $('html,body').animate({ scrollTop: that.$boxWrap.find('.sp-box').offset().top + 1 }, 'slow');
                }
                if ($(this).hasClass('on')) {
                    return false;
                }
                let index = $(this).index();
                $(that.$refs.rankWrap).find('.nav-item').eq(index).addClass('on').siblings().removeClass('on');
                const cateid = that.data.navList && that.data.navList[index] && that.data.navList[index].tab_id;
                that.getRankList(cateid);
            });
            /* if (this.comb_show) {

            } */
            this.bindScroll();
        },
        bindScroll () {
            const _this = this;
            let timer = null;
            $(window).on('scroll', function () {
                timer = setTimeout(function () {
                    clearTimeout(timer);
                    const navTop = _this.$boxWrap.find('.sp-box').offset().top;
                    const boxTop = _this.$boxWrap.offset().top;
                    const boxH = _this.$boxWrap.outerHeight();
                    const scrollTop = $(this).scrollTop();
                    if (($('body').find('.nav-top.fixed, .js-geshop-nav-fixed')).length < 1) {
                        $('[data-key="U000030"], #topheader, .js-geshop-nav').show();
                    } else {
                        $('[data-key="U000030"], #topheader, .js-geshop-nav').hide();
                    }
                    if (navTop <= scrollTop && scrollTop < boxTop + boxH) {
                        _this.$boxWrap.find('.nav-top').addClass('fixed');
                    } else {
                        _this.$boxWrap.find('.nav-top').removeClass('fixed');
                    }
                }, 60);
            });
        }
    }
};
</script>
<style scoped lang="less">
    @import "index_v1";
</style>
