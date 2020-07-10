<template>
    <div class="geshop-component-body U0000208_rank_wrap" :class="`is-${platform} ${boxWrapMedia}`" ref="rankWrap">
        <div class="banner" v-if="data.is_show == 1">
           <picture>
               <source :srcset="data.banner_img_m || 'https://geshopimg.logsss.com/uploads/1zR6Lb8XWuGZh3DNQAwCaUOdxKEHi9sm.png'" media="(max-width: 767px)">
               <img alt="" :src="data.banner_img || 'https://geshopimg.logsss.com/uploads/2ysZMWpA0mck8XvG3OYIJKUDg4LRdzoh.png'">
           </picture>
        </div>
        <div class="sp-box">
            <div class="nav-top">
                <div class="top-swiper-wrap">
                    <div class="swiper-container top-swiper-container" ref="mySwiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide nav-item" v-for="(item, index) in  (data.navList && data.navList.length > 0 && data.navList[0].tab_name ?  data.navList : 8)" :key="index" :class="{on: index == 0}"><span>{{ item.tab_name || 'Swimwear' + (index + 1) }}</span></div>
                        </div>
                    </div>
                    <div class="swiper-button-next"><span></span></div>
                    <div class="swiper-button-prev"><span></span></div>
                </div>
            </div>
        </div>
        <div class="goods-list">
            <ul>
                <li v-for="(item, index) in (list.length > 0 ? list : 7)" :key="item.goods_sn || index"  class="list" :class="index < 3 ? `list-${index + 1}` : ''">
                    <div class="box-inner">
                        <div class="item_img">
                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id">
                                <geshop-image-goods :type=1 :src="item.goods_img"></geshop-image-goods>
                                <!--M 榜单icon-->
                                <template v-if="view_platform === 'm'">
                                    <div v-if="index < 3" class="icon-rank"> {{index + 1}} </div>
                                </template>
                            </geshop-analytics-href>

                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    v-if="item.goods_number == undefined || item.goods_number > 0"
                                    class="btn-buy"
                            >
                                <p>
                                    <img src="//geshopimg.logsss.com/uploads/XGNQimMYqJsgLAxkPUjy6WeFOBRoIt3D.png" width="22" height="22" alt="">
                                    <span>{{ $lang('shop_now') }}</span>
                                </p>

                            </geshop-analytics-href>
                            <!--sold out-->
                            <geshop-soldout class="item_soldout" :visible="item.goods_number <= 0"></geshop-soldout>

                            <geshop-discount :percent="typeof item.discount != 'undefined' ? item.discount : 50"></geshop-discount>

                        </div>
                        <div class="item_goods_info">
                            <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id" class="item-title">
                                <p>{{ item.goods_title || 'Rosegal HOT Slip Pockets Fast Rosegal HOT Slip Pockets Fast' }}</p>
                            </geshop-analytics-href>
                            <div class="price-info">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                <geshop-market-price class="mk_price" :value="item.market_price" v-show="Number(item.market_price || 0 ) - Number(item.shop_price || 0) >= 0"></geshop-market-price>
                            </div>

                            <!--PC  营销信息-->
                            <template v-if="view_platform === 'pc'">
                                <div v-if="item.promotions && item.promotions.length > 0"  class="promotion-info">
                                    <div v-if="item.promotions.length == 1" v-html="item.promotions[0]"></div>
                                    <template  v-else>
                                        <p v-html="item.promotions[0] + '...'"></p>
                                        <ul>
                                            <li v-for="(its, ins) in item.promotions" :key="ins" v-html="its"></li>
                                        </ul>
                                    </template>
                                </div>
                                <div v-if="item.promotions === undefined" class="promotion-info">
                                    <p>Buy 1 Get 10% OFF</p>
                                    <ul>
                                        <li>Buy 1 Get 10% OFF</li>
                                        <li>Buy 1 Get 20% OFF</li>
                                        <li>Buy 1 Get 30% OFF</li>
                                    </ul>
                                </div>
                                <div v-if="item.promotions && item.promotions.length <= 0" class="promotion-info"></div>
                            </template>
                            <!--pad m  营销信息-->
                            <template v-else>
                                <div v-if="item.promotions && item.promotions.length > 0"  class="promotion-info" :class="showPromo && cur === index ? 'on' : 'off'">
                                    <div v-if="item.promotions.length == 1" v-html="item.promotions[0]"></div>
                                    <template  v-else>
                                        <p  @click="toggelPro(index)" v-html="`${item.promotions[0]}<span class='icon-r'></span>`"></p>
                                        <ul >
                                            <li v-for="(its, ins) in item.promotions" :key="ins" v-html="its"></li>
                                        </ul>
                                    </template>
                                </div>
                                <div v-if="item.promotions === undefined" class="promotion-info" :class="showPromo && cur === index ? 'on' : 'off'">
                                    <p @click="toggelPro(index)">Buy 1 Get 10% OFF<span class="icon-r"></span></p>
                                    <ul >
                                        <li>Buy 1 Get 10% OFF</li>
                                        <li>Buy 1 Get 20% OFF</li>
                                        <li>Buy 1 Get 30% OFF</li>
                                    </ul>
                                </div>
                                <div v-if="item.promotions && item.promotions.length <= 0 && index >= 3" class="promotion-info"></div>
                            </template>
                            <!--评论 goods_grade-->
                            <div class="grade_box" v-if="index > 2">
                                <template v-if="item.goods_reviews > 0">
                                    <div class="grade-bar-l">
                                        <div class="grade-bar grade-top"><span v-for="i in 5" class="iconfont">&#xe685;</span></div>
                                        <div class="grade-bar grade-bot" :style="{width: (item.goods_grade /5)*100 +'%'}"><span v-for="i in 5" class="iconfont">&#xe685;</span></div>
                                    </div>
                                    <div class="grade-bar-r">{{item.goods_reviews}} {{ $lang('reviews') }}</div>
                                </template>
                            </div>
                            <div class="grade_box" v-if="index <3 && item.goods_reviews > 0">
                                <div class="grade-bar-l">
                                    <div class="grade-bar grade-top"><span v-for="i in 5" class="iconfont">&#xe685;</span></div>
                                    <div class="grade-bar grade-bot" :style="{width: (item.goods_grade /5)*100 +'%'}"><span v-for="i in 5" class="iconfont">&#xe685;</span></div>
                                </div>
                                <div class="grade-bar-r">{{item.goods_reviews}} {{ $lang('reviews') }}</div>
                            </div>
                            <div class="grade_box" style="display: none" v-if="index <3 && item.goods_reviews < 0">
                            </div>
                            <!--M 榜单shop now-->
                            <template v-if="view_platform === 'm'">
                                <geshop-analytics-href
                                    v-if="index < 3"
                                    class="btn-shop"
                                    :item="item"
                                    :index="index">
                                    {{ data.m_shop_text || 'SHOP NOW'}}
                                </geshop-analytics-href>
                            </template>
                        </div>
                        <!--PC pad 榜单icon-->
                        <template v-if="view_platform !== 'm'">
                            <img v-if="index === 0" class="top-good-logo" :src="data.rank_img_1 || 'https://geshopimg.logsss.com/uploads/thJ9szge1MDmuHjQG06ZyNviREdCqwOP.png'">
                            <img v-if="index === 1" class="top-good-logo" :src="data.rank_img_2 || 'https://geshopimg.logsss.com/uploads/cK5FxiuoRhgaGm9NrXeqAlOvfYdZtVH6.png'">
                            <img v-if="index === 2" class="top-good-logo" :src="data.rank_img_3 || 'https://geshopimg.logsss.com/uploads/bvnGEH4XNSoZ6xJD5ja2r7ltWKTUphwA.png'">
                        </template>

                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    props: ['data', 'pid'],
    data () {
        return {
            platform: window.GESHOP_PLATFORM || 'web',
            view_platform: 'pc',
            boxWrapMedia: '',
            $boxWrap: '',
            list: [],
            swiper: '',
            showPromo: false,
            cur: 0
        };
    },
    mounted () {
        // 去处loading
        this.$store.dispatch('global/loaded', this);
        // 加载swiper
        loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {
            this.$nextTick(() => {
                // 追加函数到队列
                this.$store.commit('dresslily/update_onresize_marque', this.resizeChange);
                this.$boxWrap = $(this.$refs.rankWrap);
                this.mountedInit();
            });
        });
    },
    methods: {
        toggelPro (index) {
            this.cur = index;
            this.showPromo = !this.showPromo;
        },
        mountedInit () {
            this.resizeChange();
            this.initSwiper();
            const cateid = this.data.navList && this.data.navList[0] && this.data.navList[0].tab_id;
            this.getRankList(cateid);
            this.bindEvent();
        },
        resizeChange () {
            // this.setPlatform();

            const newValue = document.body.clientWidth || document.documentElement.clientWidth;
            let boxWrapMedia = '';
            if (newValue >= 1025) {
                // pc
                this.view_platform = 'pc';
                boxWrapMedia = 'geshop_dl_pc';
            } else if (newValue <= 1024 && newValue >= 768) {
                // pad
                this.view_platform = 'pad';
                boxWrapMedia = 'geshop_dl_pad';
            } else if (newValue <= 767) {
                // m
                this.view_platform = 'm';
                boxWrapMedia = 'geshop_dl_wap geshop_dl_m';
            }
            this.boxWrapMedia = boxWrapMedia;
            this.hideBtn();
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
            this.hideBtn();
        },
        hideBtn () {
            let sw = 0;
            if (this.view_platform == 'pc') {
                sw = 1100;
            } else {
                sw = document.body.clientWidth - 100;
            }
            if (this.swiper.virtualSize <= sw) {
                this.$boxWrap.find('.swiper-button-next, .swiper-button-prev').addClass('off');
            } else {
                this.$boxWrap.find('.swiper-button-next, .swiper-button-prev').removeClass('off');
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
                type: this.data.goods_type,
                cateid: cateid,
                lang: lang,
                pageno: 1,
                pagesize: Math.min(this.data.tab_num, 100) || 100,
                client: window.GESHOP_PLATFORM || 'web'
            };
            const url = GESHOP_INTERFACE.goods_getrankdetail_new.url;
            // const url = 'http://www.pc-dresslily.com.dl_geshop.php5.egomsl.com/geshop/goods/getrankdetail_new';
            this.$jsonp(url, params, { cache: true, jsonpCallback: `GESHOP_${this.pid}` }).then((res) => {
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
                        $('[data-key="U000186"], .js_header, .js-geshop-nav').show();
                    } else {
                        $('[data-key="U000186"], .js_header, .js-geshop-nav').hide();
                    }
                    if (navTop <= scrollTop && scrollTop < boxTop + boxH) {
                        _this.$boxWrap.find('.nav-top').addClass('fixed');
                    } else {
                        _this.$boxWrap.find('.nav-top').removeClass('fixed');
                    }
                }, 10);
            });
        }

    }
};
</script>

<style lang="less">
    .U0000208_rank_wrap {
        .geshop-market-price {
            /*vertical-align: middle;*/
            margin-left: 4px;
        }

        .promotion-info {
            &.on {

                .icon-r {
                    -webkit-transform: rotate(-45deg);
                    -moz-transform: rotate(-45deg);
                    -ms-transform: rotate(-45deg);
                    -o-transform: rotate(-45deg);
                    transform: rotate(-45deg);
                    vertical-align: 0;
                }
            }

            .icon-r {
                width: 6px;
                height: 6px;
                display: inline-block;
                border-top: 1px solid currentColor;
                border-right: 1px solid currentColor;
                -webkit-transform: rotate(135deg);
                -moz-transform: rotate(135deg);
                -ms-transform: rotate(135deg);
                -o-transform: rotate(135deg);
                transform: rotate(135deg);
                margin-left: 8px;
                vertical-align: 4px;
            }
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
            line-height: 70px;
            margin-top: 0;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            /*background: #fff;*/
            &:focus {
                border: none;
                outline: none;
            }
            &:hover {
                &:before {
                    content: '';
                    opacity: 1;
                }
            }
            /*&:before {*/
                /*content: '';*/
                /*position: absolute;*/
                /*background: #fff;*/
                /*width: 40px;*/
                /*height: 40px;*/
                /*top: 15px;*/
                /*cursor: pointer;*/
                /*border-radius: 100%;*/
                /*opacity: .7;*/
            /*}*/
            &.swiper-button-disabled {
                &:before {
                    content: '';
                    opacity: 1;
                }
                opacity: 0.5;
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
                border: 1px solid #FFFFFF;
                border-width: 0 0 2px 2px;
                width: 10px;
                height: 10px;
            }
        }
        .swiper-button-next {
            right: 0;
            /*border-radius:  0 45px 45px 0;*/
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
            /*border-radius: 45px 0 0 45px;*/
            left: 0;
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
        @media screen and (max-width: 767px) {
            .swiper-button-next, .swiper-button-prev {
                display: none;
            }
            .icon-rank {
                background: url("https://geshopimg.logsss.com/uploads/9m7su2KIFZhXEkCUaze1q635W8J4QVYn.png") 50% 50% no-repeat;
                position: absolute;
                width: 40px;
                height: 50px;
                background-size: 100% 100%;
                top: -10px;
                left: 0;
                font-size: 18px;
                font-family:Arial-BoldMT,Arial;
                color: #ffffff;
                text-align: center;
                line-height: 40px;
            }
        }
    }
</style>

<style scoped lang="less">
@import "index_vue";
</style>
