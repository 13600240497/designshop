<template>
   <div class="geshop-U000109-rg_template1_v1 rg-rank-container" ref="rankWrap">
       <div class="gs-banner" style="min-height: 200px">
            <img :src="data.banner_img ? data.banner_img : 'https://geshoptest.s3.amazonaws.com/uploads/6j1XDu9l8WVUyZkFNivzd0CwsLhfOR2r.png'" alt="">
       </div>

       <div class="sp-box">
           <div class="nav-top">
               <div class="top-swiper-wrap">
                   <div class="swiper-container top-swiper-container" ref="mySwiper">
                       <div class="swiper-wrapper">
                           <div class="swiper-slide nav-item" v-for="(item, index) in  (data.navList && data.navList[0].tab_name ?  data.navList : 8)" :key="index" :class="{on: index == 0}"><span>{{ item.tab_name || 'Swimwear' + (index + 1) }}</span></div>
                       </div>
                   </div>
                   <div class="swiper-button-next"><span></span></div>
                   <div class="swiper-button-prev"><span></span></div>
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
                       <geshop-stocktip class="item_stocktip" :item="typeof item == 'object' ? item : {}"></geshop-stocktip>
                       <!--sold out-->
                       <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>
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
                           <geshop-market-price :value="item.market_price" v-show="Number(item.market_price || 0 ) - Number(item.shop_price || 0) >= 0"></geshop-market-price>
                       </div>
                       <div class="promot-info" v-if="data.goods_type == 3"><span v-show="item.discount > 0">{{ item.discount || 'xxxx' }}{{ $lang('rank_off') }}</span></div>
                       <div class="promot-info" v-else><span>{{ item.sale_number || 'xxxx' }} {{ $lang('rank_sold') }}</span></div>
                       <geshop-analytics-href
                               :href="item.url_title"
                               :sku="item.goods_sn"
                               :cate="item.cateid"
                               :warehouse="item.warehousecode"
                               :goods_id="item.goods_id"
                               class="btn-buy"
                       >
                           <span>{{data.buy_text || 'SNAP UP'}}</span>
                       </geshop-analytics-href>
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
    name: 'rg_default_v3',
    props: ['data', 'pid'],
    data () {
        return {
            $boxWrap: '',
            swiper: '',
            comb_show: true,
            list: []
        };
    },
    mounted () {
        // 加载swiper
        loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {
            this.$nextTick(() => {
                this.$boxWrap = $(this.$refs.rankWrap);
                // 去处loading
                this.$store.dispatch('global/loaded', this);
                this.init();
            });
        });
    },
    methods: {
        init () {
            this.initSwiper();
            const cateid = this.data.navList && this.data.navList[0] && this.data.navList[0].tab_id;
            this.getRankList(cateid);
            this.bindEvent();
        },
        initSwiper () {
            this.swiper = new Swiper(this.$boxWrap.find('.swiper-container'), {
                slidesPerView: 'auto',
                slideToClickedSlide: true,
                observer: true,
                observeParents: true,
                navigation: {
                    nextEl: this.$boxWrap.find('.swiper-button-next'),
                    prevEl: this.$boxWrap.find('.swiper-button-prev')
                }
            });
            if (this.swiper.virtualSize <= 1064) {
                this.$boxWrap.find('.swiper-button-next, .swiper-button-prev').addClass('off');
            }
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
                let index = $(this).index();
                $(that.$refs.rankWrap).find('.nav-item').eq(index).addClass('on').siblings().removeClass('on');
                const cateid = that.data.navList && that.data.navList[index] && that.data.navList[index].tab_id;
                that.getRankList(cateid);
            });
            /* if (this.comb_show) {

            } */
            if (this.data.is_fixed == 1) {
                this.bindScroll();
            }
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
                        $('[data-key="U000027"], #js_nav, .js-geshop-nav').show();
                    } else {
                        $('[data-key="U000027"], #js_nav, .js-geshop-nav').hide();
                    }
                    if (navTop <= scrollTop && scrollTop < boxTop + boxH) {
                        _this.$boxWrap.find('.nav-top').addClass('fixed');
                        // _this.$boxWrap.find('.sp-box').css('background-color', 'transparent');
                    } else {
                        _this.$boxWrap.find('.nav-top').removeClass('fixed');
                        // _this.$boxWrap.find('.sp-box').css('background-color', this.data.tb_bg_color || '#D8D8D8');
                    }
                }, 10);
            });
        }
    }
};
</script>
<style lang="less">
    .geshop-U000109-rg_template1_v1 {
        .geshop-market-price {
            vertical-align: middle;
            margin-left: 8px;
        }
        .swiper-button-next, .swiper-button-prev {
            background-image: none;
            position: absolute;
            top: 0;
            width: 50px;
            height: 100%;
            z-index: 1;
            cursor: pointer;
            background-size: 50px 44px;
            background-position: center;
            background-repeat: no-repeat;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            line-height: 56px;
            margin-top: 0;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            &:hover {
                &:before {
                    content: '';
                    opacity: 1;
                }
            }
            &:before {
                content: '';
                position: absolute;
                background: #fff;
                width: 40px;
                height: 40px;
                top: 8px;
                cursor: pointer;
                border-radius: 100%;
                opacity: .7;
            }
            &.swiper-button-disabled {
                &:before {
                    content: '';
                    opacity: 1;
                }
                opacity: 1;
                span {
                    border: 1px solid #ededed;
                    border-width: 0 0 2px 2px;
                }
            }
            &.off {
                span {
                    display: none;
                }
                &:before {
                    content: '';
                    display: none;
                }
            }
            span {
                position: absolute;
                top: 14px;
                border: 1px solid #000;
                border-width: 0 0 2px 2px;
                width: 10px;
                height: 10px;
            }
        }
        .swiper-button-next {
            right: -49px;
            border-radius:  0 45px 45px 0;
            &:before {
                content: '';
                right: 8px;
            }
            span {
                top: 50%;
                left: 50%;
                transform: translate(-90%, -50%) rotate(-135deg);
            }
        }
        .swiper-button-prev {
            border-radius: 45px 0 0 45px;
            left: -49px;
            &:before {
                content: '';
                left: 8px;
            }
            span {
                top: 50%;
                left: 50%;
                transform: translate(0%, -50%) rotate(45deg);
            }
        }
    }
</style>

<style scoped lang="less">
    @import "index_v1";
</style>
