<template>
    <div class="geshop-u000209-modal1-body" :style="boxStyle">
        <div class="list-wrap" ref="swipers">
            <div class="nav-hd-wrap">
                <div class="swiper-container" :style="{ background: data.nav1_bgcolor }">
                    <ul class="goods-nav-name swiper-wrapper">
                        <li class="swiper-slide"
                            :class="{ 'on': index == nav1Cur }"
                            v-for="(item, index) in initData"
                            @click="handelNav1(index)"
                            :key="index">
                            <span :style="index == nav1Cur ? nav1OnStyle : nav1Style">{{ item.navName }}</span>
                        </li>
                    </ul>
                </div>
                <div class="pagenation-wrap" :style="{ background: data.nav1_bgcolor }">
                    <div class="nav-info" :class="{ 'on': showNav }" @click="showNav = !showNav">
                        {{ nav2Name }}
                        <span class="con-all"></span>
                    </div>
                    <div class="sub-nav-wrap" :class="{ 'on': showNav }">
                        <ul class="sub-nav">
                            <li class="sub-item"
                                :class="{ 'on': index == nav2Cur }"
                                :style="index == nav2Cur ? nav2OnStyle : nav2Style"
                                v-for="(item, index) in navListData"
                                @click="handelRenderDetail(index)"
                                :key="index"
                            >
                                <span>{{ item.listName }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="bd-box" :style="bdBoxStyle"></div>
            <div class="goods-list-wrap" v-if=" currentGoods.length == 0 ">
                <ul>
                    <li class="list-item" v-for="(item, index) in 4" :key="index">
                        <div class="item-img">
                            <geshop-analytics-href>
                                <geshop-image-goods>
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <p class="sale-discount"
                               v-if="(data.discount_type || 1) == 1"
                               :style="{ backgroundColor: data.discount_bg_color || '#EA5455', color: data.discount_font_color || '#FFFFFF' }">
                                <span>50% </span><span>OFF</span>
                            </p>
                            <p class="sale-discount"
                               v-else
                               :style="{ backgroundColor: data.discount_bg_color || '#EA5455', color: data.discount_font_color || '#FFFFFF' }">
                                <span>-50%</span>
                            </p>
                            <div class="promotion-info">Buy Get 1 15% Off</div>
                            <geshop-soldout :visible="false"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <div class="rate-box">
                                <p class="item-shop-price">
                                    <geshop-shop-price></geshop-shop-price>
                                </p>
                                <p class="item-shop-prce2">
                                    <geshop-market-price></geshop-market-price>
                                </p>
                                <a href="#" class="shop-fast"></a>
                                <!--<a href="#" class="shop-fast js_fast_buy"
                                   data-href="/m-goods_fast-a-ajax_goods-id-"></a>-->
                            </div>
                            <a href="javascript:void (0)" class="item-title">Tartan Panel Long Sleeve Asymmetrical
                                T-shirt OFF</a>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="goods-list-wrap" v-else>
                <ul>
                    <li class="list-item" v-for="(item, index) in currentGoods" :key="item.goods_id">
                        <div class="item-img">
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
                                        :index="index">
                                </geshop-image-goods>
                            </geshop-analytics-href>
                            <span v-if="item.discount - 0 > 0">
                                <p class="sale-discount"
                                   v-if="(data.discount_type || 1) == 1"
                                   :style="discountStyle">
                                    <span>{{ item.discount }}% </span><span>OFF</span>
                                </p>
                                <p class="sale-discount"
                                   v-else
                                   :style="discountStyle">
                                    <span>-{{ item.discount }}%</span>
                                </p>
                            </span>
                            <div class="promotion-info" v-if="item.promotions.length > 0 && (data.marketing_is_show || 1) == 1" v-html="item.promotions[0]"></div>
                            <geshop-soldout :visible="item.goods_number <= 0"></geshop-soldout>
                        </div>
                        <div class="item-info-box">
                            <div class="rate-box">
                                <p class="item-shop-price">
                                    <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                </p>
                                <p class="item-shop-prce2">
                                    <geshop-market-price :value="item.market_price" v-if="item.market_price - item.shop_price > 0"></geshop-market-price>
                                </p>
                                <geshop-analytics-href
                                        v-if="client == 'app'"
                                        :href="item.url_title"
                                        :sku="item.goods_sn"
                                        :cate="item.cateid"
                                        :warehouse="item.warehousecode"
                                        :goods_id="item.goods_id" class="shop-fast">
                                </geshop-analytics-href>
                                <a href="javascript:void (0)"
                                   v-else
                                   class="shop-fast js_fast_buy"
                                   :data-href="'/m-goods_fast-a-ajax_goods-id-' + item.goods_id">
                                </a>
                            </div>
                            <geshop-analytics-href
                                    v-if="(data.title_is_show || 1) == 1"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id" class="item-title">{{ item.goods_title }}
                            </geshop-analytics-href>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data () {
        return {
            $boxWrap: null, // 当前容器
            lang: '', // 当前语言
            navListData: [], // 导航数据
            nav1Name: '', // 当前导航1
            nav2Name: '', // 当前导航2
            sku: '', // 当前sku
            nav1Cur: 0, // 当前导航1 index
            nav2Cur: 0, // 当前导航2 index
            showNav: false, // 是佛显示二级导航
            currentGoods: [],
            client: '',
            lazyLoad: true,
            cachList: {} // 缓存所有list
        };
    },
    computed: {
        initData () {
            let res = [];
            if (this.data.navList !== 'false' && !!this.data.navList) {
                res = this.data.navList;
            } else {
                res = [
                    {
                        'navName': 'nav1',
                        'list': [{ 'listName': 'lsitNav1-1', 'goods': '' }]
                    },
                    {
                        'navName': 'nav2',
                        'list': [{ 'listName': 'lsitNav1-2', 'goods': '' }]
                    },
                    {
                        'navName': 'nav3',
                        'list': [{ 'listName': 'lsitNav1-3', 'goods': '' }]
                    }
                ];
            };
            return res;
        },
        bdBoxStyle () {
            return {
                height: `${this.data.nav1_height * 2 / 75}rem`
            };
        },
        discountStyle () {
            return {
                backgroundColor: this.data.discount_bg_color || '#EA5455',
                backgroundImage: `url(${this.data.discount_bg_image})`,
                color: this.data.discount_font_color || '#FFFFFF',
                backgroundSize: 'cover'
            };
        },
        boxStyle () {
            return {
                marginTop: `${this.data.box_margin_top / 75}rem`,
                marginBottom: `${this.data.box_margin_bottom / 75}rem`,
                backgroundColor: `${this.data.box_bg_color}`,
                backgroundImage: `url(${this.data.box_bg_image})`
            };
        },
        nav1OnStyle () {
            return {
                color: this.data.hover_color,
                height: this.data.nav1_height / 75 + 'rem',
                'line-height': this.data.nav1_height / 75 + 'rem'
            };
        },
        nav1Style () {
            return {
                color: this.data.nav1_color,
                height: this.data.nav1_height / 75 + 'rem',
                'line-height': this.data.nav1_height / 75 + 'rem'
            };
        },
        nav2OnStyle () {
            return {
                color: this.data.hover_color
            };
        },
        nav2Style () {
            return {
                color: this.data.nav2_color
            };
        }
    },

    mounted () {
        loadCss(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.min.css');
        $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(() => {
            this.lang = GESHOP_LANG || 'en';
            this.$nextTick(() => {
                this.$boxWrap = $(this.$refs.swipers);
                this.client = GESHOP_PLATFORM || 'wap';
                this.setNav(0, 0);
                this.init();
            });
        });
    },

    methods: {
        async init () {
            await this.initSwiper();
            this.sku = this.initData[0].list[0].goods;
            if (this.sku !== '') {
                this.getGoods(this.sku);
            };
            if (this.data.is_fixed == 1) {
                this.bindScroll();
            };
        },

        initSwiper () {
            return new Promise((resolve) => {
                const swiper = new Swiper3(this.$boxWrap.find('.swiper-container'), {
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
        bindScroll () {
            const _this = this;
            // let timer = null;

            $(window).on('scroll', function () {
                // _this.$boxWrap.closest('#page').find('#pageHeader').hide();
                // const $boxWp = _this.$boxWrap.find('.list-wrap');
                const boxTop = _this.$boxWrap.offset().top;
                const boxH = _this.$boxWrap.outerHeight();
                const scrollTop = $(this).scrollTop();
                if (boxTop <= scrollTop && scrollTop < boxTop + boxH) {
                    _this.$boxWrap.find('.nav-hd-wrap').addClass('fixed');
                    _this.$boxWrap.find('.bd-box').show();
                } else {
                    _this.$boxWrap.find('.nav-hd-wrap').removeClass('fixed');
                    _this.$boxWrap.find('.bd-box').hide();
                };
            });
        },

        handelNav1 (index) {
            // 切换一级导航
            this.showNav = false;
            this.scrollTop();
            if (this.nav1Cur == index) {
                return false;
            }
            this.nav1Cur = index;
            this.nav2Cur = 0;
            this.setNav(index, 0);
            this.getGoods();
        },

        handelRenderDetail (index) {
            this.showNav = false;
            this.scrollTop();
            if (this.nav2Cur == index) {
                return false;
            }
            this.nav2Cur = index;
            this.setNav(this.nav1Cur, index);
            this.getGoods();
        },

        setNav (nav1Index, nav2Index) {
            this.navListData = this.initData[nav1Index].list;
            this.nav1Name = this.initData[nav1Index].navName;
            this.nav2Name = this.initData[nav1Index].list[nav2Index].listName;
        },

        scrollTop () {
            if (this.data.is_fixed == 1) {
                const targetScrollTop = this.$boxWrap.offset().top;
                $('html,body').animate({ scrollTop: targetScrollTop }, 500);
            }
        },

        async getGoods () {
            const key = `${this.nav1Cur}-${this.nav2Cur}`;
            if (!this.cachList[key]) {
                this.sku = this.initData[this.nav1Cur].list[this.nav2Cur].goods;
                this.lazyLoad = true;
                const data = {
                    lang: this.lang,
                    client: GESHOP_PLATFORM || 'wap',
                    filterStock: 1,
                    goodsSn: this.sku
                };
                const url = GESHOP_INTERFACE.goods_async_detail.url;
                await this.$jsonp(url, data).then(res => {
                    this.currentGoods = res.data.goodsInfo;

                    let arr = [];
                    this.currentGoods.forEach((item, index) => {
                        if (item.goods_number <= 0) {
                            arr.push(item);
                            this.currentGoods.splice(index, 1);
                        }
                    });
                    this.currentGoods = this.currentGoods.concat(arr);
                    this.cachList[key] = this.currentGoods;
                    // 页面元素初始化
                    this.$store.dispatch('global/async_goods_init', this);
                });
            } else {
                this.lazyLoad = false;
                this.currentGoods = this.cachList[key];
            }
        }
    }
};
</script>

<style lang="less" scoped>
    .geshop-u000209-modal1-body {
        /*font-family: TrebuchetMS,Arial;*/
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        .list-wrap {
            box-sizing: border-box;

            .bd-box {
                display: none;
            }

           .nav-hd-wrap{
               &.fixed {
                   position: fixed;
                   left: 0;
                   top: 0;
                   right: 0;
                   width: 100%;
                   z-index: 101;
                   /*<!--height: 84 / 75rem;-->*/
                   border-radius: 0 !important;
                   box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.15);
               }
               .swiper-container {
                   height: 88 / 75rem;
                   line-height: 88 / 75rem;
                   width: 10rem;
                   margin: 0 auto;
                   border-bottom: 1px solid #EEEEEE;
                   /*background-color: #ffffff;*/

                   .goods-nav-name {
                       /*text-align: center;*/
                       display: inline-block;
                       white-space: nowrap;
                   }

                   .swiper-slide {
                       display: inline-block;
                       width: auto;
                       padding: 0 36/75rem;
                       font-size: 28/75rem;

                       span {
                           height: 88 / 75rem;
                           box-sizing: border-box;
                           display: inline-block;
                           line-height: 88 / 75rem;
                           position: relative;
                       }

                       &.on {
                           span {
                               &:after {
                                   position: absolute;
                                   width: 100%;
                                   content: '';
                                   height: 6/75rem;
                                   background-color: currentColor;
                                   border-radius: 40/75rem;
                                   left: 0;
                                   bottom: 0;
                               }
                           }
                       }
                   }
               }
           }

            .pagenation-wrap {
                font-size: 28 /75rem;
                position: relative;

                .nav-info {
                    margin:0 24/75rem;
                    height: 88 / 75rem;
                    line-height: 88 / 75rem;
                    position: relative;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    white-space: nowrap;
                    padding-right: 7%;
                    .con-all {
                        position: absolute;
                        right: 2%;
                        top: 38%;
                        width: 16/75rem;
                        height: 16/75rem;
                        border-right: 1px solid currentColor;
                        border-bottom: 1px solid currentColor;
                        transform: rotate(45deg);

                    }

                    &.on {
                        .con-all {
                            top: 47%;
                            transform: rotate(-135deg);
                        }
                    }
                }

                .sub-nav-wrap {
                    background-color: #ffffff;
                    border-top: 1px solid #EEEEEE;
                    border-bottom: 1px solid #EEEEEE;
                    position: absolute;
                    width: 100%;
                    z-index: 1;
                    overflow: hidden;
                    display: none;

                    &.on {
                        display: block;
                    }

                    .sub-nav {
                        .sub-item {
                            box-sizing: border-box;
                            display: block;
                            margin: 0 24/75rem;
                            line-height: 44 /37.5rem;
                            border-bottom: 1px solid #eee;
                            text-overflow: ellipsis;
                            overflow: hidden;
                            white-space: nowrap;

                            &:last-child {
                                border-bottom: none;
                            }
                        }
                    }
                }
            }

            .goods-list-wrap {
                > ul {
                    display: block;
                    font-size: 0;
                    width: (375 - 15)/37.5rem;
                    margin: 0 auto;

                    li.list-item {
                        display: inline-block;
                        width: 171/ 37.5rem;
                        height: auto;
                        margin: 0 4.5/37.5rem 10/37.5rem;
                        font-size: 12/37.5rem;
                        color: #333333;
                        position: relative;
                        background-color: #ffffff;
                        overflow: hidden;
                        vertical-align: top;

                        .item-img {
                            width: 171/ 37.5rem;
                            height: 228/37.5rem;
                            position: relative;
                            display: block;
                            overflow: hidden;
                            .geshop-zaful-image-good, a {
                                display: flex;
                                height: 100%;
                                justify-content: center;
                            }

                            img {
                                display: block;
                                width: 100%;
                            }

                            .sale-discount {
                                line-height: 18/37.5rem;
                                position: absolute;
                                padding: 0 4/37.5rem;
                                top: 8/37.5rem;
                                right: 0;
                                font-size: 12/37.5rem;
                            }

                            .promotion-info {
                                width: 100%;
                                height: 20/37.5rem;
                                line-height: 20/37.5rem;
                                position: absolute;
                                bottom: 0;
                                text-align: center;
                                background: rgba(255, 255, 255, 0.6);
                            }
                        }

                        .item-info-box {
                            .item-title {
                                overflow: hidden;
                                /*text-overflow: ellipsis;  有些示例里需要定义该属性，实际可省略*/
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                line-height: 32/75rem;
                                /*display: block;*/
                                height: 29 /37.5rem;
                                margin: 10/37.5rem 0 10/37.5rem;
                                font-size: 12/37.5rem;
                            }

                            .rate-box {
                                position: relative;

                                .item-shop-price {
                                    margin-top: 6/37.5rem;

                                    .geshop-shop-price {
                                        line-height: 22/37.5rem;
                                        font-size: 16/37.5rem;
                                    }
                                }

                                .item-shop-price2 {
                                    .geshop-market-price {
                                        line-height: 17/37.5rem;
                                        font-size: 12/37.5rem;
                                    }
                                }

                                .shop-fast {
                                    background: url("https://geshoptest.s3.amazonaws.com/uploads/XR6nmvfcejDVSgNbwHpLaqCyI08T1h9l.png") 50% 50% no-repeat;
                                    width: 24/37.5rem;
                                    background-size: 24/37.5rem auto;
                                    height: 24/37.5rem;
                                    position: absolute;
                                    right: 0;
                                    top: 6/37.5rem;
                                }
                            }

                        }
                    }
                }
            }
        }
    }
</style>
