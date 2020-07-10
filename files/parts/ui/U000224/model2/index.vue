<template>
    <div class="geshop-U000224-model2-body" :style="style_body">
        <div class="wrap">
            <div class="default-left-image-box" v-if="left_active[this.platform] == '1' ">
                <a :href="data.img_links||'javascript:;'" :target="openType">
                    <img :src="left_img[this.platform]" :class="'img_'+this.platform">
                </a>
            </div>

            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <div class="swiper-slide" v-for="(item, index) in list" :key="index">
                        <div class="list-item">

                            <!--折扣标-->
                            <div class="item-discount">
                                <geshop-discount :value="item.discount" :percent="item.discount"></geshop-discount>
                            </div>

                            <!-- 图片 -->
                            <div class="item-image">
                                <geshop-analytics-href
                                    :target="openType"
                                    :key="`${item.goods_sn}-${index}`"
                                    :href="item.url_title"
                                    :sku="item.goods_sn"
                                    :cate="item.cateid"
                                    :warehouse="item.warehousecode"
                                    :goods_id="item.goods_id"
                                    :index="index"
                                    :mrlc="''"
                                    :zt="1"
                                    pm="mr">

                                    <div class="geshop-dl-image-goods">
                                        <geshop-image-goods
                                            :src="item.goods_img"
                                            :sku="item.goods_sn"
                                            :mrlc="''">
                                        </geshop-image-goods>
                                    </div>
                                </geshop-analytics-href>
                            </div>

                            <!--sku标题-->
                            <div
                                class="item-title"
                                :class="platform === 'wap' && data.title_is_show == '0' ? 'hide': '' ">
                                <span>{{ item.goods_title || 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …' }}</span>
                            </div>

                            <!--销售价-->
                            <div class="item-shop">
                                <geshop-shop-price :value="item.shop_price"></geshop-shop-price>
                                <div class="inline-block"
                                     :class="(platform == 'wap' && data.price_is_show == 0) || Number(item.market_price) <= Number(item.shop_price) ? 'hide':''">
                                    <geshop-market-price :value="item.market_price"></geshop-market-price>
                                </div>
                            </div>

                            <!-- 按钮 -->
                            <div
                                class="item-buy-buttom align-center"
                                :class="platform === 'wap' && data.buy_is_show == 0 ? 'hide':''">
                                <geshop-buynow
                                    :href="item.url_title"
                                    :skus="item.goods_sn"
                                    :value="data.buy_text"
                                    :target="openType">
                                </geshop-buynow>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div>
                    <span class="swiper-button swiper-button-next hide"></span><span
                    class="swiper-button swiper-button-prev hide"></span>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    props: ['pid', 'theme', 'data'],
    data () {
        return {
            bodyWidth: 0,
            platform: 'pc',
            mySwiper: Object,
            mNavShow: false,
            pcNavShow: false,
            style_body: '',
            list: [
                { 1: 0 },
                { 1: 0 },
                { 1: 0 },
                { 1: 0 },
                { 1: 0 }
            ],
            left_img: {
                pc: '',
                pad: '',
                wap: ''
            },
            left_active: {
                pc: 1,
                pad: 1,
                wap: 1
            },
            defaultList: [
                {
                    goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png',
                    goods_title: 'ZAFUL Asymmetric Striped Slit Shirt Dress - Dark Gree …',
                    shop_price: 16.9,
                    market_price: 20.56
                }
            ],
            scale: 264 / 352,
            imgLoadTime: 0,
            // 不同的端，初始化swiper的参数也不一样
            swiperConfig: {
                pc: {
                    slideNum: 5, // swiper 每页展示的个数
                    spaceNum: 20 // swiper 每一项的间隔
                },
                pad: {
                    slideNum: 3,
                    spaceNum: 10
                },
                wap: {
                    slideNum: 2,
                    spaceNum: 15
                }
            }
        };
    },
    watch: {
        media_platform () {
            this.platform = this.media_platform;
            // 获取 swiper 配置项，并初始化
            const slideNum = this.swiperConfig[this.media_platform].slideNum;
            const spaceNum = this.swiperConfig[this.media_platform].spaceNum;
            this.initSwiper(slideNum, spaceNum, this.media_platform);
        }
    },
    computed: {
        isEditEnv () {
            return this.data.isEditEnv;
        },
        media_platform () {
            return this.$store.state.dresslily.media_platform;
        },
        openType () {
            return this.media_platform === 'pc' || this.media_platform === 'pad' ? this.$root.data.is_open_new : this.$root.data.is_open_new_m;
        }
    },
    methods: {
        /**
         * 初始化 swiper
         * @param {Number} 展示 slide 的个数
         * @param {Number} 每个 slide 之间的间距(px)
         * @param {String} 当前平台
         */
        initSwiper (slideNum, spaceNum, platfromValue) {
            this.$nextTick(() => {
                if (this.mySwiper.destroy) {
                    this.mySwiper.destroy(false);
                    this.mySwiper = {};
                }
                let _this = this;
                let pagination = null;
                let nextButton = null;
                let prevButton = null;
                let loopState = false;
                let platfrom = platfromValue || this.platform;

                // 暂时不明白
                if (platfrom !== 'wap' && this.left_active[this.platform] == '0') {
                    slideNum += 1;
                }

                // 如果是PC端
                if (platfrom === 'pc') {
                    $(this.$el).find('.swiper-pagination').addClass('hide');
                    if (this.data.goodsInfo) {
                        if (Object.keys(this.data.goodsInfo).length > slideNum) {
                            nextButton = this.$el.querySelector('.swiper-button-next');
                            prevButton = this.$el.querySelector('.swiper-button-prev');
                            loopState = true;
                            $(this.$el).find('.swiper-button-next,.swiper-button-prev').removeClass('hide');
                        } else {
                            this.pcNavShow = false;
                            $(this.$el).find('.swiper-button-next,.swiper-button-prev').addClass('hide');
                        }
                    }
                    this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                        spaceBetween: spaceNum,
                        slidesPerView: slideNum,
                        loopedSlides: slideNum,
                        slidesPerGroup: slideNum,
                        observer: true,
                        loop: loopState,
                        loopFillGroupWithBlank: true,
                        lazyLoading: false,
                        lazyLoadingInPrevNext: true,
                        lazyLoadingOnTransitionStart: true,
                        watchSlidesVisibility: true,
                        loadPrevNextAmount: slideNum,
                        navigation: {
                            nextEl: nextButton,
                            prevEl: prevButton
                        },
                        on: {
                            init: function (swiper) {
                                _this.updateLeftHeight();
                                if (_this.data.goodsInfo) {
                                    if (slideNum === Object.keys(_this.data.goodsInfo).length) {
                                        swiper.wrapper[0].style.transform = 'none';
                                    }
                                }
                                GLOBAL && GLOBAL.currency.change_html(null, $(this.$el));
                            },
                            slideChangeTransitionEnd: function () {
                                if (_this.mySwiper.lazy) {
                                    _this.mySwiper.lazy.load();
                                }
                            },
                            lazyImageReady: function () {
                                _this.updateLeftHeight();
                            }
                        }
                    });
                } else {
                    pagination = this.$el.querySelector('.swiper-pagination');
                    $(this.$el).find('.swiper-button-next,.swiper-button-prev').addClass('hide');
                    if (platfrom === 'pad') {
                        if (this.data.goodsInfo) {
                            if (Object.keys(this.data.goodsInfo).length > slideNum) {
                                $(this.$el).find('.swiper-pagination').removeClass('hide');
                            } else {
                                $(this.$el).find('.swiper-pagination').addClass('hide');
                            }
                        }

                        if (this.data.goodsInfo && Object.keys(this.data.goodsInfo).length > slideNum) {
                            loopState = true;
                        } else {
                            this.mNavShow = false;
                        }
                        this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                            slidesPerGroup: slideNum,
                            spaceBetween: spaceNum,
                            slidesPerView: slideNum,
                            observer: true,
                            loop: loopState,
                            loopFillGroupWithBlank: true,
                            lazyLoading: false,
                            watchSlidesVisibility: true,
                            uniqueNavElements: false,
                            pagination: {
                                el: pagination,
                                clickable: true
                            },
                            on: {
                                init: function () {
                                    _this.updateLeftHeight();
                                    GLOBAL && GLOBAL.currency.change_html(null, $(this.$el));
                                },
                                slideChangeTransitionEnd: function () {
                                    // if (_this.mySwiper.lazy) {
                                    //     _this.mySwiper.lazy.load();
                                    // }
                                },
                                lazyImageReady: function () {
                                    _this.updateLeftHeight();
                                }
                            }
                        });
                    } else if (platfrom === 'wap') {
                        if (this.data.goodsInfo && Object.keys(this.data.goodsInfo).length > slideNum) {
                            loopState = true;
                            $(this.$el).find('.swiper-pagination').removeClass('hide');
                        } else {
                            this.mNavShow = false;
                        }
                        this.mySwiper = new Swiper(this.$el.querySelector('.swiper-container'), {
                            slidesPerGroup: slideNum,
                            spaceBetween: spaceNum,
                            slidesPerView: slideNum,
                            loop: loopState,
                            loopFillGroupWithBlank: true,
                            observer: true,
                            lazyLoading: false,
                            watchSlidesVisibility: true,
                            uniqueNavElements: false,
                            paginationClickable: true,
                            pagination: {
                                el: pagination,
                                clickable: true
                            },
                            on: {
                                init: function () {
                                    GLOBAL && GLOBAL.currency.change_html(null, $(this.$el));
                                },
                                slideChangeTransitionEnd: function () {
                                    if (_this.mySwiper.lazy) {
                                        _this.mySwiper.lazy.load();
                                    }
                                }
                            }
                        });
                    }

                    if (typeof GESHOP_UTIL != 'undefined' && typeof GESHOP_UTIL.goodsLazy != 'undefined') {
                        window.GESHOP_UTIL.goodsLazy($('.geshop-U000224-model2-body img.js_gdexp_lazy'));
                    }
                    this.updateLeftHeight();
                    this.mySwiper.update();
                }
            });

            // 图片懒加载，装修页不需要？
            if (this.isEditEnv === 1) {
                $('.swiper-container .swiper-slide').each((index, item) => {
                    $(item).attr('data-src', 'src');
                });
            }
        },

        /**
         * 更新左侧高度？？？？
         */
        updateLeftHeight (type) {
            // let _this = this;
            // let updateTimeOut;
            // if (!type) {
            //     this.imgLoadTime = 0;
            // }
            // 判断左侧AD图是否开启状态
            // if (this.left_active[this.platform] === 1) {
            //     let imgHeight = _this.$el.querySelector('.list-item .geshop-dl-image-goods').offsetHeight;
            //     if (imgHeight === 0) {
            //         if (!this.imgLoadTime) {
            //             if (updateTimeOut) clearTimeout(updateTimeOut);
            //             updateTimeOut = setTimeout(() => {
            //                 this.imgLoadTime = 1;
            //                 this.updateLeftHeight('reload');
            //             }, 500);
            //         }
            //         return false;
            //     }
            //     if (_this.left_active[_this.platform] === 1) {
            //         _this.$el.querySelector('.default-left-image-box img').style.height = _this.$el.querySelector('.list-item').offsetHeight + 'px';
            //     }
            // }
        }
    },
    created () {
        // 因为传过来的是object，无序变量，需要手动的做个排序
        if (this.data.goodsSKU) {
            this.list = [];
            this.data.goodsSKU.toString().split(',').map(sku => {
                // PS: v1.8.9 D网做了网红链接的需求，所以 goodsSKU 数组中的值，在 goodsInfo 里面不一定存在，所以要做判断
                if (this.data.goodsInfo.hasOwnProperty(sku)) {
                    this.list.push(this.data.goodsInfo[sku]);
                }
            });
        }

        // 左侧AD的默认图？
        this.left_img = {
            'pc': this.data.pc_link_image || 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
            'pad': this.data.pc_link_image || 'https://geshopimg.logsss.com/uploads/ktIZfa7N1S68pLzAB2o5gTrC4jMV0beQ.png',
            'wap': this.data.m_link_image || 'https://geshopimg.logsss.com/uploads/30HiEDfyl7ov8bmkw6xSqQY1dCjnZXKU.png'
        };

        // 左侧AD的开启状态？
        this.left_active = {
            'pc': this.data.pc_img_is_show || 1,
            'pad': this.data.pc_img_is_show || 1,
            'wap': this.data.m_img_is_show || 1
        };
    },

    mounted () {
        // 获取 state 的平台值, pc/pad/wap
        this.platform = this.$store.state.dresslily.media_platform;

        let staticDomain = typeof GESHOP_STATIC == 'undefined' ? '' : GESHOP_STATIC;
        loadCss(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.css');
        $LAB.script(staticDomain + '/resources/javascripts/library/swiper/swiper.4.5.min.js').wait(() => {
            // 获取 swiper 配置项，并初始化
            const slideNum = this.swiperConfig[this.platform].slideNum;
            const spaceNum = this.swiperConfig[this.platform].spaceNum;
            this.initSwiper(slideNum, spaceNum, this.platform);

            if (typeof GESHOP_UTIL != 'undefined' && typeof GESHOP_UTIL.goodsLazy != 'undefined') {
                window.GESHOP_UTIL.goodsLazy($('.geshop-U000224-model2-body img.js_gdexp_lazy'));
            }
        });

        // 曝光埋点
        this.$nextTick(() => {
            $(document).triggerHandler('logsss_explore', {
                $elList: $(this.$el).find('.js_logsss_browser')
            });
        });
    }
};
</script>

<style lang="less" scoped>
    .geshop-U000224-model2-body {
        box-sizing: border-box;
        overflow: hidden;

        .hide {
            display: none !important;
        }

        .wrap {
            display: flex;
            position: relative;
            justify-content: space-between;

            .swiper-container {
                flex: 1;

                .swiper-slide {
                    background-color: #fff;

                    img {
                        width: 100%;
                        opacity: 1 !important;
                        display: block !important;
                    }

                    .cursorDefault {
                        cursor: default;
                    }
                }

                .swiper-button {
                    width: 40px;
                    height: 65px;
                    opacity: 0.7;
                    margin-top: -60px !important;
                    background-size: 100%;
                    transition: all 0.3s;

                    &:hover {
                        opacity: 1;
                        transition: all 0.3s;
                    }

                    &.swiper-button-prev {
                        background-image: url(https://geshopimg.logsss.com/uploads/c2Ipt87XRzgOy3KavesirQCbWMmnVZGF.png);
                        left: 0;
                    }

                    &.swiper-button-next {
                        right: 0;
                        background-image: url(https://geshopimg.logsss.com/uploads/xhOnwqUI85BJSlVfHFbykMcmip2u3YD7.png)
                    }

                }

            }
        }

        .inline-block {
            display: inline-block;
        }
    }

    .item-image {
        position: relative;
        font-size: 0;
        padding-top: (329/246) * 100%;

        a {
            position: absolute;
            left: 0px;
            right: 0px;
            top: 0px;
            bottom: 0px;
            display: flex;
            justify-items: center;
            align-items: center;
        }

        .geshop-dl-image-goods {
            width: 100%;
        }
    }

    .default-left-image-box {
        font-size: 0;

        img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
        }

        a {
            display: block;
        }
    }

    .item-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
    }

    .align-center {
        text-align: center;
    }

    .goods-buy-buttom {
        display: inline-block;
        text-align: center;
        border-radius: 20px;
        font-size: 16px;
        max-width: 100%;
        width: 100%;
    }

    // M端
    @media (max-width: 767px) {
        .geshop-U000224-model2-body {
            padding: 20px;

            .wrap {
                padding: 0;
                display: block;
            }

            .default-left-image-box {
                margin-bottom: 15px;

                img {
                    height: auto !important
                }
            }

            .item-image {
                margin-bottom: 12px;
            }

            .list-item {
                padding: 12px;
            }

            .item-title {
                font-size: 13px;
                margin-bottom: 8px;
            }

            .item-shop {
                margin-bottom: 12px;
            }
        }

        .goods-buy-buttom {
            height: 30px;
            line-height: 30px;
        }
    }

    // PAD
    @media (min-width: 768px) and (max-width: 1024px) {
        .geshop-U000224-model2-body {
            padding: 20px 30px 0 30px;

            .wrap {
                .default-left-image-box {
                    max-width: 24%;
                    width: 232px;
                    height: 100%;
                    margin-right: 10px;
                }
            }
        }

        .list-item {
            position: relative;
            width: 100%;
            height: 100%;
            padding: ( 12 / 234) * 100%;
            padding-bottom: 0px;
            box-sizing: border-box;
        }

        .item-shop {
            padding-bottom: (12/246) * 100%;
        }

        .item-title {
            margin-bottom: (8 / 210) * 100%;
        }

        .item-image {
            margin-bottom: (12 / 246) * 100%;
        }

        .goods-buy-buttom {
            height: 36px;
            line-height: 36px;
        }

        .swiper-pagination {
            bottom: -28px;
        }

        .swiper-pagination-bullet {
            margin: 0 6px;
        }

        .geshop-button-buynow {
            display: block;
            height: auto;
            line-height: 1em;
            padding-top: (10/246) * 100%;
            padding-bottom: (10/246) * 100%;
        }
    }

    // 768的特殊处理
    @media (min-width: 768px) and (max-width: 768px) {
        .item-title {
            margin-bottom: 0px;
        }
    }

    // PC
    @media (min-width: 1025px) {
        .geshop-U000224-model2-body {
            padding: 20px 0;

            .wrap {
                padding: 0 100px;

                .default-left-image-box {
                    max-width: 15.69%;
                    width: 270px;
                    height: 100%;
                    margin-right: 20px;
                }
            }

            .list-item {
                position: relative;
                width: 100%;
                height: 100%;
                padding: ( 12 / 273) * 100%;
                padding-bottom: 0px;
                box-sizing: border-box;
            }

            .item-image {
                margin-bottom: (12 / 246) * 100%;
            }

            .item-title {
                font-size: 13px;
                line-height: 1.538em;
                padding-bottom: (8/246) * 100%;
            }

            .item-shop {
                min-height: (27/480) * 100%;
                overflow: hidden;
                padding-bottom: (12/246) * 100%;
            }

            .item-buy-buttom {
                position: relative;
                overflow: hidden;
                font-size: 0px;
            }

            .geshop-button-buynow {
                display: block;
                height: auto;
                line-height: 1em;
                padding-top: (10/246) * 100%;
                padding-bottom: (10/246) * 100%;
            }
        }
    }
</style>
<style lang="less">
    .geshop-U000224-model2-body {
        .wrap {
            position: relative;

            .swiper-pagination-bullet {
                margin: 0 6px;
                background: #c9c6c6;

                &.swiper-pagination-bullet-active {
                    background: #000;
                }
            }
        }

        .geshop-components-discount {
            label i {
                padding-left: 1px;
            }
        }
    }
</style>
