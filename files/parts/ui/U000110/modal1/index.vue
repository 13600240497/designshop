<template>
    <div class="geshop-u000110-modal1"
         :style="{
            backgroundColor : data.box_bg_color || '#F2F2F2',
            marginBottom: `${ data.box_margin_bottom / 75 || 40/75}rem`
          }">

        <img :src="data.bannerImg || 'https://geshopimg.logsss.com/uploads/XVbaglsB9icvKSeMTxdC2PFY4WQ8tyuA.png'" alt="banner" class="top-banner" v-if="data.banner_show ==1">
        <div class="list-wrap" ref="swiper">
            <div class="swiper-container"
                 :style="{backgroundColor: data.tabBgc || '#ffffff' , 'border-radius': `${data.tabBorderRadius ? data.tabBorderRadius / 75 : (12 / 75) }rem`}">
                <ul class="goods-nav-name swiper-wrapper">
                    <li class="swiper-slide" :class="{'on': index == cur}"
                        :style="{ color: cur == index ? data.tabSelectedTextColor : data.tabTextColor }"
                        v-if="data.goodsIds != '' && data.goodsIds[0].cateid !=''"
                        v-for="(item, index) in data.goodsIds"
                        :key="item.category"
                        @touchend.prevent="handleAddClass(index)">
                        <span>{{ item.category }}</span>
                    </li>
                    <li class="swiper-slide"
                        :class="{'on': index == cur}"
                        v-if="data.goodsIds == '' || data.goodsIds[0].cateid ==''"
                        v-for="(key2, index) in 5" :key="index"
                        :style="{ color: cur == index ? data.tabSelectedTextColor || '#FFA800': data.tabTextColor || '#333' }"
                        @click="handleAddClass(index)">
                        <span>Mini Dresses</span>
                    </li>
                </ul>
            </div>
            <div class="bd-box"></div>
            <div class="list-group">
                <div class="cont-list"
                     v-if="data.goodsIds != '' && data.goodsIds[0].cateid != '' ">
                    <ul>
                        <li class="list-item" v-for="(item, index) in goodInfo" :key="item.goods_sn">
                            <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id"
                                class="item-img">
                                <geshop-image-goods :src="item.goods_img"
                                                    :sku="item.goods_sn"
                                                    :lazyload="lazyload"
                                                    :index="index">

                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <div class="item-info-box">
                                <geshop-analytics-href
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id" class="item-title">{{ item.goods_title }}
                                </geshop-analytics-href>

                                <P class="sale-discount" :style="{ backgroundColor: data.discount_bg_color, color: data.discount_font_color }">
                                    <span>{{ $lang("discount_rank_label").replace('XX', item.discount)}}</span>
                                </p>

                                <p class="item-shop-price">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                </p>
                                <p class="item-shop-price2">
                                    <geshop-market-price :value="item.market_price"></geshop-market-price>
                                </p>
                            </div>
                            <span class="btn-rank"
                                  v-if="index == 0"
                                  :style="{
                                       backgroundImage: `url(${data.iconFirstImg || 'https://geshopimg.logsss.com/uploads/dYjSD7mRkobON2nWZagAf9rGLJsTwi3h.png' })`
                                  }">
                            </span>
                            <span class="btn-rank"
                                  v-if="index == 1"
                                  :style="{
                                       backgroundImage: `url(${data.iconSecondImg || 'https://geshopimg.logsss.com/uploads/LH5k8UDZxdBqE67Yo9XtGRgCQWIfzyhJ.png' })`
                                  }">
                            </span>
                            <span class="btn-rank"
                                  v-if="index == 2"
                                  :style="{
                                       backgroundImage: `url(${data.iconThirdImg || 'https://geshopimg.logsss.com/uploads/yparqCgv2YnZ1xe3uRP7iowMl8SAQBIN.png' } )`
                                  }">
                            </span>
                            <span class="btn-rank mid"
                                  v-if="index > 2"
                                  :style="{
                                       backgroundImage: `url( ${data.iconOtherImg || 'https://geshopimg.logsss.com/uploads/rkgPzLI91s3mXYneJuhCDi6OUy275Bwx.png'} )`
                                  }">
                                <span
                                    :style="{'font-size': index + 1 > 99 ? 18/75+'rem' : '', color: `${data.iconOtherTextColor || '#ffffff'}`}">{{ index + 1}}</span>
                            </span>
                            <geshop-analytics-href
                                :href="item.url_title"
                                :sku="item.goods_sn"
                                :cate="item.cateid"
                                :warehouse="item.warehousecode"
                                :goods_id="item.goods_id" class="btn-buy"
                                :style="{
                                    backgroundImage: `url(${data.buy_img || 'https://geshopimg.logsss.com/uploads/HUMSCLlQox95nA20Nchpv8iVKBjr3yz6.png'})`
                            }">
                            </geshop-analytics-href>
                            >
                        </li>
                    </ul>
                    <!--<div class="pagetion">
                        <span class="btn-prev btn-pagetion off"></span>
                        <span class="btn-next btn-pagetion"></span>
                    </div>-->
                </div>
                <div class="cont-box" :data-name="index"
                     v-if="(data.goodsIds == '' || data.goodsIds[0].cateid == '' ) && cur==index"
                     v-for="(key, index) in 5" :key="index">
                    <ul>
                        <li class="list-item" v-for="(item, index) in 10" :key="index">
                            <geshop-analytics-href class="item-img">
                                <geshop-image-goods>
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <div class="item-info-box">
                                <a href="#" class="item-title">ZAFUL{{cur}} Plunge Slit Printed Maxi Dress - Red Wine
                                    M</a>
                                <P class="sale-discount" v-if="lang == 'en'"
                                   :style="{ backgroundColor: data.discount_bg_color || '#ffeecc', color: data.discount_font_color || '#ffa800' }">
                                    <span>50%</span><span>OFF</span>
                                </P>
                                <P class="sale-discount" v-else
                                   :style="{ backgroundColor: data.discount_bg_color || '#ffeecc', color: data.discount_font_color || '#ffa800' }">
                                    <span>-50%</span><span>OFF</span>
                                </P>
                                <p class="item-shop-price">
                                    <geshop-shop-price></geshop-shop-price>
                                </p>
                                <p class="item-shop-price2">
                                    <geshop-market-price></geshop-market-price>
                                </p>
                            </div>
                            <span class="btn-rank"
                                  v-if="index == 0"
                                  :style="{
                                       backgroundImage: `url(${data.iconFirstImg || 'https://geshopimg.logsss.com/uploads/dYjSD7mRkobON2nWZagAf9rGLJsTwi3h.png' })`
                                  }">
                            </span>
                            <span class="btn-rank"
                                  v-if="index == 1"
                                  :style="{
                                       backgroundImage: `url(${data.iconSecondImg || 'https://geshopimg.logsss.com/uploads/LH5k8UDZxdBqE67Yo9XtGRgCQWIfzyhJ.png' })`
                                  }">
                            </span>
                            <span class="btn-rank"
                                  v-if="index == 2"
                                  :style="{
                                       backgroundImage: `url(${data.iconThirdImg || 'https://geshopimg.logsss.com/uploads/yparqCgv2YnZ1xe3uRP7iowMl8SAQBIN.png' } )`
                                  }">
                            </span>
                            <span class="btn-rank mid"
                                  v-if="index > 2"
                                  :style="{
                                       backgroundImage: `url( ${data.iconOtherImg || 'https://geshopimg.logsss.com/uploads/rkgPzLI91s3mXYneJuhCDi6OUy275Bwx.png'} )`
                                  }">
                                <span
                                    :style="{'font-size': index + 1 > 99 ? 18/75+'rem' : '', color: `${data.iconOtherTextColor || '#ffffff'}`}">{{ index + 1}}</span>
                            </span>
                            <a href="javascript:void (0)" class="btn-buy" :style="{
                                backgroundImage: `url(${data.buy_img || 'https://geshopimg.logsss.com/uploads/HUMSCLlQox95nA20Nchpv8iVKBjr3yz6.png'})`
                            }"></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            goodsIds: [],
            cur: 0,
            scrollTop: 0,
            $wrap: null,
            $boxWrap: null,
            boxH: 0,
            // pageno: 1,
            lang: 'en',
            goodInfo: [],
            lazyload: true,
            goodsData: {},
            mySwiper: null
        };
    },
    created () {
        loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(() => {
            this.lang = GESHOP_LANG || 'en';
            // 去处loading
            this.$store.dispatch('global/loaded', this);
            // 页面初始化
            this.$store.dispatch('global/async_goods_init', this);
            this.$nextTick(() => {
                this.init();
                this.$boxWrap = $(this.$refs.swiper);
            });
        });
    },
    methods: {
        async init () {
            await this.initSwiper();
            if (this.data.goodsIds && this.data.goodsIds.length > 0 && this.data.goodsIds[0].cateid) {
                this.getGoods(0);
            }
            this.bindScroll();
        },
        initSwiper () {
            return new Promise((resolve) => {
                this.mySwiper = new Swiper3('.swiper-container', {
                    autoplay: false,
                    slidesPerView: 'auto',
                    slideToClickedSlide: true,
                    lazyLoading: true,
                    observer: true,
                    observeParents: true
                });
                resolve();
            });
        },
        handleAddClass (index) {
            $('html,body').animate({ scrollTop: this.scrollTop + 1 }, 'slow');
            if (this.cur == index) {
                return false;
            } else {
                // this.pageno = 1;
                this.cur = index;
                if (!!this.data.goodsIds[0].cateid) {
                    if (!!this.goodsData[index]) {
                        this.lazyload = false;
                        this.goodInfo = this.goodsData[index];
                    } else {
                        this.lazyload = true;
                        this.getGoods(index);
                    }
                }
            }
        },
        bindScroll () {
            const _this = this;
            // let timer = null;

            $(window).on('scroll', function () {
                _this.scrollTop = _this.$boxWrap.offset().top;
                _this.boxH = _this.$boxWrap.outerHeight();
                let scrollTop = $(this).scrollTop();

                if (_this.scrollTop <= scrollTop && scrollTop < _this.scrollTop + _this.boxH) {
                    _this.$boxWrap.find('.swiper-container').addClass('fixed');
                    _this.$boxWrap.find('.bd-box').show();
                    // _this.$boxWrap.closest('#page').find('#pageHeader').css('zIndex', '-1');
                    _this.$boxWrap.parents('.geshop-component-box').addClass('js-geshop-nav-fixed');

                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').hide();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000030"]').length) {
                        $('div[data-key="U000030"]').find('nav').hide();
                    }
                } else {
                    _this.$boxWrap.find('.swiper-container').removeClass('fixed');
                    _this.$boxWrap.find('.bd-box').hide();
                    _this.$boxWrap.parents('.geshop-component-box').removeClass('js-geshop-nav-fixed');

                    // 站点导航栏处理
                    if ($('.js-geshop-nav').length) {
                        $('.js-geshop-nav').show();
                    }

                    // 页面中存在水平导航时，隐藏水平导航
                    if ($('div[data-key="U000030"]').length) {
                        $('div[data-key="U000030"]').find('nav').show();
                    }

                    if (GEShopSiteCommon) {
                        GEShopSiteCommon.jsNavFixed();
                    }
                }
            });
        },
        getGoods (index) {
            const _this = this;
            let params = {
                type: 3, // 1新品2热卖3低价
                cateid: _this.data.goodsIds[index].cateid, // 类目ID
                lang: GESHOP_LANG || 'en', // 语言包
                // pageno: this.pageno, 当前页码
                pagesize: Math.min(this.data.goodsNum, 100) || 20, // 每页条数
                pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : ''),
                client: (typeof GESHOP_PLATFORM != 'undefined' ? GESHOP_PLATFORM : '')
            };
            if (_this.data.goodsIds[index].cateid) {
                this.$jsonp(GESHOP_INTERFACE.getrankdetail.url, params).then(res => {
                    _this.goodInfo = res.data.goodsInfo;
                    _this.goodsData[index] = res.data.goodsInfo;
                    // 页面元素初始化
                    _this.$store.dispatch('global/async_goods_init', _this);
                });
            }
        }
    }
};
</script>

<style lang="less">
    .geshop-u000110-modal1 {
        .geshop-components-default-image-goods img {
            display: inline-block;
            vertical-align: bottom;
        }
    }

    #pageHeader.headroom--top {
        z-index: 10 !important;
    }
</style>
<style lang="less" scoped>
    .geshop-u000110-modal1 {
        background: #F2F2F2;

        img {
            max-width: 100%;
            display: block;
        }

        .def-img {
            background: #F2F2F2;
            height: 300 /75rem;
            position: relative;

            img {
                width: 129/75rem;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        }

        .list-wrap {
            box-sizing: border-box;
            padding: 0 24/75rem 8/75rem;

            .bd-box {
                display: none;
                height: 84 / 75rem;
                line-height: 84 / 75rem;
                margin-bottom: 24/75rem;
            }

            .swiper-container {
                height: 84 / 75rem;
                line-height: 84 / 75rem;
                width: (750 - 48)/75rem;
                margin: 0 auto 24/75rem;

                &.fixed {
                    position: fixed;
                    left: 0;
                    top: 0;
                    right: 0;
                    width: 100%;
                    z-index: 9999;
                    height: 84 / 75rem;
                    border-radius: 0 !important;
                    box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.15);
                }

                .goods-nav-name {
                    text-align: center;
                    display: inline-block;
                    white-space: nowrap;
                }

                .swiper-slide {
                    display: inline-block;
                    width: auto;
                    padding: 0 30/75rem;
                    font-size: 32/75rem;

                    span {
                        height: 84 / 75rem;
                        box-sizing: border-box;
                        display: inline-block;
                        line-height: 84 / 75rem;
                        position: relative;
                    }

                    &.on {
                        span {
                            &:after {
                                position: absolute;
                                width: 100%;
                                content: '';
                                height: 4/75rem;
                                background-color: currentColor;
                                border-radius: 40/75rem;
                                left: 0;
                                bottom: 0;
                            }
                        }
                    }
                }
            }

            .list-group {
                .list-item {
                    width: 100%;
                    height: 360 / 75rem;
                    background: rgba(255, 255, 255, 1);
                    border-radius: 12/75rem;
                    font-size: 0;
                    margin-bottom: 16 / 75rem;
                    overflow: hidden;
                    position: relative;

                    .item-img {
                        display: inline-block;
                        width: 270 / 75rem;
                        height: 360 / 75rem;
                        vertical-align: top;
                    }

                    .btn-rank {
                        position: absolute;
                        width: 52/75rem;
                        height: 58/75rem;
                        background-size: 52/75rem 58/75rem !important;
                        left: 24 / 75rem;
                        top: 0;

                        &.mid {
                            font-size: 30/75rem;
                            line-height: 58/75rem;
                            text-align: center;
                        }
                    }

                    .btn-buy {
                        position: absolute;
                        right: 0;
                        bottom: 0;
                        width: 100 /75rem;
                        height: 100 /75rem;
                        /*background: url("https://geshopimg.logsss.com/uploads/HUMSCLlQox95nA20Nchpv8iVKBjr3yz6.png") 0 0 no-repeat;*/
                        background-repeat: no-repeat;
                        background-size: 100 /75rem auto;
                    }

                    .item-info-box {
                        display: inline-block;
                        vertical-align: top;
                        font-size: .24 / 75rem;
                        color: #333333;
                        box-sizing: border-box;
                        width: 428 / 75rem;
                        padding: 30/75rem 24/75rem 0 30/75rem;

                        .item-title {
                            font-size: 24/75rem;
                            color: #333333;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            white-space: nowrap;
                            /*text-overflow: ellipsis;  有些示例里需要定义该属性，实际可省略*/
                            /*  display: -webkit-box;
                              -webkit-line-clamp: 2;
                              -webkit-box-orient: vertical;*/
                            line-height: 32/75rem;
                            display: block;
                            height: 32 /75rem;
                            margin-bottom: 24 / 75rem;
                        }

                        .sale-discount {
                            display: inline-block;
                            padding: 0 0.32rem;
                            font-size: 0;
                            max-width: 90%;
                            overflow: hidden;
                            white-space: nowrap;
                            height: 48/75rem;
                            line-height: 48/75rem;
                            text-align: center;
                            border-radius: 48/75rem;
                            margin-bottom: 24/75rem;

                            > span {
                                line-height: normal;
                                display: inline-block;
                                vertical-align: middle;
                                font-size: 26/75rem;
                                margin: 0 4/75rem;
                            }
                        }

                        .item-shop-price {
                            font-size: 36 / 75rem;
                            font-family: OpenSans-Bold;
                            font-weight: bold;
                            line-height: 48/ 75rem;
                        }

                        .item-shop-price2 {
                            line-height: 36/ 75rem;
                        }
                    }
                }

                .pagetion {
                    text-align: center;
                    padding: 16 / 75rem 0 32/75rem 0;

                    .btn-pagetion {
                        display: inline-block;
                        width: 80 /75rem;
                        height: 80 /75rem;
                        background-size: 80 /75rem auto !important;
                        margin: 0 56/75rem;

                        &.btn-prev {
                            background: url("//geshopimg.logsss.com/uploads/76LkASIeKljWXmoyBt4wRFp5hbi9JDqf.png") no-repeat 50% 50%;

                            &.off {
                                background: url("//geshopimg.logsss.com/uploads/uKx5h62EMbeIlL4ZYvDW0B71gzTHXjmt.png") no-repeat 50% 50%;
                            }
                        }

                        &.btn-next {
                            background: url("//geshopimg.logsss.com/uploads/HfL0ZQiCVePoAzR6mWBI3FsgxTStn5Uh.png") no-repeat 50% 50%;

                            &.off {
                                background: url("//geshopimg.logsss.com/uploads/Vxa5dDgvjRnBlueOIoLUPwzf9bMirSTJ.png") no-repeat 50% 50%;
                            }
                        }
                    }
                }
            }
        }
    }
</style>
